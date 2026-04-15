import { ExamTimer } from '@/components/exam-timer';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Head, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Flag, FlagOff } from 'lucide-react';
import { useState } from 'react';

type Option = { id: number; text: string };
type Question = {
    position: number;
    question_id: number;
    text: string;
    options: Option[];
    selected_option_ids: number[];
    flagged: boolean;
};

type Attempt = {
    id: number;
    timer_expires_at: string;
    total_questions: number;
};

export default function ExamQuestion({ attempt, questions }: { attempt: Attempt; questions: Question[] }) {
    const [currentPosition, setCurrentPosition] = useState(1);
    const [state, setState] = useState(() =>
        questions.reduce<Record<number, { selected: number[]; flagged: boolean }>>((acc, q) => {
            acc[q.position] = { selected: q.selected_option_ids, flagged: q.flagged };
            return acc;
        }, {}),
    );

    const current = questions.find((q) => q.position === currentPosition)!;
    const currentState = state[currentPosition];

    const save = (nextSelected: number[], nextFlagged: boolean) => {
        router.patch(
            `/pruefungssimulation/${attempt.id}/answer/${currentPosition}`,
            { selected_option_ids: nextSelected, flagged: nextFlagged },
            { preserveScroll: true, preserveState: true, only: [] },
        );
    };

    const toggleOption = (optionId: number) => {
        const nextSelected = currentState.selected.includes(optionId)
            ? currentState.selected.filter((id) => id !== optionId)
            : [...currentState.selected, optionId];

        setState({ ...state, [currentPosition]: { ...currentState, selected: nextSelected } });
        save(nextSelected, currentState.flagged);
    };

    const toggleFlag = () => {
        const nextFlagged = !currentState.flagged;
        setState({ ...state, [currentPosition]: { ...currentState, flagged: nextFlagged } });
        save(currentState.selected, nextFlagged);
    };

    const submit = () => {
        if (!confirm('Prüfung endgültig abschicken? Danach kannst du keine Antworten mehr ändern.')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/pruefungssimulation/${attempt.id}/submit`;
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '';
        form.appendChild(tokenInput);
        document.body.appendChild(form);
        form.submit();
    };

    const answeredCount = Object.values(state).filter((s) => s.selected.length > 0).length;

    return (
        <>
            <Head title={`Frage ${currentPosition} / ${attempt.total_questions}`} />

            <div className="min-h-screen bg-background">
                <header className="sticky top-0 z-10 border-b border-border bg-background/95 backdrop-blur">
                    <div className="mx-auto flex max-w-3xl items-center justify-between px-6 py-4">
                        <div className="text-sm tabular-nums text-muted-foreground">
                            Frage {currentPosition} / {attempt.total_questions}
                            <span className="ml-3">· {answeredCount} beantwortet</span>
                        </div>
                        <ExamTimer expiresAt={attempt.timer_expires_at} />
                    </div>
                </header>

                <main className="mx-auto max-w-3xl px-6 py-8">
                    <Card className="border-border">
                        <CardHeader>
                            <div className="flex items-start justify-between gap-4">
                                <div className="text-lg leading-relaxed">{current.text}</div>
                                <Button
                                    type="button"
                                    variant={currentState.flagged ? 'default' : 'outline'}
                                    size="sm"
                                    onClick={toggleFlag}
                                    aria-label={currentState.flagged ? 'Markierung entfernen' : 'Frage markieren'}
                                >
                                    {currentState.flagged ? (
                                        <Flag data-icon="inline-start" className="size-4" />
                                    ) : (
                                        <FlagOff data-icon="inline-start" className="size-4" />
                                    )}
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3">
                            {current.options.map((option) => (
                                <Label
                                    key={option.id}
                                    className="flex cursor-pointer items-start gap-3 rounded-md border border-border p-3 hover:bg-muted"
                                >
                                    <Checkbox
                                        checked={currentState.selected.includes(option.id)}
                                        onCheckedChange={() => toggleOption(option.id)}
                                    />
                                    <span className="text-base leading-relaxed">{option.text}</span>
                                </Label>
                            ))}
                        </CardContent>
                    </Card>

                    <p className="mt-3 text-xs text-muted-foreground">
                        Mehrfachauswahl möglich. Alle richtigen Optionen ankreuzen — ein falsch angekreuztes Feld reicht, damit die Frage als falsch gewertet wird.
                    </p>

                    <div className="mt-8 flex items-center justify-between">
                        <Button
                            type="button"
                            variant="outline"
                            disabled={currentPosition === 1}
                            onClick={() => setCurrentPosition(currentPosition - 1)}
                        >
                            <ChevronLeft data-icon="inline-start" className="size-4" />
                            Zurück
                        </Button>

                        {currentPosition < attempt.total_questions ? (
                            <Button type="button" onClick={() => setCurrentPosition(currentPosition + 1)}>
                                Weiter
                                <ChevronRight data-icon="inline-end" className="size-4" />
                            </Button>
                        ) : (
                            <Button type="button" onClick={submit}>
                                Prüfung abschicken
                            </Button>
                        )}
                    </div>
                </main>
            </div>
        </>
    );
}
