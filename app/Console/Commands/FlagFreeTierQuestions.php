<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('exam:flag-free-tier {--ids= : Comma-separated question IDs to flag} {--count=50 : How many to flag if --ids is omitted}')]
#[Description('Reset is_free_tier on all questions and flag the chosen 50 as the guest pool.')]
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

        return Question::query()
            ->orderBy('id')
            ->limit($count)
            ->pluck('id')
            ->all();
    }
}
