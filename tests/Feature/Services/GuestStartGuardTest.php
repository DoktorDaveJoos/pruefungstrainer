<?php

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use App\Services\GuestStartGuard;
use App\Services\GuestStartStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

function requestWithSessionCookie(?string $value): Request
{
    $request = Request::create('/pruefungssimulation/start', 'POST');

    if ($value !== null) {
        $request->cookies->set(ExamAttemptFinder::SESSION_COOKIE, $value);
    }

    return $request;
}

it('returns ok when no cookie is present', function () {
    $state = (new GuestStartGuard)->inspect(requestWithSessionCookie(null));

    expect($state->status)->toBe(GuestStartStatus::Available)
        ->and($state->attemptId)->toBeNull();
});

it('returns ok when the cookie points to a non-existent attempt (stale cookie)', function () {
    $state = (new GuestStartGuard)->inspect(requestWithSessionCookie(Str::uuid()->toString()));

    expect($state->status)->toBe(GuestStartStatus::Available)
        ->and($state->attemptId)->toBeNull();
});

it('returns resume when cookie points to an in-progress guest attempt', function () {
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->create([
        'user_id' => null,
        'session_uuid' => $uuid,
        'submitted_at' => null,
    ]);

    $state = (new GuestStartGuard)->inspect(requestWithSessionCookie($uuid));

    expect($state->status)->toBe(GuestStartStatus::Resume)
        ->and($state->attemptId)->toBe($attempt->id);
});

it('returns already_done when cookie points to a submitted, unclaimed attempt', function () {
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    $state = (new GuestStartGuard)->inspect(requestWithSessionCookie($uuid));

    expect($state->status)->toBe(GuestStartStatus::AlreadyDone)
        ->and($state->attemptId)->toBe($attempt->id);
});

it('returns already_done when cookie points to a submitted, claimed attempt', function () {
    $user = User::factory()->create();
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(40)->create([
        'user_id' => $user->id,
        'session_uuid' => $uuid,
        'claimed_at' => now(),
    ]);

    $state = (new GuestStartGuard)->inspect(requestWithSessionCookie($uuid));

    expect($state->status)->toBe(GuestStartStatus::AlreadyDone)
        ->and($state->attemptId)->toBe($attempt->id);
});
