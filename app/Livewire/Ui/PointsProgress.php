<?php

namespace App\Livewire\Ui;

use App\Models\AcademicYear;
use App\Models\RewardPinHash;
use App\Models\Student;
use App\Models\StudentGift;
use App\Models\StudentGiftPointsHistory;
use App\Services\RewardProgressionService;
use App\Support\LifecycleGate;
use App\Support\RewardGiftVisibility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;
use Livewire\Component;

class PointsProgress extends Component
{
    public bool $allowReachedClick = true;

    public bool $circleView = true;

    public bool $barView = true;

    public int $studentId;

    public ?int $pendingGiftId = null;

    public ?int $teacherSubjectId = null;

    public int $current = 0;

    public int $total = 100;

    public ?string $icon = null;

    public string $label = 'Points';

    public string $status = StudentGift::STATUS_WAITING;

    public bool $reached = false;

    public string $pin = '';

    public bool $pinOk = false;

    public string $modalId;

    public int $floorPoints = 0;

    public ?int $lastCompletedGiftId = null;

    public ?string $lastCompletedStatus = null;

    public float $pctNormalized = 0.0;

    public ?int $redeemGiftId = null;

    public int $reachedCount = 0;

    public ?int $lastReachedGiftId = null;

    public ?string $lastReachedGiftImage = null;

    public int $redeemedCount = 0;

    public ?int $lastRedeemedGiftId = null;

    public ?string $lastRedeemedGiftImage = null;

    public ?string $pointsProgressStateSignature = null;

    public bool $showRewardDetails = false;

    public bool $showRewardDetailsToggle = false;

    public function mount(int $studentId, bool $allowReachedClick = true, bool $circleView = true, bool $barView = true,
        ?int $lastReachedGiftId = null, ?int $pendingGiftId = null, ?int $teacherSubjectId = null, string $label = 'Points',
        bool $showRewardDetailsToggle = false): void
    {
        $this->authorizeStudentAccess($studentId);
        $this->studentId = $studentId;
        $this->teacherSubjectId = $teacherSubjectId;
        $this->pendingGiftId = $pendingGiftId;
        $this->label = $label;
        $this->showRewardDetailsToggle = $showRewardDetailsToggle;
        $this->modalId = 'pinModal';
        $this->allowReachedClick = $allowReachedClick;
        $this->circleView = $circleView;
        $this->barView = $barView;
        $this->lastReachedGiftId = $lastReachedGiftId;

        $this->refreshProgress();
        $this->pointsProgressStateSignature = $this->pointsProgressStateSignature();
    }

    protected function authorizeStudentAccess(int $studentId): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasAnyRole(['admin', 'super_admin', 'teacher'])) {
            return;
        }

        if ($user->hasRole('student')) {
            $authorizedStudentId = Student::where('user_id', $user->id)->value('id');
            abort_unless((int) $authorizedStudentId === $studentId, 403);
        }

        if ($user->hasRole('parent')) {
            $parentModel = $user->parent_user;
            abort_unless(
                $parentModel && $parentModel->students()->where('students.id', $studentId)->exists(),
                403
            );
        }

        abort_if(LifecycleGate::inspect($studentId)->denied(), 403, LifecycleGate::NEUTRAL_MESSAGE);
    }

    public function openRedeemModal(int $giftId): void
    {
        if (! $this->allowReachedClick) {
            return;
        }
        $this->redeemGiftId = $giftId;

        $this->pin = '';
        $this->resetValidation('pin');
        $this->dispatch('pin-modal:open');
    }

    public function redeem(): void
    {
        $this->pin = trim($this->pin);

        $this->validate([
            'pin' => ['required', 'string', 'size:4'],
        ]);

        if (! $this->redeemGiftId) {
            return;
        }

        $hashedPin = optional(RewardPinHash::where('user_id', $this->rewardPinOwnerUserId())->first())->pin_hash;

        if (! $hashedPin || ! Hash::check($this->pin, $hashedPin)) {
            $this->addError('pin', 'Invalid PIN');

            return;
        }

        if (! $this->passesLearnerLifecycleRecheck()) {
            return;
        }

        if (! app(RewardProgressionService::class)->redeemGift($this->studentId, $this->redeemGiftId)) {
            $this->addError('pin', 'This gift is not ready to claim yet.');

            return;
        }

        $this->refreshProgress();

        $this->dispatch('pin-modal:close');
        $this->dispatch('redeem:success');
    }

    public function render()
    {
        return view('livewire.ui.points-progress', [
            'canToggleRewardDetails' => $this->canToggleRewardDetails(),
        ]);
    }

    #[On('task-completed')]
    #[On('redeem:success')]
    #[On('reward-points:updated')]
    public function refreshProgress(): void
    {
        $current = $this->readCurrentPoints();
        $academicYearId = AcademicYear::currentId();
        $this->floorPoints = (int) (StudentGift::maxCompletedPoints($this->studentId, $academicYearId) ?? 0);

        $this->reachedCount = (int) StudentGift::query()
            ->where('student_id', $this->studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', StudentGift::STATUS_REACHED)
            ->count();

        $lastReached = StudentGift::query()
            ->where('student_id', $this->studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', StudentGift::STATUS_REACHED)
            ->orderBy('points_required', 'desc')
            ->first();

        $this->lastReachedGiftId = $lastReached?->id;
        $this->lastReachedGiftImage = $this->visibleGiftImagePath($lastReached);
        $this->lastCompletedGiftId = $lastReached?->id;
        $this->lastCompletedStatus = $lastReached?->status;

        $this->redeemedCount = (int) StudentGift::query()
            ->where('student_id', $this->studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', StudentGift::STATUS_REDEEMED)
            ->count();

        $lastRedeemed = StudentGift::query()
            ->where('student_id', $this->studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', StudentGift::STATUS_REDEEMED)
            ->orderBy('points_required', 'desc')
            ->first();

        $this->lastRedeemedGiftId = $lastRedeemed?->id;
        $this->lastRedeemedGiftImage = $this->visibleGiftImagePath($lastRedeemed);

        $lastCompleted = StudentGift::query()
            ->where('student_id', $this->studentId)
            ->where('academic_year_id', $academicYearId)
            ->whereIn('status', [StudentGift::STATUS_REACHED, StudentGift::STATUS_REDEEMED])
            ->orderBy('points_required', 'desc')
            ->first();

        $this->lastCompletedGiftId = $lastCompleted?->id;
        $this->lastCompletedStatus = $lastCompleted?->status;

        $pending = StudentGift::query()
            ->where('student_id', $this->studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', StudentGift::STATUS_PENDING)
            ->orderBy('points_required', 'asc')
            ->first();

        if ($pending) {
            $target = (int) $pending->points_required;

            $this->pctNormalized = $this->visibleProgressPercent($current, $target);
            $this->pendingGiftId = $pending->id;
            $this->total = $target;
            $this->status = $pending->status;
            $this->current = $current;
            $this->icon = $this->visibleGiftImagePath($pending) ?? $this->icon;
        } else {
            $this->pctNormalized = 0.0;
            $this->pendingGiftId = null;
            $this->total = max(50, $current + 50);
            $this->status = StudentGift::STATUS_WAITING ?? 'waiting';
            $this->current = $current;
        }
    }

    /**
     * Return the visual fill for the displayed current/target pair.
     *
     * Milestone floors still protect reached gifts from being lost, but the bar
     * should match the visible "current / target" values after deductions.
     */
    private function visibleProgressPercent(int $current, int $target): float
    {
        if ($target <= 0) {
            return 0.0;
        }

        return max(0.0, min(100.0, ($current / $target) * 100.0));
    }

    public function refreshProgressIfChanged(): void
    {
        $signature = $this->pointsProgressStateSignature();

        if ($signature === $this->pointsProgressStateSignature) {
            $this->skipRender();

            return;
        }

        $this->refreshProgress();
        $this->pointsProgressStateSignature = $signature;
    }

    public function updatedShowRewardDetails(bool $showRewardDetails): void
    {
        $this->showRewardDetails = $this->canToggleRewardDetails() && $showRewardDetails;
        $this->refreshProgress();
    }

    #[On('reward-details-visibility-changed')]
    public function syncRewardDetailsVisibility(int $studentId, bool $showRewardDetails): void
    {
        if ((int) $studentId !== (int) $this->studentId) {
            return;
        }

        $this->showRewardDetails = $this->canToggleRewardDetails() && $showRewardDetails;
        $this->refreshProgress();
    }

    protected function passesLearnerLifecycleRecheck(): bool
    {
        $user = Auth::user();

        if (! $user || $user->hasAnyRole(['admin', 'super_admin', 'teacher'])) {
            return true;
        }

        if (! $user->hasAnyRole(['student', 'parent'])) {
            return true;
        }

        if (! LifecycleGate::inspect($this->studentId)->denied()) {
            return true;
        }

        $this->addError('pin', LifecycleGate::NEUTRAL_MESSAGE);

        return false;
    }

    protected function readCurrentPoints(): int
    {
        return app(RewardProgressionService::class)->currentPoints($this->studentId);
    }

    protected function rewardPinOwnerUserId(): int
    {
        return Auth::user()?->hasRole('parent')
            ? (int) Student::query()->whereKey($this->studentId)->value('user_id')
            : (int) Auth::id();
    }

    protected function visibleGiftImagePath(?StudentGift $gift): ?string
    {
        if (! $gift) {
            return null;
        }

        if (! $this->shouldShowRewardDetails()) {
            return 'gifts/default_gift.png';
        }

        return $gift->gift_image;
    }

    protected function canToggleRewardDetails(): bool
    {
        return $this->showRewardDetailsToggle && RewardGiftVisibility::canViewDetails(Auth::user());
    }

    protected function shouldShowRewardDetails(): bool
    {
        return $this->showRewardDetails && $this->canToggleRewardDetails();
    }

    protected function pointsProgressStateSignature(): string
    {
        $academicYearId = AcademicYear::currentId();
        $history = StudentGiftPointsHistory::query()
            ->where('student_id', $this->studentId)
            ->where('academic_year_id', $academicYearId)
            ->first(['points', 'sign', 'date']);

        $gifts = StudentGift::query()
            ->where('student_id', $this->studentId)
            ->where('academic_year_id', $academicYearId)
            ->selectRaw('COUNT(*) as rows_count')
            ->selectRaw('COALESCE(MAX(id), 0) as max_id')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'pending' THEN points_required ELSE 0 END), 0) as pending_points")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'reached' THEN points_required ELSE 0 END), 0) as reached_points")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'redeemed' THEN points_required ELSE 0 END), 0) as redeemed_points")
            ->first();

        return implode('|', [
            (int) ($history?->points ?? 0),
            (string) ($history?->sign ?? ''),
            (string) ($history?->date ?? ''),
            (int) ($gifts->rows_count ?? 0),
            (int) ($gifts->max_id ?? 0),
            (int) ($gifts->pending_points ?? 0),
            (int) ($gifts->reached_points ?? 0),
            (int) ($gifts->redeemed_points ?? 0),
        ]);
    }
}
