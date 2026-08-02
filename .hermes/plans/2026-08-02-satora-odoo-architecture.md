# Satora Commerce Platform — Complete Architecture Design
## "The Odoo of Ecommerce"

> **For Hermes:** This is a pure architecture design document. No code to implement.
> Use for reference when building individual packages, presets, themes, and modules.

**Goal:** Design Satora as a modular, multi-tenant SaaS commerce platform supporting 8+ business niches with 3 theme variants each, operating identically in Single Store and Marketplace modes.

**Architecture:** Bagisto 2.4.x core + Concord modules. Each niche is a BusinessPreset class defining its entire domain model. Each theme is a Theme + Template pair with full CSS variable files. Every feature is a self-contained package under `packages/Webkul/`.

**Current State (Aug 2, 2026):**
- Multi-tenant operational: Tenant, ThemeManager (3 themes, 4 templates), BusinessPreset (8 presets) submodules on GitHub
- Onboarding wizard: 6-step Blade wizard (Type → Info → Preset → Template → Theme → Done)
- Themes: MinimalLuxury, ModernDark, Colorful (code-based, colors+typography only — no actual CSS files)
- Templates: Fashion, Electronics, Grocery, General (section names + navigation names — no actual layout files)
- Presets: categories, default pages, navigation labels, recommended settings only
- **Gap:** Presets define WHAT but not HOW. No product attributes, inventory rules, checkout flows, reports, or filters defined per niche.
- **Gap:** Themes define colors/typography only — no actual CSS, no component styles, no mobile breakpoints, no page layouts.
- **Gap:** Templates define section names only — no Blade views, no actual homepage sections, no product page layout variations.

---

# Part 1: Business Niches

## Niche 1: Fashion

### Required Product Attributes
| Attribute | Type | Filterable | Comparable | Notes |
|-----------|------|------------|------------|-------|
| Size | Select (Dropdown) | Yes | No | S, M, L, XL, XXL, 0-24 numeric |
| Color | Select (Swatch) | Yes | No | Hex + name swatches |
| Material | Select | Yes | No | Cotton, Silk, Leather, Polyester, Wool, Linen |
| Brand | Select | Yes | No | Tenant-managed brand list |
| Season | Select | Yes | No | SS, AW, Resort, Year-Round |
| Fit | Select | No | No | Slim, Regular, Oversized, Petite, Tall |
| Gender | Select | Yes | No | Women, Men, Unisex, Kids |
| Care Instructions | Textarea | No | No | Wash/care info |
| SKU Suffix | Text (Variant) | No | No | Per variant: COLOR-SIZE |

### Required Product Types
1. **Simple** — Single item (t-shirt, jeans, bag)
2. **Configurable** — Size × Color variants (dress in Red-S, Blue-M, etc.)
3. **Bundle** — Outfit sets (dress + bag + shoes)
4. **Gift Card** — Store credit, digital or physical

### Required Inventory Features
- **Size-based stock** — Each variant tracks stock independently
- **Low-stock alerts** — Per size/color, notify admin at threshold
- **Pre-order mode** — For upcoming collections (SS26, AW26)
- **Back-in-stock notification** — Customer subscribes to specific variant restock
- **Size guide** — Interactive size chart per category (women's dresses vs men's shirts)
- **Stock sync** — Between warehouse and storefront, support multi-warehouse

### Required Checkout Features
- **Size recommendation tool** — Based on customer measurements or previous purchases
- **Virtual try-on integration** — API slot for AR try-on providers
- **Gift wrapping** — Optional checkbox with message field
- **Gift receipt** — Separate receipt without prices
- **Order tracking with carrier integration** — DHL, FedEx, UPS, local couriers
- **Split shipment** — Items from different warehouses ship separately

### Required CMS Blocks
- **Size Guide** — Interactive table with measurements per category
- **Lookbook** — Seasonal editorial photography with shoppable hot-spots
- **Sustainability Page** — Materials sourcing, ethical manufacturing
- **Style Guide** — How to wear/styling tips blog-style
- **New Collection Landing** — Full-page campaign with countdown timer

### Required Homepage Sections
1. **Hero slider** — Full-width campaign imagery, 3-5 slides
2. **New Arrivals grid** — 6-8 products, auto-populated from "new" tag
3. **Shop by Category** — Image tiles linking to Women/Men/Accessories/Shoes
4. **Trending Now** — Algorithm-driven by views/sales velocity
5. **Lookbook strip** — Horizontal scrollable editorial images with product pins
6. **Featured Collection** — Curated seasonal collection, editorial layout
7. **Testimonials** — Customer review carousel with photos
8. **Instagram Shop** — Embedded social feed with product tagging
9. **Newsletter CTA** — Email capture with discount incentive

### Required Search Filters
- **Category** — Tree with checkboxes (Women > Dresses > Evening)
- **Price** — Range slider
- **Size** — Multi-select checkboxes
- **Color** — Visual swatch grid
- **Brand** — A-Z list with checkboxes
- **Material** — Checkboxes
- **Season** — Checkboxes
- **Sale/New** — Toggle switches
- **Rating** — Star selector
- **Availability** — In Stock / Pre-Order / All

### Required Reports
1. **Size analytics** — Which sizes sell most per category (inventory planning)
2. **Color trend report** — Seasonal color performance
3. **Return reason analysis** — Size/fit vs quality vs changed mind
4. **Collection performance** — Revenue by season/collection line
5. **Customer size profile** — Per-customer purchased sizes (recommendation engine feed)
6. **Inventory aging** — Slow-moving SKUs by size
7. **Lookbook engagement** — Hot-spot click-through rates
8. **Brand performance** — Revenue, margin, returns by brand

### Required Customer Features
- **My Sizes** — Saved size profile (auto-fills size on product page)
- **Style Quiz** — Onboarding quiz to determine taste preferences
- **Wishlist with restock alerts** — Per-variant (not just per-product)
- **Personalized recommendations** — Based on style quiz + purchase history
- **Loyalty tier** — Fashion-specific: VIP early access to new collections
- **Virtual wardrobe** — Save purchased items, get outfit suggestions
- **Outfit builder** — Mix & match purchased items
- **Return portal** — Self-service return with reason selection

### Required Seller Features (Marketplace)
- **Brand verification** — Sellers submit brand authorization documents
- **Size chart templates** — Seller can create/store their own size charts per category
- **Lookbook submission** — Seller submits editorial content for marketplace lookbook
- **Authenticity guarantee** — Verified authentic badge for approved sellers
- **Seller analytics dashboard** — Sales by size, color, category
- **Commission tiers** — Per-category commission rates (luxury vs fast fashion)

### Required Admin Features
- **Size chart manager** — Global + per-category size charts, metric/imperial toggle
- **Collection manager** — Create seasonal collections, assign products, set launch dates
- **Lookbook builder** — Drag-drop editorial pages, tag products on images
- **Style guide CMS** — Blog-style editor for fashion content
- **Brand manager** — Add/edit brands, logos, descriptions
- **Trend report generator** — AI-assisted seasonal trend analysis
- **Runway calendar** — Internal calendar for collection launches and promotions

---

## Niche 2: Electronics

### Required Product Attributes
| Attribute | Type | Filterable | Comparable | Notes |
|-----------|------|------------|------------|-------|
| Brand | Select | Yes | No | Samsung, Apple, Sony, LG, etc. |
| Model | Text | Yes | No | Model number (iPhone 16 Pro, Galaxy S25) |
| Color | Select (Swatch) | Yes | No | Product color variants |
| Storage Capacity | Select | Yes | Yes | 128GB, 256GB, 512GB, 1TB |
| RAM | Select | Yes | Yes | 8GB, 16GB, 32GB |
| Screen Size | Select | Yes | Yes | In inches (6.1", 6.7", 13", 15") |
| Processor | Select | Yes | Yes | A17 Pro, Snapdragon 8 Gen 3, M3 |
| Battery | Text | No | Yes | mAh rating |
| Connectivity | Multi-select | Yes | No | WiFi 6E, Bluetooth 5.3, 5G, NFC |
| OS | Select | Yes | No | iOS 18, Android 15, Windows 11 |
| Warranty | Select | No | No | 1 Year, 2 Years, Extended |
| Condition | Select | Yes | No | New, Refurbished, Open Box |
| Ports | Multi-select | No | No | USB-C, HDMI, Thunderbolt, 3.5mm |
| Weight | Text | No | Yes | In grams |
| Dimensions | Text | No | No | H×W×D in mm |

### Required Product Types
1. **Simple** — Single SKU product (charger, cable, case)
2. **Configurable** — Storage × Color variants (iPhone in 128GB-Black, 256GB-White)
3. **Bundle** — Kit bundles (phone + case + screen protector + charger)
4. **Virtual** — Digital goods (software licenses, e-books)
5. **Subscription** — Extended warranty, insurance plans

### Required Inventory Features
- **Serial number tracking** — IMEI/SN per unit for high-value items
- **Warehouse bin location** — Shelf/rack tracking
- **Warranty activation** — Trigger warranty start on shipment date
- **Refurbished grading** — Grade A/B/C conditions with different pricing
- **Trade-in module** — Old device trade-in value calculator
- **Stock buffer** — Minimum stock for display/demo units

### Required Checkout Features
- **Warranty upsell** — Extended warranty offer at checkout
- **Insurance offer** — Device insurance add-on
- **Accessory recommendations** — "Customers also bought" cases/chargers
- **Trade-in integration** — Apply trade-in value at checkout
- **Financing options** — Installment payment integration (Klarna, Affirm)
- **Delivery installation** — White-glove TV setup service add-on
- **SIM card integration** — For phones, offer carrier plans at checkout

### Required CMS Blocks
- **Buying Guide** — How to choose a smartphone/laptop/TV
- **Comparison Tool** — Side-by-side product comparison (CPU, RAM, camera, etc.)
- **Tech Blog** — Reviews, unboxings, how-to guides
- **Trade-in Value Calculator** — Dynamic form to estimate device value
- **Service Center Locator** — Map with authorized repair centers

### Required Homepage Sections
1. **Hero banner with product spotlight** — Latest flagship launch
2. **Deal of the Day** — Countdown timer + discounted price
3. **Featured Categories** — Smartphones, Laptops, TVs, Audio, Gaming grid
4. **New Releases** — Latest product grid with "New" badge
5. **Best Sellers** — Top 10 by sales volume
6. **Comparison Spotlight** — "iPhone 16 vs Galaxy S25" CTA card
7. **Bundle Deals** — Curated bundles (gaming PC + monitor + keyboard)
8. **Trade-in Banner** — "Trade in your old device, get credit"
9. **Tech News feed** — Latest blog posts carousel
10. **Brand Shops** — Brand-dedicated landing tiles (Samsung Store, Apple Corner)

### Required Search Filters
- **Category** — Tree (Smartphones > Android, iOS)
- **Brand** — Multi-select with logos
- **Price** — Range slider
- **Storage** — Checkboxes (128GB, 256GB, 512GB, 1TB+)
- **Screen Size** — Range (phone: 5.5-6.9", laptop: 11-17")
- **RAM** — Checkboxes
- **Condition** — New / Refurbished / Open Box
- **Color** — Swatches
- **Rating** — Star selector
- **Features** — 5G, Waterproof, Wireless Charging, etc.
- **Availability** — In Stock / Pre-Order / Coming Soon

### Required Reports
1. **Sales by brand** — Revenue, units, margin per brand
2. **Warranty claims rate** — Claims per product category/brand
3. **Trade-in volume** — Devices traded in, average value
4. **Accessory attachment rate** — % orders with accessory add-ons
5. **Refurbished vs new mix** — Revenue split
6. **Product lifecycle** — Sales velocity from launch to EOL
7. **Comparison tool usage** — Which comparisons drive conversions
8. **Pre-order performance** — Pre-order to launch day conversion

### Required Customer Features
- **My Devices** — Registered device list with warranty expiry, support links
- **Product Comparison** — Persistent comparison list (compare up to 4)
- **Price Drop Alert** — Notification when watched product price drops
- **Tech Profile** — Saved preferences (preferred brands, categories)
- **Installment Calculator** — EMI calculator on product page
- **User Manual Library** — PDF downloads per purchased product
- **Service Booking** — Schedule repair/installation visit

### Required Seller Features (Marketplace)
- **Authorized reseller verification** — Brand authorization documents
- **Serial number registration** — Seller registers IMEI/SNs they sell
- **Refurbished certification** — Seller submits refurbishment process docs
- **Warranty management** — Seller-defined warranty terms per product
- **Price match guarantee** — Seller can opt into auto price-matching

### Required Admin Features
- **Product comparison manager** — Define comparable attributes per category
- **Brand store manager** — Create brand landing pages with custom banners
- **Trade-in program manager** — Define device models + trade-in values matrix
- **Warranty manager** — Define warranty tiers, terms, providers
- **Serial number tracker** — Inventory search by IMEI/SN
- **Tech spec importer** — API or CSV import for detailed specs
- **Unboxing/review campaign manager** — Send review units to influencers

---

## Niche 3: Grocery

### Required Product Attributes
| Attribute | Type | Filterable | Comparable | Notes |
|-----------|------|------------|------------|-------|
| Weight | Text | No | Yes | In grams or kg |
| Unit | Select | No | No | kg, g, L, ml, piece, pack |
| Organic | Boolean | Yes | No | Certified organic badge |
| Dietary Info | Multi-select | Yes | No | Vegan, Gluten-Free, Halal, Kosher, Dairy-Free |
| Nutritional Info | JSON | No | No | Calories, protein, fat, carbs per serving |
| Ingredients | Textarea | No | No | Full ingredient list |
| Allergens | Multi-select | Yes | No | Nuts, Dairy, Soy, Gluten, Eggs, Shellfish |
| Brand | Select | Yes | No | Nestle, Unilever, local brands |
| Country of Origin | Select | Yes | No | Product source country |
| Expiry Date | Date (per batch) | No | No | Best before date |
| Storage | Select | No | No | Ambient, Refrigerated, Frozen |
| Packaging | Select | Yes | No | Plastic-Free, Recyclable, Biodegradable |
| Alcohol % | Decimal | No | No | For alcoholic beverages |
| Spice Level | Select | Yes | No | Mild, Medium, Hot, Extra Hot |

### Required Product Types
1. **Simple** — Standard grocery item (milk, bread, rice)
2. **Weight-based** — Priced per kg (fresh produce, deli items)
3. **Bundle** — Meal kit, combo deal (pasta + sauce + cheese)
4. **Subscription** — Weekly grocery box, milk delivery
5. **Pre-order** — Seasonal items (Christmas hampers, Ramadan boxes)
6. **Digital Gift Card** — Store credit

### Required Inventory Features
- **Batch/lot tracking** — Track expiry per batch, not per SKU
- **FIFO/FEFO picking** — First Expired First Out warehouse logic
- **Perishable alerts** — Auto-flag items approaching expiry
- **Weight-based inventory** — Track kg not units for loose items
- **Cold chain tracking** — Temperature monitoring for refrigerated items
- **Dynamic pricing** — Auto-discount items near expiry
- **Substitution rules** — Define acceptable substitutions for out-of-stock items
- **Minimum order quantity** — Per product (buy min 500g)

### Required Checkout Features
- **Delivery slot booking** — Calendar with time windows (1hr/2hr/4hr)
- **Substitution preferences** — Customer chooses: "substitute with similar", "no substitution", "contact me"
- **Minimum order amount** — Configurable threshold per delivery zone
- **Delivery fee tiers** — Free above X, sliding scale below
- **Bag fee** — Optional eco bag charge
- **Tip driver** — Optional gratuity at checkout
- **Repeat order** — One-click reorder from previous basket
- **Shopping list import** — Paste text list to add to cart
- **Dietary filter** — Filter entire catalog by diet on checkout

### Required CMS Blocks
- **Recipe Blog** — Recipes using store products with "add to cart" buttons
- **Weekly Deals Flyer** — Digital version of supermarket flyer
- **Seasonal Produce Guide** — What's in season, storage tips
- **Dietary Guide** — Explaining organic, vegan, gluten-free labels
- **Supplier Stories** — Meet the farmers/producers

### Required Homepage Sections
1. **Delivery slot CTA** — "Book your slot" prominent banner with availability indicator
2. **Weekly Offers** — Scrolling strip of discounted products with % off badges
3. **Shop by Aisle** — Visual grid: Fruits & Veg, Dairy, Bakery, Meat, Frozen, Drinks
4. **Fresh Today** — Auto-populated by inventory arrival date
5. **Meal Deals** — Bundle offers (breakfast bundle, BBQ pack)
6. **Recipe Inspiration** — Cards with recipe → "buy ingredients" button
7. **Brand Spotlight** — Featured brand promotions
8. **New Products** — Recently added items
9. **Loyalty Points Banner** — "You have X points" + redemption suggestions

### Required Search Filters
- **Category** — Hierarchical with emoji icons
- **Brand** — Checkboxes
- **Dietary** — Toggle chips (Vegan, Gluten-Free, Halal, Organic)
- **Price** — Range slider
- **Weight/Unit** — Per kg toggle for comparable items
- **Country of Origin** — Checkboxes
- **Allergens** — Exclusion filters ("hide products with nuts")
- **Packaging** — Plastic-Free toggle
- **Deals** — On Sale / Multibuy / Clearance
- **Spice Level** — Select

### Required Reports
1. **Basket analysis** — What items are bought together (market basket analysis)
2. **Perishable waste** — Expired/discarded inventory value per category
3. **Slot utilization** — Delivery slot fill rate by day/hour
4. **Substitution rate** — How often items substituted, acceptance rate
5. **Recipe-to-cart conversion** — Recipes viewed → add-to-cart → purchase
6. **Seasonal demand forecasting** — Predict demand by week (holiday spikes)
7. **Supplier performance** — Fill rate, quality returns by supplier
8. **Loyalty redemption** — Points earned vs redeemed, top redemptions

### Required Customer Features
- **Shopping Lists** — Multiple named lists (Weekly Shop, Party, BBQ)
- **Past Purchases** — Quick reorder from order history
- **Dietary Profile** — Set dietary preferences, auto-filter catalog
- **Delivery Preferences** — Saved address + preferred time windows
- **Recipe Box** — Save recipes, one-click "add all to cart"
- **Substitution History** — View and manage substitution preferences
- **Family Accounts** — Shared shopping list with household members
- **Price Alert** — Notification when favorite product goes on offer
- **Meal Planner** — Weekly meal plan with ingredient aggregation

### Required Seller Features (Marketplace)
- **Vendor delivery slots** — Each seller manages their own delivery windows
- **Product sourcing info** — Seller declares origin, organic cert, supplier
- **Temperature-controlled delivery** — Flag for cold chain requirements
- **Shelf-life declaration** — Seller must provide expiry on listing
- **Quality score** — Customer ratings for freshness, accuracy, packaging

### Required Admin Features
- **Slot manager** — Define delivery zones, time windows, capacity, cut-off times
- **Expiry manager** — Dashboard of products approaching expiry with auto-discount rules
- **Recipe builder** — CMS to create recipes with linked products
- **Flyer builder** — Visual weekly ad builder (drag-drop products onto pages)
- **Supplier portal** — Supplier self-service: submit product info, certs, pricing
- **Cold chain monitor** — Temperature logs per delivery route
- **Waste dashboard** — Perishable waste tracking and reduction targets

---

## Niche 4: Beauty

### Required Product Attributes
| Attribute | Type | Filterable | Comparable | Notes |
|-----------|------|------------|------------|-------|
| Skin Type | Multi-select | Yes | No | Oily, Dry, Combination, Sensitive, Normal |
| Skin Concern | Multi-select | Yes | No | Acne, Aging, Hyperpigmentation, Redness, Dullness |
| Skin Tone | Select (Swatch) | No | No | Fair, Light, Medium, Tan, Deep (for foundations) |
| Finish | Select | Yes | No | Matte, Dewy, Natural, Satin, Glossy |
| Coverage | Select | Yes | No | Sheer, Light, Medium, Full |
| Formulation | Select | Yes | No | Liquid, Powder, Cream, Stick, Gel, Oil |
| Key Ingredients | Multi-select | Yes | No | Hyaluronic Acid, Retinol, Vitamin C, Niacinamide, SPF |
| Free From | Multi-select | Yes | No | Paraben-Free, Sulfate-Free, Cruelty-Free, Vegan, Fragrance-Free |
| Scent Family | Select | Yes | No | Floral, Woody, Citrus, Oriental, Fresh |
| Longevity | Select | No | No | Short (1-3h), Medium (4-6h), Long (8h+), All Day |
| Volume/Size | Select | No | No | Travel, Standard, Value, Professional |
| Expiry After Opening | Text | No | No | PAO symbol: 6M, 12M, 24M |
| SPF Level | Select | Yes | Yes | None, SPF15, SPF30, SPF50, SPF50+ |
| Shade Name | Text (Variant) | Yes | No | Per variant |

### Required Product Types
1. **Simple** — Single product (lipstick, nail polish, brush)
2. **Configurable** — Shade variants (foundation in 40 shades)
3. **Bundle** — Routine sets (cleanser + toner + moisturizer)
4. **Sample** — Trial size (free or paid mini)
5. **Subscription** — Monthly beauty box, replenishment subscription
6. **Gift Set** — Curated holiday/occasion sets
7. **Digital Gift Card** — Store credit

### Required Inventory Features
- **Batch tracking by expiry** — Beauty products have shelf life
- **Shade-level stock** — Track per shade variant
- **Sample inventory** — Separate from full-size, may be zero-price
- **Limited edition flag** — Auto-remove from catalog when depleted
- **Pre-order for upcoming launches** — Hype product launches
- **Tester stock** — In-store tester inventory (not sellable)

### Required Checkout Features
- **Shade finder tool** — Quiz-based shade matching before add-to-cart
- **Routine builder upsell** — "Complete your routine" cross-sell
- **Free samples** — Choose 2-3 samples at checkout (above order threshold)
- **Gift wrap + message** — Premium packaging for gifts
- **Subscription option** — "Subscribe & Save X%" toggle on product page
- **Loyalty points multiplier** — Double points on new launches
- **Virtual try-on** — AR lipstick/eyeshadow shade preview

### Required CMS Blocks
- **Ingredient Encyclopedia** — Detailed ingredient database with benefits
- **Skin Quiz** — Interactive quiz → personalized routine recommendation
- **Beauty Blog** — Tutorials, trends, ingredient deep-dives
- **Before & After Gallery** — Customer transformation photos
- **Brand Story** — Per-brand origin stories and values
- **How to Apply** — Video/photo guides per product type
- **Shade Matching Guide** — Foundation/concealer shade finder

### Required Homepage Sections
1. **Hero with video loop** — Product application demo
2. **New Launches** — "Just Dropped" product strip
3. **Shop by Concern** — Visual tiles: Acne, Anti-Aging, Hydration, Brightening
4. **Best Sellers** — Top rated products carousel
5. **Routine Builder CTA** — "Build Your Perfect Routine" quiz card
6. **Brand Spotlight** — Featured brand with founder story
7. **Before & After** — Transformation carousel (user-generated)
8. **Trending Now** — Social media trending products (TikTok made me buy it)
9. **Gift Shop** — Curated gift sets by recipient/occasion
10. **Subscription Box Teaser** — Monthly box preview + subscribe CTA

### Required Search Filters
- **Category** — Skincare, Makeup, Haircare, Body, Fragrance, Tools
- **Skin Type** — Toggle chips
- **Skin Concern** — Toggle chips
- **Brand** — Grid with logos
- **Price** — Range slider
- **Formulation** — Checkboxes (Liquid, Powder, Cream, etc.)
- **Coverage** — Select (for makeup)
- **Finish** — Select (for makeup)
- **Free From** — Toggle chips (Vegan, Cruelty-Free, Paraben-Free, etc.)
- **Key Ingredient** — Checkboxes (Retinol, Vitamin C, Hyaluronic Acid, etc.)
- **SPF Level** — Checkboxes
- **Rating** — Star selector
- **Shade** — Swatch grid (when in foundation/concealer category)

### Required Reports
1. **Shade analytics** — Which foundation shades sell most by skin tone
2. **Ingredient trend** — Rising ingredient searches and purchases
3. **Sample-to-full-size conversion** — % who buy full size after sample
4. **Subscription churn** — Monthly box retention, replenishment renewal rate
5. **Before/After engagement** — UGC photo submission and conversion impact
6. **Routine completion rate** — Routines started vs completed purchases
7. **Gift sales seasonality** — Holiday spikes, gift set performance
8. **Influencer ROI** — Affiliate/creator campaign revenue attribution

### Required Customer Features
- **Beauty Profile** — Skin type, concerns, shade, allergies, preferences
- **Routine Tracker** — Morning/evening routine with timer and order
- **Shade Library** — Saved shades across brands (my shade in MAC, Fenty, NARS)
- **Wishlist with back-in-stock** — For limited edition and sold-out shades
- **Sample Request** — Queue samples to try next
- **Review with Photo** — Upload before/after photos with reviews
- **Ingredient Scanner** — Upload ingredient list to check for allergens/concerns
- **Subscription Manager** — Pause, skip, change box preferences
- **Loyalty Tier** — Beauty-specific: birthday gift, early access, exclusive shades

### Required Seller Features (Marketplace)
- **Brand authenticity verification** — Authorized retailer certification
- **Ingredient disclosure** — Mandatory full ingredient list on every listing
- **Shade consistency** — Seller shade names must match brand official names
- **Tester program** — Sellers can offer sample/tester SKUs
- **Cruelty-free badge** — Verified certification display
- **Seller beauty blog** — Sellers can publish tutorials using their products

### Required Admin Features
- **Shade database** — Global shade taxonomy across all brands
- **Ingredient database** — Encyclopedia with effects, conflicts, search
- **Routine builder engine** — Rule-based routine generation from quiz results
- **Sample program manager** — Define sample eligibility, thresholds, selection
- **Beauty quiz manager** — Build/maintain skin quiz questions and mappings
- **UGC moderation** — Review before/after photos before publish
- **Influencer dashboard** — Track affiliate links, codes, commission

---

## Niche 5: Digital Products

### Required Product Attributes
| Attribute | Type | Filterable | Comparable | Notes |
|-----------|------|------------|------------|-------|
| File Type | Select | Yes | No | PDF, EPUB, ZIP, MP3, MP4, Software, License Key |
| File Size | Text | No | No | In MB/GB |
| Version | Text | No | Yes | v1.0, v2.3, 2024 Edition |
| Platform | Multi-select | Yes | No | Windows, macOS, Linux, iOS, Android, Web |
| Language | Multi-select | Yes | No | Content language |
| License Type | Select | Yes | No | Personal, Commercial, Extended, GPL |
| Access Duration | Select | No | No | Lifetime, 1 Year, Per-Use |
| Author/Creator | Select | Yes | No | Creator name |
| Format | Select | Yes | No | For digital art: PNG, SVG, PSD, AI, BLEND |
| Resolution | Select | Yes | No | For assets: 1K, 2K, 4K, 8K |
| Software Version | Select | No | No | Compatible software: Photoshop CC 2024, etc. |

### Required Product Types
1. **Downloadable** — Single file or ZIP package
2. **License Key** — Software serial number, activation code
3. **Streaming** — Video/audio accessed via streaming (no download)
4. **Online Course** — Multi-lesson structured course with progress tracking
5. **Subscription** — Monthly access to asset library, software, membership
6. **Bundle** — Multiple digital products bundled
7. **Pay What You Want** — Flexible pricing

### Required Inventory Features
- **License key pool** — Auto-assign from pool of keys, track remaining
- **Download limit** — Max downloads per purchase (default 5, configurable)
- **Download expiry** — Link valid for X days after purchase
- **Concurrent download limit** — Prevent sharing (1 active download at a time)
- **Version management** — Customers get free updates within same major version
- **Watermarking** — Optional auto-watermark for preview images
- **DRM integration** — Optional DRM wrapper for video/PDF
- **Unlimited stock** — Digital products have no physical stock limit

### Required Checkout Features
- **Instant delivery** — Download link immediately after payment
- **License key display** — Key shown on order confirmation + email
- **Zero shipping** — No shipping step, no address required (or optional for invoices)
- **Tax handling** — Digital goods tax (EU VAT MOSS, US state taxes)
- **Gift purchase** — Buy for someone else, email delivery to recipient
- **Bundle pricing** — Dynamic discount for buying multiple products
- **Coupon code** — Specialized for digital: % off, fixed amount, free upgrade
- **Pre-order with early access** — Buy now, get beta access, full version on release

### Required CMS Blocks
- **Documentation** — Per-product user guides and docs
- **Changelog** — Version history per product
- **Demo/Preview** — Interactive preview, sample download
- **Creator Profile** — Author bio, portfolio, other products
- **License Comparison** — Table comparing license tiers
- **FAQ** — Installation, compatibility, refund policy

### Required Homepage Sections
1. **Featured Product** — Large hero with demo embed
2. **New Releases** — Latest digital products grid
3. **Top Sellers** — Best selling by category
4. **Freebies** — Free downloads to capture emails
5. **Deals** — Time-limited discounts with countdown
6. **Categories** — Templates, Courses, Software, Plugins, Assets, eBooks, Music
7. **Creator Spotlight** — Featured creator/author interview
8. **Bundle Deals** — "Complete Creator Bundle" curated packs
9. **Newsletter with freebie** — "Subscribe and get a free template"

### Required Search Filters
- **Category** — Hierarchical product type tree
- **File Type** — Checkboxes
- **Platform** — Multi-select
- **License Type** — Checkboxes
- **Price** — Range + Free toggle
- **Language** — Checkboxes
- **Author** — Searchable select
- **Rating** — Star selector
- **Format** — Checkboxes (for design assets)
- **Last Updated** — Date range

### Required Reports
1. **License key utilization** — Keys issued vs remaining per product
2. **Download analytics** — Downloads per purchase, re-download rate
3. **Refund rate by product** — Digital refunds (higher risk category)
4. **Freebie conversion** — Free download → paid purchase conversion
5. **Bundle performance** — Bundle vs individual purchase revenue
6. **Changelog impact** — Sales before/after version update
7. **Creator revenue** — Revenue per creator/author
8. **Affiliate performance** — Affiliate-driven digital sales

### Required Customer Features
- **My Library** — Persistent access to all purchased digital products
- **Download Manager** — View history, re-download, see version updates
- **License Key Manager** — View all keys, copy, activate count
- **Course Progress** — For courses: track progress, resume, certificate
- **Review + Rating** — Rate and review purchased products
- **Wishlist** — Save for later, get notified on sale
- **Update Notifications** — Email when purchased product gets updated
- **Creator Follow** — Follow creators, get new release notifications

### Required Seller Features (Marketplace)
- **Creator profile** — Bio, portfolio, social links, follower count
- **License key upload** — Bulk key upload, auto-assign on purchase
- **Version management** — Upload new versions, notify buyers
- **Preview/Demo tools** — Embed video, interactive preview, sample files
- **Payout method** — Digital-specific: instant payouts, threshold-based
- **Affiliate program** — Creator-defined commission for their products

### Required Admin Features
- **License key manager** — Global pool management, fraud detection
- **Digital delivery CDN** — Configure CDN for fast global delivery
- **Tax engine** — EU VAT MOSS + US state digital tax rules
- **DMCA/Copyright** — Takedown request handling
- **Content review** — Moderation queue for new digital products
- **Download analytics** — Abuse detection (excessive re-downloads)
- **Version diff** — Compare versions, require changelog on upload

---

## Niche 6: Furniture

### Required Product Attributes
| Attribute | Type | Filterable | Comparable | Notes |
|-----------|------|------------|------------|-------|
| Material | Multi-select | Yes | No | Wood, Metal, Glass, Fabric, Leather, Rattan, Marble |
| Color | Select (Swatch) | Yes | No | Color family with hex |
| Dimensions (H×W×D) | Text | No | Yes | In cm and inches |
| Weight | Text | No | No | In kg |
| Assembly Required | Boolean | Yes | No | Self-assembly vs pre-assembled |
| Style | Select | Yes | No | Modern, Scandinavian, Industrial, Traditional, Mid-Century, Minimalist |
| Room | Select | Yes | No | Living Room, Bedroom, Dining, Office, Outdoor, Kids |
| Seating Capacity | Number | No | Yes | For sofas/dining sets |
| Upholstery | Select | Yes | No | Fabric type (for sofas/chairs) |
| Mattress Type | Select | No | No | Memory Foam, Spring, Latex, Hybrid |
| Mattress Firmness | Select | Yes | No | Soft, Medium, Firm, Extra Firm |
| Bed Size | Select | Yes | No | Single, Double, Queen, King, Super King |
| Warranty | Select | No | No | Years (5, 10, 15, Lifetime) |
| Made In | Select | Yes | No | Country of manufacture |
| Eco Certifications | Multi-select | Yes | No | FSC, GREENGUARD, OEKO-TEX |

### Required Product Types
1. **Simple** — Single item (lamp, mirror, rug)
2. **Configurable** — Color × Size variants (sofa in Grey-3Seater, Beige-2Seater)
3. **Bundle** — Room sets (bedroom set: bed + nightstands + dresser)
4. **Custom/Made-to-Order** — Configurable dimensions, material, finish
5. **Pre-order** — Upcoming collection, made-to-order
6. **Digital Gift Card** — Store credit

### Required Inventory Features
- **Bulky item tracking** — Special handling, large-item warehouse zones
- **Floor model inventory** — Showroom stock separate from sellable
- **Made-to-order queue** — Production slots, estimated completion date
- **Raw material tracking** — For custom orders (fabric yardage, wood stock)
- **Multi-warehouse routing** — Route order to nearest warehouse with stock
- **Assembly service stock** — Track technician availability for assembly
- **Discontinued flag** — Floor model clearance, last pieces

### Required Checkout Features
- **Delivery type selection** — Standard (curbside), White Glove (in-room + assembly), Pickup
- **Room of choice delivery** — Select which room item goes to
- **Assembly service add-on** — Per-item assembly booking
- **Old furniture removal** — Haul-away service add-on
- **Delivery date picker** — Calendar with availability by zip code
- **Split delivery** — In-stock items ship now, backordered later
- **Financing (larger purchases)** — 0% installment plans
- **Trade program** — Interior designer/trade discount at checkout
- **Measurement guide popup** — "Will it fit?" door/elevator check
- **3D room preview** — AR view in your space before checkout

### Required CMS Blocks
- **Room Ideas** — Inspirational room galleries by style
- **Buying Guide** — Sofa buying guide, mattress guide, dining table sizing
- **Material Guide** — Wood types, fabric durability, leather grades
- **Care Guide** — How to maintain wood, clean upholstery, etc.
- **Assembly Instructions** — Video and PDF per product
- **Trade Program** — Information for interior designers
- **Showroom Locator** — Physical store locations with hours

### Required Homepage Sections
1. **Hero carousel** — Room scene photography, full-width
2. **Shop by Room** — Visual tiles: Living, Bedroom, Dining, Office, Outdoor
3. **New Collection** — Latest collection launch with editorial imagery
4. **Best Sellers** — Most popular items grid
5. **Style Edit** — Curated products by style (Scandi, Industrial, Modern)
6. **Room Inspiration** — Real customer room photos with "Shop the Look"
7. **Deals** — Sale/clearance section with original vs sale price
8. **Design Services CTA** — Free design consultation booking
9. **Trade Program Banner** — Interior designer signup
10. **Blog feed** — Latest design tips and trends

### Required Search Filters
- **Category** — Tree (Furniture > Living Room > Sofas > Sectionals)
- **Price** — Range slider
- **Room** — Toggle chips
- **Style** — Checkboxes
- **Material** — Checkboxes
- **Color** — Swatches
- **Assembly Required** — Toggle
- **Dimensions** — Width range, height range
- **Brand** — Checkboxes
- **Rating** — Star selector
- **Eco Certifications** — Toggle chips
- **Made In** — Checkboxes
- **In Stock** — Toggle (vs made-to-order vs pre-order)

### Required Reports
1. **Room category performance** — Revenue by room type
2. **Style trend** — Top selling styles by quarter
3. **Assembly vs pre-assembled mix** — Revenue, margin, return rate comparison
4. **White glove adoption** — % orders choosing white glove delivery
5. **Return rate by product type** — Sofas vs tables vs decor
6. **AR usage conversion** — AR views → add to cart → purchase
7. **Trade program revenue** — % revenue from designer accounts
8. **Custom order lead time** — Average production-to-delivery by product

### Required Customer Features
- **Room Planner** — 2D/3D room layout with products placed
- **AR View** — View furniture in your space using phone camera
- **My Rooms** — Saved room designs and wishlists per room
- **Trade Account** — Designer profile, tax exemption, trade pricing
- **Delivery Tracker** — Real-time delivery tracking with map
- **Assembly Booking** — Schedule/reschedule assembly appointment
- **Furniture Care** — Purchase-based care tips, order cleaning products
- **Design Consultation** — Book virtual/in-person design session

### Required Seller Features (Marketplace)
- **Maker/Artisan profile** — Workshop story, craftsmanship details
- **Custom order system** — Accept custom dimensions/materials from buyers
- **Lead time declaration** — Per-product production estimate
- **Delivery zone definition** — Seller sets where they deliver + rates
- **Assembly service** — Seller can offer assembly (own or outsourced)
- **Material certification upload** — FSC, organic, fair trade certs
- **White glove network** — Join marketplace white glove fulfillment

### Required Admin Features
- **Delivery zone manager** — Zip code-based delivery zones, rates, time slots
- **White glove scheduler** — Assembly technician calendar and routing
- **Trade program manager** — Designer applications, approvals, pricing tiers
- **Room planner engine** — Keep products and dimensions for room planner
- **AR model manager** — Upload/manage 3D models per product
- **Material library** — Central material/fabric/color database
- **Floor model manager** — Track floor models, schedule rotation

---

## Niche 7: Generic

### Design Philosophy
Generic is NOT "nothing defined." It is the **most flexible, everything-on** preset that can sell anything. It ships with ALL common ecommerce features enabled but NO niche-specific defaults — the admin configures everything.

### Required Product Attributes
- **All EAV attributes available** — No attributes pre-created. Admin creates whatever product attributes they need.
- **Default attribute types:** Text, Textarea, Select, Multi-select, Boolean, Date, Decimal, Integer

### Required Product Types
1. **Simple** — Always available
2. **Configurable** — Always available
3. **Bundle** — Always available
4. **Grouped** — Always available
5. **Virtual** — Always available
6. **Downloadable** — Always available

### Required Inventory Features
- **Standard stock tracking** — Per SKU quantity
- **Backorder** — Allow/disallow per product
- **Low stock alerts** — Configurable threshold
- **Multi-source** — Optional multi-warehouse

### Required Checkout Features
- **Standard shipping** — Flat rate, table rate, free shipping
- **Standard payment** — All payment gateways enabled
- **Guest checkout** — Always on
- **Tax calculation** — Configurable per zone
- **Coupon codes** — Standard cart rules

### Required CMS Blocks
- **About Us** — Store story
- **Contact** — Contact form + map
- **FAQ** — Common questions
- **Privacy Policy** — Auto-generated template
- **Terms & Conditions** — Auto-generated template
- **Shipping Policy** — Editable
- **Returns Policy** — Editable

### Required Homepage Sections
1. **Hero banner** — Single image with CTA
2. **Featured Products** — Admin-curated product selection
3. **Categories Grid** — Image tiles for main categories
4. **New Products** — Latest added
5. **On Sale** — Discounted items
6. **Newsletter** — Email capture

### Required Search Filters
- **Category** — Tree
- **Price** — Range slider
- **Rating** — Star selector
- **Availability** — In stock toggle
- **Any custom attribute** — Dynamic by category

### Required Reports
1. **Sales overview** — Revenue, orders, AOV
2. **Product performance** — Units sold, revenue per product
3. **Customer report** — New vs returning, LTV
4. **Abandoned cart** — Rate + value
5. **Tax report** — Collected by zone

### Required Customer/Seller/Admin Features
- Standard Bagisto features with no niche-specific additions.
- Admin can enable optional niche features through module toggles.

---

## Niche 8: Custom

### Design Philosophy
Custom is a **blank canvas** for developers/agencies. It inherits Generic's flexibility but adds an **SDK/API-first approach** with hooks, events, and extension points. The admin is expected to build their own preset by cherry-picking features from packages.

### Key Differentiators from Generic
- **Preset Builder UI** — Visual interface to cherry-pick features from other niches
- **Event hook system** — Every commerce event emits standardized hooks
- **Webhook manager** — Configure outbound webhooks for every event
- **API playground** — Built-in API explorer with tenant-scoped tokens
- **Custom module scaffold** — CLI to generate package skeleton for custom logic
- **No default CMS pages** — Admin creates everything from scratch
- **No default categories** — Admin builds their own taxonomy
- **No default attributes** — Empty EAV system ready for configuration

---

# Part 2: Theme System — 3 Themes Per Niche

Each niche gets 3 COMPLETELY DIFFERENT themes. A theme = Theme (visual layer) + Template (layout structure). Each feels like a completely different product.

## Niche 1: Fashion — 3 Themes

### Theme 1A: "Editorial"
- **Design Language:** Magazine-editorial, Vogue-inspired. Large photography, minimal text. Content IS the product.
- **Typography:** Display: "Cormorant Garamond" serif. Body: "Inter" sans-serif 400. Accent: "Montserrat" uppercase tracking.
- **Color Palette:** Primary: #0A0A0A (deep black), Secondary: #D4AF37 (gold accent), Background: #FAFAFA (off-white), Surface: #FFFFFF, Text: #1A1A1A, Border: #E5E5E5, Sale: #C41E3A
- **Component Style:** Cards: no borders, soft shadow. Buttons: outlined with gold hover fill. Banners: full-bleed photography, text overlay with gradient. Product labels: subtle dotted underline price. Badges: gold foil-stamped look.
- **Navigation Style:** Centered logo, left-aligned nav with wide spacing. Transparent on hero, solid white on scroll. Hamburger exposes full-height overlay with large serif typography.
- **Homepage Layout:** Full-screen hero slider (3 editorial shoots) → "The Edit" curated story blocks → Shop the Look horizontal scroll → New Arrivals asymmetric grid → Journal entries → Instagram wall footer.
- **Product Page:** Hero shot carousel (zoom on hover). Details in gold-accented sidebar. "Complete the Look" below fold. Size guide as expandable drawer.
- **Category Page:** Large category hero with editorial quote. Filter as left drawer. Products in 2-column on mobile, 3 on desktop. Hover zoom.
- **Checkout:** Minimal 1-page. Branded progress dots. Trust badges under CTA.
- **Mobile:** Hamburger fullscreen menu. Product images swipeable. Filters as bottom sheet. Sticky ATC bar. Smooth page transitions.

### Theme 1B: "Streetwear"
- **Design Language:** Bold, high-contrast, urban. Heavy typography, neon accents. Feels like a streetwear brand's own site — NOT a template.
- **Typography:** Display: "Bebas Neue" (all caps, heavy weight). Body: "Space Grotesk" sans-serif. Accent: "VT323" monospace for prices.
- **Color Palette:** Primary: #0D0D0D (pitch black), Secondary: #39FF14 (neon green), Accent: #FF2D55 (hot pink), Background: #F0F0F0 (concrete gray), Surface: #FFFFFF, Text: #0D0D0D, Border: #CCCCCC, Sale: #FF2D55
- **Component Style:** Cards: brutalist — thick black borders, no radius. Buttons: chunky pill shape, black with neon hover. Product badges: diagonal corner ribbons. Price display: large monospace font. Tags: spray-paint aesthetic badges.
- **Navigation Style:** Left-aligned logo, bold uppercase nav links, oversized cart icon with counter badge. Search bar integrated as expandable input. Mobile: slide-from-left drawer with bold typography.
- **Homepage Layout:** Video hero loop (runway/lifestyle) → "Drops" countdown timer for limited releases → Category grid (3-column exaggerated) → Best Sellers ticker strip → Lookbook video → Newsletter with "Early Access" hook.
- **Product Page:** Split layout: 60% image, 40% info. Product name in heavy uppercase. "Limited Release" urgency badge. Size selector as pill buttons. "Notify Me" for sold-out sizes.
- **Category Page:** No hero — just products. Masonry grid. Quick-add button on hover. Filter as top bar chips.
- **Checkout:** Dark themed. Progress bar at top. Collapsible sections. Express checkout buttons prominent (Apple Pay, Google Pay).
- **Mobile:** Bottom tab navigation (Shop, Search, Cart, Account). Products in 2-column dense grid. Swipe actions on cart items. Full-width product images.

### Theme 1C: "Romantic"
- **Design Language:** Soft, feminine, boutique feel. Pastels, cursive accents, rounded shapes. Feels like a small luxury boutique.
- **Typography:** Display: "Playfair Display" italic, Heading: "Lora" serif, Body: "Nunito" rounded sans-serif, Accent: "Dancing Script" for special labels.
- **Color Palette:** Primary: #4A3344 (mauve), Secondary: #D4A5A5 (dusty rose), Accent: #E8C7C7 (blush), Background: #FFF5F7 (warm white), Surface: #FFFFFF, Text: #3D2C33, Border: #E8D5D5, Sale: #C97B7B
- **Component Style:** Cards: soft shadow, 12px border radius. Buttons: fully rounded, gradient fill. Product tiles: rounded images with soft zoom. Category tiles: circular images. Badges: pastel pills with cursive text. Dividers: thin ornamental line.
- **Navigation Style:** Centered logo, minimal nav (4-5 links), elegant thin line separator. Search icon only (no bar). Cart with heart animation. Mobile: center-aligned, soft sliding panels.
- **Homepage Layout:** Soft hero with floating text overlay → "New This Week" → Category circles (4-6) → "The Look" editorial → Testimonials with florals → Boutique Story → Instagram grid with pink overlay → "Join the List" with floral border.
- **Product Page:** Centered layout. Product name in italic serif. Price in dusty rose. "Only X left" soft urgency. Size selector as fabric-swatch-style buttons. Complete the look as soft suggestions.
- **Category Page:** Banner with watercolor wash. Filter as gentle top accordion. Products in 3-column with generous spacing. "Add to Wishlist" heart icon prominent.
- **Checkout:** Wrapped in soft container. Gift options prominent (wrap, message). Flowers/illustration accents. Return policy reassuring copy.
- **Mobile:** Airy spacing. Soft-touch interactions. Product images fill width with gentle shadow. Category as scrollable pill list. Floating wislist button.

---

## Niche 2: Electronics — 3 Themes

### Theme 2A: "Tech Hub"
- **Design Language:** Futuristic, dark, neon-accented. Cyberpunk-lite. Feels like a premium tech retailer.
- **Typography:** Display: "Orbitron" (geometric, techy), Heading: "Rajdhani" semi-bold, Body: "Inter" 400, Mono: "JetBrains Mono"
- **Color Palette:** Primary: #0A0E27 (deep space blue), Secondary: #00F0FF (cyan neon), Accent: #7B2FFF (purple), Background: #0D1117 (GitHub dark), Surface: #161B22, Text: #E6EDF3, Border: #30363D, Success: #3FB950, Price: #00F0FF
- **Component Style:** Cards: glassmorphism — semi-transparent bg with backdrop blur, 1px glowing border. Buttons: glowing neon outline, fill on hover. Product badges: angular, geometric. Specs display: table with mono font. Comparison: horizontal slider with spec bars.
- **Navigation Style:** Sticky dark header with subtle blur. Mega-dropdown for categories (columns with icons). Search bar with glowing focus state. Mobile: bottom sheet with glassmorphism.
- **Homepage Layout:** Animated particle hero + featured product → "Hot Deals" with countdown timers → Category grid (icon + gradient tiles) → Comparison spotlight carousel → New Releases → Tech Blog feed → Brand stores grid → Newsletter with neon CTA.
- **Product Page:** Split: gallery + sticky info. Specs in expandable sections with icons. Comparison table at bottom. Accessories cross-sell strip. Price history graph (was/is).
- **Category Page:** Left sidebar filters with live count. Products in 4-column grid. Quick-view modal with glass panel. Sort by: Featured, Price, Rating, Newest.
- **Checkout:** Dark minimal. Stepper with glowy active step. Order summary sticky in sidebar. Payment icons glow on selection.
- **Mobile:** Bottom nav with icon labels. Products in 2-column. Filters as full-screen modal. Product gallery: horizontal swipe with dot indicators. Gesture-based navigation.

### Theme 2B: "Clean Tech"
- **Design Language:** Apple-inspired minimalism. White space, precision typography, product-focused. Feels premium, considered, calm.
- **Typography:** Display: "SF Pro Display" (system font), Heading: "Inter" 500, Body: "Inter" 400. All clean, neutral.
- **Color Palette:** Primary: #1D1D1F (Apple black), Secondary: #0071E3 (Apple blue), Background: #FFFFFF, Surface: #F5F5F7, Text: #1D1D1F, Text Secondary: #86868B, Border: #D2D2D7
- **Component Style:** Cards: clean white, no borders, subtle shadow on hover only. Buttons: pill shape, solid blue or outlined. Product tiles: image-only on white, name below in light gray. Specs: inline clean list with dots. No badges (text labels only).
- **Navigation Style:** Top bar, centered nav links (8px spacing), logo left, icons right. Glass-blur sticky header. No mega menu — simple dropdowns. Mobile: bottom sheet, elegant and sparse.
- **Homepage Layout:** Large product hero with ambient background → "New" ribbon → Category strip (simple icons) → Featured product deep-dive section → Best Sellers → "Why Buy From Us" value props → Trade-in promo → Footer with clean sitemap.
- **Product Page:** Sticky gallery (left 60%), scrollable info (right 40%). Specs in clean 2-column grid. Configurable options as rectangular selectors. Delivery estimate calc. Accessories as strip. Reviews at bottom in clean cards.
- **Category Page:** No left sidebar. Top filter bar with pill selectors. 3-column grid. Products: image, name, price only. Hover reveals quick-actions.
- **Checkout:** Two-column: form left, summary right. Progress bar thin line. Express checkout (Apple Pay primary). Clean error states.
- **Mobile:** Full-width products, single column. Filter as top scrollable pills. Large touch targets. Product page: image carousel full-width then info scrolls.

### Theme 2C: "Gamer Zone"
- **Design Language:** RGB gaming aesthetic. Dark, vibrant, aggressive. Feels like a gaming peripheral/laptop store.
- **Typography:** Display: "Russo One" (heavy, impactful), Heading: "Rajdhani" 700, Body: "Titillium Web" 400, Price: "Share Tech Mono"
- **Color Palette:** Primary: #0F0F1A (deep void), Secondary: #FF0055 (hot red-pink), Accent: #00FF88 (acid green), Background: #1A1A2E, Surface: #16213E, Text: #E0E0E0, Border: #0F3460, RGB: rotating gradient #FF0055→#7000FF→#00FF88
- **Component Style:** Cards: dark with angular borders, RGB glow on hover. Buttons: sharp corners, gradient fill, glow effect. Badges: angular, neon-bordered. Specs: table with glowing headers. Price: large mono with pulsing glow.
- **Navigation Style:** Dark header with RGB underline on active. Mega menu with game genre icons. Cart with pulsing RGB ring. Mobile: full-screen menu with animated background.
- **Homepage Layout:** Animated RGB particle hero → "Flash Sale" with animated fire timer → Genre categories (FPS, RPG, Racing etc.) → Gaming PC builder CTA → Top Streamer Gear (influencer picks) → New Drops → Community highlights → "Join the Clan" newsletter.
- **Product Page:** Dark theme. 360-degree product viewer (if available). Specs as tech-spec card with RGB accents. "Also Bought" as horizontal scroller. Benchmarks section (FPS, temperature graphs). Reviews with "Verified Gamer" badge.
- **Category Page:** Dark sidebar with glowing filters. Products in 3-column with hover RGB effect. Quick-add flashes green.
- **Checkout:** Dark themed. Gaming-style progress bar (level up). Order summary in sidebar. "Power Up" CTA button.
- **Mobile:** Dark interface. Products 2-column tight. Filters full-screen with toggle switches. Product images pinch-zoom. Cart slide-up panel.

---

## Niche 3: Grocery — 3 Themes

### Theme 3A: "Fresh Market"
- **Design Language:** Farmers market aesthetic. Warm, natural, organic. Kraft paper textures, green accents, handwritten touches.
- **Typography:** Display: "Amatic SC" (handwritten feel), Heading: "Lora" serif, Body: "Nunito" 400, Price: "Nunito" 700
- **Color Palette:** Primary: #2D5A27 (forest green), Secondary: #8B4513 (kraft brown), Accent: #F4A460 (warm orange), Background: #FFFDF7 (cream paper), Surface: #FFFFFF, Text: #3E2723, Border: #D7CCC8, Sale: #E65100, Organic: #2E7D32
- **Component Style:** Cards: white with soft shadow, 8px radius, green top border for organic. Buttons: rounded, green fill, brown outline. Product badges: kraft paper style labels (Organic, Local, New). Price: large green numbers. Category tiles: circular photos with handwritten labels.
- **Navigation Style:** Top bar with green background, white text. Department dropdown with icons. Delivery slot indicator always visible. Mobile: bottom nav with 5 icons (Shop, Search, Lists, Cart, Account).
- **Homepage Layout:** Delivery slot booking bar (sticky top) → Weekly offers carousel → Shop by Department grid (6-8 icons) → Fresh Today (new arrivals) → Seasonal Picks → Recipe cards (3) with "Shop Ingredients" → Supplier stories → Loyalty points banner.
- **Product Page:** Large product image left, info right. Weight/unit selector. Nutritional panel accordion. Dietary badges row. "Frequently Bought Together" suggestions. Recipe ideas using this product.
- **Category Page:** Left sidebar with subcategories + filters. Products in 3-column. Quick-add quantity selector. Organic/badges visible on tile.
- **Checkout:** Delivery slot selection prominent (first step). Substitution preferences. Order summary with delivery fee calc. Eco bag option. Loyalty points redemption.
- **Mobile:** Bottom nav. Products 2-column. Filter as bottom sheet. Quantity +/- stepper on product tile. Swipe to add to cart.

### Theme 3B: "Modern Pantry"
- **Design Language:** Sleek, urban, premium grocery. Dark mode default. Feels like an upscale city grocery delivery app.
- **Typography:** Display: "DM Serif Display" for headings, Body: "DM Sans" 400, Price: "DM Sans" 600
- **Color Palette:** Primary: #1A1A2E (dark navy), Secondary: #E94560 (vibrant red), Background: #16213E (dark blue-gray), Surface: #0F3460, Text: #EEE, Border: #1A1A2E, Accent: #F5F5F5, Sale: #E94560, Organic: #00B894
- **Component Style:** Cards: dark with subtle gradient, 6px radius. Buttons: pill, solid red or outlined white. Category tiles: dark with colored icon marker. Product tiles: dark, clean, badge-as-dot. Price: white, bold.
- **Navigation Style:** Dark sidebar (persistent on desktop). Collapses to hamburger on mobile. Category tree with item counts. Search bar prominent in header. Cart with item count in red circle.
- **Homepage Layout:** Dark hero with "Order in 60 minutes" CTA → Category sidebar (always visible) + main content area → Quick Reorder section (past items) → Deals of the Week → Fresh arrivals → Meal Kit spotlight → "New & Notable".
- **Product Page:** Dark layout. Image left, info right. Macro nutrition bar chart. "Compare at" price strikethrough. Unit price always shown. "Add to List" button prominent.
- **Category Page:** Sidebar persistent. Products in 3-column grid. Quick-add +/- on tile. Sort by top bar. Infinite scroll (not pagination).
- **Checkout:** Two-column dark. Delivery slot as horizontal time picker. Express checkout (saved payment). Tip driver option.
- **Mobile:** Bottom tab bar. Products in single column with large images. Slide-up cart panel. Delivery slot as scrolling horizontal pills.

### Theme 3C: "Family Shopper"
- **Design Language:** Friendly, accessible, family-oriented. Large text, bright colors, clear CTAs. Feels like a family supermarket.
- **Typography:** Display: "Fredoka One" (rounded, friendly), Heading: "Nunito" 700, Body: "Nunito" 400, Price: "Nunito" 800
- **Color Palette:** Primary: #1B5E20 (trusted green), Secondary: #FF6F00 (warm amber), Accent: #4FC3F7 (sky blue), Background: #F1F8E9 (mint white), Surface: #FFFFFF, Text: #212121, Border: #C8E6C9, Sale: #D32F2F, Kids: #4FC3F7
- **Component Style:** Cards: white, generous padding, 16px round corners. Buttons: large, rounded, green with white text. Category tiles: bright colored circles with emoji icons. Product badges: colorful rounded pills. Price: large green or red. Quantity: big +/- buttons.
- **Navigation Style:** Full-width primary nav (green bar), secondary category nav (white). Search bar big and central. Store locator + delivery info in top bar. Mobile: hamburger menu with large touch targets.
- **Homepage Layout:** Delivery slot + postcode checker (prominent top) → Weekly Specials (flyer-style grid) → Shop by Aisle (emoji + color tiles) → Family Meal Deals (bundle cards) → New Products → Recipe of the Week (large card) → Loyalty Club CTA → "Saving You Money" value section.
- **Product Page:** Large, clear layout. Product name big. Weight/unit clearly stated. "Price per 100g" always shown. Dietary badges large and colorful. Add-to-list heart big and obvious. "Also in" (smaller/larger size options).
- **Category Page:** Top filters as scrolling pill chips. Products in 2-column on mobile, 4 on desktop. Large product images. Price bold. Quick-add quantity selector on tile.
- **Checkout:** Step-by-step progress bar. Delivery slot picker (grid of time slots). Substitution preference per item. Loyalty points redemption prominent. Family-friendly payment options.
- **Mobile:** Large touch targets everywhere. Bottom nav with labels. Product tiles single column. Cart accessible from bottom tab. Delivery slot visible at all times. One-tap reorder.

---

## Niche 4: Beauty — 3 Themes

### Theme 4A: "Luxury Beauty"
- **Design Language:** High-end cosmetic brand. Black + gold, marble textures, serif typography. Feels like Sephora or a luxury brand's own site.
- **Typography:** Display: "Cormorant Garamond" (elegant serif), Heading: "Montserrat" 300 (light), Body: "Inter" 400, Accent: "Playfair Display" italic
- **Color Palette:** Primary: #0A0A0A (true black), Secondary: #C9A96E (champagne gold), Background: #FCFCFC, Surface: #FFFFFF, Text: #1A1A1A, Border: #E8E8E8, Sale: #8B0000, Premium: #C9A96E
- **Component Style:** Cards: white, thin gold border on hover, soft shadow. Buttons: gold outline → gold fill on hover. Product tiles: image-dominant, brand name small, product name medium, price gold. Badges: gold foil style. Reviews: gold stars.
- **Navigation Style:** Centered logo, sparse nav (New, Skincare, Makeup, Fragrance, Gifts), search icon + user + cart right-aligned. Dropdowns with editorial imagery. Mobile: fullscreen overlay, elegant serif links.
- **Homepage Layout:** Full-screen editorial video hero → "New Arrivals" staggered grid → Brand Spotlight (large editorial) → Bestsellers → "The Routine" (step-by-step product flow) → Gift Shop → Beauty Journal → VIP Loyalty CTA.
- **Product Page:** Gallery left, info right. Shade selector as numbered swatches. "What It Is" + "How to Use" accordions. Ingredients panel. "Complete Your Routine" strip. Reviews with photo upload.
- **Category Page:** Hero with category description. Sidebar filters (skin type, concern, etc.). Products 3-column. Quick-view modal with shade selector.
- **Checkout:** Minimal, branded. Sample selection step. Gift wrap upgrade. Loyalty points summary. "You'll earn X points" messaging.
- **Mobile:** Bottom sheet filters. Product images swipeable. Shade selector as horizontal scroll. Quick-add from category. Large ATC button.

### Theme 4B: "Clean Beauty"
- **Design Language:** Minimal, airy, botanical-inspired. White space, sage greens, botanical line drawings. Feels like a clean beauty boutique.
- **Typography:** Display: "Josefin Sans" 300, Heading: "Josefin Sans" 400, Body: "Lato" 400, Accent: "Caveat" (handwritten)
- **Color Palette:** Primary: #4A7C59 (sage green), Secondary: #D4E6C3 (pale green), Accent: #F5E6CC (warm sand), Background: #FDFCF8 (warm white), Surface: #FFFFFF, Text: #2C3A2F, Border: #E8EDE4, Sale: #C4706A, Vegan: #4A7C59
- **Component Style:** Cards: white, 4px radius, subtle green shadow. Buttons: sage green, pill, soft. Product tiles: image on cream background, clean labels. Badges: soft green rounded pills. Ingredient highlights as icons. Reviews: clean, minimal.
- **Navigation Style:** Top bar, logo left, centered nav, icons right. Thin separator line. Dropdowns with photography. Mobile: slide-over drawer, botanical illustrations.
- **Homepage Layout:** Soft lifestyle hero → "Shop by Concern" (4 emotional tiles) → New In → Ingredient Spotlight (educational) → Routine Builder CTA → Bestsellers Clean → "Our Standards" trust section → Blog → Newsletter.
- **Product Page:** Airy layout, lots of white space. Product name in light serif. "Free From" badge row prominent. Ingredient deep-dive expandable. "Pairs Well With" suggestions. "Our Promise" footnote.
- **Category Page:** Filter sidebar with "Skin Type" + "Concern" prominent. Products 3-column, generous spacing. Badge-heavy tiles. Sort by "Best Match" default.
- **Checkout:** Calm, clean process. Eco packaging option. Carbon offset toggle. Sample choices. Soft reassurance copy.
- **Mobile:** Simple, airy. Products 2-column. Filters as drill-down pages. Large ingredient info accessible. Smooth transitions.

### Theme 4C: "K-Beauty"
- **Design Language:** Korean beauty inspired. Playful, colorful, cute. Pastels, animations, chibi illustrations. Feels like Olive Young or a K-beauty retailer.
- **Typography:** Display: "Gamja Flower" (Korean-style rounded), Heading: "Noto Sans KR" 500, Body: "Noto Sans" 400, Price: "Noto Sans" 700
- **Color Palette:** Primary: #FF6B8A (coral pink), Secondary: #7EC8E3 (sky blue), Accent: #FFE66D (sunny yellow), Background: #FFF5F7 (pink tint white), Surface: #FFFFFF, Text: #2D2D2D, Border: #FFD4DE, Sale: #FF4757, New: #FF6B8A
- **Component Style:** Cards: white, 12px round corners, colorful shadow. Buttons: gradient pink→coral, fully rounded. Product tiles: cute, badge-heavy (New, Hot, Sale, 1+1). Reviews: photo-heavy with emoji reactions. Category icons: illustrated characters.
- **Navigation Style:** Pink header with white text. Cute icon nav. Category mega-menu with illustrated icons. Cart with animated bounce. Mobile: colorful bottom nav with emoji labels.
- **Homepage Layout:** Animated character hero → Flash Deals (with cute countdown) → "Trending in Korea" → 1+1 Deals section → Brand Spotlight (Korean brands) → "10 Step Routine" educational → Best Reviewers → Live Commerce CTA → Member benefits.
- **Product Page:** Cute layout. Product name playful. "1+1" badge prominent. Before/After photos. Ingredient "good/bad" rating visual. Step usage guidance. Reviews with skin type tags.
- **Category Page:** Top playful filters. 3-column grid. Quick-view with large images. "Best" rank badges. Sort includes "Popular" (like-based).
- **Checkout:** Colorful, fun. Points/gifts animations. "Surprise gift" eligible messaging. Friendly error states.
- **Mobile:** Highly animated. Bottom tab bar with cute icons. Products 2-column dense. Cart with bounce animation. Emoji-heavy UI. Horizontal scroll categories.

---

## Niche 5: Digital Products — 3 Themes

### Theme 5A: "Creator Hub"
- **Design Language:** Modern creator economy aesthetic. Vibrant gradients, bold cards, social proof prominent. Feels like Gumroad or Creative Market.
- **Typography:** Display: "Clash Display" (modern, unique), Heading: "Plus Jakarta Sans" 600, Body: "Plus Jakarta Sans" 400, Price: "Plus Jakarta Sans" 700
- **Color Palette:** Primary: #6C5CE7 (creative purple), Secondary: #00CEC9 (teal), Accent: #FD79A8 (pink), Background: #F8F9FE, Surface: #FFFFFF, Text: #2D3436, Border: #DFE6E9, Sale: #E17055
- **Component Style:** Cards: white, 12px radius, colored top accent border. Buttons: gradient purple→pink, pill. Product tiles: mockup-in-device preview image. Creator badge on tile. Price large and bold. Ratings: emoji reactions.
- **Navigation Style:** Minimal top bar, search-centric. Logo left, search bar center (dominant), icons right. Category dropdown as 2-column grid. Mobile: bottom tab (Explore, Search, Library, Account).
- **Homepage Layout:** Gradient hero with search bar → Trending tags (animated pills) → Featured Creators carousel → "New & Noteworthy" grid → Category icons → Freebies section → Bundle Deals → Creator Spotlight interview → Newsletter with freebie.
- **Product Page:** Large preview/screenshots gallery left. Price, license, add-to-cart right. "Created by" author card prominent. Version + changelog accordion. "Also from this creator" strip. Review breakdown.
- **Category Page:** Sidebar with subcategories + file type filters. Products in 3-column. Sort: Best Selling, Newest, Top Rated, Price. Preview on hover.
- **Checkout:** Simple 1-page. No address needed (email only for delivery). License type selection if variable. Coupon code field. "Instant access" reassurance.
- **Mobile:** Search-focused home. Products 2-column. Filter bottom sheet. Large preview images. Quick "Buy Now" option.

### Theme 5B: "Dev Shop"
- **Design Language:** Developer-focused. Dark mode default, monospace accents, terminal aesthetic. Feels like a premium code marketplace.
- **Typography:** Display: "Fira Code" (mono), Heading: "Inter" 600, Body: "Inter" 400, Code: "JetBrains Mono"
- **Color Palette:** Primary: #011627 (night owl blue), Secondary: #82AAFF (syntax blue), Accent: #C792EA (syntax purple), Background: #011627, Surface: #1D3B53, Text: #D6DEEB, Border: #1D3B53, Price: #FFEB95, Sale: #FF5874
- **Component Style:** Cards: dark, 4px radius, 1px syntax-colored border. Buttons: outlined with syntax highlight. Code snippets in product cards. File type icons. Terminal-style badges ("v2.3.1"). Reviews: markdown support.
- **Navigation Style:** Dark sidebar (persistent). Logo top, nav tree (like IDE file explorer). Search in top bar. Icons: extensible panel. Mobile: hamburger → dark drawer.
- **Homepage Layout:** Terminal-style hero with typed animation → "Hot Right Now" trending → Categories as file-tree → Featured Creator (with GitHub stats) → New Releases → Free Tools → "Pro Plan" subscription CTA → Blog (tutorials).
- **Product Page:** Dark layout. Code preview with syntax highlighting embedded. "Try Demo" link prominent. Documentation tab. Version history timeline. License type code-style radio. Dependencies listed.
- **Category Page:** Sidebar + main grid. Products 3-column. Language/framework badges on tiles. Sort: Most Downloaded, Best Rated, Recently Updated.
- **Checkout:** Dark, minimal. GitHub/Google social login option. License key generation shown. "Access Your Download" immediate.
- **Mobile:** Bottom tab bar. Products single column. Code preview collapsed. Filters as search parameters.

### Theme 5C: "Learning Hub"
- **Design Language:** Educational, clean, structured. Card-heavy, progress tracking, certification-focused. Feels like an online learning platform.
- **Typography:** Display: "Merriweather" (academic serif), Heading: "Nunito Sans" 700, Body: "Nunito Sans" 400, Price: "Nunito Sans" 800
- **Color Palette:** Primary: #2C3E50 (navy), Secondary: #3498DB (learning blue), Accent: #F39C12 (gold star), Background: #F4F6F9, Surface: #FFFFFF, Text: #2C3E50, Border: #E0E6ED, Sale: #E74C3C, Completed: #27AE60
- **Component Style:** Cards: white, 8px radius, progress bar on course cards. Buttons: blue, rounded. Course tiles: thumbnail with duration badge + progress bar. Star ratings prominent. Instructor avatar + name on tile. "Certificate" badge.
- **Navigation Style:** Top bar, logo left, "Browse Courses" mega menu, search center, icons right. Category mega-menu with topic areas + levels. Mobile: bottom nav (Discover, My Learning, Wishlist, Account).
- **Homepage Layout:** Hero with search + "What do you want to learn?" → Top Categories (icon grid) → Featured Course (large card) → New Releases → Top Instructors → "Learning Paths" (bundled courses) → Student Success Stories → Free Courses → Pricing Plans CTA.
- **Product Page:** Thumbnail video preview. Course curriculum accordion (module list). Instructor bio card. "What You'll Learn" checklist. Reviews with "Was this helpful?" vote. Student count. Last updated date.
- **Category Page:** Filters: Topic, Level (Beginner/Intermediate/Advanced), Duration, Price (Free/Paid), Rating, Language. Products as course cards with thumbnails.
- **Checkout:** Simple. "Start Learning Now" CTA. No-shipping. Enrollment confirmation.
- **Mobile:** Course cards 1-column. Video preview prominent. Curriculum collapsible. Progress synced. Offline lesson access indicators.

---

## Niche 6: Furniture — 3 Themes

### Theme 6A: "Design Gallery"
- **Design Language:** High-end furniture gallery. Minimal, architectural, photography-driven. Feels like a design showroom or Design Within Reach.
- **Typography:** Display: "Bodoni Moda" (architectural serif), Heading: "Jost" 300 (light), Body: "Jost" 400, Price: "Jost" 500
- **Color Palette:** Primary: #1A1A1A (charcoal), Secondary: #B8860B (brass/gold), Background: #F7F5F2 (warm white), Surface: #FFFFFF, Text: #2D2D2D, Border: #E5E2DE, Sale: #A0522D
- **Component Style:** Cards: no borders, image-only until hover → overlays appear elegantly. Buttons: minimal — thin underline on hover. Product tiles: room-context photography. Dimensions shown as line drawing icon. Material swatches as subtle circles. Price: small, elegant.
- **Navigation Style:** Logo center-top, nav below (sparse: Furniture, Lighting, Decor, Outdoor, Trade). Search icon far right, cart right. Sticky on scroll with subtle background. Mobile: hamburger with elegant fullscreen overlay.
- **Homepage Layout:** Full-screen room photo hero (single image, no slider) → "New Collection" editorial → Shop by Room (large tiles) → Designer Picks → "The Making" craftsmanship story → Style Edit (by aesthetic) → Trade Program → Visit Showroom.
- **Product Page:** Large image (70% width), info slim right column. Product name in architectural serif. Dimensions + materials as clean table. "Available in" finish/material swatches. "Complete the Room" strip below. "Design Services" CTA.
- **Category Page:** No sidebar. Top bar: category name + description + sort. Products in 2-column large tiles. Each tile: room photo + product name + price small.
- **Checkout:** Minimal, calm. Delivery type selection (Standard, White Glove). Date picker calendar. Assembly + removal add-ons. Designer trade discount field.
- **Mobile:** Full-width room photos. Products 1-column large. AR view button prominent. Swatches as horizontal scroll. Cart accessible from icon.

### Theme 6B: "Scandi Living"
- **Design Language:** Scandinavian — light, airy, natural. Wood tones, pastels, clean lines. Feels like IKEA's premium cousin.
- **Typography:** Display: "Raleway" 300, Heading: "Raleway" 500, Body: "Open Sans" 400, Price: "Open Sans" 600
- **Color Palette:** Primary: #3E4A3B (forest green), Secondary: #D4A574 (light oak), Accent: #F4C9A0 (peach), Background: #FAF7F2 (cream), Surface: #FFFFFF, Text: #4A4A4A, Border: #E8E3D9, Sale: #C97B5B
- **Component Style:** Cards: white, 0 radius, thin separator lines. Buttons: flat, filled with muted green. Product tiles: clean product-cutout on white. Name + short description + price. Materials: wooden-toned icons. Measurements: simple vector line drawings.
- **Navigation Style:** Simple top bar. Logo left, categories as horizontal list, search + user + cart right. No dropdowns (flyout or mega). Section header pages for categories. Mobile: slide drawer.
- **Homepage Layout:** Lifestyle hero (bright room) → "New Arrivals" grid → Category tiles (clean, photo-dominant) → "Our Materials" sustainability → Room Inspiration gallery → "Affordable Design" value props → Store Locator → Newsletter.
- **Product Page:** Clean split: image (50%), info (50%). Product name in Raleway. Materials + dimensions in collapsible sections. "How It's Made" story block. Assembly instructions video thumbnail. "Pair It With" suggestions. AR view button.
- **Category Page:** Top: category description + lifestyle banner. Sidebar: Room, Price, Material, Color filters. 3-column clean product grid.
- **Checkout:** Bright, clean. Steps numbered clearly. Delivery options with icons. Assembly booking. Price breakdown clear.
- **Mobile:** Products 2-column, clean. Filter as drawer. AR button prominent. Cart slide-up.

### Theme 6C: "Industrial Loft"
- **Design Language:** Industrial, raw, urban. Exposed textures, dark metals, concrete grays. Feels like an urban furniture brand.
- **Typography:** Display: "Oswald" (condensed, bold), Heading: "Oswald" 400, Body: "Roboto" 400, Price: "Roboto Mono"
- **Color Palette:** Primary: #212121 (near-black), Secondary: #BC6C25 (copper), Accent: #606060 (concrete), Background: #F5F0EB (warm gray), Surface: #FFFFFF, Text: #1C1C1C, Border: #BDBDBD, Sale: #D84315
- **Component Style:** Cards: raw edges, 2px solid dark borders. Buttons: dark, sharp corners. Product tiles: product-on-gray-background. Materials: metal/wood texture icons. Price: bold mono. Badges: stamped metal aesthetic.
- **Navigation Style:** Dark header bar, white text. Logo left, nav links uppercase, condensed font. Cart with counter badge. Search as icon-triggered full bar. Mobile: dark drawer.
- **Homepage Layout:** Dark industrial hero (loft interior) → "Raw Materials" editorials → Category grid (bold, dark tiles) → Bestsellers (large, bold) → "The Workshop" behind-the-scenes → New Drops → Custom Orders CTA → Newsletter.
- **Product Page:** Dark accents in layout. Product image large, info in clean panel. Material/finish as real texture swatches. Dimensions as industrial blueprint graphic. "Customize This" CTA for configurable items. Assembly as "Build It" guide.
- **Category Page:** Sidebar with checkboxes (dark styled). Products 3-column. Hover: dark overlay with quick-actions.
- **Checkout:** Dark accented. Industrial icons for steps. White glove option prominent. Financing CTA for large purchases.
- **Mobile:** Dark interface. Products 2-column. Bold typography. Swipe actions. Bottom tab nav.

---

## Niche 7: Generic — 3 Themes

### Theme 7A: "Modern Store"
- **Design Language:** Clean, universal, modern ecommerce. Works for any product. Neutral, professional, trustworthy.
- **Typography:** System font stack (native performance). Headings: 600 weight, Body: 400.
- **Color Palette:** Configurable by tenant admin (live preview). Defaults: Primary: #2563EB (blue), Secondary: #10B981 (green), Background: #FFFFFF, Text: #111827.
- **Component Style:** Standard ecommerce patterns. Clean cards, rounded corners, clear CTAs.
- **Navigation Style:** Standard top bar. Category dropdowns. Search center. Cart right. Configurable by admin.
- **Homepage Layout:** Configurable section builder (drag-and-drop). Default: Hero → Categories → Featured → New → Newsletter.
- **Product Page:** Standard gallery + info layout. Configurable elements.
- **Category Page:** Sidebar + grid. Standard filters.
- **Checkout:** Standard multi-step. Configurable.
- **Mobile:** Responsive standard patterns.

### Theme 7B: "Bold Store"
- **Design Language:** Bold, colorful, high-energy. Large typography, vibrant palette.
- **Typography:** "Poppins" 700 headings, 400 body.
- **Color Palette:** Configurable. Defaults: Primary: #FF6B35 (orange), Secondary: #004E89 (navy), Accent: #F7C59F (peach).
- **Component Style:** Bold shadows, large border radius, large CTAs.
- **Navigation Style:** Full-width colored bar. Bold links.
- **Homepage Layout:** Configurable section builder. Default: Full-width hero video → Featured grid → Categories with oversized tiles → Testimonials → CTA.
- **Product Page:** Bold layout with large images.
- **Category Page:** Top filter bar. Bold product tiles.
- **Checkout:** Simplified, bold CTAs.
- **Mobile:** Bold touch targets, progressive disclosure.

### Theme 7C: "Minimal Store"
- **Design Language:** Utmost minimalism. White-dominant, thin typography, generous negative space.
- **Typography:** "Inter" 300 headings, 400 body.
- **Color Palette:** Configurable. Defaults: Primary: #000000, Background: #FFFFFF, Text: #333333.
- **Component Style:** No borders, minimal shadows, thin separators. Image-driven.
- **Navigation Style:** Ultra-minimal top bar. Sparse links. Search only icon.
- **Homepage Layout:** Configurable section builder. Default: Full-image hero → Categories in thin strip → Featured products with large images → Single testimonial → Footer.
- **Product Page:** Image dominant (80%). Info slim right column.
- **Category Page:** No sidebar. Top sort only. Products in large-image grid.
- **Checkout:** Minimal 1-page.
- **Mobile:** Ultra-clean. Large images, minimal chrome.

---

## Niche 8: Custom — 3 Themes

Theme 7A/B/C (Generic) serve Custom unless custom themes are installed. The Custom niche additionally supports:
- **Theme SDK** — Build-your-own theme via hook system
- **Child themes** — Inherit from any theme, override specific components
- **Headless mode** — API-only storefront (no theme needed)

---

For Generic and Custom niches, I'll skip the full 3-theme expansions above the base definitions since they are already partially covered.

---

# Part 3: Marketplace Compatibility

## Design Principle: Feature Parity

EVERY feature works identically in Single Store mode and Marketplace mode. The difference is WHO provides the feature, not whether the feature exists.

## Feature Mapping: Single Store vs Marketplace

| Feature | Single Store | Marketplace |
|---------|-------------|-------------|
| Product listing | Admin creates | Seller creates (with admin approval) |
| Inventory management | Admin manages all | Seller manages own (admin can override) |
| Order fulfillment | Single fulfillment | Per-seller fulfillment (split orders) |
| Shipping | Admin-defined zones/rates | Seller-defined zones/rates + marketplace defaults |
| Returns | Single return policy | Per-seller policies + marketplace baseline |
| Customer support | Store support | Seller support + marketplace mediation |
| Reviews | Product reviews | Product reviews + seller ratings |
| Categories | Admin-defined taxonomy | Marketplace taxonomy, sellers assign |
| CMS | Admin creates | Admin creates + seller pages (limited) |
| Pricing | Admin sets | Seller sets + admin commission overlay |
| Payouts | Direct to store | Marketplace holds → seller payout (net commission) |
| Analytics | Store-wide | Marketplace-wide + per-seller |
| Tax | Store-configured | Per-seller configuration + marketplace rules |
| Theme | Single theme | Unified marketplace theme (sellers can't theme) |
| Search | Store-wide | Marketplace-wide + per-seller filter |

## Key Architectural Differences

### 1. Order Splitting
In Marketplace mode, a single cart can contain items from multiple sellers. On checkout:
- A parent Order is created (order_id, customer_id, total)
- Per-seller SubOrders are created (suborder_id, order_id, seller_id, seller_total, commission)
- Each SubOrder has its own status lifecycle
- Customer sees ONE order with item-level seller attribution
- Seller only sees their SubOrders

### 2. Commission Engine
- Configurable per seller, per category, or global
- Supports: flat fee, percentage, tiered (volume-based), subscription-based
- Commission calculated on SubOrder creation
- Payout system: threshold-based, scheduled (weekly/monthly), or manual

### 3. Seller Dashboard
Mirrors admin dashboard but scoped to seller's products/orders only:
- Product CRUD (with admin approval queue)
- Order management (their SubOrders only)
- Inventory management
- Shipping management
- Analytics (seller-scoped)
- Payout history
- Store profile management

### 4. Admin Approval Layer
New products in Marketplace mode go through:
- Draft → Submitted for Review → Approved/Rejected → Published
- Admin can bypass for trusted sellers (auto-approve)
- Product edits can be configured to require re-approval or auto-publish

### 5. Search Scope
- Single Store: searches all products
- Marketplace: searches all approved products across all sellers
- Seller filter available as a facet
- Relevance scoring MAY demote low-rated sellers (configurable)

---

# Part 4: SaaS Compatibility — Tenant Isolation

## Isolation Model: Shared Database with tenant_id Scoping

All tenants share one database. Every row in every tenant-scoped table has a `tenant_id` foreign key. Global scopes filter queries automatically.

## Layer-by-Layer Isolation

### Migrations
- Tenant-aware tables include `$table->foreignId('tenant_id')->constrained()->cascadeOnDelete()`
- Central tables (tenants, tenant_user, packages, subscriptions) do NOT have tenant_id
- Migration strategy:
  - Core Bagisto tables: UNCHANGED (products, categories, orders, etc.)
  - Tenant scoping: Added via a `SatoraServiceProvider` that adds global scopes to core models
  - New Satora packages: Each package's migrations create tables with `tenant_id` baked in
  - Migration execution: `php artisan migrate` runs ALL migrations. Tenant-scoped tables are created once with all tenants sharing the schema.

**Strategy for existing Bagisto tables:**
Instead of adding `tenant_id` to core tables (breaking upgrades), we create a parallel scoping table:

```sql
-- core: bagisto products unchanged
-- satora: tenant_product_scopes
tenant_id | product_id | is_visible | custom_price
```

This keeps core untouched. Global scopes on core models join through the scoping table.

### Config
- Per-tenant config stored in `tenant_settings` table (key-value, JSON value)
- Loaded via `TenantConfig` service that merges with base config
- Cached per tenant in Redis with `tenant:{id}:config` prefix
- Admin UI for tenant-specific config (System > Configuration)
- Configuration inheritance: tenant settings → preset defaults → package defaults
- **NO `.env` per tenant.** All tenant config is DB-driven.

### Storage
- `storage/app/public/` with tenant subdirectories:
  ```
  storage/app/public/
    tenants/
      {tenant_id}/
        images/
        downloads/
        imports/
        exports/
        media/
  ```
- Laravel Filesystem: `'tenant'` disk scoped to `storage/app/public/tenants/{tenant_id}/`
- URL access: `https://{tenant-domain}/storage/tenants/{tenant_id}/...`
- Disk isolation via `TenantFilesystemAdapter` that resolves tenant from request
- Cleanup: when tenant deleted, their storage directory is purged

### Themes
- **Tenant-level theme selection:** Each tenant picks ONE theme/template
- **Inheritance:** Tenant theme inherits from the selected base theme
- **Customization:** Admin can override theme CSS variables via DB (tenant_settings)
- **No file-level customization:** Tenants can't modify Blade files (security)
- **Theme assets:** Compiled per theme (Vite), cached, served from `public/themes/{theme}/`
  - NOT per-tenant copies — shared across tenants using the same theme
- **Active theme resolution:** `SatoraTheme` facade resolves from `app('current_tenant')`

### Uploads
- All file uploads tagged with tenant_id (product images, category images, CMS images)
- Upload controller resolves tenant from auth/session and scopes storage
- Media library scoped per tenant (admin can only see their tenant's files)
- Image URLs include tenant context for CDN cache key separation

### Cache
- **Cache key prefix:** All tenant-aware cache keys prefixed with `tenant:{id}:`
- **Examples:**
  - `tenant:42:product:categories`
  - `tenant:42:cms:pages`
  - `tenant:42:config:general`
- **No cross-tenant cache pollution:** Cache tags include tenant prefix
- **Cache invalidation:** When tenant config changes, clear only `tenant:{id}:*` keys
- **Global cache:** Non-tenant data (package config, core Bagisto) uses unprefixed keys

### Queues
- **Queue isolation:** Jobs tagged with `tenant_id` in their payload
- **Horizon tags:** `tenant:{id}` for monitoring per-tenant queue health
- **Queue prioritization:** Premium tenants get higher priority (configurable)
- **Rate limiting:** Per-tenant rate limits on queue consumption
- **Failed jobs:** Stored with tenant_id for per-tenant failed job review
- **Job middleware:** `TenantJobMiddleware` auto-injects tenant_id from the job context

### Database Connections
- **Shared connection:** All tenants use the same MySQL connection
- **Global scopes:** `TenantScope` (implements `Scope`) auto-filters by `tenant_id`
- **Model boot:** In `SatoraServiceProvider::boot()`, apply scope to all tenant-scoped models
- **Tenant bypass:** Admin can "impersonate" a tenant, scope auto-switches
- **Super admin:** Global scope NOT applied — sees all data across all tenants

### Search (Elasticsearch)
- **Index naming:** `products_{tenant_id}`, `categories_{tenant_id}`, etc.
- **Alias per tenant:** `products` → `products_{tenant_id}` (resolved at query time)
- **No cross-tenant search leakage:** Query always scoped to tenant's index
- **Global search (super admin):** Separate super-admin index with all tenant data

### Scheduled Tasks / Cron
- Tenant-aware scheduled tasks receive tenant_id
- `php artisan satora:run-tenant-jobs` loops through active tenants and dispatches
- Each tenant's scheduled work runs in isolation

---

# Part 5: Package Architecture

## Package Inventory (16 Packages)

All new packages go under `packages/Webkul/{Name}/`. Existing packages (ThemeManager, BusinessPreset, Tenant) are enhanced.

### 1. ThemeManager (Enhance Existing)
**Purpose:** Visual theme system (covers Part 2 theme definitions)
**Path:** `packages/Webkul/ThemeManager/`
**Status:** EXISTS — needs expansion

**New additions:**
- CSS file compilation per theme (SCSS → CSS with CSS variables)
- Theme preview image manager
- Theme customizer API (tenant can adjust colors in admin)
- Per-theme Blade component overrides (theme can override `product-card.blade.php`)
- Theme asset pipeline (Vite per theme)
- Theme inheritance (child themes)
- Theme marketplace (install/activate from gallery)
- Headless theme mode (API-only, no Blade)
- Theme versioning + migration

### 2. IndustryModules (NEW)
**Purpose:** Per-niche feature modules (covers Part 1 niche-specific features)
**Path:** `packages/Webkul/IndustryModules/`
**Status:** NEW

**Sub-modules (each can be enabled/disabled per tenant):**
```
IndustryModules/src/Modules/
  Fashion/
    SizeChart/           # Interactive size guide
    Lookbook/            # Editorial image + product tagging
    CollectionManager/   # Seasonal collection CRUD
    StyleQuiz/           # Customer style preference quiz
    VirtualWardrobe/     # Customer wardrobe + outfit builder

  Electronics/
    ComparisonEngine/    # Side-by-side product comparison
    SerialTracker/       # IMEI/SN inventory tracking
    TradeInCalculator/   # Device trade-in valuation
    TechBlog/            # Reviews, unboxing, how-to CMS
    WarrantyManager/     # Extended warranty management

  Grocery/
    DeliverySlots/       # Time-slot booking + capacity
    FEFOEngine/          # First Expired First Out picking
    DynamicPricing/      # Near-expiry auto-discount
    RecipeBuilder/       # Recipes with "shop ingredients"
    SupplierPortal/      # Supplier self-service

  Beauty/
    ShadeFinder/         # Foundation/concealer shade matching
    IngredientEncyclopedia/ # Ingredient database + search
    RoutineBuilder/      # Personalized skincare routine
    BeforeAfterGallery/  # UGC photo moderation + display
    BeautySubscription/  # Monthly beauty box management

  Digital/
    LicenseKeyManager/   # Key pool, assignment, tracking
    DigitalDelivery/     # CDN delivery, watermarking, DRM
    VersionManager/      # Semantic versioning, changelog, updates
    CourseBuilder/       # Structured online course authoring
    CreatorPortal/       # Author/creator onboarding + analytics

  Furniture/
    RoomPlanner/         # 2D/3D room layout + product placement
    WhiteGloveManager/   # In-room delivery + assembly scheduling
    TradeProgram/        # Interior designer accounts + pricing
    ARViewer/            # Augmented reality product viewing
    CustomOrderEngine/   # Configurable dimensions/materials
```

Each sub-module is a Composer package with its own ServiceProvider. The IndustryModules package acts as the installer/registry.

### 3. AI (NEW)
**Purpose:** AI-powered features across the platform
**Path:** `packages/Webkul/AI/`
**Status:** NEW

**Modules:**
- `ProductDescriptionGenerator` — AI writes product descriptions from attributes
- `ImageTagging` — Auto-tag product images (color, object, style)
- `SearchSmart` — Semantic/vector search using embeddings
- `RecommendationEngine` — Collaborative + content-based recommendations
- `Chatbot` — Customer support chatbot with product knowledge
- `ContentGenerator` — SEO meta, blog posts, category descriptions
- `PricingOptimizer` — Dynamic pricing suggestions
- `FraudDetection` — Anomaly detection on orders
- `InventoryForecast` — Demand prediction for inventory planning
- `ReviewAnalyzer` — Sentiment analysis + summary generation
- `SizeRecommendationAI` — Predict customer size from measurements + history
- `VirtualTryOn` — AI-powered virtual try-on integration layer
- `VisualSearch` — Upload photo → find similar products

### 4. Marketplace (NEW)
**Purpose:** Multi-vendor marketplace engine
**Path:** `packages/Webkul/Marketplace/`
**Status:** NEW

**Components:**
- `Seller` model (Contract + Model + Proxy + Repository)
- `SellerOnboarding` — Application, verification, approval workflow
- `SellerDashboard` — Scoped admin panel for sellers
- `OrderSplitter` — Cart → Parent Order + per-seller SubOrders
- `CommissionEngine` — Configurable per seller/category commissions
- `PayoutManager` — Scheduled/manual seller payouts
- `SellerReview` — Rating + review for sellers
- `SellerStore` — Seller store page (within marketplace storefront)
- `SellerShipping` — Seller-defined shipping zones/rates
- `MarketplaceSearch` — Scoped search with seller facet
- `MarketplaceAdmin` — Admin moderation, approval, seller management

### 5. CMS (Enhance Existing)
**Purpose:** Content management — page builder, blocks, sections
**Path:** `packages/Webkul/CMS/` (enhance existing)
**Status:** EXISTS — needs expansion

**New additions:**
- **Page Builder** — Drag-and-drop block editor (hero, grid, carousel, CTA, video, etc.)
- **Section Templates** — Pre-built homepage sections per niche
- **Block Library** — Reusable CMS blocks (referenced from any page)
- **Dynamic Content** — Personalization rules (show X to returning customers)
- **Scheduling** — Publish/unpublish pages and blocks on schedule
- **A/B Testing** — Content variant testing
- **Multi-locale CMS** — Per-locale content with fallback
- **SEO Manager** — Per-page meta, schema markup, canonical URLs
- **Media Library** — Central asset management with tenant scoping

### 6. Builder (NEW)
**Purpose:** Visual store builder (WYSIWYG) for non-technical admins
**Path:** `packages/Webkul/Builder/`
**Status:** NEW

**Components:**
- **Homepage Builder** — Drag sections, reorder, configure per section
- **Product Page Builder** — Configure element order, add/remove sections
- **Category Page Builder** — Configure filter position, product grid, banner
- **Header/Footer Builder** — Configure navigation, logo, footer links
- **Color Customizer** — Live color picker overrides for theme variables
- **Font Customizer** — Select from approved fonts, live preview
- **Mobile Preview** — Responsive preview with device selector
- **Undo/Redo** — Edit history with version snapshots
- **Template Creator** — Save custom layout as reusable template
- **Export/Import** — Transfer builder config between tenants

### 7. Integrations (NEW)
**Purpose:** Third-party service integrations hub
**Path:** `packages/Webkul/Integrations/`
**Status:** NEW

**Integration categories:**
- **Shipping carriers:** DHL, FedEx, UPS, USPS, local carriers (API rate + label generation)
- **Payment gateways:** Stripe, PayPal, Razorpay, PayU, Square, local gateways
- **Marketing:** Mailchimp, Klaviyo, SendGrid, Twilio (SMS)
- **Analytics:** Google Analytics 4, Facebook Pixel, Hotjar, Mixpanel
- **CRM:** HubSpot, Salesforce, Zoho
- **Accounting:** QuickBooks, Xero, Zoho Books
- **Social:** Instagram Shop, Facebook Shop, TikTok Shop
- **AI services:** OpenAI, Google AI, Stability AI (for AI package)
- **CDN:** Cloudflare, BunnyCDN, AWS CloudFront
- **Tax:** TaxJar, Avalara, Vertex
- **Identity:** Google Login, Facebook Login, Apple Sign In
- **Notifications:** Firebase (push), OneSignal, Pusher

Each integration is a self-contained driver class implementing a common interface (e.g., `ShippingCarrierInterface`, `PaymentGatewayInterface`).

### 8. Reports (NEW)
**Purpose:** Advanced reporting and business intelligence
**Path:** `packages/Webkul/Reports/`
**Status:** NEW

**Report types (per niche, configurable):**
- Sales reports (daily, weekly, monthly, quarterly, annual)
- Product reports (best sellers, slow movers, margin analysis)
- Customer reports (RFM, cohort, LTV, churn)
- Order reports (AOV, conversion funnel, abandonment)
- Inventory reports (aging, turnover, forecasting)
- Tax reports (collected, payable)
- Marketing reports (campaign ROI, affiliate, coupon usage)
- Seller reports (marketplace: seller performance, commission)
- Niche-specific reports (from Part 1 per niche)

**Features:**
- Report builder (custom reports: select metrics + dimensions + filters)
- Scheduled reports (email delivery)
- Export (CSV, Excel, PDF)
- Dashboard widgets (embed report snippets on admin dashboard)
- Data warehouse export (push to external BI tools)

### 9. Analytics (NEW)
**Purpose:** Real-time analytics and event tracking
**Path:** `packages/Webkul/Analytics/`
**Status:** NEW

**Components:**
- **Event Tracker** — Capture all commerce events (view, add-to-cart, purchase, etc.)
- **Real-time Dashboard** — Live sales, visitors, conversion rate
- **Funnel Analysis** — Step-by-step conversion funnel (visit → view → cart → checkout → purchase)
- **Customer Journey** — Per-user event timeline
- **Heatmaps** — Click/scroll data collection (optional, privacy-conscious)
- **Attribution** — Multi-touch marketing attribution modeling
- **Cohort Analysis** — Behavioral cohort tracking
- **A/B Test Results** — Statistical significance calculator
- **Product Performance** — Views, conversions, review correlation
- **Search Analytics** — Search terms, zero-results, conversion per term

### 10. Marketing (Enhance Existing)
**Purpose:** Marketing automation and campaign management
**Path:** `packages/Webkul/Marketing/` (enhance existing)
**Status:** EXISTS — needs expansion

**New additions:**
- **Email Campaign Builder** — Drag-drop email designer, templates per niche
- **Automation Workflows** — Visual workflow builder (triggers → conditions → actions)
- **Segmentation Engine** — Dynamic customer segments (behavior, purchase history, demographics)
- **Abandoned Cart Recovery** — Multi-step email/SMS sequence
- **Welcome Series** — Post-signup nurture sequence
- **Win-back Campaigns** — Inactive customer re-engagement
- **Birthday/Anniversary** — Date-triggered offers
- **Product Recommendations in Email** — Personalized product feed
- **Push Notifications** — Web push + mobile push
- **SMS Marketing** — Twilio integration + campaign manager
- **Affiliate Program** — Referral tracking, commissions, payout
- **Coupon Manager** — Advanced: usage limits, customer-specific, stackable rules
- **Flash Sale Manager** — Time-limited sale with countdown

### 11. CRM (NEW)
**Purpose:** Customer relationship management
**Path:** `packages/Webkul/CRM/`
**Status:** NEW

**Components:**
- **Customer 360 View** — Unified profile (orders, tickets, reviews, activity, segments)
- **Customer Segments** — Dynamic groups based on rules (RFM, demographics, behavior)
- **Activity Timeline** — Every customer interaction logged
- **Notes & Tags** — Admin notes + custom tags on customers
- **Task Management** — Follow-up tasks, reminders for customer success team
- **Ticket System** — Customer support ticketing with status, priority, assignment
- **Live Chat** — Real-time chat widget (optional)
- **Customer Health Score** — Churn risk indicator
- **Email History** — All emails sent to customer with open/click tracking
- **GDPR Tools** — Data export, anonymization, consent management

### 12. Loyalty (NEW)
**Purpose:** Loyalty and rewards program
**Path:** `packages/Webkul/Loyalty/`
**Status:** NEW

**Components:**
- **Points Engine** — Earn points on purchase, review, referral, social share
- **Tier System** — Bronze/Silver/Gold/Platinum with escalating benefits
- **Reward Catalog** — Points redemption (discounts, free products, free shipping)
- **VIP Program** — Invite-only tier with exclusive perks
- **Gamification** — Badges, achievements, progress bars
- **Referral Program** — "Give X, Get Y" referral tracking
- **Birthday Rewards** — Auto-issued birthday coupon
- **Points Expiry** — Configurable expiry rules with reminder emails
- **Niche-specific rewards** — Fashion: early collection access, Beauty: birthday gift
- **Analytics** — Points earned/redeemed, tier distribution, program ROI

### 13. POS (NEW)
**Purpose:** Point of sale for physical retail
**Path:** `packages/Webkul/POS/`
**Status:** NEW

**Components:**
- **POS Interface** — Web-based register (works on tablet/desktop)
- **Product Search** — Barcode scan + quick search
- **Cart** — Quick-add, quantity adjust, discount, hold/recall
- **Payment** — Cash, card (Stripe Terminal), mobile wallet
- **Receipt** — Thermal printer support, email receipt option
- **Customer Lookup** — Phone/email search, create customer on the fly
- **Inventory Sync** — Real-time sync between online and POS stock
- **Multi-register** — Multiple POS terminals, shared inventory
- **Offline Mode** — Cache transactions, sync when back online
- **Staff Permissions** — Role-based access (cashier vs manager)
- **End of Day** — Cash reconciliation, shift reports
- **Returns/Exchanges** — In-store return processing
- **Loyalty Integration** — Points earn/redeem at POS

### 14. Subscriptions (NEW)
**Purpose:** Recurring billing and subscription management
**Path:** `packages/Webkul/Subscriptions/`
**Status:** NEW

**Components:**
- **Subscription Plans** — Define recurring products (weekly, monthly, annual)
- **Billing Engine** — Auto-charge via Stripe/PayPal, retry failed payments
- **Subscription Management** — Pause, resume, skip, cancel, change plan
- **Trial Periods** — Free trial with automatic conversion
- **Dunning Management** — Failed payment email sequence
- **Proration** — Mid-cycle plan changes with prorated charges
- **Subscription Box** — Curated box with preference-based personalization
- **Usage-based Billing** — Metered billing for API/digital products
- **Churn Analytics** — Cancellation reasons, churn prediction
- **Customer Portal** — Self-service subscription management

### 15. BusinessPreset (Enhance Existing)
**Purpose:** Niche presets — onboarding data + feature activation
**Path:** `packages/Webkul/BusinessPreset/`
**Status:** EXISTS — needs expansion

**Current state:** Defines categories, pages, navigation, recommended settings, recommended theme/template.
**Gap:** No product attributes, no product types, no feature toggles, no section definitions.

**New additions:**
- **Product attribute creation** — Each preset defines which attributes to auto-create
- **Product type registration** — Each preset defines which types to enable
- **Feature toggles** — Each preset enables/disables specific IndustryModule sub-modules
- **Homepage section defaults** — Each preset defines default section configuration
- **CMS block defaults** — Each preset creates its required CMS blocks
- **Email template defaults** — Each preset provides niche-appropriate email templates
- **Search filter configuration** — Each preset pre-configures filter facets
- **Report defaults** — Each preset enables relevant reports
- **Niche icons + preview images** — Marketing assets for preset selection UI

**Presets to keep:** Fashion, Electronics, Grocery, Beauty, Digital, Furniture, Generic, Custom, Marketplace
**Presets to remove:** Restaurant, Services, Diverse (these are NOT ecommerce niches)

### 16. Tenant (Enhance Existing)
**Purpose:** Multi-tenant core
**Path:** `packages/Webkul/Tenant/`
**Status:** EXISTS — needs expansion

**New additions:**
- `TenantScope` global scope (auto-filter models by tenant_id)
- `TenantConfig` service (DB-driven per-tenant config)
- `TenantFilesystem` adapter (scoped storage)
- `TenantCache` manager (prefixed cache keys)
- `TenantJobMiddleware` (tenant-scoped queue jobs)
- `SuperAdmin` panel (manage all tenants)
- `TenantImpersonation` (admin login as tenant)
- `Subscription Plans` for tenants (freemium, paid tiers)
- `TenantBilling` (tenant subscription invoice)
- `TenantUsageLimits` (products, storage, bandwidth caps)
- `TenantAnalytics` (aggregated across tenants for platform owner)

---

## Package Dependency Graph

```
                        ┌─────────────┐
                        │    Core     │ (Bagisto — NEVER modified)
                        └──────┬──────┘
                               │
              ┌────────────────┼────────────────┐
              ▼                ▼                 ▼
       ┌──────────┐    ┌─────────────┐    ┌────────────┐
       │  Tenant  │    │ IndustryModules│   │ThemeManager│
       └────┬─────┘    └──────┬──────┘    └─────┬──────┘
            │                 │                  │
            ▼                 ▼                  ▼
    ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
    │BusinessPreset│  │      AI      │  │    Builder   │
    └──────┬───────┘  └──────┬───────┘  └──────┬───────┘
           │                 │                  │
           └─────────┬───────┴──────────────────┘
                     │
         ┌───────────┼───────────┬──────────────┐
         ▼           ▼           ▼              ▼
   ┌──────────┐ ┌─────────┐ ┌────────┐  ┌─────────────┐
   │Marketplace│ │   CMS   │ │Reports │  │  Analytics  │
   └─────┬─────┘ └────┬────┘ └────┬───┘  └──────┬──────┘
         │            │           │               │
         └────────────┼───────────┼───────────────┘
                      │           │
          ┌───────────┼───────────┼───────────┐
          ▼           ▼           ▼           ▼
   ┌──────────┐ ┌─────────┐ ┌────────┐ ┌──────────────┐
   │Marketing │ │   CRM   │ │Loyalty │ │ Subscriptions│
   └──────────┘ └─────────┘ └────────┘ └──────────────┘
          │
          ▼
   ┌──────────┐ ┌──────────────┐
   │   POS    │ │ Integrations │
   └──────────┘ └──────────────┘
```

**Key rules:**
- All packages depend on Tenant (for scoping)
- All packages depend on Core (for Bagisto primitives)
- IndustryModules and ThemeManager are peer dependencies
- Feature packages (Marketing, CRM, Loyalty, etc.) are leaf nodes
- Builder depends on ThemeManager + CMS
- AI is cross-cutting — any package can consume it
- Integrations are cross-cutting — any package can register integrations
- Each package is independently installable (can be excluded from composer.json)

---

## Package Installation System

Each package must be independently installable. This is achieved through:

### 1. Composer Path Repositories
Already configured: `"packages/*/*"` with `"symlink": true`

### 2. Module Registry
Each package registers itself in a central `module_registry` table:
```sql
modules: id, name, version, is_installed, is_active, installed_at
```

### 3. Package Manifest
Each package provides a `satora.json` manifest:
```json
{
  "name": "satora/marketplace",
  "version": "1.0.0",
  "description": "Multi-vendor marketplace engine",
  "dependencies": {
    "satora/tenant": "^1.0",
    "satora/industry-modules": "^1.0"
  },
  "provides": ["marketplace-features"],
  "config": {
    "marketplace.commission.default": "10"
  }
}
```

### 4. Installation Command
```bash
php artisan satora:module:install Marketplace
# → checks dependencies → runs migrations → publishes assets → registers module
```

### 5. Tenant-level Activation
Modules can be enabled per tenant:
```php
// In tenant_settings
'modules' => [
    'marketplace' => true,
    'loyalty' => true,
    'pos' => false,
]
```

---

# Cross-Cutting Concerns

## 1. Search Architecture
- **Elasticsearch** (already in Bagisto) with per-tenant indices
- **Vector search** (via AI package) for semantic product discovery
- **Search API** — Unified interface, per-niche facet configuration
- **Federated search** — Marketplace: search across all sellers
- **Search synonyms** — Per-tenant synonym dictionary
- **Search analytics** — Zero-result tracking, popular queries

## 2. API Layer
- **REST API** — Full coverage, versioned (`/api/v1/`), tenant-scoped
- **Admin API** — For tenant admin panel (SPA)
- **Storefront API** — For headless storefronts
- **Seller API** — For marketplace sellers
- **Webhook API** — Outbound event webhooks
- **GraphQL** — Optional layer (separate package or API plugin)

## 3. Event System
Every commerce action emits events:
- `product.created`, `product.updated`, `product.deleted`
- `order.placed`, `order.paid`, `order.shipped`, `order.delivered`, `order.refunded`
- `customer.registered`, `customer.login`, `customer.updated`
- `cart.item_added`, `cart.item_removed`, `cart.abandoned`
- `review.submitted`, `review.approved`
- `seller.application`, `seller.approved`, `seller.suspended`
- `subscription.created`, `subscription.renewed`, `subscription.cancelled`

Events power: webhooks, analytics, marketing automation, real-time dashboard.

## 4. Real-Time
- **Pusher/WebSocket** — Already in Bagisto
- **Use cases:** Admin notifications, live order updates, stock alerts, chat
- **Per-tenant channels:** `tenant.{id}.orders`, `tenant.{id}.inventory`

## 5. Performance
- **Redis** — Cache, queues, sessions, real-time
- **Octane** — Already in composer.json (RoadRunner/Swoop)
- **CDN** — Static assets, product images, theme assets
- **Elasticsearch** — Offload catalog queries from MySQL
- **Lazy loading** — Images, below-fold content
- **Code splitting** — Per-page JS bundles (Vite)

## 6. Security
- **Tenant isolation** — Global scopes, cache prefix, index prefix
- **XSS** — Purify (already in Bagisto)
- **CSRF** — Laravel default
- **Rate limiting** — Per-tenant, per-IP, per-endpoint
- **File upload scanning** — ClamAV integration optional
- **GDPR** — Already in Bagisto, enhanced with per-tenant data export

## 7. Observability
- **Logging** — Structured JSON logs with tenant_id context
- **Monitoring** — Horizon (queues), Telescope (queries), Pulse (performance)
- **Error tracking** — Sentry/Bugsnag integration
- **Audit trail** — All admin actions logged with tenant_id + user_id

---

# Implementation Phasing

## Phase 1: Foundation (Months 1-3)
1. Enhance Tenant package (global scopes, config, storage, cache, queues)
2. Enhance BusinessPreset (attributes, product types, feature toggles, sections)
3. Enhance ThemeManager (CSS variables, component system, Vite pipeline)
4. Build IndustryModules skeleton + Fashion module as reference implementation
5. Enhance CMS (page builder v1, block library)

## Phase 2: Core Commerce (Months 4-6)
6. Build Marketplace package (seller, orders, commission, payout)
7. Build AI package (product description, search, recommendations v1)
8. Build Reports package (core reports for all niches)
9. Complete IndustryModules: Electronics, Grocery, Beauty

## Phase 3: Growth (Months 7-9)
10. Build Marketing package (email campaigns, workflows, segmentation)
11. Build Loyalty package
12. Build CRM package
13. Complete IndustryModules: Digital, Furniture
14. Build Builder package (visual homepage builder)

## Phase 4: Advanced (Months 10-12)
15. Build Subscriptions package
16. Build POS package
17. Build Integrations hub
18. Build Analytics package
19. Theme completion: All 24 themes (8 niches × 3 themes)
20. Custom niche SDK + headless mode

---

# Design Review & Challenge Log

## Challenges and Resolutions

### Challenge 1: "Won't 16 packages be too many?"
**Resolution:** Each package is independently installable. A tenant using only Fashion + Single Store only loads: Tenant, ThemeManager, BusinessPreset, IndustryModules (Fashion subset), CMS, Reports. That's 6 packages — very manageable.

### Challenge 2: "How do we keep 24 themes from becoming stale?"
**Resolution:** Themes use CSS variable tokens. Changing the color palette updates ALL themes that use tokens. The Theme class defines the tokens; the CSS uses them. A single token change propagates everywhere.

### Challenge 3: "Aren't you over-engineering the niches?"
**Resolution:** Each niche is a PRESET — not hardcoded. A Fashion tenant CAN enable Electronics features if they want. The preset just provides smart defaults. The underlying platform is unified — only the defaults and recommended modules differ.

### Challenge 4: "What about upgrades? Modifying core will break Bagisto updates."
**Resolution:** We NEVER modify core Bagisto packages. We use:
- Global scopes to add tenant_id filtering
- Scoping tables (tenant_product_scopes) instead of modifying core tables
- Events/hooks to inject behavior
- Concord proxy system to extend models

### Challenge 5: "Tenant isolation — what if someone bypasses the global scope?"
**Resolution:** Defense in depth:
1. Global scopes (Eloquent layer)
2. Repository layer always goes through scoped queries
3. Middleware injects tenant context
4. Tests validate no cross-tenant data leakage
5. Audit logging catches anomalies

### Challenge 6: "Marketplace + Single Store — do we maintain two code paths?"
**Resolution:** NO. The Marketplace package builds on the same base. In Single Store mode, the Marketplace package is simply not installed. The codebase has ONE path. When Marketplace IS installed, it adds seller concepts ON TOP of the existing product/order flow.

---

# Final Validation Matrix

| Concern | Coverage |
|---------|----------|
| 8 niches fully specified | YES — Fashion, Electronics, Grocery, Beauty, Digital, Furniture, Generic, Custom |
| Product attributes per niche | YES — detailed tables for each |
| Product types per niche | YES — niche-appropriate types |
| Inventory features per niche | YES — niche-specific inventory |
| Checkout features per niche | YES — niche-specific flows |
| CMS blocks per niche | YES — specific pages/blocks |
| Homepage sections per niche | YES — 9-10 sections each |
| Search filters per niche | YES — facet configs |
| Reports per niche | YES — 7-8 reports each |
| Customer features per niche | YES — niche-specific features |
| Seller features per niche | YES — marketplace-seller specific |
| Admin features per niche | YES — admin tools per niche |
| 3 themes per niche (24 total) | YES — fully described design language, typography, colors, components, navigation, layouts, mobile |
| Marketplace compatibility | YES — every feature mapped to both modes |
| SaaS tenant isolation | YES — migrations, config, storage, themes, uploads, cache, queues, DB, search, cron |
| 16 packages designed | YES — with dependency graph |
| Packages independently installable | YES — with manifest and install system |
| No core Bagisto modification | YES — scoping tables + global scopes + events |
| Implementation phases | YES — 4 phases over 12 months |
| Design self-challenged | YES — 6 challenges with resolutions |

---

# Part 6: Critical Gaps — Self-Review & Supplementary Design

## Gap 1: Internationalization & Multi-Locale Architecture

**Problem:** Satora is Persian-first RTL but must support global deployment. 21 Bagisto locales. Each tenant picks their default locale. Each product, category, and CMS page can have translations. RTL and LTR themes must coexist.

### Locale Architecture

```
Platform Level:
  - Bagisto's existing 21-locale system handles backend translations
  - Admin panel locale: per-admin preference (session), default: tenant locale

Tenant Level:
  - Default locale: set during onboarding (fa, en, ar, tr, etc.)
  - Supported locales: tenant can enable additional storefront locales
  - Locale switcher: dropdown/flag on storefront for multi-locale tenants

Content Level:
  - Products: translatable fields (name, description, short_description, meta_*)
    via Astrotomic\Translatable (already in Bagisto)
  - Categories: translatable (name, description, meta_*)
  - CMS pages: already translatable via cms_page_translations
  - CMS blocks: new translatable block content table
  - Theme strings: locale files per theme (theme-name/locale/fa.json)
  - Niche-specific terms: per-locale in IndustryModules (e.g., "Size Guide" → "راهنمای سایز")

RTL/LTR Handling:
  - Theme declares supported directions: ['rtl', 'ltr'] or ['rtl-only']
  - CSS uses logical properties (margin-inline-start, padding-inline-end) — NOT left/right
  - All 24 themes implement BOTH RTL + LTR variants via CSS logical properties
  - Component mirroring: icons, arrows, carousels auto-flip based on dir="rtl"
  - Number formatting: fa_IR uses Persian numerals, en_US uses Arabic numerals
  - Date formatting: Jalali (Persian) calendar support for fa_IR locale
```

### Locale Fallback Chain
```
Product display locale:
  1. Requested locale (URL segment or domain detection)
  2. Tenant default locale
  3. English (en) — always the ultimate fallback

Admin locale:
  1. Admin's session preference
  2. Tenant default locale
  3. English (en)
```

---

## Gap 2: Payment Architecture — Marketplace Split Payments

**Problem:** In marketplace mode, a single cart may contain items from 3 sellers. Customer pays once. Each seller must receive their share minus commission. This requires a sophisticated payment routing system.

### Payment Gateway Abstraction

```php
interface PaymentGatewayInterface {
    public function charge(float $amount, string $currency, array $metadata): PaymentResult;
    public function refund(string $transactionId, float $amount): RefundResult;
    public function supportsSplitPayments(): bool;
    public function supportsSavedCards(): bool;
    public function getWebhookHandler(): WebhookHandler;
}

// Per-gateway:
// - Stripe: supports Connect (split: destination charges or separate charges + transfers)
// - PayPal: supports Payouts API for seller disbursement (delayed payout)
// - Razorpay: supports Route (split payments)
// - Cash: no split needed — marketplace collects, pays sellers manually
```

### Split Payment Flows

**Flow A: Gateway-Native Split (Stripe Connect, Razorpay Route)**
```
Customer pays $100 → Gateway splits:
  → Seller A: $30 (product $30, commission 10%=$3, net $27)
  → Seller B: $50 (product $50, commission 15%=$7.5, net $42.5)
  → Platform: $20 (product $20 + commissions $10.5, net $30.5)
```
This is the preferred flow — instant, no payout delay, lower risk.

**Flow B: Platform Collects + Delayed Payout (Universal Fallback)**
```
Customer pays $100 → Platform collects all
Platform holds seller funds → scheduled payout:
  → Weekly batch payout to Seller A ($27)
  → Weekly batch payout to Seller B ($42.5)
```
Used when: gateway doesn't support split, or seller payout threshold not met.

### Tenant Payment Configuration
- Each tenant configures their OWN payment gateways
- Gateway credentials are tenant-scoped (tenant_settings, encrypted)
- Available gateways per tenant: Stripe (always), PayPal, Razorpay, PayU, COD, Bank Transfer
- Marketplace: platform can enforce minimum gateway requirements for sellers

---

## Gap 3: Shipping Architecture — Multi-Seller Fulfillment

**Problem:** In marketplace mode, items from 3 sellers may ship from 3 locations with 3 carriers. The customer sees one order but potentially 3 deliveries.

### Shipping Model

```
Order (customer-facing):
  - 3 items from 3 sellers
  - Shipping cost: sum of per-seller shipping OR consolidated rate

SubOrder (per-seller):
  - SubOrder 1 (Seller A): 1 item, ships from Warehouse A via DHL, $5 shipping
  - SubOrder 2 (Seller B): 1 item, ships from Warehouse B via FedEx, $7 shipping
  - SubOrder 3 (Platform): 1 item, free shipping

Customer Experience:
  - Cart shows "Shipping: $12.00" (sum)
  - Order confirmation shows per-item shipping breakdown
  - Tracking page: 3 tracking numbers with carrier logos
  - Delivery timeline: each SubOrder has separate ETA
```

### Shipping Rate Resolution
Per seller, configurable:
1. Flat rate per seller
2. Table rate (weight/destination matrix)
3. Live carrier API rates (DHL, FedEx, UPS — via Integrations package)
4. Free shipping above order threshold (per seller or marketplace-wide)
5. Local pickup (seller-defined pickup locations)

### Consolidated Shipping
When marketplace offers consolidated shipping:
- Items from all sellers route to a central warehouse
- Marketplace staff repackages into one shipment
- Additional handling fee applies
- Seller ships to warehouse (seller cost), marketplace ships to customer (customer cost)

---

## Gap 4: Tax Engine

**Problem:** Tax is jurisdiction-dependent, product-dependent, and tenant-dependent. EU VAT, US state/county/city tax, GST, digital goods tax — all different.

### Tax Architecture

```php
interface TaxCalculator {
    public function calculate(Cart $cart, Address $shippingAddress): TaxCalculation;
}

// Configurable tax rules per tenant:
// 1. Origin-based: tax at seller's location (US intrastate)
// 2. Destination-based: tax at customer's location (US interstate, EU B2C)
// 3. Product-type-based: digital goods taxed differently (EU MOSS)
// 4. Hybrid: per-category, per-product-type rules
```

### Tax Rule Structure
```
tax_rules table (tenant-scoped):
  - zone (EU, US-CA, US-NY, IN-GJ, etc.)
  - tax_class (physical-goods, digital-goods, food, clothing, luxury)
  - rate (percentage)
  - is_compound (tax on tax — Canadian GST+PST)
  - applies_to_shipping (boolean)
  - product_tax_code (optional — for TaxJar/Avalara mapping)
```

### Tax Calculation Flow
```
1. Resolve customer address → determine jurisdiction(s)
2. Resolve product tax class per item
3. Calculate base tax per item
4. Apply shipping tax if configured
5. Apply compound tax if applicable
6. Total tax = sum of all
7. Store tax breakdown on order for reporting
```

### Integration with Tax Services
- TaxJar, Avalara, Vertex via Integrations package
- Tenant can connect their own tax service account
- Fallback: built-in simple rate calculator (adequate for single-country tenants)
- EU One-Stop-Shop (OSS): automatic digital goods VAT calculation

---

## Gap 5: Mobile Strategy — PWA + Optional Native

**Problem:** "Odoo of ecommerce" implies mobile. Store owners need mobile admin. Shoppers need mobile storefronts.

### Progressive Web App (Default)
- Every tenant's storefront is installable as PWA
- Service worker: offline catalog browsing, cached product images
- Push notifications: order updates, promotions, abandoned cart
- Manifest per tenant: name, icon, colors from theme
- PWA quality: Lighthouse score > 90

### Mobile Admin
- Responsive admin panel (already partially responsive)
- PWA-able admin for store owners on-the-go
- Key admin actions mobile-optimized: approve orders, check inventory, respond to tickets

### Native Apps (Optional — Phase 4+)
- White-label app per marketplace (one app for the platform)
- Tenant storefront embedded within the marketplace app
- OR: individual tenant apps via App Store/Google Play (large tenants)
- Flutter or React Native codebase, shared across all tenants
- App shell + WebView hybrid for content-rich pages

### Mobile-First Theme Development
- ALL 24 themes are mobile-first (designed mobile first, desktop enhanced)
- Touch targets: minimum 44px (Apple HIG) / 48px (Material Design)
- Gesture support: swipe, pinch-zoom, pull-to-refresh
- Mobile payment: Apple Pay, Google Pay always prominent in mobile checkout

---

## Gap 6: Import/Export Architecture

**Problem:** Tenants need to migrate from other platforms (Shopify, WooCommerce, Magento). They need to bulk-import products, categories, customers.

### Import System
```
DataTransfer package (existing) enhanced with:
  - Niche-specific import templates (CSV/XLSX headers per niche attributes)
  - Shopify → Satora migration wizard
  - WooCommerce → Satora migration wizard
  - Image URL import + auto-download
  - Variant import (configurable products with all combinations)
  - Validation: pre-import validation report (errors before committing)
  - Dry run mode: preview 100 rows before full import
  - Batch processing: queue large imports, progress bar
  - Rollback: revert an import batch if errors detected
```

### Export System
```
  - Full catalog export (products, categories, attributes)
  - Order export (for accounting: CSV, XLSX, PDF)
  - Customer export (for CRM migration, GDPR data portability)
  - Custom export builder: select fields, filters, format
  - Scheduled export: daily/weekly to FTP/S3/email
```

---

## Gap 7: Testing Strategy

**Problem:** 16 packages × 8 niches × 24 themes × 2 modes = impossible to manually test.

### Testing Pyramid

```
Unit Tests (Pest):
  - Every model, repository, service class
  - Every preset class (does it return correct attributes?)
  - Every theme class (does it generate correct CSS variables?)
  - Every tax calculation, commission calculation
  - Target: 80%+ code coverage

Feature Tests (Pest):
  - API endpoint tests (REST + Admin + Storefront)
  - Tenant isolation tests (cross-tenant data leakage)
  - Marketplace order split tests
  - Subscription billing cycle tests
  - Import/export round-trip tests

Integration Tests (Pest):
  - Payment gateway mock tests (charge, refund, webhook)
  - Shipping carrier API mock tests
  - Email delivery tests (Mailpit)
  - Elasticsearch index + search tests
  - Redis cache isolation tests

E2E Tests (Playwright — per niche, per theme):
  - Critical user journeys per niche
  - Admin: create product, manage order, view report
  - Storefront: browse, search, add to cart, checkout (mock payment)
  - Marketplace: seller onboarding, product approval, order fulfillment
  - Mobile viewport tests for all themes
  - RTL layout tests for Persian/Arabic locales

Visual Regression (Percy/Chromatic):
  - Screenshot comparison for all 24 themes
  - Component-level visual testing
  - Responsive breakpoint visual checks
```

### Test Data Factories
- Per-package model factories with tenant_id
- Preset seeder that creates a full demo tenant with products, orders, customers
- Niche-specific demo data: Fashion tenant gets clothing products, Electronics gets devices

---

## Gap 8: CI/CD Pipeline

**Problem:** 16 packages must be built, tested, and deployed atomically.

### Pipeline Design (GitHub Actions)

```
Pull Request:
  1. pint --test (code style, 21 locale files check)
  2. pest --compact (affected packages only via path filtering)
  3. Translation check: php artisan bagisto:translations:check
  4. Playwright E2E (affected niche + theme matrix)
  5. Visual regression diff

Merge to main:
  1. Full test suite (all packages)
  2. Build assets (Vite per theme, minification)
  3. Deploy to staging
  4. Smoke tests on staging
  5. Deploy to production (blue-green)

Theme CI:
  - Each theme builds independently
  - Theme lint: CSS variable completeness check
  - Theme visual regression: screenshot comparison
  - Theme performance: Lighthouse audit per page type

Niche CI:
  - Preset generation test: create tenant with preset, verify all defaults applied
  - IndustryModule test: install module, verify features work
```

### Deployment
- Laravel Vapor (serverless) or Forge (VPS) or Kubernetes
- Zero-downtime deployment
- Database migration: `php artisan migrate` with tenant-aware batching
- Asset deployment: CDN purge per theme

---

## Gap 9: Tenant Onboarding — Full Lifecycle

**Problem:** The current 6-step wizard is too thin. Each step needs to trigger real data creation.

### Enhanced Onboarding Flow

```
Step 1: Account
  - Email, password, name
  - Tenant name, slug (auto-generated from name, editable)

Step 2: Type
  - Single Store OR Marketplace (MUTUALLY EXCLUSIVE, stored in modules JSON)
  - This determines which packages are activated

Step 3: Niche
  - Visual grid of 8 niches with preview images
  - Each niche card: icon, name, description, "best for" examples
  - Custom = blank canvas, no presets applied

Step 4: Template
  - Filtered by niche compatibility
  - Visual preview (screenshot/mockup of homepage)
  - 3+ templates per niche

Step 5: Theme
  - Visual color palette preview
  - Typography preview
  - Live mini-preview: sample product card + button
  - "Apply & Preview" → loads a demo storefront with sample content

Step 6: Finalize
  - Summary of all choices
  - "Create My Store" button triggers:
    a. Create tenant record
    b. Apply BusinessPreset (categories, attributes, product types, settings, pages, blocks)
    c. Activate IndustryModules per niche
    d. Seed demo products (optional — "Start with sample products" checkbox)
    e. Publish tenant (is_active = true)
    f. Redirect to admin dashboard with onboarding checklist
```

### Automatic Actions on Preset Application
```
PresetApplier (enhanced from current PresetApplier):
  1. Create EAV attributes (product attributes per niche)
  2. Create attribute families (group attributes into sections)
  3. Create attribute groups
  4. Create categories (hierarchy from preset definition)
  5. Enable product types (configurable, bundle, gift card, etc.)
  6. Create CMS pages (about, contact, size-guide, etc.) with default content
  7. Create CMS blocks (homepage sections with placeholder content)
  8. Apply recommended settings (admin config values)
  9. Activate niche-specific IndustryModules
  10. Send welcome email with getting-started guide
```

---

## Gap 10: Super Admin Panel

**Problem:** The platform owner (Satora) needs to manage all tenants, monitor platform health, handle billing.

### Super Admin Features

```
Tenant Management:
  - List all tenants with filters (status, niche, plan, created date)
  - View tenant details: config, usage stats, admin users
  - Impersonate tenant admin (login as tenant admin)
  - Suspend/activate/delete tenant
  - Manual override: change tenant plan, extend trial, adjust limits

Platform Analytics:
  - Total MRR, ARR, churn rate
  - Tenants by niche, by plan, by region
  - Resource usage: storage, bandwidth, API calls per tenant
  - Top tenants by revenue, orders, products
  - Growth metrics: new signups, conversion rate, trial→paid

Billing:
  - Subscription plan management (create/edit plans)
  - Tenant invoices and payment history
  - Revenue reports
  - Refund management

System Health:
  - Queue metrics (Horizon dashboard)
  - Error rates (Sentry dashboard)
  - Server metrics (CPU, memory, disk)
  - Elasticsearch cluster health
  - Database performance (slow queries)

Package Management:
  - Global module enable/disable
  - Package version management
  - Feature flags for beta features
  - Rate limiting configuration

Content Moderation:
  - Flagged products/reviews (marketplace)
  - DMCA/copyright complaints
  - Fraudulent seller detection
```

---

## Gap 11: Pricing & Subscription Tiers for Tenants

**Problem:** How does Satora make money? Need a tenant subscription system.

### Pricing Model

```
Tier Structure:
  ┌─────────────┬──────────┬───────────┬─────────────┐
  │             │  Starter  │  Business │  Enterprise │
  ├─────────────┼──────────┼───────────┼─────────────┤
  │ Products    │  100      │  1,000    │  Unlimited  │
  │ Storage     │  1 GB     │  10 GB    │  100 GB     │
  │ Bandwidth   │  50 GB    │  500 GB   │  5 TB       │
  │ Themes      │  Basic 3  │  All 24   │  All + Custom│
  │ Niches      │  Generic  │  All      │  All        │
  │ Marketplace │  No       │  Yes (5%) │  Yes (3%)   │
  │ AI Features │  No       │  Basic    │  Full       │
  │ Support     │  Email    │  Priority │  Dedicated  │
  │ Custom Domain│  Yes     │  Yes      │  Yes        │
  │ API Access  │  No       │  REST     │  REST+GraphQL│
  │ White Label │  No       │  No       │  Yes        │
  ├─────────────┼──────────┼───────────┼─────────────┤
  │ Price/mo    │  $29      │  $99      │  Custom     │
  └─────────────┴──────────┴───────────┴─────────────┘

Additional:
  - Transaction fees: 0% (Starter), 1% (Business), 0.5% (Enterprise)
  - Marketplace commission: platform takes X% of each seller transaction
  - Add-ons: POS (+$49/mo), White-label app (+$199/mo), Custom theme (+$999 one-time)
```

### Usage Tracking
```
TenantUsageService:
  - Products count, storage bytes, bandwidth bytes
  - Tracked via events (product.created → increment, image uploaded → increment bytes)
  - Soft limits: warnings at 80%, hard blocks at 100%
  - Admin notification when approaching limit
  - Usage dashboard in admin panel
```

---

## Gap 12: Email Architecture

**Problem:** Each niche needs different transactional email content and design. Fashion: elegant, imagery-rich. Electronics: clean, spec-focused. Grocery: friendly, practical.

### Email System

```
Tenant-Level Email Configuration:
  - SMTP settings (per tenant or platform-wide)
  - Sender name + email (tenant's brand)
  - Logo (from tenant settings)
  - Email footer (tenant's address, social links)

Niche-Specific Email Templates:
  Each niche provides default templates for:
    - Order Confirmation
    - Order Shipped (with tracking)
    - Order Delivered
    - Order Cancelled
    - Refund Processed
    - Welcome (new customer)
    - Password Reset
    - Abandoned Cart
    - Back in Stock
    - Review Request
    - Birthday Offer (if Loyalty enabled)
    - Subscription Renewal (if Subscriptions enabled)

Email Design System:
  - Base layout: shared across niches (footer, header structure)
  - Niche styling: colors, fonts, imagery from theme
  - Responsive: mobile-first email design
  - Preview: admin can preview and customize before sending
  - Test send: send test email to admin
  - Locale: email sent in customer's preferred locale
```

### Email Delivery
- Mailgun / SendGrid / SES via Integrations package
- Queue all emails for async sending
- Email analytics: open rate, click rate per template
- Email preference center: customer can opt out per email type

---

## Gap 13: Image & Media Architecture

**Problem:** Product images, category images, CMS images, theme assets, user uploads — all need CDN delivery with tenant scoping.

### Media Storage

```
Storage Structure:
  storage/app/public/
    tenants/{tenant_id}/
      products/{product_id}/       # Product images (original + thumbnails)
      categories/{category_id}/    # Category banners
      cms/{page_id}/               # CMS page images
      blocks/{block_id}/           # Block images
      reviews/{review_id}/         # Review photos (UGC)
      imports/                     # CSV/XLSX import files
      exports/                     # Generated export files
      email/                       # Email image assets
      favicon.ico                  # Tenant favicon
      logo.png                     # Tenant logo (multiple sizes)
      og-image.png                 # Social share image

CDN Strategy:
  - Images served via CDN (Cloudflare / BunnyCDN)
  - URL pattern: https://cdn.satora.com/tenants/{id}/products/{id}/image.jpg
  - Image optimization: WebP auto-conversion, responsive srcset
  - Lazy loading: native loading="lazy" + blur-up placeholder
  - Cache headers: immutable + long TTL for product images
  - Cache bust: append ?v={updated_at_timestamp}
```

### Image Processing
- **Intervention/image** (already in Bagisto) for resize/crop
- **Thumbnail sizes per niche:**
  - Fashion: 800×1200 (portrait), 400×600 (thumb), 80×120 (micro)
  - Electronics: 800×800 (square), 400×400, 80×80
  - Grocery: 600×600, 300×300, 60×60
  - Furniture: 1200×800 (landscape room shots), 600×400, 120×80
- ImageMagick for WebP conversion (smaller than JPEG, better quality)
- BlurHash for placeholder generation

---

## Gap 14: Database Extension Strategy (No Core Modification)

**Problem:** How to add `tenant_id` scoping and niche-specific data to existing Bagisto tables without modifying core migrations or models.

### Strategy: Scoping Tables + Model Extension

```
Core Bagisto tables: UNTOUCHED
  - products, categories, orders, customers, etc. — never modified

Satora extension tables (tenant-scoped):
  tenant_products:        tenant_id, product_id, is_visible, custom_price, niche
  tenant_categories:      tenant_id, category_id, is_visible, custom_name
  tenant_orders:          tenant_id, order_id
  tenant_customers:       tenant_id, customer_id

How it works:
  1. ProductRepository (NEW Satora repository, extends core):
     public function model(): string {
       return \Webkul\Product\Contracts\Product::class;
     }
     // Overrides core queries to JOIN tenant_products

  2. Global scope on core models:
     // In SatoraServiceProvider::boot()
     Product::addGlobalScope(new TenantProductScope());

     // TenantProductScope:
     public function apply(Builder $builder, Model $model) {
       $tenantId = app(TenantResolver::class)->id();
       if ($tenantId) {
         $builder->whereHas('tenantScope', fn($q) =>
           $q->where('tenant_id', $tenantId));
       }
     }

  3. Core model relationship (added via trait):
     // TenantAware trait applied to Product model via Concord proxy
     public function tenantScope() {
       return $this->hasOne(TenantProduct::class, 'product_id');
     }

New Satora-owned tables (full control):
  - All IndustryModule tables have tenant_id baked in
  - All new package tables have tenant_id
  - Theme settings table: tenant_theme_settings (tenant_id, theme_code, custom_colors)
```

---

## Gap 15: Niche Migration — Changing Tenant Niche

**Problem:** A tenant starts as "Generic" then decides to become "Fashion." Their existing data must be preserved while new niche features are applied.

### Migration Strategy

```
Niche Change Flow:
  1. Admin goes to Settings → Business Profile → Change Niche
  2. System shows a DIFF: what will be added, what stays, what conflicts
  3. Admin confirms

What happens:
  ADD:
    - New product attributes (Size, Color, Material, Brand, Season, etc.)
    - New CMS pages (Size Guide, Lookbook, Sustainability)
    - New CMS blocks (hero sections, lookbook strip)
    - Enable niche-specific IndustryModules
    - New search filter configuration
  
  PRESERVE:
    - Existing products (they get the new attributes as empty fields)
    - Existing categories (may map to new structure or stay as-is)
    - Existing orders, customers, reviews
    - Custom CMS pages (not overwritten)
    - Admin settings (not overwritten unless admin opts in)
  
  CONFLICT:
    - If tenant created a "Size Guide" page manually, the preset won't overwrite it
    - Admin can choose: keep mine, replace with niche default, merge
  
  REVERT:
    - Niche change is reversible for 30 days
    - After 30 days, niche-specific data (attributes, pages) can be manually removed
```

---

## Gap 16: Seller Trust & Quality System (Marketplace)

**Problem:** How do customers trust sellers in a marketplace? Need a reputation system.

### Seller Trust Score

```
Score Components (0-100):
  - Order fulfillment rate (95%+ = 25pts)
  - On-time delivery rate (90%+ = 25pts)
  - Return rate (below 5% = 20pts)
  - Review average (4.5+ stars = 20pts)
  - Response time (under 24hr = 10pts)

Badges:
  - "Verified Seller" — identity verified
  - "Top Rated" — trust score > 85
  - "Fast Shipper" — average processing < 24 hours
  - "Authentic" — brand-authorized (fashion/beauty/electronics)
  - "Eco-Friendly" — sustainable practices verified

Display:
  - Seller trust score visible on product page
  - Badges on seller store page and product cards
  - Low-trust sellers: products demoted in search (configurable)

Consequence:
  - Trust score < 50: products require manual approval
  - Trust score < 30: account suspended, review triggered
```

---

## Gap 17: Review System Enhancement

**Problem:** Bagisto has reviews but niche-specific reviews need different fields and media.

### Niche-Specific Review Fields

```
Fashion:
  - Fit: Runs Small / True to Size / Runs Large
  - Photo reviews encouraged (show the outfit on)

Electronics:
  - Pros / Cons (structured fields)
  - Verified Purchase badge prominent
  - Tech level: Beginner / Intermediate / Expert (for review context)

Beauty:
  - Skin type / Skin tone tags on reviewer
  - Before & After photos (moderated)
  - "Received as sample" disclosure

Grocery:
  - Taste rating, Freshness rating (sub-ratings)
  - "Would buy again" simple thumbs up/down

Furniture:
  - Photo of product in room (UGC)
  - Assembly difficulty: Easy / Moderate / Hard
  - "True to photo" accuracy rating

Digital:
  - "Was this review helpful?" vote count
  - Verified Download badge
  - Version reviewed (v1.0 vs v2.3)
```

### Review Moderation
- Auto-flag: profanity, competitor URLs, spam patterns
- Manual moderation queue
- Verified purchase always marked
- Seller response to reviews

---

## Gap 18: Compliance & Legal Architecture

**Problem:** Multi-tenant, multi-jurisdiction — each tenant may have different legal requirements.

### Per-Tenant Legal Configuration

```
GDPR/Privacy:
  - Cookie consent banner (per tenant, configurable)
  - Privacy policy page (per tenant)
  - Data export request (customer can download their data)
  - Data deletion request (right to be forgotten)
  - Per-tenant data retention periods

Terms & Conditions:
  - Per-tenant T&C page
  - Must-accept on registration and checkout

Tax Compliance:
  - Tax ID (VAT/GST number) per tenant
  - Invoice numbering per tenant (configurable prefix/format)
  - Invoice legal text per tenant (company registry, address)
  - Digital tax reporting (country-specific: MTD for UK, e-invoicing for Italy/India)

Age Restrictions:
  - Per-product age gate (alcohol, adult content)
  - Age verification at checkout for restricted products

PCI DSS:
  - No credit card storage (tokenized via gateway)
  - All payment pages served over HTTPS
  - SAQ-A compliance through gateway delegation

Accessibility:
  - WCAG 2.1 AA target for all themes
  - Screen reader support (ARIA labels throughout)
  - Keyboard navigation (tab order, focus indicators)
  - Color contrast compliance (built into theme definitions)
```

---

# Part 7: Revised Implementation Phasing

## Phase 1: Foundation (Months 1-3)
1. Enhance Tenant: global scopes, scoping tables, tenant config, storage, cache, queues
2. Enhance BusinessPreset: attributes, product types, feature toggles, CMS auto-creation, preset application engine
3. Enhance ThemeManager: CSS variable compilation, component system, Vite pipeline, RTL support
4. Build IndustryModules skeleton + Fashion module (reference)
5. Enhance CMS: page builder v1, block library, media library
6. **ADDED:** Tax engine v1 (simple rate calculator)
7. **ADDED:** Email architecture (base templates + niche-specific overrides)
8. **ADDED:** Super Admin panel v1 (tenant management, basic analytics)

## Phase 2: Core Commerce (Months 4-6)
9. Build Marketplace: seller, orders, split payments, commission, payout, trust system
10. Build AI: product descriptions, search, recommendations v1
11. Build Reports: core reports for all niches
12. Complete IndustryModules: Electronics, Grocery, Beauty
13. **ADDED:** Payment split engine (Stripe Connect + delayed payout)
14. **ADDED:** Shipping multi-seller (rate resolution, tracking consolidation)
15. **ADDED:** Review system v2 (niche-specific fields, photo reviews)

## Phase 3: Growth (Months 7-9)
16. Build Marketing: email campaigns, workflows, segmentation, abandoned cart
17. Build Loyalty: points, tiers, rewards, referral
18. Build CRM: customer 360, tickets, segments, health score
19. Complete IndustryModules: Digital, Furniture
20. Build Builder: visual homepage builder, color customizer, mobile preview
21. **ADDED:** Import/Export v2 (niche templates, Shopify/WooCommerce migration)
22. **ADDED:** Image/CDN strategy (tenant-scoped storage, WebP, responsive images)

## Phase 4: Advanced (Months 10-12)
23. Build Subscriptions: recurring billing, subscription management
24. Build POS: web register, barcode, inventory sync
25. Build Integrations: shipping carriers, tax services, marketing, CRM connectors
26. Build Analytics: event tracking, funnels, heatmaps, attribution
27. Theme completion: All 24 themes (8 niches × 3 themes)
28. Custom niche SDK + headless mode
29. **ADDED:** PWA implementation (service workers, offline, push notifications)
30. **ADDED:** Compliance suite (GDPR tools, cookie consent, accessibility audit)
31. **ADDED:** Multi-locale content (full translation workflow for all 21 locales)
