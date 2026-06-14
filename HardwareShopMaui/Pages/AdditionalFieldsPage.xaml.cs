using System;
using HardwareShopMaui.Models;
using HardwareShopMaui.Services;

namespace HardwareShopMaui.Pages;

public partial class AdditionalFieldsPage : ContentPage
{
    private readonly DatabaseService _db;

    public AdditionalFieldsPage() : this(new DatabaseService()) { }

    public AdditionalFieldsPage(DatabaseService db)
    {
        InitializeComponent();
        _db = db;
    }

    protected override async void OnAppearing()
    {
        base.OnAppearing();
        await RefreshAsync();
    }

    private async System.Threading.Tasks.Task RefreshAsync()
    {
        FieldsList.ItemsSource = await _db.GetCustomFieldsAsync();
    }

    private async void OnAddField(object sender, EventArgs e)
    {
        var name = FieldNameEntry.Text?.Trim();
        if (string.IsNullOrWhiteSpace(name))
        {
            await DisplayAlert("Add field", "Please enter a field name.", "OK");
            return;
        }

        await _db.SaveCustomFieldAsync(new CustomField
        {
            Name = name,
            FieldType = FieldTypePicker.SelectedItem as string ?? "text"
        });

        FieldNameEntry.Text = string.Empty;
        FieldTypePicker.SelectedItem = null;
        await RefreshAsync();
    }

    private async void OnDeleteField(object sender, EventArgs e)
    {
        if (sender is Button b && b.CommandParameter is int id)
        {
            await _db.DeleteCustomFieldAsync(id);
            await RefreshAsync();
        }
    }
}
