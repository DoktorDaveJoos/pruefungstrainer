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
