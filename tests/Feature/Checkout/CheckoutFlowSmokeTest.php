<?php

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.polar.api_key', 'polar_at_test');
    config()->set('services.polar.product_id', 'prod_test');
    config()->set('services.polar.webhook_secret', 'whsec_test');
});

it('walks anonymous user through start → Polar redirect → webhook → claim → success', function () {
    Http::fake([
        'api.polar.sh/v1/checkouts' => Http::response([
            'id' => 'co_xyz',
            'url' => 'https://buy.polar.sh/co_xyz',
        ]),
    ]);

    $attempt = ExamAttempt::factory()->submitted(score: 18)->create(['session_uuid' => 'sess-flow']);

    $startResponse = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'sess-flow')
        ->post('/checkout/start');
    $startResponse->assertRedirect('https://buy.polar.sh/co_xyz');

    $payload = json_encode([
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_polar_xyz', 'email' => 'buyer@example.com', 'name' => 'Buyer'],
            'metadata' => ['session_uuid' => 'sess-flow'],
        ],
    ]);
    $signature = hash_hmac('sha256', $payload, 'whsec_test');

    $webhookResponse = $this->call(
        method: 'POST',
        uri: '/webhooks/polar',
        server: ['CONTENT_TYPE' => 'application/json', 'HTTP_WEBHOOK_SIGNATURE' => $signature],
        content: $payload,
    );
    $webhookResponse->assertStatus(200);

    $user = User::where('email', 'buyer@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->paid_at)->not->toBeNull();
    expect($user->polar_customer_id)->toBe('cust_polar_xyz');
    expect($attempt->fresh()->user_id)->toBe($user->id);

    $successResponse = $this->get('/checkout/success');
    $successResponse->assertStatus(200);
    $successResponse->assertInertia(fn ($page) => $page->component('checkout/success'));
});
