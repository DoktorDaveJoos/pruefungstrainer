<?php

use Danestves\LaravelPolar\Order;

it('passes current pricing to the welcome Inertia view', function () {
    Order::factory()->count(20)->create(['product_id' => config('polar.products.founder')]);

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
    Order::factory()->count(100)->create(['product_id' => config('polar.products.founder')]);

    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page
        ->where('pricing.amount_eur', 49)
        ->where('pricing.is_founder_price', false)
    );
});
