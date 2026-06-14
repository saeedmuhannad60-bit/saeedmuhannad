using HardwareShopMaui.Pages;

namespace HardwareShopMaui;

public partial class MainPage : ContentPage
{
    public MainPage()
    {
        InitializeComponent();
    }

    // Each page is given the shared database service from App.Database.
    private async void OnItemDetails(object sender, EventArgs e) =>
        await Navigation.PushAsync(new ItemDetailsPage(0, App.Database));

    private async void OnImport(object sender, EventArgs e) =>
        await Navigation.PushAsync(new ImportItemsPage(App.Database));

    private async void OnExport(object sender, EventArgs e) =>
        await Navigation.PushAsync(new ExportItemsPage(App.Database));

    private async void OnPnL(object sender, EventArgs e) =>
        await Navigation.PushAsync(new ItemPnLPage(App.Database));

    private async void OnAdditionalFields(object sender, EventArgs e) =>
        await Navigation.PushAsync(new AdditionalFieldsPage(App.Database));

    private async void OnLowStock(object sender, EventArgs e) =>
        await Navigation.PushAsync(new LowStockPage(App.Database));
}
