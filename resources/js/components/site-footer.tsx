import { Link } from '@inertiajs/react';

export function SiteFooter() {
    return (
        <footer className="border-t border-border bg-background">
            <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 py-8 text-sm text-muted-foreground sm:flex-row">
                <div>© {new Date().getFullYear()} Prüfungstrainer · BSI IT-Grundschutz-Praktiker</div>
                <nav className="flex gap-6">
                    <Link href="/agb" className="hover:text-foreground">
                        AGB
                    </Link>
                    <Link href="/datenschutz" className="hover:text-foreground">
                        Datenschutz
                    </Link>
                    <Link href="/impressum" className="hover:text-foreground">
                        Impressum
                    </Link>
                </nav>
            </div>
        </footer>
    );
}
