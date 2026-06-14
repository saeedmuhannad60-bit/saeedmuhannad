# How to build the APK

The full .NET MAUI project lives in **`HardwareShopMaui/`**. It already contains the
6 features and a "More" menu screen that links to them.

> ⚠️ The APK **cannot be built in this cloud session** (no .NET SDK / Android SDK here).
> You build it on a computer with Visual Studio. The code is complete and ready.

## Option 1 — Visual Studio (easiest)
1. Install **Visual Studio 2022** with the **".NET Multi-platform App UI development"** workload.
2. Open `HardwareShopMaui/HardwareShopMaui.csproj`.
3. If NuGet warns about a package version, right-click the project ▸ **Manage NuGet Packages**
   and let it pick versions matching your installed MAUI workload.
4. Select an Android device/emulator and press **Run** to test.
5. To make the installable file: **Build ▸ Archive…** ▸ **Distribute** ▸ **Ad Hoc**,
   sign with your keystore, and it produces the signed `.apk` / `.aab`.

## Option 2 — Command line
```bash
# one-time: install the MAUI workload
dotnet workload install maui

cd HardwareShopMaui

# unsigned APK for quick testing
dotnet build -t:Run -f net8.0-android

# signed release APK
dotnet publish -f net8.0-android -c Release \
  /p:AndroidKeyStore=true \
  /p:AndroidSigningKeyStore=myapp.keystore \
  /p:AndroidSigningKeyAlias=myalias \
  /p:AndroidSigningKeyPass=*** \
  /p:AndroidSigningStorePass=***
```
The signed APK appears under `HardwareShopMaui/bin/Release/net8.0-android/`.

> Use the **same keystore** that signed your current `com.muhannad.hardwareshop` build so
> the app updates in place instead of installing as a separate copy.

## Keep your data
The app stores everything in a local SQLite file
(`hardwareshop.db3` in the app data folder). `CREATE TABLE IF NOT EXISTS` is used, so
upgrading the app will not erase existing items.

---

## بالعربي — كيف تبني ملف APK
المشروع الكامل موجود في مجلد **`HardwareShopMaui`** ويحتوي على الميزات الستّ وقائمة "More".

1. ثبّت **Visual Studio 2022** مع حزمة **.NET MAUI**.
2. افتح ملف `HardwareShopMaui/HardwareShopMaui.csproj`.
3. شغّل التطبيق على محاكي أندرويد للتجربة.
4. لإنشاء ملف APK موقّع: **Build ▸ Archive ▸ Distribute**، ووقّعه **بنفس مفتاح التوقيع** الحالي.

> لا يمكن بناء APK داخل هذه البيئة السحابية (لا توجد أدوات .NET/Android).
> الكود جاهز بالكامل — والبناء يتم على جهازك في Visual Studio.
