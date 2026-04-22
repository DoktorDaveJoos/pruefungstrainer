<?php

use App\Jobs\SendAccessExpiryReminder;
use App\Mail\AccessRefundedMail;
use App\Models\TrackedEvent;
use App\Models\User;
use Danestves\LaravelPolar\Events\OrderCreated;
use Danestves\LaravelPolar\Events\OrderUpdated;
use Danestves\LaravelPolar\Order;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Polar\Models\Components\WebhookOrderCreatedPayload;
use Polar\Models\Components\WebhookOrderUpdatedPayload;

it('schedules expiry reminder jobs on OrderCreated', function () {
    Queue::fake();

    $user = User::factory()->create();
    $order = Order::factory()->for($user, 'billable')->create();

    $payload = Mockery::mock(WebhookOrderCreatedPayload::class);
    event(new OrderCreated($user, $order, $payload));

    Queue::assertPushed(SendAccessExpiryReminder::class, 2);
});

it('records a paid event attributed to the user with no visitor hash', function () {
    Queue::fake();

    $user = User::factory()->create();
    $order = Order::factory()->for($user, 'billable')->create();

    $payload = Mockery::mock(WebhookOrderCreatedPayload::class);
    event(new OrderCreated($user, $order, $payload));

    $event = TrackedEvent::where('name', 'paid')->first();
    expect($event)->not->toBeNull();
    expect($event->user_id)->toBe($user->id);
    expect($event->visitor_hash)->toBeNull();
    expect($event->metadata)->toBe(['order_id' => $order->id]);
});

it('sends a refund confirmation mail on OrderUpdated with isRefunded=true', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::factory()->for($user, 'billable')->create([
        'refunded_at' => now(),
    ]);

    $payload = Mockery::mock(WebhookOrderUpdatedPayload::class);
    event(new OrderUpdated($user, $order, $payload, isRefunded: true));

    Mail::assertQueued(AccessRefundedMail::class, fn ($mail) => $mail->hasTo($user->email));
});

it('does not send refund mail on OrderUpdated when isRefunded=false', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::factory()->for($user, 'billable')->create();

    $payload = Mockery::mock(WebhookOrderUpdatedPayload::class);
    event(new OrderUpdated($user, $order, $payload, isRefunded: false));

    Mail::assertNothingQueued();
});
