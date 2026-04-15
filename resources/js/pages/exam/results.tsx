import { Head } from '@inertiajs/react';
import { AnswerReviewRow } from '@/components/exam/answer-review-row';
import { ScoreHero } from '@/components/exam/score-hero';
import { TopicBreakdownRow } from '@/components/exam/topic-breakdown-row';
import Heading from '@/components/heading';
import { LockedPreview } from '@/components/locked-preview';

type Attempt = {
    id: number;
    score: number;
    total_questions: number;
    passed: boolean;
    submitted_at: string | null;
    is_claimed: boolean;
};

type TopicBreakdown = Array<{
    key: string;
    label: string;
    correct: number;
    total: number;
}>;

type Pricing = {
    amount_eur: number;
};

export default function ExamResults({
    attempt,
    topicBreakdown,
    pricing,
}: {
    attempt: Attempt;
    topicBreakdown: TopicBreakdown;
    pricing?: Pricing;
}) {
    const priceLabel = pricing ? `${pricing.amount_eur} €` : '29 €';

    return (
        <>
            <Head
                title={`Ergebnis: ${attempt.score} / ${attempt.total_questions}`}
            />

            <div className="min-h-screen bg-background">
                <main className="mx-auto flex max-w-2xl flex-col gap-8 px-4 py-8 sm:px-6">
                    <ScoreHero
                        score={attempt.score}
                        total={attempt.total_questions}
                        passed={attempt.passed}
                    />

                    <section className="flex flex-col gap-4">
                        <Heading
                            variant="small"
                            title="Themen-Übersicht"
                            description="Wo bist du stark, wo schwach?"
                        />

                        <div className="flex flex-col gap-2">
                            {topicBreakdown.map(
                                ({ key, label, correct, total }) => (
                                    <TopicBreakdownRow
                                        key={key}
                                        label={label}
                                        correct={correct}
                                        total={total}
                                    />
                                ),
                            )}
                        </div>
                    </section>

                    <section>
                        <LockedPreview priceLabel={priceLabel} attemptId={attempt.id}>
                            <div className="flex flex-col gap-4">
                                <div className="flex flex-col gap-2">
                                    <div className="text-sm font-medium">
                                        Frage 12
                                    </div>
                                    <div className="text-sm text-muted-foreground">
                                        Welche Schutzbedarfskategorien kennt der
                                        IT-Grundschutz?
                                    </div>
                                    <AnswerReviewRow
                                        label="Deine Antwort"
                                        answer="Normal, Hoch, Kritisch"
                                        isCorrect={false}
                                    />
                                    <AnswerReviewRow
                                        label="Richtig"
                                        answer="Normal, Hoch, Sehr hoch"
                                        isCorrect={true}
                                    />
                                </div>
                                <div className="flex flex-col gap-2">
                                    <div className="text-sm font-medium">
                                        Frage 27
                                    </div>
                                    <div className="text-sm text-muted-foreground">
                                        Was unterscheidet die integrierte
                                        Risikobewertung im IT-Grundschutz von
                                        einer klassischen Risikoanalyse?
                                    </div>
                                </div>
                            </div>
                        </LockedPreview>
                    </section>

                    <p className="text-center text-sm text-muted-foreground">
                        Die Simulation orientiert sich am offiziellen
                        BSI-Prüfungsformat (50 Fragen, 60 Minuten, 60 %
                        Bestehensgrenze). Basis/Experte-Einteilung basiert auf
                        eigener Klassifikation, da BSI die offizielle Verteilung
                        nicht veröffentlicht.
                    </p>
                </main>
            </div>
        </>
    );
}
