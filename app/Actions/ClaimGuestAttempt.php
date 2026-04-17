<?php

namespace App\Actions;

use App\Http\Controllers\CheckoutController;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Throwable;

class ClaimGuestAttempt
{
    /**
     * Session key that remembers the most-recently-claimed attempt ID.
     * Read by {@see CheckoutController::resolveRedirectTo()}
     * so the post-Polar redirect lands on the exam the user just bought,
     * even if the `pt_exam_session` cookie is stripped on the roundtrip
     * through Polar (SameSite, cross-origin redirects, etc.).
     */
    public const SESSION_KEY = 'claimed_attempt_id';

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
                $this->rememberInSession($request, $attempt);
                $this->forgetGuestCookie();

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

            $this->rememberInSession($request, $attempt);
            $this->forgetGuestCookie();

            return $attempt;
        } catch (Throwable $e) {
            Log::error('ClaimGuestAttempt failed', [
                'user_id' => $user->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function rememberInSession(Request $request, ExamAttempt $attempt): void
    {
        if (! $attempt->isSubmitted()) {
            return;
        }

        if (! $request->hasSession()) {
            return;
        }

        $request->session()->put(self::SESSION_KEY, $attempt->id);
    }

    /**
     * The pt_exam_session cookie has done its job once the attempt is claimed.
     * Drop it so a future logged-out visitor on the same browser can start a
     * fresh guest exam without being bounced to the now-owned attempt.
     */
    private function forgetGuestCookie(): void
    {
        Cookie::queue(Cookie::forget(ExamAttemptFinder::SESSION_COOKIE));
    }
}
