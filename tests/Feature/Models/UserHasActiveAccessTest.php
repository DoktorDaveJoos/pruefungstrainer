<?php

use App\Models\User;
use Danestves\LaravelPolar\Order;

it('returns false when the user has no orders', function (): void {
    $user = User::factory()->create();

    expect($user->hasActiveAccess())->toBeFalse();
});

it('returns true when a founder order was placed within the last year', function (): void {
    $user = User::factory()->create();
    Order::factory()->for($user, 'billable')->create([
        'product_id' => config('polar.products.founder'),
        'ordered_at' => now()->subDays(30),
        'refunded_at' => null,
    ]);

    expect($user->hasActiveAccess())->toBeTrue();
});

it('returns true when a standard order was placed within the last year', function (): void {
    $user = User::factory()->create();
    Order::factory()->for($user, 'billable')->create([
        'product_id' => config('polar.products.standard'),
        'ordered_at' => now()->subDays(200),
        'refunded_at' => null,
    ]);

    expect($user->hasActiveAccess())->toBeTrue();
});

it('returns false when the only order is older than 365 days', function (): void {
    $user = User::factory()->create();
    Order::factory()->for($user, 'billable')->create([
        'product_id' => config('polar.products.founder'),
        'ordered_at' => now()->subDays(366),
        'refunded_at' => null,
    ]);

    expect($user->hasActiveAccess())->toBeFalse();
});

it('returns false when the only order is refunded', function (): void {
    $user = User::factory()->create();
    Order::factory()->for($user, 'billable')->create([
        'product_id' => config('polar.products.founder'),
        'ordered_at' => now()->subDays(10),
        'refunded_at' => now()->subDays(5),
    ]);

    expect($user->hasActiveAccess())->toBeFalse();
});

it('does not leak access from another user', function (): void {
    $me = User::factory()->create();
    $other = User::factory()->create();
    Order::factory()->for($other, 'billable')->create([
        'product_id' => config('polar.products.founder'),
        'ordered_at' => now()->subDays(30),
        'refunded_at' => null,
    ]);

    expect($me->hasActiveAccess())->toBeFalse();
});
