<?php

use App\Actions\ClaimGuestAttempt;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Support\Facades\URL;

it('sends the user to their claimed results after verification when session has a remembered attempt', function () {
    $user = User::factory()->unverified()->create();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => $user->id,
        'claimed_at' => now(),
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $response = $this->actingAs($user)
        ->withSession([ClaimGuestAttempt::SESSION_KEY => $attempt->id])
        ->get($verificationUrl);

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}/ergebnis?verified=1");
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('falls back to the DB claim record when verification happens without a session-stashed attempt', function () {
    $user = User::factory()->unverified()->create();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => $user->id,
        'claimed_at' => now(),
    ]);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}/ergebnis?verified=1");
});

it('redirects to the default home after verification when no claimed attempt exists', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $response->assertRedirect('/dashboard?verified=1');
});
