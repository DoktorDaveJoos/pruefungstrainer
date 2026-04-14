<laravel-boost-guidelines>
=== .ai/design-system rules ===

# Design System — Prüfungstrainer

An exam-simulator for the official BSI IT-Grundschutz-Praktiker certification. The UI is **extremely clean, near-shadcn-default**: serious, academic, and optimized for long reading sessions. Tokens live in `resources/css/app.css`; conventions live here.

## Tokens (source of truth: `resources/css/app.css`)

Do not override these via `className`. Use the semantic Tailwind utilities that Tailwind v4 generates from them (`bg-primary`, `text-muted-foreground`, `bg-success`, etc.).

| Token                  | Purpose                                                                    |
| ---------------------- | -------------------------------------------------------------------------- |
| `primary` / `secondary`| Neutral-only brand palette. No colored accent.                             |
| `success`              | Pass state (≥60%), correct answers (paid review), confirmation toasts.     |
| `warning`              | Below-threshold score, weak-topic flags, non-error cautions.               |
| `destructive`          | Truly wrong answers, delete actions, error toasts. **Not used for "fail"** — failing an exam is not a system error. |
| `chart-1` … `chart-5`  | Topic breakdown on the results screen. Do not reuse for status.            |

## Typography

- **Font:** Instrument Sans, weights 400 / 500 / 600 / 700.
- **UI body (dashboard, settings, nav):** `text-sm` (14px) — shadcn default.
- **Exam question stems:** `text-lg` (18px) + `leading-relaxed`. Reading 50 German technical stems over 60 minutes needs breathing room.
- **Exam option labels:** `text-base` (16px).
- **Score & major stats (results screen):** `text-4xl` or larger with `font-bold` (weight 700). Only place weight 700 is used.
- **Max content width on exam/result screens:** `max-w-2xl` (≈42rem) centered. Keeps question line length under ~75 characters for readability.

## Numeric display — always use tabular figures

Apply `tabular-nums` to any number that updates in place or aligns in columns. Otherwise digits shift width and the UI jitters — unacceptable in a 60-minute timer.

Required on:

- **Timer** (`HH:MM:SS` countdown)
- **Progress** (`23 / 50`)
- **Score** (`30 / 50`, `60%`)
- **Topic accuracy percentages**

```tsx
<span className="tabular-nums">{formatTime(remaining)}</span>
```

## Status color semantics

| State                         | Component             | Token           |
| ----------------------------- | --------------------- | --------------- |
| Passed exam (≥60%)            | `Alert` / `Badge`     | `success`       |
| Below threshold (<60%)        | `Alert` / `Badge`     | `warning`       |
| Actual error, destructive op  | `Alert` / `Button`    | `destructive`   |
| Locked / paid-only feature    | `Badge variant="secondary"` + `Lock` icon | — |
| Correct answer (paid review)  | `bg-success/10 text-success` | `success` |
| Incorrect answer (paid review)| `bg-destructive/10 text-destructive` | `destructive` |

Never invent your own pass/fail colors. Never use raw Tailwind palette colors (`bg-green-500`, `text-red-600`).

## Focus rings — high contrast, always visible

`--ring` is bound to `--primary`, so focus rings are near-black in light mode and near-white in dark mode. This is deliberate: exam-takers navigate between options with Tab / Space / arrow keys, and accessibility matters more than aesthetic subtlety. Do not tone down focus rings per-component.

## Motion — restrained

- Transitions cap at **150ms** (`transition-colors`, `transition-opacity`). No longer.
- No transforms on hover. No scale/rotate animations.
- **One allowed "moment":** the score reveal on the results screen may use a single fade-up (≤300ms). Everything else is instant or near-instant.
- Skeletons and spinners follow shadcn defaults — do not customize their timing.

## Layout conventions

- **Exam screens:** centered column, `max-w-2xl`, question `Card` with generous `p-8` or `p-10`. Timer is fixed top-center or top-right, always visible.
- **Results screen:** `max-w-3xl`. Score hero at top, topic breakdown below (list of Progress bars or Chart), locked-preview Card below that, upgrade CTA last.
- **Dashboard / list screens:** follow shadcn defaults — standard `p-4` / `gap-4`, no custom width clamps.

## What we will *not* do

- No brand accent color. The palette stays neutral + semantic status.
- No radius changes. `--radius: 0.625rem` is the shadcn default and fits.
- No font-family swap. Instrument Sans is the chosen face.
- No emoji, no decorative icons beyond Lucide's `Clock`, `Flag`, `Check`, `X`, `Lock`, `ChevronRight`, etc.
- No custom `animate-*` for loading — use `Skeleton`.
- No `z-index` overrides on overlay components (Dialog, Sheet, Popover manage their own).

## Related files

- `resources/css/app.css` — token definitions
- `components.json` — shadcn config (style, base, icon library)
- `.ai/guidelines/shadcn-ui.md` — rule that all UI is shadcn components

=== .ai/shadcn-ui rules ===

# UI Components — Always shadcn/ui

All UI in this project is built from shadcn/ui components. Do not hand-roll equivalents.

## Rules

- **Every UI element must be a shadcn component.** Before writing any markup, check if a shadcn component fits: `Button`, `Card`, `Dialog`, `Sheet`, `Alert`, `Empty`, `Badge`, `Skeleton`, `Separator`, `Tabs`, `Tooltip`, `Sidebar`, etc.
- **No custom-styled divs as UI primitives.** No styled `<span>` for badges, no `<hr>` or bordered divs for separators, no `animate-pulse` divs for loading — use `Badge`, `Separator`, `Skeleton`.
- **No raw HTML form controls.** Use `Input`, `Select`, `Checkbox`, `RadioGroup`, `Switch`, `Textarea`, `Label`, wrapped in `FieldGroup` + `Field`.
- **Callouts use `Alert`.** Empty states use `Empty`. Toasts use `sonner`'s `toast()`.
- **Compose, don't reinvent.** Settings page = `Tabs` + `Card` + form controls. Dashboard = `Sidebar` + `Card` + `Chart` + `Table`.

## Styling

- Use built-in variants (`variant="outline"`, `size="sm"`) before custom classes.
- Use semantic tokens: `bg-primary`, `text-muted-foreground`, `bg-background`. Never raw colors like `bg-blue-500`.
- `className` is for layout only — never override component colors or typography.
- `flex` + `gap-*` instead of `space-x-*` / `space-y-*`. `size-*` when width equals height.

## Workflow

1. Check the installed components list (see `components.json`) before importing.
2. If a needed component is not installed, add it: `npx shadcn@latest add <component>`.
3. Use `npx shadcn@latest docs <component>` to fetch current docs before using an unfamiliar component.
4. Never fetch shadcn source from GitHub manually — always use the CLI.

When in doubt, invoke the `shadcn` skill.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- \@inertiajs/react (INERTIA_REACT) - v3
- react (REACT) - v19
- tailwindcss (TAILWINDCSS) - v4
- \@laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `fortify-development` — ACTIVATE when the user works on authentication in Laravel. This includes login, registration, password reset, email verification, two-factor authentication (2FA/TOTP/QR codes/recovery codes), profile updates, password confirmation, or any auth-related routes and controllers. Activate when the user mentions Fortify, auth, authentication, login, register, signup, forgot password, verify email, 2FA, or references app/Actions/Fortify/, CreateNewUser, UpdateUserProfileInformation, FortifyServiceProvider, config/fortify.php, or auth guards. Fortify is the frontend-agnostic authentication backend for Laravel that registers all auth routes and controllers. Also activate when building SPA or headless authentication, customizing login redirects, overriding response contracts like LoginResponse, or configuring login throttling. Do NOT activate for Laravel Passport (OAuth2 API tokens), Socialite (OAuth social login), or non-auth Laravel features.
- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `wayfinder-development` — Use this skill for Laravel Wayfinder which auto-generates typed functions for Laravel controllers and routes. ALWAYS use this skill when frontend code needs to call backend routes or controller actions. Trigger when: connecting any React/Vue/Svelte/Inertia frontend to Laravel controllers, routes, building end-to-end features with both frontend and backend, wiring up forms or links to backend endpoints, fixing route-related TypeScript errors, importing from @/actions or @/routes, or running wayfinder:generate. Use Wayfinder route functions instead of hardcoded URLs. Covers: wayfinder() vite plugin, .url()/.get()/.post()/.form(), query params, route model binding, tree-shaking. Do not use for backend-only task
- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: test()/it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `inertia-react-development` — Develops Inertia.js v3 React client-side applications. Activates when creating React pages, forms, or navigation; using <Link>, <Form>, useForm, useHttp, setLayoutProps, or router; working with deferred props, prefetching, optimistic updates, instant visits, or polling; or when user mentions React with Inertia, React pages, React forms, or React navigation.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

</laravel-boost-guidelines>
