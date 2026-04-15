<?php

namespace App\Listeners;

use App\Jobs\SendAccessExpiryReminder;
use App\Mail\AccessRefundedMail;
use Danestves\LaravelPolar\Events\OrderCreated;
use Danestves\LaravelPolar\Events\OrderUpdated;
use Illuminate\Support\Facades\Mail;

class PolarEventListener
{
    public function handleOrderCreated(OrderCreated $event): void
    {
        $user = $event->billable;
        $order = $event->order;

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
