# Resend mail driver

**Date:** 2026-04-20
**Status:** Approved

## Goal

Send all outgoing email through Resend in production. Email verification — Fortify's `VerifyEmail` notification dispatched on user registration — must work reliably. Local development continues to use the `log` mailer so dev never sends real mail.

## Non-goals

- No new mail templates, no branded layout, no welcome email, no purchase receipt. The two existing mailables (`AccessExpiryReminderMail`, `AccessRefundedMail`) and Fortify's built-in notifications (`VerifyEmail`, `ResetPassword`) are the full surface.
- No Resend webhooks (bounces, complaints, delivered events). If we want bounce handling later, that is a separate spec.
- No mail-driver abstraction or fallback transport. One driver, selected by env.

## Current state

- `User` implements `MustVerifyEmail`.
- `Features::emailVerification()` is enabled in `config/fortify.php`.
- `verified` middleware guards the protected routes in `routes/web.php` and `routes/settings.php`.
- `config/mail.php` already lists `resend` as a supported transport; `config/services.php` already has a `resend` block reading `RESEND_KEY`.
- `MAIL_MAILER=log` in `.env`; `MAIL_FROM_ADDRESS="hello@example.com"`.
- `QUEUE_CONNECTION=database`. Both existing mailables are `ShouldQueue`. Laravel's `VerifyEmail` and `ResetPassword` notifications (the ones Fortify dispatches) do **not** implement `ShouldQueue` — they are sent synchronously inside the request.
- `resend/resend-php` is **not** installed.

## Design

Use Laravel's built-in `resend` mail transport. Driver selection is by env variable (`MAIL_MAILER`) — no code branching, no service-provider conditional. Local stays on `log`; production sets `MAIL_MAILER=resend` and `RESEND_KEY=...`.

### Changes

1. **Composer dependency**
   - Add `resend/resend-php` via `composer require resend/resend-php`. Required by Laravel's `resend` mail transport.

2. **Environment**
   - `.env.example`:
     - Add `RESEND_KEY=` (empty placeholder).
     - Change `MAIL_FROM_ADDRESS` to `no-reply@pruefungstrainer.de`.
     - Change `MAIL_FROM_NAME` to `"Prüfungstrainer"` (override the `${APP_NAME}` interpolation, which currently resolves to "Laravel").
     - Keep `MAIL_MAILER=log` as the example default.
   - `.env` (local, untracked): no change required — stays on `log`.
   - Production env (set in hosting dashboard, not committed): `MAIL_MAILER=resend`, `RESEND_KEY=re_...`, `MAIL_FROM_ADDRESS=no-reply@pruefungstrainer.de`, `MAIL_FROM_NAME="Prüfungstrainer"`.

3. **No code changes** for the verification flow. Fortify dispatches `VerifyEmail` automatically on registration when the user implements `MustVerifyEmail` and `Features::emailVerification()` is enabled — both already true.

   The verification mail is sent **synchronously** inside the registration request (the default Laravel `VerifyEmail` notification does not implement `ShouldQueue`). With Resend this adds one HTTP call's latency to `POST /register` — acceptable, and a queue worker is not required for verification to work. The two existing mailables (`AccessExpiryReminderMail`, `AccessRefundedMail`) remain queued and still need a worker, as today.

### Tests

One Pest feature test, `tests/Feature/Auth/EmailVerificationDispatchTest.php`:

- `Notification::fake()`, then POST `/register` with valid payload, then `Notification::assertSentTo($user, VerifyEmail::class)`.
- This proves: registration triggers the verification notification, the notification class is correct, and the user record receives it. It does not hit Resend (the fake intercepts at the notification layer), which is correct — the driver is an env concern, not a code concern.

The existing mailable test surface (if any) is unchanged. Driver swap is not testable from PHPUnit without an integration environment, and we explicitly do not want that here.

### Operational prerequisites (out of code scope, but required for production)

These are runbook items, not code changes. Listed here so the implementation plan can flag them.

- `pruefungstrainer.de` is verified in the Resend dashboard (SPF + DKIM DNS records added). Resend rejects sends from unverified domains.
- A Resend API key is created and stored in production env as `RESEND_KEY`.
- A queue worker process runs in production (`php artisan queue:work` or equivalent under a process supervisor). Without it, the two queued mailables (`AccessExpiryReminderMail`, `AccessRefundedMail`) accumulate in the `jobs` table. Verification and password-reset mail are **not** affected — they send synchronously.

## Risks

- **Unverified domain in Resend.** Sends fail with a 403; verification mails never arrive; users cannot complete signup. Mitigated by the runbook prerequisite above.
- **No queue worker in production.** The two queued mailables sit in `jobs` table forever. Verification mail is unaffected (sync). Mitigated by the runbook prerequisite above.
- **Wrong `MAIL_FROM_ADDRESS` env in production.** Resend rejects the send. Mitigated by setting it explicitly in production env, not relying on the example default.

## Rollback

Set `MAIL_MAILER=log` in production env and restart the app + queue workers. No code deploy required.
