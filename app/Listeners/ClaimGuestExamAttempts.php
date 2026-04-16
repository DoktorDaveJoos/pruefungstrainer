<?php

namespace App\Listeners;

use App\Models\ExamAttempt;
use App\Services\ExamAttemptFinder;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;

class ClaimGuestExamAttempts
{
    public function handleRegistered(Registered $event): void
    {
        $this->claim($event->user->id);
    }

    public function handleLogin(Login $event): void
    {
        $this->claim($event->user->id);
    }

    private function claim(int $userId): void
    {
        $sessionUuid = request()->cookie(ExamAttemptFinder::SESSION_COOKIE);

        if (! $sessionUuid) {
            return;
        }

        ExamAttempt::query()
            ->where('session_uuid', $sessionUuid)
            ->whereNull('user_id')
            ->update(['user_id' => $userId]);
    }
}
