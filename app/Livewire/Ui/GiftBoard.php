<?php

namespace App\Livewire\Ui;

use App\Models\AcademicYear;
use App\Models\RewardPinHash;
use App\Models\Student;
use App\Models\StudentGift;
use App\Services\RewardProgressionService;
use App\Support\LifecycleGate;
use App\Support\RewardGiftVisibility;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class GiftBoard extends Component
{
    public Student $student;

    /** @var \Illuminate\Support\Collection<int, StudentGift> */
    protected Collection $gifts;

    public ?int $selectedId = null;

    public string $pin = '';

    public ?int $redeemGiftId = null;

    public int $currentPoints = 0;

    public int $targetPoints = 100;

    public float $progressPercent = 0.0;

    public bool $showRewardDetails = false;

    public function mount(Student $student): void
    {
        $this->authorizeStudentAccess($student);
        $this->student = $student;
        $this->loadGifts();
    }

    protected function authorizeStudentAccess(Student $student): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasAnyRole(['admin', 'super_admin', 'teacher'])) {
            return;
        }

        if ($user->hasRole('student')) {
            abort_unless((int) $student->user_id === (int) $user->id, 403);
        }

        if ($user->hasRole('parent')) {
            $parentModel = $user->parent_user;
            abort_unless(
                $parentModel && $parentModel->students()->where('students.id', $student->id)->exists(),
                403
            );
        }

        abort_if(LifecycleGate::inspect($student->id)->denied(), 403, LifecycleGate::NEUTRAL_MESSAGE);
    }

    #[On('redeem:success')]
    #[On('reward-points:updated')]
    public function loadGifts(): void
    {
        $academicYearId = AcademicYear::currentId();
        $this->currentPoints = app(RewardProgressionService::class)->currentPoints($this->student->id);

        $this->gifts = StudentGift::query()
            ->where('student_id', $this->student->id)
            ->where('academic_year_id', $academicYearId)
            ->orderByRaw('(points_required IS NULL), points_required ASC')
            ->orderBy('id')
            ->get();

        $pending = $this->gifts()->firstWhere('status', StudentGift::STATUS_PENDING);
        $this->targetPoints = max(1, (int) ($pending?->points_required ?? max(100, $this->currentPoints + 100)));
        $this->progressPercent = min(100.0, max(0.0, ($this->currentPoints / $this->targetPoints) * 100.0));

        if ($this->selectedId && ! $this->gifts()->contains('id', $this->selectedId)) {
            $this->selectedId = null;
        }

        if (! $this->selectedId && $this->gifts()->isNotEmpty()) {
            $this->selectedId = $this->defaultSelectedGift()?->id;
        }
    }

    public function select(int $id): void
    {
        $gift = $this->findCurrentYearGift($id);
        $this->selectedId = $gift->id;
        $this->redeemGiftId = $gift->id;
        $this->pin = '';
        $this->resetValidation('pin');

        $this->dispatch('gift-detail-modal:open');
    }

    public function openRedeemModal(int $giftId): void
    {
        $gift = $this->findCurrentYearGift($giftId);
        $this->selectedId = $gift->id;

        if ($gift->status !== StudentGift::STATUS_REACHED) {
            $this->dispatch('toast', ['type' => 'warning', 'message' => 'This gift is not ready to claim yet.']);

            return;
        }

        $this->redeemGiftId = $giftId;
        $this->pin = '';
        $this->resetValidation('pin');
        $this->dispatch('gift-pin-modal:open', [
            'giftId' => $giftId,
            'studentId' => $this->student->id,
            'pointsRequired' => $gift->points_required,
        ]);
    }

    #[On('reward:claim-approved')]
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

        if (app(RewardProgressionService::class)->redeemGift($this->student->id, $this->redeemGiftId)) {
            $this->loadGifts();
            $this->dispatch('gift-pin-modal:close');
            $this->dispatch('redeem:success');
            $this->dispatch('reward-points:updated');
        }
    }

    protected function rewardPinOwnerUserId(): int
    {
        return Auth::user()?->hasRole('parent')
            ? (int) $this->student->user_id
            : (int) Auth::id();
    }

    public function getSelectedProperty(): ?StudentGift
    {
        return $this->gifts()->firstWhere('id', $this->selectedId);
    }

    public function render(): View
    {
        $cards = $this->giftCards();

        return view('livewire.ui.gift-board', [
            'cards' => $cards,
            'selectedCard' => $cards->firstWhere('id', $this->selectedId),
            'statusCounts' => $this->statusCounts(),
            'canClaimRewards' => $this->canClaimRewards(),
            'canToggleRewardDetails' => $this->canToggleRewardDetails(),
            'hasLegacyGifts' => $this->hasLegacyGiftsOutsideCurrentYear(),
        ]);
    }

    public function updatedShowRewardDetails(bool $showRewardDetails): void
    {
        $this->showRewardDetails = $this->canToggleRewardDetails() && $showRewardDetails;
        $this->dispatch(
            'reward-details-visibility-changed',
            studentId: $this->student->id,
            showRewardDetails: $this->showRewardDetails
        );
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

        if (! LifecycleGate::inspect($this->student->id)->denied()) {
            return true;
        }

        $this->addError('pin', LifecycleGate::NEUTRAL_MESSAGE);

        return false;
    }

    protected function defaultSelectedGift(): ?StudentGift
    {
        return $this->gifts()->firstWhere('status', StudentGift::STATUS_REACHED)
            ?? $this->gifts()->firstWhere('status', StudentGift::STATUS_PENDING)
            ?? $this->gifts()->firstWhere('status', StudentGift::STATUS_WAITING)
            ?? $this->gifts()->sortByDesc('points_required')->first();
    }

    protected function findCurrentYearGift(int $id): StudentGift
    {
        return $this->gifts()->firstWhere('id', $id)
            ?? StudentGift::query()
                ->where('student_id', $this->student->id)
                ->where('academic_year_id', AcademicYear::currentId())
                ->whereKey($id)
                ->firstOrFail();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    protected function giftCards(): Collection
    {
        return $this->gifts()
            ->values()
            ->map(fn (StudentGift $gift, int $index): array => $this->giftCard($gift, $index))
            ->sortBy(fn (array $card): string => sprintf(
                '%02d-%010d-%010d',
                $this->displaySortWeight((string) $card['status']),
                (int) ($card['points'] ?: PHP_INT_MAX),
                (int) $card['id']
            ))
            ->values();
    }

    protected function displaySortWeight(?string $status): int
    {
        return match ($status) {
            StudentGift::STATUS_REACHED => 0,
            StudentGift::STATUS_PENDING => 1,
            StudentGift::STATUS_WAITING => 2,
            StudentGift::STATUS_REDEEMED => 3,
            default => 4,
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function giftCard(StudentGift $gift, int $index): array
    {
        $canViewDetails = $this->shouldShowRewardDetails();
        $image = $canViewDetails ? $this->giftImage($gift) : $this->maskedGiftImage();

        return [
            'id' => $gift->id,
            'number' => str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
            'name' => $canViewDetails
                ? StudentGift::displayGiftName($gift->gift_name, $index + 1)
                : StudentGift::defaultGiftName($index + 1),
            'points' => (int) $gift->points_required,
            'status' => $gift->status,
            'status_label' => $this->statusLabel($gift->status),
            'status_hint' => $this->statusHint($gift),
            'image_url' => $image['url'],
            'has_custom_image' => $image['has_custom_image'],
            'is_selected' => (int) $gift->id === (int) $this->selectedId,
            'is_claimable' => $gift->status === StudentGift::STATUS_REACHED,
            'is_current' => $gift->status === StudentGift::STATUS_PENDING,
            'is_redeemed' => $gift->status === StudentGift::STATUS_REDEEMED,
            'reached_at' => $this->formatDate($gift->reached_at),
            'redeemed_at' => $this->formatDate($gift->redeemed_at),
        ];
    }

    /**
     * @return array{url: string, has_custom_image: bool}
     */
    protected function giftImage(StudentGift $gift): array
    {
        $path = trim((string) $gift->gift_image);

        if ($path === '') {
            return [
                'url' => StudentGift::imageUrlFor(null),
                'has_custom_image' => false,
            ];
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return [
                'url' => $path,
                'has_custom_image' => true,
            ];
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (! Storage::disk('public')->exists($path)) {
            return [
                'url' => StudentGift::imageUrlFor(null),
                'has_custom_image' => false,
            ];
        }

        return [
            'url' => asset('storage/'.$path),
            'has_custom_image' => true,
        ];
    }

    /**
     * @return array{url: string, has_custom_image: bool}
     */
    protected function maskedGiftImage(): array
    {
        return [
            'url' => StudentGift::imageUrlFor(null),
            'has_custom_image' => false,
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function statusCounts(): array
    {
        return [
            'ready' => $this->gifts()->where('status', StudentGift::STATUS_REACHED)->count(),
            'claimed' => $this->gifts()->where('status', StudentGift::STATUS_REDEEMED)->count(),
            'current' => $this->gifts()->where('status', StudentGift::STATUS_PENDING)->count(),
            'upcoming' => $this->gifts()->where('status', StudentGift::STATUS_WAITING)->count(),
        ];
    }

    protected function statusLabel(?string $status): string
    {
        return match ($status) {
            StudentGift::STATUS_REACHED => 'Ready',
            StudentGift::STATUS_REDEEMED => 'Claimed',
            StudentGift::STATUS_PENDING => 'Current',
            StudentGift::STATUS_WAITING => 'Upcoming',
            default => 'Reward',
        };
    }

    protected function statusHint(StudentGift $gift): string
    {
        return match ($gift->status) {
            StudentGift::STATUS_REACHED => 'Ready to claim',
            StudentGift::STATUS_REDEEMED => 'Already claimed',
            StudentGift::STATUS_PENDING => 'Next target',
            StudentGift::STATUS_WAITING => 'Waiting in line',
            default => 'Reward milestone',
        };
    }

    protected function formatDate($date): ?string
    {
        return $date ? $date->format('d M Y') : null;
    }

    protected function canClaimRewards(): bool
    {
        return Auth::user()?->hasAnyRole(['student', 'parent']) ?? false;
    }

    protected function canToggleRewardDetails(): bool
    {
        return RewardGiftVisibility::canViewDetails(Auth::user());
    }

    protected function shouldShowRewardDetails(): bool
    {
        return $this->showRewardDetails && $this->canToggleRewardDetails();
    }

    protected function hasLegacyGiftsOutsideCurrentYear(): bool
    {
        if ($this->gifts()->isNotEmpty()) {
            return false;
        }

        return StudentGift::query()
            ->where('student_id', $this->student->id)
            ->where(function ($query): void {
                $query->whereNull('academic_year_id')
                    ->orWhere('academic_year_id', '!=', AcademicYear::currentId());
            })
            ->exists();
    }

    /**
     * @return \Illuminate\Support\Collection<int, StudentGift>
     */
    protected function gifts(): Collection
    {
        if (! isset($this->gifts)) {
            $this->loadGifts();
        }

        return $this->gifts;
    }
}
