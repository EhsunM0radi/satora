# Storage, Session & Routing Architecture

## What's Stored Where

| Storage | What's in it | Bagisto Footprint |
|---------|-------------|-------------------|
| **Cookie: `satora_session`** | Laravel session ID (auth state, CSRF, flashes) | Cookie name derived from `APP_NAME=Satora` |
| **Cookie: `XSRF-TOKEN`** | CSRF token — standard Laravel, no branding | Clean |
| **Database: `sessions` table** | Serialized session data (driver: `database`) | Clean |
| **File: `storage/framework/cache/`** | Cache (driver: `file`) | Clean |
| **`localStorage`** | NOT used — nothing stored there | Clean |
| **`sessionStorage`** | NOT used — nothing stored there | Clean |

## Authentication

Both `admin` and `customer` guards use `driver: 'session'` — authentication state lives in the server-side session, not in cookies or localStorage. There is no separate "auth token" cookie — the session cookie IS the auth mechanism.

Config: `config/auth.php`
```php
'guards' => [
    'customer' => ['driver' => 'session', 'provider' => 'customers'],
    'admin'    => ['driver' => 'session', 'provider' => 'admins'],
],
```

## Session Cookie Naming

The session cookie name is derived in `config/session.php`:
```php
'cookie' => env(
    'SESSION_COOKIE',
    Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
),
```

With `APP_NAME=Satora` in `.env`, the cookie is named `satora_session`.

## Multi-Tenant Routing

### Production
Tenant resolution by subdomain: `{slug}.satora.com`

- `ResolveTenant` middleware extracts subdomain, queries tenant by slug
- `buildTenantUrl()` returns `http://{slug}.satora.com`
- Admin: `http://{slug}.satora.com/admin`

### Local (Herd)
Tenant resolution by path: `satora.test/shop/{slug}`

- `ResolveTenant` middleware extracts slug from URL path
- `buildTenantUrl()` returns `http://satora.test/shop/{slug}`
- Admin: `http://satora.test/admin` (same domain, session-based auth)
- No DNS/subdomain configuration needed — works with `herd park` out of the box

### Environment Detection
Both `TenantResolver` and `buildTenantUrl()` check `app()->environment('local')` to switch between path-based (local) and subdomain-based (production) routing.

## Bagisto Footprints (Rebranding Needed)

### User-Visible in Browser

| Item | Current Value | Location |
|------|--------------|----------|
| Session cookie name | `bagisto_session` | `.env` → `APP_NAME=Bagisto` |
| FPC HTML marker | `<bagisto-response-cache-session-flashes>` | `packages/Webkul/FPC/src/Replacers/FlashMessagesReplacer.php:13` |
| FPC HTML marker (view) | `<bagisto-response-cache-session-flashes>` | `packages/Webkul/Shop/src/Resources/views/components/flash-group/index.blade.php:84` |

### Internal Only (Not Visible to Browser)

| Item | Current Value | Location |
|------|--------------|----------|
| `bagisto_asset()` helper | Used in ~20 blade templates | `packages/Webkul/Theme/src/Http/helpers.php:24` |
| `view_render_event('bagisto.*')` | 257 occurrences across all packages | Event names in PHP (never sent to browser) |
| `BAGISTO_VERSION` | `'2.4.8'` | `packages/Webkul/Core/src/Core.php:31` |
| `BAGISTO_LOGO` | `'https://updates.bagisto.com/bagisto.png'` | `packages/Webkul/ImageCache/src/Http/Controllers/ImageCacheController.php:20` |
| Artisan commands | `bagisto:version`, `bagisto:translations:check` | `packages/Webkul/Core/src/Console/Commands/` |
| Mail transport | `bagisto-dynamic-smtp` | `packages/Webkul/Core/src/Mail/Transport/DynamicSmtpTransport.php:57` |

## Rebranding Priority

1. **Quick fix (high impact)**: Change `APP_NAME=Satora` in `.env` + rename FPC marker in 2 files
2. **Medium**: Rename `bagisto_asset()` → `satora_asset()` + update all ~20 blade templates
3. **Full purge**: Rename all `view_render_event('bagisto.*')` events across 257 occurrences
