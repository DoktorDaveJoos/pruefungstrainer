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
            ? 'A new verification link has been sent to the email address you provided during registration.'
            : undefined;

    return (
        <>
            <Head title="Email verification" />

            <AuthStatusAlert status={sentMessage} />

            <Form {...send.form()} className="flex flex-col items-center gap-6 text-center">
                {({ processing }) => (
                    <>
                        <Button disabled={processing} variant="secondary">
                            {processing && <Spinner />}
                            Resend verification email
                        </Button>

                        <TextLink href={logout()} className="text-sm">
                            Log out
                        </TextLink>
                    </>
                )}
            </Form>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Verify email',
    description:
        'Please verify your email address by clicking on the link we just emailed to you.',
};
