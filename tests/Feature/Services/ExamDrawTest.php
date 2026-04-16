<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Models\User;
use App\Services\ExamDraw;

function seedQuestions(int $basis, int $experte): void
{
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()->for($module)->count($basis)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Question::factory()->for($module)->count($experte)->tagged(BsiTopic::Methodik, QuestionDifficulty::Experte)->create();
}

it('draws exactly 50 questions when the pool is large enough', function () {
    seedQuestions(basis: 100, experte: 50);
    $user = User::factory()->create();

    $questions = (new ExamDraw)->drawForUser(userId: $user->id, total: 50);

    expect($questions)->toHaveCount(50);
});

it('respects the 75/25 basis/experte split when both buckets have room', function () {
    seedQuestions(basis: 100, experte: 50);
    $user = User::factory()->create();

    $questions = (new ExamDraw)->drawForUser(userId: $user->id, total: 50);

    $basisCount = $questions->where('difficulty', QuestionDifficulty::Basis)->count();
    $experteCount = $questions->where('difficulty', QuestionDifficulty::Experte)->count();

    expect($basisCount)->toBe(38);
    expect($experteCount)->toBe(12);
});

it('tops up from the other bucket when experte pool is too small', function () {
    seedQuestions(basis: 100, experte: 5);
    $user = User::factory()->create();

    $questions = (new ExamDraw)->drawForUser(userId: $user->id, total: 50);

    expect($questions)->toHaveCount(50);

    $experteCount = $questions->where('difficulty', QuestionDifficulty::Experte)->count();
    $basisCount = $questions->where('difficulty', QuestionDifficulty::Basis)->count();

    expect($experteCount)->toBe(5);
    expect($basisCount)->toBe(45);
});

it('returns fewer than 50 when the pool itself is too small', function () {
    seedQuestions(basis: 20, experte: 5);
    $user = User::factory()->create();

    $questions = (new ExamDraw)->drawForUser(userId: $user->id, total: 50);

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
    $questions = (new ExamDraw)->drawForUser(userId: $user->id, total: 50);

    $seenIds = $seen->pluck('id')->all();
    $newlyDrawnIds = $questions->pluck('id')->all();
    $overlap = array_intersect($seenIds, $newlyDrawnIds);

    // With 150 in pool and 50 seen, a 50-question draw should mostly avoid overlap.
    expect(count($overlap))->toBeLessThanOrEqual(10);
});

it('skips questions that are still null-tagged (untagged)', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()->for($module)->count(50)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Question::factory()->for($module)->count(10)->create(); // untagged
    $user = User::factory()->create();

    $questions = (new ExamDraw)->drawForUser(userId: $user->id, total: 40);

    expect($questions->whereNull('difficulty'))->toHaveCount(0);
});
