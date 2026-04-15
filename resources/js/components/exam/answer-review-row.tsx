import { Check, X } from 'lucide-react';
import { Alert, AlertDescription } from '@/components/ui/alert';

export function AnswerReviewRow({
    label,
    answer,
    isCorrect,
}: {
    label: string;
    answer: string;
    isCorrect: boolean;
}) {
    return (
        <Alert variant={isCorrect ? 'success' : 'destructive'} className="py-2">
            {isCorrect ? <Check /> : <X />}
            <AlertDescription>
                <span className="font-medium">{label}: </span>
                {answer}
            </AlertDescription>
        </Alert>
    );
}
