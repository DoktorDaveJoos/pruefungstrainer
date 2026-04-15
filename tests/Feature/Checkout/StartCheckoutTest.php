<?php

use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.polar.api_key', 'polar_at_test');
    config()->set('services.polar.product_id', 'prod_test_123');

    Http::fake([
        'api.polar.sh/v1/checkouts' => Http::response([
            'id' => 'co_abc',
            'url' => 'https://buy.polar.sh/co_abc',
        ]),
    ]);
});

it('redirects an authenticated user to the Polar checkout URL with user_id metadata', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/checkout/start');

    $response->assertRedirect('https://buy.polar.sh/co_abc');

    Http::assertSent(function ($request) use ($user) {
        $body = $request->data();

        return $body['metadata'] === ['user_id' => $user->id]
            && $body['customer_email'] === $user->email;
    });
});

it('redirects an anonymous user with session_uuid metadata from the exam cookie', function () {
    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'sess-abc-123')
        ->post('/checkout/start');

    $response->assertRedirect('https://buy.polar.sh/co_abc');

    Http::assertSent(function ($request) {
        return $request->data()['metadata'] === ['session_uuid' => 'sess-abc-123'];
    });
});

it('redirects an anonymous user without an exam cookie (empty metadata)', function () {
    $response = $this->post('/checkout/start');

    $response->assertRedirect('https://buy.polar.sh/co_abc');

    Http::assertSent(function ($request) {
        return $request->data()['metadata'] === [];
    });
});

it('returns 503 when POLAR_API_KEY is missing', function () {
    config()->set('services.polar.api_key', null);

    $response = $this->post('/checkout/start');

    $response->assertStatus(503);
});
