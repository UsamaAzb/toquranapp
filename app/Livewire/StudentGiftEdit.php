<?php

namespace App\Livewire;

use App\Models\AcademicYear;
use App\Models\StudentGift;
use App\Services\RewardProgressionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class StudentGiftEdit extends Component
{
    use WithFileUploads;

    public $student_id;

    public $giftId;

    public $gift_name;

    public $points_required;

    public $gift_image;

    public $current_image;

    public $status;

    public ?int $academic_year_id = null;

    public bool $isPendingTarget = false;

    public function mount($studentId = null)
    {
        $this->student_id = $studentId ?? null;
    }

    protected function resolveScopedGiftOrFail(int $giftId): StudentGift
    {
        $query = StudentGift::query()->whereKey($giftId);

        if ($this->student_id !== null) {
            $query->where('student_id', $this->student_id);
        }

        return $query->firstOrFail();
    }

    #[On('open-gift-editor')]
    public function openEditor($id)
    {
        $this->reset(['gift_image']);
        $this->resetValidation();

        $gift = $this->resolveScopedGiftOrFail((int) $id);
        $this->student_id = $gift->student_id;
        $this->giftId = $gift->id;
        $this->gift_name = $gift->gift_name;
        $this->points_required = $gift->points_required;
        $this->current_image = $gift->gift_image;
        $this->status = $gift->status;
        $this->academic_year_id = $gift->academic_year_id ? (int) $gift->academic_year_id : AcademicYear::currentId();
        $this->isPendingTarget = $gift->status === StudentGift::STATUS_PENDING;

        $this->dispatch('show-edit-offcanvas');
    }

    protected $messages = [
        'points_required.unique' => 'Each gift for a student must have a unique number of points',
        'gift_image.mimes' => 'Gift images must be JPG, PNG, GIF, or WebP.',
        'gift_image.image' => 'Gift image must be a supported image file.',
    ];

    protected function rules()
    {
        return [
            'gift_name' => ['required', 'string', 'max:255'],
            'points_required' => [
                'required',
                'integer',
                'min:0',
                Rule::unique('student_gifts', 'points_required')
                    ->where(fn ($query) => $query
                        ->where('student_id', $this->student_id)
                        ->where('academic_year_id', $this->academic_year_id ?? AcademicYear::currentId()))
                    ->ignore($this->giftId),
            ],
            'gift_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'status' => ['required', Rule::in(StudentGift::statuses())],
        ];
    }

    public function updatedGiftImage(): void
    {
        try {
            $this->validateOnly('gift_image');
        } catch (ValidationException $exception) {
            $this->reset('gift_image');

            throw $exception;
        }
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            $gift = $this->resolveScopedGiftOrFail((int) $this->giftId);

            if (in_array($gift->status, [StudentGift::STATUS_REACHED, StudentGift::STATUS_REDEEMED], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Reached and redeemed gifts are historical records and cannot be edited here.',
                ]);
            }

            $academicYearId = $gift->academic_year_id ?? AcademicYear::currentId();

            if ($gift->status === StudentGift::STATUS_PENDING) {
                $this->updatePendingGift($gift, $academicYearId);

                return;
            }

            if ($this->status !== StudentGift::STATUS_WAITING) {
                throw ValidationException::withMessages([
                    'status' => 'Gift edits in this panel must remain upcoming gifts.',
                ]);
            }

            $floor = StudentGift::protectedSchedulingFloorPoints($gift->student_id, $academicYearId);

            if ($floor !== null && (int) $this->points_required <= (int) $floor) {
                throw ValidationException::withMessages([
                    'points_required' => "Upcoming gift points must be greater than the protected current target ({$floor}).",
                ]);
            }

            $path = $this->storeReplacementImage();

            $gift->update([
                'gift_name' => $this->gift_name,
                'points_required' => $this->points_required,
                'gift_image' => $path,
                'status' => $this->status,
            ]);

            StudentGift::maintainUpcomingRunway($gift->student_id, $academicYearId);
        });

        $this->dispatch('hide-edit-offcanvas');
        $this->dispatch('gift-updated');
        session()->flash('success', 'Gift updated successfully.');
        $this->reset(['gift_image']);
    }

    private function updatePendingGift(StudentGift $gift, int $academicYearId): void
    {
        $completedFloor = StudentGift::maxCompletedPoints($gift->student_id, $academicYearId);

        if ($completedFloor !== null && (int) $this->points_required <= (int) $completedFloor) {
            throw ValidationException::withMessages([
                'points_required' => "Active target points must be greater than the latest earned reward ({$completedFloor}).",
            ]);
        }

        $nextWaitingPoints = StudentGift::query()
            ->where('student_id', $gift->student_id)
            ->where('academic_year_id', $academicYearId)
            ->where('status', StudentGift::STATUS_WAITING)
            ->whereNotNull('points_required')
            ->orderBy('points_required')
            ->value('points_required');

        if ($nextWaitingPoints !== null && (int) $this->points_required >= (int) $nextWaitingPoints) {
            throw ValidationException::withMessages([
                'points_required' => "Active target points must stay below the next upcoming reward ({$nextWaitingPoints}).",
            ]);
        }

        $gift->update([
            'gift_name' => $this->gift_name,
            'points_required' => $this->points_required,
            'gift_image' => $this->storeReplacementImage(),
            'status' => StudentGift::STATUS_PENDING,
        ]);

        $progression = app(RewardProgressionService::class);
        $currentPoints = $progression->currentPoints($gift->student_id, $academicYearId);

        if ($currentPoints >= (int) $this->points_required) {
            $progression->advanceGiftQueueForTotal($gift->student_id, $currentPoints, $academicYearId);

            return;
        }

        StudentGift::maintainUpcomingRunway($gift->student_id, $academicYearId);
    }

    private function storeReplacementImage(): ?string
    {
        $path = $this->current_image;

        if ($this->gift_image) {
            $path = $this->gift_image->store('gifts', 'public');

            if ($this->current_image && Storage::disk('public')->exists($this->current_image)) {
                Storage::disk('public')->delete($this->current_image);
            }
        }

        return $path;
    }

    public function render()
    {
        return view('livewire.student-gift-edit');
    }
}
