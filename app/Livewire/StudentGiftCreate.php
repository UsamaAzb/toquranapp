<?php

namespace App\Livewire;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentGift;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class StudentGiftCreate extends Component
{
    use WithFileUploads; // Livewire file uploads

    // Passed in or taken from query string
    public ?int $student_id = null;

    // Visible inputs
    public ?string $gift_name = null;

    public mixed $gift_image = null;        // Livewire temp file

    public int|string|null $points_required = null;

    // Hidden/defaults
    public ?int $academic_year_id = null;

    public string $status = StudentGift::STATUS_WAITING; // default

    public function mount($studentId = null)
    {
        // Accept either :student-id from Blade OR ?student_id= in the URL
        $resolvedStudentId = $studentId ?? request()->query('student_id');
        $this->student_id = is_numeric($resolvedStudentId) ? (int) $resolvedStudentId : null;
        $this->academic_year_id ??= AcademicYear::currentId();
    }

    protected $messages = [
        'points_required.unique' => 'Each gift for a student must have a unique number of points',
        'gift_image.mimes' => 'Gift images must be JPG, PNG, GIF, or WebP.',
        'gift_image.image' => 'Gift image must be a supported image file.',
    ];

    protected function rules()
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'gift_name' => ['required', 'string', 'max:255'],
            'gift_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'points_required' => [
                'required', 'integer', 'min:0',
                Rule::unique('student_gifts', 'points_required')
                    ->where(fn ($q) => $q
                        ->where('student_id', $this->student_id)
                        ->where('academic_year_id', $this->academic_year_id ?? AcademicYear::currentId())),
            ],
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
            $academicYearId = $this->academic_year_id ?? AcademicYear::currentId();

            if ($this->status !== StudentGift::STATUS_WAITING) {
                throw ValidationException::withMessages([
                    'status' => 'New gifts must be added as upcoming gifts.',
                ]);
            }

            $floor = StudentGift::protectedSchedulingFloorPoints($this->student_id, $academicYearId);

            if ($floor !== null && (int) $this->points_required <= (int) $floor) {
                throw ValidationException::withMessages([
                    'points_required' => "Upcoming gift points must be greater than the protected current target ({$floor}).",
                ]);
            }

            $imagePath = $this->gift_image ? $this->gift_image->store('gifts', 'public') : null;

            $gift = StudentGift::create([
                'academic_year_id' => $academicYearId,
                'student_id' => $this->student_id,
                'gift_id' => null,
                'gift_name' => $this->gift_name,
                'gift_image' => $imagePath,
                'points_required' => (int) $this->points_required,
                'status' => $this->status,
                'approved_by_id' => null,
                'approved_by_name' => null,
                'approval_timestamp' => null,
                'redeemed_at' => null,
            ]);

            StudentGift::maintainUpcomingRunway($gift->student_id, $academicYearId);
        });

        $this->reset(['gift_name', 'gift_image', 'points_required']);
        $this->dispatch('student-gift:saved'); // handled in page JS
        session()->flash('success', 'Gift added successfully.');
    }

    public function render()
    {
        return view('livewire.student-gift-create');
    }
}
