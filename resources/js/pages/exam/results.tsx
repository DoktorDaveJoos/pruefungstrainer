import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, ChevronLeft, ChevronRight, CheckCheck } from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';
import { ReviewItemCard } from '@/components/exam/review-item-card';
import type { ReviewItem } from '@/components/exam/review-item-card';
import { ScoreHero } from '@/components/exam/score-hero';
import { TopicBreakdownRow } from '@/components/exam/topic-breakdown-row';
import Heading from '@/components/heading';
import { LockedPreview } from '@/components/locked-preview';
import { Button } from '@/components/ui/button';
import {
    Empty,
    EmptyDescription,
    EmptyHeader,
    EmptyMedia,
    EmptyTitle,
} from '@/components/ui/empty';
import { dashboard } from '@/routes';

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

const LOCKED_TEASER: ReviewItem = {
    number: 12,
    topic: 'Bausteine',
    stem: 'Welche Schutzbedarfskategorien kennt der IT-Grundschutz?',
    explanation:
        'Der IT-Grundschutz unterscheidet drei Schutzbedarfskategorien anhand der möglichen Schadensauswirkungen auf Verfügbarkeit, Integrität und Vertraulichkeit.',
    quote: 'Die Schutzbedarfskategorien sind normal, hoch und sehr hoch.',
    source: 'BSI-Standard 200-2, Kapitel 8.2',
    options: [
        { text: 'Normal, Hoch, Sehr hoch', isCorrect: true, isUserChoice: false },
        { text: 'Normal, Hoch, Kritisch', isCorrect: false, isUserChoice: true },
        { text: 'Niedrig, Mittel, Hoch', isCorrect: false, isUserChoice: false },
        { text: 'Gering, Wesentlich, Existenziell', isCorrect: false, isUserChoice: false },
    ],
};

function ReviewPager({ items }: { items: ReviewItem[] }) {
    const [index, setIndex] = useState(0);
    const total = items.length;
    const safeIndex = Math.min(index, total - 1);
    const current = items[safeIndex];
    const goPrev = useCallback(() => setIndex((i) => Math.max(0, i - 1)), []);
    const goNext = useCallback(
        () => setIndex((i) => Math.min(total - 1, i + 1)),
        [total],
    );

    useEffect(() => {
        if (total <= 1) {
            return;
        }

        const onKey = (e: KeyboardEvent) => {
            const target = e.target as HTMLElement | null;

            if (target?.matches('input, textarea, [contenteditable="true"]')) {
                return;
            }

            if (e.key === 'ArrowLeft') {
                goPrev();
            } else if (e.key === 'ArrowRight') {
                goNext();
            }
        };

        window.addEventListener('keydown', onKey);

        return () => window.removeEventListener('keydown', onKey);
    }, [total, goPrev, goNext]);

    return (
        <div className="flex flex-col gap-4">
            <ReviewItemCard key={current.number} {...current} />

            <div className="flex items-center justify-between">
                <Button
                    type="button"
                    variant="outline"
                    disabled={safeIndex === 0}
                    onClick={goPrev}
                >
                    <ChevronLeft className="size-4" />
                    Zurück
                </Button>

                <span className="text-sm tabular-nums text-muted-foreground">
                    {safeIndex + 1} von {total}
                </span>

                <Button
                    type="button"
                    variant="outline"
                    disabled={safeIndex === total - 1}
                    onClick={goNext}
                >
                    Weiter
                    <ChevronRight className="size-4" />
                </Button>
            </div>
        </div>
    );
}

export default function ExamResults({
    attempt,
    topicBreakdown,
    pricing,
    hasAccess,
    reviewItems,
}: {
    attempt: Attempt;
    topicBreakdown: TopicBreakdown;
    pricing: Pricing;
    hasAccess: boolean;
    reviewItems: ReviewItem[] | null;
}) {
    const priceLabel = `${pricing.amount_eur} €`;
    const showEmptyState = reviewItems !== null && reviewItems.length === 0;

    return (
        <>
            <Head
                title={`Ergebnis: ${attempt.score} / ${attempt.total_questions}`}
            />

            <div className="min-h-screen bg-background">
                <main className="mx-auto flex max-w-2xl flex-col gap-8 px-4 py-8 sm:px-6">
                    <Link
                        href={dashboard()}
                        className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-4" />
                        Dashboard
                    </Link>

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
                        <LockedPreview
                            priceLabel={priceLabel}
                            attemptId={attempt.id}
                            hasAccess={hasAccess}
                        >
                            {showEmptyState ? (
                                <Empty className="border-0">
                                    <EmptyHeader>
                                        <EmptyMedia variant="icon">
                                            <CheckCheck />
                                        </EmptyMedia>
                                        <EmptyTitle>
                                            Alle Fragen korrekt beantwortet
                                        </EmptyTitle>
                                        <EmptyDescription>
                                            Es gibt nichts zu reviewen — sauber durch.
                                        </EmptyDescription>
                                    </EmptyHeader>
                                </Empty>
                            ) : reviewItems === null ? (
                                <ReviewItemCard {...LOCKED_TEASER} />
                            ) : (
                                <ReviewPager items={reviewItems} />
                            )}
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
