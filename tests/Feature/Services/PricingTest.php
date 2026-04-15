<?php

use App\Models\User;
use App\Services\Pricing;

it('returns Founders Price (29 EUR) when fewer than 100 users have paid', function () {
    User::factory()->paid()->count(50)->create();

    $price = (new Pricing)->currentPrice();

    expect($price)->toBe([
        'amount_eur' => 29,
        'standard_price_eur' => 49,
        'is_founder_price' => true,
        'spots_remaining' => 50,
    ]);
});

it('returns standard price (49 EUR) when 100 paid users reached', function () {
    User::factory()->paid()->count(100)->create();

    $price = (new Pricing)->currentPrice();

    expect($price)->toBe([
        'amount_eur' => 49,
        'standard_price_eur' => 49,
        'is_founder_price' => false,
        'spots_remaining' => 0,
    ]);
});

it('returns Founders Price for empty user pool', function () {
    $price = (new Pricing)->currentPrice();

    expect($price)->toBe([
        'amount_eur' => 29,
        'standard_price_eur' => 49,
        'is_founder_price' => true,
        'spots_remaining' => 100,
    ]);
});

it('counts only paid users (ignores unpaid registrations)', function () {
    User::factory()->paid()->count(30)->create();
    User::factory()->count(50)->create();

    $price = (new Pricing)->currentPrice();

    expect($price['spots_remaining'])->toBe(70);
    expect($price['is_founder_price'])->toBeTrue();
});

it('returns standard price even when 101+ users have paid (over the cap)', function () {
    User::factory()->paid()->count(101)->create();

    $price = (new Pricing)->currentPrice();

    expect($price['amount_eur'])->toBe(49);
    expect($price['spots_remaining'])->toBe(0);
});
