<?php

use App\Jobs\SendAccessExpiryReminder;
use App\Models\User;
use Danestves\LaravelPolar\Handlers\ProcessWebhook;
use Illuminate\Support\Facades\Queue;

/**
 * Build a Standard Webhooks signed request to /polar/webhook.
 *
 * PolarSignature::isValid() calls:
 *   $signingSecret = base64_encode($config->signingSecret)  // re-encodes the raw secret
 *   new \StandardWebhooks\Webhook($signingSecret)           // constructor base64_decodes it back
 * So the effective HMAC key is the raw POLAR_WEBHOOK_SECRET value.
 *
 * Signing spec: HMAC-SHA256( "{msgId}.{timestamp}.{body}" ) → base64
 * Header value: "v1,{base64Signature}"
 *
 * @param  array<string, mixed>  $payload
 * @return array{ body: string, headers: array<string, string> }
 */
function buildSignedWebhook(array $payload): array
{
    $body = json_encode($payload);
    $msgId = 'msg_' . uniqid();
    $timestamp = (string) time();
    $secret = config('webhook-client.configs.0.signing_secret'); // raw value from env

    $toSign = "{$msgId}.{$timestamp}.{$body}";
    $hexHash = hash_hmac('sha256', $toSign, $secret);
    $signature = base64_encode(pack('H*', $hexHash));

    return [
        'body' => $body,
        'headers' => [
            'Content-Type' => 'application/json',
            'webhook-id' => $msgId,
            'webhook-timestamp' => $timestamp,
            'webhook-signature' => "v1,{$signature}",
        ],
    ];
}

it('accepts a valid order.created webhook, persists an Order row, and queues reminder jobs', function () {
    // Allow ProcessWebhook to run synchronously; fake only the reminder jobs.
    Queue::fake()->except([ProcessWebhook::class]);

    $user = User::factory()->create();

    $payload = [
        'type' => 'order.created',
        'timestamp' => now()->toIso8601String(),
        'data' => [
            'id' => 'order_test_' . uniqid(),
            'status' => 'paid',
            'amount' => 2900,
            'tax_amount' => 0,
            'refunded_amount' => 0,
            'refunded_tax_amount' => 0,
            'currency' => 'EUR',
            'billing_reason' => 'purchase',
            'customer_id' => 'cust_test_' . uniqid(),
            'product_id' => config('polar.products.standard'),
            'created_at' => now()->toIso8601String(),
            'customer' => [
                'metadata' => [
                    'billable_id' => $user->id,
                    'billable_type' => get_class($user),
                ],
            ],
        ],
    ];

    $signed = buildSignedWebhook($payload);

    $response = $this->postJson('/polar/webhook', json_decode($signed['body'], true), $signed['headers']);

    $response->assertSuccessful();

    expect($user->orders()->count())->toBe(1);

    Queue::assertPushed(SendAccessExpiryReminder::class, 2);
});

it('rejects a webhook with an invalid signature', function () {
    $payload = [
        'type' => 'order.created',
        'timestamp' => now()->toIso8601String(),
        'data' => ['id' => 'order_test_bad'],
    ];

    $response = $this->postJson('/polar/webhook', $payload, [
        'Content-Type' => 'application/json',
        'webhook-id' => 'msg_bad',
        'webhook-timestamp' => (string) time(),
        'webhook-signature' => 'v1,invalidsignature==',
    ]);

    $response->assertStatus(500);

    expect(\Danestves\LaravelPolar\Order::count())->toBe(0);
});
