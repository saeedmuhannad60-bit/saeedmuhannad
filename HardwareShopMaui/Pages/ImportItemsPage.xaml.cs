using System;
using System.IO;
using System.Threading.Tasks;
using HardwareShopMaui.Services;

namespace HardwareShopMaui.Pages;

public partial class ImportItemsPage : ContentPage
{
    private readonly DatabaseService _db;

    public ImportItemsPage() : this(new DatabaseService()) { }

    public ImportItemsPage(DatabaseService db)
    {
        InitializeComponent();
        _db = db;
    }

    private async void OnDownloadTemplate(object sender, EventArgs e)
    {
        try
        {
            var path = Path.Combine(FileSystem.CacheDirectory, "items_template.csv");
            await File.WriteAllTextAsync(path,
                CsvService.Header + "\nHammer,HM-001,Tools,pcs,2.50,5.00,20,5\n");
            await Share.Default.RequestAsync(new ShareFileRequest
            {
                Title = "Items template",
                File = new ShareFile(path)
            });
        }
        catch (Exception ex)
        {
            await DisplayAlert("Error", ex.Message, "OK");
        }
    }

    private async void OnPickFile(object sender, EventArgs e)
    {
        try
        {
            var result = await FilePicker.Default.PickAsync(new PickOptions
            {
                PickerTitle = "Select items CSV"
            });
            if (result == null) return;   // user cancelled

            SetBusy(true);

            using var stream = await result.OpenReadAsync();
            var items = await CsvService.ParseAsync(stream);

            int saved = 0;
            foreach (var item in items)
            {
                await _db.SaveItemAsync(item);
                saved++;
            }

            ResultLabel.Text = $"Imported {saved} item(s) successfully.";
        }
        catch (Exception ex)
        {
            ResultLabel.Text = "Import failed: " + ex.Message;
        }
        finally
        {
            SetBusy(false);
        }
    }

    private void SetBusy(bool busy)
    {
        Busy.IsRunning = busy;
        Busy.IsVisible = busy;
        PickFileButton.IsEnabled = !busy;
    }
}
