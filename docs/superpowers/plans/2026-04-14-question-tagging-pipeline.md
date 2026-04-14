# Question Tagging Pipeline Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Classify every seeded question's `topic` (one of 8 `BsiTopic` values) and `difficulty` (Basis or Experte) by calling Claude Haiku 4.5 via the Anthropic Messages API, persisting results to the `questions` table. After this plan lands, the exam-draw logic (Plan 4) can enforce BSI's 75 % Basis / 25 % Experte split and the results screen (Plan 4) can show a per-topic accuracy breakdown.

**Architecture:** One service class (`QuestionClassifier`) that builds Anthropic requests and parses responses, wrapped by one Artisan command (`questions:classify`) that iterates over untagged questions. Uses Anthropic's prompt caching (the classification schema is identical across 165 calls → big cost + latency win). Dev runs the command locally with their `ANTHROPIC_API_KEY`. No client-side LLM code; no long-running queue — 165 sequential HTTP calls is small enough to run synchronously.

**Tech Stack:** Laravel 13, PHP 8.4, Pest 4, Laravel's `Http` facade for HTTP (no SDK), Anthropic Messages API (`claude-haiku-4-5-20251001`), prompt caching via `cache_control: ephemeral`.

## Subsequent plans (not in scope here)

1. **Plan 4 — Exam simulation flow:** uses `topic` + `difficulty` for weighted draw + topic breakdown.
2. **Plan 5 — Freies Lernen:** uses `topic` (optional filter) + `is_correct` history from `practice_answers`.
3. **Plan 6 — Checkout & Polar:** claims anonymous attempt at webhook time.
4. **Plan 7 — Landing + legal.**

---

## File Structure

### Files to CREATE

```
app/Services/QuestionClassifier.php          # Builds + parses Anthropic requests
app/Console/Commands/ClassifyQuestions.php    # Wraps the service, iterates questions
config/services.php                           # (modify) add 'anthropic' config block
tests/Unit/Services/QuestionClassifierTest.php
tests/Feature/Console/ClassifyQuestionsTest.php
```

### Files to MODIFY

```
config/services.php                 # add anthropic.api_key + anthropic.model
.env.example                        # add ANTHROPIC_API_KEY=
```

### Files NOT touched

- No migrations (`questions.topic` + `questions.difficulty` columns already exist from Plan 2)
- No frontend code
- No existing models — only the command writes to `Question`, via mass update

---

## Tasks

### Task 1: Baseline + branch

**Files:** none (setup only)

- [ ] **Step 1: Verify clean main + green tests**

```bash
git status
php artisan test --compact
```

Expected: clean tree on `main`, 87 tests passing.

- [ ] **Step 2: Create feature branch**

```bash
git checkout -b feat/question-tagging-pipeline
git commit --allow-empty -m "chore: baseline before tagging pipeline"
```

---

### Task 2: Anthropic config + env wiring

**Files:**
- Modify: `config/services.php`
- Modify: `.env.example`

- [ ] **Step 1: Add `anthropic` block to `config/services.php`**

Read `config/services.php`. Append before the closing `];`:

```php
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),
        'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
    ],
```

- [ ] **Step 2: Add env keys to `.env.example`**

Append to `.env.example`:

```
ANTHROPIC_API_KEY=
ANTHROPIC_MODEL=claude-haiku-4-5-20251001
```

Do NOT touch `.env` (local, gitignored — developer's own responsibility).

- [ ] **Step 3: Verify config reads**

Run:

```bash
php artisan config:show services.anthropic
```

Expected: shows `api_key: null`, `model: claude-haiku-4-5-20251001`, `base_url: https://api.anthropic.com/v1`.

- [ ] **Step 4: Commit**

```bash
git add config/services.php .env.example
git commit -m "chore: add Anthropic API config"
```

---

### Task 3: `QuestionClassifier` service — TDD, prompt building

**Files:**
- Create: `app/Services/QuestionClassifier.php`
- Create: `tests/Unit/Services/QuestionClassifierTest.php`

This task covers prompt construction only. Response parsing is Task 4. HTTP is Task 5.

- [ ] **Step 1: Write the failing test for prompt building**

Create `tests/Unit/Services/QuestionClassifierTest.php`:

```php
<?php

use App\Models\Module;
use App\Models\Question;
use App\Services\QuestionClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds a user prompt including question text + explanation + optional quote + source', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $question = Question::factory()->for($module)->create([
        'text' => 'Was beschreibt der BSI-Standard 200-1?',
        'explanation' => 'BSI-Standard 200-1 beschreibt die Anforderungen an ein ISMS.',
        'quote' => 'Siehe Kapitel 2.',
        'source' => 'BSI-Standard 200-1, Kapitel 2, S. 8',
    ]);

    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');
    $prompt = $classifier->buildUserPrompt($question);

    expect($prompt)
        ->toContain('Was beschreibt der BSI-Standard 200-1?')
        ->toContain('BSI-Standard 200-1 beschreibt die Anforderungen')
        ->toContain('Zitat: Siehe Kapitel 2.')
        ->toContain('Quelle: BSI-Standard 200-1, Kapitel 2, S. 8');
});

it('omits quote and source sections when they are null', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $question = Question::factory()->for($module)->create([
        'text' => 'Minimal question?',
        'explanation' => 'Minimal explanation.',
        'quote' => null,
        'source' => null,
    ]);

    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');
    $prompt = $classifier->buildUserPrompt($question);

    expect($prompt)
        ->toContain('Minimal question?')
        ->toContain('Minimal explanation.')
        ->not->toContain('Zitat:')
        ->not->toContain('Quelle:');
});
```

The test file should live under `tests/Unit/Services/` — create the directory if it doesn't exist. Unit tests are OK for this service class even though it will later talk to HTTP — the prompt-building part is pure logic.

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=QuestionClassifierTest
```

Expected: FAIL with `Class "App\Services\QuestionClassifier" not found` (or similar).

- [ ] **Step 3: Implement the classifier skeleton with `buildUserPrompt`**

Create `app/Services/QuestionClassifier.php`:

```php
<?php

namespace App\Services;

use App\Models\Question;

class QuestionClassifier
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $baseUrl = 'https://api.anthropic.com/v1',
    ) {
    }

    public function buildUserPrompt(Question $question): string
    {
        $parts = [
            'Frage: '.$question->text,
            '',
            'Erklärung: '.$question->explanation,
        ];

        if ($question->quote !== null) {
            $parts[] = '';
            $parts[] = 'Zitat: '.$question->quote;
        }

        if ($question->source !== null) {
            $parts[] = '';
            $parts[] = 'Quelle: '.$question->source;
        }

        return implode("\n", $parts);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

```bash
php artisan test --compact --filter=QuestionClassifierTest
```

Expected: 2 passing.

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add QuestionClassifier service with prompt builder"
```

---

### Task 4: `QuestionClassifier::parseResponse`

**Files:**
- Modify: `app/Services/QuestionClassifier.php`
- Modify: `tests/Unit/Services/QuestionClassifierTest.php`

- [ ] **Step 1: Append failing tests for parseResponse**

Append to `tests/Unit/Services/QuestionClassifierTest.php`:

```php
use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;

it('parses a valid JSON response into enum values', function () {
    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');

    $result = $classifier->parseResponse('{"topic":"bausteine","difficulty":"basis"}');

    expect($result)->toBe([
        'topic' => BsiTopic::Bausteine,
        'difficulty' => QuestionDifficulty::Basis,
    ]);
});

it('tolerates surrounding whitespace', function () {
    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');

    $result = $classifier->parseResponse('  {"topic":"methodik","difficulty":"experte"}  ');

    expect($result['topic'])->toBe(BsiTopic::Methodik)
        ->and($result['difficulty'])->toBe(QuestionDifficulty::Experte);
});

it('returns null for invalid JSON', function () {
    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');

    expect($classifier->parseResponse('not json'))->toBeNull()
        ->and($classifier->parseResponse(''))->toBeNull()
        ->and($classifier->parseResponse(null))->toBeNull();
});

it('returns null for unknown enum values', function () {
    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');

    expect($classifier->parseResponse('{"topic":"quantum","difficulty":"basis"}'))->toBeNull()
        ->and($classifier->parseResponse('{"topic":"bausteine","difficulty":"nightmare"}'))->toBeNull();
});

it('returns null when required keys are missing', function () {
    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');

    expect($classifier->parseResponse('{"topic":"bausteine"}'))->toBeNull()
        ->and($classifier->parseResponse('{"difficulty":"basis"}'))->toBeNull()
        ->and($classifier->parseResponse('{}'))->toBeNull();
});
```

- [ ] **Step 2: Run tests to verify new ones fail**

```bash
php artisan test --compact --filter=QuestionClassifierTest
```

Expected: 2 passing (from Task 3) + 5 failing (`parseResponse` not implemented).

- [ ] **Step 3: Implement `parseResponse`**

Add to `app/Services/QuestionClassifier.php`:

```php
use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
```

And inside the class, after `buildUserPrompt`:

```php
/**
 * @return array{topic: BsiTopic, difficulty: QuestionDifficulty}|null
 */
public function parseResponse(?string $text): ?array
{
    if ($text === null || trim($text) === '') {
        return null;
    }

    $decoded = json_decode(trim($text), true);

    if (! is_array($decoded)) {
        return null;
    }

    if (! isset($decoded['topic']) || ! isset($decoded['difficulty'])) {
        return null;
    }

    $topic = BsiTopic::tryFrom($decoded['topic']);
    $difficulty = QuestionDifficulty::tryFrom($decoded['difficulty']);

    if ($topic === null || $difficulty === null) {
        return null;
    }

    return ['topic' => $topic, 'difficulty' => $difficulty];
}
```

- [ ] **Step 4: Run tests to verify all pass**

```bash
php artisan test --compact --filter=QuestionClassifierTest
```

Expected: 7 passing.

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: QuestionClassifier parses Claude responses into enum values"
```

---

### Task 5: `QuestionClassifier::classify` — actual HTTP call with prompt caching

**Files:**
- Modify: `app/Services/QuestionClassifier.php`
- Modify: `tests/Unit/Services/QuestionClassifierTest.php`

- [ ] **Step 1: Append failing tests for `classify` using `Http::fake()`**

Append to `tests/Unit/Services/QuestionClassifierTest.php`:

```php
use Illuminate\Support\Facades\Http;

it('posts to the Anthropic Messages API with system prompt + user prompt + cache control', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => '{"topic":"bausteine","difficulty":"basis"}']],
        ]),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $question = Question::factory()->for($module)->create([
        'text' => 'Q?',
        'explanation' => 'E.',
    ]);

    $classifier = new QuestionClassifier(apiKey: 'sk-test', model: 'test-model');
    $result = $classifier->classify($question);

    expect($result['topic'])->toBe(BsiTopic::Bausteine);

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->url() === 'https://api.anthropic.com/v1/messages'
            && $request->hasHeader('x-api-key', 'sk-test')
            && $request->hasHeader('anthropic-version', '2023-06-01')
            && $body['model'] === 'test-model'
            && $body['max_tokens'] === 100
            && $body['temperature'] === 0
            && is_array($body['system'])
            && $body['system'][0]['cache_control'] === ['type' => 'ephemeral']
            && str_contains($body['system'][0]['text'], 'bausteine')
            && $body['messages'][0]['role'] === 'user'
            && str_contains($body['messages'][0]['content'], 'Q?');
    });
});

it('returns null when the API call fails', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response(status: 500),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $question = Question::factory()->for($module)->create();

    $classifier = new QuestionClassifier(apiKey: 'sk-test', model: 'test-model');

    expect($classifier->classify($question))->toBeNull();
});

it('returns null when the response text cannot be parsed', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => 'nope, not json']],
        ]),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $question = Question::factory()->for($module)->create();

    $classifier = new QuestionClassifier(apiKey: 'sk-test', model: 'test-model');

    expect($classifier->classify($question))->toBeNull();
});
```

- [ ] **Step 2: Run tests to verify failures**

```bash
php artisan test --compact --filter=QuestionClassifierTest
```

Expected: 7 passing (from Tasks 3–4) + 3 failing (`classify` not implemented).

- [ ] **Step 3: Implement `classify` + system prompt constant**

Add to `app/Services/QuestionClassifier.php`:

```php
use Illuminate\Support\Facades\Http;
```

And inside the class:

```php
private const SYSTEM_PROMPT = <<<'TEXT'
You classify German BSI IT-Grundschutz-Praktiker exam questions by curriculum topic and difficulty.

Topics (choose exactly one):
- methodik: IT-Grundschutz methodology, process phases, planning
- bausteine: specific Bausteine (SYS.x, APP.x, NET.x, INF.x, OPS.x, ORP.x, CON.x, IND.x) and their application
- risikoanalyse: risk analysis methodology, BSI-Standard 200-3
- modellierung: modeling, scope definition, asset mapping, Informationsverbund
- check: IT-Grundschutz-Check, audit mechanics, Grundschutz-Test-Kriterien
- standards: BSI-Standards 100-x / 200-x themselves, certification framework, legal/compliance context
- notfall: Notfallmanagement, BCM, recovery strategies, BSI-Standard 200-4
- siem: SIEM, monitoring, logging, SOC patterns

Difficulty (choose exactly one):
- basis: fundamental concepts, definitions, basic methodology
- experte: edge cases, cross-cutting scenarios, advanced application, detailed analysis

Respond with valid JSON only: {"topic":"<value>","difficulty":"<value>"}
No explanation, no preamble, no markdown fences.
TEXT;

/**
 * @return array{topic: BsiTopic, difficulty: QuestionDifficulty}|null
 */
public function classify(Question $question): ?array
{
    $response = Http::withHeaders([
        'x-api-key' => $this->apiKey,
        'anthropic-version' => '2023-06-01',
        'content-type' => 'application/json',
    ])->post($this->baseUrl.'/messages', [
        'model' => $this->model,
        'max_tokens' => 100,
        'temperature' => 0,
        'system' => [
            [
                'type' => 'text',
                'text' => self::SYSTEM_PROMPT,
                'cache_control' => ['type' => 'ephemeral'],
            ],
        ],
        'messages' => [
            ['role' => 'user', 'content' => $this->buildUserPrompt($question)],
        ],
    ]);

    if (! $response->successful()) {
        return null;
    }

    return $this->parseResponse($response->json('content.0.text'));
}
```

The `cache_control: ephemeral` on the system prompt enables Anthropic prompt caching (5-minute TTL). For a 165-question batch run, calls 2–165 will hit the cache — roughly 90 % of the input tokens become cache reads (10× cheaper than regular input tokens).

- [ ] **Step 4: Run tests to verify all pass**

```bash
php artisan test --compact --filter=QuestionClassifierTest
```

Expected: 10 passing.

Also run the full suite:

```bash
php artisan test --compact
```

Expected: 97 passing (87 prior + 10 new).

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: QuestionClassifier calls Anthropic API with prompt caching"
```

---

### Task 6: `ClassifyQuestions` Artisan command

**Files:**
- Create: `app/Console/Commands/ClassifyQuestions.php`
- Create: `tests/Feature/Console/ClassifyQuestionsTest.php`

- [ ] **Step 1: Write the failing command test with mocked HTTP**

Create `tests/Feature/Console/ClassifyQuestionsTest.php`:

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.anthropic.api_key', 'sk-test');
    config()->set('services.anthropic.model', 'test-model');
});

it('classifies all untagged questions by default', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::sequence()
            ->push(['content' => [['type' => 'text', 'text' => '{"topic":"bausteine","difficulty":"basis"}']]])
            ->push(['content' => [['type' => 'text', 'text' => '{"topic":"methodik","difficulty":"experte"}']]]),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $q1 = Question::factory()->for($module)->create();
    $q2 = Question::factory()->for($module)->create();
    $q3 = Question::factory()->for($module)->create([
        'topic' => BsiTopic::Check,
        'difficulty' => QuestionDifficulty::Basis,
    ]);

    $this->artisan('questions:classify')
        ->expectsOutput('Classifying 2 questions…')
        ->assertExitCode(0);

    expect($q1->fresh()->topic)->toBe(BsiTopic::Bausteine);
    expect($q2->fresh()->difficulty)->toBe(QuestionDifficulty::Experte);
    expect($q3->fresh()->topic)->toBe(BsiTopic::Check); // unchanged
});

it('with --force reclassifies already-tagged questions', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => '{"topic":"standards","difficulty":"experte"}']],
        ]),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $question = Question::factory()->for($module)->create([
        'topic' => BsiTopic::Check,
        'difficulty' => QuestionDifficulty::Basis,
    ]);

    $this->artisan('questions:classify', ['--force' => true])
        ->assertExitCode(0);

    expect($question->fresh()->topic)->toBe(BsiTopic::Standards)
        ->and($question->fresh()->difficulty)->toBe(QuestionDifficulty::Experte);
});

it('with --limit processes only N questions', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => '{"topic":"bausteine","difficulty":"basis"}']],
        ]),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()->for($module)->count(5)->create();

    $this->artisan('questions:classify', ['--limit' => 2])
        ->assertExitCode(0);

    expect(Question::whereNotNull('topic')->count())->toBe(2);
    expect(Question::whereNull('topic')->count())->toBe(3);
});

it('skips questions when the API returns unparseable output', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => 'garbage']],
        ]),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()->for($module)->create();

    $this->artisan('questions:classify')
        ->expectsOutputToContain('Errors: 1')
        ->assertExitCode(0);

    expect(Question::whereNull('topic')->count())->toBe(1);
});

it('fails fast when no API key is configured', function () {
    config()->set('services.anthropic.api_key', null);

    $this->artisan('questions:classify')
        ->expectsOutputToContain('ANTHROPIC_API_KEY is not set')
        ->assertExitCode(1);
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --compact --filter=ClassifyQuestionsTest
```

Expected: FAIL with `Command "questions:classify" is not defined`.

- [ ] **Step 3: Create the command**

```bash
php artisan make:command ClassifyQuestions --no-interaction
```

Replace contents of `app/Console/Commands/ClassifyQuestions.php` with:

```php
<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\QuestionClassifier;
use Illuminate\Console\Command;

class ClassifyQuestions extends Command
{
    protected $signature = 'questions:classify
        {--force : Reclassify questions that already have topic+difficulty set}
        {--limit= : Process at most N questions (useful for dry runs)}';

    protected $description = 'Classify questions by BSI topic + difficulty via Claude Haiku';

    public function handle(): int
    {
        $apiKey = config('services.anthropic.api_key');

        if (empty($apiKey)) {
            $this->error('ANTHROPIC_API_KEY is not set. Add it to your .env before running this command.');

            return self::FAILURE;
        }

        $classifier = new QuestionClassifier(
            apiKey: $apiKey,
            model: config('services.anthropic.model'),
            baseUrl: config('services.anthropic.base_url'),
        );

        $query = Question::query();

        if (! $this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('topic')->orWhereNull('difficulty');
            });
        }

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $questions = $query->get();

        $this->info("Classifying {$questions->count()} questions…");

        if ($questions->isEmpty()) {
            $this->info('Nothing to do.');

            return self::SUCCESS;
        }

        $tagged = 0;
        $errors = 0;
        $bar = $this->output->createProgressBar($questions->count());
        $bar->start();

        foreach ($questions as $question) {
            $result = $classifier->classify($question);

            if ($result === null) {
                $errors++;
                $this->output->isVerbose() && $this->line("  ERR  question #{$question->id}");
            } else {
                $question->update([
                    'topic' => $result['topic'],
                    'difficulty' => $result['difficulty'],
                ]);
                $tagged++;
                $this->output->isVerbose() && $this->line("  OK   #{$question->id} → {$result['topic']->value} / {$result['difficulty']->value}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Tagged: {$tagged}");
        $this->info("Errors: {$errors}");

        return self::SUCCESS;
    }
}
```

Laravel auto-discovers commands in `app/Console/Commands/`. No manual registration needed.

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test --compact --filter=ClassifyQuestionsTest
```

Expected: 5 passing.

Also run the full suite:

```bash
php artisan test --compact
```

Expected: 102 passing (97 + 5 new).

- [ ] **Step 5: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add questions:classify Artisan command"
```

---

### Task 7: Provision API key + dry run (5 questions)

This task involves a human-intervention step: the developer must paste their `ANTHROPIC_API_KEY` into `.env`. Subagents CANNOT do this — they must stop and ask the human.

- [ ] **Step 1: Set `ANTHROPIC_API_KEY` in `.env`**

If running under a subagent: STOP here. Report BLOCKED with status `NEEDS_CONTEXT` and the message: "Human must set ANTHROPIC_API_KEY in /Users/david/Workspace/pruefungstrainer/.env before Task 7 can proceed."

If running as the human controller: open `.env`, add the line:

```
ANTHROPIC_API_KEY=sk-ant-api03-...
```

with your actual key. Save.

- [ ] **Step 2: Confirm the key loads**

```bash
php artisan config:show services.anthropic
```

Expected: `api_key: sk-ant-api03-...` (or whatever your key is — should NOT be null).

- [ ] **Step 3: Pre-flight — verify counts**

```bash
php artisan tinker --execute 'echo "Total: ".App\Models\Question::count().PHP_EOL; echo "Untagged: ".App\Models\Question::whereNull("topic")->orWhereNull("difficulty")->count().PHP_EOL;'
```

Expected: Total 165, Untagged 165.

- [ ] **Step 4: Dry run 5 questions**

```bash
php artisan questions:classify --limit=5 -v
```

Expected output: progress bar + 5 lines of `OK #<id> → <topic> / <difficulty>` + summary `Tagged: 5`, `Errors: 0`.

If `Errors: > 0`, inspect which questions failed (they stay untagged in DB — re-run with `-v` to see the IDs). Common causes:

- Claude returned JSON with commentary instead of raw JSON → the system prompt may need tightening. Report before continuing.
- API rate limit / transient failure → re-run, usually recovers.

- [ ] **Step 5: Inspect the 5 classifications**

```bash
php artisan tinker --execute 'foreach (App\Models\Question::whereNotNull("topic")->limit(5)->get(["id","topic","difficulty","text"]) as $q) { echo "#$q->id [".$q->topic->value."/".$q->difficulty->value."] ".substr($q->text, 0, 80)."...".PHP_EOL; }'
```

Read the output. Does each classification look reasonable given the question text? E.g., a question about SYS.1.1 should be `bausteine`, a question about the ISMS process phases should be `methodik`.

**If 4 of 5 look right:** acceptable — full run can proceed. Classification is heuristic; some borderline questions are judgment calls.

**If ≤ 2 of 5 look right:** stop. The system prompt likely needs more concrete examples. Add a section with 2–3 worked examples before the JSON instruction. Iterate.

- [ ] **Step 6: Commit the dry-run results (even though they're DB state, not code)**

No code changed. No commit needed here. Just note the result in a git-friendly log:

```bash
git commit --allow-empty -m "chore: dry-run tagging — 5 questions classified"
```

---

### Task 8: Full run — classify all remaining untagged questions

Human-intervention task, same as Task 7.

- [ ] **Step 1: Full run**

```bash
php artisan questions:classify -v
```

Expected: progress bar → ~160 `OK` lines → summary `Tagged: ~160`, `Errors: <5`.

Total runtime: ~2–5 minutes (depends on API latency). Total cost with prompt caching: roughly €0.05–0.20.

- [ ] **Step 2: Verify full tagging**

```bash
php artisan tinker --execute 'echo "Total: ".App\Models\Question::count().PHP_EOL; echo "Tagged: ".App\Models\Question::whereNotNull("topic")->whereNotNull("difficulty")->count().PHP_EOL; echo "Untagged: ".App\Models\Question::where(fn(\$q)=>\$q->whereNull("topic")->orWhereNull("difficulty"))->count().PHP_EOL;'
```

Expected: Total 165, Tagged ≈ 163–165 (rare parse errors may leave 1–2 untagged), Untagged ≈ 0–2.

- [ ] **Step 3: Retry remaining untagged**

If any questions are still untagged after the full run (parse errors, transient API failures), just re-run:

```bash
php artisan questions:classify -v
```

The command only processes untagged questions by default, so re-runs are idempotent.

If the same questions fail twice, inspect them manually — the text may be unusual:

```bash
php artisan tinker --execute 'foreach (App\Models\Question::whereNull("topic")->orWhereNull("difficulty")->get(["id","text"]) as $q) { echo "#$q->id: ".substr($q->text, 0, 100).PHP_EOL; }'
```

Worst case: classify those 1–2 by hand via tinker:

```bash
php artisan tinker --execute 'App\Models\Question::find(123)->update(["topic" => App\Enums\BsiTopic::Bausteine, "difficulty" => App\Enums\QuestionDifficulty::Basis]);'
```

- [ ] **Step 4: Check distribution**

```bash
php artisan tinker --execute 'foreach (App\Enums\BsiTopic::cases() as $t) { echo $t->value.": ".App\Models\Question::where("topic", $t)->count().PHP_EOL; } echo "---".PHP_EOL; foreach (App\Enums\QuestionDifficulty::cases() as $d) { echo $d->value.": ".App\Models\Question::where("difficulty", $d)->count().PHP_EOL; }'
```

Expected rough shape:
- Topics: `bausteine` likely dominant (~60 questions), others smaller. No topic should have zero. If any topic has zero, consider whether the classification prompt needs refinement — but a zero is acceptable if the question pool genuinely doesn't cover that topic.
- Difficulty: targeting roughly 75/25 (Basis ~120, Experte ~40). Don't panic if it's 80/20 or 70/30 — Claude's heuristic won't match BSI's internal split exactly. Log the actual distribution.

Record the actual distribution.

- [ ] **Step 5: Commit marker**

```bash
git commit --allow-empty -m "chore: full tagging pass — <N> tagged, <M> errors, distribution: basis=<X> / experte=<Y>"
```

Fill in the actual numbers.

---

### Task 9: Human spot-check + final verification

Human-intervention task.

- [ ] **Step 1: Spot-check 10 random classifications**

```bash
php artisan tinker --execute 'foreach (App\Models\Question::inRandomOrder()->limit(10)->get(["id","topic","difficulty","text"]) as $q) { echo "#$q->id [".$q->topic->value."/".$q->difficulty->value."] ".substr($q->text, 0, 100)."...".PHP_EOL; }'
```

Read the 10 lines. For each:
- Does the topic match what the question is really about?
- Is the difficulty plausible (basic definition vs edge case)?

Acceptance: at least 7 of 10 feel reasonable. Classification is heuristic, not ground truth — some judgment calls are expected.

- [ ] **Step 2: Manual corrections (if any)**

For obvious miscategorizations found in Step 1, correct in tinker:

```bash
php artisan tinker --execute 'App\Models\Question::find(<id>)->update(["topic" => App\Enums\BsiTopic::Methodik]);'
```

Replace `<id>` and the enum value per your judgment.

- [ ] **Step 3: Full test suite**

```bash
php artisan test --compact
```

Expected: all tests still pass (102 — no code changed since Task 6, only DB state).

- [ ] **Step 4: Full project Pint**

```bash
vendor/bin/pint --format agent
```

Expected: clean. If anything formats, commit.

- [ ] **Step 5: Frontend build**

```bash
npm run build
```

Expected: succeeds (no frontend changes this plan, but good hygiene).

- [ ] **Step 6: Completion marker + branch status**

```bash
git commit --allow-empty -m "chore: question tagging complete"
git log --oneline -15
```

- [ ] **Step 7: Do NOT merge**

Leave the branch `feat/question-tagging-pipeline` for separate merge via the `finishing-a-development-branch` skill.

---

## Self-Review

**Spec coverage:**
- ✅ Claude-assisted classification of all questions: Tasks 5 (service) + 6 (command) + 8 (full run)
- ✅ `topic` populated to `BsiTopic` enum value: Task 4 parseResponse casts; Task 6 command persists
- ✅ `difficulty` populated to `QuestionDifficulty` enum value: same
- ✅ Prompt caching: Task 5 `cache_control: ephemeral` on system prompt
- ✅ Invoked once, idempotent on re-runs (only untagged by default): Task 6 query logic
- ✅ `--force` to reclassify everything: Task 6 option + tested
- ✅ `--limit` for dry runs: Task 6 option + tested
- ✅ Graceful handling of API errors + parse failures: Task 5 returns null + Task 6 increments `errors` counter
- ✅ Missing API key = helpful error, not crash: Task 6 pre-flight + test

**Placeholder scan:** No "TODO" / "TBD" / "handle edge cases" / "add appropriate validation" language. Every step has actual code or actual commands.

**Type consistency:**
- `QuestionClassifier::classify()` returns `array{topic: BsiTopic, difficulty: QuestionDifficulty}|null` — matches Task 4's `parseResponse()` and Task 6's usage (`$result['topic']` / `$result['difficulty']`).
- Constructor args `apiKey`, `model`, `baseUrl` consistent across Tasks 3, 4, 5, 6.
- `config('services.anthropic.api_key')` / `model` / `base_url` keys consistent with Task 2's config block.
- `BsiTopic::tryFrom('bausteine')` etc. uses the enum string values from Plan 2 Task 2 — stable.

**Human-intervention markers:** Tasks 7, 8, 9 explicitly call out that they need the human (`ANTHROPIC_API_KEY` in `.env`). If a subagent reaches Task 7 without the key, it should report BLOCKED / NEEDS_CONTEXT rather than stub or guess.

**What the plan deliberately does NOT include:**
- No batch API (overkill for 165 questions).
- No async queue job (sync is fast enough).
- No retry/backoff logic (transient failures handled by re-running the command).
- No rate-limit sleep (Anthropic's Haiku tier handles 165 sequential calls without throttling).
- No web UI for reviewing classifications (tinker is enough; adding UI for one-time content work is YAGNI).
- No logging of every classification to a file (verbose mode + DB are enough).

---

## Execution Handoff

Plan complete and saved to `docs/superpowers/plans/2026-04-14-question-tagging-pipeline.md`. Two execution options:

1. **Subagent-Driven (recommended)** — fresh subagent per task. Note that Tasks 7–9 require the human to set `ANTHROPIC_API_KEY` in `.env` and run the command; subagent-driven execution will halt at Task 7 so the human can step in. Same pattern has worked for prior plans.

2. **Inline Execution** — single session, I execute tasks with checkpoints.

Which approach?
