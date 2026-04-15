<?php

use App\Models\User;

it('passes current pricing to the welcome Inertia view', function () {
    User::factory()->paid()->count(20)->create();

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('welcome')
        ->where('pricing.amount_eur', 29)
        ->where('pricing.is_founder_price', true)
        ->where('pricing.spots_remaining', 80)
    );
});

it('shows standard price when founder cap reached', function () {
    User::factory()->paid()->count(100)->create();

    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page
        ->where('pricing.amount_eur', 49)
        ->where('pricing.is_founder_price', false)
    );
});
