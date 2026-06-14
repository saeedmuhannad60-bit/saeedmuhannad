using HardwareShopMaui.Services;

namespace HardwareShopMaui;

public partial class App : Application
{
    /// <summary>Single shared database service for the whole app.</summary>
    public static DatabaseService Database { get; } = new DatabaseService();

    public App()
    {
        InitializeComponent();

        // Make sure the SQLite tables exist before any page loads.
        _ = Database.InitializeAsync();

        // NavigationPage lets the feature pages use Navigation.PushAsync / PopAsync.
        MainPage = new NavigationPage(new MainPage());
    }
}
