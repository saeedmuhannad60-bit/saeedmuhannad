namespace HardwareShopMaui.Models;

/// <summary>
/// Definition of a user-created "Additional Field" (e.g. Brand, Size, Warranty).
/// The actual value for each item is stored per-item in <see cref="Item.AdditionalFields"/>.
/// </summary>
public class CustomField
{
    public int Id { get; set; }

    /// <summary>Display name shown on the Item Details form, e.g. "Brand".</summary>
    public string Name { get; set; } = string.Empty;

    /// <summary>"text", "number" or "date" — controls the input keyboard/validation.</summary>
    public string FieldType { get; set; } = "text";
}
