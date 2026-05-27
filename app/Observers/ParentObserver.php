<?php

namespace App\Observers;

use App\Enums\FamilyLifecycleStatus;
use App\Models\ParentModel;

class ParentObserver
{
    public function saving(ParentModel $parent): void
    {
        $parent->active = $parent->lifecycle_status === FamilyLifecycleStatus::Active->value;
    }
}
