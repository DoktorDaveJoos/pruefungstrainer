import { ReactNode } from 'react';

interface Option {
    id: number;
    text: string;
}

interface Question {
    id: number;
    text: string;
    topic?: string;
    topic_label?: string;
    options: Option[];
}

interface Progress {
    seen: number;
    total: number;
    correct: number;
}

interface IndexProps {
    question: Question | null;
    wrongOnly: boolean;
    progress: Progress;
}

export default function Index({ question, wrongOnly, progress }: IndexProps): ReactNode {
    return (
        <div>
            {question ? (
                <div>
                    <h1>{question.text}</h1>
                    <div>
                        {question.options.map((option) => (
                            <div key={option.id}>{option.text}</div>
                        ))}
                    </div>
                </div>
            ) : (
                <div>No questions available</div>
            )}
        </div>
    );
}
