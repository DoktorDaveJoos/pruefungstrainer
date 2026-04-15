<?php

use App\Jobs\SendAccessExpiryReminder;
use App\Mail\AccessExpiryReminderMail;
use App\Models\User;
use Danestves\LaravelPolar\Order;
use Illuminate\Support\Facades\Mail;

it('sends the expiry reminder mail', function () {
    Mail::fake();
    $user = User::factory()->create();
    $order = Order::factory()->for($user, 'billable')->create(['ordered_at' => now()->subDays(350)]);

    (new SendAccessExpiryReminder($user, $order->ordered_at))->handle();

    Mail::assertQueued(AccessExpiryReminderMail::class, fn ($m) => $m->hasTo($user->email));
});

it('does not send if the user already renewed with a newer order', function () {
    Mail::fake();
    $user = User::factory()->create();
    $old = Order::factory()->for($user, 'billable')->create(['ordered_at' => now()->subDays(350)]);
    Order::factory()->for($user, 'billable')->create(['ordered_at' => now()->subDays(10)]);

    (new SendAccessExpiryReminder($user, $old->ordered_at))->handle();

    Mail::assertNothingQueued();
});
