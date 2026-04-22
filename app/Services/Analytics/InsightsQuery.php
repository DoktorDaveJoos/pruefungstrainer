<?php

namespace App\Services\Analytics;

use App\Models\PageView;
use App\Models\TrackedEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class InsightsQuery
{
    /**
     * @return array{
     *     pageviewsToday: int,
     *     uniqueVisitorsToday: int,
     *     examStartsToday: int,
     *     examCompletionsToday: int,
     * }
     */
    public function overview(): array
    {
        $startOfToday = Carbon::now()->startOfDay();

        return [
            'pageviewsToday' => PageView::where('created_at', '>=', $startOfToday)->count(),
            'uniqueVisitorsToday' => PageView::where('created_at', '>=', $startOfToday)
                ->distinct('visitor_hash')
                ->count('visitor_hash'),
            'examStartsToday' => TrackedEvent::where('name', 'exam_started')
                ->where('created_at', '>=', $startOfToday)->count(),
            'examCompletionsToday' => TrackedEvent::where('name', 'exam_completed')
                ->where('created_at', '>=', $startOfToday)->count(),
        ];
    }

    /**
     * @return Collection<int, array{date: string, pageviews: int, uniqueVisitors: int}>
     */
    public function daily(int $days = 7): Collection
    {
        $start = Carbon::now()->startOfDay()->subDays($days - 1);

        $rows = PageView::selectRaw('date(created_at) as day, count(*) as pageviews, count(distinct visitor_hash) as unique_visitors')
            ->where('created_at', '>=', $start)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $byDay = $rows->keyBy('day');

        return collect(range(0, $days - 1))->map(function (int $offset) use ($start, $byDay): array {
            $day = $start->copy()->addDays($offset)->toDateString();
            $row = $byDay->get($day);

            return [
                'date' => $day,
                'pageviews' => (int) ($row->pageviews ?? 0),
                'uniqueVisitors' => (int) ($row->unique_visitors ?? 0),
            ];
        });
    }

    /**
     * @return array<int, array{step: string, count: int}>
     */
    public function funnel(int $days = 7): array
    {
        $start = Carbon::now()->startOfDay()->subDays($days - 1);

        $landing = PageView::where('path', '/')
            ->where('created_at', '>=', $start)->count();

        $registered = TrackedEvent::where('name', 'registered')
            ->where('created_at', '>=', $start)->count();

        $paid = TrackedEvent::where('name', 'paid')
            ->where('created_at', '>=', $start)->count();

        $examStarted = TrackedEvent::where('name', 'exam_started')
            ->where('created_at', '>=', $start)->count();

        $examCompleted = TrackedEvent::where('name', 'exam_completed')
            ->where('created_at', '>=', $start)->count();

        return [
            ['step' => 'visited_home', 'count' => $landing],
            ['step' => 'registered', 'count' => $registered],
            ['step' => 'paid', 'count' => $paid],
            ['step' => 'exam_started', 'count' => $examStarted],
            ['step' => 'exam_completed', 'count' => $examCompleted],
        ];
    }

    /**
     * @return Collection<int, array{path: string, count: int}>
     */
    public function topPages(int $days = 7, int $limit = 10): Collection
    {
        $start = Carbon::now()->startOfDay()->subDays($days - 1);

        return PageView::selectRaw('path, count(*) as c')
            ->where('created_at', '>=', $start)
            ->groupBy('path')
            ->orderByDesc('c')
            ->limit($limit)
            ->get()
            ->map(fn ($r): array => ['path' => $r->path, 'count' => (int) $r->c]);
    }

    /**
     * @return Collection<int, array{host: string, count: int}>
     */
    public function topReferrers(int $days = 7, int $limit = 10): Collection
    {
        $start = Carbon::now()->startOfDay()->subDays($days - 1);

        return PageView::selectRaw('referrer_host as host, count(*) as c')
            ->where('created_at', '>=', $start)
            ->whereNotNull('referrer_host')
            ->groupBy('referrer_host')
            ->orderByDesc('c')
            ->limit($limit)
            ->get()
            ->map(fn ($r): array => ['host' => $r->host, 'count' => (int) $r->c]);
    }
}
