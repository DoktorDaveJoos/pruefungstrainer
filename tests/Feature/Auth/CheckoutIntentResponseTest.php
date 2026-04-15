<?php

use App\Models\User;

it('redirects to checkout.start after registration when intent=checkout', function () {
    $response = $this->post('/register?intent=checkout', [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ]);

    $response->assertRedirect('/checkout/start');
});

it('redirects to the default location after registration without intent', function () {
    $response = $this->post('/register', [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
    ]);

    $response->assertRedirect(config('fortify.home'));
});

it('skips checkout and redirects to default if the logging-in user already has active access', function () {
    $user = User::factory()->hasActiveAccess()->create();

    $response = $this->post('/login?intent=checkout', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(config('fortify.home'));
});

it('redirects login with intent=checkout (no access) to checkout.start', function () {
    $user = User::factory()->create();

    $response = $this->post('/login?intent=checkout', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/checkout/start');
});
