<?php

namespace App\Http\Controllers;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\PracticeAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Response;

class DashboardController extends Controller
{
    private const PASS_THRESHOLD = 0.60;

    private const READINESS_WINDOW = 3;

    public function __invoke(Request $request): Response
    {
        $userId = $request->user()->id;

        $attempts = ExamAttempt::query()
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->get(['id', 'score', 'total_questions', 'submitted_at']);

        $runningAttempt = ExamAttempt::query()
            ->where('user_id', $userId)
            ->whereNull('submitted_at')
            ->where('timer_expires_at', '>', now())
            ->latest('started_at')
            ->first(['id']);

        $readinessWindow = $attempts->take(self::READINESS_WINDOW);

        return inertia('dashboard', [
            'attempts' => $attempts->map(function (ExamAttempt $attempt): array {
                $score = $attempt->score ?? 0;
                $total = $attempt->total_questions;

                return [
                    'id' => $attempt->id,
                    'score' => $score,
                    'total_questions' => $total,
                    'passed' => $total > 0 && ($score / $total) >= self::PASS_THRESHOLD,
                    'submitted_at' => $attempt->submitted_at?->toIso8601String(),
                ];
            })->all(),
            'runningAttemptId' => $runningAttempt?->id,
            'totalAnswered' => $this->totalAnswered($userId),
            'readinessPercent' => $this->readinessPercent($readinessWindow),
            'readinessAttempts' => $readinessWindow->count(),
        ]);
    }

    private function totalAnswered(int $userId): int
    {
        $practiceCount = PracticeAnswer::query()
            ->where('user_id', $userId)
            ->count();

        $examAnswerCount = ExamAnswer::query()
            ->whereHas('examAttempt', fn ($q) => $q
                ->where('user_id', $userId)
                ->whereNotNull('submitted_at'))
            ->whereNotNull('selected_option_ids')
            ->count();

        return $practiceCount + $examAnswerCount;
    }

    /**
     * Rolling correct-rate over the given attempt window.
     * Returns null when the window is empty.
     */
    private function readinessPercent(Collection $window): ?int
    {
        if ($window->isEmpty()) {
            return null;
        }

        $score = $window->sum(fn (ExamAttempt $attempt) => $attempt->score ?? 0);
        $total = $window->sum(fn (ExamAttempt $attempt) => $attempt->total_questions);

        if ($total === 0) {
            return null;
        }

        return (int) round(($score / $total) * 100);
    }
}
