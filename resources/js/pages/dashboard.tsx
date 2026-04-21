import { Head, Link, router } from '@inertiajs/react';
import { ChevronRight, Clock, FileCheck, GraduationCap } from 'lucide-react';
import type { ReactNode } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Empty, EmptyDescription, EmptyHeader, EmptyTitle } from '@/components/ui/empty';
import { Separator } from '@/components/ui/separator';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { cn, getCsrfToken } from '@/lib/utils';
import exam from '@/routes/exam';

type ExamAttemptRow = {
    id: number;
    score: number;
    total_questions: number;
    passed: boolean;
    submitted_at: string | null;
};

type DashboardProps = {
    attempts: ExamAttemptRow[];
    runningAttemptId: number | null;
    totalAnswered: number;
    readinessPercent: number | null;
    readinessAttempts: number;
};

const dateFormatter = new Intl.DateTimeFormat('de-DE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

const numberFormatter = new Intl.NumberFormat('de-DE');

function formatSubmittedAt(iso: string | null): string {
    if (iso === null) {
        return '—';
    }
    return dateFormatter.format(new Date(iso));
}

function StatCell({
    value,
    label,
    valueClassName,
}: {
    value: ReactNode;
    label: ReactNode;
    valueClassName?: string;
}) {
    return (
        <div className="flex flex-1 flex-col gap-2 p-6">
            <span className={cn('text-2xl font-semibold tabular-nums', valueClassName)}>
                {value}
            </span>
            <span className="text-sm text-muted-foreground">{label}</span>
        </div>
    );
}

function ReadinessCell({
    percent,
    windowSize,
}: {
    percent: number | null;
    windowSize: number;
}) {
    if (percent === null) {
        return (
            <div className="flex flex-1 flex-col gap-2 p-6">
                <span className="text-sm font-medium">Noch keine Simulation</span>
                <span className="text-sm text-muted-foreground">
                    Starte deine erste Prüfungssimulation, um deinen Vorbereitungsgrad zu messen.
                </span>
            </div>
        );
    }

    const windowLabel = windowSize === 1 ? '1 Simulation' : `${windowSize} Simulationen`;

    return (
        <StatCell
            value={`${percent} %`}
            label={`Vorbereitungsgrad · Ø letzte ${windowLabel}`}
            valueClassName={percent >= 60 ? 'text-success' : 'text-warning'}
        />
    );
}

export default function Dashboard({
    attempts,
    runningAttemptId,
    totalAnswered,
    readinessPercent,
    readinessAttempts,
}: DashboardProps) {
    return (
        <>
            <Head title="Startseite" />

            <div className="mx-auto max-w-4xl px-6 py-8">
                <h1 className="text-3xl font-bold tracking-tight">Willkommen zurück</h1>
                <p className="mt-2 text-muted-foreground">
                    Wähle einen Modus, um weiterzulernen.
                </p>

                <Card className="mt-8 p-0">
                    <div className="flex flex-col sm:flex-row">
                        <StatCell
                            value={numberFormatter.format(totalAnswered)}
                            label="Fragen beantwortet"
                        />
                        <Separator
                            orientation="horizontal"
                            className="sm:hidden"
                        />
                        <Separator
                            orientation="vertical"
                            className="hidden sm:block"
                        />
                        <ReadinessCell
                            percent={readinessPercent}
                            windowSize={readinessAttempts}
                        />
                    </div>
                </Card>

                <div className="mt-6 grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <FileCheck className="size-7 text-primary" />
                            <CardTitle className="mt-3">Prüfungssimulation</CardTitle>
                            <CardDescription>
                                50 Fragen, 60 Minuten, ohne Feedback — wie die echte BSI-Prüfung.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            {runningAttemptId !== null ? (
                                <Button asChild className="w-full">
                                    <Link href={exam.show.url(runningAttemptId)}>
                                        Prüfung fortsetzen
                                    </Link>
                                </Button>
                            ) : (
                                <form method="POST" action={exam.start.url()}>
                                    <input
                                        type="hidden"
                                        name="_token"
                                        value={getCsrfToken()}
                                    />
                                    <Button type="submit" className="w-full">
                                        Simulation starten
                                    </Button>
                                </form>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <GraduationCap className="size-7 text-primary" />
                            <CardTitle className="mt-3">Freies Lernen</CardTitle>
                            <CardDescription>
                                Fragen einzeln mit sofortigem Feedback und Quellen-Zitat.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Button asChild variant="outline" className="w-full">
                                <Link href="/freies-lernen">Freies Lernen starten</Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                <Alert className="mt-8">
                    <Clock />
                    <AlertTitle>Bald: weitere Prüfungsmodule</AlertTitle>
                    <AlertDescription>
                        ISO 27001 Auditor, ISO 27001 Implementer und BCM &amp; Notfallvorsorge sind in Vorbereitung. Alle kommenden Module sind in deinem 12-Monats-Zugang enthalten — ohne Aufpreis.
                    </AlertDescription>
                </Alert>

                <section className="mt-8">
                    <h2 className="text-xl font-semibold tracking-tight">
                        Deine Prüfungsversuche
                    </h2>

                    {attempts.length === 0 ? (
                        <Empty className="mt-4">
                            <EmptyHeader>
                                <EmptyTitle>Noch keine abgeschlossenen Versuche</EmptyTitle>
                                <EmptyDescription>
                                    Starte deine erste Prüfungssimulation, um deinen Fortschritt hier zu sehen.
                                </EmptyDescription>
                            </EmptyHeader>
                        </Empty>
                    ) : (
                        <Card className="mt-4 overflow-hidden p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Datum</TableHead>
                                        <TableHead>Ergebnis</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="w-10" />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {attempts.map((attempt) => {
                                        const href = exam.results.url(attempt.id);

                                        return (
                                            <TableRow
                                                key={attempt.id}
                                                onClick={() => router.visit(href)}
                                                className="cursor-pointer"
                                            >
                                                <TableCell>
                                                    {formatSubmittedAt(attempt.submitted_at)}
                                                </TableCell>
                                                <TableCell className="tabular-nums">
                                                    {attempt.score} / {attempt.total_questions}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge
                                                        variant={attempt.passed ? 'success' : 'warning'}
                                                    >
                                                        {attempt.passed ? 'Bestanden' : 'Nicht bestanden'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right text-muted-foreground">
                                                    <ChevronRight className="ml-auto size-4" />
                                                </TableCell>
                                            </TableRow>
                                        );
                                    })}
                                </TableBody>
                            </Table>
                        </Card>
                    )}
                </section>
            </div>
        </>
    );
}
