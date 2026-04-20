<?php

use App\Models\User;
use Danestves\LaravelPolar\Customer;
use Danestves\LaravelPolar\LaravelPolar;
use Polar\CustomerSessions;
use Polar\Models\Components;
use Polar\Models\Operations;
use Polar\Polar;

afterEach(function () {
    LaravelPolar::resetSdk();
});

it('requires authentication', function () {
    $this->get(route('billing.portal'))->assertRedirect('/login');
});

it('redirects a user without a polar customer to checkout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('billing.portal'))
        ->assertRedirect(route('checkout.start'));
});

it('returns an inertia location to the polar customer portal', function () {
    $user = User::factory()->create();

    Customer::factory()->create([
        'billable_id' => $user->id,
        'billable_type' => User::class,
        'polar_id' => 'cus_test_123',
    ]);

    $session = Mockery::mock(Components\CustomerSession::class)->makePartial();
    $session->customerPortalUrl = 'https://sandbox.polar.sh/portal/test';

    $createResponse = Mockery::mock(Operations\CustomerSessionsCreateResponse::class)->makePartial();
    $createResponse->statusCode = 201;
    $createResponse->customerSession = $session;

    $customerSessions = Mockery::mock(CustomerSessions::class);
    $customerSessions->shouldReceive('create')->once()->andReturn($createResponse);

    $sdk = Mockery::mock(Polar::class)->makePartial();
    $sdk->customerSessions = $customerSessions;
    LaravelPolar::setSdk($sdk);

    $this->actingAs($user)
        ->get(route('billing.portal'))
        ->assertRedirect('https://sandbox.polar.sh/portal/test');
});
