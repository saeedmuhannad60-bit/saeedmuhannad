using Microsoft.Extensions.Logging;

namespace HardwareShopMaui;

public static class MauiProgram
{
    public static MauiApp CreateMauiApp()
    {
        var builder = MauiApp.CreateBuilder();
        builder.UseMauiApp<App>();
        // To use custom fonts, drop the .ttf files into Resources/Fonts and register them here:
        //   .ConfigureFonts(fonts => fonts.AddFont("OpenSans-Regular.ttf", "OpenSansRegular"));

#if DEBUG
        builder.Logging.AddDebug();
#endif

        return builder.Build();
    }
}
