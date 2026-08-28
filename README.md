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

## Architecture

The moderation flow — submit, queue, approve — is built as **ports and adapters**. Its rules
live in `app/Domain` as plain PHP: no Eloquent, no facades, no `Illuminate` import anywhere
inside the directory, which `LayerBoundaryTest` enforces on every run.

The rule that matters is expressed in the type system rather than defended by a line inside a
controller. `CollectionPoint::submit()` has no status parameter, so no caller — HTTP, console
or queue — can ask for a point that is born approved. Before the refactor that guarantee was a
single assignment in the middle of a controller action.

| Piece | Role |
|---|---|
| `Domain/CollectionPoint` | Entity, `Coordinates` value object, enums, and the repository **port** |
| `Application/CollectionPoint` | One class per use case, depending only on the port |
| `Infrastructure/Persistence` | The Eloquent **adapter** that satisfies the port |
| `Infrastructure/Http/Presenter` | Turns an entity into the JSON shape the SPA expects |
| `Http/Controllers` | Validates the request shape, calls a use case, returns the response |

`AppServiceProvider` is the single place where the port meets the adapter — one `bind()` call.
Swapping the storage engine means editing that line; no use case changes.

**The CRUD routes were deliberately left on plain MVC.** Listing, editing and deleting a point
carry no rules of their own, and wrapping an `UPDATE` in three layers buys indirection and
nothing else. The layering went where the business rule is and stopped there — four routes,
not the whole application.

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

> The entrypoint writes the compose database credentials into `.env` on every boot. This is
> required because `artisan serve` forwards only a fixed allowlist of environment variables
> to the server process it spawns, so `DB_HOST` would otherwise never reach the running app.
> If you also run the project outside Docker, expect those lines to be rewritten.

## Running without Docker

Requirements: **PHP 8.4+**, **Composer**, **Node.js 20+**, and a reachable **PostgreSQL 16**.

```bash
composer run setup      # installs dependencies, creates .env, generates the key, migrates and builds assets
php artisan db:seed     # creates the team user and loads the collection points
composer run dev        # starts the server, queue worker, logs and Vite together
```

Then open **http://localhost:8000**.

## Tests

Two suites, and the split is deliberate.

**Unit** — the moderation rules exercised against an in-memory implementation of the
repository port. No database, no migrations, no framework boot, so the whole suite finishes in
tens of milliseconds and can run on every save.

```bash
php artisan test --testsuite=Unit
```

**Feature** — the same behaviour through the real HTTP stack and PostgreSQL: a submitted point
cannot approve itself, an unapproved point never reaches the public map, admin routes are
unreachable without a valid token, and approving a point publishes it.

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
  Domain/CollectionPoint/  # entity, Coordinates, enums, repository port — plain PHP, no framework
  Application/CollectionPoint/   # use cases: submit, approve, list published, list pending
  Infrastructure/Persistence/    # Eloquent adapter satisfying the repository port
  Infrastructure/Http/Presenter/ # entity -> the JSON shape the SPA consumes
  Http/Controllers/        # public API (points, login) and Admin (approval/CRUD)
  Http/Middleware/         # AuthenticateApiToken — Bearer token guard for admin routes
  Models/                  # CollectionPoint (persistence only), User
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
tests/
  Unit/Domain/             # entity rules and the layer-boundary check, no database
  Unit/Application/        # use cases driven through the in-memory port
  Support/                 # InMemoryCollectionPointRepository — the second adapter
  Feature/                 # approval workflow and authentication, against PostgreSQL
```
