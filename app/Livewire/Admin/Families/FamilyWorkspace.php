<?php

namespace App\Livewire\Admin\Families;

use App\Enums\AccountHistoryEventType;
use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Enums\LifecycleReason;
use App\Jobs\SendChildActivationEmailJob;
use App\Jobs\SendParentActivationEmailJob;
use App\Mail\ChildPasswordResetLinkMail;
use App\Mail\ParentPasswordResetLinkMail;
use App\Models\AccountHistory;
use App\Models\BookingChild;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use App\Services\AccountHistoryService;
use App\Services\CredentialService;
use App\Services\FamilyLifecycleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Throwable;

class FamilyWorkspace extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    #[Locked]
    public int $parentId;

    public ?string $revealedCredential = null;

    public ?string $revealSubjectType = null;

    public ?int $revealUserId = null;

    public bool $revealMasked = true;

    public bool $showRevealModal = false;

    public ?string $pendingLifecycleAction = null;

    public ?string $pendingLifecycleTargetType = null;

    public ?int $pendingLifecycleTargetId = null;

    public ?string $lifecycleReason = null;

    public bool $showLifecycleModal = false;

    public array $parentEditForm = [];

    public bool $showParentEditModal = false;

    public string $activeTab = 'overview';

    private ?User $authorizationUserCache = null;

    private array $authorizationAbilityCache = [];

    private const FAMILY_VIEW_ROLES = ['admin', 'super_admin', 'customer_support'];

    public function mount(ParentModel $parent): void
    {
        $this->authorizeWorkspaceAbility('families.view_workspace');

        $this->parentId = $parent->id;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = array_key_exists($tab, $this->visibleTabs())
            ? $tab
            : 'overview';

        if ($this->activeTab === 'security') {
            $this->resetPage(pageName: 'historyPage');
        }
    }

    public function openParentEditModal(): void
    {
        $this->authorizeParentEdit();

        $parent = $this->parent;
        $user = $parent->user;

        $this->resetErrorBag([
            'parentEditForm.first_name',
            'parentEditForm.last_name',
            'parentEditForm.email',
            'parentEditForm.phone',
        ]);

        $this->parentEditForm = [
            'first_name' => (string) ($parent->first_name ?? ''),
            'last_name' => (string) ($parent->last_name ?? ''),
            'email' => (string) ($user?->email ?? $parent->email ?? ''),
            'phone' => (string) ($parent->phone ?? $user?->phone ?? ''),
        ];
        $this->showParentEditModal = true;

        $this->dispatch('family-workspace-parent-edit-open');
    }

    public function closeParentEditModal(): void
    {
        $this->resetParentEditState();
        $this->dispatch('family-workspace-parent-edit-close');
    }

    #[On('parent-edit-modal-hidden')]
    public function handleParentEditModalHidden(): void
    {
        $this->resetParentEditState();
    }

    public function saveParentEdit(): void
    {
        $this->authorizeParentEdit();

        $parent = $this->parent;
        $user = $parent->user;

        $validated = $this->validate($this->parentEditRules($parent, $user));

        $payload = [
            'first_name' => trim((string) ($validated['parentEditForm']['first_name'] ?? '')),
            'last_name' => trim((string) ($validated['parentEditForm']['last_name'] ?? '')),
            'email' => trim((string) ($validated['parentEditForm']['email'] ?? '')),
            'phone' => trim((string) ($validated['parentEditForm']['phone'] ?? '')),
        ];

        DB::transaction(function () use ($parent, $payload, $user): void {
            $parent->fill($payload)->save();

            if ($user) {
                $user->forceFill([
                    'first_name' => $payload['first_name'],
                    'last_name' => $payload['last_name'],
                    'email' => $payload['email'],
                    'phone' => $payload['phone'] !== '' ? $payload['phone'] : null,
                ])->save();
            }
        });

        $this->refreshWorkspaceState();
        $this->closeParentEditModal();
        session()->flash('success', 'Parent details updated.');
    }

    public function activateFamily(): void
    {
        $this->openLifecycleModal('activate', 'family', $this->parentId);
    }

    public function activateChild(int $studentId): void
    {
        $this->openLifecycleModal('activate', 'child', $studentId);
    }

    public function openLifecycleModal(string $action, string $targetType, ?int $targetId = null): void
    {
        $this->resetErrorBag(['lifecycleAction', 'lifecycleReason']);

        try {
            [$action, $targetType, $target] = $this->resolveLifecycleSelection($action, $targetType, $targetId);
            $this->authorizeWorkspaceAbility($this->permissionFor($action, $targetType));
        } catch (InvalidArgumentException $exception) {
            $this->addError('lifecycleAction', $exception->getMessage());

            return;
        }

        $this->pendingLifecycleAction = $action;
        $this->pendingLifecycleTargetType = $targetType;
        $this->pendingLifecycleTargetId = $target->id;
        $this->lifecycleReason = null;
        $this->showLifecycleModal = true;

        $this->dispatch('family-workspace-lifecycle-open');
    }

    public function confirmLifecycleAction(): void
    {
        $this->resetErrorBag(['lifecycleAction', 'lifecycleReason']);

        if (blank($this->lifecycleReason)) {
            $this->addError('lifecycleReason', 'Select a reason to continue.');

            return;
        }

        try {
            [$action, $targetType, $target] = $this->resolveLifecycleSelection(
                (string) $this->pendingLifecycleAction,
                (string) $this->pendingLifecycleTargetType,
                $this->pendingLifecycleTargetId
            );

            $this->authorizeWorkspaceAbility($this->permissionFor($action, $targetType));
            $this->executeLifecycleAction($action, $targetType, $target, (string) $this->lifecycleReason);
        } catch (InvalidArgumentException $exception) {
            $this->addError('lifecycleAction', $exception->getMessage());

            return;
        }

        $targetName = $targetType === 'family'
            ? $this->parent->display_name
            : $target->display_name;

        $this->closeLifecycleModal();
        $this->refreshWorkspaceState();
        session()->flash('success', $this->successMessageFor($action, $targetType, $targetName));
    }

    public function closeLifecycleModal(): void
    {
        $this->resetLifecycleState();
        $this->dispatch('family-workspace-lifecycle-close');
    }

    #[On('lifecycle-modal-hidden')]
    public function handleLifecycleModalHidden(): void
    {
        $this->resetLifecycleState();
    }

    public function openRevealModal(int $userId, string $subjectType): void
    {
        [$user, $subjectId] = $this->resolveSubjectUser($userId, $subjectType);
        $history = app(AccountHistoryService::class);

        if (! $this->userCan('families.credentials.reveal')) {
            $history->record($this->parentId, AccountHistoryEventType::CredentialRevealDenied->value, [
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'metadata' => ['subject_user_id' => $user->id],
            ]);

            session()->flash('error', 'You do not have permission to reveal credentials.');

            return;
        }

        $credentials = app(CredentialService::class);
        $plain = $credentials->reveal($user);

        if ($plain === null) {
            session()->flash('error', 'No recoverable credential. Generate a new password or send a reset link first.');

            return;
        }

        $eventType = $subjectType === 'parent'
            ? AccountHistoryEventType::ParentPasswordRevealed->value
            : AccountHistoryEventType::ChildPasswordRevealed->value;

        $history->record($this->parentId, $eventType, [
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'metadata' => ['subject_user_id' => $user->id],
        ]);

        $this->revealedCredential = $plain;
        $this->revealSubjectType = $subjectType;
        $this->revealUserId = $user->id;
        $this->revealMasked = true;
        $this->showRevealModal = true;

        $this->dispatch('family-workspace-reveal-open');
    }

    public function closeRevealModal(): void
    {
        $this->resetRevealState();
        $this->dispatch('family-workspace-reveal-close');
    }

    #[On('reveal-modal-hidden')]
    public function handleRevealModalHidden(): void
    {
        $this->resetRevealState();
    }

    public function toggleRevealMask(): void
    {
        $this->revealMasked = ! $this->revealMasked;
    }

    public function sendPasswordResetLink(int $userId, string $subjectType): void
    {
        $this->authorizeWorkspaceAbility('families.credentials.send_reset_link');

        [$user, $subjectId] = $this->resolveSubjectUser($userId, $subjectType);

        if ($subjectType === 'parent') {
            if (blank($user->email)) {
                session()->flash('error', 'The parent account does not have an email address for reset links.');

                return;
            }

            $token = Password::broker()->createToken($user);
            $resetUrl = $this->passwordResetUrl($token, (string) $user->email);

            try {
                Mail::to($user->email, $this->parent->full_name)
                    ->send(new ParentPasswordResetLinkMail($this->parent, $user, $resetUrl));
            } catch (Throwable $exception) {
                report($exception);

                app(AccountHistoryService::class)->record($this->parentId, AccountHistoryEventType::ParentPasswordResetLinkFailed->value, [
                    'subject_type' => 'parent',
                    'subject_id' => $subjectId,
                    'metadata' => [
                        'subject_user_id' => $user->id,
                        'recipient_email' => $user->email,
                        'error' => $exception->getMessage(),
                    ],
                ]);

                session()->flash('error', 'Unable to send the parent reset link right now. Please try again.');

                return;
            }

            app(AccountHistoryService::class)->record($this->parentId, AccountHistoryEventType::ParentPasswordResetLinkSent->value, [
                'subject_type' => 'parent',
                'subject_id' => $subjectId,
                'metadata' => ['subject_user_id' => $user->id],
            ]);

            session()->flash('success', 'Password reset link sent.');

            return;
        }

        if (blank($this->parent->email)) {
            session()->flash('error', 'The family needs a parent email before a child reset link can be sent.');

            return;
        }

        if (blank($user->email)) {
            session()->flash('error', 'The child account needs an email address before a reset link can be generated.');

            return;
        }

        $child = $this->parent->students()
            ->whereKey($subjectId)
            ->firstOrFail();
        $token = Password::broker()->createToken($user);
        $resetUrl = $this->passwordResetUrl($token, (string) $user->email);

        try {
            Mail::to($this->parent->email, $this->parent->full_name)
                ->send(new ChildPasswordResetLinkMail($this->parent, $child, $user, $resetUrl));
        } catch (Throwable $exception) {
            report($exception);

            app(AccountHistoryService::class)->record($this->parentId, AccountHistoryEventType::ChildPasswordResetLinkFailed->value, [
                'subject_type' => 'child',
                'subject_id' => $subjectId,
                'metadata' => [
                    'subject_user_id' => $user->id,
                    'recipient_email' => $this->parent->email,
                    'error' => $exception->getMessage(),
                ],
            ]);

            session()->flash('error', 'Unable to send the child reset link right now. Please try again.');

            return;
        }

        app(AccountHistoryService::class)->record($this->parentId, AccountHistoryEventType::ChildPasswordResetLinkSent->value, [
            'subject_type' => 'child',
            'subject_id' => $subjectId,
            'metadata' => [
                'subject_user_id' => $user->id,
                'recipient_email' => $this->parent->email,
            ],
        ]);

        session()->flash('success', 'Password reset link sent.');
    }

    public function generateNewPassword(int $userId, string $subjectType): void
    {
        $this->authorizeWorkspaceAbility('families.credentials.generate_password');

        [$user, $subjectId] = $this->resolveSubjectUser($userId, $subjectType);
        $plain = DB::transaction(function () use ($subjectId, $subjectType, $user): string {
            $plain = app(CredentialService::class)->generateAndStore($user);

            $eventType = $subjectType === 'parent'
                ? AccountHistoryEventType::ParentPasswordResetByAdmin->value
                : AccountHistoryEventType::ChildPasswordResetByAdmin->value;

            app(AccountHistoryService::class)->record($this->parentId, $eventType, [
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'metadata' => ['subject_user_id' => $user->id],
            ]);

            return $plain;
        });

        $this->revealedCredential = $plain;
        $this->revealSubjectType = $subjectType;
        $this->revealUserId = $user->id;
        $this->revealMasked = true;
        $this->showRevealModal = true;

        session()->flash('success', 'New password generated.');
        $this->dispatch('family-workspace-reveal-open');
    }

    public function resendActivationEmail(int $userId, string $subjectType): void
    {
        $this->authorizeWorkspaceAbility('families.credentials.resend_activation');

        [$user, $subjectId] = $this->resolveSubjectUser($userId, $subjectType);

        if ($subjectType === 'parent') {
            if ($this->parent->lifecycle_status !== FamilyLifecycleStatus::Active->value) {
                session()->flash('error', 'Parent activation email can only be resent for an active family.');

                return;
            }

            $historyWatermark = $this->latestActivationHistoryId();

            app(AccountHistoryService::class)->record($this->parentId, AccountHistoryEventType::ActivationEmailResent->value, [
                'subject_type' => 'parent',
                'subject_id' => $subjectId,
                'metadata' => ['subject_user_id' => $user->id],
            ]);

            try {
                SendParentActivationEmailJob::dispatchSync($this->parentId, true);
            } catch (Throwable $exception) {
                report($exception);

                session()->flash('error', 'Unable to resend the parent activation email right now. Please try again.');

                return;
            }

            if (! $this->activationEmailWasSentAfter(
                $historyWatermark,
                AccountHistoryEventType::ParentActivationEmailSent->value,
                'parent',
                $subjectId
            )) {
                session()->flash('error', 'Parent activation email was not sent. Check Security & Log for the skip reason.');

                return;
            }

            session()->flash('success', 'Parent activation email sent.');

            return;
        }

        $child = $this->parent->students()
            ->whereKey($subjectId)
            ->firstOrFail();

        if ($this->parent->lifecycle_status !== FamilyLifecycleStatus::Active->value
            || $child->account_status !== ChildAccountStatus::Active->value) {
            session()->flash('error', 'Child activation email can only be resent for an active child in an active family.');

            return;
        }

        $historyWatermark = $this->latestActivationHistoryId();

        app(AccountHistoryService::class)->record($this->parentId, AccountHistoryEventType::ActivationEmailResent->value, [
            'subject_type' => 'child',
            'subject_id' => $subjectId,
            'metadata' => ['subject_user_id' => $user->id],
        ]);

        try {
            SendChildActivationEmailJob::dispatchSync($child->id, true);
        } catch (Throwable $exception) {
            report($exception);

            session()->flash('error', 'Unable to resend the child activation email right now. Please try again.');

            return;
        }

        if (! $this->activationEmailWasSentAfter(
            $historyWatermark,
            AccountHistoryEventType::ChildActivationEmailSent->value,
            'child',
            $subjectId
        )) {
            session()->flash('error', 'Child activation email was not sent. Check Security & Log for the skip reason.');

            return;
        }

        session()->flash('success', "Child activation email sent for {$child->display_name}.");
    }

    #[Computed]
    public function parent(): ParentModel
    {
        return ParentModel::with('user')->findOrFail($this->parentId);
    }

    #[Computed]
    public function children(): Collection
    {
        $query = Student::with('user')
            ->where('parent_id', $this->parentId)
            ->orderBy('first_name')
            ->orderBy('id');

        if (Schema::hasTable('grade_levels')) {
            $query->with('gradeLevel');
        }

        if (Schema::hasTable('classes')) {
            $query->with('currentClass');
        }

        return $query->get();
    }

    #[Computed]
    public function accountHistory(): LengthAwarePaginator
    {
        if ($this->activeTab !== 'security' || ! $this->canViewHistory()) {
            return $this->emptyPaginator('historyPage');
        }

        return AccountHistory::with('actor')
            ->where('parent_id', $this->parentId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20, pageName: 'historyPage');
    }

    #[Computed]
    public function consultationHistory(): Collection
    {
        if ($this->activeTab !== 'consultation'
            || ! Schema::hasTable('booking_children')
            || ! Schema::hasTable('bookings')) {
            return collect();
        }

        $studentIds = $this->children->pluck('id')->all();

        if ($studentIds === []) {
            return collect();
        }

        return BookingChild::with('booking')
            ->whereIn('student_id', $studentIds)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();
    }

    #[Computed]
    public function currentClassHistoryCounts(): Collection
    {
        if (! Schema::hasTable('student_classes_history')) {
            return collect();
        }

        $studentIds = $this->children->pluck('id')->all();

        if ($studentIds === []) {
            return collect();
        }

        return DB::table('student_classes_history')
            ->select('student_id', DB::raw('COUNT(*) as current_count'))
            ->whereIn('student_id', $studentIds)
            ->where('status', 'current')
            ->groupBy('student_id')
            ->pluck('current_count', 'student_id')
            ->map(fn ($count): int => (int) $count);
    }

    #[Computed]
    public function trustedChildSettings(): Collection
    {
        if (! Schema::hasTable('student_task_approval_settings')) {
            return collect();
        }

        $studentIds = $this->children->pluck('id')->all();

        if ($studentIds === []) {
            return collect();
        }

        return DB::table('student_task_approval_settings')
            ->whereIn('student_id', $studentIds)
            ->pluck('trusted_auto_approval_enabled', 'student_id')
            ->map(fn ($enabled): bool => (bool) $enabled);
    }

    #[Computed]
    public function availableReasons(): array
    {
        if (blank($this->pendingLifecycleAction)) {
            return [];
        }

        return LifecycleReason::forAction($this->pendingLifecycleAction);
    }

    public function canRevealCredential(?User $user): bool
    {
        return $user !== null && app(CredentialService::class)->hasRecoverableCredential($user);
    }

    public function canViewHistory(): bool
    {
        return $this->userCan('families.history.view');
    }

    public function canViewAccountSecurity(): bool
    {
        return $this->canRevealCredentials()
            || $this->canSendResetLinks()
            || $this->canGeneratePasswords()
            || $this->canResendActivationEmails();
    }

    public function canRevealCredentials(): bool
    {
        return $this->userCan('families.credentials.reveal');
    }

    public function canSendResetLinks(): bool
    {
        return $this->userCan('families.credentials.send_reset_link');
    }

    public function canGeneratePasswords(): bool
    {
        return $this->userCan('families.credentials.generate_password');
    }

    public function canResendActivationEmails(): bool
    {
        return $this->userCan('families.credentials.resend_activation');
    }

    public function familyCanActivate(): bool
    {
        return $this->parent->lifecycle_status === FamilyLifecycleStatus::PendingActivation->value
            && $this->userCan('families.activate');
    }

    public function childCanActivate(Student $child): bool
    {
        return $child->account_status === ChildAccountStatus::PendingActivation->value
            && $this->userCan('families.children.activate');
    }

    public function familyLifecycleActions(): array
    {
        return $this->resolveVisibleLifecycleActions('family', $this->parent->lifecycle_status);
    }

    public function childLifecycleActions(Student $child): array
    {
        return $this->resolveVisibleLifecycleActions('child', $child->account_status);
    }

    public function canViewStudentDomainLinks(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false;
    }

    public function canEditParentProfile(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false;
    }

    public function visibleTabs(): array
    {
        return collect([
            'overview' => ['label' => 'Overview', 'icon' => 'tabler-layout-dashboard'],
            'payments' => ['label' => 'Payments', 'icon' => 'tabler-credit-card'],
            'consultation' => ['label' => 'Consultation', 'icon' => 'tabler-clipboard-list'],
            'communication' => ['label' => 'Communication', 'icon' => 'tabler-message-circle'],
            'security' => ['label' => $this->securityTabLabel(), 'icon' => null],
            'notes' => ['label' => 'Notes', 'icon' => 'tabler-notes'],
        ])
            ->reject(fn (array $_tab, string $key): bool => $key === 'security' && ! $this->showsSecurityOrLogTab())
            ->all();
    }

    public function showsSecurityOrLogTab(): bool
    {
        return $this->canViewAccountSecurity() || $this->canViewHistory();
    }

    public function securityTabLabel(): string
    {
        return match (true) {
            $this->canViewAccountSecurity() && $this->canViewHistory() => 'Security & Log',
            $this->canViewAccountSecurity() => 'Security',
            default => 'Log',
        };
    }

    public function isActiveTab(string $tab): bool
    {
        return $this->activeTab === $tab;
    }

    public function currentClassLabel(Student $child): string
    {
        if (filled($child->currentClass?->title)) {
            return (string) $child->currentClass->title;
        }

        if (filled($child->current_class_id)) {
            return 'Class #'.$child->current_class_id;
        }

        return 'Not assigned';
    }

    public function childAcademicProfileLabel(Student $child): string
    {
        return ($child->school_system ?: '-').' | '.$this->childGradeDisplay($child);
    }

    public function childGradeDisplay(Student $child): string
    {
        $grade = null;

        if ($child->relationLoaded('gradeLevel') && filled($child->gradeLevel?->title)) {
            $grade = $child->gradeLevel->title;
        } elseif (filled($child->grade_name)) {
            $grade = $child->grade_name;
        }

        if (blank($grade)) {
            return '-';
        }

        $grade = trim((string) $grade);

        return str_starts_with(strtolower($grade), 'grade') ? $grade : 'Grade '.$grade;
    }

    public function hasDuplicateCurrentClassHistory(Student $child): bool
    {
        return ((int) ($this->currentClassHistoryCounts->get($child->id, 0))) > 1;
    }

    public function pendingLifecycleActionLabel(): string
    {
        return $this->actionLabel((string) $this->pendingLifecycleAction);
    }

    public function pendingLifecycleTargetLabel(): string
    {
        return $this->pendingLifecycleTargetType === 'child' ? 'child account' : 'family';
    }

    public function pendingLifecycleTargetName(): string
    {
        if ($this->pendingLifecycleTargetType === 'child') {
            $child = $this->children->firstWhere('id', $this->pendingLifecycleTargetId);

            return $child?->display_name ?? 'this child';
        }

        return $this->parent->display_name;
    }

    public function statusLabel(?string $status): string
    {
        return match ($status) {
            FamilyLifecycleStatus::Active->value => 'Active',
            FamilyLifecycleStatus::PendingActivation->value => 'Pending Activation',
            FamilyLifecycleStatus::Suspended->value => 'Suspended',
            FamilyLifecycleStatus::Archived->value => 'Archived',
            default => 'Unclassified',
        };
    }

    public function statusTone(?string $status): string
    {
        return match ($status) {
            FamilyLifecycleStatus::Active->value => 'success',
            FamilyLifecycleStatus::PendingActivation->value => 'warning',
            FamilyLifecycleStatus::Suspended->value => 'danger',
            FamilyLifecycleStatus::Archived->value => 'secondary',
            default => 'dark',
        };
    }

    public function render(): View
    {
        if (! array_key_exists($this->activeTab, $this->visibleTabs())) {
            $this->activeTab = 'overview';
        }

        return view('livewire.admin.families.family-workspace')
            ->layout('components.layouts.app', ['title' => 'Family Workspace']);
    }

    private function actorRole(): string
    {
        return auth()->user()?->getRoleNames()->first() ?? 'unknown';
    }

    private function resolveSubjectUser(int $userId, string $subjectType): array
    {
        if (! in_array($subjectType, ['parent', 'child'], true)) {
            abort(422);
        }

        $user = User::with(['parent_user', 'student'])->findOrFail($userId);

        if ($subjectType === 'parent') {
            if ((int) $user->parent_user?->id !== $this->parentId) {
                abort(404);
            }

            return [$user, $this->parentId];
        }

        if ((int) $user->student?->parent_id !== $this->parentId) {
            abort(404);
        }

        return [$user, (int) $user->student->id];
    }

    private function executeLifecycleAction(string $action, string $targetType, ParentModel|Student $target, string $reason): void
    {
        $service = app(FamilyLifecycleService::class);
        $actorUserId = (int) auth()->id();
        $actorRole = $this->actorRole();

        match ([$targetType, $action]) {
            ['family', 'activate'] => $service->activateFamily($target, $reason, $actorUserId, $actorRole),
            ['family', 'suspend'] => $service->suspendFamily($target, $reason, $actorUserId, $actorRole),
            ['family', 'reactivate'] => $service->reactivateFamily($target, $reason, $actorUserId, $actorRole),
            ['family', 'archive'] => $service->archiveFamily($target, $reason, $actorUserId, $actorRole),
            ['family', 'restore'] => $service->restoreFamily($target, $reason, $actorUserId, $actorRole),
            ['child', 'activate'] => $service->activateChild($target, $reason, $actorUserId, $actorRole),
            ['child', 'suspend'] => $service->suspendChild($target, $reason, $actorUserId, $actorRole),
            ['child', 'reactivate'] => $service->reactivateChild($target, $reason, $actorUserId, $actorRole),
            ['child', 'archive'] => $service->archiveChild($target, $reason, $actorUserId, $actorRole),
            ['child', 'restore'] => $service->restoreChild($target, $reason, $actorUserId, $actorRole),
            default => throw new InvalidArgumentException('Unsupported lifecycle action.'),
        };
    }

    private function resolveLifecycleSelection(string $action, string $targetType, ?int $targetId): array
    {
        $action = $this->normalizeLifecycleAction($action);
        $targetType = $this->normalizeLifecycleTargetType($targetType);

        if ($targetType === 'family') {
            $effectiveTargetId = $targetId ?? $this->parentId;

            if ($effectiveTargetId !== $this->parentId) {
                throw new InvalidArgumentException('This family action does not match the current workspace.');
            }

            return [$action, $targetType, $this->parent];
        }

        if ($targetId === null) {
            throw new InvalidArgumentException('Choose a child before continuing.');
        }

        $child = Student::with('user')
            ->where('parent_id', $this->parentId)
            ->whereKey($targetId)
            ->first();

        if (! $child) {
            throw new InvalidArgumentException('This child does not belong to the current family workspace.');
        }

        return [$action, $targetType, $child];
    }

    private function normalizeLifecycleAction(string $action): string
    {
        $allowed = ['activate', 'suspend', 'reactivate', 'archive', 'restore'];

        if (! in_array($action, $allowed, true)) {
            throw new InvalidArgumentException('Unsupported lifecycle action.');
        }

        return $action;
    }

    private function normalizeLifecycleTargetType(string $targetType): string
    {
        if (! in_array($targetType, ['family', 'child'], true)) {
            throw new InvalidArgumentException('Unsupported lifecycle target.');
        }

        return $targetType;
    }

    private function permissionFor(string $action, string $targetType): string
    {
        return match ([$targetType, $action]) {
            ['family', 'activate'] => 'families.activate',
            ['family', 'suspend'] => 'families.suspend',
            ['family', 'reactivate'] => 'families.reactivate',
            ['family', 'archive'] => 'families.archive',
            ['family', 'restore'] => 'families.restore',
            ['child', 'activate'] => 'families.children.activate',
            ['child', 'suspend'] => 'families.children.suspend',
            ['child', 'reactivate'] => 'families.children.reactivate',
            ['child', 'archive'] => 'families.children.archive',
            ['child', 'restore'] => 'families.children.restore',
            default => throw new InvalidArgumentException('Unsupported lifecycle action.'),
        };
    }

    private function resolveVisibleLifecycleActions(string $targetType, ?string $status): array
    {
        $actions = match ($status) {
            FamilyLifecycleStatus::PendingActivation->value => ['activate', 'archive'],
            FamilyLifecycleStatus::Active->value => ['suspend', 'archive'],
            FamilyLifecycleStatus::Suspended->value => ['reactivate', 'archive'],
            FamilyLifecycleStatus::Archived->value => ['restore'],
            default => [],
        };

        return collect($actions)
            ->filter(fn (string $action): bool => $this->userCan($this->permissionFor($action, $targetType)))
            ->map(fn (string $action): array => [
                'action' => $action,
                'label' => $this->actionLabel($action),
                'button_class' => $this->actionButtonClass($action),
            ])
            ->values()
            ->all();
    }

    private function actionLabel(string $action): string
    {
        return match ($action) {
            'activate' => 'Activate',
            'suspend' => 'Suspend',
            'reactivate' => 'Reactivate',
            'archive' => 'Archive',
            'restore' => 'Restore',
            default => 'Continue',
        };
    }

    private function actionButtonClass(string $action): string
    {
        return match ($action) {
            'activate', 'reactivate' => 'btn btn-sm btn-primary',
            'suspend' => 'btn btn-sm btn-outline-danger',
            'archive' => 'btn btn-sm btn-outline-secondary',
            'restore' => 'btn btn-sm btn-outline-primary',
            default => 'btn btn-sm btn-outline-secondary',
        };
    }

    private function successMessageFor(string $action, string $targetType, string $targetName): string
    {
        return match ([$targetType, $action]) {
            ['family', 'activate'] => 'Family activation queued.',
            ['family', 'suspend'] => "{$targetName} has been suspended.",
            ['family', 'reactivate'] => "{$targetName} has been reactivated.",
            ['family', 'archive'] => "{$targetName} has been archived.",
            ['family', 'restore'] => "{$targetName} has been restored.",
            ['child', 'activate'] => "{$targetName}'s account is active.",
            ['child', 'suspend'] => "{$targetName}'s account has been suspended.",
            ['child', 'reactivate'] => "{$targetName}'s account has been reactivated.",
            ['child', 'archive'] => "{$targetName}'s account has been archived.",
            ['child', 'restore'] => "{$targetName}'s account has been restored.",
            default => 'Lifecycle updated.',
        };
    }

    private function refreshWorkspaceState(): void
    {
        unset(
            $this->parent,
            $this->children,
            $this->accountHistory,
            $this->consultationHistory,
            $this->currentClassHistoryCounts
        );
        $this->resetPage(pageName: 'historyPage');
    }

    private function emptyPaginator(string $pageName): LengthAwarePaginator
    {
        return new Paginator(
            [],
            0,
            20,
            $this->getPage(pageName: $pageName),
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
    }

    private function parentEditRules(ParentModel $parent, ?User $user): array
    {
        return [
            'parentEditForm.first_name' => ['required', 'string', 'max:100'],
            'parentEditForm.last_name' => ['nullable', 'string', 'max:100'],
            'parentEditForm.email' => [
                // Linked parent accounts are expected to keep a real email on both parent and user records.
                $user ? 'required' : 'nullable',
                'email',
                'max:190',
                Rule::unique('parents', 'email')->ignore($parent->id),
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'parentEditForm.phone' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('parents', 'phone')->ignore($parent->id),
                Rule::unique('users', 'phone')->ignore($user?->id),
            ],
        ];
    }

    private function authorizeWorkspaceAbility(string $ability): void
    {
        if (! $this->userCan($ability)) {
            throw new AuthorizationException;
        }
    }

    private function authorizeParentEdit(): void
    {
        if (! $this->canEditParentProfile()) {
            throw new AuthorizationException;
        }
    }

    private function userCan(string $ability): bool
    {
        if (array_key_exists($ability, $this->authorizationAbilityCache)) {
            return $this->authorizationAbilityCache[$ability];
        }

        $user = $this->authorizationUserCache ??= auth()->user()?->fresh();

        if (! $user) {
            return $this->authorizationAbilityCache[$ability] = false;
        }

        if ($ability === 'families.view_workspace' && $user->hasAnyRole(self::FAMILY_VIEW_ROLES)) {
            return $this->authorizationAbilityCache[$ability] = true;
        }

        if ($user->hasRole('super_admin')) {
            return $this->authorizationAbilityCache[$ability] = true;
        }

        try {
            return $this->authorizationAbilityCache[$ability] = $user->hasPermissionTo($ability);
        } catch (PermissionDoesNotExist) {
            return $this->authorizationAbilityCache[$ability] = false;
        }
    }

    private function resetRevealState(): void
    {
        $this->revealedCredential = null;
        $this->revealSubjectType = null;
        $this->revealUserId = null;
        $this->revealMasked = true;
        $this->showRevealModal = false;
    }

    private function resetLifecycleState(): void
    {
        $this->pendingLifecycleAction = null;
        $this->pendingLifecycleTargetType = null;
        $this->pendingLifecycleTargetId = null;
        $this->lifecycleReason = null;
        $this->showLifecycleModal = false;
    }

    private function resetParentEditState(): void
    {
        $this->parentEditForm = [];
        $this->showParentEditModal = false;
    }

    private function passwordResetUrl(string $token, string $email): string
    {
        if (Route::has('password.reset')) {
            return url(route('password.reset', [
                'token' => $token,
                'email' => $email,
            ], false));
        }

        return url('/reset-password/'.$token.'?email='.urlencode($email));
    }

    private function latestActivationHistoryId(): int
    {
        return (int) AccountHistory::where('parent_id', $this->parentId)->max('id');
    }

    private function activationEmailWasSentAfter(int $historyWatermark, string $eventType, string $subjectType, int $subjectId): bool
    {
        return AccountHistory::where('parent_id', $this->parentId)
            ->where('id', '>', $historyWatermark)
            ->where('event_type', $eventType)
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->exists();
    }
}
