import { SiteFooter } from '@/components/site-footer';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';
import { Check, Clock, FileCheck, Lock } from 'lucide-react';

type Pricing = {
    amount_eur: number;
    is_founder_price: boolean;
    spots_remaining: number;
};

export default function Welcome() {
    const { auth, pricing } = usePage().props as { auth?: { user?: unknown }; pricing: Pricing };

    return (
        <>
            <Head title="BSI IT-Grundschutz-Praktiker Prüfungstrainer" />

            <div className="min-h-screen bg-background">
                <header className="border-b border-border">
                    <div className="mx-auto flex max-w-6xl items-center justify-between p-6">
                        <div className="text-lg font-semibold tracking-tight">Prüfungstrainer</div>
                        <nav className="flex items-center gap-4 text-sm">
                            {auth?.user ? (
                                <Link href="/dashboard" className="text-foreground hover:underline">
                                    Dashboard
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
                    <section className="mx-auto max-w-3xl px-6 py-24">
                        <h1 className="text-5xl font-bold tracking-tight">BSI IT-Grundschutz-Praktiker</h1>
                        <p className="mt-4 text-xl text-muted-foreground leading-relaxed">
                            Realistische Prüfungssimulation. 50 Fragen, 60 Minuten, 60 % Bestehensgrenze — genau wie die echte BSI-Prüfung.
                        </p>

                        <div className="mt-10 flex items-center gap-4">
                            <form method="POST" action="/pruefungssimulation/start">
                                <input
                                    type="hidden"
                                    name="_token"
                                    value={(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? ''}
                                />
                                <Button size="lg" type="submit">
                                    Prüfungssimulation starten
                                </Button>
                            </form>
                            <span className="text-sm text-muted-foreground">kostenlos · kein Login nötig</span>
                        </div>

                        <div className="mt-16 grid gap-6 md:grid-cols-3">
                            <Card>
                                <CardHeader>
                                    <FileCheck className="size-6 text-muted-foreground" />
                                    <CardTitle className="mt-2 text-base">50 Fragen</CardTitle>
                                    <CardDescription>Zufall aus über 160 BSI-Prüfungsfragen. 75 % Basis, 25 % Experte.</CardDescription>
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

                    <section className="border-t border-border bg-muted/30 py-20">
                        <div className="mx-auto max-w-3xl px-6">
                            <h2 className="text-center text-3xl font-bold tracking-tight">Lifetime-Zugang</h2>
                            <p className="mt-2 text-center text-muted-foreground">
                                Einmal zahlen. Kein Abo. Für immer üben.
                            </p>

                            <Card className="mt-10 mx-auto max-w-md border-border">
                                <CardHeader className="items-center text-center">
                                    {pricing.is_founder_price && (
                                        <div className="text-xs font-medium uppercase tracking-wider text-warning">
                                            Founder's Price · {pricing.spots_remaining} von 100 Plätzen frei
                                        </div>
                                    )}
                                    <div className="mt-2 text-5xl font-bold tabular-nums">{pricing.amount_eur} €</div>
                                    {pricing.is_founder_price && (
                                        <div className="text-sm text-muted-foreground line-through tabular-nums">49 €</div>
                                    )}
                                    <CardTitle className="mt-4 text-base font-medium">Lifetime-Zugang</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <ul className="space-y-2 text-sm">
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
                                            <span>Lebenslanger Zugang — keine Abos</span>
                                        </li>
                                    </ul>
                                    <form method="POST" action="/checkout/start">
                                        <input
                                            type="hidden"
                                            name="_token"
                                            value={(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? ''}
                                        />
                                        <Button type="submit" size="lg" className="w-full">
                                            Lifetime-Zugang freischalten
                                        </Button>
                                    </form>
                                    <p className="text-center text-xs text-muted-foreground">
                                        Sichere Zahlung über Polar.sh · 14 Tage Widerrufsrecht (siehe AGB)
                                    </p>
                                </CardContent>
                            </Card>

                            <p className="mt-8 text-center text-xs text-muted-foreground">
                                BSI-Prüfung kostet{' '}
                                <a href="https://www.bsi.bund.de/" className="underline" target="_blank" rel="noopener noreferrer">
                                    €245–450 pro Versuch
                                </a>{' '}
                                — der Lifetime-Zugang ist eine günstige Versicherung.
                            </p>
                        </div>
                    </section>

                    <section className="mx-auto max-w-3xl px-6 py-20">
                        <h2 className="text-3xl font-bold tracking-tight">Häufige Fragen</h2>

                        <div className="mt-8 space-y-6">
                            <FaqItem
                                q="Stimmt die Prüfungssimulation exakt mit dem BSI-Original überein?"
                                a="Format und Bewertung mirroren das BSI-Original (50 Fragen, 60 Minuten, 60 % Bestehensgrenze, Mehrfachauswahl mit Alles-oder-Nichts-Bewertung). Die Basis/Experte-Einteilung der Fragen basiert auf eigener Klassifikation, da BSI die offizielle Verteilung nicht veröffentlicht."
                            />
                            <FaqItem
                                q="Kann ich die Simulation vor dem Kauf testen?"
                                a="Ja. Die Prüfungssimulation ist kostenlos und ohne Login zugänglich. Nach Abschluss siehst du dein Ergebnis. Erst die Antwort-Erklärungen, das Themen-Feedback und das Freie Lernen sind im Lifetime-Zugang enthalten."
                            />
                            <FaqItem
                                q="Was passiert mit meinem Probelauf, wenn ich später kaufe?"
                                a="Sobald du den Lifetime-Zugang freischaltest, wird dein anonymer Probelauf automatisch deinem Konto zugeordnet. Du siehst sofort die Erklärungen zu allen Fragen aus genau diesem Lauf."
                            />
                            <FaqItem
                                q="Wie viele Fragen sind im Pool?"
                                a="Derzeit über 160 echte BSI-Prüfungsfragen mit Erklärungen und Quellenverweisen aus den BSI-Standards 200-1, 200-2, 200-3 und dem IT-Grundschutz-Kompendium. Der Pool wächst regelmäßig."
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
        <details className="group rounded-md border border-border p-4">
            <summary className="cursor-pointer list-none font-medium [&::-webkit-details-marker]:hidden">
                <span className="inline-flex items-center gap-2">
                    <span className="text-muted-foreground transition-transform group-open:rotate-90">›</span>
                    {q}
                </span>
            </summary>
            <p className="mt-3 pl-5 text-sm leading-relaxed text-muted-foreground">{a}</p>
        </details>
    );
}
