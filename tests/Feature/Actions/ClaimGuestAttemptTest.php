<?php

use App\Actions\ClaimGuestAttempt;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

function requestWithCookie(?string $value): Request
{
    $request = Request::create('/whatever', 'POST');

    if ($value !== null) {
        $request->cookies->set(ExamAttemptFinder::SESSION_COOKIE, $value);
    }

    return $request;
}

it('claims an unclaimed guest attempt and stamps claimed_at', function () {
    $user = User::factory()->create();
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    $claimed = (new ClaimGuestAttempt)(user: $user, request: requestWithCookie($uuid));

    expect($claimed)->not->toBeNull()
        ->and($claimed->id)->toBe($attempt->id);

    $attempt->refresh();
    expect($attempt->user_id)->toBe($user->id)
        ->and($attempt->claimed_at)->not->toBeNull();
});

it('is a no-op when no cookie is present', function () {
    $user = User::factory()->create();

    $result = (new ClaimGuestAttempt)(user: $user, request: requestWithCookie(null));

    expect($result)->toBeNull();
});

it('is a no-op when the cookie points to a non-existent attempt', function () {
    $user = User::factory()->create();

    $result = (new ClaimGuestAttempt)(user: $user, request: requestWithCookie(Str::uuid()->toString()));

    expect($result)->toBeNull();
});

it('is a no-op when the attempt is already claimed by the same user', function () {
    $user = User::factory()->create();
    $uuid = Str::uuid()->toString();
    $original = now()->subDay();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => $user->id,
        'session_uuid' => $uuid,
        'claimed_at' => $original,
    ]);

    $result = (new ClaimGuestAttempt)(user: $user, request: requestWithCookie($uuid));

    expect($result)->not->toBeNull()
        ->and($result->id)->toBe($attempt->id);

    $attempt->refresh();
    // Should NOT have been bumped to "now" — confirms the no-op.
    expect($attempt->claimed_at)->not->toBeNull()
        ->and($attempt->claimed_at->lt(now()->subHour()))->toBeTrue();
});

it('does not steal an attempt that belongs to a different user and logs a warning', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => $owner->id,
        'session_uuid' => $uuid,
        'claimed_at' => now()->subDay(),
    ]);

    Log::shouldReceive('warning')->once();

    $result = (new ClaimGuestAttempt)(user: $intruder, request: requestWithCookie($uuid));

    expect($result)->toBeNull();

    $attempt->refresh();
    expect($attempt->user_id)->toBe($owner->id);
});
