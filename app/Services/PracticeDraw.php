<?php

namespace App\Services;

use App\Models\PracticeAnswer;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class PracticeDraw
{
    public function next(int $userId, bool $wrongOnly = false, ?int $excludeQuestionId = null): ?Question
    {
        $base = Question::query()
            ->whereNotNull('topic')
            ->when($excludeQuestionId, fn ($q) => $q->where('id', '!=', $excludeQuestionId));

        if ($wrongOnly) {
            $latestWrongQuestionIds = $this->latestWrongQuestionIds($userId);

            return $base
                ->whereIn('id', $latestWrongQuestionIds)
                ->inRandomOrder()
                ->first();
        }

        $seenIds = PracticeAnswer::where('user_id', $userId)
            ->pluck('question_id')
            ->unique()
            ->all();

        $unseen = (clone $base)
            ->whereNotIn('id', $seenIds)
            ->inRandomOrder()
            ->first();

        if ($unseen !== null) {
            return $unseen;
        }

        return $base->inRandomOrder()->first();
    }

    /**
     * @return array{seen: int, total: int, correct: int}
     */
    public function progressFor(int $userId): array
    {
        $total = Question::whereNotNull('topic')->count();
        $seen = PracticeAnswer::where('user_id', $userId)->distinct('question_id')->count('question_id');
        $correct = $this->latestCorrectQuestionCount($userId);

        return ['seen' => $seen, 'total' => $total, 'correct' => $correct];
    }

    /**
     * @return array<int>
     */
    private function latestWrongQuestionIds(int $userId): array
    {
        $latestIds = DB::table('practice_answers')
            ->selectRaw('MAX(id) as id')
            ->where('user_id', $userId)
            ->groupBy('question_id')
            ->pluck('id');

        return PracticeAnswer::whereIn('id', $latestIds)
            ->where('is_correct', false)
            ->pluck('question_id')
            ->all();
    }

    private function latestCorrectQuestionCount(int $userId): int
    {
        $latestIds = DB::table('practice_answers')
            ->selectRaw('MAX(id) as id')
            ->where('user_id', $userId)
            ->groupBy('question_id')
            ->pluck('id');

        return PracticeAnswer::whereIn('id', $latestIds)
            ->where('is_correct', true)
            ->count();
    }
}
