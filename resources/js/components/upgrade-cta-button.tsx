import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { CheckoutSheet } from '@/components/checkout-sheet';
import { start } from '@/routes/checkout';

type Props = {
    children: ReactNode;
    priceLabel: string;
    attemptId?: number;
};

export function UpgradeCtaButton({ children, priceLabel, attemptId }: Props) {
    const { auth } = usePage().props;

    if (auth?.user) {
        return <a href={start.url()}>{children}</a>;
    }

    if (attemptId !== undefined) {
        return <CheckoutSheet trigger={children} attemptId={attemptId} priceLabel={priceLabel} />;
    }

    return <a href={start.url()}>{children}</a>;
}
