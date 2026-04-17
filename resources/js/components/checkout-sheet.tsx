import { Form, Link } from '@inertiajs/react';
import { Lock } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Spinner } from '@/components/ui/spinner';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { store as loginStore } from '@/routes/login';
import { store as registerStore } from '@/routes/register';

type Props = {
    trigger: React.ReactNode;
    priceLabel: string;
};

export function CheckoutSheet({ trigger, priceLabel }: Props) {
    const [open, setOpen] = useState(false);

    const intentQuery = { query: { intent: 'checkout' } };

    return (
        <Sheet open={open} onOpenChange={setOpen}>
            <SheetTrigger asChild>{trigger}</SheetTrigger>
            <SheetContent className="sm:max-w-md">
                <SheetHeader>
                    <SheetTitle className="flex items-center gap-2">
                        <Lock className="size-4" />
                        12 Monate Zugang freischalten · {priceLabel}
                    </SheetTitle>
                </SheetHeader>

                <Tabs defaultValue="register" className="mt-2 px-4 pb-4">
                    <TabsList className="grid w-full grid-cols-2">
                        <TabsTrigger value="register">Neu hier</TabsTrigger>
                        <TabsTrigger value="login">Schon Konto</TabsTrigger>
                    </TabsList>

                    <TabsContent value="register" className="mt-4">
                        <Form
                            {...registerStore.form(intentQuery)}
                            resetOnSuccess={['password', 'password_confirmation']}
                            disableWhileProcessing
                            className="flex flex-col gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="register-email">E-Mail</Label>
                                            <Input
                                                id="register-email"
                                                type="email"
                                                name="email"
                                                autoComplete="email"
                                                required
                                            />
                                            <InputError message={errors.email} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="register-password">Passwort</Label>
                                            <PasswordInput
                                                id="register-password"
                                                name="password"
                                                autoComplete="new-password"
                                                required
                                            />
                                            <InputError message={errors.password} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="register-password-confirmation">Passwort bestätigen</Label>
                                            <PasswordInput
                                                id="register-password-confirmation"
                                                name="password_confirmation"
                                                autoComplete="new-password"
                                                required
                                            />
                                            <InputError message={errors.password_confirmation} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="register-name">Name (optional)</Label>
                                            <Input
                                                id="register-name"
                                                type="text"
                                                name="name"
                                                autoComplete="name"
                                            />
                                            <InputError message={errors.name} />
                                        </div>
                                    </div>

                                    <Button type="submit" className="w-full" disabled={processing}>
                                        {processing && <Spinner />}
                                        Konto erstellen und weiter zur Zahlung
                                    </Button>
                                </>
                            )}
                        </Form>
                    </TabsContent>

                    <TabsContent value="login" className="mt-4">
                        <div className="flex flex-col gap-4">
                            <p className="text-sm text-muted-foreground">
                                Du hast schon ein Konto? Melde dich an und setze den Kauf fort.
                            </p>
                            <Button asChild className="w-full">
                                <Link href={loginStore.url(intentQuery)}>Zum Login</Link>
                            </Button>
                        </div>
                    </TabsContent>
                </Tabs>
            </SheetContent>
        </Sheet>
    );
}
