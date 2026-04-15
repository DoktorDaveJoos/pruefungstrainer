# Landing Page + Legal Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Polish the landing page into a sales-ready page (hero + pricing card with Founder's Price counter + FAQ) and add the three German legal pages (AGB, Datenschutz, Impressum) required to operate as a B2C SaaS in Germany. After this lands, the v1 product is launchable.

**Architecture:** One small `Pricing` service that determines current price (€29 Founder's Price for first 100 paid users, €49 standard after) plus the count of remaining founder spots — exposed to the welcome page via Inertia props. Three new Inertia pages (`legal/agb`, `legal/datenschutz`, `legal/impressum`) with skeleton structure + clearly-marked placeholder text the operator must fill in (or replace with output from a German legal-text generator like e-recht24.de). One footer component shared across landing + legal pages with the three required links.

**Tech Stack:** Laravel 13, Inertia 3, React 19, Pest 4, Tailwind v4, shadcn (new-york style, radix base).

## What you (the human) must do AFTER Task 8

The legal pages ship with **structural skeletons containing placeholder text marked `[TODO: ...]`**. Before going live, you MUST replace those placeholders with real legal text — either:

1. **Use a German legal-text generator** like https://www.e-recht24.de/ or https://www.it-recht-kanzlei.de/ (cheap subscription, generates lawyer-checked text customized to your business)
2. **Hire a lawyer** to write/review for ~€200–500
3. **Adapt from a similar product's terms** (last resort — risk of outdated/inappropriate clauses)

The placeholders include:
- Your business name, address, contact email, phone
- Tax ID (USt-IdNr. or "Kleinunternehmer §19 UStG" if applicable)
- Liability insurance details (if you have any)
- Payment processor details (we use Polar.sh — they're MoR so they handle some compliance, but you still need to disclose them as a recipient)
- Cookie inventory (we use one: `pt_exam_session` for anonymous exam tracking)
- Right of withdrawal handling (waived per German digital-services rules)

Without real legal text, **do not accept payments or run ads**. The German Abmahnung industry will find you.

## Subsequent plans (post-MVP, not in scope)

1. **Magic-link login** for new users created via Polar webhook (currently they use forgot-password)
2. **Email verification flow** for unpaid users
3. **Question quality + Plan 8** — generate more notfall + siem questions
4. **Analytics** — track funnel from landing → exam → conversion

---

## File Structure

### Services to CREATE

```
app/Services/Pricing.php                # currentPrice() + foundersSpotsRemaining()
```

### Inertia pages to CREATE

```
resources/js/pages/legal/agb.tsx              # Allgemeine Geschäftsbedingungen
resources/js/pages/legal/datenschutz.tsx      # Datenschutzerklärung
resources/js/pages/legal/impressum.tsx        # Impressum (TMG §5)
```

### Components to CREATE

```
resources/js/components/site-footer.tsx       # links to AGB / Datenschutz / Impressum + branding
```

### Inertia pages to MODIFY

```
resources/js/pages/welcome.tsx                # add pricing card, FAQ, footer
```

### Routes to MODIFY

```
routes/web.php                                # add 3 GET routes for legal pages
```

### Tests to CREATE

```
tests/Feature/Services/PricingTest.php
tests/Feature/Legal/LegalPagesTest.php
tests/Feature/WelcomePricingTest.php
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

Expected: clean tree on `main`, 191 tests passing.

- [ ] **Step 2: Create feature branch**

```bash
git checkout -b feat/landing-and-legal
git commit --allow-empty -m "chore: baseline before landing + legal"
```

---

### Task 2: `Pricing` service — TDD

**Files:**
- Create: `app/Services/Pricing.php`
- Create: `tests/Feature/Services/PricingTest.php`

`Pricing::currentPrice()` returns `['amount' => int (cents), 'isFounderPrice' => bool, 'spotsRemaining' => int]`. Founder phase: first 100 paid users pay €29 (2900 cents). After: €49 (4900 cents).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Services/PricingTest.php`:

```php
<?php

use App\Models\User;
use App\Services\Pricing;

it('returns Founders Price (29 EUR) when fewer than 100 users have paid', function () {
    User::factory()->paid()->count(50)->create();

    $price = (new Pricing)->currentPrice();

    expect($price)->toBe([
        'amount_eur' => 29,
        'is_founder_price' => true,
        'spots_remaining' => 50,
    ]);
});

it('returns standard price (49 EUR) when 100 paid users reached', function () {
    User::factory()->paid()->count(100)->create();

    $price = (new Pricing)->currentPrice();

    expect($price)->toBe([
        'amount_eur' => 49,
        'is_founder_price' => false,
        'spots_remaining' => 0,
    ]);
});

it('returns Founders Price for empty user pool', function () {
    $price = (new Pricing)->currentPrice();

    expect($price)->toBe([
        'amount_eur' => 29,
        'is_founder_price' => true,
        'spots_remaining' => 100,
    ]);
});

it('counts only paid users (ignores unpaid registrations)', function () {
    User::factory()->paid()->count(30)->create();
    User::factory()->count(50)->create(); // unpaid — should not count

    $price = (new Pricing)->currentPrice();

    expect($price['spots_remaining'])->toBe(70);
    expect($price['is_founder_price'])->toBeTrue();
});

it('returns standard price even when 101+ users have paid (over the cap)', function () {
    User::factory()->paid()->count(101)->create();

    $price = (new Pricing)->currentPrice();

    expect($price['amount_eur'])->toBe(49);
    expect($price['spots_remaining'])->toBe(0);
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=PricingTest
```

Expected: FAIL with "Class App\Services\Pricing not found".

- [ ] **Step 3: Implement `Pricing`**

Create `app/Services/Pricing.php`:

```php
<?php

namespace App\Services;

use App\Models\User;

class Pricing
{
    private const FOUNDER_PRICE_EUR = 29;

    private const STANDARD_PRICE_EUR = 49;

    private const FOUNDER_CAP = 100;

    /**
     * @return array{amount_eur: int, is_founder_price: bool, spots_remaining: int}
     */
    public function currentPrice(): array
    {
        $paidCount = User::whereNotNull('paid_at')->count();

        $isFounder = $paidCount < self::FOUNDER_CAP;
        $spotsRemaining = max(0, self::FOUNDER_CAP - $paidCount);

        return [
            'amount_eur' => $isFounder ? self::FOUNDER_PRICE_EUR : self::STANDARD_PRICE_EUR,
            'is_founder_price' => $isFounder,
            'spots_remaining' => $spotsRemaining,
        ];
    }
}
```

- [ ] **Step 4: Run tests to verify pass**

```bash
php artisan test --compact --filter=PricingTest
```

Expected: 5 passing.

- [ ] **Step 5: Full suite**

```bash
php artisan test --compact
```

Expected: 196 passing (191 + 5).

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add Pricing service (founder price for first 100 paid users)"
```

---

### Task 3: Site footer component

**Files:**
- Create: `resources/js/components/site-footer.tsx`

Shared footer with the three legal links. Used on landing + legal pages (NOT inside `AppLayout` which has its own chrome).

- [ ] **Step 1: Create the component**

```tsx
import { Link } from '@inertiajs/react';

export function SiteFooter() {
    return (
        <footer className="border-t border-border bg-background">
            <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 py-8 text-sm text-muted-foreground sm:flex-row">
                <div>© {new Date().getFullYear()} Prüfungstrainer · BSI IT-Grundschutz-Praktiker</div>
                <nav className="flex gap-6">
                    <Link href="/agb" className="hover:text-foreground">
                        AGB
                    </Link>
                    <Link href="/datenschutz" className="hover:text-foreground">
                        Datenschutz
                    </Link>
                    <Link href="/impressum" className="hover:text-foreground">
                        Impressum
                    </Link>
                </nav>
            </div>
        </footer>
    );
}
```

- [ ] **Step 2: Build to verify**

```bash
npm run build
```

Expected: succeeds.

- [ ] **Step 3: Commit**

```bash
git add resources/js/components/site-footer.tsx
git commit -m "feat: add site footer with legal links"
```

---

### Task 4: Welcome page — pricing card + FAQ + footer + Inertia pricing prop

**Files:**
- Modify: `resources/js/pages/welcome.tsx`
- Modify: `routes/web.php` (pass pricing prop to welcome inertia view)
- Create: `tests/Feature/WelcomePricingTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/WelcomePricingTest.php`:

```php
<?php

use App\Models\User;

it('passes current pricing to the welcome Inertia view', function () {
    User::factory()->paid()->count(20)->create();

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('welcome')
        ->where('pricing.amount_eur', 29)
        ->where('pricing.is_founder_price', true)
        ->where('pricing.spots_remaining', 80)
    );
});

it('shows standard price when founder cap reached', function () {
    User::factory()->paid()->count(100)->create();

    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page
        ->where('pricing.amount_eur', 49)
        ->where('pricing.is_founder_price', false)
    );
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=WelcomePricingTest
```

Expected: FAIL — `pricing` prop not defined yet.

- [ ] **Step 3: Update `routes/web.php` to pass pricing to welcome**

Read `routes/web.php`. The current home route uses `Route::inertia(...)`. Replace with a closure-based route that injects the pricing prop:

Find:
```php
Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');
```

Replace with:
```php
Route::get('/', function () {
    return inertia('welcome', [
        'canRegister' => \Laravel\Fortify\Features::enabled(\Laravel\Fortify\Features::registration()),
        'pricing' => app(\App\Services\Pricing::class)->currentPrice(),
    ]);
})->name('home');
```

If `use Laravel\Fortify\Features;` is already imported at the top of routes/web.php, you can skip the FQCN inside the closure and just write `Features::enabled(Features::registration())` and `Pricing::class`. Pick whichever is cleaner given the file's existing imports.

- [ ] **Step 4: Update welcome.tsx**

Read `resources/js/pages/welcome.tsx`. Replace the entire `<main>` body with the version below (preserve the `<header>` exactly as-is, add the pricing section, FAQ, and footer):

```tsx
import { SiteFooter } from '@/components/site-footer';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';
import { Check, Clock, FileCheck, Lock } from 'lucide-react';

type Pricing = {
    amount_eur: number;
    is_founder_price: boolean;
    spots_remaining: number;
};

export default function Welcome() {
    const { auth, pricing } = usePage().props as { auth?: { user?: unknown }; pricing: Pricing };

    return (
        <>
            <Head title="BSI IT-Grundschutz-Praktiker Prüfungstrainer" />

            <div className="min-h-screen bg-background">
                <header className="border-b border-border">
                    <div className="mx-auto flex max-w-6xl items-center justify-between p-6">
                        <div className="text-lg font-semibold tracking-tight">Prüfungstrainer</div>
                        <nav className="flex items-center gap-4 text-sm">
                            {auth?.user ? (
                                <Link href="/dashboard" className="text-foreground hover:underline">
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link href="/login" className="text-muted-foreground hover:text-foreground">
                                        Anmelden
                                    </Link>
                                    <Link href="/register" className="text-muted-foreground hover:text-foreground">
                                        Registrieren
                                    </Link>
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                <main>
                    <section className="mx-auto max-w-3xl px-6 py-24">
                        <h1 className="text-5xl font-bold tracking-tight">BSI IT-Grundschutz-Praktiker</h1>
                        <p className="mt-4 text-xl text-muted-foreground leading-relaxed">
                            Realistische Prüfungssimulation. 50 Fragen, 60 Minuten, 60 % Bestehensgrenze — genau wie die echte BSI-Prüfung.
                        </p>

                        <div className="mt-10 flex items-center gap-4">
                            <form method="POST" action="/pruefungssimulation/start">
                                <input
                                    type="hidden"
                                    name="_token"
                                    value={(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? ''}
                                />
                                <Button size="lg" type="submit">
                                    Prüfungssimulation starten
                                </Button>
                            </form>
                            <span className="text-sm text-muted-foreground">kostenlos · kein Login nötig</span>
                        </div>

                        <div className="mt-16 grid gap-6 md:grid-cols-3">
                            <Card>
                                <CardHeader>
                                    <FileCheck className="size-6 text-muted-foreground" />
                                    <CardTitle className="mt-2 text-base">50 Fragen</CardTitle>
                                    <CardDescription>Zufall aus über 160 BSI-Prüfungsfragen. 75 % Basis, 25 % Experte.</CardDescription>
                                </CardHeader>
                            </Card>
                            <Card>
                                <CardHeader>
                                    <Clock className="size-6 text-muted-foreground" />
                                    <CardTitle className="mt-2 text-base">60 Minuten</CardTitle>
                                    <CardDescription>Server-authoritativer Timer. Bei Ablauf wird automatisch abgegeben.</CardDescription>
                                </CardHeader>
                            </Card>
                            <Card>
                                <CardHeader>
                                    <Lock className="size-6 text-muted-foreground" />
                                    <CardTitle className="mt-2 text-base">Review freischalten</CardTitle>
                                    <CardDescription>Nach der Simulation: detaillierte Antwort-Erklärungen mit BSI-Quellen ab {pricing.amount_eur} € einmalig.</CardDescription>
                                </CardHeader>
                            </Card>
                        </div>
                    </section>

                    <section className="border-t border-border bg-muted/30 py-20">
                        <div className="mx-auto max-w-3xl px-6">
                            <h2 className="text-center text-3xl font-bold tracking-tight">Lifetime-Zugang</h2>
                            <p className="mt-2 text-center text-muted-foreground">
                                Einmal zahlen. Kein Abo. Für immer üben.
                            </p>

                            <Card className="mt-10 mx-auto max-w-md border-border">
                                <CardHeader className="items-center text-center">
                                    {pricing.is_founder_price && (
                                        <div className="text-xs font-medium uppercase tracking-wider text-warning">
                                            Founder's Price · {pricing.spots_remaining} von 100 Plätzen frei
                                        </div>
                                    )}
                                    <div className="mt-2 text-5xl font-bold tabular-nums">{pricing.amount_eur} €</div>
                                    {pricing.is_founder_price && (
                                        <div className="text-sm text-muted-foreground line-through tabular-nums">49 €</div>
                                    )}
                                    <CardTitle className="mt-4 text-base font-medium">Lifetime-Zugang</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <ul className="space-y-2 text-sm">
                                        <li className="flex items-start gap-2">
                                            <Check className="mt-0.5 size-4 shrink-0 text-success" />
                                            <span>Unbegrenzte 50-Fragen-Prüfungssimulationen</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <Check className="mt-0.5 size-4 shrink-0 text-success" />
                                            <span>Vollständige Antwort-Erklärungen mit BSI-Originalquellen</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <Check className="mt-0.5 size-4 shrink-0 text-success" />
                                            <span>Topic-Analyse: wo bist du schwach</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <Check className="mt-0.5 size-4 shrink-0 text-success" />
                                            <span>Freies Lernen — nur falsche Fragen wiederholen</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <Check className="mt-0.5 size-4 shrink-0 text-success" />
                                            <span>Lebenslanger Zugang — keine Abos</span>
                                        </li>
                                    </ul>
                                    <form method="POST" action="/checkout/start">
                                        <input
                                            type="hidden"
                                            name="_token"
                                            value={(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? ''}
                                        />
                                        <Button type="submit" size="lg" className="w-full">
                                            Lifetime-Zugang freischalten
                                        </Button>
                                    </form>
                                    <p className="text-center text-xs text-muted-foreground">
                                        Sichere Zahlung über Polar.sh · 14 Tage Widerrufsrecht (siehe AGB)
                                    </p>
                                </CardContent>
                            </Card>

                            <p className="mt-8 text-center text-xs text-muted-foreground">
                                BSI-Prüfung kostet {' '}
                                <a href="https://www.bsi.bund.de/" className="underline" target="_blank" rel="noopener noreferrer">
                                    €245–450 pro Versuch
                                </a>{' '}
                                — der Lifetime-Zugang ist eine günstige Versicherung.
                            </p>
                        </div>
                    </section>

                    <section className="mx-auto max-w-3xl px-6 py-20">
                        <h2 className="text-3xl font-bold tracking-tight">Häufige Fragen</h2>

                        <div className="mt-8 space-y-6">
                            <FaqItem
                                q="Stimmt die Prüfungssimulation exakt mit dem BSI-Original überein?"
                                a="Format und Bewertung mirroren das BSI-Original (50 Fragen, 60 Minuten, 60 % Bestehensgrenze, Mehrfachauswahl mit Alles-oder-Nichts-Bewertung). Die Basis/Experte-Einteilung der Fragen basiert auf eigener Klassifikation, da BSI die offizielle Verteilung nicht veröffentlicht."
                            />
                            <FaqItem
                                q="Kann ich die Simulation vor dem Kauf testen?"
                                a="Ja. Die Prüfungssimulation ist kostenlos und ohne Login zugänglich. Nach Abschluss siehst du dein Ergebnis. Erst die Antwort-Erklärungen, das Themen-Feedback und das Freie Lernen sind im Lifetime-Zugang enthalten."
                            />
                            <FaqItem
                                q="Was passiert mit meinem Probelauf, wenn ich später kaufe?"
                                a="Sobald du den Lifetime-Zugang freischaltest, wird dein anonymer Probelauf automatisch deinem Konto zugeordnet. Du siehst sofort die Erklärungen zu allen Fragen aus genau diesem Lauf."
                            />
                            <FaqItem
                                q="Wie viele Fragen sind im Pool?"
                                a="Derzeit über 160 echte BSI-Prüfungsfragen mit Erklärungen und Quellenverweisen aus den BSI-Standards 200-1, 200-2, 200-3 und dem IT-Grundschutz-Kompendium. Der Pool wächst regelmäßig."
                            />
                            <FaqItem
                                q="Kann ich mein Geld zurückbekommen?"
                                a="14 Tage Widerrufsrecht nach EU-Verbraucherrecht. Da der Zugang sofort freigeschaltet wird, bestätigst du beim Kauf den Beginn der Dienstleistung — das Widerrufsrecht erlischt damit (siehe AGB)."
                            />
                            <FaqItem
                                q="Muss ich ein Abo abschließen?"
                                a="Nein. Es ist ein einmaliger Kauf — kein Abo, keine wiederkehrende Gebühr."
                            />
                        </div>
                    </section>
                </main>

                <SiteFooter />
            </div>
        </>
    );
}

function FaqItem({ q, a }: { q: string; a: string }) {
    return (
        <details className="group rounded-md border border-border p-4">
            <summary className="cursor-pointer list-none font-medium [&::-webkit-details-marker]:hidden">
                <span className="inline-flex items-center gap-2">
                    <span className="text-muted-foreground transition-transform group-open:rotate-90">›</span>
                    {q}
                </span>
            </summary>
            <p className="mt-3 pl-5 text-sm leading-relaxed text-muted-foreground">{a}</p>
        </details>
    );
}
```

- [ ] **Step 5: Run tests + build**

```bash
php artisan test --compact --filter=WelcomePricingTest
npm run build
```

Expected: 2 tests passing, build succeeds.

- [ ] **Step 6: Full suite**

```bash
php artisan test --compact
```

Expected: 198 passing (196 + 2).

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: welcome page with pricing card + FAQ + footer"
```

---

### Task 5: AGB page (skeleton)

**Files:**
- Create: `resources/js/pages/legal/agb.tsx`
- Modify: `routes/web.php`
- Modify: `resources/js/app.tsx` (bypass layout for `legal/*`)

The actual legal content is placeholder. **The operator must replace `[TODO: ...]` markers with real legal text** before going live (see top-of-plan note).

- [ ] **Step 1: Add `/agb` route**

Modify `routes/web.php`. Add (alongside other public routes):

```php
Route::inertia('/agb', 'legal/agb')->name('legal.agb');
```

- [ ] **Step 2: Bypass layout for `legal/*`**

Read `resources/js/app.tsx`. Find the layout switch:

```tsx
case name.startsWith('exam/'):
case name.startsWith('checkout/'):
    return null;
```

Add `legal/` to the same null-layout group:

```tsx
case name.startsWith('exam/'):
case name.startsWith('checkout/'):
case name.startsWith('legal/'):
    return null;
```

- [ ] **Step 3: Create the AGB page**

Create `resources/js/pages/legal/agb.tsx`:

```tsx
import { SiteFooter } from '@/components/site-footer';
import { Head, Link } from '@inertiajs/react';

export default function AGB() {
    return (
        <>
            <Head title="AGB · Prüfungstrainer" />

            <div className="min-h-screen bg-background">
                <header className="border-b border-border">
                    <div className="mx-auto flex max-w-3xl items-center justify-between p-6">
                        <Link href="/" className="text-lg font-semibold tracking-tight">
                            Prüfungstrainer
                        </Link>
                        <Link href="/" className="text-sm text-muted-foreground hover:text-foreground">
                            Zurück zur Startseite
                        </Link>
                    </div>
                </header>

                <main className="mx-auto max-w-3xl px-6 py-16">
                    <h1 className="text-3xl font-bold tracking-tight">Allgemeine Geschäftsbedingungen (AGB)</h1>

                    <div className="mt-8 space-y-8 text-sm leading-relaxed">
                        <section>
                            <h2 className="text-lg font-semibold">§ 1 Geltungsbereich</h2>
                            <p className="mt-2 text-muted-foreground">
                                Diese Allgemeinen Geschäftsbedingungen gelten für alle Verträge zwischen [TODO:
                                Anbietername laut Impressum] (nachfolgend „Anbieter") und Verbrauchern (§ 13 BGB)
                                über die Nutzung des Online-Dienstes Prüfungstrainer (erreichbar unter [TODO: Domain]).
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 2 Vertragsgegenstand</h2>
                            <p className="mt-2 text-muted-foreground">
                                Der Anbieter stellt eine webbasierte Prüfungs- und Lernplattform für die Vorbereitung
                                auf die BSI-Prüfung „IT-Grundschutz-Praktiker" zur Verfügung. Der kostenpflichtige
                                Lifetime-Zugang umfasst:
                            </p>
                            <ul className="mt-2 list-disc pl-6 text-muted-foreground">
                                <li>Unbegrenzte Prüfungssimulationen mit detaillierter Antwort-Erklärung</li>
                                <li>Themenbasierte Auswertung</li>
                                <li>Modus „Freies Lernen" mit Wiederholung falsch beantworteter Fragen</li>
                                <li>Lebenslanger Zugang ohne wiederkehrende Gebühren</li>
                            </ul>
                            <p className="mt-2 text-muted-foreground">
                                Die kostenlose Prüfungssimulation steht ohne Vertragsschluss zur Verfügung.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 3 Vertragsschluss</h2>
                            <p className="mt-2 text-muted-foreground">
                                Der Vertrag kommt durch erfolgreichen Abschluss des Bezahlvorgangs über unseren
                                Zahlungsdienstleister Polar Software, Inc. („Polar.sh") zustande. Der Anbieter
                                bestätigt den Vertragsschluss per E-Mail.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 4 Preise und Zahlung</h2>
                            <p className="mt-2 text-muted-foreground">
                                Der Lifetime-Zugang kostet einmalig den auf der Bestellseite angezeigten Preis
                                (derzeit 29 € im Founder's Price oder 49 € regulär; alle Preise inklusive der jeweils
                                gesetzlichen Umsatzsteuer). Die Zahlung erfolgt über Polar Software, Inc., welche
                                als Merchant of Record auftritt und Rechnungen erstellt.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 5 Widerrufsrecht und Verzicht</h2>
                            <p className="mt-2 text-muted-foreground">
                                Verbraucher haben grundsätzlich ein 14-tägiges Widerrufsrecht. Der Anbieter erbringt
                                die Dienstleistung jedoch unmittelbar nach Vertragsschluss (sofortige Freischaltung
                                des Lifetime-Zugangs). Mit Abschluss des Bezahlvorgangs erklärt der Verbraucher
                                ausdrücklich, dass die Ausführung der Dienstleistung vor Ablauf der Widerrufsfrist
                                beginnt, und bestätigt seine Kenntnis darüber, dass er sein Widerrufsrecht durch
                                vollständige Vertragserfüllung verliert (§ 356 Abs. 5 BGB).
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 6 Verfügbarkeit und Aktualität der Inhalte</h2>
                            <p className="mt-2 text-muted-foreground">
                                Der Anbieter bemüht sich um eine möglichst hohe Verfügbarkeit der Plattform.
                                Eine ununterbrochene Erreichbarkeit wird nicht zugesichert. Die Fragen orientieren
                                sich an der jeweils aktuellen BSI-Prüfungsstruktur; der Anbieter übernimmt jedoch
                                keine Gewähr, dass alle Inhalte stets dem aktuellen Stand entsprechen.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 7 Haftung</h2>
                            <p className="mt-2 text-muted-foreground">
                                Der Anbieter haftet uneingeschränkt für Vorsatz und grobe Fahrlässigkeit. Für
                                leichte Fahrlässigkeit haftet der Anbieter nur bei Verletzung wesentlicher
                                Vertragspflichten (Kardinalpflichten) und der Höhe nach begrenzt auf den
                                vertragstypischen, vorhersehbaren Schaden.
                            </p>
                            <p className="mt-2 text-muted-foreground">
                                Die Plattform dient ausschließlich der Prüfungsvorbereitung. Eine Garantie für das
                                Bestehen der BSI-Prüfung wird nicht übernommen.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 8 Streitbeilegung</h2>
                            <p className="mt-2 text-muted-foreground">
                                Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS)
                                bereit:{' '}
                                <a href="https://ec.europa.eu/consumers/odr" className="underline" target="_blank" rel="noopener noreferrer">
                                    https://ec.europa.eu/consumers/odr
                                </a>
                                . Der Anbieter ist nicht verpflichtet, an Streitbeilegungsverfahren vor einer
                                Verbraucherschlichtungsstelle teilzunehmen, ist hierzu aber grundsätzlich bereit.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">§ 9 Schlussbestimmungen</h2>
                            <p className="mt-2 text-muted-foreground">
                                Es gilt das Recht der Bundesrepublik Deutschland unter Ausschluss des UN-Kaufrechts.
                                Sollten einzelne Bestimmungen unwirksam sein, bleibt die Wirksamkeit der übrigen
                                Bestimmungen unberührt.
                            </p>
                        </section>

                        <p className="text-xs text-muted-foreground">
                            Stand: [TODO: Datum der letzten Aktualisierung einsetzen]
                        </p>
                    </div>
                </main>

                <SiteFooter />
            </div>
        </>
    );
}
```

The text above is a structural starting point — the operator MUST review with a German lawyer or use a generator like e-recht24.de before going live.

- [ ] **Step 4: Build to verify**

```bash
npm run build
```

Expected: succeeds.

- [ ] **Step 5: Commit**

```bash
git add resources/js/pages/legal/agb.tsx routes/web.php resources/js/app.tsx
git commit -m "feat: add /agb page (skeleton — operator must populate)"
```

---

### Task 6: Datenschutz page (skeleton)

**Files:**
- Create: `resources/js/pages/legal/datenschutz.tsx`
- Modify: `routes/web.php`

- [ ] **Step 1: Add `/datenschutz` route**

Modify `routes/web.php`:

```php
Route::inertia('/datenschutz', 'legal/datenschutz')->name('legal.datenschutz');
```

- [ ] **Step 2: Create the page**

Create `resources/js/pages/legal/datenschutz.tsx`:

```tsx
import { SiteFooter } from '@/components/site-footer';
import { Head, Link } from '@inertiajs/react';

export default function Datenschutz() {
    return (
        <>
            <Head title="Datenschutz · Prüfungstrainer" />

            <div className="min-h-screen bg-background">
                <header className="border-b border-border">
                    <div className="mx-auto flex max-w-3xl items-center justify-between p-6">
                        <Link href="/" className="text-lg font-semibold tracking-tight">
                            Prüfungstrainer
                        </Link>
                        <Link href="/" className="text-sm text-muted-foreground hover:text-foreground">
                            Zurück zur Startseite
                        </Link>
                    </div>
                </header>

                <main className="mx-auto max-w-3xl px-6 py-16">
                    <h1 className="text-3xl font-bold tracking-tight">Datenschutzerklärung</h1>

                    <div className="mt-8 space-y-8 text-sm leading-relaxed">
                        <section>
                            <h2 className="text-lg font-semibold">1. Verantwortlicher</h2>
                            <p className="mt-2 text-muted-foreground">
                                Verantwortlicher im Sinne der DSGVO ist [TODO: Name + vollständige Anschrift,
                                identisch zum Impressum]. Kontakt: [TODO: E-Mail-Adresse für Datenschutzanfragen].
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">2. Welche Daten werden verarbeitet</h2>
                            <p className="mt-2 text-muted-foreground">
                                <strong>Anonyme Nutzung:</strong> Bei der kostenlosen Prüfungssimulation wird ein
                                technisch notwendiges Cookie (<code>pt_exam_session</code>) gesetzt, das einer
                                anonymen Sitzung den jeweiligen Prüfungsversuch zuordnet. Der Cookie enthält
                                ausschließlich eine zufällige UUID und ist 24 Stunden gültig. Es werden keine
                                personenbezogenen Daten erfasst.
                            </p>
                            <p className="mt-2 text-muted-foreground">
                                <strong>Bei kostenpflichtigem Erwerb:</strong> Im Rahmen des Bezahlvorgangs
                                übermittelt unser Zahlungsdienstleister Polar Software, Inc. (siehe Punkt 4) den
                                Namen und die E-Mail-Adresse des Kunden an uns. Wir legen damit ein Nutzerkonto an
                                und verknüpfen es mit dem ggf. zuvor erfassten anonymen Prüfungsversuch.
                            </p>
                            <p className="mt-2 text-muted-foreground">
                                <strong>Bei Nutzung als angemeldeter Kunde:</strong> Zur Erbringung der
                                Lerndienstleistung speichern wir pro Frage Antwort, Antwortzeitpunkt und
                                Bewertung (richtig/falsch). Diese Daten dienen ausschließlich der Bereitstellung
                                der Funktionen „Themen-Auswertung" und „Freies Lernen".
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">3. Rechtsgrundlagen</h2>
                            <ul className="mt-2 list-disc pl-6 text-muted-foreground">
                                <li>
                                    Anonyme Nutzung: Art. 6 Abs. 1 lit. f DSGVO (berechtigtes Interesse — technisch
                                    notwendige Sitzungsverwaltung).
                                </li>
                                <li>
                                    Vertragsabwicklung: Art. 6 Abs. 1 lit. b DSGVO (Erfüllung des Vertrages über den
                                    Lifetime-Zugang).
                                </li>
                                <li>
                                    Steuer- und handelsrechtliche Aufbewahrung: Art. 6 Abs. 1 lit. c DSGVO
                                    i.V.m. § 257 HGB / § 147 AO.
                                </li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">4. Empfänger / Auftragsverarbeiter</h2>
                            <ul className="mt-2 list-disc pl-6 text-muted-foreground">
                                <li>
                                    <strong>Polar Software, Inc.</strong> — Zahlungsabwicklung als
                                    Merchant of Record. Erhält Rechnungs- und Zahlungsdaten direkt vom Kunden.
                                    Datenschutzerklärung:{' '}
                                    <a href="https://polar.sh/legal/privacy" className="underline" target="_blank" rel="noopener noreferrer">
                                        polar.sh/legal/privacy
                                    </a>
                                </li>
                                <li>
                                    <strong>[TODO: Hosting-Anbieter, z. B. Hetzner / Forge / DigitalOcean]</strong>{' '}
                                    — Server-Hosting in der EU. Auftragsverarbeitungsvertrag (AVV) liegt vor.
                                </li>
                                <li>
                                    <strong>[TODO: E-Mail-Versand-Dienstleister, z. B. Mailgun / Postmark]</strong>{' '}
                                    — Versand transaktionaler E-Mails (Passwort-Reset, Kaufbestätigung).
                                </li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">5. Speicherdauer</h2>
                            <p className="mt-2 text-muted-foreground">
                                Anonyme Sitzungsdaten: 24 Stunden. Nutzerkonten und Lernfortschritt: bis zur Löschung
                                des Nutzerkontos durch den Kunden. Steuerrelevante Daten: 10 Jahre gemäß § 257 HGB
                                / § 147 AO.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">6. Ihre Rechte</h2>
                            <p className="mt-2 text-muted-foreground">
                                Sie haben das Recht auf Auskunft (Art. 15 DSGVO), Berichtigung (Art. 16 DSGVO),
                                Löschung (Art. 17 DSGVO), Einschränkung der Verarbeitung (Art. 18 DSGVO),
                                Datenübertragbarkeit (Art. 20 DSGVO) sowie Widerspruch (Art. 21 DSGVO). Anfragen
                                richten Sie bitte an die unter Punkt 1 genannte Kontaktadresse.
                            </p>
                            <p className="mt-2 text-muted-foreground">
                                Sie haben außerdem das Recht, sich bei einer Aufsichtsbehörde zu beschweren —
                                in Deutschland in der Regel bei der Datenschutzaufsicht des Bundeslandes des
                                Verantwortlichen.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">7. Cookies</h2>
                            <p className="mt-2 text-muted-foreground">
                                Wir verwenden ausschließlich technisch notwendige Cookies. Eine Einwilligung gemäß
                                § 25 TTDSG ist hierfür nicht erforderlich. Aktuell werden folgende Cookies gesetzt:
                            </p>
                            <ul className="mt-2 list-disc pl-6 text-muted-foreground">
                                <li>
                                    <code>pt_exam_session</code> — Zufalls-UUID zur Zuordnung anonymer
                                    Prüfungsversuche, gültig 24 Stunden.
                                </li>
                                <li>
                                    <code>laravel_session</code>, <code>XSRF-TOKEN</code> — Standard-Sitzungs- und
                                    CSRF-Cookies des Laravel-Frameworks.
                                </li>
                            </ul>
                        </section>

                        <p className="text-xs text-muted-foreground">
                            Stand: [TODO: Datum der letzten Aktualisierung einsetzen]
                        </p>
                    </div>
                </main>

                <SiteFooter />
            </div>
        </>
    );
}
```

- [ ] **Step 3: Build + commit**

```bash
npm run build
git add resources/js/pages/legal/datenschutz.tsx routes/web.php
git commit -m "feat: add /datenschutz page (skeleton — operator must populate)"
```

---

### Task 7: Impressum page (skeleton)

**Files:**
- Create: `resources/js/pages/legal/impressum.tsx`
- Modify: `routes/web.php`

- [ ] **Step 1: Add `/impressum` route**

Modify `routes/web.php`:

```php
Route::inertia('/impressum', 'legal/impressum')->name('legal.impressum');
```

- [ ] **Step 2: Create the page**

Create `resources/js/pages/legal/impressum.tsx`:

```tsx
import { SiteFooter } from '@/components/site-footer';
import { Head, Link } from '@inertiajs/react';

export default function Impressum() {
    return (
        <>
            <Head title="Impressum · Prüfungstrainer" />

            <div className="min-h-screen bg-background">
                <header className="border-b border-border">
                    <div className="mx-auto flex max-w-3xl items-center justify-between p-6">
                        <Link href="/" className="text-lg font-semibold tracking-tight">
                            Prüfungstrainer
                        </Link>
                        <Link href="/" className="text-sm text-muted-foreground hover:text-foreground">
                            Zurück zur Startseite
                        </Link>
                    </div>
                </header>

                <main className="mx-auto max-w-3xl px-6 py-16">
                    <h1 className="text-3xl font-bold tracking-tight">Impressum</h1>

                    <div className="mt-8 space-y-8 text-sm leading-relaxed">
                        <section>
                            <h2 className="text-lg font-semibold">Angaben gemäß § 5 TMG</h2>
                            <p className="mt-2 text-muted-foreground">
                                [TODO: Vollständiger Name des Anbieters]
                                <br />
                                [TODO: Straße und Hausnummer]
                                <br />
                                [TODO: PLZ und Ort]
                                <br />
                                Deutschland
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">Kontakt</h2>
                            <p className="mt-2 text-muted-foreground">
                                Telefon: [TODO: Telefonnummer]
                                <br />
                                E-Mail:{' '}
                                <a href="mailto:[TODO: kontakt@deine-domain.de]" className="underline">
                                    [TODO: kontakt@deine-domain.de]
                                </a>
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">Umsatzsteuer</h2>
                            <p className="mt-2 text-muted-foreground">
                                [TODO: Eine der folgenden Optionen — Unzutreffendes löschen]
                            </p>
                            <ul className="mt-2 list-disc pl-6 text-muted-foreground">
                                <li>
                                    <strong>Wenn umsatzsteuerpflichtig:</strong> Umsatzsteuer-Identifikationsnummer
                                    gemäß § 27 a UStG: [TODO: USt-IdNr. einsetzen, z. B. DE123456789]
                                </li>
                                <li>
                                    <strong>Wenn Kleinunternehmer:</strong> Gemäß § 19 UStG wird keine Umsatzsteuer
                                    berechnet.
                                </li>
                            </ul>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">Verantwortlich für den Inhalt nach § 18 Abs. 2 MStV</h2>
                            <p className="mt-2 text-muted-foreground">
                                [TODO: Name und Adresse, in der Regel identisch mit dem Anbieter oben]
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">EU-Streitschlichtung</h2>
                            <p className="mt-2 text-muted-foreground">
                                Die Europäische Kommission stellt eine Plattform zur Online-Streitbeilegung (OS)
                                bereit:{' '}
                                <a href="https://ec.europa.eu/consumers/odr" className="underline" target="_blank" rel="noopener noreferrer">
                                    https://ec.europa.eu/consumers/odr
                                </a>
                                . Unsere E-Mail-Adresse finden Sie oben im Impressum.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">Verbraucherstreitbeilegung / Universalschlichtungsstelle</h2>
                            <p className="mt-2 text-muted-foreground">
                                Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer
                                Verbraucherschlichtungsstelle teilzunehmen.
                            </p>
                        </section>

                        <section>
                            <h2 className="text-lg font-semibold">Haftung für Inhalte</h2>
                            <p className="mt-2 text-muted-foreground">
                                Als Diensteanbieter sind wir gemäß § 7 Abs. 1 TMG für eigene Inhalte auf diesen
                                Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir
                                jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu
                                überwachen.
                            </p>
                            <p className="mt-2 text-muted-foreground">
                                Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den
                                allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist
                                jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich.
                            </p>
                        </section>
                    </div>
                </main>

                <SiteFooter />
            </div>
        </>
    );
}
```

- [ ] **Step 3: Build + commit**

```bash
npm run build
git add resources/js/pages/legal/impressum.tsx routes/web.php
git commit -m "feat: add /impressum page (skeleton — operator must populate)"
```

---

### Task 8: Legal pages routing test + final verification

**Files:**
- Create: `tests/Feature/Legal/LegalPagesTest.php`

- [ ] **Step 1: Write a routing-only test**

Create `tests/Feature/Legal/LegalPagesTest.php`:

```php
<?php

it('renders /agb', function () {
    $this->get('/agb')->assertStatus(200)->assertInertia(fn ($page) => $page->component('legal/agb'));
});

it('renders /datenschutz', function () {
    $this->get('/datenschutz')->assertStatus(200)->assertInertia(fn ($page) => $page->component('legal/datenschutz'));
});

it('renders /impressum', function () {
    $this->get('/impressum')->assertStatus(200)->assertInertia(fn ($page) => $page->component('legal/impressum'));
});
```

- [ ] **Step 2: Run the test**

```bash
php artisan test --compact --filter=LegalPagesTest
```

Expected: 3 passing.

- [ ] **Step 3: Full suite**

```bash
php artisan test --compact
```

Expected: 201 passing (198 + 3).

- [ ] **Step 4: Pint + build**

```bash
vendor/bin/pint --format agent
npm run build
```

Expected: clean / succeeds.

- [ ] **Step 5: Routes verification**

```bash
php artisan route:list --except-vendor 2>&1 | grep -E "agb|datenschutz|impressum"
```

Expected: 3 lines for the three legal routes.

- [ ] **Step 6: Manual browser smoke test (operator only)**

```bash
composer run dev
```

Visit:
- `/` — see the new pricing card (€29 with "Founder's Price · 100 von 100 Plätzen frei" or whatever your dev DB count produces) + FAQ + footer
- `/agb` — see AGB skeleton
- `/datenschutz` — see Datenschutz skeleton
- `/impressum` — see Impressum skeleton with `[TODO]` markers

- [ ] **Step 7: Completion marker**

```bash
git add -A
git commit -m "test: legal page routing test"
git commit --allow-empty -m "chore: landing + legal pages complete"
git log --oneline -15
```

- [ ] **Step 8: Do NOT merge, do NOT launch**

Leave branch `feat/landing-and-legal` for human review. **Before launching to real customers**, the operator MUST:

1. Replace every `[TODO: ...]` marker in the three legal pages
2. Have legal text reviewed by a German lawyer or generated by e-recht24.de
3. Configure real Polar production keys (per Plan 6)
4. Set `APP_URL` and `POLAR_SUCCESS_URL` to the production domain
5. Set up `https` with a valid certificate
6. Test full purchase flow with a real €0.50 test product first

---

## Self-Review

**Spec coverage:**
- ✅ Landing page hero + CTA (preserved from Plan 4): Task 4
- ✅ Pricing card with Founder's Price counter: Tasks 2 + 4
- ✅ FAQ: Task 4
- ✅ Footer: Task 3 + Task 4 (uses it)
- ✅ AGB page: Task 5
- ✅ Datenschutz page: Task 6
- ✅ Impressum page: Task 7
- ✅ Routes for all three legal pages: Tasks 5–7
- ✅ Layout bypass for `legal/*` (no AppLayout chrome): Task 5

**Placeholder scan in plan steps:** No "TBD", "implement later", or "add appropriate handling" in instructions. Every step has actual code. The placeholder text `[TODO: ...]` *inside* the legal pages is intentional — it's content the operator must replace, clearly marked, and called out at the top of the plan + at the end of every legal task.

**Type consistency:**
- `Pricing::currentPrice()` returns `array{amount_eur: int, is_founder_price: bool, spots_remaining: int}` — used consistently in Tasks 2 (definition), 4 (route + frontend prop type).
- Frontend prop name `pricing` matches across `routes/web.php` and `welcome.tsx`.
- Inertia component names match: `'legal/agb'`, `'legal/datenschutz'`, `'legal/impressum'`, `'welcome'`.

**Known deferrals (post-MVP, post-launch):**
- No "Sticky CTA" on landing scroll
- No social proof / testimonials block (acquire some first)
- No analytics tracking beyond Laravel defaults
- Pricing doesn't change the actual Polar price — Polar's product config is the source of truth. The frontend is informational. To actually sell at €29, you must have a €29 product configured in Polar (or change the product price after the founder phase).

---

## Execution Handoff

Plan complete and saved to `docs/superpowers/plans/2026-04-14-landing-and-legal.md`. Two execution options:

1. **Subagent-Driven (recommended)** — backend with haiku, frontend tasks 4–7 with sonnet for design judgment.

2. **Inline Execution** — single session with checkpoints.

Which approach?
