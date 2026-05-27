<?php

namespace App\Support;

use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Models\Student;

class LifecycleGate
{
    public const NEUTRAL_MESSAGE = 'This account is not currently active.';

    public static function inspect(int $studentId): LifecycleGateResult
    {
        $student = Student::with('parent')->findOrFail($studentId);
        $parent = $student->parent;

        if ($student->account_status !== ChildAccountStatus::Active->value) {
            return new LifecycleGateResult($student, $parent, false, 'student');
        }

        if ($parent?->lifecycle_status !== FamilyLifecycleStatus::Active->value) {
            return new LifecycleGateResult($student, $parent, false, 'parent');
        }

        return new LifecycleGateResult($student, $parent, true);
    }
}
