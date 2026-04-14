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
