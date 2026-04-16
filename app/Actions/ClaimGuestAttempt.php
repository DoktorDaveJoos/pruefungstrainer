<?php

namespace App\Actions;

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClaimGuestAttempt
{
    public function __invoke(User $user, Request $request): ?ExamAttempt
    {
        try {
            $cookie = ExamAttemptFinder::readSessionCookie($request);

            if ($cookie === null) {
                return null;
            }

            $attempt = ExamAttempt::query()
                ->where('session_uuid', $cookie)
                ->first();

            if ($attempt === null) {
                return null;
            }

            if ($attempt->user_id === $user->id) {
                return $attempt;
            }

            if ($attempt->user_id !== null) {
                Log::warning('Refusing to claim guest attempt owned by a different user', [
                    'attempt_id' => $attempt->id,
                    'owner_id' => $attempt->user_id,
                    'requesting_user_id' => $user->id,
                ]);

                return null;
            }

            $attempt->update([
                'user_id' => $user->id,
                'claimed_at' => now(),
            ]);

            return $attempt;
        } catch (Throwable $e) {
            Log::error('ClaimGuestAttempt failed', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
