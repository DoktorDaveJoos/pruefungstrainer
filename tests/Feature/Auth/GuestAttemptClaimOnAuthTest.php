<?php

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Support\Str;

it('redirects to the unlocked results page on login when the user has active access and a claimed attempt', function () {
    $user = User::factory()->hasActiveAccess()->create();
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}/ergebnis");
});

it('redirects to the locked results page on login when a submitted attempt is claimed but user has no access', function () {
    $user = User::factory()->create();
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}/ergebnis");

    $attempt->refresh();
    expect($attempt->user_id)->toBe($user->id)
        ->and($attempt->claimed_at)->not->toBeNull();
});

it('redirects to checkout on register when a submitted attempt is claimed without explicit intent', function () {
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->post('/register', [
            'name' => 'Test',
            'email' => 'test-no-intent@example.com',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ]);

    $response->assertRedirect('/checkout/start');

    $attempt->refresh();
    expect($attempt->user_id)->not->toBeNull()
        ->and($attempt->claimed_at)->not->toBeNull();
});

it('claims a guest attempt on register when the cookie matches', function () {
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->post('/register?intent=checkout', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ]);

    $response->assertRedirect('/checkout/start');

    $attempt->refresh();
    expect($attempt->user_id)->not->toBeNull()
        ->and($attempt->claimed_at)->not->toBeNull();
});

it('does not break login when no cookie is present', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(config('fortify.home'));
});

it('does not break register when no cookie is present', function () {
    $response = $this->post('/register?intent=checkout', [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ]);

    $response->assertRedirect('/checkout/start');
});
