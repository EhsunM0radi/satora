# Satora — Bagisto Ecommerce Platform Implementation Plan

## Architecture Analysis Summary

**Bagisto 2.4.x + Laravel 12**
- 42 packages under `packages/Webkul/`
- Concord module system: Contract → Model → Proxy → Repository
- Existing locale system: 21 locales with `direction` field (ltr/rtl)
- Existing theme system: basic code/name/assets/views + Vite
- Installer: Vue 3 multi-step wizard (start→sysreq→env/db→ready→install→env-config→samples→admin→done)
- `laravel/boost` in dev deps, `astrotomic/laravel-translatable` for translations

## Package Map (new packages to create)

```
packages/Webkul/
├── Satora/                         # Main custom package
│   └── src/
│       ├── Config/satora.php       # Main config
│       ├── Providers/SatoraServiceProvider.php
│       └── ...
├── BusinessPreset/                 # Business presets (NEW)
│   └── src/
│       ├── Config/presets.php
│       ├── Contracts/BusinessPreset.php
│       ├── Models/BusinessPreset.php
│       ├── Models/BusinessPresetProxy.php
│       ├── Repositories/BusinessPresetRepository.php
│       ├── Presets/               # Concrete preset classes
│       │   ├── FashionPreset.php
│       │   ├── ElectronicsPreset.php
│       │   ├── GroceryPreset.php
│       │   ├── BeautyPreset.php
│       │   ├── RestaurantPreset.php
│       │   ├── DigitalProductsPreset.php
│       │   ├── MarketplacePreset.php
│       │   ├── ServicesPreset.php
│       │   └── CustomPreset.php
│       ├── Database/Migrations/
│       ├── Database/Seeders/
│       └── Providers/
│           ├── BusinessPresetServiceProvider.php
│           └── ModuleServiceProvider.php
├── ThemeManager/                   # Extended theme system (NEW)
│   └── src/
│       ├── Config/thememanager.php
│       ├── Contracts/
│       │   ├── ThemeContract.php
│       │   └── TemplateContract.php
│       ├── Models/
│       │   ├── Theme.php / ThemeProxy.php
│       │   └── Template.php / TemplateProxy.php
│       ├── Repositories/
│       │   ├── ThemeRepository.php
│       │   └── TemplateRepository.php
│       ├── Database/Migrations/
│       └── Providers/
│           ├── ThemeManagerServiceProvider.php
│           └── ModuleServiceProvider.php
│       └── Themes/               # Theme implementations
│           ├── MinimalLuxury/
│           ├── ModernDark/
│           ├── Colorful/
│           └── ...
│       └── Templates/            # Template implementations
│           ├── Fashion/
│           ├── Electronics/
│           ├── Grocery/
│           └── ...
└── Installer/                      # Extended Installer (MODIFY existing)
    └── src/
        ├── Http/Controllers/InstallerController.php (EXTEND)
        ├── Resources/views/installer/index.blade.php (MODIFY)
        └── Resources/assets/js/app.js (MODIFY)
```

## Implementation Phases

### Phase 1: Multi-Language Foundation
- Verify all existing translation completeness for fa/tr/ar/en
- Add RTL CSS support to themes
- Ensure direction auto-switching works across all views
- Add `direction` awareness to the Shop and Admin packages
- Add missing translation keys for installer steps we'll add

### Phase 2: Theme/Template Architecture (ThemeManager package)
- Create Theme and Template models with DB tables
- Build theme abstraction (colors, typography, branding, style)
- Build template abstraction (page layouts, sections, component arrangement)
- Create 3 initial themes: MinimalLuxury, ModernDark, Colorful
- Create 5 initial templates: Fashion, Electronics, Grocery, Digital, General
- Wire into existing Theme system via `config/themes.php`

### Phase 3: Business Presets System (BusinessPreset package)
- Create base BusinessPreset contract and abstract class
- Implement 8 concrete presets (Fashion, Electronics, Grocery, Beauty, Restaurant, Digital, Marketplace, Services, Custom)
- Each preset defines: default theme, template, categories, sample products, recommended settings, navigation
- Create preset seeder infrastructure
- Wire presets into installer

### Phase 4: Installer Wizard Extension
- Add "Business Type" step after language selection
- Add "Theme & Template" step after business type
- Show theme previews with thumbnails
- Show template previews with layout diagrams
- Auto-configure post-installation based on selections
- Add new translation keys across all 21 locale files

### Phase 5: Integration & Polish
- End-to-end flow testing
- Translation completeness check
- RTL testing for fa/ar
- Documentation

---

## Key Design Decisions

1. **Don't hack core** — all custom code goes in `packages/Webkul/BusinessPreset/` and `packages/Webkul/ThemeManager/`
2. **Extend installer views** — we add new UI steps to the existing Vue wizard
3. **Presets are PHP classes** — each preset is a class implementing `BusinessPresetContract`
4. **Theme = visual, Template = layout** — clean separation, composable
5. **Concord pattern throughout** — Contract → Model → Proxy → Repository
6. **Installer stores selections** — in a JSON config that gets applied during seeding
