<?php

namespace App\Console\Commands;

use App\Models\PageView;
use App\Models\TrackedEvent;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneAnalyticsCommand extends Command
{
    protected $signature = 'analytics:prune {--days=90}';

    protected $description = 'Delete analytics rows older than N days (default 90).';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $pageViews = PageView::where('created_at', '<', $cutoff)->delete();
        $events = TrackedEvent::where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$pageViews} page views and {$events} events older than {$days} days.");

        return self::SUCCESS;
    }
}
