<?php

namespace App\Support;

use App\Models\ParentModel;
use App\Models\Student;

class LifecycleGateResult
{
    public function __construct(
        public readonly Student $student,
        public readonly ?ParentModel $parent,
        public readonly bool $allowed,
        public readonly ?string $failedSide = null,
    ) {}

    public function denied(): bool
    {
        return ! $this->allowed;
    }
}
