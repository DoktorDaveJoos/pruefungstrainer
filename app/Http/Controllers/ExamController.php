<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use App\Services\ExamAttemptFinder;
use App\Services\ExamDraw;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamDraw $examDraw,
        private readonly ExamAttemptFinder $finder,
    ) {}

    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();
        $sessionUuid = $user ? null : (string) Str::uuid();

        $questions = $this->examDraw->draw(userId: $user?->id, total: 50);

        $attempt = DB::transaction(function () use ($user, $sessionUuid, $questions) {
            $startedAt = now();

            $attempt = ExamAttempt::create([
                'user_id' => $user?->id,
                'session_uuid' => $sessionUuid,
                'started_at' => $startedAt,
                'timer_expires_at' => $startedAt->copy()->addMinutes(60),
                'total_questions' => $questions->count(),
                'is_free_attempt' => $user === null,
            ]);

            foreach ($questions as $i => $question) {
                $attempt->examAnswers()->create([
                    'question_id' => $question->id,
                    'position' => $i + 1,
                ]);
            }

            return $attempt;
        });

        $response = redirect("/pruefungssimulation/{$attempt->id}");

        if ($sessionUuid !== null) {
            $response->withCookie(Cookie::make(
                ExamAttemptFinder::SESSION_COOKIE,
                $sessionUuid,
                minutes: 60 * 24,
            ));
        }

        return $response;
    }
}
