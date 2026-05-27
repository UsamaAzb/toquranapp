<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CredentialService
{
    public function generateAndStore(User $user, ?string $plainPassword = null): string
    {
        $plain = $plainPassword ?? $this->defaultPasswordFor($user);

        $user->forceFill([
            'password' => Hash::make($plain),
            'recoverable_password_encrypted' => $plain,
        ])->save();

        return $plain;
    }

    public function reveal(User $user): ?string
    {
        $raw = $user->getAttributes()['recoverable_password_encrypted'] ?? null;

        if ($raw === null || $raw === '') {
            return null;
        }

        try {
            return $user->recoverable_password_encrypted;
        } catch (DecryptException) {
            return null;
        }
    }

    public function hasRecoverableCredential(User $user): bool
    {
        return $this->reveal($user) !== null;
    }

    public function defaultPasswordFor(User $user): string
    {
        $user->loadMissing(['parent_user', 'student']);

        if ($user->parent_user?->first_name) {
            return $this->generateParentPasswordForName($user->parent_user->first_name);
        }

        return $this->generateChildPassword();
    }

    public function generateParentPasswordForName(?string $firstName): string
    {
        $normalized = preg_replace('/[^\pL\pN]+/u', ' ', trim((string) $firstName)) ?? '';
        $normalized = trim(preg_replace('/\s+/u', ' ', $normalized) ?? '');

        if ($normalized === '') {
            return 'ToQuranParent';
        }

        return 'ToQuran'.str_replace(' ', '', Str::title(Str::lower($normalized)));
    }

    public function generateChildPassword(): string
    {
        return 'ToQuran';
    }
}
