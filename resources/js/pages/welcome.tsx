import { Button } from '@/components/ui/button';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/react';
import { Clock, FileCheck, Lock } from 'lucide-react';

export default function Welcome() {
    const { auth } = usePage().props as any;

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

                <main className="mx-auto max-w-3xl px-6 py-24">
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
                                <CardDescription>Zufall aus 168 BSI-Prüfungsfragen. 75 % Basis, 25 % Experte.</CardDescription>
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
                                <CardDescription>Nach der Simulation: detaillierte Antwort-Erklärungen mit BSI-Quellen ab 29 € einmalig.</CardDescription>
                            </CardHeader>
                        </Card>
                    </div>
                </main>
            </div>
        </>
    );
}
