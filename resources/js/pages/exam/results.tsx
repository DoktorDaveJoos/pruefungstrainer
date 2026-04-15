import { LockedPreview } from '@/components/locked-preview';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import { Head } from '@inertiajs/react';
import { CheckCircle2, XCircle } from 'lucide-react';

type Attempt = {
    id: number;
    score: number;
    total_questions: number;
    passed: boolean;
    submitted_at: string | null;
    is_claimed: boolean;
};

type TopicBreakdown = Record<string, { correct: number; total: number }>;

const TOPIC_LABELS: Record<string, string> = {
    methodik: 'IT-Grundschutz-Methodik',
    bausteine: 'Bausteine',
    risikoanalyse: 'Risikoanalyse',
    modellierung: 'Modellierung',
    check: 'IT-Grundschutz-Check',
    standards: 'BSI-Standards',
    notfall: 'Notfallmanagement',
    siem: 'SIEM / Monitoring',
};

export default function ExamResults({
    attempt,
    topicBreakdown,
}: {
    attempt: Attempt;
    topicBreakdown: TopicBreakdown;
}) {
    const percentage = attempt.total_questions > 0
        ? Math.round((attempt.score / attempt.total_questions) * 100)
        : 0;

    return (
        <>
            <Head title={`Ergebnis: ${attempt.score} / ${attempt.total_questions}`} />

            <div className="min-h-screen bg-background">
                <main className="mx-auto max-w-3xl px-6 py-16">
                    <Card className="border-border">
                        <CardHeader className="items-center text-center">
                            <CardTitle className="text-base font-medium text-muted-foreground">Dein Ergebnis</CardTitle>
                            <div className="mt-4 text-6xl font-bold tabular-nums">
                                {attempt.score} / {attempt.total_questions}
                            </div>
                            <div className="mt-2 text-2xl text-muted-foreground tabular-nums">{percentage} %</div>
                            <div className="mt-6">
                                {attempt.passed ? (
                                    <Badge className="gap-2 bg-success text-success-foreground">
                                        <CheckCircle2 data-icon="inline-start" className="size-4" />
                                        Bestanden (≥ 60 %)
                                    </Badge>
                                ) : (
                                    <Badge className="gap-2 bg-warning text-warning-foreground">
                                        <XCircle data-icon="inline-start" className="size-4" />
                                        Unter der Bestehensgrenze
                                    </Badge>
                                )}
                            </div>
                        </CardHeader>
                    </Card>

                    <section className="mt-8">
                        <h2 className="text-lg font-semibold">Themen-Übersicht</h2>
                        <p className="text-sm text-muted-foreground">Wo bist du stark, wo schwach?</p>

                        <div className="mt-4 space-y-2">
                            {Object.entries(topicBreakdown).map(([topic, { correct, total }]) => {
                                const pct = total > 0 ? Math.round((correct / total) * 100) : 0;
                                return (
                                    <div key={topic} className="flex items-center gap-4 rounded-md border border-border p-3">
                                        <div className="flex-1">
                                            <div className="text-sm font-medium">{TOPIC_LABELS[topic] ?? topic}</div>
                                            <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-muted">
                                                <div className="h-full bg-primary" style={{ width: `${pct}%` }} />
                                            </div>
                                        </div>
                                        <div className="text-sm tabular-nums text-muted-foreground">
                                            {correct} / {total} · {pct} %
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </section>

                    <section className="mt-12">
                        <LockedPreview>
                            <div className="space-y-4">
                                <div>
                                    <div className="font-medium">Frage 12</div>
                                    <div className="text-sm text-muted-foreground">
                                        Welche Schutzbedarfskategorien kennt der IT-Grundschutz?
                                    </div>
                                    <div className="mt-1 text-sm text-destructive">✗ Deine Antwort: Normal, Hoch, Kritisch</div>
                                    <div className="text-sm text-success">✓ Richtig: Normal, Hoch, Sehr hoch</div>
                                </div>
                                <div>
                                    <div className="font-medium">Frage 27</div>
                                    <div className="text-sm text-muted-foreground">
                                        Was unterscheidet die integrierte Risikobewertung im IT-Grundschutz von einer
                                        klassischen Risikoanalyse?
                                    </div>
                                </div>
                            </div>
                        </LockedPreview>
                    </section>

                    <p className="mt-8 text-center text-sm text-muted-foreground">
                        Die Simulation orientiert sich am offiziellen BSI-Prüfungsformat (50 Fragen, 60 Minuten, 60 % Bestehensgrenze).
                        Basis/Experte-Einteilung basiert auf eigener Klassifikation, da BSI die offizielle Verteilung nicht veröffentlicht.
                    </p>
                </main>
            </div>
        </>
    );
}
