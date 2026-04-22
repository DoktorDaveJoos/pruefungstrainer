# In-App Analytics Tracker Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a zero-cost, owner-only in-app web-analytics tracker that records anonymous pageviews and four named funnel events (`registered`, `paid`, `exam_started`, `exam_completed`) and exposes them on a single admin dashboard at `/admin/insights`.

**Architecture:** Two SQLite tables (`page_views`, `tracked_events`), one terminable middleware that logs HTML GET requests, one service class that records named events from controllers/listeners, one Inertia admin page rendering counts and a funnel. Visitor identity is a SHA256 hash of IP+UA+APP_KEY+today that rotates daily — no cookie, no consent banner.

**Tech Stack:** Laravel 13 · PHP 8.4 · SQLite · Inertia v3 + React 19 · Pest 4 · Tailwind v4 · shadcn/ui.

**Reference spec:** `docs/superpowers/specs/2026-04-22-in-app-analytics-tracker-design.md`

## File Structure

**Create:**
- `database/migrations/2026_04_22_100000_create_page_views_table.php`
- `database/migrations/2026_04_22_100001_create_tracked_events_table.php`
- `app/Models/PageView.php`
- `app/Models/TrackedEvent.php`
- `app/Services/Analytics/VisitorHash.php`
- `app/Services/Analytics/PathNormalizer.php`
- `app/Services/Analytics/RecordEvent.php`
- `app/Services/Analytics/InsightsQuery.php`
- `app/Http/Middleware/TrackPageView.php`
- `app/Http/Middleware/EnsureOwner.php`
- `app/Http/Controllers/Admin/InsightsController.php`
- `app/Console/Commands/PruneAnalyticsCommand.php`
- `resources/js/pages/admin/insights.tsx`
- `tests/Unit/Analytics/VisitorHashTest.php`
- `tests/Unit/Analytics/PathNormalizerTest.php`
- `tests/Feature/Analytics/TrackPageViewMiddlewareTest.php`
- `tests/Feature/Analytics/RecordEventTest.php`
- `tests/Feature/Analytics/InsightsPageTest.php`
- `tests/Feature/Analytics/PruneAnalyticsCommandTest.php`

**Modify:**
- `config/app.php` — add `owner_email` config key.
- `bootstrap/app.php` — append `TrackPageView` to web group; register `owner` middleware alias.
- `routes/web.php` — register `/admin/insights` route.
- `routes/console.php` — schedule `analytics:prune` daily at 03:00.
- `app/Actions/Fortify/CreateNewUser.php` — record `registered` event.
- `app/Http/Controllers/ExamController.php` — record `exam_started` and `exam_completed`.
- `app/Listeners/PolarEventListener.php` — record `paid` event.
- `resources/views/legal/datenschutz` equivalent Inertia page (`resources/js/pages/legal/datenschutz.tsx`) — add anonymous-analytics disclosure.

---

### Task 1: Database migrations and Eloquent models

**Files:**
- Create: `database/migrations/2026_04_22_100000_create_page_views_table.php`
- Create: `database/migrations/2026_04_22_100001_create_tracked_events_table.php`
- Create: `app/Models/PageView.php`
- Create: `app/Models/TrackedEvent.php`
- Test: inline schema assertion via `database-schema` — no dedicated test file.

- [ ] **Step 1: Generate the `page_views` migration file**

Run:
```bash
php artisan make:migration create_page_views_table --no-interaction
```

Then replace its contents with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table): void {
            $table->id();
            $table->char('visitor_hash', 64);
            $table->string('path', 512);
            $table->string('referrer_host', 255)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index(['path', 'created_at']);
            $table->index(['visitor_hash', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
```

Rename the generated file so its timestamp is `2026_04_22_100000_create_page_views_table.php`.

- [ ] **Step 2: Generate the `tracked_events` migration file**

Run:
```bash
php artisan make:migration create_tracked_events_table --no-interaction
```

Replace its contents with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracked_events', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 64);
            $table->char('visitor_hash', 64)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['name', 'created_at']);
            $table->index(['visitor_hash', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracked_events');
    }
};
```

Rename to `2026_04_22_100001_create_tracked_events_table.php`.

- [ ] **Step 3: Run the migrations**

Run: `php artisan migrate`
Expected: both tables created, no errors.

- [ ] **Step 4: Create the `PageView` model**

File: `app/Models/PageView.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'visitor_hash',
        'path',
        'referrer_host',
        'user_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
```

- [ ] **Step 5: Create the `TrackedEvent` model**

File: `app/Models/TrackedEvent.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackedEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'visitor_hash',
        'user_id',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];
}
```

- [ ] **Step 6: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: no errors, possibly a formatting diff.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_04_22_100000_create_page_views_table.php \
        database/migrations/2026_04_22_100001_create_tracked_events_table.php \
        app/Models/PageView.php app/Models/TrackedEvent.php
git commit -m "feat(analytics): schema + models for page_views and tracked_events"
```

---

### Task 2: `VisitorHash` service

**Files:**
- Create: `app/Services/Analytics/VisitorHash.php`
- Test: `tests/Unit/Analytics/VisitorHashTest.php`

- [ ] **Step 1: Write the failing test**

Run: `php artisan make:test --pest --unit Analytics/VisitorHashTest`

Replace the generated file with:

```php
<?php

use App\Services\Analytics\VisitorHash;
use Illuminate\Http\Request;

it('produces a 64-character hex hash', function (): void {
    $request = Request::create('/', 'GET', server: [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'Mozilla/5.0',
    ]);

    $hash = app(VisitorHash::class)->for($request);

    expect($hash)->toMatch('/^[0-9a-f]{64}$/');
});

it('is stable for the same ip+ua on the same day', function (): void {
    $first = Request::create('/', 'GET', server: [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'Mozilla/5.0',
    ]);
    $second = Request::create('/exam', 'GET', server: [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'Mozilla/5.0',
    ]);

    $service = app(VisitorHash::class);

    expect($service->for($first))->toBe($service->for($second));
});

it('rotates daily', function (): void {
    $request = Request::create('/', 'GET', server: [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'Mozilla/5.0',
    ]);
    $service = app(VisitorHash::class);

    $today = Carbon\CarbonImmutable::parse('2026-04-22 10:00:00');
    $tomorrow = $today->addDay();

    Carbon\CarbonImmutable::setTestNow($today);
    $a = $service->for($request);

    Carbon\CarbonImmutable::setTestNow($tomorrow);
    $b = $service->for($request);

    expect($a)->not->toBe($b);
});

it('differs for different user agents', function (): void {
    $a = Request::create('/', 'GET', server: [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'Mozilla/5.0',
    ]);
    $b = Request::create('/', 'GET', server: [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'curl/8.0',
    ]);
    $service = app(VisitorHash::class);

    expect($service->for($a))->not->toBe($service->for($b));
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact --filter=VisitorHashTest`
Expected: FAIL with "Class VisitorHash not found" or similar.

- [ ] **Step 3: Implement the service**

File: `app/Services/Analytics/VisitorHash.php`

```php
<?php

namespace App\Services\Analytics;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitorHash
{
    public function for(Request $request): string
    {
        $ip = $request->ip() ?? '0.0.0.0';
        $ua = (string) $request->userAgent();
        $day = Carbon::now()->toDateString();
        $salt = (string) config('app.key');

        return hash('sha256', $ip.'|'.$ua.'|'.$day.'|'.$salt);
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php artisan test --compact --filter=VisitorHashTest`
Expected: PASS (4 tests).

- [ ] **Step 5: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Services/Analytics/VisitorHash.php tests/Unit/Analytics/VisitorHashTest.php
git commit -m "feat(analytics): daily-rotating anonymous visitor hash"
```

---

### Task 3: `PathNormalizer` helper

**Files:**
- Create: `app/Services/Analytics/PathNormalizer.php`
- Test: `tests/Unit/Analytics/PathNormalizerTest.php`

- [ ] **Step 1: Write the failing test**

Run: `php artisan make:test --pest --unit Analytics/PathNormalizerTest`

Replace the generated file with:

```php
<?php

use App\Services\Analytics\PathNormalizer;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

it('returns the matched route uri prefixed with a slash', function (): void {
    $route = new Route(['GET'], '/pruefungssimulation/{attempt}', fn () => null);
    $route->bind(Request::create('/pruefungssimulation/42', 'GET'));
    $request = Request::create('/pruefungssimulation/42', 'GET');
    $request->setRouteResolver(fn () => $route);

    expect(app(PathNormalizer::class)->forRequest($request))
        ->toBe('/pruefungssimulation/{attempt}');
});

it('falls back to the raw path when no route is matched', function (): void {
    $request = Request::create('/no-route-here', 'GET');

    expect(app(PathNormalizer::class)->forRequest($request))
        ->toBe('/no-route-here');
});

it('truncates paths longer than 512 chars', function (): void {
    $long = '/x'.str_repeat('a', 600);
    $request = Request::create($long, 'GET');

    expect(strlen(app(PathNormalizer::class)->forRequest($request)))
        ->toBeLessThanOrEqual(512);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact --filter=PathNormalizerTest`
Expected: FAIL ("class not found").

- [ ] **Step 3: Implement the service**

File: `app/Services/Analytics/PathNormalizer.php`

```php
<?php

namespace App\Services\Analytics;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PathNormalizer
{
    public function forRequest(Request $request): string
    {
        $route = $request->route();

        $path = $route !== null
            ? '/'.ltrim($route->uri(), '/')
            : '/'.ltrim($request->path(), '/');

        return Str::limit($path, 512, '');
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php artisan test --compact --filter=PathNormalizerTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Services/Analytics/PathNormalizer.php tests/Unit/Analytics/PathNormalizerTest.php
git commit -m "feat(analytics): route-based path normalizer"
```

---

### Task 4: `RecordEvent` service

**Files:**
- Create: `app/Services/Analytics/RecordEvent.php`
- Test: `tests/Feature/Analytics/RecordEventTest.php`

- [ ] **Step 1: Write the failing test**

Run: `php artisan make:test --pest Analytics/RecordEventTest`

Replace the generated file with:

```php
<?php

use App\Models\TrackedEvent;
use App\Models\User;
use App\Services\Analytics\RecordEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists an event with the given name and metadata', function (): void {
    app(RecordEvent::class)->record('exam_started', metadata: ['attempt_id' => 42]);

    expect(TrackedEvent::count())->toBe(1);

    $event = TrackedEvent::first();
    expect($event->name)->toBe('exam_started');
    expect($event->metadata)->toBe(['attempt_id' => 42]);
    expect($event->user_id)->toBeNull();
    expect($event->visitor_hash)->toBeNull();
});

it('attaches the authenticated user id', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    app(RecordEvent::class)->record('registered');

    expect(TrackedEvent::first()->user_id)->toBe($user->id);
});

it('attaches a visitor hash when an http request is in scope', function (): void {
    $this->get('/');

    app(RecordEvent::class)->record('exam_started', metadata: ['attempt_id' => 1]);

    expect(TrackedEvent::first()->visitor_hash)->toMatch('/^[0-9a-f]{64}$/');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact --filter=RecordEventTest`
Expected: FAIL ("class not found").

- [ ] **Step 3: Implement the service**

File: `app/Services/Analytics/RecordEvent.php`

```php
<?php

namespace App\Services\Analytics;

use App\Models\TrackedEvent;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RecordEvent
{
    public function __construct(
        private readonly VisitorHash $visitorHash,
        private readonly Guard $auth,
    ) {}

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function record(string $name, ?array $metadata = null): void
    {
        TrackedEvent::create([
            'name' => $name,
            'visitor_hash' => $this->resolveVisitorHash(),
            'user_id' => $this->auth->id(),
            'metadata' => $metadata,
            'created_at' => Carbon::now(),
        ]);
    }

    private function resolveVisitorHash(): ?string
    {
        $request = app('request');

        if (! $request instanceof Request || $request->userAgent() === null) {
            return null;
        }

        return $this->visitorHash->for($request);
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `php artisan test --compact --filter=RecordEventTest`
Expected: PASS (3 tests).

- [ ] **Step 5: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Services/Analytics/RecordEvent.php tests/Feature/Analytics/RecordEventTest.php
git commit -m "feat(analytics): RecordEvent service"
```

---

### Task 5: `TrackPageView` middleware

**Files:**
- Create: `app/Http/Middleware/TrackPageView.php`
- Test: `tests/Feature/Analytics/TrackPageViewMiddlewareTest.php`

- [ ] **Step 1: Write the failing test**

Run: `php artisan make:test --pest Analytics/TrackPageViewMiddlewareTest`

Replace the generated file with:

```php
<?php

use App\Models\PageView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records a page view for a GET html response', function (): void {
    $this->get('/')->assertOk();

    expect(PageView::count())->toBe(1);

    $row = PageView::first();
    expect($row->path)->toBe('/');
    expect($row->visitor_hash)->toMatch('/^[0-9a-f]{64}$/');
    expect($row->user_id)->toBeNull();
});

it('skips non-GET requests', function (): void {
    $this->post('/pruefungssimulation/start');

    expect(PageView::count())->toBe(0);
});

it('skips the admin insights path', function (): void {
    $user = User::factory()->create([
        'email' => config('app.owner_email'),
    ]);
    $this->actingAs($user)->get('/admin/insights');

    expect(PageView::where('path', '/admin/insights')->count())->toBe(0);
});

it('skips bot user agents', function (): void {
    $this->withHeaders(['User-Agent' => 'Googlebot/2.1'])->get('/');

    expect(PageView::count())->toBe(0);
});

it('tags user_id when authenticated', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/');

    expect(PageView::first()?->user_id)->toBe($user->id);
});

it('normalizes dynamic route segments', function (): void {
    $this->get('/pruefungssimulation/99999');

    $row = PageView::first();
    expect($row?->path)->toBe('/pruefungssimulation/{attempt}');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact --filter=TrackPageViewMiddlewareTest`
Expected: FAIL (middleware not yet wired).

- [ ] **Step 3: Create the middleware class**

File: `app/Http/Middleware/TrackPageView.php`

```php
<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Services\Analytics\PathNormalizer;
use App\Services\Analytics\VisitorHash;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    private const SKIP_PATHS = [
        'admin/insights',
        'api/*',
        'livewire/*',
        '_debugbar/*',
        'up',
        'polar/*',
        'build/*',
    ];

    private const BOT_UA_PATTERN = '/bot|crawler|spider|preview|lighthouse|headlesschrome/i';

    public function __construct(
        private readonly VisitorHash $visitorHash,
        private readonly PathNormalizer $pathNormalizer,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $this->shouldRecord($request, $response)) {
            return;
        }

        PageView::create([
            'visitor_hash' => $this->visitorHash->for($request),
            'path' => $this->pathNormalizer->forRequest($request),
            'referrer_host' => $this->referrerHost($request),
            'user_id' => $request->user()?->id,
            'created_at' => Carbon::now(),
        ]);
    }

    private function shouldRecord(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains(strtolower($contentType), 'text/html')) {
            return false;
        }

        if ($request->is(...self::SKIP_PATHS)) {
            return false;
        }

        $ua = (string) $request->userAgent();
        if ($ua === '' || preg_match(self::BOT_UA_PATTERN, $ua) === 1) {
            return false;
        }

        return true;
    }

    private function referrerHost(Request $request): ?string
    {
        $referrer = $request->headers->get('referer');
        if ($referrer === null) {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);
        if (! is_string($host) || $host === $request->getHost()) {
            return null;
        }

        return substr($host, 0, 255);
    }
}
```

- [ ] **Step 4: Register the middleware in the web group**

Modify `bootstrap/app.php` inside `withMiddleware`:

```php
$middleware->web(append: [
    HandleAppearance::class,
    HandleInertiaRequests::class,
    AddLinkHeadersForPreloadedAssets::class,
    \App\Http\Middleware\TrackPageView::class,
]);
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --compact --filter=TrackPageViewMiddlewareTest`
Expected: PASS (6 tests).

- [ ] **Step 6: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Middleware/TrackPageView.php bootstrap/app.php \
        tests/Feature/Analytics/TrackPageViewMiddlewareTest.php
git commit -m "feat(analytics): terminable TrackPageView middleware"
```

---

### Task 6: Hook domain events in registration, exam, and Polar flow

**Files:**
- Modify: `app/Actions/Fortify/CreateNewUser.php` (add lines near end of `create`)
- Modify: `app/Http/Controllers/ExamController.php` (inject `RecordEvent`, call in `start` and `autoSubmitIfNeeded`)
- Modify: `app/Listeners/PolarEventListener.php` (record `paid` in `handleOrderCreated`)
- Test: extend `tests/Feature/Analytics/RecordEventTest.php` with funnel assertions, or create `tests/Feature/Analytics/FunnelEventsTest.php`.

- [ ] **Step 1: Write the failing funnel test**

Create `tests/Feature/Analytics/FunnelEventsTest.php`:

```php
<?php

use App\Models\ExamAttempt;
use App\Models\TrackedEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records the registered event when a new user is created', function (): void {
    $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'new@example.com',
        'password' => 'SuperSecret123!',
        'password_confirmation' => 'SuperSecret123!',
    ]);

    $event = TrackedEvent::where('name', 'registered')->first();
    expect($event)->not->toBeNull();
    expect($event->user_id)->toBe(User::where('email', 'new@example.com')->value('id'));
});

it('records exam_started when a guest starts an attempt', function (): void {
    $this->post(route('exam.start'));

    expect(TrackedEvent::where('name', 'exam_started')->count())->toBe(1);
});

it('records exam_completed with score metadata on submit', function (): void {
    $user = User::factory()->create();
    $user->forceFill(['email_verified_at' => now()])->save();
    // Give access so submit path runs end to end.
    // Use the existing test helpers in the codebase for setting paid access.
    // Fall back to direct DB writes if no helper exists.

    $this->actingAs($user)->post(route('exam.start'));
    $attempt = ExamAttempt::latest('id')->first();

    $this->actingAs($user)->post(route('exam.submit', $attempt));

    $event = TrackedEvent::where('name', 'exam_completed')->first();
    expect($event)->not->toBeNull();
    expect($event->metadata)->toHaveKeys(['attempt_id', 'score', 'total']);
});
```

Note: if the third test fails because of access gating, use the project's existing pattern for granting paid access in tests (grep for `hasActiveAccess` in `tests/`) and adjust.

- [ ] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --compact --filter=FunnelEventsTest`
Expected: FAIL (no hooks yet).

- [ ] **Step 3: Hook `registered` in `CreateNewUser`**

Edit `app/Actions/Fortify/CreateNewUser.php`. Import and call:

```php
use App\Services\Analytics\RecordEvent;
```

After the `if (app()->environment('local')) { ... }` block, add:

```php
app(RecordEvent::class)->record('registered');
```

- [ ] **Step 4: Hook `exam_started` and `exam_completed` in `ExamController`**

Edit `app/Http/Controllers/ExamController.php`:

1. Import: `use App\Services\Analytics\RecordEvent;`
2. Add a constructor parameter `private readonly RecordEvent $events,` (alongside the existing three services).
3. At the end of `start`, after the `redirect(route('exam.show', $attempt->id))` line but before returning, add:

```php
$this->events->record('exam_started', metadata: ['attempt_id' => $attempt->id]);
```

4. In `autoSubmitIfNeeded`, replace the method body with:

```php
private function autoSubmitIfNeeded(ExamAttempt $attempt): void
{
    if ($attempt->isSubmitted()) {
        return;
    }

    app(ExamScorer::class)->score($attempt);
    $attempt->update(['submitted_at' => now()]);

    $this->events->record('exam_completed', metadata: [
        'attempt_id' => $attempt->id,
        'score' => $attempt->score ?? 0,
        'total' => $attempt->total_questions,
    ]);
}
```

- [ ] **Step 5: Hook `paid` in `PolarEventListener`**

Edit `app/Listeners/PolarEventListener.php`:

1. Import: `use App\Services\Analytics\RecordEvent;`
2. Add a constructor:

```php
public function __construct(private readonly RecordEvent $events) {}
```

3. In `handleOrderCreated`, at the top of the method (before the dispatch calls), add:

```php
$this->events->record('paid', metadata: [
    'order_id' => $order->id,
]);
```

- [ ] **Step 6: Run the funnel tests to verify they pass**

Run: `php artisan test --compact --filter=FunnelEventsTest`
Expected: PASS (3 tests).

- [ ] **Step 7: Run the full test suite to catch regressions**

Run: `php artisan test --compact`
Expected: all green.

- [ ] **Step 8: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Fortify/CreateNewUser.php \
        app/Http/Controllers/ExamController.php \
        app/Listeners/PolarEventListener.php \
        tests/Feature/Analytics/FunnelEventsTest.php
git commit -m "feat(analytics): wire registered/paid/exam_started/exam_completed events"
```

---

### Task 7: `EnsureOwner` middleware + `owner_email` config

**Files:**
- Modify: `config/app.php`
- Create: `app/Http/Middleware/EnsureOwner.php`
- Modify: `bootstrap/app.php` (alias)
- Test: will be covered in Task 8 via `InsightsPageTest`.

- [ ] **Step 1: Add the `owner_email` config key**

Edit `config/app.php`. Near the top (after `name` or `url`), add:

```php
'owner_email' => env('APP_OWNER_EMAIL', 'drdavejoos@gmail.com'),
```

Also append `APP_OWNER_EMAIL=drdavejoos@gmail.com` to `.env.example`.

- [ ] **Step 2: Create the middleware**

File: `app/Http/Middleware/EnsureOwner.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $ownerEmail = config('app.owner_email');

        if ($user === null || $user->email !== $ownerEmail) {
            abort(404);
        }

        return $next($request);
    }
}
```

- [ ] **Step 3: Register the middleware alias**

Edit `bootstrap/app.php`. Inside `withMiddleware`, add:

```php
$middleware->alias([
    'owner' => \App\Http\Middleware\EnsureOwner::class,
]);
```

- [ ] **Step 4: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add config/app.php .env.example app/Http/Middleware/EnsureOwner.php bootstrap/app.php
git commit -m "feat(analytics): owner-only middleware + owner_email config"
```

---

### Task 8: `InsightsQuery` service + `InsightsController` + route

**Files:**
- Create: `app/Services/Analytics/InsightsQuery.php`
- Create: `app/Http/Controllers/Admin/InsightsController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Analytics/InsightsPageTest.php`

- [ ] **Step 1: Write the failing InsightsPage test**

Run: `php artisan make:test --pest Analytics/InsightsPageTest`

Replace contents with:

```php
<?php

use App\Models\PageView;
use App\Models\TrackedEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('404s for guests', function (): void {
    $this->get('/admin/insights')->assertNotFound();
});

it('404s for authenticated non-owners', function (): void {
    $user = User::factory()->create(['email' => 'random@example.com']);
    $this->actingAs($user)->get('/admin/insights')->assertNotFound();
});

it('renders insights for the owner', function (): void {
    PageView::create([
        'visitor_hash' => str_repeat('a', 64),
        'path' => '/',
        'referrer_host' => null,
        'user_id' => null,
        'created_at' => now(),
    ]);
    TrackedEvent::create([
        'name' => 'exam_started',
        'visitor_hash' => str_repeat('a', 64),
        'user_id' => null,
        'metadata' => ['attempt_id' => 1],
        'created_at' => now(),
    ]);

    $owner = User::factory()->create(['email' => config('app.owner_email')]);

    $this->actingAs($owner)
        ->get('/admin/insights')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/insights')
            ->has('overview.pageviewsToday')
            ->has('overview.examStartsToday')
            ->has('funnel')
            ->has('topPages')
            ->has('topReferrers')
            ->has('daily')
        );
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact --filter=InsightsPageTest`
Expected: FAIL (route missing).

- [ ] **Step 3: Create `InsightsQuery` service**

File: `app/Services/Analytics/InsightsQuery.php`

```php
<?php

namespace App\Services\Analytics;

use App\Models\PageView;
use App\Models\TrackedEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InsightsQuery
{
    /**
     * @return array{
     *     pageviewsToday: int,
     *     uniqueVisitorsToday: int,
     *     examStartsToday: int,
     *     examCompletionsToday: int,
     * }
     */
    public function overview(): array
    {
        $startOfToday = Carbon::now()->startOfDay();

        return [
            'pageviewsToday' => PageView::where('created_at', '>=', $startOfToday)->count(),
            'uniqueVisitorsToday' => PageView::where('created_at', '>=', $startOfToday)
                ->distinct('visitor_hash')
                ->count('visitor_hash'),
            'examStartsToday' => TrackedEvent::where('name', 'exam_started')
                ->where('created_at', '>=', $startOfToday)->count(),
            'examCompletionsToday' => TrackedEvent::where('name', 'exam_completed')
                ->where('created_at', '>=', $startOfToday)->count(),
        ];
    }

    /**
     * @return Collection<int, array{date: string, pageviews: int, uniqueVisitors: int}>
     */
    public function daily(int $days = 7): Collection
    {
        $start = Carbon::now()->startOfDay()->subDays($days - 1);

        $rows = PageView::selectRaw("date(created_at) as day, count(*) as pageviews, count(distinct visitor_hash) as unique_visitors")
            ->where('created_at', '>=', $start)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $byDay = $rows->keyBy('day');

        return collect(range(0, $days - 1))->map(function (int $offset) use ($start, $byDay): array {
            $day = $start->copy()->addDays($offset)->toDateString();
            $row = $byDay->get($day);

            return [
                'date' => $day,
                'pageviews' => (int) ($row->pageviews ?? 0),
                'uniqueVisitors' => (int) ($row->unique_visitors ?? 0),
            ];
        });
    }

    /**
     * @return array<int, array{step: string, count: int}>
     */
    public function funnel(int $days = 7): array
    {
        $start = Carbon::now()->startOfDay()->subDays($days - 1);

        $landing = PageView::where('path', '/')
            ->where('created_at', '>=', $start)->count();

        $registered = TrackedEvent::where('name', 'registered')
            ->where('created_at', '>=', $start)->count();

        $paid = TrackedEvent::where('name', 'paid')
            ->where('created_at', '>=', $start)->count();

        $examStarted = TrackedEvent::where('name', 'exam_started')
            ->where('created_at', '>=', $start)->count();

        $examCompleted = TrackedEvent::where('name', 'exam_completed')
            ->where('created_at', '>=', $start)->count();

        return [
            ['step' => 'visited_home', 'count' => $landing],
            ['step' => 'registered', 'count' => $registered],
            ['step' => 'paid', 'count' => $paid],
            ['step' => 'exam_started', 'count' => $examStarted],
            ['step' => 'exam_completed', 'count' => $examCompleted],
        ];
    }

    /**
     * @return Collection<int, array{path: string, count: int}>
     */
    public function topPages(int $days = 7, int $limit = 10): Collection
    {
        $start = Carbon::now()->startOfDay()->subDays($days - 1);

        return PageView::selectRaw('path, count(*) as c')
            ->where('created_at', '>=', $start)
            ->groupBy('path')
            ->orderByDesc('c')
            ->limit($limit)
            ->get()
            ->map(fn ($r): array => ['path' => $r->path, 'count' => (int) $r->c]);
    }

    /**
     * @return Collection<int, array{host: string, count: int}>
     */
    public function topReferrers(int $days = 7, int $limit = 10): Collection
    {
        $start = Carbon::now()->startOfDay()->subDays($days - 1);

        return PageView::selectRaw('referrer_host as host, count(*) as c')
            ->where('created_at', '>=', $start)
            ->whereNotNull('referrer_host')
            ->groupBy('referrer_host')
            ->orderByDesc('c')
            ->limit($limit)
            ->get()
            ->map(fn ($r): array => ['host' => $r->host, 'count' => (int) $r->c]);
    }
}
```

- [ ] **Step 4: Create the controller**

File: `app/Http/Controllers/Admin/InsightsController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\InsightsQuery;
use Inertia\Inertia;
use Inertia\Response;

class InsightsController extends Controller
{
    public function __invoke(InsightsQuery $query): Response
    {
        return Inertia::render('admin/insights', [
            'overview' => $query->overview(),
            'daily' => $query->daily(),
            'funnel' => $query->funnel(),
            'topPages' => $query->topPages(),
            'topReferrers' => $query->topReferrers(),
        ]);
    }
}
```

- [ ] **Step 5: Register the route**

Edit `routes/web.php`. Add the import:

```php
use App\Http\Controllers\Admin\InsightsController;
```

And a new route group at the bottom (before `require __DIR__.'/settings.php';`):

```php
Route::middleware(['auth', 'owner'])->group(function (): void {
    Route::get('/admin/insights', InsightsController::class)->name('admin.insights');
});
```

- [ ] **Step 6: Create the Inertia page (minimal, tests only assert structure)**

File: `resources/js/pages/admin/insights.tsx`

```tsx
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

type Overview = {
    pageviewsToday: number;
    uniqueVisitorsToday: number;
    examStartsToday: number;
    examCompletionsToday: number;
};

type DailyRow = { date: string; pageviews: number; uniqueVisitors: number };
type FunnelRow = { step: string; count: number };
type TopPageRow = { path: string; count: number };
type TopReferrerRow = { host: string; count: number };

type Props = {
    overview: Overview;
    daily: DailyRow[];
    funnel: FunnelRow[];
    topPages: TopPageRow[];
    topReferrers: TopReferrerRow[];
};

const STEP_LABELS: Record<string, string> = {
    visited_home: 'Landing visited',
    registered: 'Registered',
    paid: 'Paid',
    exam_started: 'Exam started',
    exam_completed: 'Exam completed',
};

export default function InsightsPage({ overview, daily, funnel, topPages, topReferrers }: Props) {
    const funnelTop = funnel[0]?.count ?? 0;

    return (
        <AppLayout>
            <Head title="Insights" />

            <div className="mx-auto flex w-full max-w-4xl flex-col gap-6 px-4 py-8 sm:px-6">
                <h1 className="text-2xl font-semibold tracking-tight">Insights</h1>

                <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <KpiCard label="Pageviews today" value={overview.pageviewsToday} />
                    <KpiCard label="Unique visitors today" value={overview.uniqueVisitorsToday} />
                    <KpiCard label="Exam starts today" value={overview.examStartsToday} />
                    <KpiCard label="Exam completions today" value={overview.examCompletionsToday} />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Last 7 days</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date</TableHead>
                                    <TableHead className="text-right">Pageviews</TableHead>
                                    <TableHead className="text-right">Unique visitors</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {daily.map((row) => (
                                    <TableRow key={row.date}>
                                        <TableCell>{row.date}</TableCell>
                                        <TableCell className="text-right tabular-nums">{row.pageviews}</TableCell>
                                        <TableCell className="text-right tabular-nums">{row.uniqueVisitors}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Funnel (last 7 days)</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-2">
                        {funnel.map((row) => {
                            const pct = funnelTop > 0 ? Math.round((row.count / funnelTop) * 100) : 0;
                            return (
                                <div key={row.step} className="flex items-center justify-between text-sm">
                                    <span>{STEP_LABELS[row.step] ?? row.step}</span>
                                    <span className="tabular-nums text-muted-foreground">
                                        {row.count} ({pct}%)
                                    </span>
                                </div>
                            );
                        })}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Top pages (last 7 days)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Path</TableHead>
                                    <TableHead className="text-right">Views</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {topPages.map((row) => (
                                    <TableRow key={row.path}>
                                        <TableCell className="font-mono text-xs">{row.path}</TableCell>
                                        <TableCell className="text-right tabular-nums">{row.count}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Top referrers (last 7 days)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Host</TableHead>
                                    <TableHead className="text-right">Visits</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {topReferrers.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={2} className="text-center text-sm text-muted-foreground">
                                            No external referrers yet.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    topReferrers.map((row) => (
                                        <TableRow key={row.host}>
                                            <TableCell>{row.host}</TableCell>
                                            <TableCell className="text-right tabular-nums">{row.count}</TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

function KpiCard({ label, value }: { label: string; value: number }) {
    return (
        <Card>
            <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium text-muted-foreground">{label}</CardTitle>
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-semibold tabular-nums">{value}</div>
            </CardContent>
        </Card>
    );
}
```

- [ ] **Step 7: Run the test to verify it passes**

Run: `php artisan test --compact --filter=InsightsPageTest`
Expected: PASS (3 tests).

- [ ] **Step 8: Run Pint, type-check, format**

```bash
vendor/bin/pint --dirty --format agent
npm run types:check
npm run format
```

Expected: clean.

- [ ] **Step 9: Commit**

```bash
git add app/Services/Analytics/InsightsQuery.php \
        app/Http/Controllers/Admin/InsightsController.php \
        routes/web.php resources/js/pages/admin/insights.tsx \
        tests/Feature/Analytics/InsightsPageTest.php
git commit -m "feat(analytics): /admin/insights owner dashboard"
```

---

### Task 9: `analytics:prune` command + schedule

**Files:**
- Create: `app/Console/Commands/PruneAnalyticsCommand.php`
- Modify: `routes/console.php` (schedule)
- Test: `tests/Feature/Analytics/PruneAnalyticsCommandTest.php`

- [ ] **Step 1: Write the failing test**

Run: `php artisan make:test --pest Analytics/PruneAnalyticsCommandTest`

Replace contents with:

```php
<?php

use App\Models\PageView;
use App\Models\TrackedEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deletes rows older than 90 days and keeps younger rows', function (): void {
    PageView::create([
        'visitor_hash' => str_repeat('a', 64),
        'path' => '/',
        'referrer_host' => null,
        'user_id' => null,
        'created_at' => now()->subDays(91),
    ]);
    PageView::create([
        'visitor_hash' => str_repeat('b', 64),
        'path' => '/',
        'referrer_host' => null,
        'user_id' => null,
        'created_at' => now()->subDays(30),
    ]);
    TrackedEvent::create([
        'name' => 'paid',
        'visitor_hash' => null,
        'user_id' => null,
        'metadata' => null,
        'created_at' => now()->subDays(95),
    ]);
    TrackedEvent::create([
        'name' => 'paid',
        'visitor_hash' => null,
        'user_id' => null,
        'metadata' => null,
        'created_at' => now()->subDays(10),
    ]);

    $this->artisan('analytics:prune')->assertSuccessful();

    expect(PageView::count())->toBe(1);
    expect(TrackedEvent::count())->toBe(1);
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact --filter=PruneAnalyticsCommandTest`
Expected: FAIL ("Command analytics:prune is not defined").

- [ ] **Step 3: Create the command**

Run: `php artisan make:command PruneAnalyticsCommand --no-interaction`

Replace `app/Console/Commands/PruneAnalyticsCommand.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\PageView;
use App\Models\TrackedEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneAnalyticsCommand extends Command
{
    protected $signature = 'analytics:prune {--days=90}';

    protected $description = 'Delete analytics rows older than N days (default 90).';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $pageViews = PageView::where('created_at', '<', $cutoff)->delete();
        $events = TrackedEvent::where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$pageViews} page views and {$events} events older than {$days} days.");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Schedule it daily at 03:00**

Edit `routes/console.php`. Add at the bottom:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('analytics:prune')->dailyAt('03:00');
```

If `Schedule::` is already imported, skip the `use` line.

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --compact --filter=PruneAnalyticsCommandTest`
Expected: PASS.

- [ ] **Step 6: Run Pint and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Console/Commands/PruneAnalyticsCommand.php routes/console.php \
        tests/Feature/Analytics/PruneAnalyticsCommandTest.php
git commit -m "feat(analytics): analytics:prune command scheduled daily"
```

---

### Task 10: Privacy disclosure in Datenschutz

**Files:**
- Modify: `resources/js/pages/legal/datenschutz.tsx`

- [ ] **Step 1: Locate the existing Datenschutz page**

Run: `ls resources/js/pages/legal/`

Confirm `datenschutz.tsx` exists.

- [ ] **Step 2: Read the file to find the right insertion point**

Read the file and locate the section describing data collection or logs. The new paragraph goes at the end of that section, before the cookies section if present.

- [ ] **Step 3: Add an anonymous-analytics section**

Add this section (German copy; adjust heading level/classes to match the rest of the page):

```tsx
<section>
    <h2>Anonyme Nutzungsstatistik</h2>
    <p>
        Wir erfassen anonyme Zugriffe auf unsere Seiten, um zu verstehen,
        welche Inhalte aufgerufen werden. Dabei wird kein Cookie gesetzt und
        keine personenbezogene Kennung dauerhaft gespeichert. Als Besucher-
        Kennung dient ein täglich rotierender SHA-256-Hash aus IP-Adresse,
        User-Agent, einem serverseitigen Geheimnis und dem aktuellen Datum.
        Rohdaten werden nach 90 Tagen automatisch gelöscht. Die Erfassung
        erfolgt auf Grundlage unseres berechtigten Interesses an einer
        stabilen, kostenfrei für Besucher nutzbaren Anwendung
        (Art. 6 Abs. 1 lit. f DSGVO).
    </p>
</section>
```

- [ ] **Step 4: Format and commit**

```bash
npm run format
git add resources/js/pages/legal/datenschutz.tsx
git commit -m "content(datenschutz): disclose anonymous analytics"
```

---

### Task 11: End-to-end verification

**Files:** none modified.

- [ ] **Step 1: Run the full test suite**

Run: `composer run test`
Expected: all green, Pint clean.

- [ ] **Step 2: Run the dev server and exercise the flow manually**

Ask the user (or, in a solo-dev worktree, run yourself):

```bash
npm run dev
```

In another shell:

```bash
php artisan serve
```

1. Visit `/` — confirm a `page_views` row is created with `path='/'`.
2. Visit `/pruefungssimulation/start` as a guest — confirm an `exam_started` event.
3. Register a new user — confirm a `registered` event.
4. Log in as `drdavejoos@gmail.com` and visit `/admin/insights` — page renders with non-zero KPIs.
5. Log in as a different user and visit `/admin/insights` — 404.

Use `php artisan tinker --execute 'App\Models\PageView::count();'` etc. to confirm.

- [ ] **Step 3: Run the prune command with a short window as a dry-run**

```bash
php artisan analytics:prune --days=9999
```

Expected: "Pruned 0 page views and 0 events…" — confirms command works without destroying real data.

- [ ] **Step 4: Final commit (if anything was touched during verification)**

Only if any files changed; otherwise skip.

---

## Self-Review Notes

**Spec coverage:**

- Two tables w/ indexes — Task 1 ✓
- Daily-rotating visitor hash — Task 2 ✓
- Path normalization — Task 3 ✓
- Terminable middleware with skip rules + bot filter — Task 5 ✓
- Four named events on correct call sites — Task 6 ✓
- `EnsureOwner` middleware + config — Task 7 ✓
- Admin insights page (4 KPIs, funnel, top pages, top referrers, 7-day daily) — Task 8 ✓
- 90-day retention via scheduled command — Task 9 ✓
- Datenschutz disclosure — Task 10 ✓
- Out-of-scope items (geo, cohorts, caching) — respected ✓

**Type consistency:** `RecordEvent::record()` takes `(string $name, ?array $metadata = null)` — same signature used in every call site. `InsightsQuery` public methods: `overview()`, `daily()`, `funnel()`, `topPages()`, `topReferrers()` — matched in controller and Inertia page prop types.

**Placeholder scan:** Task 6 step 1 contains a single contingency note about paid-access test helpers — resolvable by grepping `tests/` for `hasActiveAccess`. Not a placeholder in the "TBD" sense — the behavior is specified, only the exact helper path varies.

**One known Pest 4 gotcha:** `Carbon\CarbonImmutable::setTestNow` in `VisitorHashTest` — reset between tests is automatic in Pest 4 with the Laravel plugin, so no manual `afterEach` needed.
