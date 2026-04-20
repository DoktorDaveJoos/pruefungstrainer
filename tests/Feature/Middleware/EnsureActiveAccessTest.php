<?php

use App\Models\User;

it('allows access when the user has an active order', function () {
    $user = User::factory()->hasActiveAccess()->create();

    $this->actingAs($user)
        ->get('/freies-lernen')
        ->assertOk();
});

it('redirects unauthenticated users to login', function () {
    $this->get('/freies-lernen')->assertRedirect('/login');
});

it('redirects to checkout when the user has no access', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/freies-lernen')
        ->assertRedirect(route('checkout.start'));
});

it('redirects to checkout when the user had access but it expired', function () {
    $user = User::factory()->hasExpiredAccess()->create();

    $this->actingAs($user)
        ->get('/freies-lernen')
        ->assertRedirect(route('checkout.start'));
});
