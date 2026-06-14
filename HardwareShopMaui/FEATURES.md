# Hardware Shop – New Features (drop-in code)

Ready-to-paste **.NET MAUI (C# / XAML)** code for six features:

| Feature | Files |
|---|---|
| **Import Items** (CSV) | `Pages/ImportItemsPage.xaml(.cs)`, `Services/CsvService.cs` |
| **Export Items** (CSV) | `Pages/ExportItemsPage.xaml(.cs)`, `Services/CsvService.cs` |
| **Item-wise PnL** | `Pages/ItemPnLPage.xaml(.cs)`, `Models/SaleRecord.cs` |
| **Additional Fields** | `Pages/AdditionalFieldsPage.xaml(.cs)`, `Models/CustomField.cs` |
| **Item Details** | `Pages/ItemDetailsPage.xaml(.cs)`, `Models/Item.cs` |
| **Low Stock** | `Pages/LowStockPage.xaml(.cs)` |

Shared data layer: `Services/DatabaseService.cs` (uses **Microsoft.Data.Sqlite**, which your app already ships with).

> The root namespace is `HardwareShopMaui` (matches your assembly). If your project
> uses a different namespace, do a find-and-replace on `HardwareShopMaui`.

---

## How to integrate (5 steps)

1. **Copy the folders** `Models/`, `Services/`, `Pages/` into your project (keep the
   `HardwareShopMaui.*` namespaces, or rename to match yours).

2. **Initialize the database once** at startup — in `App.xaml.cs` constructor or
   `MauiProgram.cs`:
   ```csharp
   var db = new HardwareShopMaui.Services.DatabaseService();
   await db.InitializeAsync();
   ```
   > If you already have an Items table, either point `DatabaseService` at your existing
   > DB file, or just reuse the report methods (`GetLowStockItemsAsync`, `GetItemPnLAsync`)
   > against your own data layer. `CREATE TABLE IF NOT EXISTS` will not overwrite data.

3. **Wire up the "more" menu.** Wherever your "more" list lives, navigate to each page.
   With Shell:
   ```csharp
   await Shell.Current.Navigation.PushAsync(new ImportItemsPage());
   await Shell.Current.Navigation.PushAsync(new ExportItemsPage());
   await Shell.Current.Navigation.PushAsync(new ItemPnLPage());
   await Shell.Current.Navigation.PushAsync(new AdditionalFieldsPage());
   await Shell.Current.Navigation.PushAsync(new ItemDetailsPage(0)); // 0 = new item
   await Shell.Current.Navigation.PushAsync(new LowStockPage());
   ```

4. **Permissions / capabilities.** Import & Export use `FilePicker` and `Share` from
   `Microsoft.Maui.Storage` (built into MAUI — no extra NuGet). On Android nothing extra
   is needed for share; file picking works out of the box.

5. **Build the APK** in Visual Studio:
   `Build ▸ Archive…` (or `dotnet publish -f net10.0-android -c Release`), then sign with
   your existing keystore — the same one that produced `com.muhannad.hardwareshop`.

---

## Notes
- **PnL** reads from a `Sales` table. Call `db.RecordSaleAsync(...)` from your billing
  screen so the report has data. Until then the PnL page shows "No sales recorded yet."
- **Additional Fields** are stored per item as JSON in `Item.AdditionalFieldsJson`, so you
  never have to change the database schema when the user adds a new field.
- **CSV** (not real `.xlsx`) is used so there's zero extra dependency; it opens directly in
  Excel and Google Sheets.

---

## بالعربي

هذا كود جاهز للصق بلغة **C# / .NET MAUI** يضيف 6 ميزات لتطبيق محل الأدوات:
استيراد الأصناف، تصدير الأصناف، الأرباح لكل صنف، حقول إضافية، تفاصيل الصنف، المخزون المنخفض.

**خطوات الدمج:**
1. انسخ مجلدات `Models` و `Services` و `Pages` إلى مشروعك.
2. شغّل قاعدة البيانات مرة واحدة عند البدء: `await db.InitializeAsync();`
3. اربط أزرار قائمة "more" بالصفحات عبر `PushAsync` كما في الأعلى.
4. الاستيراد/التصدير يستخدمان أدوات MAUI المدمجة (لا تحتاج مكتبات إضافية).
5. ابنِ ملف APK في **Visual Studio** ووقّعه بنفس مفتاح التوقيع الحالي.

> ملاحظة: لا يمكن بناء ملف APK داخل هذه البيئة (لا يوجد .NET/Android SDK).
> أنا أكتب الكود — والبناء يتم على جهازك في Visual Studio.
