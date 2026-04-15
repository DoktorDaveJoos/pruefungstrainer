import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Lock } from 'lucide-react';
import { ReactNode } from 'react';

export function LockedPreview({
    children,
    ctaText = 'Lifetime-Zugang freischalten · 29 €',
}: {
    children: ReactNode;
    ctaText?: string;
}) {
    return (
        <Card className="relative overflow-hidden">
            <CardHeader>
                <div className="flex items-center gap-2">
                    <Badge variant="secondary" className="gap-1">
                        <Lock data-icon="inline-start" className="size-3" />
                        Paid
                    </Badge>
                </div>
                <CardTitle className="mt-2">Review der falschen Antworten</CardTitle>
                <CardDescription>
                    Jede falsch beantwortete Frage mit Erklärung und BSI-Originalquelle — gezielt lernen, wo du schwach bist.
                </CardDescription>
            </CardHeader>
            <CardContent className="relative">
                <div className="pointer-events-none select-none opacity-30 blur-sm">{children}</div>
                <div className="absolute inset-0 flex items-center justify-center">
                    <Button asChild size="lg">
                        <a href="/checkout">{ctaText}</a>
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
}
