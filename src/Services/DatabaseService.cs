using System;
using System.Collections.Generic;
using System.IO;
using System.Threading.Tasks;
using HardwareShopMaui.Models;
using Microsoft.Data.Sqlite;

namespace HardwareShopMaui.Services;

/// <summary>
/// All data access for the new features. Uses Microsoft.Data.Sqlite, the same
/// SQLite provider the app already ships with.
///
/// If your app ALREADY has a database service, you do not need this whole class —
/// just copy the methods you are missing (GetLowStockItemsAsync, GetItemPnLAsync,
/// custom-field methods) and point them at your existing connection string.
/// </summary>
public class DatabaseService
{
    private readonly string _connectionString;

    public DatabaseService(string? dbPath = null)
    {
        dbPath ??= Path.Combine(FileSystem.AppDataDirectory, "hardwareshop.db3");
        _connectionString = $"Data Source={dbPath}";
    }

    private SqliteConnection Open()
    {
        var conn = new SqliteConnection(_connectionString);
        conn.Open();
        return conn;
    }

    /// <summary>Call once at startup (e.g. in App constructor) to make sure tables exist.</summary>
    public async Task InitializeAsync()
    {
        using var conn = Open();
        var cmd = conn.CreateCommand();
        cmd.CommandText = @"
CREATE TABLE IF NOT EXISTS Items (
    Id INTEGER PRIMARY KEY AUTOINCREMENT,
    Name TEXT NOT NULL,
    Sku TEXT,
    Category TEXT,
    Unit TEXT,
    PurchasePrice REAL,
    SalePrice REAL,
    Quantity REAL,
    LowStockThreshold REAL,
    AdditionalFieldsJson TEXT
);

CREATE TABLE IF NOT EXISTS Sales (
    Id INTEGER PRIMARY KEY AUTOINCREMENT,
    ItemId INTEGER,
    ItemName TEXT,
    Quantity REAL,
    SalePrice REAL,
    PurchasePrice REAL,
    SoldOn TEXT
);

CREATE TABLE IF NOT EXISTS CustomFields (
    Id INTEGER PRIMARY KEY AUTOINCREMENT,
    Name TEXT NOT NULL,
    FieldType TEXT
);";
        await cmd.ExecuteNonQueryAsync();
    }

    // -------------------- ITEMS --------------------

    public async Task<List<Item>> GetItemsAsync()
    {
        using var conn = Open();
        var cmd = conn.CreateCommand();
        cmd.CommandText = "SELECT * FROM Items ORDER BY Name COLLATE NOCASE;";
        return await ReadItemsAsync(cmd);
    }

    public async Task<Item?> GetItemAsync(int id)
    {
        using var conn = Open();
        var cmd = conn.CreateCommand();
        cmd.CommandText = "SELECT * FROM Items WHERE Id = $id;";
        cmd.Parameters.AddWithValue("$id", id);
        var list = await ReadItemsAsync(cmd);
        return list.Count > 0 ? list[0] : null;
    }

    public async Task<int> SaveItemAsync(Item item)
    {
        using var conn = Open();
        var cmd = conn.CreateCommand();
        if (item.Id == 0)
        {
            cmd.CommandText = @"
INSERT INTO Items (Name, Sku, Category, Unit, PurchasePrice, SalePrice, Quantity, LowStockThreshold, AdditionalFieldsJson)
VALUES ($name, $sku, $cat, $unit, $pp, $sp, $qty, $low, $json);
SELECT last_insert_rowid();";
        }
        else
        {
            cmd.CommandText = @"
UPDATE Items SET Name=$name, Sku=$sku, Category=$cat, Unit=$unit, PurchasePrice=$pp,
    SalePrice=$sp, Quantity=$qty, LowStockThreshold=$low, AdditionalFieldsJson=$json
WHERE Id=$id;
SELECT $id;";
            cmd.Parameters.AddWithValue("$id", item.Id);
        }
        cmd.Parameters.AddWithValue("$name", item.Name);
        cmd.Parameters.AddWithValue("$sku", item.Sku ?? "");
        cmd.Parameters.AddWithValue("$cat", item.Category ?? "");
        cmd.Parameters.AddWithValue("$unit", item.Unit ?? "pcs");
        cmd.Parameters.AddWithValue("$pp", item.PurchasePrice);
        cmd.Parameters.AddWithValue("$sp", item.SalePrice);
        cmd.Parameters.AddWithValue("$qty", item.Quantity);
        cmd.Parameters.AddWithValue("$low", item.LowStockThreshold);
        cmd.Parameters.AddWithValue("$json", item.AdditionalFieldsJson ?? "{}");
        var result = await cmd.ExecuteScalarAsync();
        return Convert.ToInt32(result);
    }

    public async Task DeleteItemAsync(int id)
    {
        using var conn = Open();
        var cmd = conn.CreateCommand();
        cmd.CommandText = "DELETE FROM Items WHERE Id=$id;";
        cmd.Parameters.AddWithValue("$id", id);
        await cmd.ExecuteNonQueryAsync();
    }

    /// <summary>Low Stock feature: items at or below their reorder threshold.</summary>
    public async Task<List<Item>> GetLowStockItemsAsync()
    {
        using var conn = Open();
        var cmd = conn.CreateCommand();
        cmd.CommandText = "SELECT * FROM Items WHERE Quantity <= LowStockThreshold ORDER BY Quantity ASC;";
        return await ReadItemsAsync(cmd);
    }

    private static async Task<List<Item>> ReadItemsAsync(SqliteCommand cmd)
    {
        var list = new List<Item>();
        using var r = await cmd.ExecuteReaderAsync();
        while (await r.ReadAsync())
        {
            list.Add(new Item
            {
                Id = r.GetInt32(r.GetOrdinal("Id")),
                Name = SafeStr(r, "Name"),
                Sku = SafeStr(r, "Sku"),
                Category = SafeStr(r, "Category"),
                Unit = SafeStr(r, "Unit"),
                PurchasePrice = SafeDbl(r, "PurchasePrice"),
                SalePrice = SafeDbl(r, "SalePrice"),
                Quantity = SafeDbl(r, "Quantity"),
                LowStockThreshold = SafeDbl(r, "LowStockThreshold"),
                AdditionalFieldsJson = SafeStr(r, "AdditionalFieldsJson", "{}")
            });
        }
        return list;
    }

    // -------------------- SALES + PnL --------------------

    public async Task RecordSaleAsync(SaleRecord sale)
    {
        using var conn = Open();
        var cmd = conn.CreateCommand();
        cmd.CommandText = @"
INSERT INTO Sales (ItemId, ItemName, Quantity, SalePrice, PurchasePrice, SoldOn)
VALUES ($itemId, $name, $qty, $sp, $pp, $on);";
        cmd.Parameters.AddWithValue("$itemId", sale.ItemId);
        cmd.Parameters.AddWithValue("$name", sale.ItemName);
        cmd.Parameters.AddWithValue("$qty", sale.Quantity);
        cmd.Parameters.AddWithValue("$sp", sale.SalePrice);
        cmd.Parameters.AddWithValue("$pp", sale.PurchasePrice);
        cmd.Parameters.AddWithValue("$on", sale.SoldOn.ToString("o"));
        await cmd.ExecuteNonQueryAsync();
    }

    /// <summary>Item-wise PnL feature: profit per item from recorded sales.</summary>
    public async Task<List<ItemPnL>> GetItemPnLAsync()
    {
        using var conn = Open();
        var cmd = conn.CreateCommand();
        cmd.CommandText = @"
SELECT ItemId, ItemName,
       SUM(Quantity)               AS Units,
       SUM(SalePrice * Quantity)   AS Revenue,
       SUM(PurchasePrice * Quantity) AS Cost
FROM Sales
GROUP BY ItemId, ItemName
ORDER BY (SUM(SalePrice * Quantity) - SUM(PurchasePrice * Quantity)) DESC;";
        var list = new List<ItemPnL>();
        using var r = await cmd.ExecuteReaderAsync();
        while (await r.ReadAsync())
        {
            list.Add(new ItemPnL
            {
                ItemId = r.GetInt32(0),
                ItemName = r.IsDBNull(1) ? "" : r.GetString(1),
                UnitsSold = r.IsDBNull(2) ? 0 : r.GetDouble(2),
                Revenue = r.IsDBNull(3) ? 0 : r.GetDouble(3),
                Cost = r.IsDBNull(4) ? 0 : r.GetDouble(4),
            });
        }
        return list;
    }

    // -------------------- CUSTOM (ADDITIONAL) FIELDS --------------------

    public async Task<List<CustomField>> GetCustomFieldsAsync()
    {
        using var conn = Open();
        var cmd = conn.CreateCommand();
        cmd.CommandText = "SELECT Id, Name, FieldType FROM CustomFields ORDER BY Name COLLATE NOCASE;";
        var list = new List<CustomField>();
        using var r = await cmd.ExecuteReaderAsync();
        while (await r.ReadAsync())
            list.Add(new CustomField { Id = r.GetInt32(0), Name = r.GetString(1), FieldType = r.IsDBNull(2) ? "text" : r.GetString(2) });
        return list;
    }

    public async Task SaveCustomFieldAsync(CustomField field)
    {
        using var conn = Open();
        var cmd = conn.CreateCommand();
        if (field.Id == 0)
            cmd.CommandText = "INSERT INTO CustomFields (Name, FieldType) VALUES ($n, $t);";
        else
        {
            cmd.CommandText = "UPDATE CustomFields SET Name=$n, FieldType=$t WHERE Id=$id;";
            cmd.Parameters.AddWithValue("$id", field.Id);
        }
        cmd.Parameters.AddWithValue("$n", field.Name);
        cmd.Parameters.AddWithValue("$t", field.FieldType ?? "text");
        await cmd.ExecuteNonQueryAsync();
    }

    public async Task DeleteCustomFieldAsync(int id)
    {
        using var conn = Open();
        var cmd = conn.CreateCommand();
        cmd.CommandText = "DELETE FROM CustomFields WHERE Id=$id;";
        cmd.Parameters.AddWithValue("$id", id);
        await cmd.ExecuteNonQueryAsync();
    }

    // -------------------- helpers --------------------

    private static string SafeStr(SqliteDataReader r, string col, string fallback = "")
    {
        var i = r.GetOrdinal(col);
        return r.IsDBNull(i) ? fallback : r.GetString(i);
    }

    private static double SafeDbl(SqliteDataReader r, string col)
    {
        var i = r.GetOrdinal(col);
        return r.IsDBNull(i) ? 0 : r.GetDouble(i);
    }
}
