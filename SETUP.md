# Satora — Bagisto Ecommerce Platform

> A heavily customized Laravel Bagisto 2.4.x platform with multi-language support, business presets,  
> separated theme/template architecture, and an enhanced SaaS-style installer.

---

## What Is Satora?

Satora extends the official [Bagisto](https://github.com/bagisto/bagisto) ecommerce framework with four major customisations, all built as clean, upgrade-safe packages inside `packages/Webkul/`.

| Layer | What It Does |
|-------|--------------|
| **Multi-language** | Full RTL+LTR support for Persian (fa), Turkish (tr), Arabic (ar), and English (en). Direction auto-switches per locale. |
| **Business Presets** | 9 one-click business types (Fashion, Electronics, Grocery, Beauty, Restaurant, Digital, Marketplace, Services, Custom). Each ships default categories, pages, navigation, theme, and template. |
| **Theme / Template** | Visual identity (Theme) is decoupled from page structure (Template). 3 themes × 4 templates = 12 combinations, all composable. |
| **Enhanced Installer** | API endpoints that feed a future Vue wizard step so users pick language → business type → theme → template during installation. |

---

## Project Structure (what we added)

```
packages/Webkul/
├── ThemeManager/                     ← NEW package
│   └── src/
│       ├── Config/thememanager.php
│       ├── Contracts/
│       │   ├── Theme.php             # Theme visual interface
│       │   ├── Template.php          # Template layout interface
│       │   ├── ThemeModel.php        # Concord contract
│       │   └── TemplateModel.php     # Concord contract
│       ├── Models/
│       │   ├── Theme.php + ThemeProxy.php
│       │   └── Template.php + TemplateProxy.php
│       ├── Repositories/
│       │   ├── ThemeRepository.php
│       │   └── TemplateRepository.php
│       ├── Themes/
│       │   ├── AbstractTheme.php
│       │   ├── MinimalLuxury.php     # Clean / neutral / serif
│       │   ├── ModernDark.php        # Dark-mode / tech / geometric
│       │   └── Colorful.php          # Vibrant / playful / rounded
│       ├── Templates/
│       │   ├── AbstractTemplate.php
│       │   ├── Fashion.php           # Hero + lookbook + Instagram gallery
│       │   ├── Electronics.php       # Specs + comparison tables + brand grid
│       │   ├── Grocery.php           # Category tiles + deals + quick-add
│       │   └── General.php           # All-purpose standard ecommerce
│       ├── Database/
│       │   ├── Migrations/           # satora_themes + satora_templates
│       │   └── Seeders/ThemeAndTemplateSeeder.php
│       ├── Providers/
│       │   ├── ThemeManagerServiceProvider.php
│       │   └── ModuleServiceProvider.php
│       └── Resources/lang/en/app.php
│
├── BusinessPreset/                   ← NEW package
│   └── src/
│       ├── Config/presets.php
│       ├── Contracts/
│       │   ├── BusinessPreset.php
│       │   └── BusinessPresetModel.php
│       ├── Models/
│       │   ├── BusinessPreset.php + BusinessPresetProxy.php
│       ├── Repositories/BusinessPresetRepository.php
│       ├── Presets/
│       │   ├── FashionPreset.php
│       │   ├── ElectronicsPreset.php
│       │   ├── GroceryPreset.php
│       │   ├── BeautyPreset.php
│       │   ├── RestaurantPreset.php
│       │   ├── DigitalPreset.php
│       │   ├── MarketplacePreset.php
│       │   ├── ServicesPreset.php
│       │   └── CustomPreset.php
│       ├── Helpers/
│       │   ├── PresetRegistry.php    # Singleton: all presets
│       │   └── PresetApplier.php     # Creates categories, pages, settings
│       ├── Http/Controllers/
│       │   └── InstallerApiController.php   # API for installer Vue
│       ├── Database/
│       │   ├── Migrations/           # satora_business_presets
│       │   └── Seeders/BusinessPresetSeeder.php
│       ├── Providers/
│       │   ├── BusinessPresetServiceProvider.php
│       │   └── ModuleServiceProvider.php
│       └── Resources/lang/
│           ├── en/app.php
│           ├── fa/app.php            # Persian translations
│           ├── ar/app.php            # Arabic translations
│           └── tr/app.php            # Turkish translations
│
└── Installer/                        ← EXISTING package (not modified)
    (API endpoints served from BusinessPreset, not core)
```

**Modules registered in:**
- `composer.json` — PSR-4 autoload entries
- `bootstrap/providers.php` — Service providers
- `config/concord.php` — Concord module proxies

---

## Architecture Decisions

### 1. Don't Hack Core
All custom code lives in new packages. The only core files touched are the three registration points above (standard for any new Bagisto package).

### 2. Concord Module Pattern
Every data entity follows Bagisto's three-component pattern:
```
Contract (interface) → Model (Eloquent) → Proxy (Konekt\Concord\ModelProxy)
```
This enables model substitution without touching core code.

### 3. Repository Pattern
All data access goes through repositories extending `Webkul\Core\Eloquent\Repository`. The `model()` method returns the Contract, not the concrete class.

### 4. Theme vs Template — Full Separation

| | Theme | Template |
|---|---|---|
| **Concern** | Visual identity | Page structure |
| **Defines** | Colors, typography, branding, CSS variables | Sections, navigation, homepage layout, default pages |
| **Example** | MinimalLuxury (Playfair Display + neutral palette) | Fashion (hero slider + lookbook + Instagram grid) |
| **Composability** | Any theme × any template that declares compatibility | `*` wildcard = universal |

A fashion store can use MinimalLuxury+Fashion, ModernDark+Fashion, or Colorful+Fashion — same layout, different visual skin.

### 5. Business Presets = One-Click Setup

Each preset is a PHP class that defines:
- **Recommended theme** — e.g. `minimal-luxury`
- **Recommended template** — e.g. `fashion`
- **Default categories** — hierarchical tree with names
- **Default CMS pages** — About Us, Size Guide, etc.
- **Recommended settings** — written to `core_config`
- **Navigation structure** — header links with positions
- **Localized names/descriptions** — in fa/tr/ar/en

The `PresetApplier` runs after installation to create everything automatically.

---

## Installer API

The following endpoints are available under `install/api/satora/`:

```
GET /presets                              # All 9 business presets (localized)
GET /themes                               # All active themes
GET /templates                            # All active templates
GET /themes/compatible/{templateCode}     # Themes compatible with a template
GET /templates/compatible/{themeCode}     # Templates compatible with a theme
```

These serve the installer's Vue component. The installer wizard flow:

```
Select Language → System Requirements → Database → Business Type → Theme & Template → Settings → Install
```

---

## How to Run the Project Locally

### Prerequisites

- PHP 8.3 or 8.4
- Composer 2.x
- MySQL 8.0
- Node.js 18+ with npm
- [Laravel Herd](https://herd.laravel.com) (Windows) or any local server

### Step 1 — Clone or Navigate

```bash
cd C:\Users\eh3un\Herd\satora
```

### Step 2 — Install PHP Dependencies

```bash
composer install
```

### Step 3 — Configure Environment

Copy `.env.example` to `.env` and fill in your database credentials:

```bash
cp .env.example .env
```

Edit `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=satora
DB_USERNAME=root
DB_PASSWORD=
```

### Step 4 — Create the Database

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS satora CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 5 — Run Bagisto Installation

```bash
php artisan bagisto:install
```

This will:
- Generate the application key
- Run all core Bagisto migrations
- Seed basic data (locales, currencies, admin user)
- Link the storage directory

Default admin credentials: `admin@example.com` / `admin123`

### Step 6 — Register Custom Packages

The packages are already registered in `composer.json`, `bootstrap/providers.php`, and `config/concord.php`. Run:

```bash
composer dump-autoload
php artisan optimize:clear
```

### Step 7 — Run Custom Migrations

Our packages add three database tables:

```bash
php artisan migrate
```

This creates:
- `satora_themes` — 3 themes
- `satora_templates` — 4 templates
- `satora_business_presets` — 9 business presets

### Step 8 — Seed Themes & Business Presets

On Windows (cmd), use escaped backslashes:

```bash
php artisan db:seed --class=Webkul\\\\ThemeManager\\\\Database\\\\Seeders\\\\ThemeAndTemplateSeeder
php artisan db:seed --class=Webkul\\\\BusinessPreset\\\\Database\\\\Seeders\\\\BusinessPresetSeeder
```

On macOS / Linux:

```bash
php artisan db:seed --class="Webkul\\ThemeManager\\Database\\Seeders\\ThemeAndTemplateSeeder"
php artisan db:seed --class="Webkul\\BusinessPreset\\Database\\Seeders\\BusinessPresetSeeder"
```

### Step 9 — Build Frontend Assets

```bash
# Admin panel
cd packages/Webkul/Admin && npm install && npm run build && cd ../../..

# Storefront / Shop
cd packages/Webkul/Shop && npm install && npm run build && cd ../../..

# Installer
cd packages/Webkul/Installer && npm install && npm run build && cd ../../..
```

### Step 10 — Start the Server

```bash
php artisan serve
```

Or with Herd (if the project is in your Herd directory, it's auto-served):

```
http://satora.test
```

### Step 11 — Access

| URL | Description |
|-----|-------------|
| `http://localhost:8000` | Storefront |
| `http://localhost:8000/admin` | Admin panel |
| `http://localhost:8000/install` | Installer wizard |

---

## Verifying the Custom Features

### Check Business Presets API

```bash
curl http://localhost:8000/install/api/satora/presets
```

Should return all 9 presets with localized names.

### Check Themes API

```bash
curl http://localhost:8000/install/api/satora/themes
curl http://localhost:8000/install/api/satora/templates
```

### Apply a Preset (via Tinker)

```php
php artisan tinker

$registry = app(\Webkul\BusinessPreset\Helpers\PresetRegistry::class);
$preset = $registry->get('fashion');

$applier = app(\Webkul\BusinessPreset\Helpers\PresetApplier::class);
$applier->apply($preset);
```

This creates all default categories, CMS pages, and applies settings for the Fashion preset.

### Check RTL Support

Set your locale to Persian or Arabic and verify:
- The `<html>` tag gets `dir="rtl"`
- All Tailwind `ltr:` / `rtl:` classes flip correctly
- Navigation, dropdowns, and forms respect the direction

---

## Remaining Tasks (Not Yet Complete)

1. **Extend the installer Vue component** — Add "Business Type" and "Theme & Template" steps to the existing wizard in `packages/Webkul/Installer/src/Resources/views/installer/index.blade.php`

2. **Wire preset into installer seeding** — Pass the selected preset code to `runSeeder` so `PresetApplier::apply()` runs automatically

3. **Theme preview images** — Create thumbnail images for each theme and template to show in the installer

4. **Code style** — Run `vendor/bin/pint` to align with Bagisto's Laravel code style

5. **Translation verification** — Run `php artisan bagisto:translations:check` to verify all 21 locale files are consistent

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12, PHP 8.4 |
| Ecommerce | Bagisto 2.4.x |
| Database | MySQL 8.0 |
| Admin Frontend | Vue 3, Tailwind CSS 3, Vite 5 |
| Storefront | Blade + Vue 3 components |
| Package System | Konekt Concord |
| Translations | astrotomic/laravel-translatable |
| Dev Tools | Laravel Boost, Pest, Pint, Playwright |

---

## Key Files Reference

| File | Purpose |
|------|---------|
| `packages/Webkul/ThemeManager/src/Themes/MinimalLuxury.php` | Theme color palette and typography |
| `packages/Webkul/ThemeManager/src/Templates/Fashion.php` | Template sections and navigation |
| `packages/Webkul/BusinessPreset/src/Presets/FashionPreset.php` | Full fashion store preset |
| `packages/Webkul/BusinessPreset/src/Helpers/PresetApplier.php` | Applies a preset (creates categories/pages) |
| `packages/Webkul/BusinessPreset/src/Http/Controllers/InstallerApiController.php` | API for the installer Vue |
| `composer.json` | PSR-4 namespaces for our packages |
| `bootstrap/providers.php` | Service provider registrations |
| `config/concord.php` | Concord module registrations |
