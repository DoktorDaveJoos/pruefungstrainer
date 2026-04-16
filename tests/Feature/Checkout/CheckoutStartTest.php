<?php

use App\Models\User;
use Danestves\LaravelPolar\LaravelPolar;
use Polar\Checkouts;
use Polar\Models\Components;
use Polar\Models\Operations;
use Polar\Polar;

/**
 * Build a minimal mock Polar SDK that returns a checkout URL.
 */
function mockPolarSdk(string $checkoutUrl = 'https://sandbox.polar.sh/checkout/chk_test'): Polar
{
    $checkoutComponent = Mockery::mock(Components\Checkout::class)->makePartial();
    $checkoutComponent->url = $checkoutUrl;

    $createResponse = Mockery::mock(Operations\CheckoutsCreateResponse::class)->makePartial();
    $createResponse->statusCode = 201;
    $createResponse->checkout = $checkoutComponent;

    $checkouts = Mockery::mock(Checkouts::class);
    $checkouts->shouldReceive('create')->andReturn($createResponse);

    $polarSdk = Mockery::mock(Polar::class)->makePartial();
    $polarSdk->checkouts = $checkouts;

    return $polarSdk;
}

afterEach(function () {
    LaravelPolar::resetSdk();
});

it('requires authentication', function () {
    $this->get(route('checkout.start'))->assertRedirect('/login');
});

it('redirects a logged-in user with active access back to home', function () {
    $user = User::factory()->hasActiveAccess()->create();

    $this->actingAs($user)
        ->get(route('checkout.start'))
        ->assertRedirect(config('fortify.home'));
});

it('redirects a logged-in user with no active access to a polar checkout URL', function () {
    LaravelPolar::setSdk(mockPolarSdk());

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('checkout.start'));

    $response->assertRedirect('https://sandbox.polar.sh/checkout/chk_test');
});

it('passes the founder product id when below the cap', function () {
    $capturedRequest = null;

    $checkoutComponent = Mockery::mock(Components\Checkout::class)->makePartial();
    $checkoutComponent->url = 'https://sandbox.polar.sh/checkout/chk_test';

    $createResponse = Mockery::mock(Operations\CheckoutsCreateResponse::class)->makePartial();
    $createResponse->statusCode = 201;
    $createResponse->checkout = $checkoutComponent;

    $checkouts = Mockery::mock(Checkouts::class);
    $checkouts->shouldReceive('create')
        ->once()
        ->withArgs(function (Components\CheckoutCreate $req) use (&$capturedRequest) {
            $capturedRequest = $req;

            return true;
        })
        ->andReturn($createResponse);

    $polarSdk = Mockery::mock(Polar::class)->makePartial();
    $polarSdk->checkouts = $checkouts;

    LaravelPolar::setSdk($polarSdk);

    $user = User::factory()->create();
    $this->actingAs($user)->get(route('checkout.start'));

    expect($capturedRequest)->not->toBeNull();
    expect($capturedRequest->products)->toContain(config('polar.products.founder'));
});
