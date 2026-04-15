import { Head, Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { SiteFooter } from '@/components/site-footer';

export default function LegalLayout({ title, headTitle, children }: { title: string; headTitle: string; children: ReactNode }) {
    return (
        <>
            <Head title={headTitle} />

            <div className="min-h-screen bg-background">
                <header className="border-b border-border">
                    <div className="mx-auto flex max-w-3xl items-center justify-between px-4 py-4 sm:px-6">
                        <Link href="/" className="text-base font-semibold tracking-tight">
                            Prüfungstrainer
                        </Link>
                        <Link href="/" className="text-sm text-muted-foreground hover:text-foreground">
                            Zurück zur Startseite
                        </Link>
                    </div>
                </header>

                <main className="mx-auto max-w-3xl px-4 py-8 sm:px-6">
                    <h1 className="text-2xl font-semibold tracking-tight">{title}</h1>
                    <div className="mt-6 flex flex-col gap-6 text-sm leading-relaxed">{children}</div>
                </main>

                <SiteFooter />
            </div>
        </>
    );
}
