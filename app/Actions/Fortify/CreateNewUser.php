<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Services\Analytics\RecordEvent;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);

        // Local dev shortcut: skip the email verification round-trip so we
        // don't need a working mailer to test the rest of the flow.
        if (app()->environment('local')) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        // Temporarily authenticate as the new user so RecordEvent can attach
        // the user_id. Fortify's RegisteredUserController will call login()
        // again immediately after this action returns.
        auth()->login($user);
        app(RecordEvent::class)->record('registered');

        return $user;
    }
}
