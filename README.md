# EcoDescarte ♻️

[![CI](https://github.com/DevoidTuna/ecodescarte/actions/workflows/ci.yml/badge.svg)](https://github.com/DevoidTuna/ecodescarte/actions/workflows/ci.yml)

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
| Backend | PHP 8.4, Laravel 13 (REST API) |
| Frontend | Vue 3 + Vuetify 3 + Vue Router (SPA), Vite |
| Map | Leaflet + OpenStreetMap |
| Database | PostgreSQL 16 |
| Containers | Docker Compose |
| CI | GitHub Actions — feature tests against PostgreSQL, plus asset and image builds |

Laravel serves the SPA from a single Blade view — all screen routing happens client-side.
Communication goes through a JSON API under `/api`, split into public routes (browsing and
submitting points) and Bearer-token-protected routes for administration.

Two decisions worth calling out:

- **Token auth is a custom `AuthenticateApiToken` middleware** rather than a package. Only
  the SHA-256 hash of a token is persisted; the raw value exists solely on the client, so a
  database leak does not hand over live sessions. The prototype gets the property it needs
  without an OAuth dependency it does not.
- **PostgreSQL in development, tests and CI alike.** Tests run against the same engine as
  production rather than an in-memory SQLite, so dialect differences — the `json` column on
  `waste_types`, decimal coordinate precision — cannot pass locally and fail in production.

## Running with Docker

```bash
docker compose up --build
```

That is the entire setup. The container entrypoint creates `.env`, generates the app key,
waits for PostgreSQL, runs the migrations and seeds the collection points before starting
the server — a fresh clone goes from nothing to a populated map in one command. Seeding is
idempotent (`firstOrCreate`), so restarting never duplicates points or overwrites edits made
in the admin area.

The app is served at **http://localhost:8000**. Compose starts PostgreSQL alongside it and
creates both the development and the test database on first boot.

## Running without Docker

Requirements: **PHP 8.4+**, **Composer**, **Node.js 20+**, and a reachable **PostgreSQL 16**.

```bash
composer run setup      # installs dependencies, creates .env, generates the key, migrates and builds assets
php artisan db:seed     # creates the team user and loads the collection points
composer run dev        # starts the server, queue worker, logs and Vite together
```

Then open **http://localhost:8000**.

## Tests

The suite covers the moderation boundary end to end: a submitted point cannot approve
itself, an unapproved point never reaches the public map, admin routes are unreachable
without a valid token, and approving a point publishes it.

```bash
docker compose up -d db     # the suite needs PostgreSQL running
php artisan test
```

Tests use the `ecodescarte_test` database declared in `.env.testing`, kept separate from the
development one so `RefreshDatabase` never touches local data.

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
  factories/               # CollectionPointFactory, UserFactory
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
tests/Feature/             # approval workflow and authentication
```
