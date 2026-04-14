<?php

use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;

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
        ->toThrow(UniqueConstraintViolationException::class);
});
