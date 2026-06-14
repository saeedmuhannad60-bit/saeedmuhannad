using System;
using System.Collections.Generic;
using System.Text;
using HardwareShopMaui.Models;
using HardwareShopMaui.Services;

namespace HardwareShopMaui.Pages;

public partial class LowStockPage : ContentPage
{
    private readonly DatabaseService _db;
    private List<Item> _items = new();

    public LowStockPage() : this(new DatabaseService()) { }

    public LowStockPage(DatabaseService db)
    {
        InitializeComponent();
        _db = db;
    }

    protected override async void OnAppearing()
    {
        base.OnAppearing();
        _items = await _db.GetLowStockItemsAsync();
        LowStockList.ItemsSource = _items;
    }

    private async void OnShare(object sender, EventArgs e)
    {
        if (_items.Count == 0)
        {
            await DisplayAlert("Low Stock", "Nothing to reorder right now.", "OK");
            return;
        }

        var sb = new StringBuilder("Reorder list:\n");
        foreach (var i in _items)
            sb.AppendLine($"- {i.Name} (in stock: {i.Quantity}, reorder at: {i.LowStockThreshold})");

        await Share.Default.RequestAsync(new ShareTextRequest
        {
            Title = "Reorder list",
            Text = sb.ToString()
        });
    }
}
