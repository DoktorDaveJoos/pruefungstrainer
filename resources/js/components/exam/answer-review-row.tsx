import { Check, X } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';

export type AnswerReviewState = 'correct' | 'user-wrong' | 'neutral';

const STATE_CONFIG: Record<
    Exclude<AnswerReviewState, 'neutral'>,
    { variant: 'success' | 'destructive'; icon: LucideIcon }
> = {
    correct: { variant: 'success', icon: Check },
    'user-wrong': { variant: 'destructive', icon: X },
};

export function AnswerReviewRow({
    answer,
    state,
}: {
    answer: string;
    state: AnswerReviewState;
}) {
    if (state === 'neutral') {
        return (
            <div className="rounded-lg border bg-background px-4 py-2 text-sm text-foreground shadow-xs">
                {answer}
            </div>
        );
    }

    const { variant, icon: Icon } = STATE_CONFIG[state];

    return (
        <Alert variant={variant} className="py-2">
            <Icon />
            <AlertDescription>{answer}</AlertDescription>
        </Alert>
    );
}
