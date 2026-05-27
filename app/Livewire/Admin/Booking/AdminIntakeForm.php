<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Booking;

use App\Models\Booking;
use App\Models\GradeLevel;
use App\Models\Services_type;
use App\Services\BookingIntakeDetectionService;
use App\Services\BookingIntakeWriter;
use App\Support\BookingIntakePayloadRules;
use App\Support\BookingServiceInterest;
use App\Support\SchoolSystemOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminIntakeForm extends Component
{
    public bool $isOpen = false;

    public string $intakeMode = 'new';

    public string $parentName = '';

    public string $parentEmail = '';

    public string $parentPhone = '';

    public string $existingFamilySearch = '';

    public ?int $selectedExistingBookingId = null;

    public string $notes = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $children = [];

    /**
     * @var array<int|string, string>
     */
    public array $gradeTitles = [];

    /**
     * @var array<string, string>
     */
    public array $schoolSystemOptions = [];

    /**
     * @var array<int, array{value: string, label: string}>
     */
    public array $serviceOptions = [];

    public function mount(): void
    {
        $this->gradeTitles = $this->gradeTitles();
        $this->schoolSystemOptions = $this->schoolSystemOptions();
        $this->serviceOptions = $this->serviceOptions()->all();
        $this->resetForm();
    }

    #[On('admin-intake-form:open')]
    public function openModal(): void
    {
        $this->resetForm();
        $this->resetErrorBag();
        $this->isOpen = true;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->resetErrorBag();
    }

    public function addChild(): void
    {
        $this->children[] = $this->blankChild();
        $this->dispatch('admin-intake-form:child-added');
    }

    public function updatedIntakeMode(string $value): void
    {
        if ($value === 'existing') {
            $this->parentName = '';
            $this->parentEmail = '';
            $this->parentPhone = '';

            return;
        }

        $this->clearExistingFamilySelection();
    }

    public function updatedExistingFamilySearch(): void
    {
        if ($this->selectedExistingBookingId !== null) {
            $this->selectedExistingBookingId = null;
            $this->parentName = '';
            $this->parentEmail = '';
            $this->parentPhone = '';
        }
    }

    public function removeChild(int $index): void
    {
        if (! array_key_exists($index, $this->children) || count($this->children) === 1) {
            return;
        }

        unset($this->children[$index]);
        $this->children = array_values($this->children);
    }

    public function save(
        BookingIntakeDetectionService $bookingIntakeDetectionService,
        BookingIntakeWriter $bookingIntakeWriter
    ): void {
        $this->resetErrorBag();

        try {
            $payload = $this->validatedPayload();
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return;
        }

        try {
            $result = $bookingIntakeDetectionService->withSubmissionFingerprintLock(
                $payload,
                function () use ($payload, $bookingIntakeDetectionService, $bookingIntakeWriter): array {
                    $detection = $bookingIntakeDetectionService->analyze($payload);

                    if (($detection['route'] ?? null) === 'review') {
                        $review = $bookingIntakeDetectionService->writeReviewRecord($payload, $detection);

                        return [
                            'route' => 'review',
                            'review_id' => $review->id,
                        ];
                    }

                    $booking = $bookingIntakeWriter->createFromDetectionPayload($payload, $detection);

                    return [
                        'route' => 'normal',
                        'booking_id' => $booking->id,
                        'appended' => $booking->children->count() > count($payload['children'] ?? []),
                    ];
                }
            );
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('form', 'Intake could not be saved right now.');

            return;
        }

        $this->closeModal();
        $this->resetForm();

        if (($result['route'] ?? null) === 'review') {
            session()->flash('warning', 'Submission routed to Intake Review.');
            session()->flash('intake_review_id', $result['review_id']);
        } else {
            $message = ! empty($result['appended'])
                ? "Child(ren) added to existing booking #{$result['booking_id']}."
                : "Intake saved to booking queue (booking #{$result['booking_id']}).";

            session()->flash('success', $message);
        }

        $this->dispatch('intake-created');
    }

    public function render()
    {
        return view('livewire.admin.booking.admin-intake-form', [
            'existingFamilyOptions' => $this->existingFamilyOptions(),
            'selectedExistingFamilySummary' => $this->selectedExistingFamilySummary(),
        ]);
    }

    public function selectExistingFamily(int $bookingId): void
    {
        $booking = Booking::query()
            ->select(['id', 'parent_name', 'parent_email', 'parent_phone', 'booking_reference', 'updated_at'])
            ->find($bookingId);

        if (! $booking) {
            $this->addError('selectedExistingBookingId', 'The selected family could not be found.');

            return;
        }

        $this->selectedExistingBookingId = $booking->id;
        $this->parentName = trim((string) ($booking->parent_name ?? ''));
        $this->parentEmail = trim((string) ($booking->parent_email ?? ''));
        $this->parentPhone = trim((string) ($booking->parent_phone ?? ''));
        $this->existingFamilySearch = $this->familySummaryLabel($booking);
        $this->resetErrorBag('selectedExistingBookingId');
    }

    public function clearExistingFamilySelection(): void
    {
        $this->selectedExistingBookingId = null;
        $this->existingFamilySearch = '';
        $this->parentName = '';
        $this->parentEmail = '';
        $this->parentPhone = '';
        $this->resetErrorBag('selectedExistingBookingId');
    }

    protected function validatedPayload(): array
    {
        $selectedExistingBooking = null;

        if ($this->intakeMode === 'existing' && $this->selectedExistingBookingId === null) {
            throw ValidationException::withMessages([
                'selectedExistingBookingId' => 'Select the existing family before saving intake.',
            ]);
        }

        if ($this->intakeMode === 'existing') {
            $selectedExistingBooking = Booking::query()
                ->select(['id', 'parent_name', 'parent_email', 'parent_phone'])
                ->find($this->selectedExistingBookingId);

            if (! $selectedExistingBooking) {
                throw ValidationException::withMessages([
                    'selectedExistingBookingId' => 'The selected family could not be found.',
                ]);
            }
        }

        $payload = [
            'parent_name' => trim((string) ($selectedExistingBooking?->parent_name ?? $this->parentName)),
            'parent_email' => trim((string) ($selectedExistingBooking?->parent_email ?? $this->parentEmail)),
            'parent_phone' => trim((string) ($selectedExistingBooking?->parent_phone ?? $this->parentPhone)),
            'notes' => trim($this->notes),
            'children' => collect($this->children)
                ->map(function (array $child): array {
                    return [
                        'child_name' => trim((string) ($child['child_name'] ?? '')),
                        'child_age' => trim((string) ($child['child_age'] ?? '')),
                        'child_grade' => $child['child_grade'] !== '' ? ($child['child_grade'] ?? null) : null,
                        'school_system' => SchoolSystemOptions::normalize($child['school_system'] ?? null),
                        'service_interests' => collect($child['service_interests'] ?? [])
                            ->map(fn ($value) => BookingServiceInterest::normalize(is_string($value) ? trim($value) : $value))
                            ->filter()
                            ->values()
                            ->all(),
                    ];
                })
                ->values()
                ->all(),
        ];

        $validator = Validator::make(
            $payload,
            BookingIntakePayloadRules::rules(),
            BookingIntakePayloadRules::messages(),
            BookingIntakePayloadRules::attributes()
        );
        BookingIntakePayloadRules::applyAfter($validator, $payload);

        $validated = $validator->validate();

        if ($selectedExistingBooking) {
            $validated['selected_existing_booking_id'] = $selectedExistingBooking->id;
        }

        return $validated;
    }

    protected function gradeTitles(): array
    {
        if (! Schema::hasTable('grade_levels')) {
            return [];
        }

        $query = GradeLevel::query();

        if (Schema::hasColumn('grade_levels', 'active')) {
            $query->orderByDesc('active');
        }

        if (Schema::hasColumn('grade_levels', 'level_order')) {
            $query->orderBy('level_order');
        }

        return $query
            ->orderBy('id')
            ->pluck('title', 'id')
            ->all();
    }

    protected function schoolSystemOptions(): array
    {
        return SchoolSystemOptions::labels();
    }

    protected function serviceOptions(): Collection
    {
        $fallback = collect([
            ['value' => 'Quran Memorization', 'label' => 'Quran Memorization'],
            ['value' => 'Quranic Arabic', 'label' => 'Quranic Arabic'],
            ['value' => 'My Deen Journey', 'label' => 'My Deen Journey'],
            ['value' => 'Paid Parental Consultation', 'label' => 'Paid Parental Consultation'],
            ['value' => 'Sanad Ijazah', 'label' => 'Sanad Ijazah'],
        ]);

        if (! Schema::hasTable('services_types')) {
            return $fallback;
        }

        $hasConfiguredServices = Services_type::query()->exists();
        $query = Services_type::query();

        if (Schema::hasColumn('services_types', 'active')) {
            $query->where(function ($activeQuery) {
                $activeQuery->whereNull('active')
                    ->orWhere('active', 1);
            });
        }

        $services = $query
            ->orderBy('title')
            ->get(['title', 'value'])
            ->map(function (Services_type $service): array {
                $value = BookingServiceInterest::normalize($service->value) ?? $service->value;

                return [
                    'value' => $value,
                    'label' => $service->title,
                ];
            })
            ->filter(fn (array $option) => filled($option['value'] ?? null))
            ->filter(fn (array $option) => BookingServiceInterest::isChildFacingOption($option))
            ->unique('value')
            ->values();

        if (! $hasConfiguredServices) {
            return $fallback;
        }

        return $services;
    }

    /**
     * @return array<string, mixed>
     */
    protected function blankChild(): array
    {
        return [
            'child_name' => '',
            'child_age' => '',
            'child_grade' => null,
            'school_system' => '',
            'service_interests' => [],
        ];
    }

    protected function resetForm(): void
    {
        $this->intakeMode = 'new';
        $this->parentName = '';
        $this->parentEmail = '';
        $this->parentPhone = '';
        $this->existingFamilySearch = '';
        $this->selectedExistingBookingId = null;
        $this->notes = '';
        $this->children = [$this->blankChild()];
    }

    /**
     * @return array<int, array{id: int, label: string, sublabel: string}>
     */
    protected function existingFamilyOptions(): array
    {
        if (! Schema::hasTable('bookings')) {
            return [];
        }

        $search = trim($this->existingFamilySearch);

        $query = Booking::query()
            ->select(['id', 'parent_name', 'parent_email', 'parent_phone', 'booking_reference', 'updated_at'])
            ->where(function ($bookingQuery) {
                $bookingQuery
                    ->where(function ($emailQuery) {
                        $emailQuery->whereNotNull('parent_email')
                            ->where('parent_email', '!=', '');
                    })
                    ->orWhere(function ($phoneQuery) {
                        $phoneQuery->whereNotNull('parent_phone')
                            ->where('parent_phone', '!=', '');
                    });
            });

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('parent_email', 'like', '%'.$search.'%')
                    ->orWhere('parent_phone', 'like', '%'.$search.'%')
                    ->orWhere('parent_name', 'like', '%'.$search.'%');
            });
        }

        return $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit($search === '' ? 10 : 20)
            ->get()
            ->unique(fn (Booking $booking) => strtolower(trim((string) $booking->parent_email)).'|'.trim((string) $booking->parent_phone).'|'.strtolower(trim((string) $booking->parent_name)))
            ->map(fn (Booking $booking) => [
                'id' => $booking->id,
                'label' => $this->familySummaryLabel($booking),
                'sublabel' => $this->familySecondaryLabel($booking),
            ])
            ->values()
            ->all();
    }

    protected function selectedExistingFamilySummary(): ?array
    {
        if ($this->selectedExistingBookingId === null) {
            return null;
        }

        $booking = Booking::query()
            ->select(['id', 'parent_name', 'parent_email', 'parent_phone', 'booking_reference', 'updated_at'])
            ->find($this->selectedExistingBookingId);

        if (! $booking) {
            return null;
        }

        return [
            'id' => $booking->id,
            'label' => $this->familySummaryLabel($booking),
            'sublabel' => $this->familySecondaryLabel($booking),
        ];
    }

    protected function familySummaryLabel(Booking $booking): string
    {
        $parts = collect([
            trim((string) ($booking->parent_name ?? '')),
            trim((string) ($booking->parent_email ?? '')),
            trim((string) ($booking->parent_phone ?? '')),
        ])->filter()->values();

        return $parts->isNotEmpty()
            ? $parts->implode(' • ')
            : 'Family #'.$booking->id;
    }

    protected function familySecondaryLabel(Booking $booking): string
    {
        $parts = collect([
            $booking->booking_reference ? 'Booking ref: '.$booking->booking_reference : null,
            $booking->updated_at ? 'Updated '.$booking->updated_at->diffForHumans() : null,
        ])->filter()->values();

        return $parts->implode(' • ');
    }
}
