import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { cn, getCsrfToken } from '@/lib/utils';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { CheckCircle2, XCircle } from 'lucide-react';
import { useState } from 'react';

type Option = { id: number; text: string };
type Question = {
    id: number;
    text: string;
    topic: string | null;
    topic_label: string | null;
    options: Option[];
};

type Progress = {
    seen: number;
    total: number;
    correct: number;
};

type Feedback = {
    is_correct: boolean;
    correct_option_ids: number[];
    explanation: string;
    quote: string | null;
    source: string | null;
};

export default function FreiesLernen({
    question,
    wrongOnly,
    progress,
}: {
    question: Question | null;
    wrongOnly: boolean;
    progress: Progress;
}) {
    const [selected, setSelected] = useState<number[]>([]);
    const [feedback, setFeedback] = useState<Feedback | null>(null);
    const [submitting, setSubmitting] = useState(false);

    const toggleOption = (id: number) => {
        if (feedback) return;
        setSelected((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]));
    };

    const submit = async () => {
        if (!question || submitting) return;
        setSubmitting(true);
        try {
            const csrf = getCsrfToken();
            const res = await axios.post(
                '/freies-lernen/answer',
                { question_id: question.id, selected_option_ids: selected },
                { headers: { 'X-CSRF-TOKEN': csrf } },
            );
            setFeedback(res.data);
        } finally {
            setSubmitting(false);
        }
    };

    const next = () => {
        const params = new URLSearchParams();
        if (wrongOnly) params.set('wrong_only', '1');
        if (question) params.set('exclude', String(question.id));
        router.visit(`/freies-lernen?${params.toString()}`);
    };

    const toggleWrongOnly = (checked: boolean) => {
        const params = new URLSearchParams();
        if (checked) params.set('wrong_only', '1');
        router.visit(`/freies-lernen?${params.toString()}`);
    };

    const accuracy = progress.seen > 0 ? Math.round((progress.correct / progress.seen) * 100) : 0;

    const optionStyle = (optionId: number): string => {
        if (!feedback) {
            return selected.includes(optionId) ? 'border-primary bg-primary/5' : 'border-border';
        }
        const isCorrect = feedback.correct_option_ids.includes(optionId);
        const wasSelected = selected.includes(optionId);

        if (isCorrect && wasSelected) return 'border-success bg-success/10';
        if (isCorrect && !wasSelected) return 'border-success bg-success/5';
        if (!isCorrect && wasSelected) return 'border-destructive bg-destructive/10';
        return 'border-border';
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Freies Lernen', href: '/freies-lernen' }]}>
            <Head title="Freies Lernen" />

            <div className="mx-auto max-w-2xl px-6 py-8">
                <header className="flex items-center justify-between gap-4">
                    <div className="text-sm text-muted-foreground tabular-nums">
                        {progress.seen} von {progress.total} gesehen · {accuracy} % korrekt
                    </div>
                    <Label className="flex cursor-pointer items-center gap-2 text-sm">
                        <Switch checked={wrongOnly} onCheckedChange={toggleWrongOnly} />
                        Nur falsch beantwortete
                    </Label>
                </header>

                {question === null ? (
                    <Card className="mt-8">
                        <CardContent className="py-12 text-center text-muted-foreground">
                            {wrongOnly
                                ? 'Keine falsch beantworteten Fragen vorhanden. Schalte den Filter aus, um neue Fragen zu üben.'
                                : 'Keine Fragen verfügbar.'}
                        </CardContent>
                    </Card>
                ) : (
                    <>
                        <Card className="mt-8">
                            <CardHeader>
                                <div className="text-lg leading-relaxed">{question.text}</div>
                                {question.topic_label && (
                                    <div className="text-xs text-muted-foreground">{question.topic_label}</div>
                                )}
                            </CardHeader>
                            <CardContent className="flex flex-col gap-3">
                                {question.options.map((option) => (
                                    <Label
                                        key={option.id}
                                        className={cn(
                                            'flex cursor-pointer items-start gap-3 rounded-md border p-3 transition-colors',
                                            optionStyle(option.id),
                                            feedback && 'cursor-default',
                                        )}
                                    >
                                        <Checkbox
                                            checked={selected.includes(option.id)}
                                            onCheckedChange={() => toggleOption(option.id)}
                                            disabled={feedback !== null}
                                        />
                                        <span className="text-base leading-relaxed">{option.text}</span>
                                        {feedback && feedback.correct_option_ids.includes(option.id) && (
                                            <CheckCircle2 className="ml-auto size-4 text-success" />
                                        )}
                                        {feedback && !feedback.correct_option_ids.includes(option.id) && selected.includes(option.id) && (
                                            <XCircle className="ml-auto size-4 text-destructive" />
                                        )}
                                    </Label>
                                ))}
                            </CardContent>
                        </Card>

                        {feedback && (
                            <Card className="mt-4 border-border">
                                <CardContent className="space-y-3 py-6">
                                    <div className={cn('text-sm font-medium', feedback.is_correct ? 'text-success' : 'text-destructive')}>
                                        {feedback.is_correct ? '✓ Richtig' : '✗ Falsch'}
                                    </div>
                                    <p className="text-sm leading-relaxed">{feedback.explanation}</p>
                                    {feedback.quote && (
                                        <blockquote className="border-l-2 border-border pl-3 text-sm italic text-muted-foreground">
                                            {feedback.quote}
                                        </blockquote>
                                    )}
                                    {feedback.source && <div className="text-xs text-muted-foreground">Quelle: {feedback.source}</div>}
                                </CardContent>
                            </Card>
                        )}

                        <div className="mt-6 flex justify-end">
                            {feedback ? (
                                <Button onClick={next}>Nächste Frage</Button>
                            ) : (
                                <Button onClick={submit} disabled={selected.length === 0 || submitting}>
                                    Antwort prüfen
                                </Button>
                            )}
                        </div>
                    </>
                )}
            </div>
        </AppLayout>
    );
}
