<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Http\Responses\LoginResponse as CustomLoginResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    private const FAMILY_LIFECYCLE_ROLES = ['parent', 'student'];

    private ?bool $userStatusColumnAvailable = null;

    private ?bool $familyLifecycleSchemaCache = null;

    public function register(): void
    {
        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateUsing(function (Request $request) {
            $identifier = Fortify::email();
            $genericLoginMessage = __('We could not sign you in. Please check your details or contact support.');
            $loginValue = trim((string) $request->get($identifier));
            $normalizedLoginValue = Str::lower($loginValue);
            $userQuery = User::query();

            if ($this->familyLifecycleSchemaAvailable()) {
                $userQuery->with(['parent_user', 'student.parent']);
            }

            $user = $userQuery
                ->where(function ($query) use ($identifier, $normalizedLoginValue) {
                    $query->whereRaw('LOWER('.$identifier.') = ?', [$normalizedLoginValue])
                        ->orWhereRaw('LOWER(name) = ?', [$normalizedLoginValue]);
                })
                ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                if (! $this->userStatusAllowsLogin($user)) {
                    throw ValidationException::withMessages([
                        $identifier => $genericLoginMessage,
                    ]);
                }

                $lifecycleRole = $this->familyLifecycleSchemaAvailable()
                    ? $this->familyLifecycleGateRole($user)
                    : null;

                if ($lifecycleRole === 'parent') {
                    $parentProfile = $user->parent_user;

                    if ($parentProfile?->lifecycle_status !== FamilyLifecycleStatus::Active->value) {
                        throw ValidationException::withMessages([
                            $identifier => $genericLoginMessage,
                        ]);
                    }
                } elseif ($lifecycleRole === 'student') {
                    $studentProfile = $user->student;

                    if ($studentProfile?->account_status !== ChildAccountStatus::Active->value
                        || $studentProfile?->parent?->lifecycle_status !== FamilyLifecycleStatus::Active->value) {
                        throw ValidationException::withMessages([
                            $identifier => $genericLoginMessage,
                        ]);
                    }
                }

                return $user;
            }

            throw ValidationException::withMessages([
                $identifier => $genericLoginMessage,
            ]);
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }

    private function familyLifecycleGateRole(User $user): ?string
    {
        $roleNames = $user->getRoleNames();

        // Staff/internal roles keep legacy login behavior even if a support account also has
        // a parent/student profile attached. Only pure family accounts are lifecycle-gated.
        if ($roleNames->diff(self::FAMILY_LIFECYCLE_ROLES)->isNotEmpty()) {
            return null;
        }

        if ($roleNames->contains('parent')) {
            return 'parent';
        }

        if ($roleNames->contains('student')) {
            return 'student';
        }

        return null;
    }

    private function userStatusAllowsLogin(User $user): bool
    {
        if ($this->userStatusColumnAvailable === null) {
            $this->userStatusColumnAvailable = Schema::hasColumn($user->getTable(), 'status');
        }

        if (! $this->userStatusColumnAvailable) {
            return true;
        }

        return ($user->status ?? 'active') === 'active';
    }

    private function familyLifecycleSchemaAvailable(): bool
    {
        if ($this->familyLifecycleSchemaCache !== null) {
            return $this->familyLifecycleSchemaCache;
        }

        return $this->familyLifecycleSchemaCache = Schema::hasTable('parents')
            && Schema::hasTable('students')
            && Schema::hasColumn('parents', 'lifecycle_status')
            && Schema::hasColumn('students', 'account_status');
    }
}
