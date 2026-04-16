<?php

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Support\Str;

it('marks the free tier as available for a fresh visitor', function () {
    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page
        ->component('welcome')
        ->where('freeTier.status', 'available')
        ->where('freeTier.lastAttemptId', null)
    );
});

it('marks the free tier as resume when the cookie points to an in-progress attempt', function () {
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->create([
        'user_id' => null,
        'session_uuid' => $uuid,
        'submitted_at' => null,
    ]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)->get('/');

    $response->assertInertia(fn ($page) => $page
        ->where('freeTier.status', 'resume')
        ->where('freeTier.lastAttemptId', $attempt->id)
    );
});

it('marks the free tier as already_done when the cookie points to a submitted attempt', function () {
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)->get('/');

    $response->assertInertia(fn ($page) => $page
        ->where('freeTier.status', 'already_done')
        ->where('freeTier.lastAttemptId', $attempt->id)
    );
});

it('marks the free tier as available for authenticated users regardless of cookie state', function () {
    $user = User::factory()->create();
    $uuid = Str::uuid()->toString();
    ExamAttempt::factory()->submitted(30)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    $response = $this->actingAs($user)
        ->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->get('/');

    $response->assertInertia(fn ($page) => $page
        ->where('freeTier.status', 'available')
        ->where('freeTier.lastAttemptId', null)
    );
});
