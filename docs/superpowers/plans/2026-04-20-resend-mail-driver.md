# Resend Mail Driver Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Send all outgoing email through Resend in production (keeping `log` for local dev) and add a regression test proving `POST /register` dispatches `VerifyEmail`.

**Architecture:** Driver selection by env — set `MAIL_MAILER=resend` + `RESEND_KEY` in production; local stays on `log`. No app-code branching. Install `resend/resend-php` (required by Laravel's built-in `resend` transport). Cover the registration→verification wiring with one Pest feature test so future regressions surface fast.

**Tech Stack:** Laravel 13, Fortify (email verification feature already enabled), `resend/resend-php`, Pest 4.

**Spec:** `docs/superpowers/specs/2026-04-20-resend-mail-driver-design.md`

---

## Task 1: Regression test — registration dispatches VerifyEmail

**Files:**
- Modify: `tests/Feature/Auth/RegistrationTest.php`

This test must exist **before** we touch anything else. It is the acceptance bar for "verifyEmail must work" — it proves the User model, Fortify feature flag, and `Registered` event listener are wired up end-to-end. It uses `Notification::fake()` so it does not hit Resend — that is correct; the driver is an env concern, not a test concern.

- [ ] **Step 1: Add the failing test**

Append to `tests/Feature/Auth/RegistrationTest.php`:

```php
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

test('registration dispatches email verification notification', function () {
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'verify@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'verify@example.com')->firstOrFail();

    Notification::assertSentTo($user, VerifyEmail::class);
});
```

Note: `use App\Models\User;` is already at the top of the file — do not duplicate it. Add `use Illuminate\Auth\Notifications\VerifyEmail;` and `use Illuminate\Support\Facades\Notification;` next to it.

- [ ] **Step 2: Run test to confirm it passes (sanity check, not a failure)**

Run: `php artisan test --compact --filter='registration dispatches email verification notification'`

Expected: PASS. The wiring already exists (User implements `MustVerifyEmail`, `Features::emailVerification()` is on). If this fails, something is already broken in the verification flow — stop and investigate before proceeding.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Auth/RegistrationTest.php
git commit -m "test(auth): assert registration dispatches VerifyEmail notification"
```

---

## Task 2: Install `resend/resend-php`

**Files:**
- Modify: `composer.json`, `composer.lock`

- [ ] **Step 1: Add the package**

Run: `composer require resend/resend-php`

Expected: Composer installs the package and updates `composer.json` + `composer.lock`. No code changes needed — Laravel 13's `resend` mail transport is already registered and picks up the driver when the package class is available.

- [ ] **Step 2: Run the full test suite**

Run: `php artisan test --compact`

Expected: all tests pass. The new package must not break anything (it shouldn't — it only activates when `MAIL_MAILER=resend`, and local is still `log`).

- [ ] **Step 3: Commit**

```bash
git add composer.json composer.lock
git commit -m "chore: add resend/resend-php for Resend mail transport"
```

---

## Task 3: Update `.env.example`

**Files:**
- Modify: `.env.example`

Three changes: add `RESEND_KEY`, set a real From address, set a real From name. Keep `MAIL_MAILER=log` as the example default so fresh clones don't accidentally send real mail.

- [ ] **Step 1: Locate the mail block in `.env.example`**

Run: `grep -n "MAIL_" .env.example`

Expected output (line numbers may differ):

```
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

- [ ] **Step 2: Replace the `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME` lines**

In `.env.example`, change:

```
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

to:

```
MAIL_FROM_ADDRESS="no-reply@pruefungstrainer.de"
MAIL_FROM_NAME="Prüfungstrainer"
```

- [ ] **Step 3: Add the `RESEND_KEY` line immediately after the mail block**

Add a blank line then:

```
RESEND_KEY=
```

Place it directly after `MAIL_FROM_NAME="Prüfungstrainer"`.

- [ ] **Step 4: Verify the result**

Run: `grep -nE "MAIL_|RESEND_" .env.example`

Expected:

```
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="no-reply@pruefungstrainer.de"
MAIL_FROM_NAME="Prüfungstrainer"
RESEND_KEY=
```

- [ ] **Step 5: Commit**

```bash
git add .env.example
git commit -m "chore(env): document RESEND_KEY and set real mail From identity"
```

---

## Task 4: Local smoke test — verify the app still boots on `log` driver

**Files:** none modified

Sanity check before handing off. Confirms no hidden regression from the package install.

- [ ] **Step 1: Confirm local `.env` still has `MAIL_MAILER=log`**

Run: `grep '^MAIL_MAILER=' .env`

Expected: `MAIL_MAILER=log`

If it already says `resend`, stop — that is not what the user asked for. Revert it to `log`.

- [ ] **Step 2: Run lint + full test suite one last time**

Run: `vendor/bin/pint --dirty --format agent && php artisan test --compact`

Expected: Pint reports no changes (nothing PHP was edited by hand; the `composer require` may have touched `composer.json` but that's not a file Pint formats). All tests pass.

- [ ] **Step 3: No commit required** — nothing changed in this task.

---

## Production runbook (out of code scope — user performs in hosting dashboard)

These are **not plan tasks** — the agent cannot do them. List them in the PR description so the user knows what's required before the feature works in production:

1. Verify `pruefungstrainer.de` in the Resend dashboard: add the SPF and DKIM DNS records Resend provides, wait for verification.
2. Create a Resend API key, copy the value.
3. In production env (not committed), set:
   - `MAIL_MAILER=resend`
   - `RESEND_KEY=re_...` (the key from step 2)
   - `MAIL_FROM_ADDRESS=no-reply@pruefungstrainer.de`
   - `MAIL_FROM_NAME="Prüfungstrainer"`
4. Deploy. Confirm a queue worker is already running (it must be, for the existing `AccessExpiryReminderMail` / `AccessRefundedMail` flows — verification mail sends synchronously, so it does not depend on the worker).
5. Smoke test: register a new account on production with a real inbox, confirm the verification email arrives within a minute.

### Rollback

If production email sending breaks: set `MAIL_MAILER=log` in production env and restart app + workers. No code revert needed.
