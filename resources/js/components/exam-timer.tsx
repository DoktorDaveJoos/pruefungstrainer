import { cn } from '@/lib/utils';
import { Clock } from 'lucide-react';
import { useEffect, useState } from 'react';

export function ExamTimer({ expiresAt, className }: { expiresAt: string; className?: string }) {
    const [remaining, setRemaining] = useState(() =>
        Math.max(0, Math.floor((new Date(expiresAt).getTime() - Date.now()) / 1000)),
    );

    useEffect(() => {
        const tick = () =>
            setRemaining(Math.max(0, Math.floor((new Date(expiresAt).getTime() - Date.now()) / 1000)));
        tick();
        const interval = setInterval(tick, 1000);
        return () => clearInterval(interval);
    }, [expiresAt]);

    const mins = Math.floor(remaining / 60);
    const secs = remaining % 60;
    const isWarning = remaining < 300; // under 5 min
    const isExpired = remaining === 0;

    return (
        <div
            className={cn(
                'inline-flex items-center gap-2 rounded-md border border-border bg-card px-3 py-2 text-sm font-medium tabular-nums',
                isWarning && !isExpired && 'text-warning',
                isExpired && 'text-destructive',
                className,
            )}
        >
            <Clock data-icon="inline-start" className="size-4" />
            <span>
                {String(mins).padStart(2, '0')}:{String(secs).padStart(2, '0')}
            </span>
        </div>
    );
}
