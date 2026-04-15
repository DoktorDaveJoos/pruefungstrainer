# Polar Payments Integration — Design

**Date:** 2026-04-15
**Status:** Approved for planning
**Scope:** Replace the in-progress custom Polar scaffolding with the `danestves/laravel-polar` package. Ship one-time checkout for a 12-month access license, with two price tiers (founder €29 / standard €49) and a guest-friendly buy flow.

---

## 1. Goals & non-goals

### Goals
- Accept real payments via Polar (sandbox first, then production).
- One-time purchase grants the buyer 12 months of access to paid features (wrong-answer review, free-practice mode).
- Preserve the existing guest-exam flow: users can take a full simulation without an account, and only need to register at the "Unlock" moment.
- Keep the founder-pricing promise (€29 for the first 100 paying customers, €49 afterwards) intact.
- Reach feature parity with the current scaffolding before switching DNS-level traffic — no loss of the guest→user session-claim flow, no loss of the `LockedPreview` UX.

### Non-goals
- Recurring subscriptions or auto-renewals.
- Multi-currency (EUR only; Polar handles VAT).
- In-app discount codes (can layer Polar's native discounts later with zero app-side work).
- Magic-link auth (Fortify password-reset covers the "forgot password" edge case).

---

## 2. What gets deleted, what gets added

### Deleted (custom scaffolding superseded by the package)
- `app/Services/PolarCheckout.php`
- `app/Services/PolarWebhookProcessor.php`
- `CheckoutController::webhook()` action
- `users.paid_at` column (and its index)
- `users.polar_customer_id` column
- `User::isPaid()` helper
- The `polar` block in `config/services.php` (moved into the package's `config/polar.php`)
- The `/webhooks/polar` route (replaced by package-default `/polar/webhook`)

### Kept (orthogonal to payments)
- `app/Services/Pricing.php` — founder vs. standard price logic (internals swap; interface unchanged)
- `app/Http/Middleware/EnsurePaid.php` — renamed to `EnsureActiveAccess`; checks `hasActiveAccess()`
- `resources/js/components/locked-preview.tsx` — copy changes from "Lifetime" to "12 Monate"
- The `session_uuid` cookie + `ExamAttemptFinder` guest-attempt mechanism (unchanged)
- Founder cap counting logic (rewired to count via the package's `Order` model)

### Added
- `danestves/laravel-polar` composer package
- `Billable` trait on `App\Models\User`
- Package-installed tables: `customers`, `orders`, `subscriptions`, `subscription_items`, `checkouts`
- Two Polar products (founder + standard) configured in the Polar dashboard
- Env vars: `POLAR_ACCESS_TOKEN`, `POLAR_WEBHOOK_SECRET`, `POLAR_PRODUCT_FOUNDER`, `POLAR_PRODUCT_STANDARD`
- `App\Listeners\PolarEventListener` — handles `OrderCreated` + `OrderRefunded`
- `User::hasActiveAccess()` method — replaces `isPaid()`
- Sheet-based inline register/login form on the results screen for the guest checkout path
- Custom `RegisterResponse` + `LoginResponse` contracts honoring `?intent=checkout`
- Scheduled `SendAccessExpiryReminder` job — day -14 and day -1 before expiry

---

## 3. Data model changes

### Migrations (in order)
1. Package install: `php artisan polar:install` publishes migrations for `customers`, `orders`, `subscriptions`, `subscription_items`, `checkouts`.
2. `drop_paid_at_from_users` — drops the index, then the column.
3. `drop_polar_customer_id_from_users` — drops the column (polymorphic join on `customers.billable_id` / `billable_type` replaces it).

**Backfill:** none needed. `database-query` confirms no production Polar env vars are set; no real orders exist yet; these columns are empty in practice.

**Order of deploy:**
1. Deploy code that computes `Pricing::currentPrice()` founder count via the package's `Order` table *before* dropping `paid_at`.
2. Run `drop_paid_at_from_users` + `drop_polar_customer_id_from_users` migrations after the code is verified live.

### No user schema additions
The package's Billable trait works via the polymorphic `customers.billable_*` join. No columns added to `users`. `email` (unique) and `name` already exist and drive `polarEmail()` / `polarName()` by default.

---

## 4. Product & pricing configuration

### Polar dashboard (sandbox first, then production)
- **Prüfungstrainer — Founder (12 Monate Zugang)** — €29, one-time payment, no recurring
- **Prüfungstrainer — Standard (12 Monate Zugang)** — €49, one-time payment, no recurring

### Environment variables
```
POLAR_ACCESS_TOKEN=polar_at_...
POLAR_WEBHOOK_SECRET=whsec_...
POLAR_PRODUCT_FOUNDER=prod_...
POLAR_PRODUCT_STANDARD=prod_...
POLAR_PATH=polar
```

### Cache-safe config layer
The package's `polar:install` publishes `config/polar.php`. We append a `products` block to it:
```php
// config/polar.php (appended, after publishing)
'products' => [
    'founder'  => env('POLAR_PRODUCT_FOUNDER'),
    'standard' => env('POLAR_PRODUCT_STANDARD'),
],
```

Access via `config('polar.products.founder')` — never `env()` at runtime (breaks under `config:cache`).

### `Pricing` service changes
```php
public function currentPrice(): array
{
    $founderCount = Order::query()
        ->where('product_id', config('polar.products.founder'))
        ->whereNull('refunded_at')
        ->count();
    $isFounder = $founderCount < self::FOUNDER_CAP;
    // returns amount_eur, standard_price_eur, is_founder_price, spots_remaining
}

public function currentProductId(): string
{
    return $this->currentPrice()['is_founder_price']
        ? config('polar.products.founder')
        : config('polar.products.standard');
}
```

### Founder cap accounting
- Counts all-time non-refunded founder orders. Once a founder slot is sold, it stays sold even after that customer's 12-month window lapses.
- Refunds *do* reopen a slot (a refunded order didn't "really" happen).
- Accepted race: at the 99→100 boundary two concurrent buyers can both receive founder pricing, producing 101 total founder orders. Documented and accepted.

### Access check
```php
public function hasActiveAccess(): bool
{
    return $this->orders()
        ->whereIn('product_id', [
            config('polar.products.founder'),
            config('polar.products.standard'),
        ])
        ->whereNull('refunded_at')
        ->where('ordered_at', '>=', now()->subYear())
        ->exists();
}
```

---

## 5. User flows

### 5.1 New guest → first purchase
1. Guest takes a full simulation (`session_uuid` cookie tracks attempts).
2. Lands on `/pruefungssimulation/{attempt}/ergebnis`. Sees `LockedPreview` over the wrong-answer review.
3. Clicks "12 Monate Zugang freischalten · {currentPrice} €".
4. Sheet slides in with tabs: **Neu hier** (register, default) / **Schon Konto** (login).
5. Submits register form (email, password, password_confirm, optional name) with `?intent=checkout&attempt={uuid}` in the query.
6. Fortify's `CreateNewUser` creates the row; `Auth::login()` fires; custom `RegisterResponse` sees `intent=checkout` and redirects to `/checkout/start?attempt={uuid}`.
7. A listener on Laravel's `Registered` event (plus a corresponding `Login` listener for the login tab) claims any attempts with `session_uuid = cookie` to `user_id = new user`. Both are implemented in `App\Listeners\ClaimGuestExamAttempts`.
8. `CheckoutController::start()` calls `$user->checkout([$pricing->currentProductId()])` with the Polar success URL and session metadata; returns a redirect to Polar.
9. User completes checkout on Polar.
10. Polar redirects to `/checkout/success?attempt={uuid}&checkout_id=...`.
11. Polar fires `order.created` webhook in parallel → package persists `Order` row → `PolarEventListener::handleOrderCreated` runs:
    - Safety-net session claim (idempotent with step 7).
    - Dispatches `SendAccessExpiryReminder` jobs delayed to day -14 and day -1.
12. Success page re-fetches attempt data from server. `hasActiveAccess()` now true → `LockedPreview` gone, wrong-answer review visible. Small success `Alert` at top: "Zugang aktiviert".

### 5.2 Returning user flows
- **Logged in, no active access:** skip the Sheet, go straight from "Unlock" to `/checkout/start`.
- **Logged in, active access:** no `LockedPreview`, no CTA — the review renders inline.
- **Logged in, expired access:** same as "no active access" — they see the CTA with whatever `Pricing::currentPrice()` says (almost certainly standard price by then).
- **Logged out but has active access (cookies cleared):** Sheet → "Schon Konto" tab → login. Custom `LoginResponse` sees `intent=checkout`, checks `hasActiveAccess()`, and if true redirects to results page *without* calling `/checkout/start`. Prevents double-charge.

### 5.3 Webhook-vs-success-page race
- **Webhook lands first:** success page loads, `hasActiveAccess()` already true, renders review directly.
- **Success page lands first:** polls a small `/api/access-status` endpoint every 500ms for up to 10s (using Inertia's `router.reload({ only: [...] })` or a plain `fetch` in a hook). Falls back to "Zahlung wird verarbeitet — du erhältst gleich eine E-Mail" copy if still false after 10s.

---

## 6. Webhook handling

### Listener
`App\Listeners\PolarEventListener` implements `ShouldQueue` (keeps webhook ack <500ms regardless of listener work).

Handles:
- `OrderCreated` — safety-net session claim; schedule expiry-reminder jobs
- `OrderRefunded` — send refund-confirmation email; no state to clear (`hasActiveAccess()` re-reads orders live)

Laravel 11+ auto-discovers listeners by event type on handler methods. No `EventServiceProvider` wiring needed.

### Events explicitly ignored
- `SubscriptionCreated/Updated/Active/Canceled/Revoked` — not used; no-op if ever misfired.
- `BenefitGrant*` — not used.

### Idempotency
Package's `orders` table has a unique constraint on `polar_id`. Re-delivered webhooks from Polar insert nothing the second time. No app-side dedup table needed.

### Signature verification
Handled entirely by the package's `WebhookController`. Invalid signatures return 403 before our listener runs.

---

## 7. Access-expiry reminders

### Job
`App\Jobs\SendAccessExpiryReminder` — takes a user + the original `ordered_at`. Sends an email via Resend with re-purchase CTA.

### Dispatch
From the `OrderCreated` listener:
```php
SendAccessExpiryReminder::dispatch($user, $order->ordered_at)
    ->delay($order->ordered_at->addYear()->subDays(14));
SendAccessExpiryReminder::dispatch($user, $order->ordered_at)
    ->delay($order->ordered_at->addYear()->subDay());
```

### Guard
Each job re-checks `hasActiveAccess()` at run time — if the user already bought again in the meantime (new order within the last year), skip sending. Prevents "your access expires" emails to currently-active users.

---

## 8. Testing strategy

All tests use Pest + `RefreshDatabase`. No real Polar HTTP calls — mock via `Http::fake()` for outbound, synthetic webhook POSTs for inbound.

### Unit / service-level
1. `Pricing::currentPrice()` — founder vs. standard thresholds; refunded orders not counted; `spots_remaining` calculation.
2. `User::hasActiveAccess()` — matrix of (no orders / founder recent / standard recent / expired / refunded / other-user's-order).

### Feature / integration
3. Guest registration → checkout redirect — asserts correct `product_id` sent to Polar HTTP call; asserts Polar redirect URL returned.
4. Logged-in without access → `/checkout/start` — correct redirect.
5. Logged-in with active access → `/checkout/start` — redirects to results page, no Polar call.
6. Webhook `order.created` with valid signature — Order row created, session attempts claimed, reminder jobs queued (`Queue::assertPushed` with delay assertion).
7. Webhook `order.created` with bad signature — 403, no Order row.
8. Duplicate `order.created` (same polar_id) — no duplicate Order row.
9. Webhook `order.refunded` — `refunded_at` set, `hasActiveAccess()` returns false, refund email sent.

### Boundary tests
10. Order dated exactly 365 days ago → `hasActiveAccess()` false.
11. Order dated 364 days ago → true.
12. `SendAccessExpiryReminder` job short-circuits when user has a newer order.

### Browser smoke (Pest 4)
13. Guest results page → click Unlock → Sheet visible → submit register form → assert redirect to a stub Polar domain.

### Factories added
- `OrderFactory` for the package's `orders` table (varying `product_id`, `ordered_at`, `refunded_at`)
- `UserFactory::hasActiveAccess()` state (seeds a 30-day-old founder order)
- `UserFactory::hasExpiredAccess()` state (seeds a 400-day-old founder order)

### Explicit non-targets
- Polar's hosted checkout UI
- Package internals
- The 99→100 founder-cap race (accepted behavior, see §4)

---

## 9. Risks & mitigations

| Risk | Mitigation |
| --- | --- |
| Webhook lands *after* success page | Success page polls `hasActiveAccess()` 10s before falling back to "wird verarbeitet" copy |
| User pays, closes browser, returns later | Webhook already landed; `hasActiveAccess()` true on next login. No UX work needed |
| Email divergence (Polar email ≠ app email) | `billable_id` FK links the order to the app user; email is cosmetic only |
| Founder cap race at 99→100 | Accepted; at most 1 over cap; no DB locking |
| Orphan users from abandoned registrations | Accepted; cheap rows; re-entry just repeats the flow |
| Refund mid-year | `hasActiveAccess()` re-reads live; user loses review on next page load; refund email explains |
| Polar API outage during checkout | Catch exception in `CheckoutController::start`, render "Zahlung vorübergehend nicht möglich" error state, log to Sentry |
| Test env missing `POLAR_*` vars | `.env.testing` ships non-empty stub values; HTTP always mocked in tests |
| Package webhook runs sync on slow listener work | Listener implements `ShouldQueue`; verify in sandbox that Polar sees <500ms acks |

---

## 10. Deployment sequence

1. Install package, publish config + migrations, run `polar:install` locally.
2. Implement code changes (listener, Billable trait, access helper, Pricing refactor, custom auth responses, Sheet UI, success page polling).
3. Delete custom scaffolding (PolarCheckout, PolarWebhookProcessor, Checkout webhook action, old config block, old route).
4. Ship tests green on CI.
5. Create founder + standard products in the Polar **sandbox**.
6. Set sandbox env vars on staging; smoke-test the full flow end-to-end (real sandbox checkout).
7. Create founder + standard products in **production** Polar.
8. Set production env vars; run migrations that drop `paid_at` + `polar_customer_id`.
9. Announce founder pricing; monitor.

CSRF-exclusion for `polar/*` in `bootstrap/app.php` is handled as part of step 2 (code changes), not a separate deploy step.

---

## 11. Open items for the implementation plan

- Confirm whether `danestves/laravel-polar`'s webhook controller queues or runs sync (per §9).
- Confirm whether the package's `hasPurchasedProduct` excludes refunded orders by default — if not, our `hasActiveAccess()` already handles this explicitly.
- Decide exact copy for the access-expiry emails (coordinate with existing transactional email voice).
- Settle the Sheet's visual: tabs vs. toggle vs. segmented control — deferred to the UI step of the implementation plan.
