<?php

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Question;
use Illuminate\Database\UniqueConstraintViolationException;

it('creates an in-progress exam answer via factory', function () {
    $answer = ExamAnswer::factory()->create();

    expect($answer->selected_option_ids)->toBeNull()
        ->and($answer->is_correct)->toBeNull()
        ->and($answer->position)->toBe(1);
});

it('stores selected option IDs as array', function () {
    $answer = ExamAnswer::factory()->correct(selectedIds: [1, 3, 5])->create();

    expect($answer->selected_option_ids)->toBe([1, 3, 5])
        ->and($answer->is_correct)->toBeTrue();
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
        ->toThrow(UniqueConstraintViolationException::class);
});
