# In-App Analytics Tracker — Design

**Date:** 2026-04-22
**Status:** Approved for planning
**Author:** David

## Goal

Answer two questions, without paying for or self-hosting a third-party analytics service:

1. **Is anyone visiting the site at all?**
2. **Do visitors reach the exam simulation, and how far do they get in the register → pay → start-exam → complete-exam funnel?**

Non-goals: real-time streaming, geo breakdowns, session replay, A/B testing, heatmaps, bot detection beyond a trivial UA filter.

## Constraints

- Zero external cost. No SaaS, no additional infra.
- No cookie banner. Tracking must be anonymous enough to fall outside ePrivacy/GDPR cookie-consent requirements (no device fingerprints stored, no long-lived identifier, no cookies set for tracking).
- SQLite-compatible. The app runs on SQLite in production.
- Solo-operator scale — expect <10k pageviews/day for the foreseeable future. Schema and queries can be naive.
- Owner-only dashboard. Only `drdavejoos@gmail.com` should see insights.

## Architecture

Two tables, one middleware, one event listener per domain event, one admin Inertia page.

### Data model

**`page_views`** — one row per tracked HTTP request.

| Column          | Type           | Notes                                                             |
| --------------- | -------------- | ----------------------------------------------------------------- |
| `id`            | bigint PK      |                                                                   |
| `visitor_hash`  | char(64)       | SHA256 of `IP + UA + APP_KEY + date(today)`. Rotates daily.       |
| `path`          | string(512)    | Normalized request path, e.g. `/pruefungssimulation/{attempt}`.    |
| `referrer_host` | string(255) null| Host part of `Referer` header, null if same-origin or missing.   |
| `user_id`       | bigint null FK | Logged-in user, nullable for guests.                              |
| `created_at`    | timestamp      |                                                                   |

Indexes: `(created_at)`, `(path, created_at)`, `(visitor_hash, created_at)`.

**`tracked_events`** — one row per named event (`registered`, `paid`, `exam_started`, `exam_completed`).

| Column         | Type           | Notes                                                       |
| -------------- | -------------- | ----------------------------------------------------------- |
| `id`           | bigint PK      |                                                             |
| `name`         | string(64)     | Enum-ish: see Events section below.                         |
| `visitor_hash` | char(64) null  | Same hash format as `page_views`. Null for server-only events without a request context. |
| `user_id`      | bigint null FK |                                                             |
| `metadata`     | json null      | Small payload — e.g. `{ "attempt_id": 42, "score": 32 }`.   |
| `created_at`   | timestamp      |                                                             |

Indexes: `(name, created_at)`, `(visitor_hash, created_at)`.

### Why a hashed visitor ID rather than a cookie?

The hash is derived per-request from IP + UA + app secret + today's date. It gives us "unique visitor today" approximation without setting any cookie or persistent identifier on the user's device. It rotates daily, so it can't link a visitor across days — which also means cross-day retention metrics are out of scope (acceptable trade-off).

Under GDPR/TTDSG this is defensible as a statistical counter, not a cookie-equivalent identifier, and requires no consent banner. We still document it in `/datenschutz`.

### Path normalization

Dynamic segments collapse to their route parameter name to avoid cardinality blowup.

- `/pruefungssimulation/42` → `/pruefungssimulation/{attempt}`
- `/pruefungssimulation/42/ergebnis` → `/pruefungssimulation/{attempt}/ergebnis`

Implemented by reading the matched route's URI from the request (`$request->route()->uri()`), not by regexing the raw path.

### Middleware — `TrackPageView`

Registered on the `web` middleware group. Runs as **terminable** middleware (`terminate()` method) so it logs *after* the response is sent and does not add latency to the user-facing request.

Skip logic (early return, no DB write) when any of:

- Request method is not `GET`.
- Response status is not 2xx or 3xx.
- Response is not HTML (no `text/html` in `Content-Type`).
- Path matches a skip list: `/api/*`, `/livewire/*`, `/_debugbar/*`, `/up`, Pulse-style asset paths, webhooks.
- User-Agent matches a short bot regex (`/bot|crawler|spider|preview|lighthouse/i`).
- Path is itself `/admin/insights` (don't pollute metrics with owner usage).

### Domain events

Named events are written synchronously from the controllers / Fortify actions that already handle each transition. A dedicated service `App\Services\Analytics\RecordEvent` keeps call sites to one line.

| Event              | Recorded at                                                            | `metadata` payload                         |
| ------------------ | ---------------------------------------------------------------------- | ------------------------------------------- |
| `registered`       | `App\Actions\Fortify\CreateNewUser` after `User::create`.              | `null`                                      |
| `paid`             | Polar webhook handler that sets the paid state on the user.            | `{ "order_id": "..." }` if available        |
| `exam_started`     | `ExamController@start` after a new `ExamAttempt` is persisted.         | `{ "attempt_id": 42 }`                      |
| `exam_completed`   | `ExamController@submit` after the attempt is finalized with a score.   | `{ "attempt_id": 42, "score": 32, "total": 50 }` |

Only the fields listed above are written to `metadata` — no PII, no free-form blobs.

The service resolves `visitor_hash` from the current request (same hash as the middleware would produce) when invoked in an HTTP context, and sets `user_id` from `Auth::id()`. Webhook-originated events (no request context from the end user, e.g., Polar confirming payment) fall back to `user_id` only, `visitor_hash` null.

### Timezone

All "today" and "last 7 days" bucketing uses the app's configured timezone (`config('app.timezone')`). `created_at` stays in UTC on disk per Laravel convention; controller queries convert at query time.

### Admin dashboard

Route: `GET /admin/insights`, Inertia page `admin/insights`.

Gated by a new middleware `EnsureOwner` that 404s anyone whose email is not `drdavejoos@gmail.com`. The email is read from config (`config('app.owner_email')`) with the default set in `config/app.php`, so test environments can override it.

Page layout (single Inertia page, no sub-routes):

- **Overview strip** — four KPI cards: pageviews today, unique visitors today, exam starts today, exam completions today.
- **Last-7-days chart** — daily pageviews + unique visitors, shadcn `Chart`.
- **Funnel card** — five stacked rows for last 7 days: `visited /` → `registered` → `paid` → `exam_started` → `exam_completed`, each as a count with the percentage of the previous step.
- **Top pages** — top 10 normalized paths in last 7 days with view counts.
- **Top referrers** — top 10 referrer hosts in last 7 days (guest traffic only, `user_id IS NULL`), counts.

All queries executed in the controller (`App\Http\Controllers\Admin\InsightsController`). No caching for v1 — solo dev loading a page a few times a day does not need it.

### Retention

A scheduled command `analytics:prune` runs daily at 03:00 server time and deletes rows older than 90 days from both tables. Keeps the SQLite file small.

## Testing

Feature tests (Pest):

- Middleware records a row on a GET HTML response and skips for POST, non-HTML, 4xx, `/api/*`, bot UA.
- Path normalization collapses `/pruefungssimulation/42` to `/pruefungssimulation/{attempt}`.
- Visitor hash is stable within the same calendar day for the same IP+UA, different the next day.
- Each domain event writes a `tracked_events` row with the correct `name` and `user_id`.
- `/admin/insights` returns 404 for a non-owner user, 200 for the owner, redirects guests to login.
- Prune command deletes rows older than 90 days and leaves younger rows alone.

Unit tests:

- `VisitorHash` service — determinism and daily rotation (freeze time).
- Path normalization helper.

## Rollout

One migration creates both tables. Deploy, then verify by loading the landing page once and confirming one `page_views` row and the admin dashboard renders. No feature flag needed; the feature is invisible to end users.

## Out of scope (explicit)

- Cross-day retention cohorts.
- Geo/country breakdown.
- Live updates — dashboard reflects whatever was in the DB at page-load time.
- Session-level analytics or full funnels by individual visitor.
- Exports (CSV). Query SQLite directly if ever needed.
- Caching / materialized aggregates. Revisit if the insights page ever feels slow.
