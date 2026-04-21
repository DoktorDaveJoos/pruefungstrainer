<?php

use App\Models\ExamAttempt;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->hasActiveAccess()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk();
});

test('authenticated users without paid access are redirected to checkout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('checkout.start'));
});

test('dashboard lists submitted attempts for the current user ordered newest first', function () {
    $user = User::factory()->hasActiveAccess()->create();

    $older = ExamAttempt::factory()
        ->forUser($user)
        ->submitted(score: 29)
        ->create([
            'submitted_at' => now()->subDays(3),
            'total_questions' => 50,
        ]);

    $newer = ExamAttempt::factory()
        ->forUser($user)
        ->submitted(score: 42)
        ->create([
            'submitted_at' => now()->subDay(),
            'total_questions' => 50,
        ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->component('dashboard')
        ->has('attempts', 2)
        ->where('attempts.0.id', $newer->id)
        ->where('attempts.0.score', 42)
        ->where('attempts.0.total_questions', 50)
        ->where('attempts.0.passed', true)
        ->where('attempts.1.id', $older->id)
        ->where('attempts.1.passed', false)
    );
});

test('dashboard excludes unsubmitted attempts from the list', function () {
    $user = User::factory()->hasActiveAccess()->create();

    ExamAttempt::factory()->forUser($user)->create([
        'submitted_at' => null,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page->has('attempts', 0));
});

test('dashboard does not leak other users attempts', function () {
    $user = User::factory()->hasActiveAccess()->create();
    $other = User::factory()->create();

    ExamAttempt::factory()->forUser($other)->submitted(score: 40)->create([
        'total_questions' => 50,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page->has('attempts', 0));
});

test('dashboard exposes the running attempt id when one exists', function () {
    $user = User::factory()->hasActiveAccess()->create();

    $running = ExamAttempt::factory()->forUser($user)->create([
        'submitted_at' => null,
        'timer_expires_at' => now()->addMinutes(30),
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('runningAttemptId', $running->id)
    );
});

test('dashboard ignores expired running attempts', function () {
    $user = User::factory()->hasActiveAccess()->create();

    ExamAttempt::factory()->forUser($user)->expired()->create([
        'submitted_at' => null,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertInertia(fn ($page) => $page
        ->where('runningAttemptId', null)
    );
});
