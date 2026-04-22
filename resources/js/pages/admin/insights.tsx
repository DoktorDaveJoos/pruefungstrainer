import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type Overview = {
    pageviewsToday: number;
    uniqueVisitorsToday: number;
    examStartsToday: number;
    examCompletionsToday: number;
};

type DailyRow = { date: string; pageviews: number; uniqueVisitors: number };
type FunnelRow = { step: string; count: number };
type TopPageRow = { path: string; count: number };
type TopReferrerRow = { host: string; count: number };

type Props = {
    overview: Overview;
    daily: DailyRow[];
    funnel: FunnelRow[];
    topPages: TopPageRow[];
    topReferrers: TopReferrerRow[];
};

const STEP_LABELS: Record<string, string> = {
    visited_home: 'Landing visited',
    registered: 'Registered',
    paid: 'Paid',
    exam_started: 'Exam started',
    exam_completed: 'Exam completed',
};

export default function InsightsPage({
    overview,
    daily,
    funnel,
    topPages,
    topReferrers,
}: Props) {
    const funnelTop = funnel[0]?.count ?? 0;

    return (
        <>
            <Head title="Insights" />

            <div className="mx-auto flex w-full max-w-4xl flex-col gap-6 px-4 py-8 sm:px-6">
                <h1 className="text-2xl font-semibold tracking-tight">
                    Insights
                </h1>

                <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <KpiCard
                        label="Pageviews today"
                        value={overview.pageviewsToday}
                    />
                    <KpiCard
                        label="Unique visitors today"
                        value={overview.uniqueVisitorsToday}
                    />
                    <KpiCard
                        label="Exam starts today"
                        value={overview.examStartsToday}
                    />
                    <KpiCard
                        label="Exam completions today"
                        value={overview.examCompletionsToday}
                    />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Last 7 days</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date</TableHead>
                                    <TableHead className="text-right">
                                        Pageviews
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Unique visitors
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {daily.map((row) => (
                                    <TableRow key={row.date}>
                                        <TableCell>{row.date}</TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {row.pageviews}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {row.uniqueVisitors}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Funnel (last 7 days)</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-2">
                        {funnel.map((row) => {
                            const pct =
                                funnelTop > 0
                                    ? Math.round((row.count / funnelTop) * 100)
                                    : 0;
                            return (
                                <div
                                    key={row.step}
                                    className="flex items-center justify-between text-sm"
                                >
                                    <span>
                                        {STEP_LABELS[row.step] ?? row.step}
                                    </span>
                                    <span className="text-muted-foreground tabular-nums">
                                        {row.count} ({pct}%)
                                    </span>
                                </div>
                            );
                        })}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Top pages (last 7 days)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Path</TableHead>
                                    <TableHead className="text-right">
                                        Views
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {topPages.map((row) => (
                                    <TableRow key={row.path}>
                                        <TableCell className="font-mono text-xs">
                                            {row.path}
                                        </TableCell>
                                        <TableCell className="text-right tabular-nums">
                                            {row.count}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Top referrers (last 7 days)</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Host</TableHead>
                                    <TableHead className="text-right">
                                        Visits
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {topReferrers.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={2}
                                            className="text-center text-sm text-muted-foreground"
                                        >
                                            No external referrers yet.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    topReferrers.map((row) => (
                                        <TableRow key={row.host}>
                                            <TableCell>{row.host}</TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {row.count}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

function KpiCard({ label, value }: { label: string; value: number }) {
    return (
        <Card>
            <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {label}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-semibold tabular-nums">
                    {value}
                </div>
            </CardContent>
        </Card>
    );
}
