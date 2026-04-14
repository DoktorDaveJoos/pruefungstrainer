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
