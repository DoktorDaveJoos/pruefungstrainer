import { Lock, Sparkles, Target } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { UpgradeCtaButton } from '@/components/upgrade-cta-button';

type Props = {
    passed: boolean;
    priceLabel: string;
    attemptId?: number;
};

export function ResultsUpgradeCallout({ passed, priceLabel, attemptId }: Props) {
    const Icon = passed ? Sparkles : Target;

    const title = passed
        ? 'Glückwunsch — du hast bestanden.'
        : 'Knapp dran — noch nicht bestanden.';

    const description = passed
        ? 'Dein Wissen sitzt. Mit dem Zugang siehst du deine stärksten Themen, deckst verbleibende Lücken auf und kannst jede Frage mit Erklärung und BSI-Originalquelle nachvollziehen — damit du in der echten Prüfung nicht nur bestehst, sondern sicher bist.'
        : 'Dein Ergebnis liegt unter der 60 %-Marke. Mit dem Zugang siehst du genau, in welchen Themenbereichen du Lücken hast, bekommst zu jeder Frage die Erklärung samt BSI-Originalquelle und kannst gezielt nacharbeiten — bis du beim nächsten Mal sicher bestehst.';

    const ctaLabel = passed ? 'Ergebnisse freischalten' : 'Lücken aufdecken';

    return (
        <Alert variant={passed ? 'success' : 'warning'}>
            <Icon />
            <AlertTitle className="line-clamp-none text-base font-semibold">
                {title}
            </AlertTitle>
            <AlertDescription>
                <p>{description}</p>
            </AlertDescription>
            <div className="col-start-2 mt-4">
                <UpgradeCtaButton priceLabel={priceLabel} attemptId={attemptId}>
                    <Button size="lg">
                        <Lock className="mr-2 size-4" />
                        {ctaLabel} · {priceLabel}
                    </Button>
                </UpgradeCtaButton>
            </div>
        </Alert>
    );
}
