# AGENTS.md — ServisPilot

> **READ THIS FIRST.** This is a dual-platform product. Read `WEB_MOBIL_SENKRON_KURALLARI.md` before any change.

## Project Overview
ServisPilot is a multi-tenant fleet/service management SaaS with two clients:
- **Web panel** — Laravel 12 + Blade + Tailwind (admin dashboard)
- **Mobile app** — React Native + Expo SDK 54 (`mobile-app/`)

Both consume the same `/api/v1/*` API.

## CRITICAL RULE — Web/Mobile Parity
**Any change to web behavior MUST be mirrored in the mobile app, and vice versa.**

When you edit:
- A `app/Http/Controllers/Api/V1/*ApiController.php` → also check `mobile-app/src/screens/*Screen.js` and `mobile-app/src/api/*.js`
- A migration → check mobile form fields and state
- A permission slug → check `hasPermission()` calls in mobile screens
- A validation rule → mirror it in the mobile form
- A response shape → update mobile parsing

If you ship only one side, the change is INCOMPLETE.

See `WEB_MOBIL_SENKRON_KURALLARI.md` for the full sync contract.

## Build & Test Commands
- Web: `php artisan serve`, `php artisan test`, `php artisan migrate`
- Mobile: `cd mobile-app && npm install && npx expo start`
- Smoke tests for API: `php test_batch1.php`, `php test_batch2.php`, `php test_batch3.php`

## Permission System
Custom (no Spatie). See `app/Models/User.php::hasPermission()` and `app/Http/Middleware/Permission.php`.
Slugs are defined as `module.action` (e.g., `customers.create`, `trips.delete`).
Both web routes (`routes/web.php` `permission:` middleware) AND mobile UI (`hasPermission()` in `AuthContext`) AND API routes (`routes/api.php`) use the same slugs.

## Multi-tenancy
Every tenant model uses `BelongsToCompany` trait + `company_id` column. New models MUST follow this pattern.

## Conventions
- API controllers: PascalCase + `ApiController` suffix, in `app/Http/Controllers/Api/V1/`
- Mobile screens: PascalCase + `Screen` suffix, in `mobile-app/src/screens/`
- Mobile axios calls: import from `mobile-app/src/api/axios.js` (centralized auth + 401/403 handling)

## Forbidden
- Do NOT use `eloquent-sluggable` package
- Do NOT use Spatie permission package
- Do NOT bypass `BelongsToCompany` scope
- Do NOT add web-only features without explicit user confirmation
