# Freies Lernen Flow Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A logged-in paid user can practice questions one at a time with instant per-option feedback (de-dsb-learner UX), see explanation + BSI source after each answer, toggle "Nur falsche" to drill questions they previously got wrong, and watch a progress strip update as they go.

**Architecture:** One service (`PracticeDraw`) that picks the next question — preferring unseen, optionally filtered to "previously wrong" — plus one controller (`PracticeController`) with two actions (`show` + `saveAnswer`). The Inertia page is single-question-per-visit (de-dsb-learner pattern): user submits → server records to `practice_answers` and returns `is_correct` + correct option ids → user clicks "Nächste Frage" which navigates to `/freies-lernen?wrong_only=X&exclude=<id>`. Access gated by a thin `paid` middleware that checks `user.paid_at IS NOT NULL`. Until Polar is wired (Plan 6), set `paid_at` manually via tinker for testing.

**Tech Stack:** Laravel 13, Fortify 1, Inertia 3, React 19, Pest 4, Wayfinder 0, Tailwind v4, shadcn (new-york style, radix base).

## Subsequent plans (not in scope here)

1. **Plan 6 — Checkout & Polar:** real `paid_at` activation via webhook; until then, dev-only manual paid status.
2. **Plan 7 — Landing + legal.**

---

## File Structure

### Services to CREATE

```
app/Services/PracticeDraw.php          # Picks next question (unseen-first), optional wrong-only filter, plus progress stats
```

### Controllers + Middleware to CREATE

```
app/Http/Controllers/PracticeController.php       # show + saveAnswer
app/Http/Requests/SavePracticeAnswerRequest.php   # validates question_id + selected_option_ids[]
app/Http/Middleware/EnsurePaid.php                # 403 if user.paid_at is null
```

### Routes to MODIFY

```
routes/web.php           # add 2 /freies-lernen routes behind auth + paid middleware
bootstrap/app.php        # register the 'paid' middleware alias
```

### Inertia pages to CREATE

```
resources/js/pages/freies-lernen/index.tsx       # main practice loop (question, options, submit, feedback, next)
```

### Inertia pages to MODIFY

```
resources/js/pages/dashboard.tsx        # enable the "Freies Lernen" card link
```

### Tests to CREATE

```
tests/Feature/Services/PracticeDrawTest.php
tests/Feature/Practice/EnsurePaidMiddlewareTest.php
tests/Feature/Practice/ShowPracticeTest.php
tests/Feature/Practice/SavePracticeAnswerTest.php
tests/Feature/Practice/PracticeFlowSmokeTest.php
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

Expected: clean tree on `main`, 140 tests passing.

- [ ] **Step 2: Create feature branch**

```bash
git checkout -b feat/freies-lernen-flow
git commit --allow-empty -m "chore: baseline before freies lernen"
```

---

### Task 2: `EnsurePaid` middleware — TDD

**Files:**
- Create: `app/Http/Middleware/EnsurePaid.php`
- Create: `tests/Feature/Practice/EnsurePaidMiddlewareTest.php`
- Modify: `bootstrap/app.php` (register alias)

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Practice/EnsurePaidMiddlewareTest.php`:

```php
<?php

use App\Http\Middleware\EnsurePaid;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/_test/paid-only', fn () => 'ok')->middleware(['auth', EnsurePaid::class]);
});

it('allows a paid user through', function () {
    $user = User::factory()->paid()->create();

    $this->actingAs($user)
        ->get('/_test/paid-only')
        ->assertStatus(200);
});

it('returns 403 for an unpaid authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/_test/paid-only')
        ->assertStatus(403);
});

it('redirects an unauthenticated user to login (auth middleware fires first)', function () {
    $this->get('/_test/paid-only')
        ->assertRedirect('/login');
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=EnsurePaidMiddlewareTest
```

Expected: FAIL with "Class App\Http\Middleware\EnsurePaid not found".

- [ ] **Step 3: Create the middleware**

Create `app/Http/Middleware/EnsurePaid.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePaid
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isPaid()) {
            abort(403, 'Paid access required.');
        }

        return $next($request);
    }
}
```

- [ ] **Step 4: Run tests to verify pass**

```bash
php artisan test --compact --filter=EnsurePaidMiddlewareTest
```

Expected: 3 passing.

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add EnsurePaid middleware (gates on user.paid_at)"
```

Note: registering the middleware as a global alias in `bootstrap/app.php` is optional — using the FQCN directly in route definitions (Task 5) is just as good.

---

### Task 3: `PracticeDraw` service — TDD

**Files:**
- Create: `app/Services/PracticeDraw.php`
- Create: `tests/Feature/Services/PracticeDrawTest.php`

`PracticeDraw` selects the next practice question for a user. Two modes:
- Default: pick unseen first; fall back to least-recently-seen.
- Wrong-only: pick a question the user's MOST RECENT practice answer got wrong.

Plus a `progressFor(userId)` method returning `['seen' => N, 'total' => M, 'correct' => K]` where `correct` counts questions the user got right on their MOST RECENT attempt at each.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Services/PracticeDrawTest.php`:

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Module;
use App\Models\PracticeAnswer;
use App\Models\Question;
use App\Models\User;
use App\Services\PracticeDraw;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
});

it('returns null when no tagged questions exist', function () {
    expect((new PracticeDraw)->next($this->user->id))->toBeNull();
});

it('returns a tagged question for an empty-history user', function () {
    Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    $q = (new PracticeDraw)->next($this->user->id);

    expect($q)->not->toBeNull()
        ->and($q->topic)->toBe(BsiTopic::Methodik);
});

it('skips untagged questions', function () {
    Question::factory()->for($this->module)->create(); // no topic/difficulty

    expect((new PracticeDraw)->next($this->user->id))->toBeNull();
});

it('prefers an unseen question over a seen one', function () {
    $seenQuestion = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $unseenQuestion = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    PracticeAnswer::factory()->for($this->user)->for($seenQuestion)->create();

    $q = (new PracticeDraw)->next($this->user->id);

    expect($q->id)->toBe($unseenQuestion->id);
});

it('falls back to seen questions when all are seen', function () {
    $a = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    PracticeAnswer::factory()->for($this->user)->for($a)->create();

    $q = (new PracticeDraw)->next($this->user->id);

    expect($q->id)->toBe($a->id);
});

it('respects exclude parameter', function () {
    $a = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $b = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    $q = (new PracticeDraw)->next($this->user->id, excludeQuestionId: $a->id);

    expect($q->id)->toBe($b->id);
});

it('wrong-only mode returns a question whose latest answer was wrong', function () {
    $wrongQ = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $rightQ = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    PracticeAnswer::factory()->for($this->user)->for($wrongQ)->incorrect([1])->create();
    PracticeAnswer::factory()->for($this->user)->for($rightQ)->correct([1])->create();

    $q = (new PracticeDraw)->next($this->user->id, wrongOnly: true);

    expect($q->id)->toBe($wrongQ->id);
});

it('wrong-only mode uses MOST RECENT answer (a later-correct beats earlier-wrong)', function () {
    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    // First wrong, then right — should NOT be in wrong-only pool
    PracticeAnswer::factory()->for($this->user)->for($q)->incorrect([1])->create();
    PracticeAnswer::factory()->for($this->user)->for($q)->correct([1])->create();

    $next = (new PracticeDraw)->next($this->user->id, wrongOnly: true);

    expect($next)->toBeNull();
});

it('wrong-only mode returns null when no wrong-and-not-yet-corrected questions exist', function () {
    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    PracticeAnswer::factory()->for($this->user)->for($q)->correct([1])->create();

    expect((new PracticeDraw)->next($this->user->id, wrongOnly: true))->toBeNull();
});

it('progressFor reports seen / total / correct counts', function () {
    $a = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $b = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $c = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    PracticeAnswer::factory()->for($this->user)->for($a)->correct([1])->create();
    PracticeAnswer::factory()->for($this->user)->for($b)->incorrect([1])->create();
    // c is unseen

    $progress = (new PracticeDraw)->progressFor($this->user->id);

    expect($progress)->toBe([
        'seen' => 2,
        'total' => 3,
        'correct' => 1,
    ]);
});

it('progressFor counts MOST RECENT answer per question', function () {
    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    // Wrong first, then right — should count as correct
    PracticeAnswer::factory()->for($this->user)->for($q)->incorrect([1])->create();
    PracticeAnswer::factory()->for($this->user)->for($q)->correct([1])->create();

    $progress = (new PracticeDraw)->progressFor($this->user->id);

    expect($progress)->toBe([
        'seen' => 1,
        'total' => 1,
        'correct' => 1,
    ]);
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=PracticeDrawTest
```

Expected: FAIL (class not found).

- [ ] **Step 3: Implement `PracticeDraw`**

Create `app/Services/PracticeDraw.php`:

```php
<?php

namespace App\Services;

use App\Models\PracticeAnswer;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class PracticeDraw
{
    public function next(int $userId, bool $wrongOnly = false, ?int $excludeQuestionId = null): ?Question
    {
        $base = Question::query()
            ->whereNotNull('topic')
            ->when($excludeQuestionId, fn ($q) => $q->where('id', '!=', $excludeQuestionId));

        if ($wrongOnly) {
            $latestWrongQuestionIds = $this->latestWrongQuestionIds($userId);

            return $base
                ->whereIn('id', $latestWrongQuestionIds)
                ->inRandomOrder()
                ->first();
        }

        $seenIds = PracticeAnswer::where('user_id', $userId)
            ->pluck('question_id')
            ->unique()
            ->all();

        $unseen = (clone $base)
            ->whereNotIn('id', $seenIds)
            ->inRandomOrder()
            ->first();

        if ($unseen !== null) {
            return $unseen;
        }

        return $base->inRandomOrder()->first();
    }

    /**
     * @return array{seen: int, total: int, correct: int}
     */
    public function progressFor(int $userId): array
    {
        $total = Question::whereNotNull('topic')->count();
        $seen = PracticeAnswer::where('user_id', $userId)->distinct('question_id')->count('question_id');
        $correct = $this->latestCorrectQuestionCount($userId);

        return ['seen' => $seen, 'total' => $total, 'correct' => $correct];
    }

    /**
     * @return array<int>
     */
    private function latestWrongQuestionIds(int $userId): array
    {
        // Subquery: latest practice_answer id per question for this user
        $latestIds = DB::table('practice_answers')
            ->selectRaw('MAX(id) as id')
            ->where('user_id', $userId)
            ->groupBy('question_id')
            ->pluck('id');

        return PracticeAnswer::whereIn('id', $latestIds)
            ->where('is_correct', false)
            ->pluck('question_id')
            ->all();
    }

    private function latestCorrectQuestionCount(int $userId): int
    {
        $latestIds = DB::table('practice_answers')
            ->selectRaw('MAX(id) as id')
            ->where('user_id', $userId)
            ->groupBy('question_id')
            ->pluck('id');

        return PracticeAnswer::whereIn('id', $latestIds)
            ->where('is_correct', true)
            ->count();
    }
}
```

- [ ] **Step 4: Run tests to verify pass**

```bash
php artisan test --compact --filter=PracticeDrawTest
```

Expected: 11 passing.

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add PracticeDraw service (unseen-first + wrong-only mode + progress)"
```

---

### Task 4: Practice show endpoint — TDD

**Files:**
- Create: `app/Http/Controllers/PracticeController.php` (with `show` only)
- Create: `tests/Feature/Practice/ShowPracticeTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Practice/ShowPracticeTest.php`:

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\Module;
use App\Models\PracticeAnswer;
use App\Models\Question;
use App\Models\User;

beforeEach(function () {
    $this->module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
});

it('renders the practice page with a question for a paid user', function () {
    $user = User::factory()->paid()->create();
    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($q)->count(4)->create();

    $response = $this->actingAs($user)->get('/freies-lernen');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('freies-lernen/index')
        ->has('question.id')
        ->has('question.options', 4)
        ->has('progress.seen')
        ->has('progress.total')
        ->has('progress.correct')
        ->where('wrongOnly', false)
    );
});

it('passes wrongOnly query param through to props', function () {
    $user = User::factory()->paid()->create();
    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($q)->correct()->create();
    PracticeAnswer::factory()->for($user)->for($q)->incorrect([1])->create();

    $response = $this->actingAs($user)->get('/freies-lernen?wrong_only=1');

    $response->assertInertia(fn ($page) => $page
        ->where('wrongOnly', true)
        ->where('question.id', $q->id)
    );
});

it('honors exclude query param', function () {
    $user = User::factory()->paid()->create();
    $a = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $b = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($a)->count(2)->create();
    Answer::factory()->for($b)->count(2)->create();

    $response = $this->actingAs($user)->get("/freies-lernen?exclude={$a->id}");

    $response->assertInertia(fn ($page) => $page->where('question.id', $b->id));
});

it('renders an empty state when no question is available', function () {
    $user = User::factory()->paid()->create();
    // Wrong-only mode with no wrong answers in history → no question
    Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    $response = $this->actingAs($user)->get('/freies-lernen?wrong_only=1');

    $response->assertInertia(fn ($page) => $page
        ->component('freies-lernen/index')
        ->where('question', null)
        ->where('wrongOnly', true)
    );
});

it('returns 403 for an unpaid user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/freies-lernen')->assertStatus(403);
});

it('redirects unauthenticated user to login', function () {
    $this->get('/freies-lernen')->assertRedirect('/login');
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=ShowPracticeTest
```

Expected: FAIL.

- [ ] **Step 3: Create controller**

Create `app/Http/Controllers/PracticeController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Services\PracticeDraw;
use Illuminate\Http\Request;
use Inertia\Response;

class PracticeController extends Controller
{
    public function __construct(private readonly PracticeDraw $practiceDraw)
    {
    }

    public function show(Request $request): Response
    {
        $user = $request->user();
        $wrongOnly = $request->boolean('wrong_only');
        $excludeId = $request->integer('exclude') ?: null;

        $question = $this->practiceDraw->next($user->id, $wrongOnly, $excludeId);

        return inertia('freies-lernen/index', [
            'question' => $question === null ? null : [
                'id' => $question->id,
                'text' => $question->text,
                'topic' => $question->topic?->value,
                'topic_label' => $question->topic?->label(),
                'options' => $question->answers()->get(['id', 'text'])->shuffle()->values(),
            ],
            'wrongOnly' => $wrongOnly,
            'progress' => $this->practiceDraw->progressFor($user->id),
        ]);
    }
}
```

- [ ] **Step 4: Add route**

Read `routes/web.php`. Add after the existing exam routes (and before `require __DIR__.'/settings.php'`):

```php
Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsurePaid::class])->group(function () {
    Route::get('/freies-lernen', [\App\Http\Controllers\PracticeController::class, 'show'])->name('practice.show');
});
```

- [ ] **Step 5: Regenerate Wayfinder + run tests**

```bash
php artisan wayfinder:generate --with-form
php artisan test --compact --filter=ShowPracticeTest
```

Expected: 6 passing.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: practice show endpoint with wrong-only + exclude support"
```

---

### Task 5: Practice save-answer endpoint — TDD

**Files:**
- Create: `app/Http/Requests/SavePracticeAnswerRequest.php`
- Modify: `app/Http/Controllers/PracticeController.php` (add `saveAnswer`)
- Create: `tests/Feature/Practice/SavePracticeAnswerTest.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Practice/SavePracticeAnswerTest.php`:

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\Module;
use App\Models\PracticeAnswer;
use App\Models\Question;
use App\Models\User;

beforeEach(function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $this->question = Question::factory()->for($module)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->create();
    $this->correct = Answer::factory()->for($this->question)->correct()->create();
    $this->wrong = Answer::factory()->for($this->question)->incorrect()->create();
});

it('records a practice answer and returns is_correct + correct option ids + explanation', function () {
    $user = User::factory()->paid()->create();

    $response = $this->actingAs($user)->postJson('/freies-lernen/answer', [
        'question_id' => $this->question->id,
        'selected_option_ids' => [$this->correct->id],
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'is_correct' => true,
        'correct_option_ids' => [$this->correct->id],
        'explanation' => $this->question->explanation,
    ]);

    expect(PracticeAnswer::where('user_id', $user->id)->count())->toBe(1);
    $row = PracticeAnswer::first();
    expect($row->is_correct)->toBeTrue();
    expect($row->selected_option_ids)->toBe([$this->correct->id]);
});

it('marks wrong when only some correct options are ticked', function () {
    $user = User::factory()->paid()->create();
    $extraCorrect = Answer::factory()->for($this->question)->correct()->create();

    $response = $this->actingAs($user)->postJson('/freies-lernen/answer', [
        'question_id' => $this->question->id,
        'selected_option_ids' => [$this->correct->id], // missing $extraCorrect
    ]);

    $response->assertJson(['is_correct' => false]);
    expect(PracticeAnswer::first()->is_correct)->toBeFalse();
});

it('marks wrong when a wrong option is ticked alongside correct', function () {
    $user = User::factory()->paid()->create();

    $response = $this->actingAs($user)->postJson('/freies-lernen/answer', [
        'question_id' => $this->question->id,
        'selected_option_ids' => [$this->correct->id, $this->wrong->id],
    ]);

    $response->assertJson(['is_correct' => false]);
});

it('marks wrong when no options are ticked', function () {
    $user = User::factory()->paid()->create();

    $response = $this->actingAs($user)->postJson('/freies-lernen/answer', [
        'question_id' => $this->question->id,
        'selected_option_ids' => [],
    ]);

    $response->assertJson(['is_correct' => false]);
});

it('returns 403 for an unpaid user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/freies-lernen/answer', [
        'question_id' => $this->question->id,
        'selected_option_ids' => [$this->correct->id],
    ])->assertStatus(403);
});

it('returns 422 for non-existent question', function () {
    $user = User::factory()->paid()->create();

    $this->actingAs($user)->postJson('/freies-lernen/answer', [
        'question_id' => 999999,
        'selected_option_ids' => [],
    ])->assertStatus(422);
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=SavePracticeAnswerTest
```

Expected: FAIL.

- [ ] **Step 3: Create form request**

Create `app/Http/Requests/SavePracticeAnswerRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SavePracticeAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_id' => ['required', 'integer', 'exists:questions,id'],
            'selected_option_ids' => ['present', 'array'],
            'selected_option_ids.*' => ['integer', 'exists:answers,id'],
        ];
    }
}
```

- [ ] **Step 4: Add `saveAnswer` action**

Modify `app/Http/Controllers/PracticeController.php`. Add this import:

```php
use App\Http\Requests\SavePracticeAnswerRequest;
use App\Models\Answer;
use App\Models\PracticeAnswer;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
```

And add this method to the class (after `show`):

```php
public function saveAnswer(SavePracticeAnswerRequest $request): JsonResponse
{
    $user = $request->user();
    $question = Question::with('answers')->findOrFail($request->integer('question_id'));
    $selected = collect($request->input('selected_option_ids', []));

    $correctIds = $question->answers->where('is_correct', true)->pluck('id');
    $incorrectIds = $question->answers->where('is_correct', false)->pluck('id');

    $missingCorrect = $correctIds->diff($selected);
    $tickedIncorrect = $selected->intersect($incorrectIds);
    $isCorrect = $missingCorrect->isEmpty() && $tickedIncorrect->isEmpty() && $selected->isNotEmpty();

    PracticeAnswer::create([
        'user_id' => $user->id,
        'question_id' => $question->id,
        'selected_option_ids' => $selected->values()->all(),
        'is_correct' => $isCorrect,
    ]);

    return response()->json([
        'is_correct' => $isCorrect,
        'correct_option_ids' => $correctIds->values()->all(),
        'explanation' => $question->explanation,
        'quote' => $question->quote,
        'source' => $question->source,
    ]);
}
```

- [ ] **Step 5: Add route**

Modify `routes/web.php`. Inside the same `auth + verified + EnsurePaid` group from Task 4, add:

```php
Route::post('/freies-lernen/answer', [\App\Http\Controllers\PracticeController::class, 'saveAnswer'])->name('practice.save-answer');
```

- [ ] **Step 6: Regenerate + run tests**

```bash
php artisan wayfinder:generate --with-form
php artisan test --compact --filter=SavePracticeAnswerTest
```

Expected: 6 passing.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: practice save-answer endpoint with all-or-nothing scoring"
```

---

### Task 6: Practice Inertia page UI

**Files:**
- Create: `resources/js/pages/freies-lernen/index.tsx`

The page handles two visual states:
- **Question presented:** options + "Antwort prüfen" button
- **Answer revealed:** highlights correct/wrong per option + explanation + source + "Nächste Frage" button

Plus a header with: progress strip ("X von Y · Z% korrekt"), wrong-only toggle.

- [ ] **Step 1: Create the page**

```tsx
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { cn } from '@/lib/utils';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { CheckCircle2, XCircle } from 'lucide-react';
import { useState } from 'react';

type Option = { id: number; text: string };
type Question = {
    id: number;
    text: string;
    topic: string | null;
    topic_label: string | null;
    options: Option[];
};

type Progress = {
    seen: number;
    total: number;
    correct: number;
};

type Feedback = {
    is_correct: boolean;
    correct_option_ids: number[];
    explanation: string;
    quote: string | null;
    source: string | null;
};

export default function FreiesLernen({
    question,
    wrongOnly,
    progress,
}: {
    question: Question | null;
    wrongOnly: boolean;
    progress: Progress;
}) {
    const [selected, setSelected] = useState<number[]>([]);
    const [feedback, setFeedback] = useState<Feedback | null>(null);
    const [submitting, setSubmitting] = useState(false);

    const toggleOption = (id: number) => {
        if (feedback) return; // can't change after revealing
        setSelected((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]));
    };

    const submit = async () => {
        if (!question || submitting) return;
        setSubmitting(true);
        try {
            const csrf = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '';
            const res = await axios.post(
                '/freies-lernen/answer',
                { question_id: question.id, selected_option_ids: selected },
                { headers: { 'X-CSRF-TOKEN': csrf } },
            );
            setFeedback(res.data);
        } finally {
            setSubmitting(false);
        }
    };

    const next = () => {
        const params = new URLSearchParams();
        if (wrongOnly) params.set('wrong_only', '1');
        if (question) params.set('exclude', String(question.id));
        router.visit(`/freies-lernen?${params.toString()}`);
    };

    const toggleWrongOnly = (checked: boolean) => {
        const params = new URLSearchParams();
        if (checked) params.set('wrong_only', '1');
        router.visit(`/freies-lernen?${params.toString()}`);
    };

    const accuracy = progress.seen > 0 ? Math.round((progress.correct / progress.seen) * 100) : 0;

    const optionStyle = (optionId: number): string => {
        if (!feedback) {
            return selected.includes(optionId) ? 'border-primary bg-primary/5' : 'border-border';
        }
        const isCorrect = feedback.correct_option_ids.includes(optionId);
        const wasSelected = selected.includes(optionId);

        if (isCorrect && wasSelected) return 'border-success bg-success/10';
        if (isCorrect && !wasSelected) return 'border-success bg-success/5';
        if (!isCorrect && wasSelected) return 'border-destructive bg-destructive/10';
        return 'border-border';
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Freies Lernen', href: '/freies-lernen' }]}>
            <Head title="Freies Lernen" />

            <div className="mx-auto max-w-2xl px-6 py-8">
                <header className="flex items-center justify-between gap-4">
                    <div className="text-sm text-muted-foreground tabular-nums">
                        {progress.seen} von {progress.total} gesehen · {accuracy} % korrekt
                    </div>
                    <Label className="flex cursor-pointer items-center gap-2 text-sm">
                        <Switch checked={wrongOnly} onCheckedChange={toggleWrongOnly} />
                        Nur falsch beantwortete
                    </Label>
                </header>

                {question === null ? (
                    <Card className="mt-8">
                        <CardContent className="py-12 text-center text-muted-foreground">
                            {wrongOnly
                                ? 'Keine falsch beantworteten Fragen vorhanden. Schalte den Filter aus, um neue Fragen zu üben.'
                                : 'Keine Fragen verfügbar.'}
                        </CardContent>
                    </Card>
                ) : (
                    <>
                        <Card className="mt-8">
                            <CardHeader>
                                <div className="text-lg leading-relaxed">{question.text}</div>
                                {question.topic_label && (
                                    <div className="text-xs text-muted-foreground">{question.topic_label}</div>
                                )}
                            </CardHeader>
                            <CardContent className="flex flex-col gap-3">
                                {question.options.map((option) => (
                                    <Label
                                        key={option.id}
                                        className={cn(
                                            'flex cursor-pointer items-start gap-3 rounded-md border p-3 transition-colors',
                                            optionStyle(option.id),
                                            feedback && 'cursor-default',
                                        )}
                                    >
                                        <Checkbox
                                            checked={selected.includes(option.id)}
                                            onCheckedChange={() => toggleOption(option.id)}
                                            disabled={feedback !== null}
                                        />
                                        <span className="text-base leading-relaxed">{option.text}</span>
                                        {feedback && feedback.correct_option_ids.includes(option.id) && (
                                            <CheckCircle2 className="ml-auto size-4 text-success" />
                                        )}
                                        {feedback && !feedback.correct_option_ids.includes(option.id) && selected.includes(option.id) && (
                                            <XCircle className="ml-auto size-4 text-destructive" />
                                        )}
                                    </Label>
                                ))}
                            </CardContent>
                        </Card>

                        {feedback && (
                            <Card className="mt-4 border-border">
                                <CardContent className="space-y-3 py-6">
                                    <div className={cn('text-sm font-medium', feedback.is_correct ? 'text-success' : 'text-destructive')}>
                                        {feedback.is_correct ? '✓ Richtig' : '✗ Falsch'}
                                    </div>
                                    <p className="text-sm leading-relaxed">{feedback.explanation}</p>
                                    {feedback.quote && (
                                        <blockquote className="border-l-2 border-border pl-3 text-sm italic text-muted-foreground">
                                            {feedback.quote}
                                        </blockquote>
                                    )}
                                    {feedback.source && <div className="text-xs text-muted-foreground">Quelle: {feedback.source}</div>}
                                </CardContent>
                            </Card>
                        )}

                        <div className="mt-6 flex justify-end">
                            {feedback ? (
                                <Button onClick={next}>Nächste Frage</Button>
                            ) : (
                                <Button onClick={submit} disabled={selected.length === 0 || submitting}>
                                    Antwort prüfen
                                </Button>
                            )}
                        </div>
                    </>
                )}
            </div>
        </AppLayout>
    );
}
```

- [ ] **Step 2: Verify Switch component is installed**

The page imports `@/components/ui/switch`. Check if it exists:

```bash
ls resources/js/components/ui/switch.tsx 2>&1
```

If missing:

```bash
npx shadcn@latest add switch
```

- [ ] **Step 3: Verify axios is available**

The page imports `axios`. It's typically present in Laravel + Inertia projects via `bootstrap.js`. Check `package.json` and `resources/js/app.tsx` to confirm. If missing:

```bash
npm install axios
```

- [ ] **Step 4: Build to verify**

```bash
npm run build
```

Expected: succeeds.

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact
```

Expected: tests still pass (frontend changes don't affect PHP tests).

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "feat: freies-lernen practice page with feedback + wrong-only toggle"
```

---

### Task 7: Enable dashboard "Freies Lernen" link

**Files:**
- Modify: `resources/js/pages/dashboard.tsx`

Plan 4 made the Freies Lernen card a placeholder ("Kommt bald" with a disabled-styled button). Now we wire it to actually navigate.

- [ ] **Step 1: Read dashboard.tsx and update the Freies Lernen card**

In `resources/js/pages/dashboard.tsx`, find the Freies Lernen Card. Update:

- Card description: change `"Fragen einzeln mit sofortigem Feedback und Quellen-Zitat. Kommt bald."` to `"Fragen einzeln mit sofortigem Feedback und Quellen-Zitat."`.
- Button: keep the existing `<Link href="/freies-lernen">Freies Lernen starten</Link>` but ensure no `disabled` styling/className remains. Change `variant="outline"` to `variant="default"` if you want it more prominent — or keep outline if visual hierarchy distinguishes the primary "Prüfungssimulation" CTA from this secondary one.

The exact diff depends on what's currently there — read the file first.

- [ ] **Step 2: Build to verify**

```bash
npm run build
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/pages/dashboard.tsx
git commit -m "feat: enable freies-lernen card on dashboard"
```

---

### Task 8: End-to-end practice flow smoke test

**Files:**
- Create: `tests/Feature/Practice/PracticeFlowSmokeTest.php`

- [ ] **Step 1: Create the smoke test**

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\Module;
use App\Models\PracticeAnswer;
use App\Models\Question;
use App\Models\User;

beforeEach(function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);

    Question::factory()->for($module)->count(5)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->has(Answer::factory()->correct()->count(1), 'answers')
        ->has(Answer::factory()->incorrect()->count(2), 'answers')
        ->create();
});

it('walks a paid user through 3 practice questions and tracks progress', function () {
    $user = User::factory()->paid()->create();

    for ($i = 0; $i < 3; $i++) {
        // GET the page — get a question
        $showResponse = $this->actingAs($user)->get('/freies-lernen?exclude='.($i > 0 ? PracticeAnswer::where('user_id', $user->id)->latest('id')->first()?->question_id : 0));
        $showResponse->assertStatus(200);

        $questionId = $showResponse->viewData('page')['props']['question']['id'];
        $correctOptionId = Answer::where('question_id', $questionId)->where('is_correct', true)->first()->id;

        // POST an answer (always correct)
        $answerResponse = $this->actingAs($user)->postJson('/freies-lernen/answer', [
            'question_id' => $questionId,
            'selected_option_ids' => [$correctOptionId],
        ]);
        $answerResponse->assertStatus(200);
        $answerResponse->assertJson(['is_correct' => true]);
    }

    // Verify progress
    $progressResponse = $this->actingAs($user)->get('/freies-lernen');
    $progressResponse->assertInertia(fn ($page) => $page
        ->where('progress.seen', 3)
        ->where('progress.correct', 3)
    );

    expect(PracticeAnswer::where('user_id', $user->id)->count())->toBe(3);
});
```

- [ ] **Step 2: Run the smoke test**

```bash
php artisan test --compact --filter=PracticeFlowSmokeTest
```

Expected: 1 passing.

- [ ] **Step 3: Run full suite**

```bash
php artisan test --compact
```

Expected: all tests pass.

- [ ] **Step 4: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "test: end-to-end freies-lernen flow smoke test"
```

---

### Task 9: Final verification

**Files:** none (verification only)

- [ ] **Step 1: Full suite**

```bash
php artisan test --compact
```

Record exact pass count.

- [ ] **Step 2: Pint**

```bash
vendor/bin/pint --format agent
```

Expected: clean. Commit if anything formatted.

- [ ] **Step 3: Build**

```bash
npm run build
```

- [ ] **Step 4: Routes verification**

```bash
php artisan route:list --except-vendor 2>&1 | grep -E "freies-lernen|practice"
```

Expected output: 2 lines — `GET /freies-lernen` and `POST /freies-lernen/answer`.

- [ ] **Step 5: Manual test prep — promote a user to paid**

For local manual testing, register a user via the UI or:

```bash
php artisan tinker --execute '$u = App\Models\User::factory()->paid()->create(["email" => "test@example.com", "password" => bcrypt("password")]); echo "User #".$u->id." promoted to paid.".PHP_EOL;'
```

Then visit `/login`, sign in as `test@example.com` / `password`, and click **Freies Lernen starten** on the dashboard.

- [ ] **Step 6: Completion marker**

```bash
git commit --allow-empty -m "chore: freies lernen flow complete"
git log --oneline -15
```

- [ ] **Step 7: Do NOT merge**

Leave branch `feat/freies-lernen-flow` for separate merge via the `finishing-a-development-branch` skill.

---

## Self-Review

**Spec coverage:**
- ✅ Per-question forgiving feedback (per-option correctness highlight): Task 6 `optionStyle` function
- ✅ Explanation + BSI source + quote shown after answering: Task 6 feedback Card
- ✅ Wrong-only toggle: Task 6 `Switch` + Task 4 `wrong_only` query param + Task 3 `PracticeDraw` mode
- ✅ Progress strip ("X von Y · Z% korrekt"): Task 6 header + Task 3 `progressFor()`
- ✅ Paid-user-only gate: Task 2 `EnsurePaid` middleware
- ✅ Tracks every answer in `practice_answers` table (separate from exam tracking): Task 5 `saveAnswer` writes via `PracticeAnswer::create`
- ✅ Dashboard link enabled: Task 7

**Placeholder scan:** All steps have actual code or commands.

**Type consistency:**
- `PracticeDraw::next(int $userId, bool $wrongOnly = false, ?int $excludeQuestionId = null): ?Question` — signature stable across Tasks 3, 4.
- `PracticeDraw::progressFor(int $userId): array{seen,total,correct}` — stable across Tasks 3, 4, 6.
- Route paths stable: `/freies-lernen` (GET), `/freies-lernen/answer` (POST).
- Inertia page name `'freies-lernen/index'` stable across Tasks 4, 6.
- Inertia props shape: `{question, wrongOnly, progress}` consistent.

**Known dependencies / deferrals:**
- Real `paid_at` activation comes via Polar webhook in Plan 6. For now, manually promote test users via `User::factory()->paid()->create([...])`.
- The `axios` import in Task 6 assumes axios is in `package.json`. Inertia v3 dropped axios from defaults — verify in Step 3 of Task 6 and `npm install axios` if missing.

---

## Execution Handoff

Plan complete and saved to `docs/superpowers/plans/2026-04-14-freies-lernen-flow.md`. Two execution options:

1. **Subagent-Driven (recommended)** — same proven pattern. Backend tasks 2–5 fast with haiku; frontend Task 6 warrants sonnet for UI judgment.

2. **Inline Execution** — single session with checkpoints.

Which approach?
