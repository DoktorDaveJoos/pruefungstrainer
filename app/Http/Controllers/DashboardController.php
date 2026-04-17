<?php

namespace App\Http\Controllers;

use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Inertia\Response;

class DashboardController extends Controller
{
    private const PASS_THRESHOLD = 0.60;

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
        ]);
    }
}
