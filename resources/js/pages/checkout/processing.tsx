import { useEffect, useRef, useState } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Loader2 } from 'lucide-react';

type Props = { hasAccess: boolean; redirectTo: string | null };

export default function Processing({ hasAccess: initialAccess, redirectTo: initialRedirectTo }: Props) {
    const [redirectTo, setRedirectTo] = useState<string | null>(initialAccess ? (initialRedirectTo ?? '/') : null);
    const [gaveUp, setGaveUp] = useState(false);
    const hasNavigated = useRef(false);

    useEffect(() => {
        if (redirectTo && !hasNavigated.current) {
            hasNavigated.current = true;
            window.location.href = redirectTo;
            return;
        }
        if (redirectTo) return;

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
                setRedirectTo(json.redirectTo ?? '/');
                return;
            }
            setTimeout(tick, 500);
        };
        tick();
        return () => {
            cancelled = true;
        };
    }, [redirectTo]);

    return (
        <div className="mx-auto flex min-h-screen max-w-2xl items-center justify-center p-4">
            {gaveUp ? (
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
