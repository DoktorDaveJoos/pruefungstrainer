import { Head, Link, usePage } from '@inertiajs/react';
import { Check, ChevronRight, Clock, FileCheck, Lock } from 'lucide-react';
import { CheckoutSheet } from '@/components/checkout-sheet';
import { SiteFooter } from '@/components/site-footer';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { getCsrfToken } from '@/lib/utils';
import { start } from '@/routes/checkout';

type Pricing = {
    amount_eur: number;
    standard_price_eur: number;
    is_founder_price: boolean;
    spots_remaining: number;
};

type FreeTier = {
    status: 'available' | 'resume' | 'already_done';
    lastAttemptId: number | null;
};

export default function Welcome() {
    const { auth, pricing, freeTier } = usePage<{ pricing: Pricing; freeTier: FreeTier }>().props;

    return (
        <>
            <Head title="BSI IT-Grundschutz-Praktiker Prüfungstrainer" />

            <div className="min-h-screen bg-background">
                <header className="border-b border-border">
                    <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                        <div className="text-base font-semibold tracking-tight">Prüfungstrainer</div>
                        <nav className="flex items-center gap-4 text-sm">
                            {auth?.user ? (
                                <Link href="/dashboard" className="text-foreground hover:underline">
                                    Startseite
                                </Link>
                            ) : (
                                <>
                                    <Link href="/login" className="text-muted-foreground hover:text-foreground">
                                        Anmelden
                                    </Link>
                                    <Link href="/register" className="text-muted-foreground hover:text-foreground">
                                        Registrieren
                                    </Link>
                                </>
                            )}
                        </nav>
                    </div>
                </header>

                <main>
                    <section className="mx-auto max-w-3xl px-4 py-16 sm:px-6">
                        <h1 className="text-4xl font-semibold tracking-tight">BSI IT-Grundschutz-Praktiker</h1>
                        <p className="mt-4 text-lg leading-relaxed text-muted-foreground">
                            Realistische Prüfungssimulation. 50 Fragen, 60 Minuten, 60 % Bestehensgrenze — genau wie die echte BSI-Prüfung.
                        </p>

                        {freeTier.status === 'already_done' && freeTier.lastAttemptId !== null ? (
                            <Card className="mt-8 max-w-md">
                                <CardHeader>
                                    <CardTitle className="text-base">Deinen kostenlosen Testlauf hast du schon absolviert</CardTitle>
                                    <CardDescription>
                                        Schalte den 12 Monate Zugang frei, um dein Ergebnis und alle Erklärungen zu sehen.
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="flex flex-col gap-2 sm:flex-row [&>*]:flex-1">
                                    <Button variant="outline" asChild>
                                        <Link href={`/pruefungssimulation/${freeTier.lastAttemptId}/ergebnis`}>
                                            Zum Ergebnis
                                        </Link>
                                    </Button>
                                    <CheckoutSheet
                                        trigger={<Button>Zugang freischalten</Button>}
                                        priceLabel={`${pricing.amount_eur} €`}
                                    />
                                </CardContent>
                            </Card>
                        ) : (
                            <div className="mt-8 flex items-center gap-4">
                                <form method="POST" action="/pruefungssimulation/start">
                                    <input
                                        type="hidden"
                                        name="_token"
                                        value={getCsrfToken()}
                                    />
                                    <Button size="lg" type="submit">
                                        {freeTier.status === 'resume'
                                            ? 'Prüfung fortsetzen'
                                            : 'Prüfungssimulation starten'}
                                    </Button>
                                </form>
                                <span className="text-sm text-muted-foreground">
                                    {freeTier.status === 'resume'
                                        ? 'Dein Testlauf läuft noch'
                                        : 'kostenlos · kein Login nötig'}
                                </span>
                            </div>
                        )}

                        <div className="mt-12 grid gap-6 md:grid-cols-3">
                            <Card>
                                <CardHeader>
                                    <FileCheck className="size-6 text-muted-foreground" />
                                    <CardTitle className="mt-2 text-base">50 Fragen</CardTitle>
                                    <CardDescription>Zufall aus über 900 BSI-Prüfungsfragen. 75 % Basis, 25 % Experte.</CardDescription>
                                </CardHeader>
                            </Card>
                            <Card>
                                <CardHeader>
                                    <Clock className="size-6 text-muted-foreground" />
                                    <CardTitle className="mt-2 text-base">60 Minuten</CardTitle>
                                    <CardDescription>Server-authoritativer Timer. Bei Ablauf wird automatisch abgegeben.</CardDescription>
                                </CardHeader>
                            </Card>
                            <Card>
                                <CardHeader>
                                    <Lock className="size-6 text-muted-foreground" />
                                    <CardTitle className="mt-2 text-base">Review freischalten</CardTitle>
                                    <CardDescription>Nach der Simulation: detaillierte Antwort-Erklärungen mit BSI-Quellen ab {pricing.amount_eur} € einmalig.</CardDescription>
                                </CardHeader>
                            </Card>
                        </div>
                    </section>

                    <section className="border-t border-border bg-muted/30 py-16">
                        <div className="mx-auto max-w-3xl px-4 sm:px-6">
                            <h2 className="text-center text-xl font-semibold tracking-tight">12 Monate Zugang</h2>
                            <p className="mt-2 text-center text-sm text-muted-foreground">
                                Einmal zahlen. Kein Abo. Für immer üben.
                            </p>

                            <Card className="mx-auto mt-8 max-w-md">
                                <CardHeader className="items-center text-center">
                                    {pricing.is_founder_price && (
                                        <div className="text-xs font-medium uppercase tracking-wider text-warning">
                                            Founder's Price · {pricing.spots_remaining} von 100 Plätzen frei
                                        </div>
                                    )}
                                    <div className="mt-2 text-5xl font-bold tabular-nums">{pricing.amount_eur} €</div>
                                    {pricing.is_founder_price && (
                                        <div className="text-sm text-muted-foreground line-through tabular-nums">{pricing.standard_price_eur} €</div>
                                    )}
                                    <CardTitle className="mt-2 text-base font-medium">12 Monate Zugang</CardTitle>
                                </CardHeader>
                                <CardContent className="flex flex-col gap-4">
                                    <ul className="flex flex-col gap-2 text-sm">
                                        <li className="flex items-start gap-2">
                                            <Check className="mt-0.5 size-4 shrink-0 text-success" />
                                            <span>Unbegrenzte 50-Fragen-Prüfungssimulationen</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <Check className="mt-0.5 size-4 shrink-0 text-success" />
                                            <span>Vollständige Antwort-Erklärungen mit BSI-Originalquellen</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <Check className="mt-0.5 size-4 shrink-0 text-success" />
                                            <span>Topic-Analyse: wo bist du schwach</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <Check className="mt-0.5 size-4 shrink-0 text-success" />
                                            <span>Freies Lernen — nur falsche Fragen wiederholen</span>
                                        </li>
                                        <li className="flex items-start gap-2">
                                            <Check className="mt-0.5 size-4 shrink-0 text-success" />
                                            <span>12 Monate Zugang — keine automatische Verlängerung</span>
                                        </li>
                                    </ul>
                                    <a href={start.url()}>
                                        <Button size="lg" className="w-full">
                                            12 Monate Zugang freischalten
                                        </Button>
                                    </a>
                                    <p className="text-center text-xs text-muted-foreground">
                                        Sichere Zahlung über Polar.sh · 14 Tage Widerrufsrecht (siehe AGB)
                                    </p>
                                </CardContent>
                            </Card>

                            <p className="mt-6 text-center text-xs text-muted-foreground">
                                BSI-Prüfung kostet{' '}
                                <a href="https://www.bsi.bund.de/" className="underline" target="_blank" rel="noopener noreferrer">
                                    €245–450 pro Versuch
                                </a>{' '}
                                — 12 Monate Zugang ist eine günstige Versicherung.
                            </p>
                        </div>
                    </section>

                    <section className="border-t border-border py-16">
                        <div className="mx-auto max-w-3xl px-4 sm:px-6">
                            <h2 className="text-center text-xl font-semibold tracking-tight">Mehr Prüfungsmodule folgen</h2>
                            <p className="mt-2 text-center text-sm text-muted-foreground">
                                BSI IT-Grundschutz ist erst der Anfang. Dein 12-Monats-Zugang enthält alle kommenden Module automatisch — ohne Aufpreis.
                            </p>

                            <div className="mt-8 grid gap-6 sm:grid-cols-2">
                                <Card>
                                    <CardHeader>
                                        <Badge variant="success">Verfügbar</Badge>
                                        <CardTitle className="mt-3 text-base">BSI IT-Grundschutz-Praktiker</CardTitle>
                                        <CardDescription>
                                            Prüfung nach BSI-Standard 200-1/-2/-3 und IT-Grundschutz-Kompendium.
                                        </CardDescription>
                                    </CardHeader>
                                </Card>
                                <Card>
                                    <CardHeader>
                                        <Badge variant="secondary">Bald verfügbar</Badge>
                                        <CardTitle className="mt-3 text-base">ISO 27001 Auditor</CardTitle>
                                        <CardDescription>
                                            Auditorenrolle nach ISO/IEC 27001 und ISO 19011.
                                        </CardDescription>
                                    </CardHeader>
                                </Card>
                                <Card>
                                    <CardHeader>
                                        <Badge variant="secondary">Bald verfügbar</Badge>
                                        <CardTitle className="mt-3 text-base">ISO 27001 Implementer</CardTitle>
                                        <CardDescription>
                                            Aufbau und Betrieb eines ISMS nach ISO/IEC 27001.
                                        </CardDescription>
                                    </CardHeader>
                                </Card>
                                <Card>
                                    <CardHeader>
                                        <Badge variant="secondary">Bald verfügbar</Badge>
                                        <CardTitle className="mt-3 text-base">BCM &amp; Notfallvorsorge</CardTitle>
                                        <CardDescription>
                                            Business Continuity Management nach BSI-Standard 200-4.
                                        </CardDescription>
                                    </CardHeader>
                                </Card>
                            </div>
                        </div>
                    </section>

                    <section className="mx-auto max-w-3xl px-4 py-16 sm:px-6">
                        <h2 className="text-xl font-semibold tracking-tight">Häufige Fragen</h2>

                        <div className="mt-6 flex flex-col gap-4">
                            <FaqItem
                                q="Stimmt die Prüfungssimulation exakt mit dem BSI-Original überein?"
                                a="Format und Bewertung mirroren das BSI-Original (50 Fragen, 60 Minuten, 60 % Bestehensgrenze, Mehrfachauswahl mit Alles-oder-Nichts-Bewertung). Die Basis/Experte-Einteilung der Fragen basiert auf eigener Klassifikation, da BSI die offizielle Verteilung nicht veröffentlicht."
                            />
                            <FaqItem
                                q="Kann ich die Simulation vor dem Kauf testen?"
                                a="Ja. Die Prüfungssimulation ist kostenlos und ohne Login zugänglich. Nach Abschluss siehst du dein Ergebnis. Erst die Antwort-Erklärungen, das Themen-Feedback und das Freie Lernen sind im 12 Monate Zugang enthalten."
                            />
                            <FaqItem
                                q="Was passiert mit meinem Probelauf, wenn ich später kaufe?"
                                a="Sobald du den 12 Monate Zugang freischaltest, wird dein anonymer Probelauf automatisch deinem Konto zugeordnet. Du siehst sofort die Erklärungen zu allen Fragen aus genau diesem Lauf."
                            />
                            <FaqItem
                                q="Wie viele Fragen sind im Pool?"
                                a="Derzeit über 900 echte BSI-Prüfungsfragen mit Erklärungen und Quellenverweisen aus den BSI-Standards 200-1, 200-2, 200-3 und dem IT-Grundschutz-Kompendium. Der Pool wächst regelmäßig."
                            />
                            <FaqItem
                                q="Kann ich mein Geld zurückbekommen?"
                                a="14 Tage Widerrufsrecht nach EU-Verbraucherrecht. Da der Zugang sofort freigeschaltet wird, bestätigst du beim Kauf den Beginn der Dienstleistung — das Widerrufsrecht erlischt damit (siehe AGB)."
                            />
                            <FaqItem
                                q="Muss ich ein Abo abschließen?"
                                a="Nein. Es ist ein einmaliger Kauf — kein Abo, keine wiederkehrende Gebühr."
                            />
                        </div>
                    </section>
                </main>

                <SiteFooter />
            </div>
        </>
    );
}

function FaqItem({ q, a }: { q: string; a: string }) {
    return (
        <details className="group rounded-md border border-border p-4 shadow-xs">
            <summary className="cursor-pointer list-none text-sm font-medium [&::-webkit-details-marker]:hidden">
                <span className="inline-flex items-center gap-2">
                    <ChevronRight className="size-4 text-muted-foreground transition-transform group-open:rotate-90" />
                    {q}
                </span>
            </summary>
            <p className="mt-2 pl-6 text-sm leading-relaxed text-muted-foreground">{a}</p>
        </details>
    );
}
