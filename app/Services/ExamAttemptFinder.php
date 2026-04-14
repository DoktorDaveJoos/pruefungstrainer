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

        $cookie = $request->cookie(self::SESSION_COOKIE);

        return $cookie !== null && $cookie === $attempt->session_uuid ? $attempt : null;
    }
}
