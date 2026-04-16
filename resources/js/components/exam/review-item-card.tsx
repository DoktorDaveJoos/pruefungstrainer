import { AnswerReviewRow  } from '@/components/exam/answer-review-row';
import type {AnswerReviewState} from '@/components/exam/answer-review-row';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';

export type ReviewItemOption = {
    text: string;
    isCorrect: boolean;
    isUserChoice: boolean;
};

export type ReviewItem = {
    number: number;
    topic: string;
    stem: string;
    explanation: string;
    quote: string | null;
    source: string | null;
    options: ReviewItemOption[];
};

function optionState(option: ReviewItemOption): AnswerReviewState {
    if (option.isCorrect) {
        return 'correct';
    }

    if (option.isUserChoice) {
        return 'user-wrong';
    }

    return 'neutral';
}

export function ReviewItemCard({
    number,
    topic,
    stem,
    explanation,
    quote,
    source,
    options,
}: ReviewItem) {
    return (
        <article className="flex flex-col gap-6 rounded-xl border bg-background px-8 py-8 shadow-xs">
            <header className="flex items-center justify-between gap-4">
                <span className="text-sm font-medium tabular-nums">Frage {number}</span>
                <Badge variant="secondary">{topic}</Badge>
            </header>

            <p className="text-lg leading-relaxed">{stem}</p>

            <div className="flex flex-col gap-2">
                {options.map((option, index) => (
                    <AnswerReviewRow
                        key={index}
                        answer={option.text}
                        state={optionState(option)}
                    />
                ))}
            </div>

            <Separator />

            <div className="flex flex-col gap-2">
                <div className="text-sm font-semibold">Erklärung</div>
                <p className="text-sm leading-relaxed text-muted-foreground">
                    {explanation}
                </p>
            </div>

            {quote && (
                <div className="flex flex-col gap-2">
                    <div className="text-sm font-semibold">BSI-Quelle</div>
                    <blockquote className="border-l-2 pl-4 text-sm italic leading-relaxed text-muted-foreground">
                        {quote}
                    </blockquote>
                    {source && (
                        <div className="text-xs text-muted-foreground">— {source}</div>
                    )}
                </div>
            )}
        </article>
    );
}
