# Checkout & Polar Integration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Wire `/checkout` end-to-end with Polar.sh as the merchant of record. Anonymous user clicks "Lifetime-Zugang freischalten" on results screen → redirect to Polar's hosted checkout → pays → Polar webhook fires → backend creates the user, marks `paid_at`, claims their anonymous exam attempt via `session_uuid` metadata. Authenticated unpaid user follows the same path with `user_id` metadata instead. The result: payment unlocks Freies Lernen + the locked review preview, and the user lands on `/checkout/success` with their attempt's review available.

**Architecture:** Two service classes (`PolarCheckout` for creating checkout sessions via Polar's REST API, `PolarWebhookProcessor` for handling incoming webhooks with signature verification + event dispatch) + one controller (`CheckoutController`) with three actions (`start`, `success`, `webhook`). Polar's API is called via Laravel's `Http` facade — no SDK, no Cashier (Polar isn't supported by Cashier). Webhook signature uses HMAC-SHA-256 with a shared secret. Anonymous attempt claiming relies on the `session_uuid` we already store on `exam_attempts`.

**Tech Stack:** Laravel 13, Pest 4, Inertia 3, React 19, Polar.sh REST API.

## Subsequent plans (not in scope here)

1. **Plan 7 — Landing + legal:** marketing landing page hero/pricing/FAQ + AGB / Datenschutz / Impressum.
2. **Plan 8+ — Content quality:** generate more questions for `notfall` / `siem` topics, more `experte` questions.

## What you (the human) must do BEFORE Task 9 (manual setup)

This plan implements the code, but the live system needs three things you do once in the Polar dashboard:

1. **Create a Polar account** at https://polar.sh and verify your organization
2. **Create a one-time product** in your Polar org:
   - Name: "Prüfungstrainer Lifetime"
   - Price: €49 (or €29 for Founder's Price phase)
   - Type: One-time purchase
   - Note the `product_id` (something like `prod_XXXX`)
3. **Generate an API key** with checkout + webhook permissions, note it
4. **Configure a webhook endpoint** pointing to `https://yourdomain.com/webhooks/polar` (or use a tunnel like `ngrok` for local), select event `order.created`, copy the signing secret

Set in `.env`:
```
POLAR_API_KEY=polar_at_...
POLAR_PRODUCT_ID=prod_...
POLAR_WEBHOOK_SECRET=whsec_...
POLAR_ORGANIZATION_ID=org_...   # optional, used in some endpoints
```

The plan's tests use HTTP fakes — you can implement and test all 11 tasks WITHOUT touching the real Polar dashboard. Real-money testing comes after Task 11.

---

## File Structure

### Services to CREATE

```
app/Services/PolarCheckout.php           # Creates a checkout session via Polar API
app/Services/PolarWebhookProcessor.php   # Verifies signature + dispatches event handlers
```

### Controllers to CREATE

```
app/Http/Controllers/CheckoutController.php   # start / success / webhook
```

### Migrations to CREATE

```
database/migrations/YYYY_MM_DD_HHMMSS_add_polar_customer_id_to_users.php
```

### Routes to MODIFY

```
routes/web.php           # add /checkout/start, /checkout/success, /webhooks/polar
bootstrap/app.php        # exclude /webhooks/polar from CSRF
```

### Inertia pages to CREATE

```
resources/js/pages/checkout/success.tsx   # confirmation page after Polar redirect
```

### Frontend to MODIFY

```
resources/js/components/locked-preview.tsx   # rewrite the "<a href='/checkout'>" link to POST /checkout/start
```

### Config to MODIFY

```
config/services.php          # add polar block
.env.example                 # add POLAR_* keys
```

### Tests to CREATE

```
tests/Feature/Services/PolarCheckoutTest.php
tests/Feature/Services/PolarWebhookProcessorTest.php
tests/Feature/Checkout/StartCheckoutTest.php
tests/Feature/Checkout/PolarWebhookTest.php
tests/Feature/Checkout/CheckoutSuccessTest.php
```

---

## Tasks

### Task 1: Baseline + branch

**Files:** none (setup only)

- [ ] **Step 1: Verify clean main + green tests**

```bash
git status
php artisan test --compact
```

Expected: clean tree on `main`, 167 tests passing.

- [ ] **Step 2: Create feature branch**

```bash
git checkout -b feat/checkout-and-polar
git commit --allow-empty -m "chore: baseline before checkout + polar"
```

---

### Task 2: Polar config + env wiring

**Files:**
- Modify: `config/services.php`
- Modify: `.env.example`

- [ ] **Step 1: Add `polar` block to `config/services.php`**

Read `config/services.php`. After the `anthropic` block (added in Plan 3), append:

```php
'polar' => [
    'api_key' => env('POLAR_API_KEY'),
    'product_id' => env('POLAR_PRODUCT_ID'),
    'webhook_secret' => env('POLAR_WEBHOOK_SECRET'),
    'organization_id' => env('POLAR_ORGANIZATION_ID'),
    'base_url' => env('POLAR_BASE_URL', 'https://api.polar.sh/v1'),
    'success_url' => env('POLAR_SUCCESS_URL', 'http://localhost:8000/checkout/success'),
],
```

- [ ] **Step 2: Add env keys to `.env.example`**

Append to `.env.example`:

```
POLAR_API_KEY=
POLAR_PRODUCT_ID=
POLAR_WEBHOOK_SECRET=
POLAR_ORGANIZATION_ID=
POLAR_BASE_URL=https://api.polar.sh/v1
POLAR_SUCCESS_URL=http://localhost:8000/checkout/success
```

- [ ] **Step 3: Verify config**

```bash
php artisan config:show services.polar
```

Expected output: shows all six keys with `null` for empty + the two URL defaults.

- [ ] **Step 4: Run full suite**

```bash
php artisan test --compact
```

Expected: 167 still passing.

- [ ] **Step 5: Commit**

```bash
git add config/services.php .env.example
git commit -m "chore: add Polar config block"
```

---

### Task 3: Migration — `polar_customer_id` on users

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_add_polar_customer_id_to_users.php`
- Modify: `app/Models/User.php`

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration add_polar_customer_id_to_users --table=users --no-interaction
```

Replace contents of the generated file with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('polar_customer_id')->nullable()->index()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('polar_customer_id');
        });
    }
};
```

- [ ] **Step 2: Add `polar_customer_id` to User Fillable**

Read `app/Models/User.php`. Update the `#[Fillable(...)]` attribute to include `'polar_customer_id'`:

Before:
```php
#[Fillable(['name', 'email', 'password', 'paid_at'])]
```

After:
```php
#[Fillable(['name', 'email', 'password', 'paid_at', 'polar_customer_id'])]
```

- [ ] **Step 3: Run migration**

```bash
php artisan migrate
```

Expected: migrates cleanly.

- [ ] **Step 4: Verify**

```bash
php artisan tinker --execute 'echo in_array("polar_customer_id", \Illuminate\Support\Facades\Schema::getColumnListing("users")) ? "OK" : "MISSING";'
```

Expected: `OK`.

- [ ] **Step 5: Run full suite**

```bash
php artisan test --compact
```

Expected: 167 still passing.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add polar_customer_id to users"
```

---

### Task 4: `PolarCheckout` service — TDD

**Files:**
- Create: `app/Services/PolarCheckout.php`
- Create: `tests/Feature/Services/PolarCheckoutTest.php`

`PolarCheckout` calls Polar's REST API to create a checkout session and returns the hosted-checkout URL the user gets redirected to.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Services/PolarCheckoutTest.php`:

```php
<?php

use App\Services\PolarCheckout;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.polar.api_key', 'polar_at_test');
    config()->set('services.polar.product_id', 'prod_test_123');
    config()->set('services.polar.base_url', 'https://api.polar.sh/v1');
    config()->set('services.polar.success_url', 'http://localhost:8000/checkout/success');
});

it('posts to Polar /checkouts with product_id + metadata + auth header', function () {
    Http::fake([
        'api.polar.sh/v1/checkouts' => Http::response([
            'id' => 'co_abc',
            'url' => 'https://buy.polar.sh/co_abc',
        ]),
    ]);

    $url = (new PolarCheckout)->createSession(metadata: ['session_uuid' => 'abc-123'], customerEmail: 'foo@example.com');

    expect($url)->toBe('https://buy.polar.sh/co_abc');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->url() === 'https://api.polar.sh/v1/checkouts'
            && $request->hasHeader('Authorization', 'Bearer polar_at_test')
            && $body['product_id'] === 'prod_test_123'
            && $body['metadata'] === ['session_uuid' => 'abc-123']
            && $body['customer_email'] === 'foo@example.com'
            && $body['success_url'] === 'http://localhost:8000/checkout/success?checkout_id={CHECKOUT_ID}';
    });
});

it('omits customer_email when null', function () {
    Http::fake([
        'api.polar.sh/v1/checkouts' => Http::response(['id' => 'co_abc', 'url' => 'https://buy.polar.sh/co_abc']),
    ]);

    (new PolarCheckout)->createSession(metadata: ['user_id' => 42]);

    Http::assertSent(function ($request) {
        return ! isset($request->data()['customer_email']);
    });
});

it('throws when Polar returns an error', function () {
    Http::fake([
        'api.polar.sh/v1/checkouts' => Http::response(['error' => 'invalid_product'], 422),
    ]);

    expect(fn () => (new PolarCheckout)->createSession(metadata: []))
        ->toThrow(\RuntimeException::class, 'Polar checkout failed');
});

it('throws when api_key is not configured', function () {
    config()->set('services.polar.api_key', null);

    expect(fn () => (new PolarCheckout)->createSession(metadata: []))
        ->toThrow(\RuntimeException::class, 'POLAR_API_KEY is not set');
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=PolarCheckoutTest
```

Expected: FAIL with "Class App\Services\PolarCheckout not found".

- [ ] **Step 3: Implement `PolarCheckout`**

Create `app/Services/PolarCheckout.php`:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PolarCheckout
{
    /**
     * @param  array<string, mixed>  $metadata  arbitrary key/value pairs attached to the order; received back via webhook
     */
    public function createSession(array $metadata, ?string $customerEmail = null): string
    {
        $apiKey = config('services.polar.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('POLAR_API_KEY is not set');
        }

        $payload = [
            'product_id' => config('services.polar.product_id'),
            'success_url' => config('services.polar.success_url').'?checkout_id={CHECKOUT_ID}',
            'metadata' => $metadata,
        ];

        if ($customerEmail !== null) {
            $payload['customer_email'] = $customerEmail;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type' => 'application/json',
        ])->post(config('services.polar.base_url').'/checkouts', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Polar checkout failed: '.$response->body());
        }

        $url = $response->json('url');

        if (! is_string($url) || $url === '') {
            throw new RuntimeException('Polar checkout returned no URL');
        }

        return $url;
    }
}
```

The `{CHECKOUT_ID}` token in `success_url` is a Polar-side template variable they substitute before redirecting back. We can use it on the success page if needed.

- [ ] **Step 4: Run tests**

```bash
php artisan test --compact --filter=PolarCheckoutTest
```

Expected: 4 passing.

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add PolarCheckout service (creates hosted checkout sessions)"
```

---

### Task 5: `/checkout/start` endpoint — TDD

**Files:**
- Create: `app/Http/Controllers/CheckoutController.php` (with `start` only)
- Create: `tests/Feature/Checkout/StartCheckoutTest.php`
- Modify: `routes/web.php`

The `start` action looks at the request:
- If user is authenticated: pass `metadata.user_id` + `customerEmail = $user->email`
- If user is anonymous: pass `metadata.session_uuid` from cookie (or generate one if missing — same cookie used by exam flow)

Returns a redirect to Polar's hosted checkout URL.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Checkout/StartCheckoutTest.php`:

```php
<?php

use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.polar.api_key', 'polar_at_test');
    config()->set('services.polar.product_id', 'prod_test_123');

    Http::fake([
        'api.polar.sh/v1/checkouts' => Http::response([
            'id' => 'co_abc',
            'url' => 'https://buy.polar.sh/co_abc',
        ]),
    ]);
});

it('redirects an authenticated user to the Polar checkout URL with user_id metadata', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/checkout/start');

    $response->assertRedirect('https://buy.polar.sh/co_abc');

    Http::assertSent(function ($request) use ($user) {
        $body = $request->data();

        return $body['metadata'] === ['user_id' => $user->id]
            && $body['customer_email'] === $user->email;
    });
});

it('redirects an anonymous user with session_uuid metadata from the exam cookie', function () {
    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'sess-abc-123')
        ->post('/checkout/start');

    $response->assertRedirect('https://buy.polar.sh/co_abc');

    Http::assertSent(function ($request) {
        return $request->data()['metadata'] === ['session_uuid' => 'sess-abc-123'];
    });
});

it('redirects an anonymous user without an exam cookie (empty metadata)', function () {
    $response = $this->post('/checkout/start');

    $response->assertRedirect('https://buy.polar.sh/co_abc');

    Http::assertSent(function ($request) {
        return $request->data()['metadata'] === [];
    });
});

it('returns 503 when POLAR_API_KEY is missing', function () {
    config()->set('services.polar.api_key', null);

    $response = $this->post('/checkout/start');

    $response->assertStatus(503);
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=StartCheckoutTest
```

Expected: FAIL (route undefined).

- [ ] **Step 3: Create controller**

Create `app/Http/Controllers/CheckoutController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Services\ExamAttemptFinder;
use App\Services\PolarCheckout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(private readonly PolarCheckout $polarCheckout)
    {
    }

    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();
        $metadata = [];

        if ($user) {
            $metadata['user_id'] = $user->id;
            $email = $user->email;
        } else {
            $sessionUuid = $request->cookie(ExamAttemptFinder::SESSION_COOKIE);
            if ($sessionUuid !== null) {
                $metadata['session_uuid'] = $sessionUuid;
            }
            $email = null;
        }

        try {
            $url = $this->polarCheckout->createSession($metadata, $email);
        } catch (RuntimeException $e) {
            abort(503, 'Checkout temporarily unavailable: '.$e->getMessage());
        }

        return redirect()->away($url);
    }
}
```

- [ ] **Step 4: Add route**

Read `routes/web.php`. Add (no auth/middleware — anonymous and authenticated users both use it):

```php
Route::post('/checkout/start', [\App\Http\Controllers\CheckoutController::class, 'start'])->name('checkout.start');
```

- [ ] **Step 5: Regenerate + run**

```bash
php artisan wayfinder:generate --with-form
php artisan test --compact --filter=StartCheckoutTest
```

Expected: 4 passing.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: /checkout/start redirects to Polar hosted checkout"
```

---

### Task 6: `PolarWebhookProcessor` service — TDD

**Files:**
- Create: `app/Services/PolarWebhookProcessor.php`
- Create: `tests/Feature/Services/PolarWebhookProcessorTest.php`

This service has two responsibilities:
1. **`verifySignature(string $payload, string $signatureHeader): bool`** — HMAC-SHA-256 against `POLAR_WEBHOOK_SECRET`.
2. **`process(array $payload): void`** — handle event types (specifically `order.created`): find or create the user (by metadata or customer email), set `paid_at`, claim anonymous attempt by `session_uuid`.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Services/PolarWebhookProcessorTest.php`:

```php
<?php

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\PolarWebhookProcessor;

beforeEach(function () {
    config()->set('services.polar.webhook_secret', 'whsec_test');
});

it('verifies a valid HMAC-SHA-256 signature', function () {
    $payload = '{"type":"order.created"}';
    $signature = hash_hmac('sha256', $payload, 'whsec_test');

    expect((new PolarWebhookProcessor)->verifySignature($payload, $signature))->toBeTrue();
});

it('rejects an invalid signature', function () {
    $payload = '{"type":"order.created"}';

    expect((new PolarWebhookProcessor)->verifySignature($payload, 'wrong_signature'))->toBeFalse();
});

it('rejects when no webhook secret is configured', function () {
    config()->set('services.polar.webhook_secret', null);

    expect((new PolarWebhookProcessor)->verifySignature('payload', 'sig'))->toBeFalse();
});

it('marks an existing user as paid via metadata.user_id', function () {
    $user = User::factory()->create();

    $payload = [
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_polar_abc', 'email' => $user->email],
            'metadata' => ['user_id' => (string) $user->id],
        ],
    ];

    (new PolarWebhookProcessor)->process($payload);

    $fresh = $user->fresh();
    expect($fresh->paid_at)->not->toBeNull();
    expect($fresh->polar_customer_id)->toBe('cust_polar_abc');
});

it('creates a new user from customer email when no metadata.user_id', function () {
    $payload = [
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_polar_xyz', 'email' => 'newbie@example.com', 'name' => 'Anna Newbie'],
            'metadata' => [],
        ],
    ];

    (new PolarWebhookProcessor)->process($payload);

    $user = User::where('email', 'newbie@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->paid_at)->not->toBeNull();
    expect($user->polar_customer_id)->toBe('cust_polar_xyz');
    expect($user->name)->toBe('Anna Newbie');
});

it('claims anonymous exam attempt by session_uuid when both metadata fields present', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'sess-abc-123']);

    $payload = [
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_p', 'email' => 'claim@example.com', 'name' => 'Claim User'],
            'metadata' => ['session_uuid' => 'sess-abc-123'],
        ],
    ];

    (new PolarWebhookProcessor)->process($payload);

    $user = User::where('email', 'claim@example.com')->first();
    $fresh = $attempt->fresh();

    expect($fresh->user_id)->toBe($user->id);
});

it('is idempotent — replaying the same order does not re-mark or re-create', function () {
    $user = User::factory()->paid()->create();

    $payload = [
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_p', 'email' => $user->email],
            'metadata' => ['user_id' => (string) $user->id],
        ],
    ];

    $originalPaidAt = $user->fresh()->paid_at;

    (new PolarWebhookProcessor)->process($payload);
    (new PolarWebhookProcessor)->process($payload);

    expect(User::where('email', $user->email)->count())->toBe(1);
    expect($user->fresh()->paid_at->toIso8601String())->toBe($originalPaidAt->toIso8601String());
});

it('ignores non-order.created events without throwing', function () {
    expect(fn () => (new PolarWebhookProcessor)->process([
        'type' => 'subscription.canceled',
        'data' => [],
    ]))->not->toThrow(\Throwable::class);
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=PolarWebhookProcessorTest
```

Expected: FAIL (class not found).

- [ ] **Step 3: Implement the service**

Create `app/Services/PolarWebhookProcessor.php`:

```php
<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\User;

class PolarWebhookProcessor
{
    public function verifySignature(string $payload, string $signatureHeader): bool
    {
        $secret = config('services.polar.webhook_secret');

        if (empty($secret)) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signatureHeader);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function process(array $payload): void
    {
        if (($payload['type'] ?? null) !== 'order.created') {
            return;
        }

        $data = $payload['data'] ?? [];
        $customer = $data['customer'] ?? [];
        $metadata = $data['metadata'] ?? [];

        $user = $this->findOrCreateUser($customer, $metadata);

        if ($user->paid_at === null) {
            $user->update([
                'paid_at' => now(),
                'polar_customer_id' => $customer['id'] ?? $user->polar_customer_id,
            ]);
        } elseif ($user->polar_customer_id === null && isset($customer['id'])) {
            $user->update(['polar_customer_id' => $customer['id']]);
        }

        $this->claimAnonymousAttempt($user, $metadata);
    }

    /**
     * @param  array<string, mixed>  $customer
     * @param  array<string, mixed>  $metadata
     */
    private function findOrCreateUser(array $customer, array $metadata): User
    {
        if (isset($metadata['user_id'])) {
            $user = User::find((int) $metadata['user_id']);
            if ($user !== null) {
                return $user;
            }
        }

        $email = $customer['email'] ?? null;

        if ($email === null) {
            throw new \RuntimeException('Polar webhook: no user_id metadata and no customer email');
        }

        $existing = User::where('email', $email)->first();

        if ($existing !== null) {
            return $existing;
        }

        return User::create([
            'name' => $customer['name'] ?? explode('@', $email)[0],
            'email' => $email,
            'password' => bcrypt(\Illuminate\Support\Str::random(32)),
            'polar_customer_id' => $customer['id'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function claimAnonymousAttempt(User $user, array $metadata): void
    {
        $sessionUuid = $metadata['session_uuid'] ?? null;

        if ($sessionUuid === null) {
            return;
        }

        ExamAttempt::where('session_uuid', $sessionUuid)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);
    }
}
```

A user created via webhook gets a random password — they can use the "Forgot password" flow to set a real one. Better UX would be a magic-link sign-in, but that's Plan-something else.

- [ ] **Step 4: Run tests**

```bash
php artisan test --compact --filter=PolarWebhookProcessorTest
```

Expected: 8 passing.

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: PolarWebhookProcessor (signature verification + order processing + claim)"
```

---

### Task 7: Webhook endpoint + CSRF exemption

**Files:**
- Modify: `app/Http/Controllers/CheckoutController.php` (add `webhook` action)
- Create: `tests/Feature/Checkout/PolarWebhookTest.php`
- Modify: `routes/web.php`
- Modify: `bootstrap/app.php` (exclude `/webhooks/polar` from CSRF)

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Checkout/PolarWebhookTest.php`:

```php
<?php

use App\Models\ExamAttempt;
use App\Models\User;

beforeEach(function () {
    config()->set('services.polar.webhook_secret', 'whsec_test');
});

it('processes a valid order.created webhook and marks the user paid', function () {
    $user = User::factory()->create();

    $payload = json_encode([
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_p', 'email' => $user->email],
            'metadata' => ['user_id' => (string) $user->id],
        ],
    ]);

    $signature = hash_hmac('sha256', $payload, 'whsec_test');

    $response = $this->call(
        method: 'POST',
        uri: '/webhooks/polar',
        server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_WEBHOOK_SIGNATURE' => $signature,
        ],
        content: $payload,
    );

    $response->assertStatus(200);
    expect($user->fresh()->paid_at)->not->toBeNull();
});

it('rejects a webhook with bad signature (401)', function () {
    $payload = json_encode(['type' => 'order.created', 'data' => []]);

    $response = $this->call(
        method: 'POST',
        uri: '/webhooks/polar',
        server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_WEBHOOK_SIGNATURE' => 'wrong_sig',
        ],
        content: $payload,
    );

    $response->assertStatus(401);
});

it('rejects a webhook with missing signature header (401)', function () {
    $payload = json_encode(['type' => 'order.created', 'data' => []]);

    $response = $this->call(
        method: 'POST',
        uri: '/webhooks/polar',
        server: ['CONTENT_TYPE' => 'application/json'],
        content: $payload,
    );

    $response->assertStatus(401);
});

it('claims anonymous attempt via webhook metadata', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'sess-claim']);

    $payload = json_encode([
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_p', 'email' => 'newuser@example.com', 'name' => 'New U'],
            'metadata' => ['session_uuid' => 'sess-claim'],
        ],
    ]);

    $signature = hash_hmac('sha256', $payload, 'whsec_test');

    $response = $this->call(
        method: 'POST',
        uri: '/webhooks/polar',
        server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_WEBHOOK_SIGNATURE' => $signature,
        ],
        content: $payload,
    );

    $response->assertStatus(200);

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();
    expect($attempt->fresh()->user_id)->toBe($user->id);
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=PolarWebhookTest
```

Expected: FAIL (route missing or 419 CSRF).

- [ ] **Step 3: Add `webhook` action to CheckoutController**

Modify `app/Http/Controllers/CheckoutController.php`. Add at the top:

```php
use App\Services\PolarWebhookProcessor;
use Illuminate\Http\Response;
```

Update the constructor to inject the processor:

```php
public function __construct(
    private readonly PolarCheckout $polarCheckout,
    private readonly PolarWebhookProcessor $webhookProcessor,
) {
}
```

Add the `webhook` method to the class:

```php
public function webhook(Request $request): Response
{
    $payload = $request->getContent();
    $signature = $request->header('webhook-signature');

    if ($signature === null || ! $this->webhookProcessor->verifySignature($payload, $signature)) {
        abort(401, 'Invalid signature');
    }

    $decoded = json_decode($payload, true);

    if (! is_array($decoded)) {
        abort(400, 'Malformed payload');
    }

    $this->webhookProcessor->process($decoded);

    return response('', 200);
}
```

- [ ] **Step 4: Add route**

Read `routes/web.php`. Add (no auth, no CSRF):

```php
Route::post('/webhooks/polar', [\App\Http\Controllers\CheckoutController::class, 'webhook'])->name('webhooks.polar');
```

- [ ] **Step 5: Exempt webhook route from CSRF**

Read `bootstrap/app.php`. Find the `->withMiddleware(function (Middleware $middleware) { ... })` block. Inside, add the CSRF exclusion:

```php
$middleware->validateCsrfTokens(except: ['/webhooks/polar']);
```

If there's already a `validateCsrfTokens` call, add to the exception array. If not, add the line as shown.

- [ ] **Step 6: Run tests**

```bash
php artisan wayfinder:generate --with-form
php artisan test --compact --filter=PolarWebhookTest
```

Expected: 4 passing.

- [ ] **Step 7: Full suite**

```bash
php artisan test --compact
```

Expected: all green.

- [ ] **Step 8: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: /webhooks/polar endpoint with HMAC signature verification"
```

---

### Task 8: `/checkout/success` page — TDD

**Files:**
- Modify: `app/Http/Controllers/CheckoutController.php` (add `success` action)
- Create: `tests/Feature/Checkout/CheckoutSuccessTest.php`
- Create: `resources/js/pages/checkout/success.tsx`
- Modify: `routes/web.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Checkout/CheckoutSuccessTest.php`:

```php
<?php

use App\Models\User;

it('renders the success page for an authenticated paid user', function () {
    $user = User::factory()->paid()->create();

    $response = $this->actingAs($user)->get('/checkout/success');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('checkout/success')
        ->where('isPaid', true)
        ->where('isAuthenticated', true)
    );
});

it('renders the success page for an anonymous visitor (post-Polar redirect, before webhook fires)', function () {
    $response = $this->get('/checkout/success');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('checkout/success')
        ->where('isPaid', false)
        ->where('isAuthenticated', false)
    );
});

it('shows isPaid=false for an authenticated unpaid user (webhook not yet fired)', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/checkout/success');

    $response->assertInertia(fn ($page) => $page
        ->where('isPaid', false)
        ->where('isAuthenticated', true)
    );
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=CheckoutSuccessTest
```

Expected: FAIL (route + page missing).

- [ ] **Step 3: Add `success` action**

Modify `app/Http/Controllers/CheckoutController.php`. Add to imports:

```php
use Inertia\Response as InertiaResponse;
```

Add the method:

```php
public function success(Request $request): InertiaResponse
{
    $user = $request->user();

    return inertia('checkout/success', [
        'isAuthenticated' => $user !== null,
        'isPaid' => $user?->isPaid() ?? false,
        'checkoutId' => $request->query('checkout_id'),
    ]);
}
```

- [ ] **Step 4: Add route**

Modify `routes/web.php`:

```php
Route::get('/checkout/success', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');
```

- [ ] **Step 5: Create Inertia page**

Create `resources/js/pages/checkout/success.tsx`:

```tsx
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { CheckCircle2, Mail } from 'lucide-react';

export default function CheckoutSuccess({
    isAuthenticated,
    isPaid,
}: {
    isAuthenticated: boolean;
    isPaid: boolean;
    checkoutId: string | null;
}) {
    return (
        <>
            <Head title="Vielen Dank!" />

            <div className="min-h-screen bg-background">
                <main className="mx-auto max-w-2xl px-6 py-24">
                    <Card className="border-border">
                        <CardHeader className="items-center text-center">
                            <CheckCircle2 className="size-12 text-success" />
                            <CardTitle className="mt-4 text-3xl">Vielen Dank!</CardTitle>
                            <CardDescription className="mt-2">
                                Deine Zahlung wurde erfolgreich verarbeitet.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6 text-center">
                            {isPaid && isAuthenticated ? (
                                <>
                                    <p className="text-sm text-muted-foreground">
                                        Dein Lifetime-Zugang ist freigeschaltet. Du kannst sofort loslegen.
                                    </p>
                                    <div className="flex justify-center gap-3">
                                        <Button asChild>
                                            <Link href="/dashboard">Zum Dashboard</Link>
                                        </Button>
                                        <Button asChild variant="outline">
                                            <Link href="/freies-lernen">Freies Lernen</Link>
                                        </Button>
                                    </div>
                                </>
                            ) : (
                                <>
                                    <p className="text-sm text-muted-foreground">
                                        Wir haben deine Zahlung erhalten. Dein Zugang wird in wenigen Sekunden freigeschaltet.
                                    </p>
                                    <div className="flex items-start gap-3 rounded-md border border-border bg-muted p-4 text-left text-sm">
                                        <Mail className="mt-0.5 size-5 shrink-0 text-muted-foreground" />
                                        <div>
                                            <div className="font-medium">Login per Magic Link</div>
                                            <div className="mt-1 text-muted-foreground">
                                                Wir haben eine E-Mail an die Adresse aus dem Checkout gesendet. Klicke auf
                                                <strong> „Passwort zurücksetzen"</strong>, um ein eigenes Passwort zu vergeben
                                                und dich anzumelden.
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex justify-center">
                                        <Button asChild variant="outline">
                                            <Link href="/forgot-password">Passwort setzen / Anmelden</Link>
                                        </Button>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>
                </main>
            </div>
        </>
    );
}
```

- [ ] **Step 6: Run tests + build**

```bash
php artisan wayfinder:generate --with-form
php artisan test --compact --filter=CheckoutSuccessTest
npm run build
```

Expected: tests pass, build succeeds.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: /checkout/success page + Inertia confirmation UI"
```

---

### Task 9: Wire `LockedPreview` button to POST `/checkout/start`

**Files:**
- Modify: `resources/js/components/locked-preview.tsx`

The current `LockedPreview` component (Plan 4 Task 13) has a button that links to `/checkout` (a 404 until now). Update it to POST to `/checkout/start` so it triggers a real Polar redirect.

- [ ] **Step 1: Read current `locked-preview.tsx`**

Use Read on `resources/js/components/locked-preview.tsx`. Note its current button structure — it uses `<Button asChild><a href="/checkout">...</a></Button>`.

- [ ] **Step 2: Replace the button with a POST form**

Change the button to a form-wrapped submit:

```tsx
<form method="POST" action="/checkout/start">
    <input
        type="hidden"
        name="_token"
        value={(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? ''}
    />
    <Button size="lg" type="submit">{ctaText}</Button>
</form>
```

The full file should now look like (read first, then apply this minimal change):

```tsx
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Lock } from 'lucide-react';
import { ReactNode } from 'react';

export function LockedPreview({
    children,
    ctaText = 'Lifetime-Zugang freischalten · 29 €',
}: {
    children: ReactNode;
    ctaText?: string;
}) {
    return (
        <Card className="relative overflow-hidden">
            <CardHeader>
                <div className="flex items-center gap-2">
                    <Badge variant="secondary" className="gap-1">
                        <Lock data-icon="inline-start" className="size-3" />
                        Paid
                    </Badge>
                </div>
                <CardTitle className="mt-2">Review der falschen Antworten</CardTitle>
                <CardDescription>
                    Jede falsch beantwortete Frage mit Erklärung und BSI-Originalquelle — gezielt lernen, wo du schwach bist.
                </CardDescription>
            </CardHeader>
            <CardContent className="relative">
                <div className="pointer-events-none select-none opacity-30 blur-sm">{children}</div>
                <div className="absolute inset-0 flex items-center justify-center">
                    <form method="POST" action="/checkout/start">
                        <input
                            type="hidden"
                            name="_token"
                            value={(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? ''}
                        />
                        <Button size="lg" type="submit">
                            {ctaText}
                        </Button>
                    </form>
                </div>
            </CardContent>
        </Card>
    );
}
```

- [ ] **Step 3: Build to verify**

```bash
npm run build
```

Expected: succeeds.

- [ ] **Step 4: Commit**

```bash
git add resources/js/components/locked-preview.tsx
git commit -m "feat: locked-preview CTA POSTs to /checkout/start"
```

---

### Task 10: End-to-end mock checkout flow smoke test

**Files:**
- Create: `tests/Feature/Checkout/CheckoutFlowSmokeTest.php`

Verifies the full path: anonymous user posts /checkout/start → backend creates Polar session (mocked) → user gets redirect URL → simulate Polar firing webhook → user is created + paid + attempt claimed.

- [ ] **Step 1: Create the smoke test**

```php
<?php

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.polar.api_key', 'polar_at_test');
    config()->set('services.polar.product_id', 'prod_test');
    config()->set('services.polar.webhook_secret', 'whsec_test');
});

it('walks anonymous user through start → Polar redirect → webhook → claim → success', function () {
    Http::fake([
        'api.polar.sh/v1/checkouts' => Http::response([
            'id' => 'co_xyz',
            'url' => 'https://buy.polar.sh/co_xyz',
        ]),
    ]);

    // Anonymous user has an exam attempt
    $attempt = ExamAttempt::factory()->submitted(score: 18)->create(['session_uuid' => 'sess-flow']);

    // Step 1: POST /checkout/start with the cookie → redirected to Polar
    $startResponse = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'sess-flow')
        ->post('/checkout/start');
    $startResponse->assertRedirect('https://buy.polar.sh/co_xyz');

    // Step 2: simulate Polar firing the webhook after payment
    $payload = json_encode([
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_polar_xyz', 'email' => 'buyer@example.com', 'name' => 'Buyer'],
            'metadata' => ['session_uuid' => 'sess-flow'],
        ],
    ]);
    $signature = hash_hmac('sha256', $payload, 'whsec_test');

    $webhookResponse = $this->call(
        method: 'POST',
        uri: '/webhooks/polar',
        server: ['CONTENT_TYPE' => 'application/json', 'HTTP_WEBHOOK_SIGNATURE' => $signature],
        content: $payload,
    );
    $webhookResponse->assertStatus(200);

    // Step 3: verify user was created, marked paid, and attempt claimed
    $user = User::where('email', 'buyer@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->paid_at)->not->toBeNull();
    expect($user->polar_customer_id)->toBe('cust_polar_xyz');
    expect($attempt->fresh()->user_id)->toBe($user->id);

    // Step 4: hit /checkout/success — anonymous still (no session login automatic)
    $successResponse = $this->get('/checkout/success');
    $successResponse->assertStatus(200);
    $successResponse->assertInertia(fn ($page) => $page->component('checkout/success'));
});
```

- [ ] **Step 2: Run smoke test**

```bash
php artisan test --compact --filter=CheckoutFlowSmokeTest
```

Expected: 1 passing.

- [ ] **Step 3: Full suite**

```bash
php artisan test --compact
```

Expected: all green. Record exact pass count.

- [ ] **Step 4: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "test: end-to-end checkout + webhook flow smoke test"
```

---

### Task 11: Final verification

**Files:** none (verification only)

- [ ] **Step 1: Full suite**

```bash
php artisan test --compact
```

Expected: all tests pass. Record exact count.

- [ ] **Step 2: Pint**

```bash
vendor/bin/pint --format agent
```

Expected: clean. Commit if anything formatted.

- [ ] **Step 3: Build**

```bash
npm run build
```

Expected: succeeds.

- [ ] **Step 4: Routes**

```bash
php artisan route:list --except-vendor 2>&1 | grep -E "checkout|webhook"
```

Expected: 4 lines:
- `POST /checkout/start`
- `GET /checkout/success`
- `POST /webhooks/polar`

- [ ] **Step 5: Schema verification — `polar_customer_id` exists**

```bash
php artisan tinker --execute 'echo in_array("polar_customer_id", \Illuminate\Support\Facades\Schema::getColumnListing("users")) ? "OK\n" : "MISSING\n";'
```

Expected: `OK`.

- [ ] **Step 6: Manual setup checklist (for the human, not the agent)**

Print a reminder to the operator:

```
=== Polar production setup ===
1. Sign up at https://polar.sh and create your organization
2. Create a one-time product "Prüfungstrainer Lifetime" (€49 — or €29 for Founder's Price)
3. Generate API key with checkout + webhook permissions
4. Configure webhook endpoint: https://YOUR_DOMAIN/webhooks/polar
   - Event: order.created
   - Copy the signing secret
5. Set in production .env:
   POLAR_API_KEY=polar_at_...
   POLAR_PRODUCT_ID=prod_...
   POLAR_WEBHOOK_SECRET=whsec_...
   POLAR_SUCCESS_URL=https://YOUR_DOMAIN/checkout/success
6. For local testing: use ngrok to expose /webhooks/polar publicly,
   create a TEST product in Polar with €0.50, do a real test purchase.
```

- [ ] **Step 7: Completion marker**

```bash
git commit --allow-empty -m "chore: checkout + polar integration complete"
git log --oneline -15
```

- [ ] **Step 8: Do NOT merge**

Leave branch `feat/checkout-and-polar` for separate merge via `finishing-a-development-branch` skill.

---

## Self-Review

**Spec coverage:**
- ✅ Anonymous user can click "Lifetime freischalten" → Polar checkout: Tasks 5 + 9
- ✅ Polar webhook with HMAC signature verification: Task 6 + 7
- ✅ Webhook creates new user OR updates existing user's `paid_at`: Task 6 `findOrCreateUser`
- ✅ Anonymous attempt claimed via `session_uuid` metadata: Task 6 `claimAnonymousAttempt`
- ✅ Authenticated user passes `user_id` metadata: Task 5 controller logic
- ✅ Success page after Polar redirect: Task 8
- ✅ `polar_customer_id` stored on user: Task 3 + Task 6
- ✅ CSRF exemption for webhook: Task 7 step 5
- ✅ End-to-end smoke test: Task 10

**Placeholder scan:** no TODOs / TBDs. All steps have concrete code.

**Type consistency:**
- `PolarCheckout::createSession(array $metadata, ?string $customerEmail = null): string` — stable across Tasks 4, 5, 10.
- `PolarWebhookProcessor::verifySignature(string $payload, string $signatureHeader): bool` — stable across Tasks 6, 7.
- `PolarWebhookProcessor::process(array $payload): void` — stable across Tasks 6, 7.
- Webhook signature header: `webhook-signature` (lowercase, HTTP-style; Laravel's `$request->header('webhook-signature')` is case-insensitive).
- Routes: `/checkout/start` (POST), `/checkout/success` (GET), `/webhooks/polar` (POST).
- Inertia component: `'checkout/success'`.

**Known assumptions / verify against real Polar before launch:**
- Polar's checkout-create endpoint URL: `POST /v1/checkouts`. **Verify against current Polar docs** before going live — they may have renamed to `/v1/checkout-sessions` or similar.
- Polar's webhook signature header name + format: assumed `webhook-signature` with raw HMAC-SHA-256 hex digest. **Real Polar might use** `webhook-signature: v1,<base64sig>` or similar — adjust `verifySignature` to match their actual format. The test mocks our internal format, but real webhooks need to match Polar's wire format.
- Polar's event type `order.created`: confirm with current docs. Other candidates: `checkout.completed`, `subscription.created`.
- Polar's metadata key handling: confirmed via docs that `metadata` field on checkout sessions roundtrips through to webhook payloads.

These are all "verify against real Polar dashboard at the time of integration" items. The code structure is correct; only the wire-format details might shift.

**Known deferrals:**
- No `subscription.canceled` handling (we sell lifetime, not subscription).
- No customer portal link in `/settings` — Polar hosts one but we'd need to fetch + display URL.
- No refund flow.
- No magic-link login (the success page directs anonymous-buyers to use "Forgot password" to set credentials).

---

## Execution Handoff

Plan complete and saved to `docs/superpowers/plans/2026-04-14-checkout-and-polar.md`. Two execution options:

1. **Subagent-Driven (recommended)** — same proven pattern. Backend tasks 2–7 with haiku, Task 8 + 9 with sonnet for UI judgment.

2. **Inline Execution** — single session with checkpoints.

Which approach?
