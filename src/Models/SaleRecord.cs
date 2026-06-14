using System;

namespace HardwareShopMaui.Models;

/// <summary>
/// One line of a sale. Used to calculate Item-wise Profit &amp; Loss.
/// The purchase price is captured at the moment of sale so historical PnL stays
/// correct even if the item's cost price changes later.
/// </summary>
public class SaleRecord
{
    public int Id { get; set; }

    public int ItemId { get; set; }

    public string ItemName { get; set; } = string.Empty;

    public double Quantity { get; set; }

    public double SalePrice { get; set; }       // price sold at, per unit

    public double PurchasePrice { get; set; }   // cost at time of sale, per unit

    public DateTime SoldOn { get; set; } = DateTime.Now;

    public double LineProfit => (SalePrice - PurchasePrice) * Quantity;

    public double LineRevenue => SalePrice * Quantity;
}

/// <summary>Aggregated profit/loss numbers for a single item (the PnL report rows).</summary>
public class ItemPnL
{
    public int ItemId { get; set; }
    public string ItemName { get; set; } = string.Empty;
    public double UnitsSold { get; set; }
    public double Revenue { get; set; }
    public double Cost { get; set; }
    public double Profit => Revenue - Cost;
    public string ProfitDisplay => Profit.ToString("N2");
}
