import { CheckCircle2, XCircle } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';

export function ScoreHero({
    score,
    total,
    passed,
}: {
    score: number;
    total: number;
    passed: boolean;
}) {
    const percentage = total > 0 ? Math.round((score / total) * 100) : 0;

    return (
        <Card>
            <CardHeader className="items-center gap-2 text-center">
                <CardTitle className="text-base font-medium text-muted-foreground">
                    Dein Ergebnis
                </CardTitle>
                <div className="text-5xl font-bold tabular-nums">
                    {score} / {total}
                </div>
                <div className="text-xl tabular-nums text-muted-foreground">
                    {percentage} %
                </div>
                <Badge
                    variant={passed ? 'success' : 'warning'}
                    className="mt-2 gap-2"
                >
                    {passed ? (
                        <>
                            <CheckCircle2 className="size-4" />
                            Bestanden (≥ 60 %)
                        </>
                    ) : (
                        <>
                            <XCircle className="size-4" />
                            Unter der Bestehensgrenze
                        </>
                    )}
                </Badge>
            </CardHeader>
        </Card>
    );
}
