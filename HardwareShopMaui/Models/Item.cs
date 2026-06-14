using System.Collections.Generic;
using System.Text.Json;

namespace HardwareShopMaui.Models;

/// <summary>
/// A single stock item in the hardware shop.
/// Custom user-defined "Additional Fields" are stored in <see cref="AdditionalFieldsJson"/>
/// as a JSON object so the schema never has to change when the user adds a new field.
/// </summary>
public class Item
{
    public int Id { get; set; }

    public string Name { get; set; } = string.Empty;

    public string Sku { get; set; } = string.Empty;      // barcode / item code

    public string Category { get; set; } = string.Empty;

    public string Unit { get; set; } = "pcs";            // pcs, box, kg, m ...

    public double PurchasePrice { get; set; }            // cost price per unit

    public double SalePrice { get; set; }                // selling price per unit

    public double Quantity { get; set; }                 // current stock on hand

    public double LowStockThreshold { get; set; } = 5;   // reorder point

    /// <summary>Raw JSON for the custom Additional Fields. Stored as one TEXT column.</summary>
    public string AdditionalFieldsJson { get; set; } = "{}";

    /// <summary>Convenience accessor that (de)serializes <see cref="AdditionalFieldsJson"/>.</summary>
    public Dictionary<string, string> AdditionalFields
    {
        get => string.IsNullOrWhiteSpace(AdditionalFieldsJson)
            ? new Dictionary<string, string>()
            : JsonSerializer.Deserialize<Dictionary<string, string>>(AdditionalFieldsJson)
              ?? new Dictionary<string, string>();
        set => AdditionalFieldsJson = JsonSerializer.Serialize(value);
    }

    // ----- Computed helpers (not stored) -----

    /// <summary>Profit per unit = sale price - purchase price.</summary>
    public double UnitProfit => SalePrice - PurchasePrice;

    /// <summary>Money currently tied up in this item's stock (at cost).</summary>
    public double StockValueAtCost => PurchasePrice * Quantity;

    public bool IsLowStock => Quantity <= LowStockThreshold;
}
