import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/react';
import { CheckCircle2, Mail } from 'lucide-react';

export default function CheckoutSuccess({
    isAuthenticated,
    isPaid,
}: {
    isAuthenticated: boolean;
    isPaid: boolean;
    checkoutId: string | null;
}) {
    return (
        <>
            <Head title="Vielen Dank!" />

            <div className="min-h-screen bg-background">
                <main className="mx-auto max-w-2xl px-6 py-24">
                    <Card className="border-border">
                        <CardHeader className="items-center text-center">
                            <CheckCircle2 className="size-12 text-success" />
                            <CardTitle className="mt-4 text-3xl">Vielen Dank!</CardTitle>
                            <CardDescription className="mt-2">
                                Deine Zahlung wurde erfolgreich verarbeitet.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-6 text-center">
                            {isPaid && isAuthenticated ? (
                                <>
                                    <p className="text-sm text-muted-foreground">
                                        Dein Lifetime-Zugang ist freigeschaltet. Du kannst sofort loslegen.
                                    </p>
                                    <div className="flex justify-center gap-3">
                                        <Button asChild>
                                            <Link href="/dashboard">Zum Dashboard</Link>
                                        </Button>
                                        <Button asChild variant="outline">
                                            <Link href="/freies-lernen">Freies Lernen</Link>
                                        </Button>
                                    </div>
                                </>
                            ) : (
                                <>
                                    <p className="text-sm text-muted-foreground">
                                        Wir haben deine Zahlung erhalten. Dein Zugang wird in wenigen Sekunden freigeschaltet.
                                    </p>
                                    <div className="flex items-start gap-3 rounded-md border border-border bg-muted p-4 text-left text-sm">
                                        <Mail className="mt-0.5 size-5 shrink-0 text-muted-foreground" />
                                        <div>
                                            <div className="font-medium">Login per Magic Link</div>
                                            <div className="mt-1 text-muted-foreground">
                                                Wir haben eine E-Mail an die Adresse aus dem Checkout gesendet. Klicke auf
                                                <strong> „Passwort zurücksetzen"</strong>, um ein eigenes Passwort zu vergeben
                                                und dich anzumelden.
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex justify-center">
                                        <Button asChild variant="outline">
                                            <Link href="/forgot-password">Passwort setzen / Anmelden</Link>
                                        </Button>
                                    </div>
                                </>
                            )}
                        </CardContent>
                    </Card>
                </main>
            </div>
        </>
    );
}
