<?php

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;

it('claims orphan attempts on registration when the session cookie matches', function () {
    $sessionUuid = 'session-'.fake()->uuid();
    $attempt = ExamAttempt::factory()->create([
        'user_id' => null,
        'session_uuid' => $sessionUuid,
    ]);

    $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid)
        ->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();
    expect($attempt->fresh()->user_id)->toBe($user->id);
});

it('claims orphan attempts on login when the session cookie matches', function () {
    $sessionUuid = 'session-'.fake()->uuid();
    $attempt = ExamAttempt::factory()->create([
        'user_id' => null,
        'session_uuid' => $sessionUuid,
    ]);
    $user = User::factory()->create();

    $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid)
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    expect($attempt->fresh()->user_id)->toBe($user->id);
});

it('does not claim attempts owned by a different user', function () {
    $other = User::factory()->create();
    $attempt = ExamAttempt::factory()->create([
        'user_id' => $other->id,
        'session_uuid' => 'session-abc',
    ]);

    $user = User::factory()->create();

    $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'session-abc')
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

    expect($attempt->fresh()->user_id)->toBe($other->id);
});
