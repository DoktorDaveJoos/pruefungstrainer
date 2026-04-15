<?php

use App\Models\ExamAttempt;
use App\Models\User;

beforeEach(function () {
    config()->set('services.polar.webhook_secret', 'whsec_test');
});

it('processes a valid order.created webhook and marks the user paid', function () {
    $user = User::factory()->create();

    $payload = json_encode([
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_p', 'email' => $user->email],
            'metadata' => ['user_id' => (string) $user->id],
        ],
    ]);

    $signature = hash_hmac('sha256', $payload, 'whsec_test');

    $response = $this->call(
        method: 'POST',
        uri: '/webhooks/polar',
        server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_WEBHOOK_SIGNATURE' => $signature,
        ],
        content: $payload,
    );

    $response->assertStatus(200);
    expect($user->fresh()->paid_at)->not->toBeNull();
});

it('rejects a webhook with bad signature (401)', function () {
    $payload = json_encode(['type' => 'order.created', 'data' => []]);

    $response = $this->call(
        method: 'POST',
        uri: '/webhooks/polar',
        server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_WEBHOOK_SIGNATURE' => 'wrong_sig',
        ],
        content: $payload,
    );

    $response->assertStatus(401);
});

it('rejects a webhook with missing signature header (401)', function () {
    $payload = json_encode(['type' => 'order.created', 'data' => []]);

    $response = $this->call(
        method: 'POST',
        uri: '/webhooks/polar',
        server: ['CONTENT_TYPE' => 'application/json'],
        content: $payload,
    );

    $response->assertStatus(401);
});

it('claims anonymous attempt via webhook metadata', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'sess-claim']);

    $payload = json_encode([
        'type' => 'order.created',
        'data' => [
            'customer' => ['id' => 'cust_p', 'email' => 'newuser@example.com', 'name' => 'New U'],
            'metadata' => ['session_uuid' => 'sess-claim'],
        ],
    ]);

    $signature = hash_hmac('sha256', $payload, 'whsec_test');

    $response = $this->call(
        method: 'POST',
        uri: '/webhooks/polar',
        server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_WEBHOOK_SIGNATURE' => $signature,
        ],
        content: $payload,
    );

    $response->assertStatus(200);

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user)->not->toBeNull();
    expect($attempt->fresh()->user_id)->toBe($user->id);
});
