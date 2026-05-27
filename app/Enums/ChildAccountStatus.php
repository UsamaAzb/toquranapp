<?php

namespace App\Enums;

enum ChildAccountStatus: string
{
    case PendingActivation = 'pending_activation';
    case Active = 'active';
    case Suspended = 'suspended';
    case Archived = 'archived';

    public function toUserStatus(): string
    {
        return match ($this) {
            self::Active => 'active',
            self::Suspended => 'suspended',
            default => 'inactive',
        };
    }
}
