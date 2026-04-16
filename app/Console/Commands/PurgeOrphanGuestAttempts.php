<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('exam:purge-orphan-attempts {--days=90 : Delete unclaimed guest attempts older than this many days}')]
#[Description('Delete unclaimed guest exam attempts older than N days (default 90). Cascade-deletes their answers.')]
class PurgeOrphanGuestAttempts extends Command
{
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = ExamAttempt::query()
            ->whereNull('user_id')
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$deleted} orphan guest attempt(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
