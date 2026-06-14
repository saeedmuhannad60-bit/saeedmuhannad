using System;
using System.Collections.Generic;
using System.Globalization;
using System.IO;
using System.Text;
using System.Threading.Tasks;
using HardwareShopMaui.Models;

namespace HardwareShopMaui.Services;

/// <summary>
/// CSV import/export for items. CSV is used (not real .xlsx) because it needs no
/// external NuGet package and opens fine in Excel / Google Sheets.
/// Column order: Name,Sku,Category,Unit,PurchasePrice,SalePrice,Quantity,LowStockThreshold
/// </summary>
public static class CsvService
{
    public const string Header =
        "Name,Sku,Category,Unit,PurchasePrice,SalePrice,Quantity,LowStockThreshold";

    public static string ToCsv(IEnumerable<Item> items)
    {
        var sb = new StringBuilder();
        sb.AppendLine(Header);
        foreach (var i in items)
        {
            sb.AppendLine(string.Join(",",
                Escape(i.Name),
                Escape(i.Sku),
                Escape(i.Category),
                Escape(i.Unit),
                i.PurchasePrice.ToString(CultureInfo.InvariantCulture),
                i.SalePrice.ToString(CultureInfo.InvariantCulture),
                i.Quantity.ToString(CultureInfo.InvariantCulture),
                i.LowStockThreshold.ToString(CultureInfo.InvariantCulture)));
        }
        return sb.ToString();
    }

    /// <summary>Parse a CSV stream into items. Skips the header row. Bad rows are ignored.</summary>
    public static async Task<List<Item>> ParseAsync(Stream stream)
    {
        var items = new List<Item>();
        using var reader = new StreamReader(stream);
        string? line;
        bool first = true;
        while ((line = await reader.ReadLineAsync()) != null)
        {
            if (first) { first = false; continue; }              // skip header
            if (string.IsNullOrWhiteSpace(line)) continue;

            var cols = SplitCsvLine(line);
            if (cols.Count < 1 || string.IsNullOrWhiteSpace(cols[0])) continue;

            items.Add(new Item
            {
                Name = Get(cols, 0),
                Sku = Get(cols, 1),
                Category = Get(cols, 2),
                Unit = string.IsNullOrWhiteSpace(Get(cols, 3)) ? "pcs" : Get(cols, 3),
                PurchasePrice = ParseDouble(Get(cols, 4)),
                SalePrice = ParseDouble(Get(cols, 5)),
                Quantity = ParseDouble(Get(cols, 6)),
                LowStockThreshold = string.IsNullOrWhiteSpace(Get(cols, 7)) ? 5 : ParseDouble(Get(cols, 7)),
            });
        }
        return items;
    }

    // ----- helpers -----

    private static string Get(List<string> cols, int i) => i < cols.Count ? cols[i].Trim() : "";

    private static double ParseDouble(string s) =>
        double.TryParse(s, NumberStyles.Any, CultureInfo.InvariantCulture, out var d) ? d : 0;

    private static string Escape(string? s)
    {
        s ??= "";
        if (s.Contains(',') || s.Contains('"') || s.Contains('\n'))
            return "\"" + s.Replace("\"", "\"\"") + "\"";
        return s;
    }

    /// <summary>Minimal CSV line splitter that respects double-quoted fields.</summary>
    private static List<string> SplitCsvLine(string line)
    {
        var result = new List<string>();
        var sb = new StringBuilder();
        bool inQuotes = false;
        for (int i = 0; i < line.Length; i++)
        {
            char c = line[i];
            if (inQuotes)
            {
                if (c == '"' && i + 1 < line.Length && line[i + 1] == '"') { sb.Append('"'); i++; }
                else if (c == '"') inQuotes = false;
                else sb.Append(c);
            }
            else
            {
                if (c == '"') inQuotes = true;
                else if (c == ',') { result.Add(sb.ToString()); sb.Clear(); }
                else sb.Append(c);
            }
        }
        result.Add(sb.ToString());
        return result;
    }
}
