# Roblox Account Checker

A Laravel-backed public game-profile index. The root page is a game launcher; selecting a supported title opens its own account tools. All application pages are React components served through Inertia; Laravel routes and services continue to handle Roblox profile lookup, inventory, watchlists, history, exports, and the public API.

## Stack

- Laravel 12 and Inertia.js
- React 18, Vite, and Tailwind CSS
- React Three Fiber / drei for a single lazy-loaded background scene
- Framer Motion, Lenis, and GSAP ScrollTrigger

## Local setup

Requirements: PHP 8.2+, Composer, Node.js, and npm.

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
composer run dev
```

`composer run dev` starts the Laravel server, queue listener, log tail, and Vite development server. If `.env` already exists, skip `Copy-Item`.

For frontend-only development, run `npm run dev`; use `php artisan serve` in a second terminal.

## React structure

- `resources/js/Pages/` — game index, Roblox lookup, MLBB preview, dashboard, compare, watchlist, history, API docs, and error pages.
- `resources/js/Components/` — Shared search, profile, inventory, modal, navigation, and chart components.
- `resources/js/Layouts/` — Shared Inertia application shell and navigation.
- `resources/js/Components/GlobalScene.jsx` — One lazy-loaded desktop 3D scene, with a static mobile/reduced-motion fallback.
- `resources/views/app.blade.php` — Inertia document mount only; no route-specific Blade views remain.

The `/mlbb` page is an account-report UI preview for Mobile Legends player ID and server ID. There is no MLBB data API configured yet, so account lookup, ratings, skin counts, skin catalogues, and top-up estimates are explicitly unavailable. Its JSON and SVG downloads are marked unverified and contain no fabricated account data.
The `/` route is the expandable game index, `/roblox` opens the existing Roblox lookup, and `/mlbb` opens the Mobile Legends preview. The MLBB and Roblox tiles use user-supplied images at `public/images/games/`; other titles use locally hosted, original genre-inspired vector illustrations. Other game tiles are clearly marked as coming soon until their account tools are implemented.

User-facing Roblox content comes from the existing app or actual Roblox API responses. Missing public profile data is shown as unavailable rather than fabricated.
