import { Progress } from '@/components/ui/progress';

export function TopicBreakdownRow({
    label,
    correct,
    total,
}: {
    label: string;
    correct: number;
    total: number;
}) {
    const percentage = total > 0 ? Math.round((correct / total) * 100) : 0;

    return (
        <div className="flex items-center gap-4 rounded-md border border-border p-4 shadow-xs">
            <div className="flex flex-1 flex-col gap-1">
                <div className="text-sm font-medium">{label}</div>
                <Progress value={percentage} className="h-1.5" />
            </div>
            <div className="text-sm tabular-nums text-muted-foreground">
                {correct} / {total} · {percentage} %
            </div>
        </div>
    );
}
