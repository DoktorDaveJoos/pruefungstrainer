import { SiteFooter } from '@/components/site-footer';
import { Head, Link } from '@inertiajs/react';
import { ReactNode } from 'react';

export default function LegalLayout({ title, headTitle, children }: { title: string; headTitle: string; children: ReactNode }) {
    return (
        <>
            <Head title={headTitle} />

            <div className="min-h-screen bg-background">
                <header className="border-b border-border">
                    <div className="mx-auto flex max-w-3xl items-center justify-between p-6">
                        <Link href="/" className="text-lg font-semibold tracking-tight">
                            Prüfungstrainer
                        </Link>
                        <Link href="/" className="text-sm text-muted-foreground hover:text-foreground">
                            Zurück zur Startseite
                        </Link>
                    </div>
                </header>

                <main className="mx-auto max-w-3xl px-6 py-16">
                    <h1 className="text-3xl font-bold tracking-tight">{title}</h1>
                    <div className="mt-8 space-y-8 text-sm leading-relaxed">{children}</div>
                </main>

                <SiteFooter />
            </div>
        </>
    );
}
