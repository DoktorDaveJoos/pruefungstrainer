// Components
import { Form, Head } from '@inertiajs/react';
import AuthStatusAlert from '@/components/auth-status-alert';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <>
            <Head title="Passwort vergessen" />

            <AuthStatusAlert status={status} />

            <div className="flex flex-col gap-6">
                <Form {...email.form()}>
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="email">E-Mail-Adresse</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    autoComplete="off"
                                    autoFocus
                                    placeholder="name@beispiel.de"
                                />

                                <InputError message={errors.email} />
                            </div>

                            <div className="my-6 flex items-center justify-start">
                                <Button
                                    className="w-full"
                                    disabled={processing}
                                    data-test="email-password-reset-link-button"
                                >
                                    {processing && <Spinner />}
                                    Link zum Zurücksetzen senden
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                <div className="flex items-center justify-center gap-1 text-center text-sm text-muted-foreground">
                    <span>Oder zurück zur</span>
                    <TextLink href={login()}>Anmeldung</TextLink>
                </div>
            </div>
        </>
    );
}

ForgotPassword.layout = {
    title: 'Passwort vergessen',
    description:
        'Gib deine E-Mail-Adresse ein, um einen Link zum Zurücksetzen zu erhalten',
};
