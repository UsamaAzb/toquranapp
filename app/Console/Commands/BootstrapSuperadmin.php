<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CredentialService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class BootstrapSuperadmin extends Command
{
    protected $signature = 'toquran:bootstrap-superadmin
        {--confirm-db= : Required database name confirmation}
        {--email= : Superadmin email}
        {--name= : Superadmin full name}
        {--phone= : Optional phone number}
        {--password= : Optional initial password. If omitted, a one-time password is generated.}';

    protected $description = 'Create or repair the first active To Quran superadmin account after explicit DB target confirmation.';

    public function handle(CredentialService $credentials): int
    {
        $databaseName = DB::connection()->getDatabaseName();
        $confirmedDatabase = (string) $this->option('confirm-db');

        if ($confirmedDatabase === '' || $confirmedDatabase !== $databaseName) {
            $this->error("ABORTED: pass --confirm-db={$databaseName} to confirm the current DB target.");

            return self::FAILURE;
        }

        $email = trim((string) ($this->option('email') ?: $this->ask('Superadmin email')));
        $name = trim((string) ($this->option('name') ?: $this->ask('Superadmin full name', 'To Quran Superadmin')));
        $phone = trim((string) ($this->option('phone') ?? ''));
        $plainPassword = (string) ($this->option('password') ?: $this->generatePassword());
        $generated = ! $this->option('password');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('ABORTED: provide a valid email address.');

            return self::FAILURE;
        }

        Role::findOrCreate('super_admin', 'web');

        $user = User::query()->firstOrNew(['email' => $email]);
        if (! $user->exists) {
            $user->forceFill(['password' => Hash::make($plainPassword)]);
        }

        $attributes = [
            'name' => $name,
            'email' => $email,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ];

        [$firstName, $lastName] = $this->splitName($name);

        foreach ([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone !== '' ? $phone : null,
            'status' => 'active',
        ] as $column => $value) {
            if (Schema::hasColumn($user->getTable(), $column)) {
                $attributes[$column] = $value;
            }
        }

        $user->forceFill($attributes)->save();
        $credentials->generateAndStore($user, $plainPassword);
        $user->assignRole('super_admin');

        $this->info("Superadmin ready: {$user->email} (user #{$user->id})");

        if ($generated) {
            $this->warn('Generated one-time password: '.$plainPassword);
        }

        return self::SUCCESS;
    }

    private function generatePassword(): string
    {
        return 'TQ-'.Str::random(18);
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2) ?: [];

        return [
            $parts[0] ?? $name,
            $parts[1] ?? null,
        ];
    }
}
