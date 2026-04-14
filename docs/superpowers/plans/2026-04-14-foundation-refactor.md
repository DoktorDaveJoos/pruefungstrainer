# Foundation Refactor Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reshape the Laravel-Jetstream-style scaffold into a single-user-per-account B2C app ready for the exam-simulator build. Remove personal-team auto-creation, `current_team_id`, `is_personal`, team-slug routing, team UI. Keep `Team` / `Membership` / `TeamInvitation` tables + models + role enums **dormant** as B2B hooks for a future v2. Switch app locale to German.

**Architecture:** Pure refactor — deletions ordered so each commit leaves the app in a working, testable state. Code-level decoupling happens before the DB migration drops the columns. Existing Pest suite is the safety net; tests whose subjects are deleted (`tests/Feature/Teams/*`) are removed as part of the approved teams rip-out.

**Tech Stack:** Laravel 13, Fortify 1, Inertia 3, React 19, Pest 4, Wayfinder 0, Tailwind 4, PHP 8.4.

## Subsequent plans (out of scope for this plan)

1. **Plan 2 — Data model & seed:** Port de-dsb-learner schema (`modules`, `questions`, `answers`). Add `exam_attempts`, `exam_answers`, question tags (topic + difficulty), `user.paid_at`.
2. **Plan 3 — Question tagging pipeline:** Claude-assisted classification of 222 questions into 8 BSI topic areas + Basis/Experte heuristic. Seed script + review workflow.
3. **Plan 4 — Exam simulation flow:** Anonymous session cookie, server-authoritative 60-min timer, 50-question multi-select with all-or-nothing scoring, back-nav, flag-for-review, auto-submit, results screen with locked preview.
4. **Plan 5 — Freies Lernen flow:** One-question loop with per-option instant feedback (de-dsb-learner clone), wrong-only toggle, progress strip, per-user tracking separate from exam attempts.
5. **Plan 6 — Checkout & Polar integration:** Paywall UI, `/checkout` creates Polar session, webhook handler claims anonymous attempt via `session_uuid` metadata, `user.paid_at` gate on premium routes, Kleinunternehmer-safe Widerrufs-waiver.
6. **Plan 7 — Landing page + legal:** Public landing with hero + how-it-works + pricing card (Founder's Price counter) + FAQ. AGB / Datenschutz / Impressum pages. Post-login dashboard with two shadcn Cards ("Freies Lernen" / "Prüfungssimulation").

---

## File Structure

### Files to DELETE

```
app/Actions/Fortify/CreateTeam.php
app/Concerns/HasTeams.php
app/Concerns/GeneratesUniqueTeamSlugs.php
app/Http/Controllers/Teams/TeamController.php
app/Http/Controllers/Teams/TeamInvitationController.php
app/Http/Controllers/Teams/TeamMemberController.php
app/Http/Middleware/EnsureTeamMembership.php
app/Http/Middleware/SetTeamUrlDefaults.php
app/Policies/TeamPolicy.php
app/Support/UserTeam.php
app/Support/TeamPermissions.php
resources/js/pages/teams/index.tsx
resources/js/pages/teams/edit.tsx
resources/js/pages/teams/ (entire dir if no other files remain)
tests/Feature/Teams/TeamTest.php
tests/Feature/Teams/TeamInvitationTest.php
tests/Feature/Teams/TeamMemberTest.php
tests/Feature/Teams/ (entire dir)
```

### Files to MODIFY

```
config/app.php                                      # locale = de
app/Actions/Fortify/CreateNewUser.php               # drop CreateTeam invocation
app/Models/User.php                                 # drop HasTeams, drop current_team_id from Fillable
app/Models/Team.php                                 # drop is_personal-related code
app/Http/Middleware/HandleInertiaRequests.php       # drop currentTeam / teams shared props
app/Http/Responses/LoginResponse.php                # drop currentTeam/personalTeam lookup + URL::defaults
app/Http/Responses/RegisterResponse.php             # align with LoginResponse if team-aware (verify)
routes/web.php                                      # drop {current_team} prefix + invitations route
routes/settings.php                                 # drop team routes
bootstrap/app.php                                   # drop team middleware registrations (if any)
resources/js/layouts/app-sidebar-layout.tsx         # drop team switcher (verify exact filename)
resources/js/components/app-sidebar.tsx             # drop team nav items
database/factories/UserFactory.php                  # drop current_team_id if referenced
```

### Migrations to ADD

```
database/migrations/YYYY_MM_DD_HHMMSS_drop_team_columns_for_foundation_cleanup.php
```

### Files to KEEP (dormant hooks)

```
app/Models/Team.php                     # minus is_personal usage
app/Models/Membership.php
app/Models/TeamInvitation.php
app/Enums/TeamRole.php
app/Enums/TeamPermission.php
database/migrations/2026_01_27_000001_create_teams_table.php    # creates teams tables — stays
```

The `2026_01_27_000002_add_current_team_id_to_users_table.php` migration stays in history; the new drop migration reverses its effect.

---

## Tasks

### Task 1: Baseline — confirm green suite before refactor

**Files:** none (verification only)

- [ ] **Step 1: Run full suite, confirm baseline green**

Run:
```bash
php artisan test --compact
```

Expected: all tests pass. If any fail, fix before continuing — the refactor safety net requires a green baseline.

- [ ] **Step 2: Commit a baseline marker**

```bash
git checkout -b refactor/foundation-cleanup
git commit --allow-empty -m "chore: baseline before foundation refactor"
```

---

### Task 2: Switch application locale to German

**Files:**
- Modify: `config/app.php`

- [ ] **Step 1: Update locale defaults**

Edit `config/app.php`, change the three locale lines:

```php
'locale' => env('APP_LOCALE', 'de'),

'fallback_locale' => env('APP_FALLBACK_LOCALE', 'de'),

'faker_locale' => env('APP_FAKER_LOCALE', 'de_DE'),
```

- [ ] **Step 2: Update `.env` + `.env.example`**

In both files, ensure:
```
APP_LOCALE=de
APP_FALLBACK_LOCALE=de
APP_FAKER_LOCALE=de_DE
```

Add the lines if missing. Do not commit `.env` (it's gitignored), only `.env.example`.

- [ ] **Step 3: Verify config reads correctly**

Run:
```bash
php artisan config:show app.locale
php artisan config:show app.fallback_locale
php artisan config:show app.faker_locale
```

Expected output: `de`, `de`, `de_DE`.

- [ ] **Step 4: Run full suite**

Run:
```bash
php artisan test --compact
```

Expected: all tests pass. Locale change should not affect anything yet (no translation calls).

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add config/app.php .env.example
git commit -m "chore: set default locale to German"
```

---

### Task 3: Decouple `LoginResponse` from teams

**Files:**
- Modify: `app/Http/Responses/LoginResponse.php`
- Test: `tests/Feature/Auth/AuthenticationTest.php` (existing — verify still passes)

- [ ] **Step 1: Rewrite `LoginResponse` to redirect cleanly to `/dashboard`**

Replace the file contents with:

```php
<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 200)
            : redirect()->intended(route('dashboard'));
    }
}
```

Note: the previous version looked up `currentTeam`/`personalTeam` and set `URL::defaults(['current_team' => $team->slug])`. Both disappear — the `dashboard` route will no longer take a team parameter after Task 6.

- [ ] **Step 2: Inspect `RegisterResponse` + `TwoFactorLoginResponse` for the same pattern**

Read:
```bash
```

Use Read tool on `app/Http/Responses/RegisterResponse.php` and `app/Http/Responses/TwoFactorLoginResponse.php`. If either references `currentTeam`, `personalTeam`, or `URL::defaults(['current_team' => ...])`, apply the same cleanup — drop team references, redirect to `route('dashboard')`.

- [ ] **Step 3: Run auth tests**

Run:
```bash
php artisan test --compact --filter=Auth
```

Expected: all tests still pass. AuthenticationTest covers the login redirect — if it asserts a specific redirect target, this should still succeed because `dashboard` is still a named route.

- [ ] **Step 4: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Responses/
git commit -m "refactor: drop team lookup from login / register responses"
```

---

### Task 4: Drop team-related shared props from `HandleInertiaRequests`

**Files:**
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`

- [ ] **Step 1: Remove `currentTeam` and `teams` from shared props**

Replace the `share` method with:

```php
public function share(Request $request): array
{
    $user = $request->user();

    return [
        ...parent::share($request),
        'name' => config('app.name'),
        'auth' => [
            'user' => $user,
        ],
        'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
    ];
}
```

Removed: the `currentTeam` and `teams` closures that called `$user->currentTeam`, `$user->toUserTeam(...)`, `$user->toUserTeams(...)`.

- [ ] **Step 2: Run suite to confirm middleware + any feature test that exercises Inertia props still passes**

Run:
```bash
php artisan test --compact
```

Expected: all pass. Some frontend pages may reference `currentTeam` / `teams` props (sidebar, teams pages) — those pages will be deleted in later tasks, but **for now** React may log missing-prop warnings. That's fine for PHP tests.

- [ ] **Step 3: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Middleware/HandleInertiaRequests.php
git commit -m "refactor: drop team shared props from Inertia middleware"
```

---

### Task 5: Remove team creation from registration + delete `CreateTeam` action

**Files:**
- Modify: `app/Actions/Fortify/CreateNewUser.php`
- Delete: `app/Actions/Fortify/CreateTeam.php` — **NOTE:** path is actually `app/Actions/Teams/CreateTeam.php` per the current `CreateNewUser` import; verify with `Glob`/`Read` before deleting.
- Test: `tests/Feature/Auth/RegistrationTest.php` (existing — verify)

- [ ] **Step 1: Locate the `CreateTeam` action**

Run:
```bash
```

Use Grep:
```
pattern: class CreateTeam
path: app
```

Expected: returns the full file path. Use that path for deletion later.

- [ ] **Step 2: Rewrite `CreateNewUser` to create only a user**

Replace the file contents with:

```php
<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);
    }
}
```

Removed: `CreateTeam` constructor param, `use App\Actions\Teams\CreateTeam;`, the `DB::transaction` wrapper (not needed for single insert), the `$this->createTeam->handle(...)` call.

- [ ] **Step 3: Delete the `CreateTeam` action file**

Run (using the path from Step 1 — likely `app/Actions/Teams/CreateTeam.php`):
```bash
rm app/Actions/Teams/CreateTeam.php
```

If `app/Actions/Teams/` is now empty, remove the dir:
```bash
rmdir app/Actions/Teams
```

- [ ] **Step 4: Run registration tests**

Run:
```bash
php artisan test --compact --filter=Registration
```

Expected: all pass. `RegistrationTest` asserts user creation — it should still succeed since we still create a User.

**If test fails** because it asserts team creation: update the test to only assert user creation. Example change:

```php
// Before (if present):
test('new users receive a personal team', function () {
    // ...
});

// After: remove this test entirely.
```

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "refactor: remove personal team auto-creation from registration"
```

---

### Task 6: Simplify `routes/web.php` (remove team prefix + invitation route)

**Files:**
- Modify: `routes/web.php`
- Test: `tests/Feature/DashboardTest.php` (existing — verify)

- [ ] **Step 1: Replace `routes/web.php` with team-free version**

Replace the file contents with:

```php
<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
```

Removed: `{current_team}` prefix, `EnsureTeamMembership` middleware, the `invitations/{invitation}/accept` route, the `TeamInvitationController` import, the `EnsureTeamMembership` import.

- [ ] **Step 2: Regenerate Wayfinder routes**

Run:
```bash
php artisan wayfinder:generate --with-form
```

Expected: updates `resources/js/routes/` and `resources/js/actions/` — the dashboard URL helper will no longer take a team slug parameter. Team-related route helpers disappear.

- [ ] **Step 3: Run dashboard test**

Run:
```bash
php artisan test --compact --filter=DashboardTest
```

Expected: passes. If the test constructs the URL using the old team-scoped route helper, update it to use the new helper (no team param).

- [ ] **Step 4: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add routes/web.php resources/js/routes resources/js/actions
git commit -m "refactor: drop team-scoped dashboard routing"
```

---

### Task 7: Simplify `routes/settings.php` (remove team routes)

**Files:**
- Modify: `routes/settings.php`

- [ ] **Step 1: Replace `routes/settings.php` with team-free version**

Replace the file contents with:

```php
<?php

use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::inertia('settings/appearance', 'settings/appearance')->name('appearance.edit');
});
```

Removed: all `settings/teams/*` routes, the `TeamController`/`TeamInvitationController`/`TeamMemberController`/`EnsureTeamMembership` imports, the nested `EnsureTeamMembership` middleware group.

- [ ] **Step 2: Regenerate Wayfinder routes**

Run:
```bash
php artisan wayfinder:generate --with-form
```

- [ ] **Step 3: Run settings tests**

Run:
```bash
php artisan test --compact --filter=Settings
```

Expected: `ProfileUpdateTest` and `SecurityTest` pass. No team tests in this path.

- [ ] **Step 4: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add routes/settings.php resources/js/routes resources/js/actions
git commit -m "refactor: drop team settings routes"
```

---

### Task 8: Delete team middleware

**Files:**
- Delete: `app/Http/Middleware/EnsureTeamMembership.php`
- Delete: `app/Http/Middleware/SetTeamUrlDefaults.php`
- Modify: `bootstrap/app.php` (if either middleware is registered in `$middleware->alias()` or `$middleware->append()`, remove the registration)

- [ ] **Step 1: Check `bootstrap/app.php` for middleware registrations**

Use Read on `bootstrap/app.php`. If `EnsureTeamMembership::class` or `SetTeamUrlDefaults::class` are imported or registered (e.g. via `$middleware->alias([...])` or `$middleware->web([...])`), remove those references.

Example (only if the file contains them):
```php
// BEFORE
use App\Http\Middleware\EnsureTeamMembership;
use App\Http\Middleware\SetTeamUrlDefaults;

->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [SetTeamUrlDefaults::class]);
    $middleware->alias([
        'team.member' => EnsureTeamMembership::class,
    ]);
})

// AFTER
->withMiddleware(function (Middleware $middleware) {
    //
})
```

- [ ] **Step 2: Delete the middleware files**

```bash
rm app/Http/Middleware/EnsureTeamMembership.php
rm app/Http/Middleware/SetTeamUrlDefaults.php
```

- [ ] **Step 3: Run full suite**

```bash
php artisan test --compact
```

Expected: Auth/Settings/Dashboard tests pass. Teams tests will fail (depend on deleted middleware); they get deleted in Task 13 — leave failing for now.

- [ ] **Step 4: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "refactor: delete team-membership middleware"
```

---

### Task 9: Delete team controllers + `TeamPolicy`

**Files:**
- Delete: `app/Http/Controllers/Teams/TeamController.php`
- Delete: `app/Http/Controllers/Teams/TeamInvitationController.php`
- Delete: `app/Http/Controllers/Teams/TeamMemberController.php`
- Delete: `app/Http/Controllers/Teams/` (dir, once empty)
- Delete: `app/Policies/TeamPolicy.php`
- Modify: `app/Providers/AuthServiceProvider.php` (if it explicitly registers `TeamPolicy`, remove)

- [ ] **Step 1: Check for explicit policy registration**

Use Grep:
```
pattern: TeamPolicy
path: app/Providers
```

If found, edit the provider to remove the `Team::class => TeamPolicy::class` entry from the `$policies` array.

- [ ] **Step 2: Delete the controllers + policy**

```bash
rm -r app/Http/Controllers/Teams
rm app/Policies/TeamPolicy.php
```

- [ ] **Step 3: Run full suite**

```bash
php artisan test --compact
```

Expected: Auth/Settings/Dashboard still pass. Team tests now fail harder (deleted controllers). Still deleting those tests in Task 13.

- [ ] **Step 4: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "refactor: delete team controllers and policy"
```

---

### Task 10: Remove `HasTeams` trait from `User` + delete concerns

**Files:**
- Modify: `app/Models/User.php`
- Delete: `app/Concerns/HasTeams.php`
- Delete: `app/Concerns/GeneratesUniqueTeamSlugs.php`

- [ ] **Step 1: Simplify the `User` model**

Replace the file contents with:

```php
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
```

Removed: `use App\Concerns\HasTeams;`, the `HasTeams` trait usage, `current_team_id` from the `#[Fillable]` attribute.

- [ ] **Step 2: Delete `HasTeams` trait**

```bash
rm app/Concerns/HasTeams.php
```

- [ ] **Step 3: Check if `GeneratesUniqueTeamSlugs` is used only by Team**

Use Grep:
```
pattern: use App\\Concerns\\GeneratesUniqueTeamSlugs
path: app
```

Expected: only `app/Models/Team.php` (or nothing). If used anywhere else, stop and investigate.

- [ ] **Step 4: Remove the trait usage from `Team` model**

Use Read on `app/Models/Team.php`. Remove:
- `use App\Concerns\GeneratesUniqueTeamSlugs;` import line
- `GeneratesUniqueTeamSlugs` from the `use X, Y, Z;` line inside the class

If the trait auto-generated slugs via a `booted()` hook, that hook also goes away. Since the tables are dormant (no code inserts teams), not having slug generation is fine.

- [ ] **Step 5: Delete the slug concern**

```bash
rm app/Concerns/GeneratesUniqueTeamSlugs.php
```

- [ ] **Step 6: Run full suite**

```bash
php artisan test --compact
```

Expected: Auth/Settings/Dashboard pass. `HasTeams` was providing `$user->currentTeam` etc. — anywhere else in the codebase that used those methods (outside the files we already edited) will fail.

**If any non-team test fails** (e.g., profile or security), investigate — the test may be reaching into `$user->personalTeam()` or similar. Replace with direct data access or remove the assertion.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "refactor: drop HasTeams trait + team-slug concern from user model"
```

---

### Task 11: Delete Inertia team pages + clean sidebar / nav

**Files:**
- Delete: `resources/js/pages/teams/index.tsx`
- Delete: `resources/js/pages/teams/edit.tsx`
- Delete: `resources/js/pages/teams/` (dir, once empty)
- Modify: `resources/js/components/app-sidebar.tsx` (remove team switcher + team nav items)
- Modify: `resources/js/layouts/app-layout.tsx` or similar (remove `teams` / `currentTeam` prop usage)

- [ ] **Step 1: Delete the team page components**

```bash
rm -r resources/js/pages/teams
```

- [ ] **Step 2: Clean team references from sidebar + layouts**

Use Grep:
```
pattern: currentTeam|team-switcher|teams\.index|TeamSwitcher
path: resources/js
```

For each match, Read the file and remove:
- Imports of team-related components (e.g. `TeamSwitcher`)
- JSX that references `currentTeam`, `teams`, or renders a switcher
- Nav items linking to `teams.*` routes
- Type references to `UserTeam` / `TeamPermissions` in shared prop types

If a dedicated `team-switcher.tsx` component exists, delete it:
```bash
# verify path with Glob first
rm resources/js/components/team-switcher.tsx
```

- [ ] **Step 3: Build frontend to surface any unresolved imports**

```bash
npm run build
```

Expected: succeeds. Any remaining references to deleted modules will fail the build — fix those imports (delete the line) before continuing.

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "refactor: delete team pages + sidebar team nav"
```

---

### Task 12: Delete support DTOs + drop `is_personal` from Team model

**Files:**
- Delete: `app/Support/UserTeam.php`
- Delete: `app/Support/TeamPermissions.php`
- Modify: `app/Models/Team.php` (remove `is_personal` from fillable + casts)

- [ ] **Step 1: Confirm no other file references `UserTeam` / `TeamPermissions`**

Use Grep:
```
pattern: App\\Support\\(UserTeam|TeamPermissions)
path: .
```

Expected: no matches (the `HandleInertiaRequests` reference was already removed in Task 4). If anything else references them, remove those references first.

- [ ] **Step 2: Delete the DTOs**

```bash
rm app/Support/UserTeam.php
rm app/Support/TeamPermissions.php
```

If `app/Support/` is now empty: `rmdir app/Support` (check first with `ls`).

- [ ] **Step 3: Strip `is_personal` from `Team` model**

Use Read on `app/Models/Team.php`. Find and remove any of these patterns:
- `'is_personal'` in a `Fillable` attribute or `$fillable` array
- `'is_personal' => 'boolean'` in a `casts()` method or `$casts` property
- Any `isPersonal()` / `personal()` scope or accessor
- Any `Fillable` decorator entries or factory hooks mentioning personal teams

- [ ] **Step 4: Run full suite**

```bash
php artisan test --compact
```

Expected: Auth/Settings/Dashboard pass.

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "refactor: drop UserTeam/TeamPermissions DTOs and is_personal from Team model"
```

---

### Task 13: Migration — drop `current_team_id` from users and `is_personal` from teams

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_drop_team_columns_for_foundation_cleanup.php`
- Modify: `database/factories/UserFactory.php` (remove `current_team_id` reference if any)

- [ ] **Step 1: Create the migration**

```bash
php artisan make:migration drop_team_columns_for_foundation_cleanup --no-interaction
```

- [ ] **Step 2: Implement the migration**

Open the newly created file in `database/migrations/` and replace its contents with:

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
            $table->dropForeign(['current_team_id']);
            $table->dropColumn('current_team_id');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('is_personal');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_team_id')->nullable()->constrained('teams')->nullOnDelete();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('is_personal')->default(false);
        });
    }
};
```

- [ ] **Step 3: Clean `UserFactory`**

Use Read on `database/factories/UserFactory.php`. If it defines `current_team_id` in the `definition()` or any state, remove that line.

- [ ] **Step 4: Run the migration**

```bash
php artisan migrate
```

Expected: migrates cleanly. If the foreign-key name differs from Laravel's convention (`users_current_team_id_foreign`), `dropForeign(['current_team_id'])` form handles the inference — no manual name needed.

- [ ] **Step 5: Verify columns are gone**

Use Boost tool `database-schema` (preferred) or tinker:
```bash
php artisan tinker --execute 'print_r(Schema::getColumnListing("users"));'
php artisan tinker --execute 'print_r(Schema::getColumnListing("teams"));'
```

Expected: no `current_team_id` in users, no `is_personal` in teams.

- [ ] **Step 6: Run full suite**

```bash
php artisan test --compact
```

Expected: tests use `RefreshDatabase` trait so they see the new schema. Auth/Settings/Dashboard pass. Team tests still failing (deleted next).

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "refactor: drop current_team_id and is_personal columns"
```

---

### Task 14: Delete team feature tests

**Files:**
- Delete: `tests/Feature/Teams/TeamTest.php`
- Delete: `tests/Feature/Teams/TeamInvitationTest.php`
- Delete: `tests/Feature/Teams/TeamMemberTest.php`
- Delete: `tests/Feature/Teams/` (dir, once empty)

Deletion is approved as part of the teams rip-out decision. The test subjects (controllers, policies, middleware, routes) no longer exist.

- [ ] **Step 1: Confirm the tests target deleted subjects**

Use Read on each of the three test files. Verify each one covers only team controllers / team routes / team middleware (not cross-cutting concerns used elsewhere).

- [ ] **Step 2: Delete the tests**

```bash
rm -r tests/Feature/Teams
```

- [ ] **Step 3: Run full suite**

```bash
php artisan test --compact
```

Expected: **all tests pass.** This is the first fully-green state since Task 8.

- [ ] **Step 4: Commit**

```bash
git add -A
git commit -m "test: remove team feature tests (scope removed)"
```

---

### Task 15: Final verification + manual smoke test

**Files:** none (verification only)

- [ ] **Step 1: Run full test suite**

```bash
php artisan test --compact
```

Expected: all tests pass.

- [ ] **Step 2: Run Pint across the whole project**

```bash
vendor/bin/pint --format agent
```

Expected: no changes (prior per-task Pint runs covered everything). If anything is formatted now, commit it:
```bash
git add -A
git commit -m "chore: pint formatting"
```

- [ ] **Step 3: Build frontend**

```bash
npm run build
```

Expected: succeeds with no unresolved imports.

- [ ] **Step 4: Manual smoke test — start dev server**

```bash
composer run dev
```

In a browser, verify end-to-end:
- `/` (landing) loads, shows login/register buttons
- `/register` — create a new account. Should redirect to `/dashboard` (no team slug in URL)
- `/dashboard` loads
- `/settings/profile` loads
- `/settings/security` loads
- `/settings/appearance` loads
- Sidebar shows no team switcher / no team nav items
- Log out, log in again — still lands on `/dashboard`

If any page throws (React error, 500), stop and fix. Most likely cause: a React component still references `currentTeam` / `teams` props.

- [ ] **Step 5: Confirm dormant teams infrastructure is intact**

Use `database-schema` Boost tool or:
```bash
php artisan tinker --execute 'print_r(Schema::getColumnListing("teams"));'
php artisan tinker --execute 'print_r(Schema::getColumnListing("team_members"));'
php artisan tinker --execute 'print_r(Schema::getColumnListing("team_invitations"));'
```

Expected: all three tables exist. `teams` has no `is_personal` column. The tables are empty and dormant — ready to rehydrate for a B2B v2.

- [ ] **Step 6: Confirm locale is German**

```bash
php artisan config:show app.locale
```

Expected: `de`.

- [ ] **Step 7: Final commit marker + push branch (do not merge)**

```bash
git commit --allow-empty -m "chore: foundation refactor complete"
git status
```

Do NOT push / merge without user approval. Stop here and hand back.

---

## Self-Review

**Spec coverage check:**
- ✅ Locale → German: Task 2
- ✅ Delete personal-team auto-create: Task 5
- ✅ Delete `current_team_id`: Task 10 (fillable), Task 13 (migration)
- ✅ Delete `is_personal`: Task 12 (model), Task 13 (migration)
- ✅ Delete team-slug routing: Task 6
- ✅ Delete `EnsureTeamMembership` / `SetTeamUrlDefaults`: Task 8
- ✅ Delete `HasTeams` trait / `GeneratesUniqueTeamSlugs`: Task 10
- ✅ Delete team controllers + `TeamPolicy`: Task 9
- ✅ Delete team UI: Task 11
- ✅ Delete `UserTeam` / `TeamPermissions`: Task 12
- ✅ Delete `CreateTeam` action: Task 5
- ✅ Keep `Team` / `Membership` / `TeamInvitation` models + tables + enums dormant: verified in Task 15
- ✅ Keep Team tables migration in history: verified in Task 13 down()

**Placeholder scan:** No "TODO", "TBD", "add validation", etc. Every step has exact code or exact commands.

**Ordering check:** Each task leaves the app in a **code-level** working state. Team feature tests break starting at Task 8 and stay broken through Task 13 — they're deleted in Task 14. No non-team test breaks at any step. Migration (Task 13) runs only after all code references to `current_team_id` / `is_personal` are gone (Tasks 3, 4, 5, 10, 12).

**Type consistency:** `LoginResponse` signature unchanged (Contract). `CreateNewUser::create()` signature unchanged. `User` public API only loses trait methods (`currentTeam`, `teams`, `personalTeam`, `ownedTeams`, `belongsToTeam`, `switchTeam`, `hasTeamPermission`, `teamRole`, `toUserTeam`, `toUserTeams`) — all of which are dropped intentionally. No downstream callers outside the team code we're also deleting.

---

## Execution Handoff

Plan saved to `docs/superpowers/plans/2026-04-14-foundation-refactor.md`. Two execution options:

1. **Subagent-Driven (recommended)** — I dispatch a fresh subagent per task, review between tasks, fast iteration. Best for a refactor this mechanical.
2. **Inline Execution** — Execute tasks in this session using executing-plans, batch execution with checkpoints.

Which approach?

Optional: create a dedicated git worktree first (`git worktree add ../pruefungstrainer-foundation refactor/foundation-cleanup`) so this work is isolated from the main workspace. Recommended for safety, not required.
