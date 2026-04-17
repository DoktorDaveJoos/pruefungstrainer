<?php

namespace App\Console\Commands;

use App\Console\Commands\Questions\Balance;
use App\Models\Question;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('exam:flag-free-tier {--ids= : Comma-separated question IDs to flag} {--count=50 : How many to flag if --ids is omitted}')]
#[Description('Reset is_free_tier on all questions and flag the chosen 50 as the guest pool, distributed across topics per the Balance blueprint.')]
class FlagFreeTierQuestions extends Command
{
    public function handle(): int
    {
        Question::query()->update(['is_free_tier' => false]);

        $ids = $this->resolveIds();

        if ($ids === []) {
            $this->error('No questions available to flag. Seed questions first.');

            return self::FAILURE;
        }

        $flagged = Question::query()->whereIn('id', $ids)->update(['is_free_tier' => true]);

        $this->info("Flagged {$flagged} question(s) as is_free_tier.");

        $expected = count($ids);
        if ($flagged !== $expected) {
            $this->warn("Expected {$expected}, but only {$flagged} matched. Check your --ids list.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, int>
     */
    private function resolveIds(): array
    {
        $explicit = $this->option('ids');

        if ($explicit !== null) {
            return collect(explode(',', $explicit))
                ->map(fn (string $id) => (int) trim($id))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $count = (int) $this->option('count');

        return $this->drawDistributed($count);
    }

    /**
     * Pick `$total` question IDs distributed across topics per Balance blueprint,
     * using largest-remainder rounding. Fills any topic shortfall from the remaining pool.
     *
     * @return array<int, int>
     */
    private function drawDistributed(int $total): array
    {
        $allocation = $this->computeAllocation($total);

        $ids = [];

        foreach ($allocation as $topic => $n) {
            if ($n <= 0) {
                continue;
            }

            $picked = Question::query()
                ->where('topic', $topic)
                ->orderBy('id')
                ->limit($n)
                ->pluck('id')
                ->all();

            $ids = array_merge($ids, $picked);
        }

        $shortfall = $total - count($ids);

        if ($shortfall > 0) {
            $fill = Question::query()
                ->whereNotIn('id', $ids ?: [0])
                ->orderBy('id')
                ->limit($shortfall)
                ->pluck('id')
                ->all();

            $ids = array_merge($ids, $fill);
        }

        return $ids;
    }

    /**
     * Largest-remainder rounding of Balance::REFERENCE_TOPIC_PCT to a total of `$total`.
     *
     * @return array<string, int>
     */
    private function computeAllocation(int $total): array
    {
        $pct = Balance::REFERENCE_TOPIC_PCT;

        $floors = [];
        $remainders = [];
        foreach ($pct as $topic => $p) {
            $ideal = $total * $p / 100;
            $floors[$topic] = (int) floor($ideal);
            $remainders[$topic] = $ideal - $floors[$topic];
        }

        $toAdd = $total - array_sum($floors);

        $topics = array_keys($pct);
        usort($topics, fn (string $a, string $b): int => [$remainders[$b], $floors[$b]] <=> [$remainders[$a], $floors[$a]]);

        foreach (array_slice($topics, 0, $toAdd) as $topic) {
            $floors[$topic]++;
        }

        return $floors;
    }
}
