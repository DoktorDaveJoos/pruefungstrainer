<?php

use App\Models\ExamAttempt;
use App\Models\PracticeAnswer;
use App\Models\User;
use Carbon\CarbonImmutable;

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

    expect($user->paid_at)->toBeInstanceOf(CarbonImmutable::class);
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
