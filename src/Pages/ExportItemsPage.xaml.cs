using System;
using System.IO;
using HardwareShopMaui.Services;

namespace HardwareShopMaui.Pages;

public partial class ExportItemsPage : ContentPage
{
    private readonly DatabaseService _db;

    public ExportItemsPage() : this(new DatabaseService()) { }

    public ExportItemsPage(DatabaseService db)
    {
        InitializeComponent();
        _db = db;
    }

    private async void OnExport(object sender, EventArgs e)
    {
        try
        {
            Busy.IsRunning = Busy.IsVisible = true;
            ExportButton.IsEnabled = false;

            var items = await _db.GetItemsAsync();
            var csv = CsvService.ToCsv(items);

            var fileName = $"items_{DateTime.Now:yyyyMMdd_HHmm}.csv";
            var path = Path.Combine(FileSystem.CacheDirectory, fileName);
            await File.WriteAllTextAsync(path, csv);

            ResultLabel.Text = $"Exported {items.Count} item(s).";

            await Share.Default.RequestAsync(new ShareFileRequest
            {
                Title = "Items export",
                File = new ShareFile(path)
            });
        }
        catch (Exception ex)
        {
            ResultLabel.Text = "Export failed: " + ex.Message;
        }
        finally
        {
            Busy.IsRunning = Busy.IsVisible = false;
            ExportButton.IsEnabled = true;
        }
    }
}
