import { Lock } from 'lucide-react';
import type { ReactNode } from 'react';
import { usePage } from '@inertiajs/react';
import { CheckoutSheet } from '@/components/checkout-sheet';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { start } from '@/routes/checkout';

type Props = {
    children: ReactNode;
    priceLabel: string;
    attemptId?: number;
};

type PageProps = {
    auth?: { user?: unknown };
};

export function LockedPreview({ children, priceLabel, attemptId }: Props) {
    const { auth } = usePage<PageProps>().props;

    const cta = (
        <Button size="lg">
            <Lock className="mr-2 size-4" />
            12 Monate Zugang freischalten · {priceLabel}
        </Button>
    );

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
                    {auth?.user ? (
                        <a href={start.url()}>{cta}</a>
                    ) : attemptId !== undefined ? (
                        <CheckoutSheet trigger={cta} attemptId={attemptId} priceLabel={priceLabel} />
                    ) : (
                        <a href={start.url()}>{cta}</a>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
