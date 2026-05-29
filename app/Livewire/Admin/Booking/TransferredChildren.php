<?php

namespace App\Livewire\Admin\Booking;

use App\Enums\LifecycleReason;
use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\BookingIntakeReview;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use App\Services\FamilyLifecycleService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class TransferredChildren extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public int $perPage = 10;

    public ?string $pendingLifecycleAction = null;

    public ?string $pendingLifecycleTargetType = null;

    public ?int $pendingLifecycleTargetId = null;

    public ?string $lifecycleReason = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        $this->normalizePerPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->normalizePerPage();
        $this->resetPage();
    }

    #[On('intake-created')]
    public function refreshAfterIntake(): void
    {
        // Re-render the transferred list page after the shared intake modal writes or routes a submission.
    }

    public function resetListFilters(): void
    {
        $this->search = '';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function openLifecycleModal(string $action, string $targetType, int $targetId): void
    {
        $this->resetErrorBag(['lifecycleAction', 'lifecycleReason']);

        try {
            $this->authorizeLifecycleManagement();
            [$action, $targetType] = $this->normalizeLifecycleSelection($action, $targetType);
            $this->resolveLifecycleTarget($targetType, $targetId);
        } catch (AuthorizationException|InvalidArgumentException $exception) {
            $this->addError('lifecycleAction', $exception->getMessage());

            return;
        }

        $this->pendingLifecycleAction = $action;
        $this->pendingLifecycleTargetType = $targetType;
        $this->pendingLifecycleTargetId = $targetId;
        $this->lifecycleReason = null;

        $this->dispatch('transferred-children-lifecycle-open');
    }

    public function confirmLifecycleAction(): void
    {
        $this->resetErrorBag(['lifecycleAction', 'lifecycleReason']);

        if (blank($this->lifecycleReason)) {
            $this->addError('lifecycleReason', 'Select a reason to continue.');

            return;
        }

        try {
            $this->authorizeLifecycleManagement();
            [$action, $targetType] = $this->normalizeLifecycleSelection(
                (string) $this->pendingLifecycleAction,
                (string) $this->pendingLifecycleTargetType
            );
            $target = $this->resolveLifecycleTarget($targetType, $this->pendingLifecycleTargetId);

            $this->executeLifecycleAction($action, $targetType, $target, (string) $this->lifecycleReason);
        } catch (AuthorizationException|InvalidArgumentException $exception) {
            $this->addError('lifecycleAction', $exception->getMessage());

            return;
        }

        $targetName = $target->display_name;

        $this->closeLifecycleModal();
        session()->flash('success', $this->successMessageFor($action, $targetType, $targetName));
    }

    public function closeLifecycleModal(): void
    {
        $this->resetLifecycleState();
        $this->dispatch('transferred-children-lifecycle-close');
    }

    #[On('transferred-children-lifecycle-modal-hidden')]
    public function handleLifecycleModalHidden(): void
    {
        $this->resetLifecycleState();
    }

    public function render()
    {
        $bookings = $this->bookingsPage();

        return view('livewire.admin.booking.transferred-children', [
            'bookings' => $bookings,
            'pendingIntakeReviewCount' => $this->pendingIntakeReviewCount(),
            'supportUsers' => $this->supportUsers(),
        ])->layout('components.layouts.app', ['title' => 'Transferred Children']);
    }

    protected function pendingIntakeReviewCount(): int
    {
        if (! Schema::hasTable('booking_intake_review')) {
            return 0;
        }

        return BookingIntakeReview::query()
            ->where('status', 'pending_review')
            ->count();
    }

    public function consultationTypeLabel(?string $value): string
    {
        return match ($value) {
            'online' => 'Online',
            'in-person' => 'In-Person',
            default => 'Undecided',
        };
    }

    public function formatDate(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('d M Y');
        }

        try {
            return Carbon::parse((string) $value)->format('d M Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function formatTime(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        $normalized = trim((string) $value);

        if (preg_match('/^\d{1,2}\.\d{2}$/', $normalized)) {
            $normalized = str_replace('.', ':', $normalized);
        }

        try {
            return Carbon::parse($normalized)->format('g:i A');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function formatDateTime(mixed $value): string
    {
        if (blank($value)) {
            return '-';
        }

        if ($value instanceof CarbonInterface) {
            return $value->format('d M Y g:i A');
        }

        try {
            return Carbon::parse((string) $value)->format('d M Y g:i A');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function formatSchedule(BookingChild $child): string
    {
        $formattedDate = $this->formatDate($child->scheduled_date ?: $child->booking?->consultation_date);
        $formattedTime = $this->formatTime($child->scheduled_time ?: $child->booking?->consultation_time);

        if ($formattedDate === '-' && $formattedTime === '-') {
            return '-';
        }

        if ($formattedDate === '-') {
            return $formattedTime;
        }

        if ($formattedTime === '-') {
            return $formattedDate;
        }

        return $formattedDate.' at '.$formattedTime;
    }

    public function gradeLabel(BookingChild $child): string
    {
        if (filled($child->student?->gradeLevel?->title)) {
            return $child->student->gradeLevel->title;
        }

        if (blank($child->child_grade)) {
            return '-';
        }

        return (string) $child->child_grade;
    }

    public function studentWorkspaceLabel(BookingChild $child): string
    {
        if (filled($child->student?->user?->name)) {
            return $child->student->user->name;
        }

        if (filled($child->student?->user_name)) {
            return (string) $child->student->user_name;
        }

        if (filled($child->student?->student_email)) {
            return (string) $child->student->student_email;
        }

        return 'Workspace link unavailable';
    }

    public function parentAccountTargetId(Booking $booking): ?int
    {
        return $booking->primaryTransferredChild?->student_id;
    }

    public function familyWorkspaceTargetId(Booking $booking): ?int
    {
        return $booking->displayTransferredChildren
            ->pluck('student.parent_id')
            ->filter()
            ->first();
    }

    public function familyWorkspaceUrl(Booking $booking): ?string
    {
        $parentId = $this->familyWorkspaceTargetId($booking);

        return $parentId ? route('admin.families.show', $parentId) : null;
    }

    public function familySupportId(Booking $booking): ?int
    {
        return $booking->displayTransferredChildren
            ->pluck('student.parent.family_support_id')
            ->filter()
            ->first();
    }

    public function familySupportName(Booking $booking): string
    {
        $supportName = $booking->displayTransferredChildren
            ->pluck('student.parent.familySupport.name')
            ->filter()
            ->first();

        return filled($supportName) ? (string) $supportName : 'Unassigned';
    }

    public function assignFamilySupport(int $parentId, mixed $supportUserId): void
    {
        $this->resetErrorBag(['familySupport']);

        try {
            $this->authorizeFamilySupportAssignment();
        } catch (AuthorizationException $exception) {
            $this->addError('familySupport', $exception->getMessage());

            return;
        }

        $supportUserId = blank($supportUserId) ? null : (int) $supportUserId;
        $supportUser = null;

        if ($supportUserId !== null) {
            $supportUser = User::query()
                ->whereKey($supportUserId)
                ->where('status', 'active')
                ->first();

            if (! $supportUser?->hasRole('customer_support')) {
                $this->addError('familySupport', 'Choose an active customer support user.');

                return;
            }
        }

        if (! $this->parentHasTransferredChild($parentId)) {
            $this->addError('familySupport', 'Choose a transferred family from this page.');

            return;
        }

        $parent = ParentModel::findOrFail($parentId);
        $parent->forceFill(['family_support_id' => $supportUserId])->save();

        session()->flash(
            'success',
            $supportUser
                ? "Family support owner assigned to {$supportUser->name}."
                : 'Family support owner cleared.'
        );
    }

    protected function parentHasTransferredChild(int $parentId): bool
    {
        return BookingChild::query()
            ->where('transfer_status', 'transferred')
            ->whereHas('student', fn (Builder $query) => $query->where('parent_id', $parentId))
            ->exists();
    }

    public function parentStatusMeta(Booking $booking): array
    {
        return match ($this->normalizedParentStatus($booking)) {
            'active' => ['label' => 'Active', 'tone' => 'success'],
            'suspended' => ['label' => 'Suspended', 'tone' => 'danger'],
            'archived' => ['label' => 'Archived', 'tone' => 'secondary'],
            'inactive' => ['label' => 'Inactive', 'tone' => 'secondary'],
            default => ['label' => 'Pending', 'tone' => 'warning'],
        };
    }

    public function paymentSummary(Booking $booking): array
    {
        return [
            'label' => 'Billing not wired yet',
            'textClass' => 'text-body-secondary',
        ];
    }

    public function nextActionMeta(Booking $booking): array
    {
        return [
            'label' => 'Not wired yet',
            'tone' => 'secondary',
        ];
    }

    public function accountMenuActions(Booking $booking): array
    {
        return $this->resolveVisibleLifecycleActions('family', $this->normalizedParentStatus($booking));
    }

    public function childStatusMeta(BookingChild $child): array
    {
        return match ($this->normalizedChildStatus($child)) {
            'active' => ['label' => 'Active', 'tone' => 'success'],
            'suspended' => ['label' => 'Suspended', 'tone' => 'danger'],
            'archived' => ['label' => 'Archived', 'tone' => 'secondary'],
            'inactive' => ['label' => 'Inactive', 'tone' => 'secondary'],
            default => ['label' => 'Pending', 'tone' => 'warning'],
        };
    }

    public function childMenuActions(BookingChild $child): array
    {
        return $this->resolveVisibleLifecycleActions('child', $this->normalizedChildStatus($child));
    }

    public function parentDisplayName(Booking $booking): string
    {
        $linkedParent = $booking->displayTransferredChildren
            ->pluck('student.parent.display_name')
            ->filter()
            ->first();

        if (filled($linkedParent)) {
            return (string) $linkedParent;
        }

        if (filled($booking->parent_name)) {
            return (string) $booking->parent_name;
        }

        if (filled($booking->parent_id)) {
            return 'Parent #'.$booking->parent_id;
        }

        return 'Not linked yet';
    }

    public function parentContactEmail(Booking $booking): string
    {
        $linkedEmail = $booking->displayTransferredChildren
            ->pluck('student.parent.email')
            ->filter()
            ->first();

        return $linkedEmail ?: ($booking->parent_email ?: '-');
    }

    public function parentContactPhone(Booking $booking): string
    {
        $linkedPhone = $booking->displayTransferredChildren
            ->pluck('student.parent.phone')
            ->filter()
            ->first();

        return $linkedPhone ?: ($booking->parent_phone ?: '-');
    }

    public function serviceSummary(BookingChild $child): string
    {
        $services = collect($child->displayServiceInterests())
            ->filter()
            ->values();

        if ($services->isEmpty() && filled($child->booking?->service_interest)) {
            $services = collect(explode(',', (string) $child->booking->service_interest))
                ->map(fn ($service) => trim($service))
                ->filter()
                ->map(fn ($service) => Booking::displayServiceInterest($service))
                ->values();
        }

        return $services->implode(', ') ?: 'Need Guidance';
    }

    public function childGradeDisplay(BookingChild $child): string
    {
        $grade = $this->gradeLabel($child);

        if ($grade === '-') {
            return '-';
        }

        return str_starts_with(strtolower($grade), 'grade') ? $grade : 'Grade '.$grade;
    }

    public function childAccountAccess(BookingChild $child): array
    {
        $parentId = $child->student?->parent_id;

        return [
            'username' => $child->student?->user_name ?: '-',
            'workspace_url' => $parentId ? route('admin.families.show', $parentId) : null,
        ];
    }

    public function canViewStudentDomainLinks(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false;
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
        if ($this->pendingLifecycleTargetType === 'family') {
            $target = $this->pendingLifecycleTargetId
                ? ParentModel::find($this->pendingLifecycleTargetId)
                : null;

            return $target?->display_name ?? 'this family';
        }

        $target = $this->pendingLifecycleTargetId
            ? Student::find($this->pendingLifecycleTargetId)
            : null;

        return $target?->display_name ?? 'this child';
    }

    public function availableLifecycleReasons(): array
    {
        if (blank($this->pendingLifecycleAction)) {
            return [];
        }

        return LifecycleReason::forAction((string) $this->pendingLifecycleAction);
    }

    public function initials(?string $value, string $fallback = '?'): string
    {
        $words = collect(preg_split('/\s+/', trim((string) $value)))
            ->filter()
            ->values();

        $initials = strtoupper(
            ($words->isNotEmpty() ? mb_substr((string) $words->first(), 0, 1) : $fallback).
            ($words->count() > 1 ? mb_substr((string) $words->last(), 0, 1) : '')
        );

        return $initials !== '' ? $initials : $fallback;
    }

    public function childAvatarUrl(BookingChild $child): ?string
    {
        $student = $child->student;

        return $this->assetPath($student?->avatar_path);
    }

    public function currentReturnUrl(?int $page = null): string
    {
        return route('admin.bookings.transferred', array_filter([
            'search' => $this->search !== '' ? $this->search : null,
            'perPage' => $this->perPage !== 10 ? $this->perPage : null,
            'page' => ($page ?? $this->getPage()) > 1 ? ($page ?? $this->getPage()) : null,
        ], fn ($value) => $value !== null && $value !== ''));
    }

    protected function bookingsPage(): LengthAwarePaginator
    {
        $familyPage = $this->familyGroupsPage();
        $parentIds = $familyPage->getCollection()
            ->pluck('parent_id')
            ->filter()
            ->map(fn ($parentId) => (int) $parentId)
            ->values()
            ->all();

        if ($parentIds === []) {
            $familyPage->setCollection(collect());

            return $familyPage;
        }

        $children = $this->baseQuery()
            ->whereHas('student', fn (Builder $studentQuery) => $studentQuery->whereIn('parent_id', $parentIds))
            ->get()
            ->sortBy([
                ['student.parent_id', 'asc'],
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        $parentOrder = array_flip(array_map('strval', $parentIds));
        $familyGroups = $this->mapFamilyGroups($children)
            ->sortBy(fn (Booking $booking) => $parentOrder[(string) $booking->primaryTransferredChild->student->parent_id] ?? PHP_INT_MAX)
            ->values();

        $familyPage->setCollection($familyGroups);

        return $familyPage;
    }

    protected function baseQuery(): Builder
    {
        $query = BookingChild::query()
            ->where('transfer_status', 'transferred')
            ->whereHas('student.parent')
            ->with([
                'booking',
                'student' => function ($studentQuery) {
                    $studentQuery->with([
                        'parent.familySupport',
                        'parent.user',
                        'user',
                        'gradeLevel',
                        'program',
                        'services_type',
                    ]);
                },
            ]);

        $this->applySearchFilter($query);

        return $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id');
    }

    protected function familyGroupsPage(): LengthAwarePaginator
    {
        return $this->familyGroupsQuery()->paginate(
            $this->perPage,
            ['students.parent_id'],
            'page',
            $this->getPage()
        );
    }

    protected function familyGroupsQuery(): Builder
    {
        $query = BookingChild::query()
            ->join('students', 'students.id', '=', 'booking_children.student_id')
            ->selectRaw('students.parent_id as parent_id')
            ->selectRaw('MAX(booking_children.updated_at) as latest_transferred_at')
            ->selectRaw('MAX(booking_children.id) as latest_transferred_child_id')
            ->where('booking_children.transfer_status', 'transferred')
            ->whereNotNull('students.parent_id')
            ->whereHas('student.parent');

        $this->applySearchFilter($query);

        return $query
            ->groupBy('students.parent_id')
            ->orderByDesc(DB::raw('MAX(booking_children.updated_at)'))
            ->orderByDesc(DB::raw('MAX(booking_children.id)'));
    }

    protected function mapFamilyGroups(Collection $children): Collection
    {
        return $children
            ->groupBy(fn (BookingChild $child) => (string) $child->student->parent_id)
            ->map(function (Collection $children): Booking {
                $sortedChildren = $children
                    ->sortBy([
                        ['sort_order', 'asc'],
                        ['id', 'asc'],
                    ])
                    ->values();
                $representativeChild = $sortedChildren
                    ->sortByDesc(fn (BookingChild $child) => optional($child->updated_at)->getTimestamp() ?? 0)
                    ->first() ?? $sortedChildren->first();
                $booking = $representativeChild->booking;

                $booking->displayTransferredChildren = $sortedChildren;
                $booking->displayTransferredChildCount = $sortedChildren->count();
                $booking->primaryTransferredChild = $sortedChildren->first();
                $booking->latest_transferred_at = $sortedChildren
                    ->max(fn (BookingChild $child) => optional($child->updated_at)->getTimestamp() ?? 0);

                return $booking;
            })
            ->values();
    }

    protected function applySearchFilter(Builder $query): void
    {
        $search = trim($this->search);

        if ($search === '') {
            return;
        }

        $query->where(function (Builder $searchQuery) use ($search) {
            $searchQuery->where('booking_children.child_name', 'like', "%{$search}%")
                ->orWhere('booking_children.current_school', 'like', "%{$search}%")
                ->orWhereHas('booking', function (Builder $bookingQuery) use ($search) {
                    $bookingQuery->where('parent_name', 'like', "%{$search}%")
                        ->orWhere('parent_email', 'like', "%{$search}%")
                        ->orWhere('parent_phone', 'like', "%{$search}%")
                        ->orWhere('booking_reference', 'like', "%{$search}%");
                })
                ->orWhereHas('student', function (Builder $studentQuery) use ($search) {
                    $studentQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('student_email', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%");
                })
                ->orWhereHas('student.parent', function (Builder $parentQuery) use ($search) {
                    $parentQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%");
                });
        });
    }

    protected function normalizePerPage(): void
    {
        if (! in_array($this->perPage, [10, 25, 50], true)) {
            $this->perPage = 10;
        }
    }

    protected function normalizedParentStatus(Booking $booking): string
    {
        $parent = $booking->displayTransferredChildren
            ->pluck('student.parent')
            ->filter()
            ->first();
        $lifecycleStatus = strtolower((string) ($parent?->lifecycle_status ?? ''));

        if (in_array($lifecycleStatus, ['active', 'pending_activation', 'suspended', 'archived'], true)) {
            return $lifecycleStatus === 'pending_activation' ? 'pending' : $lifecycleStatus;
        }

        if ($parent) {
            return 'pending';
        }

        $userStatus = strtolower((string) ($parent?->user?->status ?? ''));

        if (in_array($userStatus, ['active', 'inactive', 'pending'], true)) {
            return $userStatus;
        }

        if ($parent && $parent->active === false) {
            return 'inactive';
        }

        return 'pending';
    }

    protected function normalizedChildStatus(BookingChild $child): string
    {
        $accountStatus = strtolower((string) ($child->student?->account_status ?? ''));

        if (in_array($accountStatus, ['active', 'pending_activation', 'suspended', 'archived'], true)) {
            return $accountStatus === 'pending_activation' ? 'pending' : $accountStatus;
        }

        if ($child->student) {
            return 'pending';
        }

        $userStatus = strtolower((string) ($child->student?->user?->status ?? ''));

        if (in_array($userStatus, ['active', 'inactive', 'pending'], true)) {
            return $userStatus;
        }

        $studentStatus = strtolower((string) ($child->student?->status ?? ''));

        if (in_array($studentStatus, ['active', 'inactive', 'pending'], true)) {
            return $studentStatus;
        }

        return 'pending';
    }

    protected function assetPath(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $value = trim((string) $path);

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
            return $value;
        }

        return asset($value);
    }

    protected function resolveVisibleLifecycleActions(string $targetType, string $normalizedStatus): array
    {
        if (! $this->canViewStudentDomainLinks()) {
            return [];
        }

        $actions = match ($normalizedStatus) {
            'pending' => ['activate', 'archive'],
            'active' => ['suspend', 'archive'],
            'suspended' => ['reactivate', 'archive'],
            'archived' => ['restore'],
            default => [],
        };

        return collect($actions)
            ->map(fn (string $action): array => [
                'action' => $action,
                'label' => $this->actionLabel($action),
                'target_type' => $targetType,
            ])
            ->values()
            ->all();
    }

    protected function actionLabel(string $action): string
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

    protected function successMessageFor(string $action, string $targetType, string $targetName): string
    {
        return match ([$targetType, $action]) {
            ['family', 'activate'] => "{$targetName} is now active.",
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

    protected function resetLifecycleState(): void
    {
        $this->pendingLifecycleAction = null;
        $this->pendingLifecycleTargetType = null;
        $this->pendingLifecycleTargetId = null;
        $this->lifecycleReason = null;
    }

    protected function authorizeLifecycleManagement(): void
    {
        if (! $this->canViewStudentDomainLinks()) {
            throw new AuthorizationException('You do not have permission to manage lifecycle actions from this page.');
        }
    }

    protected function authorizeFamilySupportAssignment(): void
    {
        if (! $this->canViewStudentDomainLinks()) {
            throw new AuthorizationException('Only admin or superadmin can assign family support ownership.');
        }
    }

    protected function supportUsers(): Collection
    {
        $supportRole = Role::query()
            ->where('name', 'customer_support')
            ->where('guard_name', 'web')
            ->first();

        if (! $supportRole) {
            return collect();
        }

        return User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereKey($supportRole->id))
            ->where('status', 'active')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'email']);
    }

    protected function normalizeLifecycleSelection(string $action, string $targetType): array
    {
        $action = trim($action);
        $targetType = trim($targetType);
        $allowedActions = ['activate', 'suspend', 'reactivate', 'archive', 'restore'];

        if (! in_array($action, $allowedActions, true)) {
            throw new InvalidArgumentException('Unsupported lifecycle action.');
        }

        if (! in_array($targetType, ['family', 'child'], true)) {
            throw new InvalidArgumentException('Unsupported lifecycle target.');
        }

        return [$action, $targetType];
    }

    protected function resolveLifecycleTarget(string $targetType, ?int $targetId): ParentModel|Student
    {
        if (! $targetId) {
            throw new InvalidArgumentException('Choose a valid account before continuing.');
        }

        if ($targetType === 'family') {
            return ParentModel::with('user')->findOrFail($targetId);
        }

        return Student::with(['parent.user', 'user'])->findOrFail($targetId);
    }

    protected function executeLifecycleAction(string $action, string $targetType, ParentModel|Student $target, string $reason): void
    {
        $service = app(FamilyLifecycleService::class);
        $actorUserId = (int) auth()->id();
        $actorRole = auth()->user()?->getRoleNames()->first() ?? 'unknown';

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
}
