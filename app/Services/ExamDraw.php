<?php

namespace App\Services;

use App\Enums\QuestionDifficulty;
use App\Models\ExamAnswer;
use App\Models\Question;
use Illuminate\Support\Collection;

class ExamDraw
{
    public function draw(?int $userId, int $total = 50): Collection
    {
        $basisTarget = (int) round($total * 0.75);
        $experteTarget = $total - $basisTarget;

        $seenQuestionIds = $userId
            ? ExamAnswer::whereHas('examAttempt', fn ($q) => $q->where('user_id', $userId))
                ->pluck('question_id')
                ->unique()
                ->all()
            : [];

        $basis = $this->drawBucket(QuestionDifficulty::Basis, $basisTarget, $seenQuestionIds);
        $experte = $this->drawBucket(QuestionDifficulty::Experte, $experteTarget, $seenQuestionIds);

        $basisShort = $basisTarget - $basis->count();
        $experteShort = $experteTarget - $experte->count();

        if ($basisShort > 0 && $experteShort === 0) {
            $alreadyPicked = $experte->pluck('id')->all();
            $extra = $this->drawBucket(QuestionDifficulty::Experte, $basisShort, array_merge($seenQuestionIds, $alreadyPicked));
            $experte = $experte->merge($extra);
        }

        if ($experteShort > 0 && $basisShort === 0) {
            $alreadyPicked = $basis->pluck('id')->all();
            $extra = $this->drawBucket(QuestionDifficulty::Basis, $experteShort, array_merge($seenQuestionIds, $alreadyPicked));
            $basis = $basis->merge($extra);
        }

        return $basis->merge($experte)->shuffle()->values();
    }

    /**
     * @param  array<int>  $seenQuestionIds
     */
    private function drawBucket(QuestionDifficulty $difficulty, int $target, array $seenQuestionIds): Collection
    {
        if ($target <= 0) {
            return collect();
        }

        $unseen = Question::query()
            ->with('answers')
            ->where('difficulty', $difficulty)
            ->whereNotNull('topic')
            ->whereNotIn('id', $seenQuestionIds)
            ->inRandomOrder()
            ->limit($target)
            ->get();

        if ($unseen->count() >= $target) {
            return $unseen;
        }

        $remaining = $target - $unseen->count();

        $fallback = Question::query()
            ->with('answers')
            ->where('difficulty', $difficulty)
            ->whereNotNull('topic')
            ->whereIn('id', $seenQuestionIds)
            ->inRandomOrder()
            ->limit($remaining)
            ->get();

        return $unseen->merge($fallback);
    }
}
