using System;
using HardwareShopMaui.Services;

namespace HardwareShopMaui.Pages;

public partial class ItemPnLPage : ContentPage
{
    private readonly DatabaseService _db;

    public ItemPnLPage() : this(new DatabaseService()) { }

    public ItemPnLPage(DatabaseService db)
    {
        InitializeComponent();
        _db = db;
    }

    protected override async void OnAppearing()
    {
        base.OnAppearing();
        await LoadAsync();
    }

    private async System.Threading.Tasks.Task LoadAsync()
    {
        var rows = await _db.GetItemPnLAsync();
        PnlList.ItemsSource = rows;

        double profit = 0, revenue = 0;
        foreach (var r in rows) { profit += r.Profit; revenue += r.Revenue; }

        TotalProfitLabel.Text = profit.ToString("N2");
        TotalRevenueLabel.Text = revenue.ToString("N2");
        TotalProfitLabel.TextColor = profit < 0 ? Colors.Red : Colors.Green;
    }
}
