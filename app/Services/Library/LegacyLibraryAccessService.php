<?php

namespace App\Services\Library;

use App\Models\User;

class LegacyLibraryAccessService
{
    public function ownerUserIds(): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', (array) config('toquran.legacy_library_owner_user_ids', []))
        )));
    }

    public function isConfiguredOwner(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $ownerUserIds = $this->ownerUserIds();

        if ($ownerUserIds === []) {
            return false;
        }

        return in_array((int) $user->id, $ownerUserIds, true);
    }

    public function canAccessLegacyLibrary(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->isConfiguredOwner($user);
    }
}
