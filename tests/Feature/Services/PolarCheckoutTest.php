<?php

use App\Services\PolarCheckout;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.polar.api_key', 'polar_at_test');
    config()->set('services.polar.product_id', 'prod_test_123');
    config()->set('services.polar.base_url', 'https://api.polar.sh/v1');
    config()->set('services.polar.success_url', 'http://localhost:8000/checkout/success');
});

it('posts to Polar /checkouts with product_id + metadata + auth header', function () {
    Http::fake([
        'api.polar.sh/v1/checkouts' => Http::response([
            'id' => 'co_abc',
            'url' => 'https://buy.polar.sh/co_abc',
        ]),
    ]);

    $url = (new PolarCheckout)->createSession(metadata: ['session_uuid' => 'abc-123'], customerEmail: 'foo@example.com');

    expect($url)->toBe('https://buy.polar.sh/co_abc');

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->url() === 'https://api.polar.sh/v1/checkouts'
            && $request->hasHeader('Authorization', 'Bearer polar_at_test')
            && $body['product_id'] === 'prod_test_123'
            && $body['metadata'] === ['session_uuid' => 'abc-123']
            && $body['customer_email'] === 'foo@example.com'
            && $body['success_url'] === 'http://localhost:8000/checkout/success?checkout_id={CHECKOUT_ID}';
    });
});

it('omits customer_email when null', function () {
    Http::fake([
        'api.polar.sh/v1/checkouts' => Http::response(['id' => 'co_abc', 'url' => 'https://buy.polar.sh/co_abc']),
    ]);

    (new PolarCheckout)->createSession(metadata: ['user_id' => 42]);

    Http::assertSent(function ($request) {
        return ! isset($request->data()['customer_email']);
    });
});

it('throws when Polar returns an error', function () {
    Http::fake([
        'api.polar.sh/v1/checkouts' => Http::response(['error' => 'invalid_product'], 422),
    ]);

    expect(fn () => (new PolarCheckout)->createSession(metadata: []))
        ->toThrow(RuntimeException::class, 'Polar checkout failed');
});

it('throws when api_key is not configured', function () {
    config()->set('services.polar.api_key', null);

    expect(fn () => (new PolarCheckout)->createSession(metadata: []))
        ->toThrow(RuntimeException::class, 'POLAR_API_KEY is not set');
});
