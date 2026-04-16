<?php

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Support\Str;

it('claims a guest attempt and returns the unlocked-results redirect target on /api/access-status when access is active', function () {
    $user = User::factory()->hasActiveAccess()->create();
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(40)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    $response = $this->actingAs($user)
        ->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->get('/api/access-status');

    $response->assertStatus(200)
        ->assertJson([
            'hasAccess' => true,
            'redirectTo' => "/pruefungssimulation/{$attempt->id}/ergebnis",
        ]);

    $attempt->refresh();
    expect($attempt->user_id)->toBe($user->id)
        ->and($attempt->claimed_at)->not->toBeNull();
});

it('returns the fortify home redirect target when access is active but no claimed attempt exists', function () {
    $user = User::factory()->hasActiveAccess()->create();

    $response = $this->actingAs($user)->get('/api/access-status');

    $response->assertStatus(200)
        ->assertJson([
            'hasAccess' => true,
            'redirectTo' => config('fortify.home'),
        ]);
});

it('returns no redirect target while access is still pending', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/api/access-status');

    $response->assertStatus(200)
        ->assertJson([
            'hasAccess' => false,
            'redirectTo' => null,
        ]);
});

it('claims and exposes redirect target on the processing page render', function () {
    $user = User::factory()->hasActiveAccess()->create();
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(40)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    $response = $this->actingAs($user)
        ->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->get('/checkout/processing');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('checkout/processing')
        ->where('hasAccess', true)
        ->where('redirectTo', "/pruefungssimulation/{$attempt->id}/ergebnis")
    );

    $attempt->refresh();
    expect($attempt->user_id)->toBe($user->id);
});
