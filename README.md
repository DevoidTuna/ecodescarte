# EcoDescarte ♻️

A collaborative map of proper waste-disposal points across **Brazil**.

Anyone can look up where to drop off a given type of waste — batteries, cooking oil,
electronics, light bulbs, construction debris, medication — and submit new collection
points. Submissions stay pending until the team approves them in the admin area.

## Features

- **Public map** (`/`) — approved collection points rendered on a Leaflet/OpenStreetMap
  map, filterable by waste type.
- **Community submissions** — any visitor can register a new point, which enters the
  queue as *pending*.
- **Team area** (`/admin`) — username/password login, listing of pending and approved
  points, with approve, edit and delete actions.
- **Real seed data** — the seeder loads an initial set of genuine collection points
  (Itajaí region, Santa Catarina), which then grows through user contributions from
  anywhere in the country.

## Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3, Laravel 13 (REST API) |
| Frontend | Vue 3 + Vuetify 3 + Vue Router (SPA), Vite |
| Map | Leaflet + OpenStreetMap |
| Database | SQLite (`database/database.sqlite`) |

Laravel serves the SPA from a single Blade view — all screen routing happens client-side.
Communication goes through a JSON API under `/api`, split into public routes (browsing and
submitting points) and Bearer-token-protected routes for administration. Token auth is
handled by a custom `AuthenticateApiToken` middleware rather than a package, keeping the
prototype free of an OAuth dependency it doesn't need.

## Running locally

Requirements: **PHP 8.3+**, **Composer**, **Node.js 20+**.

```bash
composer run setup      # installs dependencies, creates .env, generates the key, migrates and builds assets
php artisan db:seed     # creates the team user and loads the collection points
composer run dev        # starts the server, queue worker, logs and Vite together
```

Then open **http://localhost:8000**.

> If port 8000 is taken, use `php artisan serve --port=8001` (alongside `npm run dev` in
> another terminal) and browse to `localhost`, not `127.0.0.1`.

If `migrate` complains that the database doesn't exist, create the file first:
`touch database/database.sqlite`.

## Team area access

Go to **/admin** and sign in with the demo credentials:

- **Username:** `admin`
- **Password:** `admin`

> Fixed credentials, for prototype evaluation only.

## Project layout

```
app/
  Http/Controllers/        # public API (points, login) and Admin (approval/CRUD)
  Http/Middleware/         # AuthenticateApiToken — Bearer token guard for admin routes
  Models/                  # CollectionPoint, User
database/
  migrations/              # collection points table + auth columns
  seeders/                 # admin user + real collection points for Itajaí and region
resources/js/
  App.vue, router.js       # SPA shell and routes (/ and /admin)
  views/MapView.vue        # public map with waste-type filters
  views/AdminView.vue      # team dashboard
  components/PointFormDialog.vue
  wasteTypes.js            # waste type catalogue (icons and colours)
routes/
  api.php                  # API routes, public and token-protected
  web.php                  # fallback that serves the SPA
```
