// Components
import { Form, Head } from '@inertiajs/react';
import AuthStatusAlert from '@/components/auth-status-alert';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    const sentMessage =
        status === 'verification-link-sent'
            ? 'Ein neuer Bestätigungslink wurde an die bei der Registrierung angegebene E-Mail-Adresse gesendet. Prüfe auch den Spam-Ordner.'
            : undefined;

    return (
        <>
            <Head title="E-Mail-Adresse bestätigen" />

            <AuthStatusAlert status={sentMessage} />

            <Form
                {...send.form()}
                className="flex flex-col items-center gap-6 text-center"
            >
                {({ processing }) => (
                    <>
                        <Button disabled={processing} variant="secondary">
                            {processing && <Spinner />}
                            Bestätigungs-E-Mail erneut senden
                        </Button>

                        <TextLink href={logout()} className="text-sm">
                            Abmelden
                        </TextLink>
                    </>
                )}
            </Form>
        </>
    );
}

VerifyEmail.layout = {
    title: 'E-Mail-Adresse bestätigen',
    description:
        'Bitte bestätige deine E-Mail-Adresse, indem du auf den Link in der E-Mail klickst, die wir dir gerade gesendet haben. Prüfe auch den Spam-Ordner.',
};
