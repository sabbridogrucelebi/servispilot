# ANTIGRAVITY PROMPT — Universal AI Sync Rules

## AMAÇ
Bu projede hangi AI aracı (Claude Code, Cursor, Antigravity, Windsurf, GitHub Copilot, Codex CLI, Aider, vs.) kod yazarsa yazsın, **web panel** ve **mobile-app** arasındaki paritenin korunması zorunlu olduğunu otomatik olarak anlamalı. Hiçbir AI "ben sadece web'i değiştirdim" diyemeyecek — eğer web'deki bir endpoint, model, izin, validation kuralı veya iş mantığı değişiyorsa, mobil tarafının da senkron edilmesi MECBURİYET.

Bunu sağlamak için endüstri standardı convention dosyalarını oluştur. Her AI aracı kendi conventionunu okuyacak ama hepsi **aynı altın kuralı** söyleyecek.

---

## YAPILACAKLAR (DURAKSAMA, ARA ONAY ALMA, HEPSİNİ TEK SEFERDE TAMAMLA)

### 1) Kanonik kural dokümanı oluştur
**Dosya:** `WEB_MOBIL_SENKRON_KURALLARI.md` (proje kökü)

İçerik:
- **Altın Kural** (kalın, üst): "ServisPilot çift platformlu bir üründür. Web panel (Laravel + Blade) ve mobile-app (React Native/Expo) **fonksiyonel olarak eşit (parite)** olmak ZORUNDADIR. Web'de yapılan her değişiklik mobilde de karşılığını almalıdır; aksi halde değişiklik **EKSİK** kabul edilir ve PR/commit reddedilir."
- **Tetikleyiciler** (web'de neyin değişmesi mobil değişikliği zorunlu kılar):
  - Yeni route/endpoint ekleme veya değiştirme (`routes/api.php`, `routes/web.php`)
  - Controller'da yeni method/validation/iş mantığı
  - Migration (yeni tablo, yeni kolon, kolon adı/tipi değişikliği)
  - Model'e yeni `$fillable`, `$casts`, scope, relation ekleme
  - Permission slug ekleme/değiştirme/silme (`permissions` seeder, `PermissionSlug` enum)
  - Form Request validation kuralı ekleme/değiştirme
  - API response shape değişikliği (alan ekleme/silme/yeniden adlandırma)
  - Yeni tenant-scoped model
  - Yeni dosya/asset upload akışı
- **Senkronizasyon eşleşme tablosu** (web → mobile karşılıkları):
  - `app/Http/Controllers/Api/V1/*ApiController.php` ↔ `mobile-app/src/screens/*Screen.js` + `mobile-app/src/api/*.js`
  - `app/Models/*.php` ↔ `mobile-app/src/types/*.js` (varsa) ve ekran formları
  - `app/Http/Middleware/Permission.php` slugları ↔ `mobile-app/src/context/AuthContext.js` `hasPermission()` çağrıları
  - `routes/api.php` ↔ `mobile-app/src/api/axios.js` endpoint URL'leri
  - Migration kolonları ↔ Mobil form alanları + state
- **Checklist** (her PR için): "[ ] Endpoint web'de eklendi mi? [ ] Mobilde aynı endpoint çağrılıyor mu? [ ] Permission slug eklendiyse mobilde `hasPermission` ile gizlendi/açıldı mı? [ ] Validation kuralları mobilde de uygulandı mı? [ ] Tenant scope korunuyor mu?"
- **İstisnalar** (sadece şunlar mobil senkronu gerektirmez): admin-only super admin paneli özellikleri (`/admin-panel/companies` gibi sadece super admin'e ait olanlar), web özel reporting/export ekranları, e-posta template'leri.
- **Yaptırım**: "Eğer tek tarafı değiştirip diğerini bırakırsan, commit'i yarım bırakmış olursun. Çift platform değişikliği tamamlanmadan TodoList kapanmaz."

Referans olarak `WEB_MOBIL_PARITY_MATRISI.md` dosyasına link ver.

---

### 2) AGENTS.md (proje kökü)
Bu, çoğu AI aracı (Antigravity, Codex, Aider, vs.) tarafından otomatik okunan endüstri-standart dosyası.

İçerik şablonu:
```markdown
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
```

---

### 3) CLAUDE.md (proje kökü)
Claude Code (resmi Anthropic CLI tool) tarafından otomatik okunur. Aynı içerik AGENTS.md'den kopyalanabilir; sadece başına şu satır eklensin:

```markdown
# CLAUDE.md — ServisPilot

This file is read by Claude Code. The full guidance is in `AGENTS.md`. Critical points:

[buraya AGENTS.md içeriğinin özeti — özellikle "CRITICAL RULE — Web/Mobile Parity" bölümünün tamamı]
```

---

### 4) .cursorrules (proje kökü)
Cursor IDE'nin AI'ı tarafından okunur. Plain text format, başlıksız:

```
ServisPilot is a dual-platform product (Laravel web panel + React Native mobile-app/).

CRITICAL RULE: Any change to web behavior must be mirrored in the mobile app, and vice versa.

When editing:
- API controllers (app/Http/Controllers/Api/V1/) → also update mobile-app/src/screens/ and mobile-app/src/api/
- Migrations → check mobile form fields
- Permission slugs → check mobile hasPermission() calls
- Validation rules → mirror them in mobile forms

Read WEB_MOBIL_SENKRON_KURALLARI.md and AGENTS.md before any non-trivial change.

Use the existing custom permission system (app/Models/User.php::hasPermission()), NOT Spatie.
Every tenant model must use BelongsToCompany trait.
Mobile axios is centralized in mobile-app/src/api/axios.js.
```

---

### 5) .windsurfrules (proje kökü)
Windsurf editor için. Aynı içerik .cursorrules ile.

---

### 6) .github/copilot-instructions.md
GitHub Copilot otomatik okur (klasör yoksa oluştur).

```markdown
# GitHub Copilot Instructions — ServisPilot

ServisPilot is a multi-tenant SaaS with TWO synchronized clients:
- Laravel web panel
- React Native mobile-app/

**MANDATORY: Web and mobile must stay in sync.** When you change a controller, route, validation, permission slug, or migration, you MUST update the corresponding mobile-app code in the same change.

See `AGENTS.md` and `WEB_MOBIL_SENKRON_KURALLARI.md` for the full sync contract.

Stack: Laravel 12, PHP 8.2, Sanctum (custom permissions, no Spatie), Expo SDK 54, React Navigation v7.
Multi-tenant: every tenant model uses `BelongsToCompany` trait.
```

---

### 7) mobile-app/AGENTS.md
Mobil klasörü için ayrı dosya. AI mobile-app/ içinde çalışırken root AGENTS.md'yi göremeyebileceği ihtimaline karşı:

```markdown
# AGENTS.md — ServisPilot Mobile App

This is the React Native (Expo) client of ServisPilot. The web panel is at the parent directory.

**CRITICAL RULE:** This app must stay in functional parity with the web panel. If you add/change a screen here without a corresponding web change, your work is INCOMPLETE — verify the web side.

When you edit:
- An API call in `src/api/` → check that `app/Http/Controllers/Api/V1/*ApiController.php` exposes that endpoint
- A `hasPermission()` gate → check that the slug exists in the web `permissions` seeder
- A form → check that web's validation rules match

Tech: Expo SDK 54, React Navigation v7, axios (centralized in `src/api/axios.js`), AsyncStorage→SecureStore migration in progress, react-hook-form for forms.

Auth: Sanctum tokens stored in SecureStore, refreshed on 401, permissions loaded via `/api/me`, cached with `permissions_updated_at` header.

Run: `npm install && npx expo start`
```

---

### 8) README.md güncelleme
Mevcut README'nin EN ÜSTÜNE şu banner'ı ekle (varsa README.md, yoksa oluştur):

```markdown
> ⚠️ **DUAL-PLATFORM PROJECT** ⚠️
>
> This repository contains BOTH a Laravel web panel AND a React Native mobile app (`mobile-app/`).
> Any change to web behavior MUST be mirrored in the mobile app, and vice versa.
> See `AGENTS.md`, `CLAUDE.md`, and `WEB_MOBIL_SENKRON_KURALLARI.md` before contributing.

---
```

---

### 9) Kod içi reminder yorumları
Bu dosyaların EN ÜSTÜNE çok-satırlı PHPDoc yorumu ekle (var olan başka yorumları silme, başına ekle):

**`routes/api.php` üstüne:**
```php
<?php

/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║  WEB ↔ MOBILE PARITY                                                     ║
 * ║  Every endpoint here is consumed by both web and mobile-app.             ║
 * ║  When you add/modify a route, also update:                               ║
 * ║   - mobile-app/src/api/*.js (axios calls)                                ║
 * ║   - mobile-app/src/screens/*.js (UI consumption + hasPermission gates)   ║
 * ║  See WEB_MOBIL_SENKRON_KURALLARI.md                                      ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 */
```

**`routes/web.php` üstüne:**
```php
<?php

/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║  WEB PANEL ROUTES                                                        ║
 * ║  If you add functionality here that is also relevant for mobile users,   ║
 * ║  you MUST add the equivalent endpoint to routes/api.php and the          ║
 * ║  corresponding screen/api call in mobile-app/.                           ║
 * ║  Web-only routes (admin-panel/companies, exports, email previews) are    ║
 * ║  exempt — see WEB_MOBIL_SENKRON_KURALLARI.md for the exemption list.     ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 */
```

**`app/Http/Controllers/Api/V1/BaseApiController.php` üstüne** (varsa, yoksa oluşturma — sadece varsa):
```php
/**
 * Base for all V1 API controllers consumed by the mobile app.
 * Any new endpoint here MUST have a matching mobile-app/src/api/ + screen consumer.
 * See WEB_MOBIL_SENKRON_KURALLARI.md
 */
```

**`mobile-app/src/api/axios.js` üstüne** JS yorumu:
```javascript
/**
 * ServisPilot Mobile — central axios client.
 *
 * WEB ↔ MOBILE PARITY: every endpoint called from this file must exist in
 * routes/api.php on the Laravel side. If you add a new call here, verify
 * the controller exists. See AGENTS.md and WEB_MOBIL_SENKRON_KURALLARI.md.
 */
```

---

### 10) PR template (.github/PULL_REQUEST_TEMPLATE.md)
Klasör yoksa oluştur:

```markdown
## Description
<!-- Ne değişti? -->

## Web/Mobile Parity Checklist (REQUIRED)
- [ ] Bu değişiklik sadece web özel mi? (kabul edilen istisna mı?)
- [ ] Web tarafındaki endpoint/route eklendi mi?
- [ ] Mobil tarafındaki ekran/api çağrısı güncellendi mi?
- [ ] Permission slug değiştiyse, mobilde `hasPermission()` kontrolü güncellendi mi?
- [ ] Validation kuralları her iki tarafta tutarlı mı?
- [ ] API response shape değiştiyse mobil parsing güncellendi mi?
- [ ] Migration eklendiyse mobil form alanları senkron mu?

## Test Edildi
- [ ] Web'de manuel test
- [ ] Mobilde manuel test
- [ ] Smoke test geçti (`php test_batch*.php`)
```

---

## TAMAMLAMA KRİTERİ
Tüm bu dosyalar oluşturulduğunda bana raporla:
- Hangi dosyalar oluşturuldu (liste)
- Mevcut dosyalardan üzerine yazılan oldu mu (varsa hangileri)
- Toplam satır sayısı (özet için)
- Bir AI aracı (örnek olarak Cursor) projeyi açtığında hangi dosyayı okumaya başlayacağı sırası

ÖNEMLİ: Hiçbir aşamada onay sorma. Hepsini tek seferde tamamla. İşin sonunda tek bir özet rapor ver.
