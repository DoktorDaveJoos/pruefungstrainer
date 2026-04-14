# Data Model & Seed Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stand up the persistent data layer for the BSI IT-Grundschutz-Praktiker exam simulator — questions, answers, exam attempts + answers, practice-mode answers, and payment status on users — and seed ~220 real BSI questions from the existing de-dsb-learner repo. After this plan lands, the schema is ready for tagging (Plan 3), exam flow (Plan 4), Freies Lernen (Plan 5), and checkout (Plan 6).

**Architecture:** Seven migrations, five new models (`Module`, `Question`, `Answer`, `ExamAttempt`, `ExamAnswer`, `PracticeAnswer`), two enums (`BsiTopic`, `QuestionDifficulty`), factories for all new models, and four ported question seeders. Columns `questions.topic` and `questions.difficulty` ship nullable — Plan 3 populates them via Claude classification. Anonymous exam attempts are supported via `exam_attempts.session_uuid`, claimed at checkout by Plan 6's webhook. Practice mode is user-scoped (paid-only), separate from exam tracking so studying doesn't consume exam-freshness budget.

**Tech Stack:** Laravel 13, Pest 4, PHP 8.4. Source data: `/Users/david/Workspace/de-dsb-learner/database/seeders/*.php`.

## Subsequent plans (not in scope here)

1. **Plan 3 — Question tagging pipeline:** Claude-assisted pass classifying all ~220 questions into 8 `BsiTopic` values + `QuestionDifficulty` (Basis/Experte). Writes via a console command.
2. **Plan 4 — Exam simulation flow:** Anonymous session cookie, server-authoritative 60-min timer, 50-question multi-select with all-or-nothing scoring, flag-for-review, auto-submit, results screen with locked preview.
3. **Plan 5 — Freies Lernen flow:** Per-question forgiving feedback loop, wrong-only toggle, progress strip.
4. **Plan 6 — Checkout & Polar integration:** Paywall UI, Polar checkout session, webhook claims anonymous attempt via `session_uuid`, `user.paid_at` gate.
5. **Plan 7 — Landing + legal:** Public landing page, AGB / Datenschutz / Impressum, post-login dashboard.

---

## File Structure

### Migrations to CREATE

```
database/migrations/YYYY_MM_DD_HHMMSS_create_modules_table.php
database/migrations/YYYY_MM_DD_HHMMSS_create_questions_table.php
database/migrations/YYYY_MM_DD_HHMMSS_create_answers_table.php
database/migrations/YYYY_MM_DD_HHMMSS_create_exam_attempts_table.php
database/migrations/YYYY_MM_DD_HHMMSS_create_exam_answers_table.php
database/migrations/YYYY_MM_DD_HHMMSS_create_practice_answers_table.php
database/migrations/YYYY_MM_DD_HHMMSS_add_paid_at_to_users_table.php
```

### Models to CREATE

```
app/Models/Module.php                  # belongsTo-less root; hasMany Questions
app/Models/Question.php                # belongsTo Module; hasMany Answers; topic/difficulty enums
app/Models/Answer.php                  # belongsTo Question; is_correct boolean
app/Models/ExamAttempt.php             # belongsTo User (nullable); hasMany ExamAnswers; session_uuid for anon
app/Models/ExamAnswer.php              # belongsTo ExamAttempt + Question; selected_option_ids JSON
app/Models/PracticeAnswer.php          # belongsTo User + Question; lightweight per-interaction row
```

### Enums to CREATE

```
app/Enums/BsiTopic.php                 # 8 cases, backed string
app/Enums/QuestionDifficulty.php       # 2 cases (Basis, Experte), backed string
```

### Factories to CREATE

```
database/factories/ModuleFactory.php
database/factories/QuestionFactory.php
database/factories/AnswerFactory.php
database/factories/ExamAttemptFactory.php
database/factories/ExamAnswerFactory.php
database/factories/PracticeAnswerFactory.php
```

### Seeders to CREATE

```
database/seeders/ModuleSeeder.php
database/seeders/BsiStandard2001QuestionsSeeder.php       # port from de-dsb-learner
database/seeders/BsiStandard2002QuestionsSeeder.php       # port from de-dsb-learner
database/seeders/BsiStandard2003QuestionsSeeder.php       # port from de-dsb-learner
database/seeders/ItGrundschutzKompendiumQuestionsSeeder.php  # port from de-dsb-learner
```

### Files to MODIFY

```
database/seeders/DatabaseSeeder.php    # wire up ModuleSeeder + question seeders
app/Models/User.php                    # add paid_at to cast + hasMany ExamAttempts + PracticeAnswers
```

### Tests to CREATE

```
tests/Feature/Models/ModuleTest.php
tests/Feature/Models/QuestionTest.php
tests/Feature/Models/AnswerTest.php
tests/Feature/Models/ExamAttemptTest.php
tests/Feature/Models/ExamAnswerTest.php
tests/Feature/Models/PracticeAnswerTest.php
tests/Feature/Models/UserPaidAtTest.php
tests/Feature/SeedersTest.php
```

### Data

Source: `/Users/david/Workspace/de-dsb-learner/database/seeders/*.php` — **four BSI seeders only** (skip `SampleQuestionsSeeder.php` which is dev-only scratch data).

---

## Tasks

### Task 1: Baseline + branch

**Files:** none (setup only)

- [ ] **Step 1: Verify clean main**

```bash
git status
```

Expected: `working tree clean` on branch `main`.

- [ ] **Step 2: Run baseline tests**

```bash
php artisan test --compact
```

Expected: all 40 tests pass.

- [ ] **Step 3: Create feature branch**

```bash
git checkout -b feat/data-model-and-seed
git commit --allow-empty -m "chore: baseline before data-model + seed work"
```

---

### Task 2: Create `BsiTopic` enum

**Files:**
- Create: `app/Enums/BsiTopic.php`
- Test: `tests/Feature/Models/ModuleTest.php` will cover enum integration later — this task has its own unit-level test

- [ ] **Step 1: Create enum file**

Use `php artisan make:enum BsiTopic --string` if that generator exists; otherwise write the file directly:

```php
<?php

namespace App\Enums;

enum BsiTopic: string
{
    case Methodik = 'methodik';
    case Bausteine = 'bausteine';
    case Risikoanalyse = 'risikoanalyse';
    case Modellierung = 'modellierung';
    case Check = 'check';
    case Standards = 'standards';
    case Notfallmanagement = 'notfall';
    case Siem = 'siem';

    public function label(): string
    {
        return match ($this) {
            self::Methodik => 'IT-Grundschutz-Methodik',
            self::Bausteine => 'Bausteine',
            self::Risikoanalyse => 'Risikoanalyse',
            self::Modellierung => 'Modellierung',
            self::Check => 'IT-Grundschutz-Check',
            self::Standards => 'BSI-Standards',
            self::Notfallmanagement => 'Notfallmanagement',
            self::Siem => 'SIEM / Monitoring',
        };
    }
}
```

These 8 cases map to the BSI IT-Grundschutz curriculum topic areas used on the results-screen topic breakdown. The exact string values are stable — they become persisted DB values.

- [ ] **Step 2: Create Pest test**

Create `tests/Feature/Enums/BsiTopicTest.php`:

```php
<?php

use App\Enums\BsiTopic;

it('has exactly 8 cases', function () {
    expect(BsiTopic::cases())->toHaveCount(8);
});

it('exposes a German label for each case', function (BsiTopic $topic) {
    expect($topic->label())->toBeString()->not->toBeEmpty();
})->with(BsiTopic::cases());

it('uses stable string backing values', function () {
    $expected = ['methodik', 'bausteine', 'risikoanalyse', 'modellierung', 'check', 'standards', 'notfall', 'siem'];
    expect(array_map(fn (BsiTopic $t) => $t->value, BsiTopic::cases()))->toBe($expected);
});
```

- [ ] **Step 3: Run tests**

```bash
php artisan test --compact --filter=BsiTopicTest
```

Expected: 10 passing (8 dataset cases + 2 non-dataset).

- [ ] **Step 4: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Enums/BsiTopic.php tests/Feature/Enums/BsiTopicTest.php
git commit -m "feat: add BsiTopic enum (8 BSI curriculum areas)"
```

---

### Task 3: Create `QuestionDifficulty` enum

**Files:**
- Create: `app/Enums/QuestionDifficulty.php`
- Test: `tests/Feature/Enums/QuestionDifficultyTest.php`

- [ ] **Step 1: Create enum**

Create `app/Enums/QuestionDifficulty.php`:

```php
<?php

namespace App\Enums;

enum QuestionDifficulty: string
{
    case Basis = 'basis';
    case Experte = 'experte';

    public function label(): string
    {
        return match ($this) {
            self::Basis => 'Basis',
            self::Experte => 'Experte',
        };
    }
}
```

BSI's official split is 75 % Basis / 25 % Experte per exam. We'll use this enum when populating the tags in Plan 3 and when drawing exam questions in Plan 4.

- [ ] **Step 2: Create test**

Create `tests/Feature/Enums/QuestionDifficultyTest.php`:

```php
<?php

use App\Enums\QuestionDifficulty;

it('has exactly 2 cases', function () {
    expect(QuestionDifficulty::cases())->toHaveCount(2);
});

it('maps Basis → basis and Experte → experte', function () {
    expect(QuestionDifficulty::Basis->value)->toBe('basis');
    expect(QuestionDifficulty::Experte->value)->toBe('experte');
});

it('exposes a German label for each case', function () {
    expect(QuestionDifficulty::Basis->label())->toBe('Basis');
    expect(QuestionDifficulty::Experte->label())->toBe('Experte');
});
```

- [ ] **Step 3: Run tests**

```bash
php artisan test --compact --filter=QuestionDifficultyTest
```

Expected: 3 passing.

- [ ] **Step 4: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Enums/QuestionDifficulty.php tests/Feature/Enums/QuestionDifficultyTest.php
git commit -m "feat: add QuestionDifficulty enum (Basis / Experte)"
```

---

### Task 4: `modules` table + `Module` model + factory + test

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_modules_table.php`
- Create: `app/Models/Module.php`
- Create: `database/factories/ModuleFactory.php`
- Create: `tests/Feature/Models/ModuleTest.php`

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration create_modules_table --create=modules --no-interaction
```

Replace the generated file contents with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
```

- [ ] **Step 2: Create `Module` model**

Create `app/Models/Module.php`:

```php
<?php

namespace App\Models;

use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description'])]
class Module extends Model
{
    /** @use HasFactory<ModuleFactory> */
    use HasFactory;

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
```

Note: `Question` model is defined in Task 5 — the relation method can reference it before the class exists because Laravel resolves it lazily.

- [ ] **Step 3: Create factory**

Create `database/factories/ModuleFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{
    protected $model = Module::class;

    public function definition(): array
    {
        $name = fake()->unique()->sentence(3);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->paragraph(),
        ];
    }
}
```

- [ ] **Step 4: Create test**

Create `tests/Feature/Models/ModuleTest.php`:

```php
<?php

use App\Models\Module;

it('creates a module via factory', function () {
    $module = Module::factory()->create();

    expect($module)->toBeInstanceOf(Module::class)
        ->and($module->name)->not->toBeEmpty()
        ->and($module->slug)->not->toBeEmpty();
});

it('enforces unique slugs', function () {
    Module::factory()->create(['slug' => 'duplicate-slug']);

    expect(fn () => Module::factory()->create(['slug' => 'duplicate-slug']))
        ->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

it('resolves route key by slug', function () {
    $module = Module::factory()->create();

    expect($module->getRouteKeyName())->toBe('slug');
});
```

- [ ] **Step 5: Run migration + tests**

```bash
php artisan migrate
php artisan test --compact --filter=ModuleTest
```

Expected: migration runs cleanly; 3 tests pass.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add Module model + migration + factory"
```

---

### Task 5: `questions` table + `Question` model + factory + test

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_questions_table.php`
- Create: `app/Models/Question.php`
- Create: `database/factories/QuestionFactory.php`
- Create: `tests/Feature/Models/QuestionTest.php`

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration create_questions_table --create=questions --no-interaction
```

Replace contents:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->text('explanation');
            $table->text('quote')->nullable();
            $table->string('source')->nullable();
            $table->string('topic')->nullable()->index();
            $table->string('difficulty')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
```

`topic` and `difficulty` are nullable because Plan 3 populates them. Indexed for exam-draw queries.

- [ ] **Step 2: Create model**

Create `app/Models/Question.php`:

```php
<?php

namespace App\Models;

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['module_id', 'text', 'explanation', 'quote', 'source', 'topic', 'difficulty'])]
class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'topic' => BsiTopic::class,
            'difficulty' => QuestionDifficulty::class,
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
```

- [ ] **Step 3: Create factory**

Create `database/factories/QuestionFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'text' => fake()->sentence(12).'?',
            'explanation' => fake()->paragraph(),
            'quote' => fake()->optional()->sentence(20),
            'source' => fake()->optional()->randomElement([
                'BSI-Standard 200-1, Kapitel 2, S. 8',
                'BSI-Standard 200-2, Kapitel 4, S. 17',
                'IT-Grundschutz-Kompendium, SYS.1.1',
            ]),
            'topic' => null,
            'difficulty' => null,
        ];
    }

    public function tagged(BsiTopic $topic, QuestionDifficulty $difficulty): static
    {
        return $this->state(fn () => [
            'topic' => $topic,
            'difficulty' => $difficulty,
        ]);
    }
}
```

The `tagged()` state is used by Plan 4's tests to seed tagged questions.

- [ ] **Step 4: Create test**

Create `tests/Feature/Models/QuestionTest.php`:

```php
<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\Module;
use App\Models\Question;

it('creates a question via factory', function () {
    $question = Question::factory()->create();

    expect($question)->toBeInstanceOf(Question::class)
        ->and($question->text)->not->toBeEmpty()
        ->and($question->explanation)->not->toBeEmpty();
});

it('belongs to a module', function () {
    $module = Module::factory()->create();
    $question = Question::factory()->for($module)->create();

    expect($question->module)->toBeInstanceOf(Module::class)
        ->and($question->module->id)->toBe($module->id);
});

it('has many answers', function () {
    $question = Question::factory()
        ->has(Answer::factory()->count(4))
        ->create();

    expect($question->answers)->toHaveCount(4);
});

it('casts topic to BsiTopic enum', function () {
    $question = Question::factory()->tagged(BsiTopic::Bausteine, QuestionDifficulty::Basis)->create();

    expect($question->topic)->toBe(BsiTopic::Bausteine)
        ->and($question->difficulty)->toBe(QuestionDifficulty::Basis);
});

it('stores null topic/difficulty when untagged', function () {
    $question = Question::factory()->create();

    expect($question->topic)->toBeNull()
        ->and($question->difficulty)->toBeNull();
});
```

Note: `Answer::factory()` is created in Task 6; this test relies on it. Because task ordering places Task 5 before Task 6, the `it('has many answers')` test will fail until Task 6 lands. Run just the first 2, 4, 5 tests during Task 5 and re-run the full file in Task 6.

Actually: run the full test file in Task 5 — let the "has many answers" test fail, then fix it transparently in Task 6. This is a legitimate use of red-green cycles.

- [ ] **Step 5: Run migration + partial tests**

```bash
php artisan migrate
php artisan test --compact --filter=QuestionTest
```

Expected: migration runs; 4 tests pass, 1 fails (the `has many answers` test — Answer model not yet created). This is expected and resolves in Task 6.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add Question model + migration + factory (answers WIP)"
```

---

### Task 6: `answers` table + `Answer` model + factory + test

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_answers_table.php`
- Create: `app/Models/Answer.php`
- Create: `database/factories/AnswerFactory.php`
- Create: `tests/Feature/Models/AnswerTest.php`

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration create_answers_table --create=answers --no-interaction
```

Replace contents:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
```

- [ ] **Step 2: Create model**

Create `app/Models/Answer.php`:

```php
<?php

namespace App\Models;

use Database\Factories\AnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['question_id', 'text', 'is_correct'])]
class Answer extends Model
{
    /** @use HasFactory<AnswerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
```

- [ ] **Step 3: Create factory**

Create `database/factories/AnswerFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    protected $model = Answer::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'text' => fake()->sentence(8),
            'is_correct' => fake()->boolean(25),
        ];
    }

    public function correct(): static
    {
        return $this->state(fn () => ['is_correct' => true]);
    }

    public function incorrect(): static
    {
        return $this->state(fn () => ['is_correct' => false]);
    }
}
```

25 % true matches the rough density of correct answers in BSI multi-select questions (1–2 correct out of 4).

- [ ] **Step 4: Create test**

Create `tests/Feature/Models/AnswerTest.php`:

```php
<?php

use App\Models\Answer;
use App\Models\Question;

it('creates an answer via factory', function () {
    $answer = Answer::factory()->create();

    expect($answer)->toBeInstanceOf(Answer::class)
        ->and($answer->text)->not->toBeEmpty()
        ->and($answer->is_correct)->toBeBool();
});

it('belongs to a question', function () {
    $question = Question::factory()->create();
    $answer = Answer::factory()->for($question)->create();

    expect($answer->question)->toBeInstanceOf(Question::class)
        ->and($answer->question->id)->toBe($question->id);
});

it('casts is_correct to boolean', function () {
    $correct = Answer::factory()->correct()->create();
    $wrong = Answer::factory()->incorrect()->create();

    expect($correct->is_correct)->toBeTrue()
        ->and($wrong->is_correct)->toBeFalse();
});
```

- [ ] **Step 5: Run migration + tests**

```bash
php artisan migrate
php artisan test --compact --filter='AnswerTest|QuestionTest'
```

Expected: migration runs; all AnswerTest tests pass; ALL QuestionTest tests now pass (including `has many answers` that was failing in Task 5).

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add Answer model + migration + factory"
```

---

### Task 7: Port `ModuleSeeder` + create root module

**Files:**
- Create: `database/seeders/ModuleSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Read the reference seeder from de-dsb-learner**

Use Read on `/Users/david/Workspace/de-dsb-learner/database/seeders/DatabaseSeeder.php` to see how it orchestrates module creation. Note the module name and slug convention (likely `"M2 - BSI Grundschutz"` with slug `"m2-bsi-grundschutz"`).

- [ ] **Step 2: Create `ModuleSeeder`**

Create `database/seeders/ModuleSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        Module::firstOrCreate(
            ['slug' => 'm2-bsi-grundschutz'],
            [
                'name' => 'M2 - BSI Grundschutz',
                'description' => 'IT-Grundschutz-Praktiker Prüfungsvorbereitung nach BSI-Standards 200-1, 200-2, 200-3 und dem IT-Grundschutz-Kompendium.',
            ],
        );
    }
}
```

`firstOrCreate` keeps the seeder idempotent — running it repeatedly doesn't create duplicates.

- [ ] **Step 3: Wire it into `DatabaseSeeder`**

Read the existing `database/seeders/DatabaseSeeder.php`. Replace its contents with:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ModuleSeeder::class,
            // Question seeders get appended in Task 8.
        ]);
    }
}
```

This drops the default `User::factory()->create()` that Laravel ships with — we don't want an auto-created test user at seed time (there's no app-level "paid test user" expectation yet).

- [ ] **Step 4: Run seeders**

```bash
php artisan migrate:fresh --seed
```

Expected: runs cleanly. Confirm via tinker:

```bash
php artisan tinker --execute 'echo App\Models\Module::count();'
```

Expected: `1`.

```bash
php artisan tinker --execute 'echo App\Models\Module::first()->slug;'
```

Expected: `m2-bsi-grundschutz`.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/seeders/
git commit -m "feat: seed core BSI Grundschutz module"
```

---

### Task 8: Port question seeders from de-dsb-learner

**Files:**
- Create: `database/seeders/BsiStandard2001QuestionsSeeder.php` — port
- Create: `database/seeders/BsiStandard2002QuestionsSeeder.php` — port
- Create: `database/seeders/BsiStandard2003QuestionsSeeder.php` — port
- Create: `database/seeders/ItGrundschutzKompendiumQuestionsSeeder.php` — port
- Modify: `database/seeders/DatabaseSeeder.php` — append the four new seeders

**Scope note:** Skip `SampleQuestionsSeeder.php` — it contains three dev-scratch questions, not production data.

- [ ] **Step 1: Read all four source seeders**

Use Read on:
- `/Users/david/Workspace/de-dsb-learner/database/seeders/BsiStandard2001QuestionsSeeder.php`
- `/Users/david/Workspace/de-dsb-learner/database/seeders/BsiStandard2002QuestionsSeeder.php`
- `/Users/david/Workspace/de-dsb-learner/database/seeders/BsiStandard2003QuestionsSeeder.php`
- `/Users/david/Workspace/de-dsb-learner/database/seeders/ItGrundschutzKompendiumQuestionsSeeder.php`

Note each seeder's structure. Expected pattern (from the existing code review): fetch the module by slug, iterate over an array of question definitions (text, explanation, quote, source, answers[] with text/is_correct), create each `Question` + its `Answer` children.

- [ ] **Step 2: Port `BsiStandard2001QuestionsSeeder`**

Copy `/Users/david/Workspace/de-dsb-learner/database/seeders/BsiStandard2001QuestionsSeeder.php` to `database/seeders/BsiStandard2001QuestionsSeeder.php`. Adjust:

- Namespace stays `Database\Seeders`
- Any reference to `Module::where('slug', 'm2-bsi-grundschutz')->first()` stays — our schema matches
- Any reference to new columns (`topic`, `difficulty`) — our table has them nullable, so inserting WITHOUT those fields is fine (they stay null for Plan 3 to populate)
- Make sure the file ends with a newline (Pint's EOF rule)

If the source seeder uses **anything not in our schema** (e.g., calls `$question->tag(...)` or writes to a column like `weight` that doesn't exist), stop and report. Our schema has: `text`, `explanation`, `quote`, `source`, `topic`, `difficulty`, `module_id`, timestamps. Answer has: `text`, `is_correct`, `question_id`, timestamps.

- [ ] **Step 3: Port the other three seeders identically**

Repeat Step 2 for:
- `BsiStandard2002QuestionsSeeder.php`
- `BsiStandard2003QuestionsSeeder.php`
- `ItGrundschutzKompendiumQuestionsSeeder.php`

Each is a verbatim copy with only namespace/pathing identical (they already target `Database\Seeders`).

- [ ] **Step 4: Wire them into `DatabaseSeeder`**

Update `database/seeders/DatabaseSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ModuleSeeder::class,
            BsiStandard2001QuestionsSeeder::class,
            BsiStandard2002QuestionsSeeder::class,
            BsiStandard2003QuestionsSeeder::class,
            ItGrundschutzKompendiumQuestionsSeeder::class,
        ]);
    }
}
```

- [ ] **Step 5: Reseed and verify counts**

```bash
php artisan migrate:fresh --seed
```

Verify:

```bash
php artisan tinker --execute 'echo "Modules: ".App\Models\Module::count().PHP_EOL; echo "Questions: ".App\Models\Question::count().PHP_EOL; echo "Answers: ".App\Models\Answer::count().PHP_EOL;'
```

Expected:
- Modules: 1
- Questions: ≈ 220 (exact count depends on the source seeders — record the actual number)
- Answers: ≈ 880 (roughly 4 answers per question)

Record the exact numbers in the commit message for reference.

- [ ] **Step 6: Sanity check — inspect a sample question**

```bash
php artisan tinker --execute 'print_r(App\Models\Question::with("answers")->first()->toArray());'
```

Expected: one question with its 4 answers, populated `text`, `explanation`, possibly `quote` + `source`, and `topic` / `difficulty` both `null` (to be filled by Plan 3).

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/seeders/
git commit -m "feat: seed ~220 BSI questions from 4 source seeders (Q: <N>, A: <M>)"
```

Fill in `<N>` and `<M>` with the actual counts from Step 5.

---

### Task 9: Seeders smoke test

**Files:**
- Create: `tests/Feature/SeedersTest.php`

- [ ] **Step 1: Create a test that runs seeders and asserts shape**

Create `tests/Feature/SeedersTest.php`:

```php
<?php

use App\Models\Answer;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Support\Facades\Artisan;

it('seeds the BSI module and all questions', function () {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);

    expect(Module::count())->toBe(1)
        ->and(Module::first()->slug)->toBe('m2-bsi-grundschutz')
        ->and(Question::count())->toBeGreaterThan(200)
        ->and(Question::count())->toBeLessThan(250);
});

it('every question has between 2 and 5 answers', function () {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);

    $counts = Question::withCount('answers')->get()->pluck('answers_count');

    expect($counts->min())->toBeGreaterThanOrEqual(2);
    expect($counts->max())->toBeLessThanOrEqual(5);
});

it('every question has at least one correct answer', function () {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);

    $withoutCorrect = Question::whereDoesntHave('answers', fn ($q) => $q->where('is_correct', true))->count();

    expect($withoutCorrect)->toBe(0);
});

it('every answer belongs to a loaded question', function () {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);

    $orphaned = Answer::whereNull('question_id')->count();
    expect($orphaned)->toBe(0);
});
```

`RefreshDatabase` resets before each test, so each assertion runs against a fresh seeded DB.

- [ ] **Step 2: Run the test**

```bash
php artisan test --compact --filter=SeedersTest
```

Expected: 4 passing. Execution time: ~15–30 s (seeding happens 4 times — one per test). Acceptable for a guard test.

- [ ] **Step 3: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add tests/Feature/SeedersTest.php
git commit -m "test: add seed data integrity checks"
```

---

### Task 10: `exam_attempts` table + `ExamAttempt` model + factory + test

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_exam_attempts_table.php`
- Create: `app/Models/ExamAttempt.php`
- Create: `database/factories/ExamAttemptFactory.php`
- Create: `tests/Feature/Models/ExamAttemptTest.php`

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration create_exam_attempts_table --create=exam_attempts --no-interaction
```

Replace contents:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('session_uuid')->nullable()->unique();
            $table->timestamp('started_at');
            $table->timestamp('timer_expires_at');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedSmallInteger('total_questions')->default(50);
            $table->unsignedSmallInteger('score')->nullable();
            $table->boolean('is_free_attempt')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
```

`user_id` is nullable — anonymous users start exams with `session_uuid` set and `user_id` null. On claim (Plan 6), `user_id` is set. `session_uuid` is unique for idempotent claim lookup.

- [ ] **Step 2: Create model**

Create `app/Models/ExamAttempt.php`:

```php
<?php

namespace App\Models;

use Database\Factories\ExamAttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'session_uuid',
    'started_at',
    'timer_expires_at',
    'submitted_at',
    'total_questions',
    'score',
    'is_free_attempt',
])]
class ExamAttempt extends Model
{
    /** @use HasFactory<ExamAttemptFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'timer_expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'total_questions' => 'integer',
            'score' => 'integer',
            'is_free_attempt' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function examAnswers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function isAnonymous(): bool
    {
        return $this->user_id === null;
    }

    public function hasExpired(): bool
    {
        return $this->timer_expires_at->isPast();
    }
}
```

- [ ] **Step 3: Create factory**

Create `database/factories/ExamAttemptFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ExamAttempt>
 */
class ExamAttemptFactory extends Factory
{
    protected $model = ExamAttempt::class;

    public function definition(): array
    {
        $startedAt = now();

        return [
            'user_id' => null,
            'session_uuid' => Str::uuid()->toString(),
            'started_at' => $startedAt,
            'timer_expires_at' => $startedAt->copy()->addMinutes(60),
            'submitted_at' => null,
            'total_questions' => 50,
            'score' => null,
            'is_free_attempt' => true,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'session_uuid' => null,
            'is_free_attempt' => false,
        ]);
    }

    public function submitted(int $score): static
    {
        return $this->state(fn () => [
            'submitted_at' => now(),
            'score' => $score,
        ]);
    }

    public function expired(): static
    {
        return $this->state(function () {
            $startedAt = now()->subMinutes(61);

            return [
                'started_at' => $startedAt,
                'timer_expires_at' => $startedAt->copy()->addMinutes(60),
            ];
        });
    }
}
```

- [ ] **Step 4: Create test**

Create `tests/Feature/Models/ExamAttemptTest.php`:

```php
<?php

use App\Models\ExamAttempt;
use App\Models\User;

it('creates an anonymous exam attempt via factory', function () {
    $attempt = ExamAttempt::factory()->create();

    expect($attempt->isAnonymous())->toBeTrue()
        ->and($attempt->session_uuid)->not->toBeNull()
        ->and($attempt->total_questions)->toBe(50)
        ->and($attempt->is_free_attempt)->toBeTrue()
        ->and($attempt->submitted_at)->toBeNull();
});

it('creates a user-owned exam attempt via forUser state', function () {
    $user = User::factory()->create();
    $attempt = ExamAttempt::factory()->forUser($user)->create();

    expect($attempt->isAnonymous())->toBeFalse()
        ->and($attempt->user_id)->toBe($user->id)
        ->and($attempt->session_uuid)->toBeNull()
        ->and($attempt->is_free_attempt)->toBeFalse();
});

it('detects submitted attempts', function () {
    $attempt = ExamAttempt::factory()->submitted(score: 32)->create();

    expect($attempt->isSubmitted())->toBeTrue()
        ->and($attempt->score)->toBe(32)
        ->and($attempt->submitted_at)->not->toBeNull();
});

it('detects expired attempts', function () {
    $attempt = ExamAttempt::factory()->expired()->create();

    expect($attempt->hasExpired())->toBeTrue();
});

it('enforces unique session_uuid', function () {
    $uuid = '00000000-0000-4000-8000-000000000001';
    ExamAttempt::factory()->create(['session_uuid' => $uuid]);

    expect(fn () => ExamAttempt::factory()->create(['session_uuid' => $uuid]))
        ->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});
```

- [ ] **Step 5: Run migration + tests**

```bash
php artisan migrate
php artisan test --compact --filter=ExamAttemptTest
```

Expected: migration runs; 5 tests pass.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add ExamAttempt model + migration + factory"
```

---

### Task 11: `exam_answers` table + `ExamAnswer` model + factory + test

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_exam_answers_table.php`
- Create: `app/Models/ExamAnswer.php`
- Create: `database/factories/ExamAnswerFactory.php`
- Create: `tests/Feature/Models/ExamAnswerTest.php`

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration create_exam_answers_table --create=exam_answers --no-interaction
```

Replace contents:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->json('selected_option_ids')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->boolean('flagged')->default(false);
            $table->unsignedSmallInteger('position');
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'question_id']);
            $table->index(['exam_attempt_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};
```

- `selected_option_ids` is JSON — an array of Answer IDs the user ticked. BSI is multi-select; this supports 0–4 ticks.
- `is_correct` is nullable — computed at submission. During the 60-min window it stays null.
- `position` is 1..50, for ordering within an attempt.
- Unique (exam_attempt_id, question_id) prevents duplicate rows for the same question in one attempt.

- [ ] **Step 2: Create model**

Create `app/Models/ExamAnswer.php`:

```php
<?php

namespace App\Models;

use Database\Factories\ExamAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'exam_attempt_id',
    'question_id',
    'selected_option_ids',
    'is_correct',
    'flagged',
    'position',
])]
class ExamAnswer extends Model
{
    /** @use HasFactory<ExamAnswerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'selected_option_ids' => 'array',
            'is_correct' => 'boolean',
            'flagged' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
```

- [ ] **Step 3: Create factory**

Create `database/factories/ExamAnswerFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAnswer>
 */
class ExamAnswerFactory extends Factory
{
    protected $model = ExamAnswer::class;

    public function definition(): array
    {
        return [
            'exam_attempt_id' => ExamAttempt::factory(),
            'question_id' => Question::factory(),
            'selected_option_ids' => null,
            'is_correct' => null,
            'flagged' => false,
            'position' => 1,
        ];
    }

    public function flagged(): static
    {
        return $this->state(fn () => ['flagged' => true]);
    }

    public function correct(array $selectedIds): static
    {
        return $this->state(fn () => [
            'selected_option_ids' => $selectedIds,
            'is_correct' => true,
        ]);
    }

    public function incorrect(array $selectedIds): static
    {
        return $this->state(fn () => [
            'selected_option_ids' => $selectedIds,
            'is_correct' => false,
        ]);
    }
}
```

- [ ] **Step 4: Create test**

Create `tests/Feature/Models/ExamAnswerTest.php`:

```php
<?php

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Question;

it('creates an in-progress exam answer via factory', function () {
    $answer = ExamAnswer::factory()->create();

    expect($answer->selected_option_ids)->toBeNull()
        ->and($answer->is_correct)->toBeNull()
        ->and($answer->flagged)->toBeFalse()
        ->and($answer->position)->toBe(1);
});

it('stores selected option IDs as array', function () {
    $answer = ExamAnswer::factory()->correct(selectedIds: [1, 3, 5])->create();

    expect($answer->selected_option_ids)->toBe([1, 3, 5])
        ->and($answer->is_correct)->toBeTrue();
});

it('supports flagged answers', function () {
    $answer = ExamAnswer::factory()->flagged()->create();

    expect($answer->flagged)->toBeTrue();
});

it('belongs to an attempt and a question', function () {
    $attempt = ExamAttempt::factory()->create();
    $question = Question::factory()->create();
    $answer = ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($question)
        ->create(['position' => 1]);

    expect($answer->examAttempt->id)->toBe($attempt->id)
        ->and($answer->question->id)->toBe($question->id);
});

it('prevents duplicate question within an attempt', function () {
    $attempt = ExamAttempt::factory()->create();
    $question = Question::factory()->create();

    ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($question)
        ->create(['position' => 1]);

    expect(fn () => ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($question)
        ->create(['position' => 2]))
        ->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});
```

- [ ] **Step 5: Wire inverse relation on `ExamAttempt`**

Verify `app/Models/ExamAttempt.php` already has the `examAnswers()` hasMany relation (it should from Task 10). Test it quickly:

```php
// quick tinker check (optional)
php artisan tinker --execute '$a = App\Models\ExamAttempt::factory()->create(); App\Models\ExamAnswer::factory()->count(3)->for($a, "examAttempt")->create(); echo $a->fresh()->examAnswers->count();'
```

Expected: `3`.

- [ ] **Step 6: Run migration + tests**

```bash
php artisan migrate
php artisan test --compact --filter=ExamAnswerTest
```

Expected: 5 tests pass.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add ExamAnswer model + migration + factory"
```

---

### Task 12: `practice_answers` table + `PracticeAnswer` model + factory + test

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_create_practice_answers_table.php`
- Create: `app/Models/PracticeAnswer.php`
- Create: `database/factories/PracticeAnswerFactory.php`
- Create: `tests/Feature/Models/PracticeAnswerTest.php`

Practice tracking is separate from exam tracking (per the grill decision on Freies Lernen) so studying doesn't consume exam-freshness. It's paid-user-only, so `user_id` is always set (NOT nullable).

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration create_practice_answers_table --create=practice_answers --no-interaction
```

Replace contents:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->json('selected_option_ids');
            $table->boolean('is_correct');
            $table->timestamps();

            $table->index(['user_id', 'question_id', 'created_at']);
            $table->index(['user_id', 'is_correct']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_answers');
    }
};
```

The `(user_id, is_correct)` index supports the "replay only wrong ones" filter in Freies Lernen (Plan 5).

- [ ] **Step 2: Create model**

Create `app/Models/PracticeAnswer.php`:

```php
<?php

namespace App\Models;

use Database\Factories\PracticeAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'question_id', 'selected_option_ids', 'is_correct'])]
class PracticeAnswer extends Model
{
    /** @use HasFactory<PracticeAnswerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'selected_option_ids' => 'array',
            'is_correct' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
```

- [ ] **Step 3: Create factory**

Create `database/factories/PracticeAnswerFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\PracticeAnswer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PracticeAnswer>
 */
class PracticeAnswerFactory extends Factory
{
    protected $model = PracticeAnswer::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'question_id' => Question::factory(),
            'selected_option_ids' => [],
            'is_correct' => fake()->boolean(),
        ];
    }

    public function correct(array $selectedIds): static
    {
        return $this->state(fn () => [
            'selected_option_ids' => $selectedIds,
            'is_correct' => true,
        ]);
    }

    public function incorrect(array $selectedIds): static
    {
        return $this->state(fn () => [
            'selected_option_ids' => $selectedIds,
            'is_correct' => false,
        ]);
    }
}
```

- [ ] **Step 4: Create test**

Create `tests/Feature/Models/PracticeAnswerTest.php`:

```php
<?php

use App\Models\PracticeAnswer;
use App\Models\Question;
use App\Models\User;

it('creates a practice answer via factory', function () {
    $answer = PracticeAnswer::factory()->create();

    expect($answer->user_id)->not->toBeNull()
        ->and($answer->question_id)->not->toBeNull()
        ->and($answer->selected_option_ids)->toBeArray()
        ->and($answer->is_correct)->toBeBool();
});

it('allows multiple practice answers for the same user + question (no unique constraint)', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create();

    PracticeAnswer::factory()->for($user)->for($question)->create();
    PracticeAnswer::factory()->for($user)->for($question)->create();

    expect(PracticeAnswer::where('user_id', $user->id)->where('question_id', $question->id)->count())
        ->toBe(2);
});

it('belongs to a user and a question', function () {
    $user = User::factory()->create();
    $question = Question::factory()->create();
    $answer = PracticeAnswer::factory()->for($user)->for($question)->create();

    expect($answer->user->id)->toBe($user->id)
        ->and($answer->question->id)->toBe($question->id);
});

it('casts selected_option_ids to array and is_correct to bool', function () {
    $answer = PracticeAnswer::factory()->correct([10, 11])->create();

    expect($answer->selected_option_ids)->toBe([10, 11])
        ->and($answer->is_correct)->toBeTrue();
});
```

The "allows multiple practice answers" test is significant — the user might practice the same question repeatedly to learn. We do NOT enforce uniqueness here (unlike `exam_answers`).

- [ ] **Step 5: Run migration + tests**

```bash
php artisan migrate
php artisan test --compact --filter=PracticeAnswerTest
```

Expected: 4 tests pass.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add PracticeAnswer model + migration + factory"
```

---

### Task 13: Add `paid_at` to users + update `User` model relations

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_add_paid_at_to_users_table.php`
- Modify: `app/Models/User.php`
- Create: `tests/Feature/Models/UserPaidAtTest.php`

- [ ] **Step 1: Create migration**

```bash
php artisan make:migration add_paid_at_to_users_table --table=users --no-interaction
```

Replace contents:

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
            $table->timestamp('paid_at')->nullable()->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }
};
```

- [ ] **Step 2: Update `User` model**

Read the current `app/Models/User.php`. Replace the entire file with:

```php
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'paid_at'])]
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
            'paid_at' => 'datetime',
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

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }
}
```

- [ ] **Step 3: Add `paid` state to `UserFactory`**

Read `database/factories/UserFactory.php`. Append a `paid()` state method to the class:

```php
public function paid(): static
{
    return $this->state(fn () => ['paid_at' => now()]);
}
```

Do NOT change the default `definition()` — new users default to unpaid.

- [ ] **Step 4: Create test**

Create `tests/Feature/Models/UserPaidAtTest.php`:

```php
<?php

use App\Models\ExamAttempt;
use App\Models\PracticeAnswer;
use App\Models\User;

it('creates a user without paid_at by default', function () {
    $user = User::factory()->create();

    expect($user->paid_at)->toBeNull()
        ->and($user->isPaid())->toBeFalse();
});

it('creates a paid user via paid() state', function () {
    $user = User::factory()->paid()->create();

    expect($user->paid_at)->not->toBeNull()
        ->and($user->isPaid())->toBeTrue();
});

it('casts paid_at to a datetime', function () {
    $user = User::factory()->paid()->create();

    expect($user->paid_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('has many exam attempts', function () {
    $user = User::factory()->create();
    ExamAttempt::factory()->count(2)->forUser($user)->create();

    expect($user->examAttempts)->toHaveCount(2);
});

it('has many practice answers', function () {
    $user = User::factory()->create();
    PracticeAnswer::factory()->count(3)->for($user)->create();

    expect($user->practiceAnswers)->toHaveCount(3);
});
```

- [ ] **Step 5: Run migration + tests**

```bash
php artisan migrate
php artisan test --compact --filter=UserPaidAtTest
```

Expected: 5 tests pass.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add paid_at to users + wire exam/practice relations"
```

---

### Task 14: Final verification

**Files:** none (verification only)

- [ ] **Step 1: Fresh migrate + full seed**

```bash
php artisan migrate:fresh --seed
```

Expected: runs cleanly.

- [ ] **Step 2: Verify schema**

Run:

```bash
php artisan tinker --execute 'foreach (["modules","questions","answers","exam_attempts","exam_answers","practice_answers","users"] as $t) { echo $t.": ".implode(",", \Illuminate\Support\Facades\Schema::getColumnListing($t)).PHP_EOL; }'
```

Verify each table has the expected columns:
- `modules`: id, name, slug, description, created_at, updated_at
- `questions`: id, module_id, text, explanation, quote, source, topic, difficulty, created_at, updated_at
- `answers`: id, question_id, text, is_correct, created_at, updated_at
- `exam_attempts`: id, user_id, session_uuid, started_at, timer_expires_at, submitted_at, total_questions, score, is_free_attempt, created_at, updated_at
- `exam_answers`: id, exam_attempt_id, question_id, selected_option_ids, is_correct, flagged, position, created_at, updated_at
- `practice_answers`: id, user_id, question_id, selected_option_ids, is_correct, created_at, updated_at
- `users`: …, paid_at, …

Quote the actual output in the report. Flag any discrepancies.

- [ ] **Step 3: Verify seed counts**

```bash
php artisan tinker --execute 'echo "Modules: ".App\Models\Module::count().PHP_EOL; echo "Questions: ".App\Models\Question::count().PHP_EOL; echo "Answers: ".App\Models\Answer::count().PHP_EOL; echo "Untagged questions: ".App\Models\Question::whereNull("topic")->count().PHP_EOL;'
```

Expected:
- Modules: 1
- Questions: ≈ 220 (document exact count)
- Answers: ≈ 800–900
- Untagged questions: same as total questions (all are null until Plan 3)

- [ ] **Step 4: Full test suite**

```bash
php artisan test --compact
```

Expected: ALL tests pass. Record the exact pass count (should be around 65+, depending on exact test counts in each task).

- [ ] **Step 5: Pint + frontend build**

```bash
vendor/bin/pint --format agent
npm run build
```

Expected: Pint clean (or formats trivially — commit if so); build succeeds.

- [ ] **Step 6: Commit a completion marker**

```bash
git commit --allow-empty -m "chore: data-model + seed complete"
git log --oneline -30
```

Quote the last 15 commit lines in the report.

- [ ] **Step 7: Do NOT merge to main**

Leave the branch `feat/data-model-and-seed` as-is. Merge is a separate human-decision step via the `finishing-a-development-branch` skill.

Report that the branch is ready for review.

---

## Self-Review

**Spec coverage:**
- ✅ Port `modules` / `questions` / `answers` from de-dsb-learner: Tasks 4, 5, 6 (schema) + Tasks 7, 8 (data)
- ✅ `questions.topic` + `questions.difficulty` columns (nullable): Task 5
- ✅ `BsiTopic` + `QuestionDifficulty` enums: Tasks 2, 3
- ✅ `exam_attempts` with anonymous support via `session_uuid`: Task 10
- ✅ `exam_answers` with multi-select JSON + flagged + position: Task 11
- ✅ `practice_answers` (paid-user-only, supports repeat practice): Task 12
- ✅ `user.paid_at` + `isPaid()` helper + relations: Task 13
- ✅ Server-authoritative timer field (`timer_expires_at`): Task 10

**Placeholder scan:** No "TODO" / "TBD" / "handle edge cases" / "similar to Task N". Every step has full code or exact commands. Task 5 Step 4 intentionally references `Answer::factory()` that's created in Task 6 — this is a red-green TDD pattern, explicitly called out with the expected partial-failure state.

**Type consistency:**
- `selected_option_ids` field name matches across `ExamAnswer`, `PracticeAnswer` models + factories + tests.
- `session_uuid` spelled consistently (not `sessionUuid` or `session_id`).
- `is_free_attempt` (not `is_free` or `free_attempt`) consistent.
- `examAttempts` / `practiceAnswers` relations on User use camelCase method names (Laravel convention).
- Enum imports reference `App\Enums\BsiTopic` + `App\Enums\QuestionDifficulty` consistently.

**Migration ordering:** Modules → Questions (FK to modules) → Answers (FK to questions) → Exam attempts (FK to users optional) → Exam answers (FK to exam_attempts + questions) → Practice answers (FK to users + questions) → Users.paid_at. Each foreign-key target exists before the dependent migration runs. ✅

**Test dependencies:** Task 5's QuestionTest has one expected-failing test until Task 6 lands. Documented explicitly. Tasks 10–13 factory/relation tests all use seeded questions from Tasks 4–6 via factories — no implicit seed dependency.

---

## Execution Handoff

Plan complete and saved to `docs/superpowers/plans/2026-04-14-data-model-and-seed.md`. Two execution options:

1. **Subagent-Driven (recommended)** — I dispatch a fresh subagent per task, review between tasks, fast iteration. Proven pattern from the foundation refactor.

2. **Inline Execution** — Execute tasks in this session using executing-plans, batch execution with checkpoints.

Which approach?
