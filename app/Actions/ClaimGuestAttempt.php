<?php

namespace App\Actions;

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
     * Survives Polar's cross-origin roundtrip, which can strip SameSite=Lax
     * cookies in some browsers and would otherwise leave the processing page
     * unable to locate the just-claimed attempt via `pt_exam_session`.
     * Prefer {@see self::rememberedAttemptId()} in production code; the
     * constant is public so tests can pre-seed the session.
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

    public static function rememberedAttemptId(Request $request): ?int
    {
        if (! $request->hasSession()) {
            return null;
        }

        $id = $request->session()->get(self::SESSION_KEY);

        return $id !== null ? (int) $id : null;
    }

    /**
     * Session-remembered first (survives Polar's cross-origin hop), DB
     * fallback second. Both post-payment and post-verification redirects
     * go through this, so changes to "which attempt counts as claimed"
     * stay in one place.
     */
    public static function resolveClaimedAttemptId(User $user, Request $request): ?int
    {
        return self::rememberedAttemptId($request)
            ?? ExamAttempt::query()
                ->where('user_id', $user->id)
                ->whereNotNull('claimed_at')
                ->whereNotNull('submitted_at')
                ->latest('claimed_at')
                ->value('id');
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
