<?php

namespace App\Support;

use App\Models\User;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class RewardGiftVisibility
{
    public const TEACHER_DETAIL_PERMISSION = 'view student reward gift details';

    public static function canViewDetails(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['student', 'parent', 'admin', 'super_admin'])) {
            return true;
        }

        if (! $user->hasRole('teacher')) {
            return false;
        }

        try {
            return $user->hasPermissionTo(self::TEACHER_DETAIL_PERMISSION);
        } catch (PermissionDoesNotExist) {
            return false;
        }
    }
}
