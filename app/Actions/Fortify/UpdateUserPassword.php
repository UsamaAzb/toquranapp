<?php

namespace App\Actions\Fortify;

use App\Enums\AccountHistoryEventType;
use App\Models\User;
use App\Services\AccountHistoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ], [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');

        DB::transaction(function () use ($user, $input): void {
            $updates = [
                'password' => Hash::make($input['password']),
            ];

            if (Schema::hasColumn($user->getTable(), 'recoverable_password_encrypted')) {
                $updates['recoverable_password_encrypted'] = $input['password'];
            }

            $user->forceFill($updates)->save();

            if (
                $user->hasRole('parent')
                && Schema::hasTable('parents')
                && Schema::hasTable('account_histories')
                && $user->parent_user
            ) {
                app(AccountHistoryService::class)->record($user->parent_user->id, AccountHistoryEventType::ParentPasswordChangedByUser->value, [
                    'subject_type' => 'parent',
                    'subject_id' => $user->parent_user->id,
                    'actor_user_id' => $user->id,
                    'actor_role' => $user->getRoleNames()->first(),
                    'metadata' => ['subject_user_id' => $user->id],
                ]);
            }
        });
    }
}
