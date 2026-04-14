# Exam Simulation Flow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** An anonymous visitor can click "Prüfungssimulation starten", take a 50-question / 60-minute BSI-format exam with server-authoritative timer, multi-select all-or-nothing scoring, back-navigation, flag-for-review, and auto-submit on expiry — then see a results screen with score, pass/fail, topic breakdown, and a locked preview teasing the paid upgrade. A paid logged-in user can do the same, with unlimited attempts.

**Architecture:** Three service classes (`ExamDraw`, `ExamScorer`, `ExamAttemptFinder`) + one controller (`ExamController`) + five routes. Session cookie holds `session_uuid` for anonymous ownership (1-day TTL). Auto-submit logic lives in a small helper called by every controller action — if `now > timer_expires_at`, the attempt is finalized and the user is routed to results. Frontend is three Inertia pages (exam question, results, locked-preview placeholder) plus one countdown component. Locked-preview CTAs point to `/checkout` which is not implemented in this plan — it's a dead link until Plan 6.

**Tech Stack:** Laravel 13, Fortify 1, Inertia 3, React 19, Pest 4, Wayfinder 0, Tailwind v4, shadcn (new-york style, radix base), Instrument Sans + tabular-nums.

## Subsequent plans (not in scope here)

1. **Plan 5 — Freies Lernen:** forgiving practice mode with per-option feedback + wrong-only toggle.
2. **Plan 6 — Checkout & Polar:** wires `/checkout` for real; webhook claims anonymous attempts via `session_uuid`.
3. **Plan 7 — Landing + legal:** marketing landing page hero/pricing/FAQ + AGB / Datenschutz / Impressum.

---

## File Structure

### Services to CREATE

```
app/Services/ExamDraw.php              # Picks 50 questions — 75/25 Basis/Experte, weighted toward unseen
app/Services/ExamScorer.php            # Scores a submitted attempt (all-or-nothing per question), computes topic breakdown
app/Services/ExamAttemptFinder.php     # Locates the "current attempt" for a request: auth user OR session_uuid cookie
```

### Controllers to CREATE

```
app/Http/Controllers/ExamController.php      # start / show / saveAnswer / submit / results
app/Http/Requests/SaveAnswerRequest.php      # validates PATCH payload: selected_option_ids[] + flagged
```

### Routes to MODIFY

```
routes/web.php            # add 5 /pruefungssimulation routes (public — no auth middleware)
```

### Inertia pages to CREATE

```
resources/js/pages/exam/question.tsx     # mid-exam UI: timer + progress + question card + options + flag + back/next/submit
resources/js/pages/exam/results.tsx      # score hero + pass/fail + topic breakdown + locked preview
```

### Components to CREATE

```
resources/js/components/exam-timer.tsx       # tabular-nums countdown, server-time-driven
resources/js/components/locked-preview.tsx   # shadcn Card with blur overlay + Lock icon + upgrade CTA
```

### Inertia pages to MODIFY

```
resources/js/pages/welcome.tsx           # add big "Prüfungssimulation starten" CTA
resources/js/pages/dashboard.tsx         # add "Prüfungssimulation starten" + "Freies Lernen" cards (Freies Lernen link goes to Plan 5 placeholder)
```

### Tests to CREATE

```
tests/Unit/Services/ExamDrawTest.php
tests/Unit/Services/ExamScorerTest.php
tests/Feature/Services/ExamAttemptFinderTest.php
tests/Feature/Exam/StartExamTest.php
tests/Feature/Exam/ShowExamTest.php
tests/Feature/Exam/SaveAnswerTest.php
tests/Feature/Exam/SubmitExamTest.php
tests/Feature/Exam/ResultsTest.php
tests/Feature/Exam/ExamFlowSmokeTest.php     # end-to-end anonymous flow
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

Expected: clean tree on `main`, 102 tests passing.

- [ ] **Step 2: Create feature branch**

```bash
git checkout -b feat/exam-simulation-flow
git commit --allow-empty -m "chore: baseline before exam simulation flow"
```

---

### Task 2: `ExamDraw` service — TDD

**Files:**
- Create: `app/Services/ExamDraw.php`
- Create: `tests/Unit/Services/ExamDrawTest.php`

`ExamDraw` picks 50 questions per exam with these rules:

- 75 % `basis`, 25 % `experte` = 38 basis + 12 experte. If a bucket is short (e.g. only 29 experte in pool), top up from the other bucket.
- For a user with prior attempts: prefer questions the user hasn't answered in any exam attempt. Fall back to least-recently-seen.
- For an anonymous caller (`user_id = null`): pure random.
- Every draw shuffles order (positions 1..50 assigned after shuffle).

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Services/ExamDrawTest.php`:

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Models\User;
use App\Services\ExamDraw;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedQuestions(int $basis, int $experte): void
{
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()->for($module)->count($basis)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Question::factory()->for($module)->count($experte)->tagged(BsiTopic::Methodik, QuestionDifficulty::Experte)->create();
}

it('draws exactly 50 questions when the pool is large enough', function () {
    seedQuestions(basis: 100, experte: 50);

    $questions = (new ExamDraw)->draw(userId: null, total: 50);

    expect($questions)->toHaveCount(50);
});

it('respects the 75/25 basis/experte split when both buckets have room', function () {
    seedQuestions(basis: 100, experte: 50);

    $questions = (new ExamDraw)->draw(userId: null, total: 50);

    $basisCount = $questions->where('difficulty', QuestionDifficulty::Basis)->count();
    $experteCount = $questions->where('difficulty', QuestionDifficulty::Experte)->count();

    expect($basisCount)->toBe(38);
    expect($experteCount)->toBe(12);
});

it('tops up from the other bucket when experte pool is too small', function () {
    seedQuestions(basis: 100, experte: 5);

    $questions = (new ExamDraw)->draw(userId: null, total: 50);

    expect($questions)->toHaveCount(50);

    $experteCount = $questions->where('difficulty', QuestionDifficulty::Experte)->count();
    $basisCount = $questions->where('difficulty', QuestionDifficulty::Basis)->count();

    expect($experteCount)->toBe(5);
    expect($basisCount)->toBe(45);
});

it('returns fewer than 50 when the pool itself is too small', function () {
    seedQuestions(basis: 20, experte: 5);

    $questions = (new ExamDraw)->draw(userId: null, total: 50);

    expect($questions)->toHaveCount(25);
});

it('prefers unseen questions for a user with prior attempts', function () {
    seedQuestions(basis: 100, experte: 50);
    $user = User::factory()->create();

    // Prior attempt: user saw the first 50 questions
    $priorAttempt = ExamAttempt::factory()->forUser($user)->submitted(30)->create();
    $seen = Question::orderBy('id')->limit(50)->get();
    foreach ($seen as $i => $q) {
        ExamAnswer::factory()
            ->for($priorAttempt, 'examAttempt')
            ->for($q)
            ->create(['position' => $i + 1]);
    }

    // New draw should prefer questions the user hasn't seen
    $questions = (new ExamDraw)->draw(userId: $user->id, total: 50);

    $seenIds = $seen->pluck('id')->all();
    $newlyDrawnIds = $questions->pluck('id')->all();
    $overlap = array_intersect($seenIds, $newlyDrawnIds);

    // With 150 questions in pool and 50 already seen, a 50-question draw should mostly avoid overlap.
    // Allow up to 10 overlap (in case the 75/25 constraint forces some reuse).
    expect(count($overlap))->toBeLessThanOrEqual(10);
});

it('skips questions that are still null-tagged (untagged)', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()->for($module)->count(50)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Question::factory()->for($module)->count(10)->create(); // untagged — topic/difficulty NULL

    $questions = (new ExamDraw)->draw(userId: null, total: 40);

    expect($questions->whereNull('difficulty'))->toHaveCount(0);
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=ExamDrawTest
```

Expected: all FAIL with "Class App\Services\ExamDraw not found".

- [ ] **Step 3: Implement `ExamDraw`**

Create `app/Services/ExamDraw.php`:

```php
<?php

namespace App\Services;

use App\Enums\QuestionDifficulty;
use App\Models\ExamAnswer;
use App\Models\Question;
use Illuminate\Support\Collection;

class ExamDraw
{
    public function draw(?int $userId, int $total = 50): Collection
    {
        $basisTarget = (int) round($total * 0.75);
        $experteTarget = $total - $basisTarget;

        $seenQuestionIds = $userId
            ? ExamAnswer::whereHas('examAttempt', fn ($q) => $q->where('user_id', $userId))
                ->pluck('question_id')
                ->unique()
                ->all()
            : [];

        $basis = $this->drawBucket(QuestionDifficulty::Basis, $basisTarget, $seenQuestionIds);
        $experte = $this->drawBucket(QuestionDifficulty::Experte, $experteTarget, $seenQuestionIds);

        // Top-up: if either bucket came up short, borrow from the other bucket (same unseen-first rule).
        $basisShort = $basisTarget - $basis->count();
        $experteShort = $experteTarget - $experte->count();

        if ($basisShort > 0 && $experteShort === 0) {
            $alreadyPicked = $experte->pluck('id')->all();
            $extra = $this->drawBucket(QuestionDifficulty::Experte, $basisShort, array_merge($seenQuestionIds, $alreadyPicked));
            $experte = $experte->merge($extra);
        }

        if ($experteShort > 0 && $basisShort === 0) {
            $alreadyPicked = $basis->pluck('id')->all();
            $extra = $this->drawBucket(QuestionDifficulty::Basis, $experteShort, array_merge($seenQuestionIds, $alreadyPicked));
            $basis = $basis->merge($extra);
        }

        return $basis->merge($experte)->shuffle()->values();
    }

    /**
     * @param  array<int>  $seenQuestionIds
     */
    private function drawBucket(QuestionDifficulty $difficulty, int $target, array $seenQuestionIds): Collection
    {
        if ($target <= 0) {
            return collect();
        }

        $unseen = Question::query()
            ->where('difficulty', $difficulty)
            ->whereNotNull('topic')
            ->whereNotIn('id', $seenQuestionIds)
            ->inRandomOrder()
            ->limit($target)
            ->get();

        if ($unseen->count() >= $target) {
            return $unseen;
        }

        // Fall back: fill remainder from previously-seen questions (least-recently-seen isn't worth the SQL complexity).
        $remaining = $target - $unseen->count();

        $fallback = Question::query()
            ->where('difficulty', $difficulty)
            ->whereNotNull('topic')
            ->whereIn('id', $seenQuestionIds)
            ->inRandomOrder()
            ->limit($remaining)
            ->get();

        return $unseen->merge($fallback);
    }
}
```

Notes:
- `whereNotNull('topic')` implicitly filters out untagged questions (Plan 3 populated tags for all 168, but the check is defensive — future untagged questions shouldn't leak into exams).
- `inRandomOrder()` is the database-level shuffle; the final `->shuffle()` is Collection-level shuffle that mixes the two buckets together so basis and experte are interleaved.
- No caching — a 168-row query is trivially fast.

- [ ] **Step 4: Run tests to verify all pass**

```bash
php artisan test --compact --filter=ExamDrawTest
```

Expected: 6 passing.

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add ExamDraw service with 75/25 split and unseen-first"
```

---

### Task 3: `ExamScorer` service — TDD

**Files:**
- Create: `app/Services/ExamScorer.php`
- Create: `tests/Unit/Services/ExamScorerTest.php`

BSI rule: 1 point iff every correct option ticked AND no wrong option ticked, else 0. This service scores a whole submitted attempt and returns per-question correctness + topic breakdown.

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Services/ExamScorerTest.php`:

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Services\ExamScorer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buildQuestion(array $correct, array $wrong, BsiTopic $topic = BsiTopic::Methodik): Question
{
    $module = Module::firstOrCreate(['slug' => 'm2-bsi-grundschutz'], ['name' => 'M2']);
    $q = Question::factory()->for($module)->tagged($topic, QuestionDifficulty::Basis)->create();

    foreach ($correct as $text) {
        Answer::factory()->for($q)->correct()->create(['text' => $text]);
    }
    foreach ($wrong as $text) {
        Answer::factory()->for($q)->incorrect()->create(['text' => $text]);
    }

    return $q->fresh(['answers']);
}

it('scores a question correct only when every correct option is ticked AND no wrong option is ticked', function () {
    $q = buildQuestion(correct: ['A', 'C'], wrong: ['B', 'D']);
    [$a, $b, $c, $d] = $q->answers->pluck('id')->all();

    $attempt = ExamAttempt::factory()->create();
    $examAnswer = ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q)
        ->create(['selected_option_ids' => [$a, $c], 'position' => 1]);

    (new ExamScorer)->score($attempt->fresh());

    expect($examAnswer->fresh()->is_correct)->toBeTrue();
});

it('scores wrong when one correct option is missing', function () {
    $q = buildQuestion(correct: ['A', 'C'], wrong: ['B', 'D']);
    [$a, $b, $c, $d] = $q->answers->pluck('id')->all();

    $attempt = ExamAttempt::factory()->create();
    $examAnswer = ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q)
        ->create(['selected_option_ids' => [$a], 'position' => 1]);

    (new ExamScorer)->score($attempt->fresh());

    expect($examAnswer->fresh()->is_correct)->toBeFalse();
});

it('scores wrong when one wrong option is ticked', function () {
    $q = buildQuestion(correct: ['A'], wrong: ['B']);
    [$a, $b] = $q->answers->pluck('id')->all();

    $attempt = ExamAttempt::factory()->create();
    $examAnswer = ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q)
        ->create(['selected_option_ids' => [$a, $b], 'position' => 1]);

    (new ExamScorer)->score($attempt->fresh());

    expect($examAnswer->fresh()->is_correct)->toBeFalse();
});

it('scores wrong when no options are ticked', function () {
    $q = buildQuestion(correct: ['A'], wrong: ['B']);

    $attempt = ExamAttempt::factory()->create();
    $examAnswer = ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q)
        ->create(['selected_option_ids' => null, 'position' => 1]);

    (new ExamScorer)->score($attempt->fresh());

    expect($examAnswer->fresh()->is_correct)->toBeFalse();
});

it('writes the total score back to the exam_attempt row', function () {
    $q1 = buildQuestion(correct: ['A'], wrong: ['B']);
    $q2 = buildQuestion(correct: ['X'], wrong: ['Y']);
    $q1Correct = $q1->answers->firstWhere('is_correct', true)->id;

    $attempt = ExamAttempt::factory()->create(['total_questions' => 2]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q1)->create(['selected_option_ids' => [$q1Correct], 'position' => 1]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q2)->create(['selected_option_ids' => null, 'position' => 2]);

    (new ExamScorer)->score($attempt->fresh());

    expect($attempt->fresh()->score)->toBe(1);
});

it('computes a topic breakdown of correct / total per topic', function () {
    $q1 = buildQuestion(correct: ['A'], wrong: ['B'], topic: BsiTopic::Bausteine);
    $q2 = buildQuestion(correct: ['A'], wrong: ['B'], topic: BsiTopic::Bausteine);
    $q3 = buildQuestion(correct: ['A'], wrong: ['B'], topic: BsiTopic::Methodik);

    $attempt = ExamAttempt::factory()->create(['total_questions' => 3]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q1)->create(['selected_option_ids' => [$q1->answers->firstWhere('is_correct', true)->id], 'position' => 1]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q2)->create(['selected_option_ids' => null, 'position' => 2]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q3)->create(['selected_option_ids' => [$q3->answers->firstWhere('is_correct', true)->id], 'position' => 3]);

    $breakdown = (new ExamScorer)->topicBreakdown($attempt->fresh());

    expect($breakdown[BsiTopic::Bausteine->value])->toBe(['correct' => 1, 'total' => 2]);
    expect($breakdown[BsiTopic::Methodik->value])->toBe(['correct' => 1, 'total' => 1]);
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=ExamScorerTest
```

Expected: all FAIL (class not found).

- [ ] **Step 3: Implement `ExamScorer`**

Create `app/Services/ExamScorer.php`:

```php
<?php

namespace App\Services;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;

class ExamScorer
{
    public function score(ExamAttempt $attempt): void
    {
        $attempt->load('examAnswers.question.answers');

        $totalCorrect = 0;

        foreach ($attempt->examAnswers as $examAnswer) {
            $isCorrect = $this->isAnswerCorrect($examAnswer);
            $examAnswer->update(['is_correct' => $isCorrect]);

            if ($isCorrect) {
                $totalCorrect++;
            }
        }

        $attempt->update(['score' => $totalCorrect]);
    }

    /**
     * @return array<string, array{correct: int, total: int}>
     */
    public function topicBreakdown(ExamAttempt $attempt): array
    {
        $attempt->load('examAnswers.question');

        $breakdown = [];

        foreach ($attempt->examAnswers as $examAnswer) {
            $topic = $examAnswer->question->topic?->value;

            if ($topic === null) {
                continue;
            }

            $breakdown[$topic] ??= ['correct' => 0, 'total' => 0];
            $breakdown[$topic]['total']++;

            if ($examAnswer->is_correct) {
                $breakdown[$topic]['correct']++;
            }
        }

        return $breakdown;
    }

    private function isAnswerCorrect(ExamAnswer $examAnswer): bool
    {
        $selected = collect($examAnswer->selected_option_ids ?? []);
        $correctIds = $examAnswer->question->answers->where('is_correct', true)->pluck('id');
        $incorrectIds = $examAnswer->question->answers->where('is_correct', false)->pluck('id');

        $missingCorrect = $correctIds->diff($selected);
        $tickedIncorrect = $selected->intersect($incorrectIds);

        return $missingCorrect->isEmpty() && $tickedIncorrect->isEmpty() && $selected->isNotEmpty();
    }
}
```

Edge case: `$selected->isNotEmpty()` in `isAnswerCorrect` handles the case where a question legitimately has zero correct options. In the BSI format every question has ≥ 1 correct option, so in practice an empty selection = wrong. Documented behavior: "no ticks = score 0".

- [ ] **Step 4: Run tests to verify all pass**

```bash
php artisan test --compact --filter=ExamScorerTest
```

Expected: 6 passing.

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add ExamScorer service with all-or-nothing scoring + topic breakdown"
```

---

### Task 4: `ExamAttemptFinder` service — TDD

**Files:**
- Create: `app/Services/ExamAttemptFinder.php`
- Create: `tests/Feature/Services/ExamAttemptFinderTest.php`

Finds the attempt that matches a given request — either by auth user OR by session_uuid cookie. Returns null if no match (controller returns 404).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Services/ExamAttemptFinderTest.php`:

```php
<?php

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;

it('matches an attempt owned by the authenticated user', function () {
    $user = User::factory()->create();
    $attempt = ExamAttempt::factory()->forUser($user)->create();

    $this->actingAs($user);
    $request = request();

    $found = (new ExamAttemptFinder)->find($request, $attempt->id);

    expect($found?->id)->toBe($attempt->id);
});

it('returns null when auth user does not own the attempt', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $attempt = ExamAttempt::factory()->forUser($owner)->create();

    $this->actingAs($intruder);

    $found = (new ExamAttemptFinder)->find(request(), $attempt->id);

    expect($found)->toBeNull();
});

it('matches an anonymous attempt by session_uuid cookie', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);

    $request = request();
    $request->cookies->set('pt_exam_session', 'abc-123');

    $found = (new ExamAttemptFinder)->find($request, $attempt->id);

    expect($found?->id)->toBe($attempt->id);
});

it('returns null when anonymous cookie does not match the attempt', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);

    $request = request();
    $request->cookies->set('pt_exam_session', 'wrong-uuid');

    $found = (new ExamAttemptFinder)->find($request, $attempt->id);

    expect($found)->toBeNull();
});

it('returns null when the attempt does not exist', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $found = (new ExamAttemptFinder)->find(request(), 999999);

    expect($found)->toBeNull();
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=ExamAttemptFinderTest
```

Expected: FAIL (class not found).

- [ ] **Step 3: Implement `ExamAttemptFinder`**

Create `app/Services/ExamAttemptFinder.php`:

```php
<?php

namespace App\Services;

use App\Models\ExamAttempt;
use Illuminate\Http\Request;

class ExamAttemptFinder
{
    public const SESSION_COOKIE = 'pt_exam_session';

    public function find(Request $request, int $attemptId): ?ExamAttempt
    {
        $attempt = ExamAttempt::find($attemptId);

        if ($attempt === null) {
            return null;
        }

        if ($attempt->user_id !== null) {
            return $request->user()?->id === $attempt->user_id ? $attempt : null;
        }

        $cookie = $request->cookie(self::SESSION_COOKIE);

        return $cookie !== null && $cookie === $attempt->session_uuid ? $attempt : null;
    }
}
```

- [ ] **Step 4: Run tests to verify all pass**

```bash
php artisan test --compact --filter=ExamAttemptFinderTest
```

Expected: 5 passing.

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add ExamAttemptFinder service (auth user + anonymous cookie)"
```

---

### Task 5: Exam start endpoint — TDD

**Files:**
- Create: `app/Http/Controllers/ExamController.php` (with `start` method only)
- Create: `tests/Feature/Exam/StartExamTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Exam/StartExamTest.php`:

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Models\User;
use App\Services\ExamAttemptFinder;

beforeEach(function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()->for($module)->count(80)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Question::factory()->for($module)->count(30)->tagged(BsiTopic::Methodik, QuestionDifficulty::Experte)->create();
});

it('creates an anonymous attempt with session_uuid + 50 exam_answers and redirects', function () {
    $response = $this->post('/pruefungssimulation/start');

    $response->assertStatus(302);

    $attempt = ExamAttempt::latest('id')->first();

    expect($attempt)->not->toBeNull()
        ->and($attempt->user_id)->toBeNull()
        ->and($attempt->session_uuid)->not->toBeNull()
        ->and($attempt->total_questions)->toBe(50)
        ->and($attempt->examAnswers)->toHaveCount(50);

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}");
    $response->assertCookie(ExamAttemptFinder::SESSION_COOKIE, $attempt->session_uuid);
});

it('creates an authenticated attempt with user_id (no session_uuid) when logged in', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/pruefungssimulation/start');

    $attempt = ExamAttempt::latest('id')->first();

    expect($attempt->user_id)->toBe($user->id)
        ->and($attempt->session_uuid)->toBeNull()
        ->and($attempt->is_free_attempt)->toBeFalse()
        ->and($attempt->examAnswers)->toHaveCount(50);

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}");
});

it('assigns unique position 1..50 across exam_answers', function () {
    $this->post('/pruefungssimulation/start');

    $attempt = ExamAttempt::latest('id')->first();
    $positions = $attempt->examAnswers->pluck('position')->sort()->values()->all();

    expect($positions)->toBe(range(1, 50));
});

it('sets timer_expires_at to exactly 60 minutes after started_at', function () {
    $this->post('/pruefungssimulation/start');

    $attempt = ExamAttempt::latest('id')->first();

    $diffMinutes = $attempt->started_at->diffInMinutes($attempt->timer_expires_at);
    expect($diffMinutes)->toBe(60);
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=StartExamTest
```

Expected: FAIL (route not defined, 404).

- [ ] **Step 3: Create controller with `start` action**

Create `app/Http/Controllers/ExamController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use App\Services\ExamAttemptFinder;
use App\Services\ExamDraw;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamDraw $examDraw,
        private readonly ExamAttemptFinder $finder,
    ) {
    }

    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();
        $sessionUuid = $user ? null : (string) Str::uuid();

        $questions = $this->examDraw->draw(userId: $user?->id, total: 50);

        $attempt = DB::transaction(function () use ($user, $sessionUuid, $questions) {
            $startedAt = now();

            $attempt = ExamAttempt::create([
                'user_id' => $user?->id,
                'session_uuid' => $sessionUuid,
                'started_at' => $startedAt,
                'timer_expires_at' => $startedAt->copy()->addMinutes(60),
                'total_questions' => $questions->count(),
                'is_free_attempt' => $user === null,
            ]);

            foreach ($questions as $i => $question) {
                $attempt->examAnswers()->create([
                    'question_id' => $question->id,
                    'position' => $i + 1,
                ]);
            }

            return $attempt;
        });

        $response = redirect("/pruefungssimulation/{$attempt->id}");

        if ($sessionUuid !== null) {
            $response->withCookie(Cookie::make(
                ExamAttemptFinder::SESSION_COOKIE,
                $sessionUuid,
                minutes: 60 * 24, // 1 day
            ));
        }

        return $response;
    }
}
```

- [ ] **Step 4: Add the route**

Modify `routes/web.php`. Add after the `Route::inertia('/', ...)` line and before the auth group:

```php
Route::post('/pruefungssimulation/start', [\App\Http\Controllers\ExamController::class, 'start'])->name('exam.start');
```

- [ ] **Step 5: Regenerate Wayfinder**

```bash
php artisan wayfinder:generate --with-form
```

- [ ] **Step 6: Run tests to verify all pass**

```bash
php artisan test --compact --filter=StartExamTest
```

Expected: 4 passing.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: exam start endpoint (anonymous + authenticated)"
```

---

### Task 6: Exam show endpoint — TDD

**Files:**
- Modify: `app/Http/Controllers/ExamController.php` (add `show` action)
- Create: `tests/Feature/Exam/ShowExamTest.php`
- Modify: `routes/web.php`

The `show` action renders the exam UI. Loads attempt + questions + current answers. Auto-submits if timer already expired.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Exam/ShowExamTest.php`:

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Services\ExamAttemptFinder;

beforeEach(function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $this->question = Question::factory()->for($module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($this->question)->count(4)->create();
});

it('renders the exam page for the owner of an anonymous attempt', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('exam/question')
        ->has('attempt')
        ->has('questions', 1)
    );
});

it('returns 404 when the session cookie does not match', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'wrong')
        ->get("/pruefungssimulation/{$attempt->id}");

    $response->assertStatus(404);
});

it('redirects to results if the timer has already expired', function () {
    $attempt = ExamAttempt::factory()->expired()->create(['session_uuid' => 'abc-123']);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}");

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}/ergebnis");
    expect($attempt->fresh()->submitted_at)->not->toBeNull();
});

it('redirects to results if the attempt is already submitted', function () {
    $attempt = ExamAttempt::factory()->submitted(30)->create(['session_uuid' => 'abc-123']);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}");

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}/ergebnis");
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=ShowExamTest
```

Expected: FAIL (route missing).

- [ ] **Step 3: Add `show` action to controller**

Add to `app/Http/Controllers/ExamController.php` (inside the class, as a method):

```php
public function show(Request $request, int $attempt): \Illuminate\Http\Response|RedirectResponse|\Inertia\Response
{
    $examAttempt = $this->finder->find($request, $attempt);

    if ($examAttempt === null) {
        abort(404);
    }

    if ($examAttempt->isSubmitted() || $examAttempt->hasExpired()) {
        $this->autoSubmitIfNeeded($examAttempt);

        return redirect("/pruefungssimulation/{$examAttempt->id}/ergebnis");
    }

    $examAttempt->load(['examAnswers.question.answers']);

    $questions = $examAttempt->examAnswers
        ->sortBy('position')
        ->values()
        ->map(fn ($ea) => [
            'position' => $ea->position,
            'question_id' => $ea->question_id,
            'text' => $ea->question->text,
            'options' => $ea->question->answers
                ->shuffle()
                ->map(fn ($a) => ['id' => $a->id, 'text' => $a->text])
                ->values(),
            'selected_option_ids' => $ea->selected_option_ids ?? [],
            'flagged' => $ea->flagged,
        ]);

    return inertia('exam/question', [
        'attempt' => [
            'id' => $examAttempt->id,
            'timer_expires_at' => $examAttempt->timer_expires_at->toIso8601String(),
            'total_questions' => $examAttempt->total_questions,
        ],
        'questions' => $questions,
    ]);
}

private function autoSubmitIfNeeded(ExamAttempt $attempt): void
{
    if ($attempt->isSubmitted()) {
        return;
    }

    app(ExamScorer::class)->score($attempt);
    $attempt->update(['submitted_at' => now()]);
}
```

Add the `use App\Services\ExamScorer;` import at the top.

Also add `use Inertia\Response;` — actually, just use the inertia() helper which handles the return type.

- [ ] **Step 4: Add the route**

Modify `routes/web.php`. Add after the start route:

```php
Route::get('/pruefungssimulation/{attempt}', [\App\Http\Controllers\ExamController::class, 'show'])->name('exam.show');
```

- [ ] **Step 5: Regenerate Wayfinder + run tests**

```bash
php artisan wayfinder:generate --with-form
php artisan test --compact --filter=ShowExamTest
```

Expected: 4 passing.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: exam show endpoint with auto-submit on expiry"
```

---

### Task 7: Save answer endpoint — TDD

**Files:**
- Modify: `app/Http/Controllers/ExamController.php` (add `saveAnswer` action)
- Create: `app/Http/Requests/SaveAnswerRequest.php`
- Create: `tests/Feature/Exam/SaveAnswerTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Exam/SaveAnswerTest.php`:

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Services\ExamAttemptFinder;

beforeEach(function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $this->question = Question::factory()->for($module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($this->question)->count(4)->create();
});

it('saves selected_option_ids + flagged for a valid position', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);
    $examAnswer = ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);
    $optionIds = $this->question->answers->pluck('id')->take(2)->values()->all();

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->patchJson("/pruefungssimulation/{$attempt->id}/answer/1", [
            'selected_option_ids' => $optionIds,
            'flagged' => true,
        ]);

    $response->assertStatus(200);
    expect($examAnswer->fresh()->selected_option_ids)->toBe($optionIds);
    expect($examAnswer->fresh()->flagged)->toBeTrue();
});

it('accepts an empty selection', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);
    $examAnswer = ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1, 'selected_option_ids' => [1, 2]]);

    $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->patchJson("/pruefungssimulation/{$attempt->id}/answer/1", [
            'selected_option_ids' => [],
            'flagged' => false,
        ]);

    expect($examAnswer->fresh()->selected_option_ids)->toBe([]);
});

it('returns 404 when cookie does not match', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'wrong')
        ->patchJson("/pruefungssimulation/{$attempt->id}/answer/1", [
            'selected_option_ids' => [],
            'flagged' => false,
        ]);

    $response->assertStatus(404);
});

it('rejects save after timer has expired (auto-submit)', function () {
    $attempt = ExamAttempt::factory()->expired()->create(['session_uuid' => 'abc-123']);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->patchJson("/pruefungssimulation/{$attempt->id}/answer/1", [
            'selected_option_ids' => [],
            'flagged' => false,
        ]);

    $response->assertStatus(409);
    expect($attempt->fresh()->submitted_at)->not->toBeNull();
});

it('returns 422 when position does not exist in the attempt', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->patchJson("/pruefungssimulation/{$attempt->id}/answer/99", [
            'selected_option_ids' => [],
            'flagged' => false,
        ]);

    $response->assertStatus(404);
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=SaveAnswerTest
```

Expected: FAIL (route missing).

- [ ] **Step 3: Create form request**

Create `app/Http/Requests/SaveAnswerRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected_option_ids' => ['present', 'array'],
            'selected_option_ids.*' => ['integer', 'exists:answers,id'],
            'flagged' => ['required', 'boolean'],
        ];
    }
}
```

- [ ] **Step 4: Add `saveAnswer` action to controller**

Add to `app/Http/Controllers/ExamController.php`:

```php
use App\Http\Requests\SaveAnswerRequest;

// ... inside the class:

public function saveAnswer(SaveAnswerRequest $request, int $attempt, int $position): \Illuminate\Http\JsonResponse
{
    $examAttempt = $this->finder->find($request, $attempt);

    if ($examAttempt === null) {
        abort(404);
    }

    if ($examAttempt->isSubmitted()) {
        abort(409, 'Attempt already submitted');
    }

    if ($examAttempt->hasExpired()) {
        $this->autoSubmitIfNeeded($examAttempt);
        abort(409, 'Timer expired');
    }

    $examAnswer = $examAttempt->examAnswers()->where('position', $position)->first();

    if ($examAnswer === null) {
        abort(404);
    }

    $examAnswer->update([
        'selected_option_ids' => $request->input('selected_option_ids'),
        'flagged' => $request->boolean('flagged'),
    ]);

    return response()->json(['saved' => true]);
}
```

- [ ] **Step 5: Add the route**

Modify `routes/web.php`. Add:

```php
Route::patch('/pruefungssimulation/{attempt}/answer/{position}', [\App\Http\Controllers\ExamController::class, 'saveAnswer'])->name('exam.save-answer');
```

- [ ] **Step 6: Regenerate Wayfinder + run tests**

```bash
php artisan wayfinder:generate --with-form
php artisan test --compact --filter=SaveAnswerTest
```

Expected: 5 passing.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: exam save-answer endpoint with timer + ownership checks"
```

---

### Task 8: Exam submit endpoint — TDD

**Files:**
- Modify: `app/Http/Controllers/ExamController.php` (add `submit` action)
- Create: `tests/Feature/Exam/SubmitExamTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Exam/SubmitExamTest.php`:

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Services\ExamAttemptFinder;

beforeEach(function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
});

it('finalizes score + submitted_at and redirects to results', function () {
    $q = Question::factory()->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $correct = Answer::factory()->for($q)->correct()->create();
    Answer::factory()->for($q)->incorrect()->create();

    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123', 'total_questions' => 1]);
    ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q)
        ->create(['selected_option_ids' => [$correct->id], 'position' => 1]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->post("/pruefungssimulation/{$attempt->id}/submit");

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}/ergebnis");

    $fresh = $attempt->fresh();
    expect($fresh->submitted_at)->not->toBeNull();
    expect($fresh->score)->toBe(1);
});

it('is idempotent — re-submitting a submitted attempt just redirects', function () {
    $attempt = ExamAttempt::factory()->submitted(30)->create(['session_uuid' => 'abc-123']);
    $originalSubmittedAt = $attempt->submitted_at;

    $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->post("/pruefungssimulation/{$attempt->id}/submit");

    expect($attempt->fresh()->submitted_at->toIso8601String())->toBe($originalSubmittedAt->toIso8601String());
});

it('returns 404 for non-owner', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'wrong')
        ->post("/pruefungssimulation/{$attempt->id}/submit");

    $response->assertStatus(404);
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=SubmitExamTest
```

Expected: FAIL (route missing).

- [ ] **Step 3: Add `submit` action**

Add to `app/Http/Controllers/ExamController.php`:

```php
public function submit(Request $request, int $attempt): RedirectResponse
{
    $examAttempt = $this->finder->find($request, $attempt);

    if ($examAttempt === null) {
        abort(404);
    }

    $this->autoSubmitIfNeeded($examAttempt);

    return redirect("/pruefungssimulation/{$examAttempt->id}/ergebnis");
}
```

Note: `autoSubmitIfNeeded` already handles the idempotent case — it no-ops if `isSubmitted()`.

- [ ] **Step 4: Add the route**

Modify `routes/web.php`. Add:

```php
Route::post('/pruefungssimulation/{attempt}/submit', [\App\Http\Controllers\ExamController::class, 'submit'])->name('exam.submit');
```

- [ ] **Step 5: Regenerate + run tests**

```bash
php artisan wayfinder:generate --with-form
php artisan test --compact --filter=SubmitExamTest
```

Expected: 3 passing.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: exam submit endpoint (idempotent scoring)"
```

---

### Task 9: Results endpoint — TDD

**Files:**
- Modify: `app/Http/Controllers/ExamController.php` (add `results` action)
- Create: `tests/Feature/Exam/ResultsTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Exam/ResultsTest.php`:

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Services\ExamAttemptFinder;

beforeEach(function () {
    $this->module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
});

it('renders score + pass status + topic breakdown for a submitted attempt', function () {
    $q1 = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $q2 = Question::factory()->for($this->module)->tagged(BsiTopic::Bausteine, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($q1)->correct()->create();
    Answer::factory()->for($q2)->correct()->create();

    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123', 'total_questions' => 2]);
    ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q1)
        ->create([
            'selected_option_ids' => [$q1->answers->first()->id],
            'is_correct' => true,
            'position' => 1,
        ]);
    ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q2)
        ->create(['selected_option_ids' => [], 'is_correct' => false, 'position' => 2]);

    $attempt->update(['score' => 1, 'submitted_at' => now()]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}/ergebnis");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('exam/results')
        ->where('attempt.score', 1)
        ->where('attempt.total_questions', 2)
        ->where('attempt.passed', false) // 1/2 = 50%, below 60% threshold
        ->has('topicBreakdown')
    );
});

it('marks attempt as passed when score >= 60%', function () {
    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($q)->correct()->create();

    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123', 'total_questions' => 50, 'score' => 35, 'submitted_at' => now()]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q)->create(['position' => 1, 'is_correct' => true]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}/ergebnis");

    $response->assertInertia(fn ($page) => $page->where('attempt.passed', true));
});

it('returns 404 for non-owner', function () {
    $attempt = ExamAttempt::factory()->submitted(30)->create(['session_uuid' => 'abc-123']);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'wrong')
        ->get("/pruefungssimulation/{$attempt->id}/ergebnis");

    $response->assertStatus(404);
});

it('redirects to exam page if the attempt is still in progress (not submitted)', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}/ergebnis");

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}");
});
```

- [ ] **Step 2: Run tests**

```bash
php artisan test --compact --filter=ResultsTest
```

Expected: FAIL (route missing).

- [ ] **Step 3: Add `results` action**

Add to `app/Http/Controllers/ExamController.php`:

```php
public function results(Request $request, int $attempt): \Inertia\Response|RedirectResponse
{
    $examAttempt = $this->finder->find($request, $attempt);

    if ($examAttempt === null) {
        abort(404);
    }

    if (! $examAttempt->isSubmitted() && ! $examAttempt->hasExpired()) {
        return redirect("/pruefungssimulation/{$examAttempt->id}");
    }

    $this->autoSubmitIfNeeded($examAttempt);

    $total = $examAttempt->total_questions;
    $score = $examAttempt->score ?? 0;
    $passed = $total > 0 && ($score / $total) >= 0.60;

    return inertia('exam/results', [
        'attempt' => [
            'id' => $examAttempt->id,
            'score' => $score,
            'total_questions' => $total,
            'passed' => $passed,
            'submitted_at' => $examAttempt->submitted_at?->toIso8601String(),
            'is_claimed' => $examAttempt->user_id !== null,
        ],
        'topicBreakdown' => app(ExamScorer::class)->topicBreakdown($examAttempt),
    ]);
}
```

- [ ] **Step 4: Add the route**

Modify `routes/web.php`. Add:

```php
Route::get('/pruefungssimulation/{attempt}/ergebnis', [\App\Http\Controllers\ExamController::class, 'results'])->name('exam.results');
```

- [ ] **Step 5: Regenerate + run tests**

```bash
php artisan wayfinder:generate --with-form
php artisan test --compact --filter=ResultsTest
```

Expected: 4 passing.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: exam results endpoint (score + pass + topic breakdown)"
```

---

### Task 10: Welcome page CTA + dashboard cards

**Files:**
- Modify: `resources/js/pages/welcome.tsx`
- Modify: `resources/js/pages/dashboard.tsx`

Welcome page: add a prominent `Prüfungssimulation starten` CTA that POSTs to `/pruefungssimulation/start`. No login required.

Dashboard: add two shadcn `Card`s — "Prüfungssimulation" + "Freies Lernen". Freies Lernen card link points to `/freies-lernen` (route doesn't exist yet; navigate will 404 until Plan 5).

- [ ] **Step 1: Read current welcome.tsx**

Use Read to inspect `resources/js/pages/welcome.tsx`. Note its existing structure and auth nav pattern.

- [ ] **Step 2: Replace welcome.tsx body**

Replace the rendered page content with (keeping existing imports + auth nav):

```tsx
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';
import { Clock, FileCheck, Lock } from 'lucide-react';

export default function Welcome() {
    const { auth } = usePage().props as any;

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

                <main className="mx-auto max-w-3xl px-6 py-24">
                    <h1 className="text-5xl font-bold tracking-tight">BSI IT-Grundschutz-Praktiker</h1>
                    <p className="mt-4 text-xl text-muted-foreground">
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
                                <CardDescription>Zufall aus 168 BSI-Prüfungsfragen. 75 % Basis, 25 % Experte.</CardDescription>
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
                                <CardDescription>Nach der Simulation: detaillierte Antwort-Erklärungen mit BSI-Quellen ab 29 € einmalig.</CardDescription>
                            </CardHeader>
                        </Card>
                    </div>
                </main>
            </div>
        </>
    );
}
```

CSRF token: Laravel's default Blade layout includes a `<meta name="csrf-token">` tag. We pull it here for the POST form to pass Laravel's CSRF middleware. If this doesn't work (e.g., the layout doesn't include it), add it to `resources/views/app.blade.php` in the `<head>`:

```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

Verify `app.blade.php` has this tag — add if missing.

- [ ] **Step 3: Replace dashboard.tsx body**

Read current `resources/js/pages/dashboard.tsx`. Replace with:

```tsx
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { FileCheck, GraduationCap } from 'lucide-react';

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={[{ title: 'Dashboard', href: '/dashboard' }]}>
            <Head title="Dashboard" />

            <div className="mx-auto max-w-4xl py-8 px-6">
                <h1 className="text-3xl font-bold tracking-tight">Willkommen zurück</h1>
                <p className="mt-2 text-muted-foreground">
                    Wähle einen Modus, um weiterzulernen.
                </p>

                <div className="mt-8 grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <FileCheck className="size-7 text-primary" />
                            <CardTitle className="mt-3">Prüfungssimulation</CardTitle>
                            <CardDescription>
                                50 Fragen, 60 Minuten, ohne Feedback — wie die echte BSI-Prüfung.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form method="POST" action="/pruefungssimulation/start">
                                <input
                                    type="hidden"
                                    name="_token"
                                    value={(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? ''}
                                />
                                <Button type="submit" className="w-full">
                                    Simulation starten
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <GraduationCap className="size-7 text-primary" />
                            <CardTitle className="mt-3">Freies Lernen</CardTitle>
                            <CardDescription>
                                Fragen einzeln mit sofortigem Feedback und Quellen-Zitat. Kommt bald.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Button asChild variant="outline" className="w-full" disabled>
                                <Link href="/freies-lernen">Freies Lernen starten</Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
```

The Freies Lernen button is disabled-styled (though `disabled` on `<Button asChild>` doesn't work reliably — it's a visual hint). The actual `/freies-lernen` route is implemented in Plan 5.

- [ ] **Step 4: Build frontend to verify**

```bash
npm run build
```

Expected: build succeeds. Any missing import path or unresolved component name fails loudly — fix before continuing.

- [ ] **Step 5: Commit**

```bash
git add resources/js/pages/welcome.tsx resources/js/pages/dashboard.tsx resources/views/app.blade.php
git commit -m "feat: exam CTAs on welcome + dashboard pages"
```

---

### Task 11: Exam timer component

**Files:**
- Create: `resources/js/components/exam-timer.tsx`

Pure display component. Receives `expiresAt` (ISO 8601 string) as prop. Computes remaining seconds against `Date.now()`, updates every second. Uses `tabular-nums` per the design system. Visual accent red when < 5 minutes remain.

- [ ] **Step 1: Create the component**

```tsx
import { cn } from '@/lib/utils';
import { Clock } from 'lucide-react';
import { useEffect, useState } from 'react';

export function ExamTimer({ expiresAt, className }: { expiresAt: string; className?: string }) {
    const [remaining, setRemaining] = useState(() => Math.max(0, Math.floor((new Date(expiresAt).getTime() - Date.now()) / 1000)));

    useEffect(() => {
        const tick = () => setRemaining(Math.max(0, Math.floor((new Date(expiresAt).getTime() - Date.now()) / 1000)));
        tick();
        const interval = setInterval(tick, 1000);
        return () => clearInterval(interval);
    }, [expiresAt]);

    const mins = Math.floor(remaining / 60);
    const secs = remaining % 60;
    const isWarning = remaining < 300; // under 5 min
    const isExpired = remaining === 0;

    return (
        <div
            className={cn(
                'inline-flex items-center gap-2 rounded-md border border-border bg-card px-3 py-2 text-sm font-medium tabular-nums',
                isWarning && !isExpired && 'text-warning',
                isExpired && 'text-destructive',
                className,
            )}
        >
            <Clock data-icon="inline-start" className="size-4" />
            <span>
                {String(mins).padStart(2, '0')}:{String(secs).padStart(2, '0')}
            </span>
        </div>
    );
}
```

Note: the client timer is display-only. Server-authoritative timer runs independently — if the client clock is skewed, the server still enforces at 60 min. The UI just hides this by recomputing against `Date.now()` (relative to user's machine).

- [ ] **Step 2: Build to verify**

```bash
npm run build
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/components/exam-timer.tsx
git commit -m "feat: exam timer component (tabular-nums, warning + expired states)"
```

---

### Task 12: Exam question Inertia page

**Files:**
- Create: `resources/js/pages/exam/question.tsx`

Renders the in-progress exam: timer, progress strip, one question at a time with its 2-6 options, checkbox for multi-select, flag toggle, back/next/submit buttons. Saves answer on change (debounced by using `patch` directly on each interaction — Inertia handles request queuing).

- [ ] **Step 1: Create the page**

```tsx
import { ExamTimer } from '@/components/exam-timer';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Head, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Flag, FlagOff } from 'lucide-react';
import { useState } from 'react';

type Option = { id: number; text: string };
type Question = {
    position: number;
    question_id: number;
    text: string;
    options: Option[];
    selected_option_ids: number[];
    flagged: boolean;
};

type Attempt = {
    id: number;
    timer_expires_at: string;
    total_questions: number;
};

export default function ExamQuestion({ attempt, questions }: { attempt: Attempt; questions: Question[] }) {
    const [currentPosition, setCurrentPosition] = useState(1);
    const [state, setState] = useState(() =>
        questions.reduce<Record<number, { selected: number[]; flagged: boolean }>>((acc, q) => {
            acc[q.position] = { selected: q.selected_option_ids, flagged: q.flagged };
            return acc;
        }, {}),
    );

    const current = questions.find((q) => q.position === currentPosition)!;
    const currentState = state[currentPosition];

    const save = (nextSelected: number[], nextFlagged: boolean) => {
        router.patch(
            `/pruefungssimulation/${attempt.id}/answer/${currentPosition}`,
            { selected_option_ids: nextSelected, flagged: nextFlagged },
            { preserveScroll: true, preserveState: true, only: [] },
        );
    };

    const toggleOption = (optionId: number) => {
        const nextSelected = currentState.selected.includes(optionId)
            ? currentState.selected.filter((id) => id !== optionId)
            : [...currentState.selected, optionId];

        setState({ ...state, [currentPosition]: { ...currentState, selected: nextSelected } });
        save(nextSelected, currentState.flagged);
    };

    const toggleFlag = () => {
        const nextFlagged = !currentState.flagged;
        setState({ ...state, [currentPosition]: { ...currentState, flagged: nextFlagged } });
        save(currentState.selected, nextFlagged);
    };

    const submit = () => {
        if (!confirm('Prüfung endgültig abschicken? Danach kannst du keine Antworten mehr ändern.')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/pruefungssimulation/${attempt.id}/submit`;
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '';
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    };

    const answeredCount = Object.values(state).filter((s) => s.selected.length > 0).length;

    return (
        <>
            <Head title={`Frage ${currentPosition} / ${attempt.total_questions}`} />

            <div className="min-h-screen bg-background">
                <header className="sticky top-0 z-10 border-b border-border bg-background/95 backdrop-blur">
                    <div className="mx-auto flex max-w-3xl items-center justify-between px-6 py-4">
                        <div className="text-sm tabular-nums text-muted-foreground">
                            Frage {currentPosition} / {attempt.total_questions}
                            <span className="ml-3">· {answeredCount} beantwortet</span>
                        </div>
                        <ExamTimer expiresAt={attempt.timer_expires_at} />
                    </div>
                </header>

                <main className="mx-auto max-w-3xl px-6 py-8">
                    <Card className="border-border">
                        <CardHeader>
                            <div className="flex items-start justify-between gap-4">
                                <div className="text-lg leading-relaxed">{current.text}</div>
                                <Button
                                    type="button"
                                    variant={currentState.flagged ? 'default' : 'outline'}
                                    size="sm"
                                    onClick={toggleFlag}
                                    aria-label={currentState.flagged ? 'Markierung entfernen' : 'Frage markieren'}
                                >
                                    {currentState.flagged ? (
                                        <Flag data-icon="inline-start" className="size-4" />
                                    ) : (
                                        <FlagOff data-icon="inline-start" className="size-4" />
                                    )}
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3">
                            {current.options.map((option) => (
                                <Label
                                    key={option.id}
                                    className="flex cursor-pointer items-start gap-3 rounded-md border border-border p-3 hover:bg-muted"
                                >
                                    <Checkbox
                                        checked={currentState.selected.includes(option.id)}
                                        onCheckedChange={() => toggleOption(option.id)}
                                    />
                                    <span className="text-base leading-relaxed">{option.text}</span>
                                </Label>
                            ))}
                        </CardContent>
                    </Card>

                    <p className="mt-3 text-xs text-muted-foreground">
                        Mehrfachauswahl möglich. Alle richtigen Optionen ankreuzen — ein falsch angekreuztes Feld reicht, damit die Frage als falsch gewertet wird.
                    </p>

                    <div className="mt-8 flex items-center justify-between">
                        <Button
                            type="button"
                            variant="outline"
                            disabled={currentPosition === 1}
                            onClick={() => setCurrentPosition(currentPosition - 1)}
                        >
                            <ChevronLeft data-icon="inline-start" className="size-4" />
                            Zurück
                        </Button>

                        {currentPosition < attempt.total_questions ? (
                            <Button type="button" onClick={() => setCurrentPosition(currentPosition + 1)}>
                                Weiter
                                <ChevronRight data-icon="inline-end" className="size-4" />
                            </Button>
                        ) : (
                            <Button type="button" onClick={submit}>
                                Prüfung abschicken
                            </Button>
                        )}
                    </div>
                </main>
            </div>
        </>
    );
}
```

This page uses the Dashboard layout's visual language (via shadcn) without actually extending `AppLayout` — the exam takes the full viewport (no sidebar). The header is a minimal top bar.

- [ ] **Step 2: Build to verify**

```bash
npm run build
```

Fix any missing components. The `Flag` icon is in lucide-react — if it doesn't exist as `Flag`, substitute `Bookmark` or similar.

- [ ] **Step 3: Commit**

```bash
git add resources/js/pages/exam/question.tsx
git commit -m "feat: exam question Inertia page"
```

---

### Task 13: Exam results Inertia page

**Files:**
- Create: `resources/js/components/locked-preview.tsx`
- Create: `resources/js/pages/exam/results.tsx`

Results page shows a big score hero, pass/fail badge, topic breakdown as progress bars, and a locked preview section teasing the paid upgrade. The upgrade CTA links to `/checkout` (not yet implemented — Plan 6).

- [ ] **Step 1: Create `locked-preview.tsx`**

```tsx
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Lock } from 'lucide-react';
import { ReactNode } from 'react';

export function LockedPreview({ children, ctaText = 'Lifetime-Zugang freischalten · 29 €' }: { children: ReactNode; ctaText?: string }) {
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
                    <Button asChild size="lg">
                        <a href="/checkout">{ctaText}</a>
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}
```

- [ ] **Step 2: Create `results.tsx`**

```tsx
import { LockedPreview } from '@/components/locked-preview';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Head } from '@inertiajs/react';
import { CheckCircle2, XCircle } from 'lucide-react';

type Attempt = {
    id: number;
    score: number;
    total_questions: number;
    passed: boolean;
    submitted_at: string | null;
    is_claimed: boolean;
};

type TopicBreakdown = Record<string, { correct: number; total: number }>;

const TOPIC_LABELS: Record<string, string> = {
    methodik: 'IT-Grundschutz-Methodik',
    bausteine: 'Bausteine',
    risikoanalyse: 'Risikoanalyse',
    modellierung: 'Modellierung',
    check: 'IT-Grundschutz-Check',
    standards: 'BSI-Standards',
    notfall: 'Notfallmanagement',
    siem: 'SIEM / Monitoring',
};

export default function ExamResults({ attempt, topicBreakdown }: { attempt: Attempt; topicBreakdown: TopicBreakdown }) {
    const percentage = Math.round((attempt.score / attempt.total_questions) * 100);

    return (
        <>
            <Head title={`Ergebnis: ${attempt.score} / ${attempt.total_questions}`} />

            <div className="min-h-screen bg-background">
                <main className="mx-auto max-w-3xl px-6 py-16">
                    <Card className="border-border">
                        <CardHeader className="items-center text-center">
                            <CardTitle className="text-base font-medium text-muted-foreground">Dein Ergebnis</CardTitle>
                            <div className="mt-4 text-6xl font-bold tabular-nums">
                                {attempt.score} / {attempt.total_questions}
                            </div>
                            <div className="mt-2 text-2xl text-muted-foreground tabular-nums">{percentage} %</div>
                            <div className="mt-6">
                                {attempt.passed ? (
                                    <Badge variant="default" className="gap-2 bg-success text-success-foreground">
                                        <CheckCircle2 data-icon="inline-start" className="size-4" />
                                        Bestanden (≥ 60 %)
                                    </Badge>
                                ) : (
                                    <Badge variant="default" className="gap-2 bg-warning text-warning-foreground">
                                        <XCircle data-icon="inline-start" className="size-4" />
                                        Unter der Bestehensgrenze
                                    </Badge>
                                )}
                            </div>
                        </CardHeader>
                    </Card>

                    <section className="mt-8">
                        <h2 className="text-lg font-semibold">Themen-Übersicht</h2>
                        <p className="text-sm text-muted-foreground">Wo bist du stark, wo schwach?</p>

                        <div className="mt-4 space-y-2">
                            {Object.entries(topicBreakdown).map(([topic, { correct, total }]) => {
                                const pct = total > 0 ? Math.round((correct / total) * 100) : 0;
                                return (
                                    <div key={topic} className="flex items-center gap-4 rounded-md border border-border p-3">
                                        <div className="flex-1">
                                            <div className="text-sm font-medium">{TOPIC_LABELS[topic] ?? topic}</div>
                                            <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-muted">
                                                <div className="h-full bg-primary" style={{ width: `${pct}%` }} />
                                            </div>
                                        </div>
                                        <div className="text-sm tabular-nums text-muted-foreground">
                                            {correct} / {total} · {pct} %
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </section>

                    <section className="mt-12">
                        <LockedPreview>
                            <div className="space-y-4">
                                <div>
                                    <div className="font-medium">Frage 12</div>
                                    <div className="text-sm text-muted-foreground">Welche Schutzbedarfskategorien kennt der IT-Grundschutz?</div>
                                    <div className="mt-1 text-sm text-destructive">✗ Deine Antwort: Normal, Hoch, Kritisch</div>
                                    <div className="text-sm text-success">✓ Richtig: Normal, Hoch, Sehr hoch</div>
                                </div>
                                <div>
                                    <div className="font-medium">Frage 27</div>
                                    <div className="text-sm text-muted-foreground">Was unterscheidet die integrierte Risikobewertung im IT-Grundschutz von einer klassischen Risikoanalyse?</div>
                                </div>
                            </div>
                        </LockedPreview>
                    </section>

                    <p className="mt-8 text-center text-sm text-muted-foreground">
                        Die Simulation orientiert sich am offiziellen BSI-Prüfungsformat (50 Fragen, 60 Minuten, 60 % Bestehensgrenze).
                        Basis/Experte-Einteilung basiert auf eigener Klassifikation, da BSI die offizielle Verteilung nicht veröffentlicht.
                    </p>
                </main>
            </div>
        </>
    );
}
```

- [ ] **Step 3: Build to verify**

```bash
npm run build
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/components/locked-preview.tsx resources/js/pages/exam/results.tsx
git commit -m "feat: exam results page with topic breakdown + locked preview"
```

---

### Task 14: End-to-end smoke test

**Files:**
- Create: `tests/Feature/Exam/ExamFlowSmokeTest.php`

- [ ] **Step 1: Create the smoke test**

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\Module;
use App\Models\Question;
use App\Services\ExamAttemptFinder;

beforeEach(function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    // Seed enough questions for a full draw.
    Question::factory()->for($module)->count(60)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->has(Answer::factory()->count(2)->correct(), 'answers')
        ->has(Answer::factory()->count(2)->incorrect(), 'answers')
        ->create();
    Question::factory()->for($module)->count(20)->tagged(BsiTopic::Bausteine, QuestionDifficulty::Experte)
        ->has(Answer::factory()->count(1)->correct(), 'answers')
        ->has(Answer::factory()->count(3)->incorrect(), 'answers')
        ->create();
});

it('walks an anonymous user through start → answer 50 → submit → results', function () {
    // Start
    $startResponse = $this->post('/pruefungssimulation/start');
    $startResponse->assertStatus(302);
    $sessionUuid = $startResponse->getCookie(ExamAttemptFinder::SESSION_COOKIE)->getValue();

    // Follow the redirect to the exam page
    $attemptUrl = $startResponse->headers->get('Location');
    preg_match('#/pruefungssimulation/(\d+)$#', $attemptUrl, $matches);
    $attemptId = (int) $matches[1];

    // Show the exam
    $showResponse = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid)->get($attemptUrl);
    $showResponse->assertStatus(200);

    // Answer all 50 questions by ticking the first option (likely wrong)
    $attempt = \App\Models\ExamAttempt::find($attemptId);
    foreach ($attempt->examAnswers as $examAnswer) {
        $firstOptionId = \App\Models\Answer::where('question_id', $examAnswer->question_id)->first()->id;

        $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid)
            ->patchJson("/pruefungssimulation/{$attemptId}/answer/{$examAnswer->position}", [
                'selected_option_ids' => [$firstOptionId],
                'flagged' => false,
            ])
            ->assertStatus(200);
    }

    // Submit
    $submitResponse = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid)
        ->post("/pruefungssimulation/{$attemptId}/submit");
    $submitResponse->assertRedirect("/pruefungssimulation/{$attemptId}/ergebnis");

    // View results
    $resultsResponse = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid)
        ->get("/pruefungssimulation/{$attemptId}/ergebnis");
    $resultsResponse->assertStatus(200);
    $resultsResponse->assertInertia(fn ($page) => $page
        ->component('exam/results')
        ->has('attempt.score')
        ->has('topicBreakdown')
    );

    // Attempt is submitted, score populated
    $fresh = $attempt->fresh();
    expect($fresh->submitted_at)->not->toBeNull();
    expect($fresh->score)->toBeInt();
});
```

- [ ] **Step 2: Run the smoke test**

```bash
php artisan test --compact --filter=ExamFlowSmokeTest
```

Expected: 1 passing.

- [ ] **Step 3: Run full suite**

```bash
php artisan test --compact
```

Expected: all tests pass. Record exact count.

- [ ] **Step 4: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "test: end-to-end exam flow smoke test"
```

---

### Task 15: Final verification + manual browser smoke

**Files:** none (verification only)

- [ ] **Step 1: Full test suite**

```bash
php artisan test --compact
```

Record exact pass count. Expected around 130+ (102 prior + ~30 new).

- [ ] **Step 2: Pint**

```bash
vendor/bin/pint --format agent
```

Expected: clean.

- [ ] **Step 3: Frontend build**

```bash
npm run build
```

Expected: succeeds.

- [ ] **Step 4: Manual browser smoke (requires dev server)**

```bash
composer run dev
```

In a browser:
1. Visit `/` — confirm landing page shows hero + CTA + 3 feature cards.
2. Click **Prüfungssimulation starten** — should redirect to `/pruefungssimulation/<id>` and show question 1 of 50.
3. Verify timer is counting down in the top-right (starts at `59:XX`).
4. Tick an answer — confirm UI updates.
5. Click the flag button — confirm it toggles visually.
6. Click **Weiter** 2-3 times → confirm progress indicator updates (`Frage 3 / 50`), previous answers persist.
7. Click **Zurück** → confirm earlier answer still selected.
8. Click **Weiter** all the way to question 50 — the button text should flip to **Prüfung abschicken**.
9. Click **Prüfung abschicken** → confirm dialog → accept.
10. Should land on `/pruefungssimulation/<id>/ergebnis` — showing score, pass/fail badge, topic breakdown, blurred locked preview.
11. Click the **Lifetime-Zugang freischalten** button — should navigate to `/checkout` (expected 404 until Plan 6 — acceptable).
12. Reload the results page — still shows same score (not a new attempt).
13. Visit `/pruefungssimulation/start` as POST again — creates a NEW attempt, different ID.

If any step throws a 500, open the dev tools + Laravel log and fix before continuing.

- [ ] **Step 5: Check server-authoritative timer by manipulating the DB**

```bash
php artisan tinker --execute '$a = App\Models\ExamAttempt::latest()->first(); $a->update(["timer_expires_at" => now()->subMinute()]); echo "Expired attempt: ".$a->id.PHP_EOL;'
```

Reload `/pruefungssimulation/<that-id>` — should redirect to `/ergebnis` and the attempt should be auto-submitted.

- [ ] **Step 6: Completion marker**

```bash
git commit --allow-empty -m "chore: exam simulation flow complete"
git log --oneline -25
```

- [ ] **Step 7: Do NOT merge**

Leave branch `feat/exam-simulation-flow` for separate merge via the `finishing-a-development-branch` skill.

---

## Self-Review

**Spec coverage:**
- ✅ Anonymous start with session cookie: Task 5
- ✅ 50-question draw with 75/25 split + weighted toward unseen: Task 2
- ✅ Server-authoritative 60-minute timer: Task 5 (set) + Tasks 6/7/8 (enforce via `hasExpired()` check)
- ✅ Auto-submit on expiry: `autoSubmitIfNeeded` helper called from show/save/submit (Task 6 onwards)
- ✅ Multi-select UI: Task 12 uses `Checkbox` per option
- ✅ Hint that it's multi-select: copy below the card in Task 12 ("Mehrfachauswahl möglich…")
- ✅ All-or-nothing scoring: Task 3 `ExamScorer::isAnswerCorrect`
- ✅ Back-navigation: Task 12 local state + prev button
- ✅ Flag-for-review: Task 7 endpoint + Task 12 UI toggle
- ✅ Results screen with score + pass/fail (≥ 60 %): Task 9
- ✅ Topic breakdown: Task 3 `topicBreakdown()` + Task 13 UI rendering
- ✅ Locked preview with paid CTA: Task 13 `LockedPreview` component
- ✅ Works for auth users too: Task 5 handles `$user` branch
- ✅ Unlimited attempts: no rate limit in the controller; each POST /start creates a new attempt

**Placeholder scan:** No TODOs, TBDs, or "handle edge cases" language. All steps have full code or exact commands.

**Type consistency:**
- `ExamDraw::draw(?int $userId, int $total = 50): Collection` — signature stable across Tasks 2, 5, 14.
- `ExamScorer::score(ExamAttempt $attempt): void` + `topicBreakdown(ExamAttempt): array` — stable across Tasks 3, 6, 9.
- `ExamAttemptFinder::find(Request, int $attemptId): ?ExamAttempt` — stable across Tasks 4, 5-9.
- `ExamAttemptFinder::SESSION_COOKIE` constant — referenced consistently in tests + frontend.
- Route paths stable: `/pruefungssimulation/start`, `/{attempt}`, `/{attempt}/answer/{position}`, `/{attempt}/submit`, `/{attempt}/ergebnis`.
- Inertia page component names stable: `'exam/question'` + `'exam/results'`.
- Attempt shape passed to Inertia is the same in Tasks 6 and 9 (id, timer_expires_at, total_questions + score/passed on results).

**Known deferrals:**
- `/checkout` link goes to a 404 — real implementation is Plan 6.
- `/freies-lernen` button on dashboard is non-functional — Plan 5.
- No rate-limiting on `POST /pruefungssimulation/start` — a determined user could spam. Add `throttle:` middleware in Plan 6 or Plan 7 if abuse emerges.

---

## Execution Handoff

Plan complete and saved to `docs/superpowers/plans/2026-04-14-exam-simulation-flow.md`. Two execution options:

1. **Subagent-Driven (recommended)** — fresh subagent per task, consistent with prior plans. Tasks 2–9 are pure backend TDD (fast). Tasks 10–13 are frontend — maybe warrant a standard (not haiku) model for better UI judgment.

2. **Inline Execution** — single session, checkpoints.

Which approach?
