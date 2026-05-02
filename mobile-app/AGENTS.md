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
