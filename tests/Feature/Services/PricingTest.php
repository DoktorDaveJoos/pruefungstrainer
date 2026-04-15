<?php

use App\Services\Pricing;
use Danestves\LaravelPolar\Order;

beforeEach(function () {
    $this->pricing = app(Pricing::class);
});

it('returns founder price with all 100 spots remaining when no orders exist', function () {
    $price = $this->pricing->currentPrice();

    expect($price['amount_eur'])->toBe(29);
    expect($price['standard_price_eur'])->toBe(49);
    expect($price['is_founder_price'])->toBeTrue();
    expect($price['spots_remaining'])->toBe(100);
});

it('returns founder price with 1 spot remaining when 99 founder orders exist', function () {
    Order::factory()->count(99)->create(['product_id' => config('polar.products.founder')]);

    $price = $this->pricing->currentPrice();

    expect($price['is_founder_price'])->toBeTrue();
    expect($price['spots_remaining'])->toBe(1);
});

it('returns standard price once 100 founder orders exist', function () {
    Order::factory()->count(100)->create(['product_id' => config('polar.products.founder')]);

    $price = $this->pricing->currentPrice();

    expect($price['amount_eur'])->toBe(49);
    expect($price['is_founder_price'])->toBeFalse();
    expect($price['spots_remaining'])->toBe(0);
});

it('does not count refunded founder orders toward the cap', function () {
    Order::factory()->count(100)->create(['product_id' => config('polar.products.founder'), 'refunded_at' => now()]);

    $price = $this->pricing->currentPrice();

    expect($price['is_founder_price'])->toBeTrue();
});

it('returns the founder product id when below the cap', function () {
    expect($this->pricing->currentProductId())->toBe(config('polar.products.founder'));
});

it('returns the standard product id at the cap', function () {
    Order::factory()->count(100)->create(['product_id' => config('polar.products.founder')]);

    expect($this->pricing->currentProductId())->toBe(config('polar.products.standard'));
});
