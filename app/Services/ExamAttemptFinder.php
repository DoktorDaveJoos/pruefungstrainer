<?php

namespace App\Services;

use App\Models\ExamAttempt;
use Illuminate\Http\Request;

class ExamAttemptFinder
{
    public const SESSION_COOKIE = 'pt_exam_session';

    public function find(Request $request, int $attemptId): ?ExamAttempt
    {
        $attempt = ExamAttempt::find($attemptId);

        if ($attempt === null) {
            return null;
        }

        if ($attempt->user_id !== null) {
            return $request->user()?->id === $attempt->user_id ? $attempt : null;
        }

        $cookie = self::readSessionCookie($request);

        return $cookie !== null && $cookie === $attempt->session_uuid ? $attempt : null;
    }

    /**
     * Read `pt_exam_session` from the request. Falls back to the raw `Cookie`
     * header so JSON test requests (which bypass Laravel's cookie parser) still work.
     */
    public static function readSessionCookie(Request $request): ?string
    {
        $value = $request->cookie(self::SESSION_COOKIE);

        if ($value !== null) {
            return $value;
        }

        $header = $request->header('Cookie');
        if ($header !== null && str_contains($header, self::SESSION_COOKIE)) {
            preg_match('/'.preg_quote(self::SESSION_COOKIE).'=([^;]+)/', $header, $matches);

            return $matches[1] ?? null;
        }

        return null;
    }
}
