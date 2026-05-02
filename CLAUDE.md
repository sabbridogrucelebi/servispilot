# CLAUDE.md — ServisPilot

This file is read by Claude Code. The full guidance is in `AGENTS.md`. Critical points:

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
