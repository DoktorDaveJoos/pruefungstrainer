import { Form } from '@inertiajs/react';
import { AlertTriangle } from 'lucide-react';
import { useRef } from 'react';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';

export default function DeleteUser() {
    const passwordInput = useRef<HTMLInputElement>(null);

    return (
        <div className="flex flex-col gap-6">
            <Heading
                variant="small"
                title="Konto löschen"
                description="Lösche dein Konto und alle zugehörigen Daten"
            />

            <Alert variant="destructive">
                <AlertTriangle />
                <AlertTitle>Warnung</AlertTitle>
                <AlertDescription>
                    Bitte vorsichtig vorgehen — dieser Schritt kann nicht
                    rückgängig gemacht werden.
                </AlertDescription>
            </Alert>

            <Dialog>
                <DialogTrigger asChild>
                    <Button
                        variant="destructive"
                        className="self-start"
                        data-test="delete-user-button"
                    >
                        Konto löschen
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogTitle>
                        Möchtest du dein Konto wirklich löschen?
                    </DialogTitle>
                    <DialogDescription>
                        Sobald dein Konto gelöscht ist, werden alle zugehörigen
                        Daten ebenfalls dauerhaft gelöscht. Bitte gib dein
                        Passwort ein, um die endgültige Löschung deines Kontos
                        zu bestätigen.
                    </DialogDescription>

                    <Form
                        {...ProfileController.destroy.form()}
                        options={{
                            preserveScroll: true,
                        }}
                        onError={() => passwordInput.current?.focus()}
                        resetOnSuccess
                        className="flex flex-col gap-6"
                    >
                        {({ resetAndClearErrors, processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label
                                        htmlFor="password"
                                        className="sr-only"
                                    >
                                        Passwort
                                    </Label>

                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        ref={passwordInput}
                                        placeholder="Passwort"
                                        autoComplete="current-password"
                                    />

                                    <InputError message={errors.password} />
                                </div>

                                <DialogFooter className="gap-2">
                                    <DialogClose asChild>
                                        <Button
                                            variant="secondary"
                                            onClick={() =>
                                                resetAndClearErrors()
                                            }
                                        >
                                            Abbrechen
                                        </Button>
                                    </DialogClose>

                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        disabled={processing}
                                        data-test="confirm-delete-user-button"
                                    >
                                        Konto löschen
                                    </Button>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>
        </div>
    );
}
