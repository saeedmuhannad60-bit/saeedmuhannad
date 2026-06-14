using System;
using System.Collections.Generic;
using System.Globalization;
using HardwareShopMaui.Models;
using HardwareShopMaui.Services;

namespace HardwareShopMaui.Pages;

public partial class ItemDetailsPage : ContentPage
{
    private readonly DatabaseService _db;
    private readonly int _itemId;
    private Item _item = new();

    // Keeps each custom field name mapped to the Entry control built for it.
    private readonly Dictionary<string, Entry> _customEntries = new();

    public ItemDetailsPage() : this(0, new DatabaseService()) { }

    public ItemDetailsPage(int itemId) : this(itemId, new DatabaseService()) { }

    public ItemDetailsPage(int itemId, DatabaseService db)
    {
        InitializeComponent();
        _db = db;
        _itemId = itemId;
    }

    protected override async void OnAppearing()
    {
        base.OnAppearing();

        if (_itemId != 0)
            _item = await _db.GetItemAsync(_itemId) ?? new Item();

        BindItemToUi();
        await BuildCustomFieldsAsync();
        UpdateProfitSummary();
    }

    private void BindItemToUi()
    {
        NameEntry.Text = _item.Name;
        SkuEntry.Text = _item.Sku;
        CategoryEntry.Text = _item.Category;
        UnitEntry.Text = _item.Unit;
        PurchaseEntry.Text = _item.PurchasePrice.ToString(CultureInfo.InvariantCulture);
        SaleEntry.Text = _item.SalePrice.ToString(CultureInfo.InvariantCulture);
        QuantityEntry.Text = _item.Quantity.ToString(CultureInfo.InvariantCulture);
        ThresholdEntry.Text = _item.LowStockThreshold.ToString(CultureInfo.InvariantCulture);
    }

    private async System.Threading.Tasks.Task BuildCustomFieldsAsync()
    {
        var fields = await _db.GetCustomFieldsAsync();
        CustomFieldsContainer.Children.Clear();
        _customEntries.Clear();

        CustomHeader.IsVisible = fields.Count > 0;

        var values = _item.AdditionalFields;
        foreach (var f in fields)
        {
            CustomFieldsContainer.Children.Add(new Label
            {
                Text = f.Name,
                FontSize = 12,
                TextColor = Colors.Gray
            });

            var entry = new Entry
            {
                Placeholder = f.Name,
                Keyboard = f.FieldType == "number" ? Keyboard.Numeric : Keyboard.Default,
                Text = values.TryGetValue(f.Name, out var v) ? v : string.Empty
            };
            CustomFieldsContainer.Children.Add(entry);
            _customEntries[f.Name] = entry;
        }
    }

    private void UpdateProfitSummary()
    {
        ProfitSummary.Text =
            $"Unit profit: {_item.UnitProfit:N2}   •   Stock value (cost): {_item.StockValueAtCost:N2}";
    }

    private async void OnSave(object sender, EventArgs e)
    {
        if (string.IsNullOrWhiteSpace(NameEntry.Text))
        {
            await DisplayAlert("Save", "Name is required.", "OK");
            return;
        }

        _item.Name = NameEntry.Text.Trim();
        _item.Sku = SkuEntry.Text?.Trim() ?? "";
        _item.Category = CategoryEntry.Text?.Trim() ?? "";
        _item.Unit = string.IsNullOrWhiteSpace(UnitEntry.Text) ? "pcs" : UnitEntry.Text.Trim();
        _item.PurchasePrice = ParseDouble(PurchaseEntry.Text);
        _item.SalePrice = ParseDouble(SaleEntry.Text);
        _item.Quantity = ParseDouble(QuantityEntry.Text);
        _item.LowStockThreshold = ParseDouble(ThresholdEntry.Text);

        // Collect custom field values
        var values = new Dictionary<string, string>();
        foreach (var kv in _customEntries)
            values[kv.Key] = kv.Value.Text ?? "";
        _item.AdditionalFields = values;

        await _db.SaveItemAsync(_item);
        await DisplayAlert("Saved", "Item saved successfully.", "OK");
        await Navigation.PopAsync();
    }

    private static double ParseDouble(string? s) =>
        double.TryParse(s, NumberStyles.Any, CultureInfo.InvariantCulture, out var d) ? d : 0;
}
