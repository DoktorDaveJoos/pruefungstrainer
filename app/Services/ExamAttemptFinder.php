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

        // Handle both normal cookies and cookies sent via header (for JSON test requests)
        $cookie = $request->cookie(self::SESSION_COOKIE);
        if ($cookie === null) {
            $cookieHeader = $request->header('Cookie');
            if ($cookieHeader !== null && str_contains($cookieHeader, self::SESSION_COOKIE)) {
                preg_match('/'.preg_quote(self::SESSION_COOKIE).'=([^;]+)/', $cookieHeader, $matches);
                $cookie = $matches[1] ?? null;
            }
        }

        return $cookie !== null && $cookie === $attempt->session_uuid ? $attempt : null;
    }
}
