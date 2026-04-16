<?php

namespace App\Services;

use App\Models\ExamAttempt;
use Illuminate\Http\Request;

class GuestStartGuard
{
    public function inspect(Request $request): GuestStartState
    {
        $cookie = ExamAttemptFinder::readSessionCookie($request);

        if ($cookie === null) {
            return GuestStartState::available();
        }

        $attempt = ExamAttempt::query()
            ->where('session_uuid', $cookie)
            ->first();

        if ($attempt === null) {
            return GuestStartState::available();
        }

        if ($attempt->isSubmitted()) {
            return GuestStartState::alreadyDone($attempt->id);
        }

        return GuestStartState::resume($attempt->id);
    }
}
