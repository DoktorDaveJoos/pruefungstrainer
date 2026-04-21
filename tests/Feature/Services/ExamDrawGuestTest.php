<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Exceptions\InsufficientFreeTierQuestionsException;
use App\Models\Module;
use App\Models\Question;
use App\Services\ExamDraw;

it('returns the first 50 free-tier questions ordered by id (deterministic across calls)', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);

    Question::factory()
        ->for($module)
        ->count(60)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->freeTier()
        ->create();

    Question::factory()
        ->for($module)
        ->count(20)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->create();

    $first = (new ExamDraw)->drawForGuest();
    $second = (new ExamDraw)->drawForGuest();

    expect($first)->toHaveCount(50)
        ->and($first->every(fn (Question $q) => $q->is_free_tier === true))->toBeTrue()
        ->and($first->pluck('id')->all())->toBe($second->pluck('id')->all());
});

it('throws when fewer than one free-tier question exists', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);

    Question::factory()
        ->for($module)
        ->count(10)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->create();

    (new ExamDraw)->drawForGuest();
})->throws(InsufficientFreeTierQuestionsException::class);

it('eager-loads answers on free-tier draws so they can be persisted as options_order', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);

    Question::factory()
        ->for($module)
        ->count(3)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->freeTier()
        ->create();

    $questions = (new ExamDraw)->drawForGuest();

    expect($questions->first()->relationLoaded('answers'))->toBeTrue();
});
