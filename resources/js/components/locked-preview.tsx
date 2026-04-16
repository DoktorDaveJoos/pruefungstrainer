import { Lock } from 'lucide-react';
import type { ReactNode } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { UpgradeCtaButton } from '@/components/upgrade-cta-button';

type Props = {
    children: ReactNode;
    priceLabel: string;
    attemptId?: number;
    hasAccess?: boolean;
    title?: string;
    lockedDescription?: string;
    unlockedDescription?: string;
};

export function LockedPreview({
    children,
    priceLabel,
    attemptId,
    hasAccess = false,
    title = 'Review der falschen Antworten',
    lockedDescription = 'Jede falsch beantwortete Frage mit Erklärung und BSI-Originalquelle — gezielt lernen, wo du schwach bist.',
    unlockedDescription = 'Jede falsch beantwortete Frage mit Erklärung und BSI-Originalquelle.',
}: Props) {
    if (hasAccess) {
        return (
            <Card>
                <CardHeader>
                    <CardTitle>{title}</CardTitle>
                    <CardDescription>{unlockedDescription}</CardDescription>
                </CardHeader>
                <CardContent>{children}</CardContent>
            </Card>
        );
    }

    return (
        <Card className="relative overflow-hidden">
            <CardHeader>
                <div className="flex items-center gap-2">
                    <Badge variant="secondary" className="gap-1">
                        <Lock data-icon="inline-start" className="size-3" />
                        Paid
                    </Badge>
                </div>
                <CardTitle className="mt-2">{title}</CardTitle>
                <CardDescription>{lockedDescription}</CardDescription>
            </CardHeader>
            <CardContent className="relative">
                <div className="pointer-events-none select-none opacity-30 blur-sm">{children}</div>
                <div className="absolute inset-0 flex items-center justify-center">
                    <UpgradeCtaButton priceLabel={priceLabel} attemptId={attemptId}>
                        <Button size="lg">
                            <Lock className="mr-2 size-4" />
                            12 Monate Zugang freischalten · {priceLabel}
                        </Button>
                    </UpgradeCtaButton>
                </div>
            </CardContent>
        </Card>
    );
}
