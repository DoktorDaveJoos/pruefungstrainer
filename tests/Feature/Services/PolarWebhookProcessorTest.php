<?php

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\PolarWebhookProcessor;

beforeEach(function () {
    config()->set('services.polar.webhook_secret', 'whsec_test');
});

it('verifies a valid HMAC-SHA-256 signature', function () {
    $payload = '{"type":"order.created"}';
    $signature = hash_hmac('sha256', $payload, 'whsec_test');

    expect((new PolarWebhookProcessor)->verifySignature($payload, $signature))->toBeTrue();
});

it('rejects an invalid signature', function () {
    $payload = '{"type":"order.created"}';

    expect((new PolarWebhookProcessor)->verifySignature($payload, 'wrong_signature'))->toBeFalse();
});

it('rejects when no webhook secret is configured', function () {
    config()->set('services.polar.webhook_secret', null);

    expect((new PolarWebhookProcessor)->verifySignature('payload', 'sig'))->toBeFalse();
});

it('marks an existing user as paid via metadata.user_id', function () {
    $user = User::factory()->create();

    $payload = [
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_polar_abc', 'email' => $user->email],
            'metadata' => ['user_id' => (string) $user->id],
        ],
    ];

    (new PolarWebhookProcessor)->process($payload);

    $fresh = $user->fresh();
    expect($fresh->paid_at)->not->toBeNull();
    expect($fresh->polar_customer_id)->toBe('cust_polar_abc');
});

it('creates a new user from customer email when no metadata.user_id', function () {
    $payload = [
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_polar_xyz', 'email' => 'newbie@example.com', 'name' => 'Anna Newbie'],
            'metadata' => [],
        ],
    ];

    (new PolarWebhookProcessor)->process($payload);

    $user = User::where('email', 'newbie@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->paid_at)->not->toBeNull();
    expect($user->polar_customer_id)->toBe('cust_polar_xyz');
    expect($user->name)->toBe('Anna Newbie');
});

it('claims anonymous exam attempt by session_uuid when both metadata fields present', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'sess-abc-123']);

    $payload = [
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_p', 'email' => 'claim@example.com', 'name' => 'Claim User'],
            'metadata' => ['session_uuid' => 'sess-abc-123'],
        ],
    ];

    (new PolarWebhookProcessor)->process($payload);

    $user = User::where('email', 'claim@example.com')->first();
    $fresh = $attempt->fresh();

    expect($fresh->user_id)->toBe($user->id);
});

it('is idempotent — replaying the same order does not re-mark or re-create', function () {
    $user = User::factory()->paid()->create();

    $payload = [
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_p', 'email' => $user->email],
            'metadata' => ['user_id' => (string) $user->id],
        ],
    ];

    $originalPaidAt = $user->fresh()->paid_at;

    (new PolarWebhookProcessor)->process($payload);
    (new PolarWebhookProcessor)->process($payload);

    expect(User::where('email', $user->email)->count())->toBe(1);
    expect($user->fresh()->paid_at->toIso8601String())->toBe($originalPaidAt->toIso8601String());
});

it('ignores non-order.created events without throwing', function () {
    expect(fn () => (new PolarWebhookProcessor)->process([
        'type' => 'subscription.canceled',
        'data' => [],
    ]))->not->toThrow(Throwable::class);
});
