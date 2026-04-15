import { Head, Link } from '@inertiajs/react';
import { CheckCircle2, Mail } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

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
                <main className="mx-auto max-w-md px-4 py-16 sm:px-6">
                    <Card>
                        <CardHeader className="items-center text-center">
                            <CheckCircle2 className="size-12 text-success" />
                            <CardTitle className="mt-2 text-2xl">Vielen Dank!</CardTitle>
                            <CardDescription>
                                Deine Zahlung wurde erfolgreich verarbeitet.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-6 text-center">
                            {isPaid && isAuthenticated ? (
                                <>
                                    <p className="text-sm text-muted-foreground">
                                        Dein Lifetime-Zugang ist freigeschaltet. Du kannst sofort loslegen.
                                    </p>
                                    <div className="flex justify-center gap-2">
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
                                    <Alert className="text-left">
                                        <Mail />
                                        <AlertTitle>Login per Magic Link</AlertTitle>
                                        <AlertDescription>
                                            Wir haben eine E-Mail an die Adresse aus dem Checkout gesendet. Klicke auf
                                            <strong> „Passwort zurücksetzen"</strong>, um ein eigenes Passwort zu vergeben
                                            und dich anzumelden.
                                        </AlertDescription>
                                    </Alert>
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
