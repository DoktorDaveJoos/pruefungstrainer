<?php

namespace App\Jobs;

use App\Mail\AccessExpiryReminderMail;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAccessExpiryReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public User $user, public CarbonInterface $originalOrderedAt) {}

    public function handle(): void
    {
        $newerOrderExists = $this->user->orders()
            ->where('ordered_at', '>', $this->originalOrderedAt)
            ->whereNull('refunded_at')
            ->exists();

        if ($newerOrderExists) {
            return;
        }

        Mail::to($this->user->email)
            ->queue(new AccessExpiryReminderMail(
                $this->user,
                $this->originalOrderedAt->addYear()
            ));
    }
}
