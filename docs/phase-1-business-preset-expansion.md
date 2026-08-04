# Phase 1: BusinessPreset Expansion — Implementation Report

**Date:** August 3, 2026
**Status:** Complete ✅
**Tests:** 100/100 passing

---

## Overview

Phase 1 transforms the BusinessPreset from a simple config-definition class into a **complete niche-installation engine**. A single `apply()` call now seeds the entire Bagisto database for a business niche — categories, CMS pages, EAV attributes, attribute families, email templates, widgets, banners, roles, and settings.

The **Fashion** preset serves as the reference implementation, demonstrating the full contract surface area.

---

## Contract Expansion

### Before (11 methods)

```php
interface BusinessPreset {
    getCode(), getName(), getDescription(),
    getRecommendedTheme(), getRecommendedTemplate(),
    getDefaultCategories(), getRecommendedSettings(),
    getSampleProducts(), getDefaultPages(), getNavigation()
}
```

### After (19 methods)

| Method | Return | Creates |
|--------|--------|---------|
| `getProductAttributes()` | `array` | Bagisto EAV attributes with options, swatches, translations |
| `getAttributeFamily()` | `array` | Attribute family + groups + group-to-attribute mappings |
| `getEmailTemplates()` | `array` | `satora_email_templates` rows |
| `getWidgets()` | `array` | `satora_widgets` rows (sidebar filters, homepage widgets, footer) |
| `getBanners()` | `array` | `satora_banners` rows (hero, promo, top-bar) |
| `getRoles()` | `array` | Bagisto `roles` rows with permission sets |
| `getPermissions()` | `array` | Permission definitions (stored in metadata) |
| `getProductTypes()` | `array` | Product type codes (simple, configurable, bundle, etc.) |

> **Naming note:** `getProductAttributes()` was chosen over `getAttributes()` to avoid collision with Eloquent Model's `getAttributes()`.

---

## New Database Tables

### `satora_email_templates`

```sql
id, code (unique), name, subject, content (text),
preset_code (indexed), tenant_id (nullable), is_active, timestamps
```

Used by: `PresetApplier::createEmailTemplates()` — idempotent via `updateOrInsert`.

### `satora_widgets`

```sql
id, type, name, position (sidebar|homepage|footer),
preset_code, tenant_id, config (json), sort_order, is_active, timestamps
```

Used by: `PresetApplier::createWidgets()` — idempotent.

### `satora_banners`

```sql
id, title, subtitle, image_path, link_url,
position (homepage|top_bar|category), preset_code, tenant_id,
sort_order, is_active, timestamps
```

Used by: `PresetApplier::createBanners()` — idempotent.

### Extended `satora_business_presets`

7 new JSON columns added: `attributes`, `attribute_family`, `email_templates`, `widgets`, `banners`, `roles`, `product_types`.

---

## PresetApplier — The Engine

### `apply(BusinessPresetContract $preset, array $options = [])`

Ordered execution:
1. **Categories** — Hierarchical, 4 locales (en, fa, ar, tr)
2. **CMS Pages** — `cms_pages` + `cms_page_translations` with real HTML content
3. **Attributes** — EAV attributes + options + swatches + translations
4. **Attribute Family** — Family + groups + group-attribute mappings + core attribute inclusion
5. **Email Templates** — Per-niche templates
6. **Widgets** — Sidebar filters, homepage sections, footer
7. **Banners** — Hero, promo, top-bar
8. **Roles** — Bagisto roles with permission JSON
9. **Settings** — `core_config` key/value pairs
10. **Config** — Stores active preset, theme, template, product types

All insertions are **idempotent** — re-applying the same preset won't create duplicates.

### `uninstall(BusinessPresetContract $preset, ?int $tenantId = null)`

Removes:
- Email templates
- Widgets
- Banners
- Preset config entry

### Idempotency Strategy

| Entity | Strategy |
|--------|----------|
| Attributes | Check `WHERE code = ?` before INSERT |
| Attribute translations | Check `WHERE attribute_id + locale` before INSERT |
| Options | Check `WHERE code = ?` (NEW attribute only if not existing) |
| Option translations | Check `WHERE option_id + locale` before INSERT |
| Email templates | `updateOrInsert` on `code + preset_code + tenant_id` |
| Widgets | `updateOrInsert` on `type + name + preset_code + tenant_id` |
| Banners | `updateOrInsert` on `title + preset_code + tenant_id` |
| Roles | `updateOrInsert` on `name` |
| Settings | `updateOrInsert` on `code` |

---

## FashionPreset — Reference Implementation

### Categories (20+)
```
Women → Dresses, Tops, Bottoms, Outerwear, Activewear
Men → Shirts, Pants, Jackets, Activewear
Accessories → Bags, Jewelry, Watches, Belts, Scarves
Shoes → Sneakers, Boots, Sandals, Heels, Flats
New Arrivals, Sale
```

### Attributes (8, with options)
- **Size** (select) — XS through XXL, numeric 2-16, One Size (15 options)
- **Color** (select, swatch=color) — Black to Brown with hex values (12 options)
- **Material** (multiselect) — Cotton through Nylon (12 options)
- **Brand** (select) — Nike through Tommy Hilfiger (8 options)
- **Season** (select) — SS, AW, Resort, Year-Round (4 options)
- **Fit** (select) — Slim through Tall (5 options)
- **Gender** (select) — Women, Men, Unisex, Kids (4 options)
- **Care Instructions** (textarea)

### Attribute Family: `fashion`
```
Column 1: General (sku, name, url_key, brand)
          Size & Fit (size, color, fit, gender)
          Product Details (material, season, care_instructions)
Column 2: Description (description, short_description)
          Price (price, cost, special_price)
          Shipping (weight, width, height, depth)
```

### Email Templates (4)
- Order Confirmation
- Shipping Confirmation
- Back in Stock (per size/color)
- New Collection Alert

### Widgets (8)
- Sidebar: Size Filter (checkboxes), Color Filter (swatches), Price Range (slider), Brand Filter (checkboxes)
- Homepage: New Arrivals (auto-tagged), Trending Now (view-based), Instagram Shop
- Footer: Newsletter Signup (with discount incentive)

### Banners (3)
- New Season Collection → /new-arrivals
- Summer Sale (up to 50% off) → /sale
- Free Shipping (on orders over $100, top-bar, no link)

### Roles (2)
- **Fashion Store Manager** — 19 permissions (products, categories, attributes, promotions, orders, invoices, shipments, customers, reviews, CMS, reporting)
- **Fashion Content Editor** — 3 permissions (CMS create/edit/delete, products edit, categories edit)

### CMS Pages (5)
- About Us — Brand story
- Size Guide — HTML table with measurements (XS-XL, bust/waist/hips in cm)
- Lookbook — Seasonal editorial
- Sustainability — Ethical practices
- Shipping & Returns — Free shipping over $100, 30-day returns

### Product Types: `['simple', 'configurable', 'bundle']`

---

## Files Changed

```
packages/Webkul/BusinessPreset/src/
├── Contracts/BusinessPreset.php              ← 11→19 methods
├── AbstractBusinessPreset.php                ← New properties + methods + defaults
├── Models/BusinessPreset.php                 ← 8 new methods + fillable/casts
├── Helpers/PresetApplier.php                 ← Complete rewrite (~380 lines)
├── Presets/FashionPreset.php                 ← Complete reference implementation (~430 lines)
├── Database/Migrations/
│   ├── 2026_08_03_100001_create_satora_email_templates_table.php  ← NEW
│   ├── 2026_08_03_100002_create_satora_widgets_table.php          ← NEW
│   ├── 2026_08_03_100003_create_satora_banners_table.php          ← NEW
│   └── 2026_08_03_200000_add_extended_columns_to_...              ← NEW
└── tests/Feature/
    ├── PresetApplierTest.php                 ← Updated expectations
    └── AdminPresetControllerTest.php         ← Fixed CSRF/session handling
```

---

## Test Results

```
Tests: 100 passed (317 assertions)
Duration: ~20s
```

Test suites:
- **Unit/PresetRegistryTest** — Preset discovery, filtering
- **Unit/PresetTest** — BusinessPreset model CRUD
- **Feature/PresetApplierTest** — Apply Fashion, Electronics, Beauty, Grocery, Custom, Digital presets; idempotency; settings writes
- **Feature/AdminPresetControllerTest** — POST apply writes config, theme config, idempotency
- **HTTP/InstallerApiControllerTest** — API endpoints

---

## Next Phases

| Phase | Package | Scope |
|-------|---------|-------|
| 2 | PackageManager | install/uninstall/upgrade/migrate/rollback lifecycle |
| 3 | Widget + Block | Widget system admin UI, CMS block builder |
| 4 | Menu | Dynamic menu editor per tenant |
| 5 | ThemeInstaller | CLI `satora:theme install <code>`, web installer |
| 6 | CSS Themes | Real CSS files per theme with component styles, mobile |
| 7 | Blade Templates | Real Blade views per template with sections |
| 8 | AI Architect | Event hooks, webhooks, API playground |
