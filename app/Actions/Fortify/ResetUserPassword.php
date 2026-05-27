<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRulesFor($user),
        ])->validate();

        DB::transaction(function () use ($user, $input): void {
            $updates = [
                'password' => Hash::make($input['password']),
            ];

            if (Schema::hasColumn($user->getTable(), 'recoverable_password_encrypted')) {
                $updates['recoverable_password_encrypted'] = $input['password'];
            }

            $user->forceFill($updates)->save();
        });
    }

    /**
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    private function passwordRulesFor(User $user): array
    {
        if ($this->isChildAccount($user)) {
            return ['required', 'string', 'min:4', 'confirmed'];
        }

        return $this->passwordRules();
    }

    private function isChildAccount(User $user): bool
    {
        return Schema::hasTable('students')
            && Schema::hasColumn('students', 'user_id')
            && $user->student()->exists();
    }
}
