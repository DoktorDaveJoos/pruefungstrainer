<?php

namespace App\Listeners;

use App\Jobs\SendAccessExpiryReminder;
use App\Mail\AccessRefundedMail;
use App\Services\Analytics\RecordEvent;
use Danestves\LaravelPolar\Events\OrderCreated;
use Danestves\LaravelPolar\Events\OrderUpdated;
use Illuminate\Support\Facades\Mail;

/**
 * No `ShouldQueue` here on purpose: the package's `ProcessWebhook` job
 * (Spatie webhook-client) is what actually queues. By the time we run, the
 * webhook ack has already been sent. This handler only dispatches further
 * jobs/mails, which queue themselves.
 */
class PolarEventListener
{
    public function __construct(private readonly RecordEvent $events) {}

    public function handleOrderCreated(OrderCreated $event): void
    {
        $user = $event->billable;
        $order = $event->order;

        $this->events->record('paid', metadata: [
            'order_id' => $order->id,
        ], includeVisitorHash: false);

        SendAccessExpiryReminder::dispatch($user, $order->ordered_at)
            ->delay($order->ordered_at->addYear()->subDays(14));

        SendAccessExpiryReminder::dispatch($user, $order->ordered_at)
            ->delay($order->ordered_at->addYear()->subDay());
    }

    public function handleOrderUpdated(OrderUpdated $event): void
    {
        if (! $event->isRefunded) {
            return;
        }

        Mail::to($event->billable->email)->queue(new AccessRefundedMail($event->billable));
    }
}
