# Polar Package Integration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the custom Polar scaffolding with `danestves/laravel-polar`. Sell 12-month access via two one-time products (Founder €29 / Standard €49). Guests register inline at the "Unlock" moment, then pay. Access grants/revocations are computed live from the package's `orders` table.

**Architecture:** Package-provided Billable trait on `User`. Access check = any non-refunded order for founder/standard product within the last 365 days. Founder cap counted via `Order` table. Guest flow: Sheet-based email+password registration on the results page, Fortify handles auth, post-auth claim listener links anonymous `session_uuid` exam attempts to the new user, custom Fortify response contracts redirect `?intent=checkout` to `/checkout/start`. Webhook via package; our `PolarEventListener` schedules expiry-reminder emails and sends refund confirmations.

**Tech Stack:** Laravel 13, Pest 4, Inertia 3 + React 19, Fortify, `danestves/laravel-polar`.

**Spec:** `docs/superpowers/specs/2026-04-15-polar-integration-design.md`

---

## What you (the human) must do BEFORE Task 19 (live checkout verification)

1. Create a **Polar sandbox organization** at <https://sandbox.polar.sh> (if you don't already have one).
2. In the sandbox dashboard create **two one-time products**:
   - `Prüfungstrainer — Founder (12 Monate Zugang)` — €29
   - `Prüfungstrainer — Standard (12 Monate Zugang)` — €49
   - Copy each `prod_...` id.
3. Generate a **sandbox access token** (Settings → Developers).
4. Configure a **sandbox webhook** (Settings → Webhooks) pointing to `https://<your-ngrok>.ngrok.app/polar/webhook`. Subscribe to: `order.created`, `order.updated`, `order.refunded`. Copy the signing secret.
5. Set in `.env`:
   ```
   POLAR_ACCESS_TOKEN=polar_oat_...
   POLAR_WEBHOOK_SECRET=whsec_...
   POLAR_PRODUCT_FOUNDER=prod_...
   POLAR_PRODUCT_STANDARD=prod_...
   POLAR_PATH=polar
   ```

Tasks 1–18 are fully testable with HTTP fakes. Real sandbox traffic begins at Task 19.

---

## File Structure

### Package-installed (via `php artisan polar:install`)
```
config/polar.php                                    # package config, extend with products
database/migrations/*_create_customers_table.php    # polymorphic billable join
database/migrations/*_create_orders_table.php       # one row per Polar order
database/migrations/*_create_subscriptions_table.php            # unused but installed
database/migrations/*_create_subscription_items_table.php       # unused but installed
database/migrations/*_create_checkouts_table.php    # transient checkout records
```

### New files
```
app/Listeners/ClaimGuestExamAttempts.php            # links session_uuid attempts post-auth
app/Listeners/PolarEventListener.php                # OrderCreated + OrderRefunded
app/Jobs/SendAccessExpiryReminder.php               # day -14 / -1 emails
app/Mail/AccessExpiryReminderMail.php
app/Mail/AccessRefundedMail.php
app/Http/Responses/RegisterResponse.php             # overrides Fortify, honors intent=checkout
app/Http/Responses/LoginResponse.php                # same
app/Http/Middleware/EnsureActiveAccess.php          # renamed from EnsurePaid
app/Http/Controllers/AccessStatusController.php     # tiny endpoint for success-page polling
database/factories/OrderFactory.php                 # for testing
resources/js/components/checkout-sheet.tsx          # inline register/login Sheet on results
resources/js/pages/checkout/processing.tsx          # success page with access-polling fallback (rewrite of existing success.tsx if present)
database/migrations/YYYY_MM_DD_HHMMSS_drop_paid_at_from_users.php
database/migrations/YYYY_MM_DD_HHMMSS_drop_polar_customer_id_from_users.php
```

### Modified files
```
composer.json                                       # add danestves/laravel-polar
config/polar.php                                    # append `products` block
config/services.php                                 # remove `polar` block
app/Models/User.php                                 # add Billable, remove paid_at/polar_customer_id, add hasActiveAccess
app/Services/Pricing.php                            # count via Order model, add currentProductId
app/Http/Controllers/CheckoutController.php         # rewrite start, rewrite success, delete webhook
app/Providers/FortifyServiceProvider.php            # bind custom response contracts
routes/web.php                                      # remove /webhooks/polar, update middleware name, add /api/access-status
bootstrap/app.php                                   # CSRF exclusion polar/*
database/factories/UserFactory.php                  # remove paid_at state, add hasActiveAccess / hasExpiredAccess states
resources/js/components/locked-preview.tsx          # copy update, open Sheet instead of POST /checkout/start
resources/js/pages/exam/results.tsx                 # mount CheckoutSheet
resources/js/pages/welcome.tsx                      # pricing card copy
```

### Deleted files
```
app/Services/PolarCheckout.php
app/Services/PolarWebhookProcessor.php
app/Http/Middleware/EnsurePaid.php
resources/js/pages/checkout/success.tsx            # if it exists; replaced by processing.tsx
tests/Feature/Services/PolarWebhookProcessorTest.php
tests/Feature/Checkout/CheckoutFlowSmokeTest.php   # replaced by new flow test
tests/Feature/Checkout/CheckoutSuccessTest.php     # replaced by new processing-page test
tests/Feature/Models/UserPaidAtTest.php            # replaced by UserHasActiveAccessTest
```

---

## Task 1: Install the `danestves/laravel-polar` package

**Files:**
- Modify: `composer.json`
- Create: `config/polar.php` (via publish)
- Create: 5 package migrations in `database/migrations/`

- [ ] **Step 1: Require the package**

```bash
composer require danestves/laravel-polar
```

Expected: composer installs without errors; `composer.json` `require` section now contains `"danestves/laravel-polar": "^1.0"` (or current major).

- [ ] **Step 2: Run the package installer**

```bash
php artisan polar:install --no-interaction
```

This publishes config + migrations and runs them. If it prompts, answer yes.

- [ ] **Step 3: Verify migrations landed**

```bash
php artisan migrate:status | grep -iE "customers|orders|subscriptions|checkouts"
```

Expected: five new tables show `Ran`.

- [ ] **Step 4: Commit**

```bash
git add composer.json composer.lock config/polar.php database/migrations/
git commit -m "feat: install danestves/laravel-polar package"
```

---

## Task 2: Append product configuration to `config/polar.php`

**Files:**
- Modify: `config/polar.php`

- [ ] **Step 1: Append products block**

Add the following block to the returned array in `config/polar.php` (place it after the existing `currency_locale` key):

```php
    /*
    |--------------------------------------------------------------------------
    | Product IDs
    |--------------------------------------------------------------------------
    |
    | The Polar product IDs for the Prüfungstrainer access tiers.
    |
    */
    'products' => [
        'founder'  => env('POLAR_PRODUCT_FOUNDER'),
        'standard' => env('POLAR_PRODUCT_STANDARD'),
    ],
```

- [ ] **Step 2: Add env var stubs for local + testing**

In `.env.example` add:
```
POLAR_ACCESS_TOKEN=
POLAR_WEBHOOK_SECRET=
POLAR_PRODUCT_FOUNDER=
POLAR_PRODUCT_STANDARD=
POLAR_PATH=polar
```

In `phpunit.xml` add these as `<env>` entries with stub values (needed for tests that boot config):

```xml
<env name="POLAR_ACCESS_TOKEN" value="test_token"/>
<env name="POLAR_WEBHOOK_SECRET" value="test_secret"/>
<env name="POLAR_PRODUCT_FOUNDER" value="prod_test_founder"/>
<env name="POLAR_PRODUCT_STANDARD" value="prod_test_standard"/>
```

- [ ] **Step 3: Verify config reads**

```bash
php artisan tinker --execute 'dump(config("polar.products"));'
```

Expected: `array(2) { ["founder"]=> NULL ["standard"]=> NULL }` (nulls OK locally until real ids are set).

- [ ] **Step 4: Commit**

```bash
git add config/polar.php .env.example phpunit.xml
git commit -m "feat: add polar product config block"
```

---

## Task 3: Remove the old custom `polar` config block from `config/services.php`

**Files:**
- Modify: `config/services.php`

- [ ] **Step 1: Remove the polar block**

Open `config/services.php` and delete lines 44–51 (the `'polar' => [...]` block).

- [ ] **Step 2: Verify app still boots**

```bash
php artisan config:clear && php artisan route:list --except-vendor | head
```

Expected: no errors.

- [ ] **Step 3: Commit**

```bash
git add config/services.php
git commit -m "refactor: drop custom polar config block (superseded by polar package)"
```

---

## Task 4: Add `Billable` trait to `User` + `hasActiveAccess()` method

**Files:**
- Modify: `app/Models/User.php`
- Test: `tests/Feature/Models/UserHasActiveAccessTest.php` (new)

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/Models/UserHasActiveAccessTest.php`:

```php
<?php

use App\Models\User;
use Danestves\LaravelPolar\Order;

it('returns false when the user has no orders', function (): void {
    $user = User::factory()->create();

    expect($user->hasActiveAccess())->toBeFalse();
});

it('returns true when a founder order was placed within the last year', function (): void {
    $user = User::factory()->create();
    Order::factory()->for($user, 'billable')->create([
        'product_id' => config('polar.products.founder'),
        'ordered_at' => now()->subDays(30),
        'refunded_at' => null,
    ]);

    expect($user->hasActiveAccess())->toBeTrue();
});

it('returns true when a standard order was placed within the last year', function (): void {
    $user = User::factory()->create();
    Order::factory()->for($user, 'billable')->create([
        'product_id' => config('polar.products.standard'),
        'ordered_at' => now()->subDays(200),
        'refunded_at' => null,
    ]);

    expect($user->hasActiveAccess())->toBeTrue();
});

it('returns false when the only order is older than 365 days', function (): void {
    $user = User::factory()->create();
    Order::factory()->for($user, 'billable')->create([
        'product_id' => config('polar.products.founder'),
        'ordered_at' => now()->subDays(366),
        'refunded_at' => null,
    ]);

    expect($user->hasActiveAccess())->toBeFalse();
});

it('returns false when the only order is refunded', function (): void {
    $user = User::factory()->create();
    Order::factory()->for($user, 'billable')->create([
        'product_id' => config('polar.products.founder'),
        'ordered_at' => now()->subDays(10),
        'refunded_at' => now()->subDays(5),
    ]);

    expect($user->hasActiveAccess())->toBeFalse();
});

it('does not leak access from another user', function (): void {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Order::factory()->for($other, 'billable')->create([
        'product_id' => config('polar.products.founder'),
        'ordered_at' => now()->subDays(30),
        'refunded_at' => null,
    ]);

    expect($me->hasActiveAccess())->toBeFalse();
});
```

- [ ] **Step 2: Run to verify failure**

```bash
php artisan test --compact --filter=UserHasActiveAccessTest
```

Expected: FAIL. Either `hasActiveAccess` is undefined or `OrderFactory` is missing — both will be built in this task and Task 6.

- [ ] **Step 3: Modify `app/Models/User.php`**

Replace the current class body. Final shape:

```php
<?php

namespace App\Models;

use Danestves\LaravelPolar\Billable;
use Danestves\LaravelPolar\Order;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password'])]
class User extends Authenticatable implements MustVerifyEmail
{
    use Billable, HasFactory, HasTimestamps, Notifiable, TwoFactorAuthenticatable;

    protected $hidden = ['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function practiceAnswers(): HasMany
    {
        return $this->hasMany(PracticeAnswer::class);
    }

    public function hasActiveAccess(): bool
    {
        return Order::query()
            ->where('billable_id', $this->id)
            ->where('billable_type', self::class)
            ->whereIn('product_id', [
                config('polar.products.founder'),
                config('polar.products.standard'),
            ])
            ->whereNull('refunded_at')
            ->where('ordered_at', '>=', now()->subYear())
            ->exists();
    }
}
```

Key changes vs. the old file:
- `paid_at`, `polar_customer_id` removed from `#[Fillable]`
- `paid_at` cast removed
- `isPaid()` removed
- `Billable` trait added
- `hasActiveAccess()` added

- [ ] **Step 4: Verify tests — will still fail until Task 6 adds `OrderFactory`**

```bash
php artisan test --compact --filter=UserHasActiveAccessTest
```

Expected: still FAIL with "Class OrderFactory not found" or "Call to undefined method factory" on `Order`. That's fine; proceed to Task 5.

- [ ] **Step 5: Commit (WIP)**

```bash
git add app/Models/User.php tests/Feature/Models/UserHasActiveAccessTest.php
git commit -m "feat: User::hasActiveAccess + Billable trait (WIP, pending OrderFactory)"
```

---

## Task 5: Drop `paid_at` and `polar_customer_id` columns from `users`

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_drop_paid_at_from_users.php`
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_drop_polar_customer_id_from_users.php`

- [ ] **Step 1: Create first migration**

```bash
php artisan make:migration drop_paid_at_from_users --table=users
```

Fill with:

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
            $table->dropIndex(['paid_at']);
            $table->dropColumn('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('email_verified_at');
            $table->index('paid_at');
        });
    }
};
```

- [ ] **Step 2: Create second migration**

```bash
php artisan make:migration drop_polar_customer_id_from_users --table=users
```

Fill with:

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
            $table->dropIndex(['polar_customer_id']);
            $table->dropColumn('polar_customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('polar_customer_id')->nullable()->index()->after('email_verified_at');
        });
    }
};
```

- [ ] **Step 3: Run migrations**

```bash
php artisan migrate
```

Expected: both migrations run clean. (No `paid_at` data exists in practice — verified via `database-query`.)

- [ ] **Step 4: Verify schema**

```bash
php artisan tinker --execute 'dump(Schema::getColumnListing("users"));'
```

Expected: output does NOT contain `paid_at` or `polar_customer_id`.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/
git commit -m "feat: drop paid_at and polar_customer_id from users (superseded by polar.orders)"
```

---

## Task 6: Factories (OrderFactory + User states)

**Files:**
- Create: `database/factories/OrderFactory.php`
- Modify: `database/factories/UserFactory.php`

- [ ] **Step 1: Inspect the package's Order model**

```bash
php artisan tinker --execute 'dump((new Danestves\LaravelPolar\Order)->getFillable());'
```

Note the fillable list (`polar_id`, `checkout_id`, `billable_type`, `billable_id`, `product_id`, `amount`, `tax_amount`, `refunded_amount`, `refunded_tax_amount`, `currency`, `ordered_at`, `refunded_at`, `status` — confirm against your output).

- [ ] **Step 2: Create `database/factories/OrderFactory.php`**

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Danestves\LaravelPolar\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'polar_id'             => 'order_'.fake()->uuid(),
            'checkout_id'          => 'checkout_'.fake()->uuid(),
            'billable_type'        => User::class,
            'billable_id'          => User::factory(),
            'product_id'           => config('polar.products.founder'),
            'amount'               => 2900,
            'tax_amount'           => 0,
            'refunded_amount'      => 0,
            'refunded_tax_amount'  => 0,
            'currency'             => 'EUR',
            'ordered_at'           => now(),
            'refunded_at'          => null,
            'status'               => 'paid',
        ];
    }

    public function refunded(): static
    {
        return $this->state(fn () => ['refunded_at' => now(), 'refunded_amount' => 2900]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['ordered_at' => now()->subDays(400)]);
    }

    public function standard(): static
    {
        return $this->state(fn () => [
            'product_id' => config('polar.products.standard'),
            'amount'     => 4900,
        ]);
    }
}
```

- [ ] **Step 3: Register the factory on the package's Order model**

The package's `Order` model needs `HasFactory` and `newFactory()` to find our factory. Add a thin extension or use the `Factory::guessFactoryNamesUsing` hook in `AppServiceProvider::boot()`:

Edit `app/Providers/AppServiceProvider.php` `boot()` and add:

```php
use Illuminate\Database\Eloquent\Factories\Factory;

Factory::guessFactoryNamesUsing(function (string $modelName) {
    if ($modelName === \Danestves\LaravelPolar\Order::class) {
        return \Database\Factories\OrderFactory::class;
    }
    return 'Database\\Factories\\'.class_basename($modelName).'Factory';
});
```

- [ ] **Step 4: Update `UserFactory`**

Remove any `paid()` / `paid_at` state if present (search for `paid_at`). Add:

```php
public function hasActiveAccess(): static
{
    return $this->afterCreating(function (\App\Models\User $user) {
        \Danestves\LaravelPolar\Order::factory()->for($user, 'billable')->create([
            'product_id' => config('polar.products.founder'),
            'ordered_at' => now()->subDays(30),
        ]);
    });
}

public function hasExpiredAccess(): static
{
    return $this->afterCreating(function (\App\Models\User $user) {
        \Danestves\LaravelPolar\Order::factory()->for($user, 'billable')->create([
            'product_id' => config('polar.products.founder'),
            'ordered_at' => now()->subDays(400),
        ]);
    });
}
```

- [ ] **Step 5: Run Task 4's tests; they should pass now**

```bash
php artisan test --compact --filter=UserHasActiveAccessTest
```

Expected: all 6 tests PASS.

- [ ] **Step 6: Commit**

```bash
git add database/factories/ app/Providers/AppServiceProvider.php
git commit -m "feat: OrderFactory + User hasActiveAccess/hasExpiredAccess states"
```

---

## Task 7: Rewire `Pricing` service + add `currentProductId()`

**Files:**
- Modify: `app/Services/Pricing.php`
- Test: `tests/Feature/Services/PricingTest.php` (new)

- [ ] **Step 1: Write the tests**

Create `tests/Feature/Services/PricingTest.php`:

```php
<?php

use App\Models\User;
use App\Services\Pricing;
use Danestves\LaravelPolar\Order;

beforeEach(function () {
    $this->pricing = app(Pricing::class);
});

it('returns founder price with all 100 spots remaining when no orders exist', function () {
    $price = $this->pricing->currentPrice();

    expect($price['amount_eur'])->toBe(29);
    expect($price['standard_price_eur'])->toBe(49);
    expect($price['is_founder_price'])->toBeTrue();
    expect($price['spots_remaining'])->toBe(100);
});

it('returns founder price with 1 spot remaining when 99 founder orders exist', function () {
    Order::factory()->count(99)->create(['product_id' => config('polar.products.founder')]);

    $price = $this->pricing->currentPrice();

    expect($price['is_founder_price'])->toBeTrue();
    expect($price['spots_remaining'])->toBe(1);
});

it('returns standard price once 100 founder orders exist', function () {
    Order::factory()->count(100)->create(['product_id' => config('polar.products.founder')]);

    $price = $this->pricing->currentPrice();

    expect($price['amount_eur'])->toBe(49);
    expect($price['is_founder_price'])->toBeFalse();
    expect($price['spots_remaining'])->toBe(0);
});

it('does not count refunded founder orders toward the cap', function () {
    Order::factory()->count(100)->refunded()->create(['product_id' => config('polar.products.founder')]);

    $price = $this->pricing->currentPrice();

    expect($price['is_founder_price'])->toBeTrue();
});

it('returns the founder product id when below the cap', function () {
    expect($this->pricing->currentProductId())->toBe(config('polar.products.founder'));
});

it('returns the standard product id at the cap', function () {
    Order::factory()->count(100)->create(['product_id' => config('polar.products.founder')]);

    expect($this->pricing->currentProductId())->toBe(config('polar.products.standard'));
});
```

- [ ] **Step 2: Run to verify failure**

```bash
php artisan test --compact --filter=PricingTest
```

Expected: FAIL (on both the `User::whereNotNull('paid_at')` query — column dropped — and the missing `currentProductId()`).

- [ ] **Step 3: Rewrite `app/Services/Pricing.php`**

```php
<?php

namespace App\Services;

use Danestves\LaravelPolar\Order;

class Pricing
{
    public const FOUNDER_PRICE_EUR = 29;

    public const STANDARD_PRICE_EUR = 49;

    public const FOUNDER_CAP = 100;

    /**
     * @return array{amount_eur: int, standard_price_eur: int, is_founder_price: bool, spots_remaining: int}
     */
    public function currentPrice(): array
    {
        $founderCount = Order::query()
            ->where('product_id', config('polar.products.founder'))
            ->whereNull('refunded_at')
            ->count();

        $isFounder = $founderCount < self::FOUNDER_CAP;

        return [
            'amount_eur'         => $isFounder ? self::FOUNDER_PRICE_EUR : self::STANDARD_PRICE_EUR,
            'standard_price_eur' => self::STANDARD_PRICE_EUR,
            'is_founder_price'   => $isFounder,
            'spots_remaining'    => max(0, self::FOUNDER_CAP - $founderCount),
        ];
    }

    public function currentProductId(): string
    {
        return $this->currentPrice()['is_founder_price']
            ? config('polar.products.founder')
            : config('polar.products.standard');
    }
}
```

- [ ] **Step 4: Re-run tests**

```bash
php artisan test --compact --filter=PricingTest
```

Expected: all 6 PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Services/Pricing.php tests/Feature/Services/PricingTest.php
git commit -m "refactor: Pricing counts via polar.orders; add currentProductId"
```

---

## Task 8: Delete old custom scaffolding (services, middleware, tests)

**Files:**
- Delete: `app/Services/PolarCheckout.php`
- Delete: `app/Services/PolarWebhookProcessor.php`
- Delete: `app/Http/Middleware/EnsurePaid.php`
- Delete: `tests/Feature/Services/PolarWebhookProcessorTest.php`
- Delete: `tests/Feature/Models/UserPaidAtTest.php`
- Delete: `tests/Feature/Checkout/CheckoutFlowSmokeTest.php`
- Delete: `tests/Feature/Checkout/CheckoutSuccessTest.php`
- Delete: `resources/js/pages/checkout/success.tsx` (will be re-created as `processing.tsx` in Task 15)

- [ ] **Step 1: Remove the files**

```bash
rm app/Services/PolarCheckout.php \
   app/Services/PolarWebhookProcessor.php \
   app/Http/Middleware/EnsurePaid.php \
   tests/Feature/Services/PolarWebhookProcessorTest.php \
   tests/Feature/Models/UserPaidAtTest.php \
   tests/Feature/Checkout/CheckoutFlowSmokeTest.php \
   tests/Feature/Checkout/CheckoutSuccessTest.php \
   resources/js/pages/checkout/success.tsx
```

- [ ] **Step 2: Verify no remaining references**

```bash
grep -RIn "PolarCheckout\|PolarWebhookProcessor\|EnsurePaid\|isPaid()" app/ resources/ routes/ tests/
```

Expected: output only shows references that Tasks 9–18 will replace (routes/web.php, CheckoutController, locked-preview.tsx, etc.).

- [ ] **Step 3: Don't try to boot yet — the app is intentionally broken until Tasks 9–12 finish**

- [ ] **Step 4: Commit**

```bash
git add -A app/Services app/Http/Middleware tests/ resources/js/pages/checkout
git commit -m "refactor: delete custom polar scaffolding (replaced by package)"
```

---

## Task 9: Create `EnsureActiveAccess` middleware

**Files:**
- Create: `app/Http/Middleware/EnsureActiveAccess.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Middleware/EnsureActiveAccessTest.php`

- [ ] **Step 1: Write the tests**

```php
<?php
// tests/Feature/Middleware/EnsureActiveAccessTest.php

use App\Models\User;

it('allows access when the user has an active order', function () {
    $user = User::factory()->hasActiveAccess()->create();

    $this->actingAs($user)
        ->get('/freies-lernen')
        ->assertOk();
});

it('redirects unauthenticated users to login', function () {
    $this->get('/freies-lernen')->assertRedirect('/login');
});

it('aborts 403 when the user has no access', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/freies-lernen')
        ->assertForbidden();
});

it('aborts 403 when the user had access but it expired', function () {
    $user = User::factory()->hasExpiredAccess()->create();

    $this->actingAs($user)
        ->get('/freies-lernen')
        ->assertForbidden();
});
```

- [ ] **Step 2: Run — expect fail**

```bash
php artisan test --compact --filter=EnsureActiveAccessTest
```

- [ ] **Step 3: Create the middleware**

```php
<?php
// app/Http/Middleware/EnsureActiveAccess.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasActiveAccess()) {
            abort(403);
        }

        return $next($request);
    }
}
```

- [ ] **Step 4: Update `routes/web.php`**

Replace `EnsurePaid::class` with `EnsureActiveAccess::class`. Update the import at the top accordingly.

- [ ] **Step 5: Run tests again**

```bash
php artisan test --compact --filter=EnsureActiveAccessTest
```

Expected: all 4 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Middleware/EnsureActiveAccess.php routes/web.php tests/Feature/Middleware/
git commit -m "feat: EnsureActiveAccess middleware replaces EnsurePaid"
```

---

## Task 10: `ClaimGuestExamAttempts` listener for post-auth

**Files:**
- Create: `app/Listeners/ClaimGuestExamAttempts.php`
- Test: `tests/Feature/Listeners/ClaimGuestExamAttemptsTest.php`

- [ ] **Step 1: Tests**

```php
<?php
// tests/Feature/Listeners/ClaimGuestExamAttemptsTest.php

use App\Models\ExamAttempt;
use App\Models\User;
use App\Support\ExamAttemptFinder;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;

it('claims orphan attempts on registration when the session cookie matches', function () {
    $sessionUuid = 'session-'.fake()->uuid();
    $attempt = ExamAttempt::factory()->create([
        'user_id'      => null,
        'session_uuid' => $sessionUuid,
    ]);
    $user = User::factory()->create();

    $this->withUnencryptedCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid)
        ->post(route('register'), [
            // not actually hitting the endpoint; we dispatch the event manually below
        ]);

    event(new Registered($user));

    expect($attempt->fresh()->user_id)->toBe($user->id);
});

it('claims orphan attempts on login when the session cookie matches', function () {
    $sessionUuid = 'session-'.fake()->uuid();
    $attempt = ExamAttempt::factory()->create([
        'user_id'      => null,
        'session_uuid' => $sessionUuid,
    ]);
    $user = User::factory()->create();

    $this->withUnencryptedCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid);
    event(new Login('web', $user, false));

    expect($attempt->fresh()->user_id)->toBe($user->id);
});

it('does not claim attempts owned by a different user', function () {
    $other = User::factory()->create();
    $attempt = ExamAttempt::factory()->create([
        'user_id'      => $other->id,
        'session_uuid' => 'session-abc',
    ]);
    $user = User::factory()->create();

    $this->withUnencryptedCookie(ExamAttemptFinder::SESSION_COOKIE, 'session-abc');
    event(new Registered($user));

    expect($attempt->fresh()->user_id)->toBe($other->id);
});
```

- [ ] **Step 2: Create the listener**

```php
<?php
// app/Listeners/ClaimGuestExamAttempts.php

namespace App\Listeners;

use App\Models\ExamAttempt;
use App\Support\ExamAttemptFinder;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cookie;

class ClaimGuestExamAttempts
{
    public function handleRegistered(Registered $event): void
    {
        $this->claim($event->user->id);
    }

    public function handleLogin(Login $event): void
    {
        $this->claim($event->user->id);
    }

    private function claim(int $userId): void
    {
        $sessionUuid = Cookie::get(ExamAttemptFinder::SESSION_COOKIE);
        if (! $sessionUuid) {
            return;
        }

        ExamAttempt::query()
            ->where('session_uuid', $sessionUuid)
            ->whereNull('user_id')
            ->update(['user_id' => $userId]);
    }
}
```

Laravel 11+ auto-discovers listeners whose methods are typed against events.

- [ ] **Step 3: Run tests**

```bash
php artisan test --compact --filter=ClaimGuestExamAttemptsTest
```

Expected: all 3 PASS. If any fail because Cookie facade in tests needs `withUnencryptedCookie` vs `withCookie`, check the project's existing cookie-reading tests for the exact helper.

- [ ] **Step 4: Commit**

```bash
git add app/Listeners/ClaimGuestExamAttempts.php tests/Feature/Listeners/
git commit -m "feat: ClaimGuestExamAttempts listener links session_uuid to user post-auth"
```

---

## Task 11: Custom `RegisterResponse` + `LoginResponse` honoring `?intent=checkout`

**Files:**
- Create: `app/Http/Responses/RegisterResponse.php`
- Create: `app/Http/Responses/LoginResponse.php`
- Modify: `app/Providers/FortifyServiceProvider.php`
- Test: `tests/Feature/Auth/CheckoutIntentResponseTest.php`

- [ ] **Step 1: Tests**

```php
<?php
// tests/Feature/Auth/CheckoutIntentResponseTest.php

use App\Models\User;

it('redirects to checkout.start after registration when intent=checkout', function () {
    $response = $this->post('/register?intent=checkout', [
        'name'                  => 'Test',
        'email'                 => 'test@example.com',
        'password'              => 'password1234',
        'password_confirmation' => 'password1234',
    ]);

    $response->assertRedirect(route('checkout.start'));
});

it('redirects to the default location after registration without intent', function () {
    $response = $this->post('/register', [
        'name'                  => 'Test',
        'email'                 => 'test@example.com',
        'password'              => 'password1234',
        'password_confirmation' => 'password1234',
    ]);

    $response->assertRedirect('/dashboard');
});

it('skips checkout and redirects to results if the logging-in user already has active access', function () {
    $user = User::factory()->hasActiveAccess()->create();

    $response = $this->post('/login?intent=checkout', [
        'email'    => $user->email,
        'password' => 'password', // UserFactory default
    ]);

    // Should NOT redirect to checkout.start — user already has access
    $response->assertRedirect('/dashboard'); // or home/results, see Step 3 note
});
```

**Step 3 note:** if there's a natural "return to results page" URL we want to bounce them to, we can store it in session at the Sheet step and read it here. For now, default to `/dashboard` since results pages aren't stable URLs.

- [ ] **Step 2: Create the responses**

```php
<?php
// app/Http/Responses/RegisterResponse.php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        if ($request->query('intent') === 'checkout') {
            return redirect()->route('checkout.start');
        }
        return redirect()->intended(config('fortify.home'));
    }
}
```

```php
<?php
// app/Http/Responses/LoginResponse.php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        if ($request->query('intent') === 'checkout') {
            $user = $request->user();
            if ($user && $user->hasActiveAccess()) {
                return redirect()->intended(config('fortify.home'));
            }
            return redirect()->route('checkout.start');
        }
        return redirect()->intended(config('fortify.home'));
    }
}
```

- [ ] **Step 3: Bind contracts in `FortifyServiceProvider::register()`**

Add to `register()`:

```php
use App\Http\Responses\LoginResponse;
use App\Http\Responses\RegisterResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

// inside register()
$this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
$this->app->singleton(LoginResponseContract::class, LoginResponse::class);
```

- [ ] **Step 4: Run tests**

```bash
php artisan test --compact --filter=CheckoutIntentResponseTest
```

Expected: all 3 PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Responses/ app/Providers/FortifyServiceProvider.php tests/Feature/Auth/
git commit -m "feat: RegisterResponse/LoginResponse route intent=checkout to /checkout/start"
```

---

## Task 12: Rewrite `CheckoutController`

**Files:**
- Modify: `app/Http/Controllers/CheckoutController.php`
- Modify: `routes/web.php` (remove webhook route)
- Modify: `bootstrap/app.php` (CSRF path change)
- Test: `tests/Feature/Checkout/CheckoutStartTest.php`

- [ ] **Step 1: Tests**

```php
<?php
// tests/Feature/Checkout/CheckoutStartTest.php

use App\Models\User;
use App\Services\Pricing;

it('requires authentication', function () {
    $this->get(route('checkout.start'))->assertRedirect('/login');
});

it('redirects a logged-in user with active access back to home', function () {
    $user = User::factory()->hasActiveAccess()->create();

    $this->actingAs($user)
        ->get(route('checkout.start'))
        ->assertRedirect(config('fortify.home'));
});

it('calls Polar checkout for a logged-in user with no active access', function () {
    \Illuminate\Support\Facades\Http::fake([
        '*/checkouts/*' => \Illuminate\Support\Facades\Http::response([
            'id'  => 'chk_test',
            'url' => 'https://sandbox.polar.sh/checkout/chk_test',
        ], 201),
    ]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('checkout.start'));

    $response->assertRedirect('https://sandbox.polar.sh/checkout/chk_test');
});

it('passes the founder product id when below the cap', function () {
    \Illuminate\Support\Facades\Http::fake([
        '*/checkouts/*' => \Illuminate\Support\Facades\Http::response([
            'id'  => 'chk_test',
            'url' => 'https://sandbox.polar.sh/checkout/chk_test',
        ], 201),
    ]);
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('checkout.start'));

    \Illuminate\Support\Facades\Http::assertSent(function ($request) {
        return str_contains($request->url(), 'checkout')
            && in_array(config('polar.products.founder'), (array) data_get($request->data(), 'products'), true);
    });
});
```

- [ ] **Step 2: Rewrite the controller**

```php
<?php

namespace App\Http\Controllers;

use App\Services\Pricing;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function start(Request $request, Pricing $pricing)
    {
        $user = $request->user();

        if ($user->hasActiveAccess()) {
            return redirect()->intended(config('fortify.home'));
        }

        return $user->checkout([$pricing->currentProductId()])
            ->withSuccessUrl(route('checkout.processing').'?checkout_id={CHECKOUT_ID}');
    }

    public function processing(Request $request): Response
    {
        return Inertia::render('checkout/processing', [
            'hasAccess' => (bool) $request->user()?->hasActiveAccess(),
        ]);
    }
}
```

Route naming change: `checkout.success` → `checkout.processing` (the page *is* a processing/polling page, not a fait-accompli success page).

- [ ] **Step 3: Update `routes/web.php`**

Replace the existing checkout routes. Drop `POST /webhooks/polar` entirely — the package registers its own webhook route under `POLAR_PATH` (`/polar/webhook`). Add auth middleware on `/checkout/start`:

```php
Route::middleware('auth')->group(function () {
    Route::get('/checkout/start', [CheckoutController::class, 'start'])->name('checkout.start');
    Route::get('/checkout/processing', [CheckoutController::class, 'processing'])->name('checkout.processing');
});
```

Delete the old `Route::post('/webhooks/polar', ...)` line.

- [ ] **Step 4: Update `bootstrap/app.php`**

Change the CSRF exclude from `/webhooks/polar` to `polar/*`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: ['polar/*']);
})
```

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=CheckoutStartTest
```

Expected: all 4 PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/CheckoutController.php routes/web.php bootstrap/app.php tests/Feature/Checkout/
git commit -m "feat: rewrite CheckoutController to use package's Billable checkout"
```

---

## Task 13: `PolarEventListener` for `OrderCreated` + `OrderRefunded`

**Files:**
- Create: `app/Listeners/PolarEventListener.php`
- Create: `app/Jobs/SendAccessExpiryReminder.php` (stub; full in Task 14)
- Create: `app/Mail/AccessRefundedMail.php`
- Test: `tests/Feature/Listeners/PolarEventListenerTest.php`

- [ ] **Step 1: Tests**

```php
<?php
// tests/Feature/Listeners/PolarEventListenerTest.php

use App\Jobs\SendAccessExpiryReminder;
use App\Mail\AccessRefundedMail;
use App\Models\User;
use Danestves\LaravelPolar\Events\OrderCreated;
use Danestves\LaravelPolar\Events\OrderRefunded;
use Danestves\LaravelPolar\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

it('schedules expiry reminder jobs on OrderCreated', function () {
    Queue::fake();
    $user = User::factory()->create();
    $order = Order::factory()->for($user, 'billable')->create();

    event(new OrderCreated($user, ['type' => 'order.created', 'data' => ['id' => $order->polar_id]], $order));

    Queue::assertPushed(SendAccessExpiryReminder::class, 2);
});

it('sends a refund confirmation mail on OrderRefunded', function () {
    Mail::fake();
    $user = User::factory()->create();
    $order = Order::factory()->refunded()->for($user, 'billable')->create();

    event(new OrderRefunded($user, ['type' => 'order.refunded', 'data' => ['id' => $order->polar_id]], $order));

    Mail::assertQueued(AccessRefundedMail::class, fn ($mail) => $mail->hasTo($user->email));
});
```

Note: confirm the exact constructor signature of the package's `OrderCreated` / `OrderRefunded` events during implementation. The test above assumes `($model, $payload, $order)` but may be `($model, $payload)` with `$order` readable via a property — adjust to whatever the package actually exposes.

- [ ] **Step 2: Stub the job + mail (full implementation in Task 14)**

```php
<?php
// app/Jobs/SendAccessExpiryReminder.php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAccessExpiryReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public User $user, public Carbon $originalOrderedAt) {}

    public function handle(): void
    {
        if ($this->user->hasActiveAccess() && $this->user->orders()->whereNotIn('ordered_at', [$this->originalOrderedAt])->exists()) {
            return; // user already renewed
        }
        // TODO Task 14: send the mail
    }
}
```

```php
<?php
// app/Mail/AccessRefundedMail.php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccessRefundedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Deine Rückerstattung wurde verbucht');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.access-refunded');
    }
}
```

Create a minimal blade view at `resources/views/mail/access-refunded.blade.php`:
```blade
<p>Hallo {{ $user->name ?? 'Lernender' }},</p>

<p>deine Rückerstattung wurde erfolgreich bearbeitet. Der Zugang zum Prüfungstrainer wurde deaktiviert.</p>

<p>Bei Fragen erreichst du uns unter support@pruefungstrainer.de.</p>
```

- [ ] **Step 3: Create the listener**

```php
<?php
// app/Listeners/PolarEventListener.php

namespace App\Listeners;

use App\Jobs\SendAccessExpiryReminder;
use App\Mail\AccessRefundedMail;
use Danestves\LaravelPolar\Events\OrderCreated;
use Danestves\LaravelPolar\Events\OrderRefunded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class PolarEventListener implements ShouldQueue
{
    public function handleOrderCreated(OrderCreated $event): void
    {
        $user = $event->model;
        $order = $event->order ?? $user->orders()->latest('ordered_at')->first();
        if (! $order) {
            return;
        }
        SendAccessExpiryReminder::dispatch($user, $order->ordered_at)
            ->delay($order->ordered_at->copy()->addYear()->subDays(14));
        SendAccessExpiryReminder::dispatch($user, $order->ordered_at)
            ->delay($order->ordered_at->copy()->addYear()->subDay());
    }

    public function handleOrderRefunded(OrderRefunded $event): void
    {
        Mail::to($event->model->email)->queue(new AccessRefundedMail($event->model));
    }
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test --compact --filter=PolarEventListenerTest
```

Expected: both PASS. If the event signature doesn't match, fix the `$event->order ?? ...` access pattern and re-run.

- [ ] **Step 5: Commit**

```bash
git add app/Listeners/PolarEventListener.php app/Jobs/ app/Mail/ resources/views/mail/ tests/Feature/Listeners/
git commit -m "feat: PolarEventListener schedules expiry reminders + refund mail"
```

---

## Task 14: Complete `SendAccessExpiryReminder` job

**Files:**
- Modify: `app/Jobs/SendAccessExpiryReminder.php`
- Create: `app/Mail/AccessExpiryReminderMail.php`
- Create: `resources/views/mail/access-expiry-reminder.blade.php`
- Test: `tests/Feature/Jobs/SendAccessExpiryReminderTest.php`

- [ ] **Step 1: Tests**

```php
<?php
// tests/Feature/Jobs/SendAccessExpiryReminderTest.php

use App\Jobs\SendAccessExpiryReminder;
use App\Mail\AccessExpiryReminderMail;
use App\Models\User;
use Danestves\LaravelPolar\Order;
use Illuminate\Support\Facades\Mail;

it('sends the expiry reminder mail', function () {
    Mail::fake();
    $user = User::factory()->create();
    $order = Order::factory()->for($user, 'billable')->create(['ordered_at' => now()->subDays(350)]);

    (new SendAccessExpiryReminder($user, $order->ordered_at))->handle();

    Mail::assertQueued(AccessExpiryReminderMail::class, fn ($m) => $m->hasTo($user->email));
});

it('does not send if the user already renewed with a newer order', function () {
    Mail::fake();
    $user = User::factory()->create();
    $old = Order::factory()->for($user, 'billable')->create(['ordered_at' => now()->subDays(350)]);
    Order::factory()->for($user, 'billable')->create(['ordered_at' => now()->subDays(10)]);

    (new SendAccessExpiryReminder($user, $old->ordered_at))->handle();

    Mail::assertNothingQueued();
});
```

- [ ] **Step 2: Create `AccessExpiryReminderMail`**

```php
<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccessExpiryReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public Carbon $expiresAt) {}

    public function envelope(): Envelope
    {
        $daysLeft = (int) now()->diffInDays($this->expiresAt, false);
        $subject = $daysLeft <= 1
            ? 'Dein Zugang endet morgen'
            : "Dein Zugang endet in {$daysLeft} Tagen";
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.access-expiry-reminder');
    }
}
```

Create `resources/views/mail/access-expiry-reminder.blade.php`:

```blade
<p>Hallo {{ $user->name ?? 'Lernender' }},</p>

<p>dein Zugang zum Prüfungstrainer endet am {{ $expiresAt->translatedFormat('d.m.Y') }}.</p>

<p>Möchtest du ihn verlängern? <a href="{{ url('/') }}">Hier geht's zum Angebot.</a></p>
```

- [ ] **Step 3: Fill in the job handle**

```php
public function handle(): void
{
    $newerOrderExists = $this->user->orders()
        ->where('ordered_at', '>', $this->originalOrderedAt)
        ->whereNull('refunded_at')
        ->exists();

    if ($newerOrderExists) {
        return;
    }

    \Illuminate\Support\Facades\Mail::to($this->user->email)
        ->queue(new \App\Mail\AccessExpiryReminderMail(
            $this->user,
            $this->originalOrderedAt->copy()->addYear()
        ));
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test --compact --filter=SendAccessExpiryReminderTest
```

Expected: both PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Jobs/SendAccessExpiryReminder.php app/Mail/AccessExpiryReminderMail.php resources/views/mail/access-expiry-reminder.blade.php tests/Feature/Jobs/
git commit -m "feat: access expiry reminder mail + guard for renewed users"
```

---

## Task 15: Frontend — `CheckoutSheet` component (inline register/login)

**Files:**
- Create: `resources/js/components/checkout-sheet.tsx`
- Modify: `resources/js/components/locked-preview.tsx`
- Modify: `resources/js/pages/exam/results.tsx`

- [ ] **Step 1: Install the shadcn `sheet`, `tabs`, `field`, `input`, `label` components if missing**

```bash
npx shadcn@latest add sheet tabs field input label button alert --yes
```

- [ ] **Step 2: Create `checkout-sheet.tsx`**

```tsx
// resources/js/components/checkout-sheet.tsx
import { useState } from 'react';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Field, FieldLabel, FieldGroup } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Form } from '@inertiajs/react';

type Props = {
    trigger: React.ReactNode;
    attemptId: number;
    priceLabel: string;
};

export function CheckoutSheet({ trigger, attemptId, priceLabel }: Props) {
    const [open, setOpen] = useState(false);

    return (
        <Sheet open={open} onOpenChange={setOpen}>
            <SheetTrigger asChild>{trigger}</SheetTrigger>
            <SheetContent className="sm:max-w-md">
                <SheetHeader>
                    <SheetTitle>12 Monate Zugang freischalten · {priceLabel}</SheetTitle>
                </SheetHeader>

                <Tabs defaultValue="register" className="mt-6">
                    <TabsList className="grid w-full grid-cols-2">
                        <TabsTrigger value="register">Neu hier</TabsTrigger>
                        <TabsTrigger value="login">Schon Konto</TabsTrigger>
                    </TabsList>

                    <TabsContent value="register" className="mt-4">
                        <Form action={`/register?intent=checkout&attempt=${attemptId}`} method="post" className="flex flex-col gap-4">
                            {({ processing, errors }) => (
                                <>
                                    <FieldGroup>
                                        <Field>
                                            <FieldLabel>E-Mail</FieldLabel>
                                            <Input type="email" name="email" autoComplete="email" required />
                                        </Field>
                                        <Field>
                                            <FieldLabel>Passwort</FieldLabel>
                                            <Input type="password" name="password" autoComplete="new-password" required />
                                        </Field>
                                        <Field>
                                            <FieldLabel>Passwort bestätigen</FieldLabel>
                                            <Input type="password" name="password_confirmation" autoComplete="new-password" required />
                                        </Field>
                                        <Field>
                                            <FieldLabel>Name (optional)</FieldLabel>
                                            <Input type="text" name="name" autoComplete="name" />
                                        </Field>
                                    </FieldGroup>
                                    {errors.email && <p className="text-sm text-destructive">{errors.email}</p>}
                                    <Button type="submit" disabled={processing}>Konto erstellen und weiter zur Zahlung</Button>
                                </>
                            )}
                        </Form>
                    </TabsContent>

                    <TabsContent value="login" className="mt-4">
                        <Form action={`/login?intent=checkout&attempt=${attemptId}`} method="post" className="flex flex-col gap-4">
                            {({ processing, errors }) => (
                                <>
                                    <FieldGroup>
                                        <Field>
                                            <FieldLabel>E-Mail</FieldLabel>
                                            <Input type="email" name="email" autoComplete="email" required />
                                        </Field>
                                        <Field>
                                            <FieldLabel>Passwort</FieldLabel>
                                            <Input type="password" name="password" autoComplete="current-password" required />
                                        </Field>
                                    </FieldGroup>
                                    {errors.email && <p className="text-sm text-destructive">{errors.email}</p>}
                                    <Button type="submit" disabled={processing}>Anmelden und weiter zur Zahlung</Button>
                                    <a href="/forgot-password" className="text-sm text-muted-foreground underline">Passwort vergessen?</a>
                                </>
                            )}
                        </Form>
                    </TabsContent>
                </Tabs>
            </SheetContent>
        </Sheet>
    );
}
```

- [ ] **Step 3: Rewrite `locked-preview.tsx`**

The current component POSTs to `/checkout/start`. Change it to render the `CheckoutSheet` as its CTA, OR — if already-logged-in — link to `/checkout/start` directly.

```tsx
// resources/js/components/locked-preview.tsx
import { Lock } from 'lucide-react';
import { CheckoutSheet } from './checkout-sheet';
import { Button } from '@/components/ui/button';
import { usePage } from '@inertiajs/react';

type Props = {
    children: React.ReactNode;
    priceLabel: string;       // e.g. "29 €"
    attemptId: number;
};

export function LockedPreview({ children, priceLabel, attemptId }: Props) {
    const { auth } = usePage().props as { auth?: { user?: unknown } };

    const cta = (
        <Button size="lg">
            <Lock className="mr-2 size-4" />
            12 Monate Zugang freischalten · {priceLabel}
        </Button>
    );

    return (
        <div className="relative">
            <div className="pointer-events-none select-none blur-sm">{children}</div>
            <div className="absolute inset-0 flex items-center justify-center">
                {auth?.user ? (
                    <a href="/checkout/start">{cta}</a>
                ) : (
                    <CheckoutSheet trigger={cta} attemptId={attemptId} priceLabel={priceLabel} />
                )}
            </div>
        </div>
    );
}
```

- [ ] **Step 4: Update `resources/js/pages/exam/results.tsx`**

Wherever `<LockedPreview>` is used, pass `priceLabel` and `attemptId`. The props are already available via the Inertia page props — reuse `pricing.amount_eur` for the label. Update the `pricing` prop shape in the results controller if needed (see Task 16).

- [ ] **Step 5: Build + smoke-test manually**

```bash
npm run build
```

Expected: clean build; no type errors.

Then spin up dev, visit a finished exam as guest, click CTA, verify the Sheet opens with tabs.

- [ ] **Step 6: Commit**

```bash
git add resources/js/components/ resources/js/pages/exam/results.tsx
git commit -m "feat: inline register/login CheckoutSheet on the locked-preview CTA"
```

---

## Task 16: Copy + controller prop updates

**Files:**
- Modify: `resources/js/pages/welcome.tsx`
- Modify: `app/Http/Controllers/ExamController.php` (results action props)
- Modify: `app/Http/Controllers/HomeController.php` (if pricing prop shape needs adjustment)

- [ ] **Step 1: Update welcome pricing card copy**

In `resources/js/pages/welcome.tsx`, replace any "Lifetime-Zugang" with "12 Monate Zugang". The CTA under the pricing card currently POSTs to `/checkout/start`; change it to a link `/checkout/start` or — for guests — open the `CheckoutSheet` too. Recommendation: for the welcome page, use a simple `<a href="/checkout/start">` that hits the (now auth-guarded) route; unauthenticated users get redirected to login/register anyway. Simpler than mounting the Sheet on the landing page.

Specifically update line ~141 (the button text): `"Lifetime-Zugang freischalten"` → `"12 Monate Zugang freischalten"`.

- [ ] **Step 2: Update `ExamController::results()` to pass `pricing` + `attempt.id`**

In the `results()` method (around line 155–184), add `pricing` to the Inertia props:

```php
return inertia('exam/results', [
    'attempt' => [
        'id' => $examAttempt->id,
        // ... existing keys
    ],
    'topicBreakdown' => app(ExamScorer::class)->topicBreakdown($examAttempt),
    'pricing'        => app(\App\Services\Pricing::class)->currentPrice(),
]);
```

- [ ] **Step 3: Manual UI check**

Reload the results page, verify the CTA reads "12 Monate Zugang freischalten · 29 €" and the Sheet opens for guests.

- [ ] **Step 4: Commit**

```bash
git add resources/js/pages/welcome.tsx app/Http/Controllers/ExamController.php app/Http/Controllers/HomeController.php
git commit -m "chore: rename 'Lifetime' copy to '12 Monate' + pass pricing to results page"
```

---

## Task 17: `/api/access-status` endpoint + processing-page polling

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/CheckoutController.php` (add `accessStatus()` method)
- Create: `resources/js/pages/checkout/processing.tsx`
- Test: `tests/Feature/Checkout/AccessStatusTest.php`

- [ ] **Step 1: Test the endpoint**

```php
<?php
// tests/Feature/Checkout/AccessStatusTest.php

use App\Models\User;

it('returns hasAccess=false for users without orders', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->getJson('/api/access-status')
        ->assertOk()
        ->assertJson(['hasAccess' => false]);
});

it('returns hasAccess=true for users with active orders', function () {
    $user = User::factory()->hasActiveAccess()->create();
    $this->actingAs($user)
        ->getJson('/api/access-status')
        ->assertOk()
        ->assertJson(['hasAccess' => true]);
});

it('returns 401 for unauthenticated requests', function () {
    $this->getJson('/api/access-status')->assertUnauthorized();
});
```

- [ ] **Step 2: Add the controller method**

```php
public function accessStatus(Request $request): \Illuminate\Http\JsonResponse
{
    return response()->json(['hasAccess' => (bool) $request->user()?->hasActiveAccess()]);
}
```

- [ ] **Step 3: Add the route**

In `routes/web.php`, add inside the `auth` middleware group:

```php
Route::get('/api/access-status', [CheckoutController::class, 'accessStatus'])->name('access-status');
```

- [ ] **Step 4: Create `resources/js/pages/checkout/processing.tsx`**

```tsx
import { useEffect, useState } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { CheckCircle2, Loader2 } from 'lucide-react';

type Props = { hasAccess: boolean };

export default function Processing({ hasAccess: initial }: Props) {
    const [hasAccess, setHasAccess] = useState(initial);
    const [gaveUp, setGaveUp] = useState(false);

    useEffect(() => {
        if (hasAccess) return;
        let cancelled = false;
        let count = 0;
        const tick = async () => {
            if (cancelled || count >= 20) { setGaveUp(!cancelled); return; }
            count += 1;
            const res = await fetch('/api/access-status', { credentials: 'same-origin' });
            if (cancelled) return;
            const json = await res.json();
            if (json.hasAccess) { setHasAccess(true); return; }
            setTimeout(tick, 500);
        };
        tick();
        return () => { cancelled = true; };
    }, [hasAccess]);

    return (
        <div className="mx-auto flex min-h-screen max-w-2xl items-center justify-center p-4">
            {hasAccess ? (
                <Alert>
                    <CheckCircle2 />
                    <AlertTitle>Zugang aktiviert</AlertTitle>
                    <AlertDescription>
                        Dein Zugang läuft für 12 Monate. <a href="/" className="underline">Zur Startseite</a>.
                    </AlertDescription>
                </Alert>
            ) : gaveUp ? (
                <Alert>
                    <AlertTitle>Zahlung wird verarbeitet</AlertTitle>
                    <AlertDescription>
                        Wir haben deine Zahlung erhalten, aber die Aktivierung dauert einen Moment.
                        Du erhältst gleich eine E-Mail sobald alles bereit ist.
                    </AlertDescription>
                </Alert>
            ) : (
                <Alert>
                    <Loader2 className="animate-spin" />
                    <AlertTitle>Zahlung wird bestätigt…</AlertTitle>
                    <AlertDescription>Das dauert nur wenige Sekunden.</AlertDescription>
                </Alert>
            )}
        </div>
    );
}
```

- [ ] **Step 5: Tests**

```bash
php artisan test --compact --filter=AccessStatusTest
```

Expected: all 3 PASS.

- [ ] **Step 6: Build frontend**

```bash
npm run build
```

- [ ] **Step 7: Commit**

```bash
git add routes/web.php app/Http/Controllers/CheckoutController.php resources/js/pages/checkout/processing.tsx tests/Feature/Checkout/AccessStatusTest.php
git commit -m "feat: /api/access-status polling + checkout processing page"
```

---

## Task 18: Integration — webhook end-to-end

**Files:**
- Test: `tests/Feature/Polar/WebhookIntegrationTest.php`

- [ ] **Step 1: Test valid signed webhook creates Order + schedules jobs**

```php
<?php
// tests/Feature/Polar/WebhookIntegrationTest.php

use App\Jobs\SendAccessExpiryReminder;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

it('persists an Order and queues reminders on a valid order.created webhook', function () {
    Queue::fake();
    $user = User::factory()->create();

    $payload = [
        'type' => 'order.created',
        'data' => [
            'id'            => 'order_test_123',
            'product_id'    => config('polar.products.founder'),
            'amount'        => 2900,
            'currency'      => 'EUR',
            'customer'      => ['email' => $user->email, 'id' => 'cust_test_xyz'],
            'billable'      => ['id' => $user->id, 'type' => User::class],
            'metadata'      => ['billable_id' => $user->id, 'billable_type' => User::class],
            'created_at'    => now()->toIso8601String(),
        ],
    ];
    $body = json_encode($payload);
    $signature = hash_hmac('sha256', $body, config('polar.webhook_secret'));

    $response = $this->call(
        'POST',
        '/polar/webhook',
        [], [], [],
        [
            'CONTENT_TYPE'            => 'application/json',
            'HTTP_POLAR_WEBHOOK_SIGNATURE' => $signature,
        ],
        $body,
    );

    $response->assertOk();
    expect($user->orders()->count())->toBe(1);
    Queue::assertPushed(SendAccessExpiryReminder::class, 2);
});

it('rejects webhook with invalid signature', function () {
    $response = $this->postJson('/polar/webhook', ['type' => 'order.created'], [
        'POLAR_WEBHOOK_SIGNATURE' => 'not-valid',
    ]);
    $response->assertStatus(403);
});
```

**Adjust:** the header name (`POLAR_WEBHOOK_SIGNATURE` or similar) must match what the package expects. Check `vendor/danestves/laravel-polar/src/Http/Controllers/WebhookController.php` for the exact header name and signing algorithm during implementation; update the test accordingly.

- [ ] **Step 2: Run**

```bash
php artisan test --compact --filter=WebhookIntegrationTest
```

Expected: both PASS.

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Polar/WebhookIntegrationTest.php
git commit -m "test: webhook integration — order.created persists + queues reminders"
```

---

## Task 19: Live sandbox smoke test (manual)

Real Polar sandbox traffic. Nothing to commit.

- [ ] **Step 1:** Start `ngrok http 80` (or your local dev port) so Polar can reach your webhook endpoint. Update your Polar sandbox webhook config to use the ngrok URL.

- [ ] **Step 2:** Visit your app in a private browser window, take an exam to completion as a guest.

- [ ] **Step 3:** On the results page, click "12 Monate Zugang freischalten · 29 €".

- [ ] **Step 4:** Register with a fresh test email in the Sheet.

- [ ] **Step 5:** Complete the Polar sandbox checkout with the test card `4242 4242 4242 4242` / any future expiry / any CVC.

- [ ] **Step 6:** Verify you're redirected to `/checkout/processing` and the page transitions to "Zugang aktiviert" within a few seconds.

- [ ] **Step 7:** Check the Polar sandbox dashboard — one order recorded under the founder product.

- [ ] **Step 8:** Inspect your DB:

```bash
php artisan tinker --execute 'dump(Danestves\LaravelPolar\Order::latest()->first()->toArray());'
```

Expected: a row with the founder product id, amount 2900, non-null `ordered_at`.

- [ ] **Step 9:** Visit the results page again — `LockedPreview` is gone, review is visible.

- [ ] **Step 10:** Visit `/freies-lernen` — 200 OK (previously 403).

If any of these fail, diagnose by reading `storage/logs/laravel.log` and the Polar webhook delivery log. Fix before moving on.

---

## Task 20: Pest 4 browser smoke (golden-path end-to-end)

**Files:**
- Test: `tests/Browser/CheckoutFlowTest.php`

- [ ] **Step 1: Write the browser test**

```php
<?php
// tests/Browser/CheckoutFlowTest.php

use App\Models\User;
use App\Support\ExamAttemptFinder;

it('lets a guest register from the results page and is redirected to Polar', function () {
    // Fake the package's HTTP calls to return a deterministic checkout URL
    \Illuminate\Support\Facades\Http::fake([
        '*/checkouts*' => \Illuminate\Support\Facades\Http::response([
            'id'  => 'chk_browser',
            'url' => 'https://sandbox.polar.sh/checkout/chk_browser',
        ], 201),
    ]);

    // Seed an attempt + cookie so the results page renders
    $sessionUuid = 'browser-'.fake()->uuid();
    $attempt = \App\Models\ExamAttempt::factory()->submitted()->create([
        'session_uuid' => $sessionUuid,
        'user_id'      => null,
    ]);

    $page = visit("/pruefungssimulation/{$attempt->id}/ergebnis")
        ->withCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid);

    $page->assertSee('12 Monate Zugang freischalten');
    $page->click('12 Monate Zugang freischalten');
    $page->assertSee('Neu hier');

    $page->fill('E-Mail', 'browser-test@example.com')
        ->fill('Passwort', 'password1234')
        ->fill('Passwort bestätigen', 'password1234')
        ->press('Konto erstellen und weiter zur Zahlung');

    $page->assertUrlContains('sandbox.polar.sh/checkout');

    expect(User::where('email', 'browser-test@example.com')->first())->not->toBeNull();
});
```

**Prerequisite:** the `ExamAttempt` factory must have a `submitted()` state that fills `submitted_at`, `score`, `total_questions`. Check `database/factories/ExamAttemptFactory.php`; add if missing.

- [ ] **Step 2: Run**

```bash
php artisan test --compact --filter=CheckoutFlowTest
```

Expected: PASS. If it fails, the likely cause is the register form action URL mismatch — verify the Form `action` attribute matches Fortify's register route.

- [ ] **Step 3: Commit**

```bash
git add tests/Browser/CheckoutFlowTest.php database/factories/ExamAttemptFactory.php
git commit -m "test: browser smoke for guest register→checkout flow"
```

---

## Task 21: Final sweep + full test run

- [ ] **Step 1: Grep for any remaining references to deleted symbols**

```bash
grep -RIn "paid_at\|polar_customer_id\|isPaid\|PolarCheckout\|PolarWebhookProcessor\|EnsurePaid" app/ resources/ routes/ tests/ database/
```

Expected: **zero** matches. If any remain, fix them now.

- [ ] **Step 2: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 3: Run full test suite**

```bash
php artisan test --compact
```

Expected: all green. Address any failures (most likely: tests that previously relied on `paid_at` state need to be migrated to `hasActiveAccess()` state).

- [ ] **Step 4: Build frontend**

```bash
npm run build
```

- [ ] **Step 5: Commit any final lint fixes**

```bash
git add -A
git commit -m "chore: final lint + cleanup post-Polar integration"
```

---

## Self-review notes (written after plan was drafted)

- **Spec §5.2 "Logged in with active access, logged out, re-auths":** covered by Task 11's `LoginResponse` test 3 ("skips checkout and redirects to home if user already has access").
- **Spec §8 test item #10 (order dated exactly 365 days ago):** add as an extra assertion inside Task 4's `hasActiveAccess` tests — boundary case noted but not a dedicated test yet. Acceptable; covered by the ">365 days" and "<365 days" tests bracketing it.
- **Spec §9 risk #12 (webhook queue vs sync):** `PolarEventListener implements ShouldQueue` already addresses this at the listener level regardless of the controller's sync/async behavior. Good.
- **Potential gap:** Fortify's default password rules require 8+ chars; `AppServiceProvider` sets production rules to 12+. Tests in Task 11 use `password1234` (12 chars) to be safe in both environments.
- **Potential gap:** `HomeController` may still be passing a `pricing` prop that uses the old `paid_at` query under the hood. Pricing service refactor in Task 7 handles this — re-verify controller still works after Task 7.
