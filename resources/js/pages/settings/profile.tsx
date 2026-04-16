import { Form, Head, Link, usePage } from '@inertiajs/react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import AuthStatusAlert from '@/components/auth-status-alert';
import DeleteUser from '@/components/delete-user';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Profil-Einstellungen" />

            <h1 className="sr-only">Profil-Einstellungen</h1>

            <div className="flex flex-col gap-6">
                <Heading
                    variant="small"
                    title="Profilinformationen"
                    description="Aktualisiere deinen Namen und deine E-Mail-Adresse"
                />

                <Form
                    {...ProfileController.update.form()}
                    options={{
                        preserveScroll: true,
                    }}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>

                                <Input
                                    id="name"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.name}
                                    name="name"
                                    required
                                    autoComplete="name"
                                    placeholder="Vollständiger Name"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.name}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">E-Mail-Adresse</Label>

                                <Input
                                    id="email"
                                    type="email"
                                    className="mt-1 block w-full"
                                    defaultValue={auth.user.email}
                                    name="email"
                                    required
                                    autoComplete="username"
                                    placeholder="E-Mail-Adresse"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.email}
                                />
                            </div>

                            {mustVerifyEmail &&
                                auth.user.email_verified_at === null && (
                                    <div className="flex flex-col gap-2">
                                        <p className="-mt-4 text-sm text-muted-foreground">
                                            Deine E-Mail-Adresse ist noch nicht
                                            bestätigt.{' '}
                                            <Link
                                                href={send()}
                                                as="button"
                                                className="text-foreground underline underline-offset-4 transition-colors hover:text-foreground/80"
                                            >
                                                Hier klicken, um die
                                                Bestätigungs-E-Mail erneut zu
                                                senden.
                                            </Link>
                                        </p>

                                        <AuthStatusAlert
                                            status={
                                                status ===
                                                'verification-link-sent'
                                                    ? 'Ein neuer Bestätigungslink wurde an deine E-Mail-Adresse gesendet.'
                                                    : undefined
                                            }
                                        />
                                    </div>
                                )}

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="update-profile-button"
                                >
                                    Speichern
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>

            <DeleteUser />
        </>
    );
}

Profile.layout = {
    breadcrumbs: [
        {
            title: 'Profil-Einstellungen',
            href: edit(),
        },
    ],
};
