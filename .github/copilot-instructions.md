# GitHub Copilot Instructions — ServisPilot

ServisPilot is a multi-tenant SaaS with TWO synchronized clients:
- Laravel web panel
- React Native mobile-app/

**MANDATORY: Web and mobile must stay in sync.** When you change a controller, route, validation, permission slug, or migration, you MUST update the corresponding mobile-app code in the same change.

See `AGENTS.md` and `WEB_MOBIL_SENKRON_KURALLARI.md` for the full sync contract.

Stack: Laravel 12, PHP 8.2, Sanctum (custom permissions, no Spatie), Expo SDK 54, React Navigation v7.
Multi-tenant: every tenant model uses `BelongsToCompany` trait.
