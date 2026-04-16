import { useEffect, useState } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { CheckCircle2, Loader2 } from 'lucide-react';

type Props = { hasAccess: boolean };

export default function Processing({ hasAccess: initial }: Props) {
    const [hasAccess, setHasAccess] = useState(initial);
    const [gaveUp, setGaveUp] = useState(false);

    useEffect(() => {
        if (hasAccess) return;
        let cancelled = false;
        let count = 0;
        const tick = async () => {
            if (cancelled || count >= 20) {
                if (!cancelled) setGaveUp(true);
                return;
            }
            count += 1;
            const res = await fetch('/api/access-status', { credentials: 'same-origin' });
            if (cancelled) return;
            const json = await res.json();
            if (json.hasAccess) {
                setHasAccess(true);
                return;
            }
            setTimeout(tick, 500);
        };
        tick();
        return () => {
            cancelled = true;
        };
    }, [hasAccess]);

    return (
        <div className="mx-auto flex min-h-screen max-w-2xl items-center justify-center p-4">
            {hasAccess ? (
                <Alert variant="success">
                    <CheckCircle2 />
                    <AlertTitle>Zugang aktiviert</AlertTitle>
                    <AlertDescription>
                        Dein Zugang läuft für 12 Monate.{' '}
                        <a href="/" className="underline">
                            Zur Startseite
                        </a>
                        .
                    </AlertDescription>
                </Alert>
            ) : gaveUp ? (
                <Alert>
                    <AlertTitle>Zahlung wird verarbeitet</AlertTitle>
                    <AlertDescription>
                        Wir haben deine Zahlung erhalten, aber die Aktivierung dauert einen Moment. Du erhältst gleich
                        eine E-Mail sobald alles bereit ist.
                    </AlertDescription>
                </Alert>
            ) : (
                <Alert>
                    <Loader2 className="animate-spin" />
                    <AlertTitle>Zahlung wird bestätigt…</AlertTitle>
                    <AlertDescription>Das dauert nur wenige Sekunden.</AlertDescription>
                </Alert>
            )}
        </div>
    );
}
