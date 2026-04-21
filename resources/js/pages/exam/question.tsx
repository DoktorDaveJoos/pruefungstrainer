import { Head, router, usePage } from '@inertiajs/react';
import { ArrowLeft, ChevronLeft, ChevronRight, Lock } from 'lucide-react';
import { useState } from 'react';
import ExamController from '@/actions/App/Http/Controllers/ExamController';
import { AnswerOption } from '@/components/exam/answer-option';
import { ExamTimer } from '@/components/exam-timer';
import TextLink from '@/components/text-link';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { dashboard, register } from '@/routes';

type Option = { id: number; text: string };
type Question = {
    position: number;
    question_id: number;
    text: string;
    options: Option[];
    selected_option_ids: number[];
};

type Attempt = {
    id: number;
    timer_expires_at: string;
    total_questions: number;
};

export default function ExamQuestion({
    attempt,
    questions,
}: {
    attempt: Attempt;
    questions: Question[];
}) {
    const { auth } = usePage().props;
    const [currentPosition, setCurrentPosition] = useState(1);
    const [state, setState] = useState(() =>
        questions.reduce<Record<number, { selected: number[] }>>(
            (acc, q) => {
                acc[q.position] = {
                    selected: q.selected_option_ids,
                };

                return acc;
            },
            {},
        ),
    );

    const current = questions.find((q) => q.position === currentPosition)!;
    const currentState = state[currentPosition];

    const save = (nextSelected: number[]) => {
        router.patch(
            ExamController.saveAnswer.url({
                attempt: attempt.id,
                position: currentPosition,
            }),
            { selected_option_ids: nextSelected },
            { preserveScroll: true, preserveState: true, only: [] },
        );
    };

    const toggleOption = (optionId: number) => {
        const nextSelected = currentState.selected.includes(optionId)
            ? currentState.selected.filter((id) => id !== optionId)
            : [...currentState.selected, optionId];

        setState({
            ...state,
            [currentPosition]: { selected: nextSelected },
        });
        save(nextSelected);
    };

    const submit = () => {
        router.post(ExamController.submit.url({ attempt: attempt.id }));
    };

    const answeredCount = Object.values(state).filter(
        (s) => s.selected.length > 0,
    ).length;

    return (
        <>
            <Head title={`Frage ${currentPosition} / ${attempt.total_questions}`} />

            <div className="min-h-screen bg-background">
                <header className="sticky top-0 z-10 border-b border-border bg-background/95 backdrop-blur">
                    <div className="mx-auto flex max-w-2xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                        <div className="flex items-center gap-3 text-sm tabular-nums text-muted-foreground">
                            {auth?.user && (
                                <>
                                    <AlertDialog>
                                        <AlertDialogTrigger asChild>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                className="gap-1 text-muted-foreground"
                                            >
                                                <ArrowLeft className="size-4" />
                                                <span className="hidden sm:inline">Dashboard</span>
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>
                                                    Prüfung verlassen?
                                                </AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    Der Timer läuft weiter. Deine
                                                    bisherigen Antworten sind
                                                    gespeichert und du kannst die
                                                    Prüfung wieder aufnehmen, solange
                                                    die Zeit nicht abgelaufen ist.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>
                                                    Weiter bearbeiten
                                                </AlertDialogCancel>
                                                <AlertDialogAction
                                                    onClick={() =>
                                                        router.visit(dashboard())
                                                    }
                                                >
                                                    Zum Dashboard
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                    <span>·</span>
                                </>
                            )}
                            <span>
                                Frage {currentPosition} / {attempt.total_questions}
                            </span>
                            <span>·</span>
                            <span>{answeredCount} beantwortet</span>
                        </div>
                        <ExamTimer expiresAt={attempt.timer_expires_at} />
                    </div>
                </header>

                <main className="mx-auto flex max-w-2xl flex-col gap-6 px-4 py-8 sm:px-6">
                    {!auth?.user && (
                        <Alert>
                            <Lock />
                            <AlertTitle>
                                Probedurchlauf — immer dieselben 50 Fragen
                            </AlertTitle>
                            <AlertDescription>
                                <p>
                                    Zum Ausprobieren bekommst du als Gast ein
                                    festes Fragenset. Für die echte
                                    Prüfungssimulation — neue Fragen pro
                                    Durchlauf, Auswertung pro Themenfeld,
                                    Freies Lernen und zwölf Monate Zugang —{' '}
                                    <TextLink href={register()}>
                                        Konto anlegen und freischalten
                                    </TextLink>
                                    .
                                </p>
                            </AlertDescription>
                        </Alert>
                    )}

                    <Card className="py-8">
                        <CardHeader className="px-8">
                            <div className="text-lg leading-relaxed">
                                {current.text}
                            </div>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-3 px-8">
                            {current.options.map((option) => (
                                <AnswerOption
                                    key={option.id}
                                    id={option.id}
                                    text={option.text}
                                    checked={currentState.selected.includes(
                                        option.id,
                                    )}
                                    onCheckedChange={() => toggleOption(option.id)}
                                />
                            ))}
                        </CardContent>
                    </Card>

                    <p className="text-xs text-muted-foreground">
                        Mehrfachauswahl möglich. Alle richtigen Optionen
                        ankreuzen — ein falsch angekreuztes Feld reicht, damit
                        die Frage als falsch gewertet wird.
                    </p>

                    <div className="flex items-center justify-between">
                        <Button
                            type="button"
                            variant="outline"
                            disabled={currentPosition === 1}
                            onClick={() => setCurrentPosition(currentPosition - 1)}
                        >
                            <ChevronLeft className="size-4" />
                            Zurück
                        </Button>

                        {currentPosition < attempt.total_questions ? (
                            <Button
                                type="button"
                                onClick={() =>
                                    setCurrentPosition(currentPosition + 1)
                                }
                            >
                                Weiter
                                <ChevronRight className="size-4" />
                            </Button>
                        ) : (
                            <AlertDialog>
                                <AlertDialogTrigger asChild>
                                    <Button type="button">
                                        Prüfung abschicken
                                    </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>
                                            Prüfung endgültig abschicken?
                                        </AlertDialogTitle>
                                        <AlertDialogDescription>
                                            Danach kannst du keine Antworten
                                            mehr ändern.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>
                                            Abbrechen
                                        </AlertDialogCancel>
                                        <AlertDialogAction onClick={submit}>
                                            Abschicken
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        )}
                    </div>
                </main>
            </div>
        </>
    );
}
