import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { FileCheck, GraduationCap } from 'lucide-react';

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={[{ title: 'Dashboard', href: '/dashboard' }]}>
            <Head title="Dashboard" />

            <div className="mx-auto max-w-4xl px-6 py-8">
                <h1 className="text-3xl font-bold tracking-tight">Willkommen zurück</h1>
                <p className="mt-2 text-muted-foreground">
                    Wähle einen Modus, um weiterzulernen.
                </p>

                <div className="mt-8 grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <FileCheck className="size-7 text-primary" />
                            <CardTitle className="mt-3">Prüfungssimulation</CardTitle>
                            <CardDescription>
                                50 Fragen, 60 Minuten, ohne Feedback — wie die echte BSI-Prüfung.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form method="POST" action="/pruefungssimulation/start">
                                <input
                                    type="hidden"
                                    name="_token"
                                    value={(document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? ''}
                                />
                                <Button type="submit" className="w-full">
                                    Simulation starten
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <GraduationCap className="size-7 text-primary" />
                            <CardTitle className="mt-3">Freies Lernen</CardTitle>
                            <CardDescription>
                                Fragen einzeln mit sofortigem Feedback und Quellen-Zitat. Kommt bald.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Button asChild variant="outline" className="w-full">
                                <Link href="/freies-lernen">Freies Lernen starten</Link>
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
