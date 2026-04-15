<?php

use App\Models\User;

it('renders the success page for an authenticated paid user', function () {
    $user = User::factory()->paid()->create();

    $response = $this->actingAs($user)->get('/checkout/success');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('checkout/success')
        ->where('isPaid', true)
        ->where('isAuthenticated', true)
    );
});

it('renders the success page for an anonymous visitor (post-Polar redirect, before webhook fires)', function () {
    $response = $this->get('/checkout/success');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('checkout/success')
        ->where('isPaid', false)
        ->where('isAuthenticated', false)
    );
});

it('shows isPaid=false for an authenticated unpaid user (webhook not yet fired)', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/checkout/success');

    $response->assertInertia(fn ($page) => $page
        ->where('isPaid', false)
        ->where('isAuthenticated', true)
    );
});
