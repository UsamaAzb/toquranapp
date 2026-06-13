<?php

namespace App\Services\Library;

use App\Models\GeneralLibraryFolder;
use App\Models\GeneralLibraryResource;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class GeneralLibraryAccessService
{
    public function canView(?User $user): bool
    {
        return $user !== null
            && $this->userIsActive($user)
            && $user->hasAnyRole(['teacher', 'admin', 'super_admin']);
    }

    public function canManageFolder(?User $user, GeneralLibraryFolder $folder): bool
    {
        return $this->canManageOwnedRow($user, (int) $folder->created_by_user_id);
    }

    public function canManageResource(?User $user, GeneralLibraryResource $resource): bool
    {
        return $this->canManageOwnedRow($user, (int) $resource->created_by_user_id);
    }

    public function canUseFolder(?User $user, GeneralLibraryFolder $folder): bool
    {
        return $this->canView($user) && $folder->isActive();
    }

    public function canUseResource(?User $user, GeneralLibraryResource $resource): bool
    {
        return $this->canView($user) && $resource->isActive();
    }

    public function authorizeView(?User $user): void
    {
        if (! $this->canView($user)) {
            throw new AuthorizationException();
        }
    }

    public function authorizeManageFolder(?User $user, GeneralLibraryFolder $folder): void
    {
        if (! $this->canManageFolder($user, $folder)) {
            throw new AuthorizationException();
        }
    }

    public function authorizeManageResource(?User $user, GeneralLibraryResource $resource): void
    {
        if (! $this->canManageResource($user, $resource)) {
            throw new AuthorizationException();
        }
    }

    private function canManageOwnedRow(?User $user, int $createdByUserId): bool
    {
        return $user !== null
            && $this->userIsActive($user)
            && (
                $user->hasAnyRole(['admin', 'super_admin'])
                || ($user->hasRole('teacher') && (int) $user->id === $createdByUserId)
            );
    }

    private function userIsActive(User $user): bool
    {
        $status = $user->getAttribute('status');

        return $status === null || $status === 'active';
    }
}
