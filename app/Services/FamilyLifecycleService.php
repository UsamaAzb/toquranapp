<?php

namespace App\Services;

use App\Enums\AccountHistoryEventType;
use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Enums\LifecycleReason;
use App\Models\AccountHistory;
use App\Models\ParentModel;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class FamilyLifecycleService
{
    private const PARENT_ACTIVATION_JOB = '\\App\\Jobs\\SendParentActivationEmailJob';

    private const CHILD_ACTIVATION_JOB = '\\App\\Jobs\\SendChildActivationEmailJob';

    public function __construct(
        private readonly AccountHistoryService $history,
        private readonly CredentialService $credentials,
    ) {}

    public function activateFamily(ParentModel $parent, string $reason, int $actorUserId, string $actorRole): void
    {
        Gate::authorize('families.activate');
        $reason = $this->validateReason('activate', $reason);

        DB::transaction(function () use ($parent, $reason, $actorUserId, $actorRole): void {
            $parent = $this->lockParentForTransition($parent->id, ['user', 'students.user']);
            $this->assertFamilyStatus($parent, [FamilyLifecycleStatus::PendingActivation->value]);

            $parentUser = $parent->user;
            $this->assertParentUserPresent($parent);
            $this->assertActivationJobAvailable(self::PARENT_ACTIVATION_JOB);

            $activeChildren = $parent->students
                ->filter(fn (Student $child): bool => $child->account_status === ChildAccountStatus::Active->value);

            if ($activeChildren->isNotEmpty()) {
                $this->assertActivationJobAvailable(self::CHILD_ACTIVATION_JOB);

                foreach ($activeChildren as $child) {
                    $this->assertChildUserPresent($child);
                }
            }

            $old = $parent->lifecycle_status;
            $parent->lifecycle_status = FamilyLifecycleStatus::Active->value;
            $parent->save();

            $parentUser->status = FamilyLifecycleStatus::Active->toUserStatus();
            $parentUser->save();

            $this->history->record($parent->id, AccountHistoryEventType::FamilyActivated->value, [
                'reason_code' => $reason,
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'subject_type' => 'family',
                'subject_id' => $parent->id,
                'old_value' => $old,
                'new_value' => FamilyLifecycleStatus::Active->value,
            ]);

            if ($this->credentials->reveal($parentUser) === null) {
                $this->credentials->generateAndStore($parentUser);
            }

            $this->history->record($parent->id, AccountHistoryEventType::ParentActivationEmailQueued->value, [
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'subject_type' => 'parent',
                'subject_id' => $parent->id,
            ]);

            $this->dispatchAfterCommit(self::PARENT_ACTIVATION_JOB, $parent->id);

            foreach ($activeChildren as $child) {
                $childUser = $child->user;

                if ($this->credentials->reveal($childUser) === null) {
                    $this->credentials->generateAndStore($childUser);
                }

                $this->history->record($parent->id, AccountHistoryEventType::ChildActivationEmailQueued->value, [
                    'actor_user_id' => $actorUserId,
                    'actor_role' => $actorRole,
                    'subject_type' => 'child',
                    'subject_id' => $child->id,
                ]);

                $this->dispatchAfterCommit(self::CHILD_ACTIVATION_JOB, $child->id);
            }
        });
    }

    public function suspendFamily(ParentModel $parent, string $reason, int $actorUserId, string $actorRole): void
    {
        Gate::authorize('families.suspend');
        $reason = $this->validateReason('suspend', $reason);

        DB::transaction(function () use ($parent, $reason, $actorUserId, $actorRole): void {
            $parent = $this->lockParentForTransition($parent->id, ['user']);
            $this->assertFamilyStatus($parent, [FamilyLifecycleStatus::Active->value]);

            $old = $parent->lifecycle_status;
            $parent->lifecycle_status = FamilyLifecycleStatus::Suspended->value;
            $parent->save();

            if ($parent->user) {
                $parent->user->status = FamilyLifecycleStatus::Suspended->toUserStatus();
                $parent->user->save();
            }

            $this->history->record($parent->id, AccountHistoryEventType::FamilySuspended->value, [
                'reason_code' => $reason,
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'subject_type' => 'family',
                'subject_id' => $parent->id,
                'old_value' => $old,
                'new_value' => FamilyLifecycleStatus::Suspended->value,
            ]);
        });
    }

    public function reactivateFamily(ParentModel $parent, string $reason, int $actorUserId, string $actorRole): void
    {
        Gate::authorize('families.reactivate');
        $reason = $this->validateReason('reactivate', $reason);

        DB::transaction(function () use ($parent, $reason, $actorUserId, $actorRole): void {
            $parent = $this->lockParentForTransition($parent->id, ['user']);
            $this->assertFamilyStatus($parent, [FamilyLifecycleStatus::Suspended->value]);

            $old = $parent->lifecycle_status;
            $parent->lifecycle_status = FamilyLifecycleStatus::Active->value;
            $parent->save();

            if ($parent->user) {
                $parent->user->status = FamilyLifecycleStatus::Active->toUserStatus();
                $parent->user->save();
            }

            $this->history->record($parent->id, AccountHistoryEventType::FamilyReactivated->value, [
                'reason_code' => $reason,
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'subject_type' => 'family',
                'subject_id' => $parent->id,
                'old_value' => $old,
                'new_value' => FamilyLifecycleStatus::Active->value,
            ]);
        });
    }

    public function archiveFamily(ParentModel $parent, string $reason, int $actorUserId, string $actorRole): void
    {
        Gate::authorize('families.archive');
        $reason = $this->validateReason('archive', $reason);

        DB::transaction(function () use ($parent, $reason, $actorUserId, $actorRole): void {
            $parent = $this->lockParentForTransition($parent->id, ['user']);
            $this->assertFamilyStatus($parent, [
                FamilyLifecycleStatus::PendingActivation->value,
                FamilyLifecycleStatus::Active->value,
                FamilyLifecycleStatus::Suspended->value,
            ]);

            $old = $parent->lifecycle_status;
            $parent->lifecycle_status = FamilyLifecycleStatus::Archived->value;
            $parent->save();

            if ($parent->user) {
                $parent->user->status = FamilyLifecycleStatus::Archived->toUserStatus();
                $parent->user->save();
            }

            $this->history->record($parent->id, AccountHistoryEventType::FamilyArchived->value, [
                'reason_code' => $reason,
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'subject_type' => 'family',
                'subject_id' => $parent->id,
                'old_value' => $old,
                'new_value' => FamilyLifecycleStatus::Archived->value,
            ]);
        });
    }

    public function restoreFamily(ParentModel $parent, string $reason, int $actorUserId, string $actorRole): void
    {
        Gate::authorize('families.restore');
        $reason = $this->validateReason('restore', $reason);

        DB::transaction(function () use ($parent, $reason, $actorUserId, $actorRole): void {
            $parent = $this->lockParentForTransition($parent->id, ['user']);
            $this->assertFamilyStatus($parent, [FamilyLifecycleStatus::Archived->value]);

            $old = $parent->lifecycle_status;
            $restoreTo = $this->priorFamilyStatusBeforeArchive($parent)
                ?? FamilyLifecycleStatus::PendingActivation->value;

            $parent->lifecycle_status = $restoreTo;
            $parent->save();

            if ($parent->user) {
                $parent->user->status = FamilyLifecycleStatus::from($restoreTo)->toUserStatus();
                $parent->user->save();
            }

            $this->history->record($parent->id, AccountHistoryEventType::FamilyRestored->value, [
                'reason_code' => $reason,
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'subject_type' => 'family',
                'subject_id' => $parent->id,
                'old_value' => $old,
                'new_value' => $restoreTo,
            ]);
        });
    }

    public function activateChild(Student $child, string $reason, int $actorUserId, string $actorRole): void
    {
        Gate::authorize('families.children.activate');
        $reason = $this->validateReason('activate', $reason);

        DB::transaction(function () use ($child, $reason, $actorUserId, $actorRole): void {
            $child = $this->lockChildForTransition($child->id, ['user']);
            $this->assertChildStatus($child, [ChildAccountStatus::PendingActivation->value]);
            $parent = $this->lockParentForChild($child);
            $childUser = $child->user;
            $this->assertChildUserPresent($child);

            if ($parent->lifecycle_status === FamilyLifecycleStatus::Active->value) {
                $this->assertActivationJobAvailable(self::CHILD_ACTIVATION_JOB);
            }

            $old = $child->account_status;
            $child->account_status = ChildAccountStatus::Active->value;
            $child->save();

            $childUser->status = ChildAccountStatus::Active->toUserStatus();
            $childUser->save();

            $this->history->record($parent->id, AccountHistoryEventType::ChildActivated->value, [
                'reason_code' => $reason,
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'subject_type' => 'child',
                'subject_id' => $child->id,
                'old_value' => $old,
                'new_value' => ChildAccountStatus::Active->value,
            ]);

            if ($parent->lifecycle_status !== FamilyLifecycleStatus::Active->value) {
                return;
            }

            if ($this->credentials->reveal($childUser) === null) {
                $this->credentials->generateAndStore($childUser);
            }

            $this->history->record($parent->id, AccountHistoryEventType::ChildActivationEmailQueued->value, [
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'subject_type' => 'child',
                'subject_id' => $child->id,
            ]);

            $this->dispatchAfterCommit(self::CHILD_ACTIVATION_JOB, $child->id);
        });
    }

    public function suspendChild(Student $child, string $reason, int $actorUserId, string $actorRole): void
    {
        Gate::authorize('families.children.suspend');
        $reason = $this->validateReason('suspend', $reason);

        DB::transaction(function () use ($child, $reason, $actorUserId, $actorRole): void {
            $child = $this->lockChildForTransition($child->id, ['user']);
            $this->assertChildStatus($child, [ChildAccountStatus::Active->value]);
            $parent = $this->lockParentForChild($child);

            $old = $child->account_status;
            $child->account_status = ChildAccountStatus::Suspended->value;
            $child->save();

            if ($child->user) {
                $child->user->status = ChildAccountStatus::Suspended->toUserStatus();
                $child->user->save();
            }

            $this->history->record($parent->id, AccountHistoryEventType::ChildSuspended->value, [
                'reason_code' => $reason,
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'subject_type' => 'child',
                'subject_id' => $child->id,
                'old_value' => $old,
                'new_value' => ChildAccountStatus::Suspended->value,
            ]);
        });
    }

    public function reactivateChild(Student $child, string $reason, int $actorUserId, string $actorRole): void
    {
        Gate::authorize('families.children.reactivate');
        $reason = $this->validateReason('reactivate', $reason);

        DB::transaction(function () use ($child, $reason, $actorUserId, $actorRole): void {
            $child = $this->lockChildForTransition($child->id, ['user']);
            $this->assertChildStatus($child, [ChildAccountStatus::Suspended->value]);
            $parent = $this->lockParentForChild($child);

            $old = $child->account_status;
            $child->account_status = ChildAccountStatus::Active->value;
            $child->save();

            if ($child->user) {
                $child->user->status = ChildAccountStatus::Active->toUserStatus();
                $child->user->save();
            }

            $this->history->record($parent->id, AccountHistoryEventType::ChildReactivated->value, [
                'reason_code' => $reason,
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'subject_type' => 'child',
                'subject_id' => $child->id,
                'old_value' => $old,
                'new_value' => ChildAccountStatus::Active->value,
            ]);
        });
    }

    public function archiveChild(Student $child, string $reason, int $actorUserId, string $actorRole): void
    {
        Gate::authorize('families.children.archive');
        $reason = $this->validateReason('archive', $reason);

        DB::transaction(function () use ($child, $reason, $actorUserId, $actorRole): void {
            $child = $this->lockChildForTransition($child->id, ['user']);
            $this->assertChildStatus($child, [
                ChildAccountStatus::PendingActivation->value,
                ChildAccountStatus::Active->value,
                ChildAccountStatus::Suspended->value,
            ]);
            $parent = $this->lockParentForChild($child);

            $old = $child->account_status;
            $child->account_status = ChildAccountStatus::Archived->value;
            $child->save();

            if ($child->user) {
                $child->user->status = ChildAccountStatus::Archived->toUserStatus();
                $child->user->save();
            }

            $this->history->record($parent->id, AccountHistoryEventType::ChildArchived->value, [
                'reason_code' => $reason,
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'subject_type' => 'child',
                'subject_id' => $child->id,
                'old_value' => $old,
                'new_value' => ChildAccountStatus::Archived->value,
            ]);
        });
    }

    public function restoreChild(Student $child, string $reason, int $actorUserId, string $actorRole): void
    {
        Gate::authorize('families.children.restore');
        $reason = $this->validateReason('restore', $reason);

        DB::transaction(function () use ($child, $reason, $actorUserId, $actorRole): void {
            $child = $this->lockChildForTransition($child->id, ['user']);
            $this->assertChildStatus($child, [ChildAccountStatus::Archived->value]);
            $parent = $this->lockParentForChild($child);

            $old = $child->account_status;
            $restoreTo = $this->priorChildStatusBeforeArchive($child)
                ?? ChildAccountStatus::PendingActivation->value;

            $child->account_status = $restoreTo;
            $child->save();

            if ($child->user) {
                $child->user->status = ChildAccountStatus::from($restoreTo)->toUserStatus();
                $child->user->save();
            }

            $this->history->record($parent->id, AccountHistoryEventType::ChildRestored->value, [
                'reason_code' => $reason,
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'subject_type' => 'child',
                'subject_id' => $child->id,
                'old_value' => $old,
                'new_value' => $restoreTo,
            ]);
        });
    }

    private function priorFamilyStatusBeforeArchive(ParentModel $parent): ?string
    {
        $oldValue = AccountHistory::where('parent_id', $parent->id)
            ->where('event_type', AccountHistoryEventType::FamilyArchived->value)
            ->where('subject_type', 'family')
            ->where('subject_id', $parent->id)
            ->latest('id')
            ->value('old_value');

        return in_array($oldValue, $this->familyStatusValues(), true) ? $oldValue : null;
    }

    private function priorChildStatusBeforeArchive(Student $child): ?string
    {
        $oldValue = AccountHistory::where('parent_id', $child->parent_id)
            ->where('event_type', AccountHistoryEventType::ChildArchived->value)
            ->where('subject_type', 'child')
            ->where('subject_id', $child->id)
            ->latest('id')
            ->value('old_value');

        return in_array($oldValue, $this->childStatusValues(), true) ? $oldValue : null;
    }

    private function validateReason(string $action, string $reason): string
    {
        $reason = trim($reason);
        $valid = LifecycleReason::forAction($action);

        if (! in_array($reason, $valid, true)) {
            throw new InvalidArgumentException("Invalid reason '{$reason}' for action '{$action}'.");
        }

        return $reason;
    }

    /** @param  list<string>  $allowed */
    private function assertFamilyStatus(ParentModel $parent, array $allowed): void
    {
        if (! in_array($parent->lifecycle_status, $allowed, true)) {
            throw new InvalidArgumentException("Family lifecycle transition not allowed from '{$this->statusForMessage($parent->lifecycle_status)}'.");
        }
    }

    /** @param  list<string>  $allowed */
    private function assertChildStatus(Student $child, array $allowed): void
    {
        if (! in_array($child->account_status, $allowed, true)) {
            throw new InvalidArgumentException("Child account transition not allowed from '{$this->statusForMessage($child->account_status)}'.");
        }
    }

    private function statusForMessage(?string $status): string
    {
        return $status ?? 'unclassified';
    }

    private function lockParentForTransition(int $parentId, array $relations = []): ParentModel
    {
        $parent = ParentModel::whereKey($parentId)->lockForUpdate()->firstOrFail();
        $parent->load($relations);

        return $parent;
    }

    private function lockChildForTransition(int $childId, array $relations = []): Student
    {
        $child = Student::whereKey($childId)->lockForUpdate()->firstOrFail();
        $child->load($relations);

        return $child;
    }

    private function lockParentForChild(Student $child): ParentModel
    {
        if (! $child->parent_id) {
            throw new InvalidArgumentException('Child must have a linked parent before lifecycle changes.');
        }

        return $this->lockParentForTransition((int) $child->parent_id);
    }

    private function assertParentUserPresent(ParentModel $parent): void
    {
        if (! $parent->user) {
            throw new InvalidArgumentException('Family must have a linked parent user before activation.');
        }
    }

    private function assertChildUserPresent(Student $child): void
    {
        if (! $child->user) {
            throw new InvalidArgumentException('Child must have a linked user before activation.');
        }
    }

    private function assertActivationJobAvailable(string $jobClass): void
    {
        if (! class_exists($jobClass)) {
            throw new InvalidArgumentException("Activation email job [{$jobClass}] is not available.");
        }
    }

    private function dispatchAfterCommit(string $jobClass, int $subjectId): void
    {
        DB::afterCommit(static fn () => $jobClass::dispatch($subjectId));
    }

    /** @return list<string> */
    private function familyStatusValues(): array
    {
        return array_map(static fn (FamilyLifecycleStatus $status): string => $status->value, FamilyLifecycleStatus::cases());
    }

    /** @return list<string> */
    private function childStatusValues(): array
    {
        return array_map(static fn (ChildAccountStatus $status): string => $status->value, ChildAccountStatus::cases());
    }
}
