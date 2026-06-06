<?php

namespace App\Livewire\Admin\Booking;

use App\Models\Booking;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingParentEdit extends Component
{
    public Booking $booking;

    public string $parentName = '';

    public string $parentEmail = '';

    public string $parentPhone = '';

    public ?string $bookingReference = null;

    public array $intakeDetails = [];

    public ?string $sharedNotes = null;

    #[Locked]
    public ?string $returnUrl = null;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->loadCount('children');
        $this->returnUrl = request()->query('return');

        $this->parentName = (string) ($booking->parent_name ?? '');
        $this->parentEmail = (string) ($booking->parent_email ?? '');
        $this->parentPhone = (string) ($booking->parent_phone ?? '');
        $this->bookingReference = $booking->booking_reference;
        [$this->intakeDetails, $this->sharedNotes] = $this->intakeDetailsFromNotes($booking->notes);
    }

    public function render()
    {
        return view('livewire.admin.booking.booking-parent-edit', [
            'booking' => $this->booking,
            'intakeDetails' => $this->intakeDetails,
            'sharedNotes' => $this->sharedNotes,
        ])->layout('components.layouts.app', ['title' => 'Edit Booking Parent']);
    }

    protected function rules(): array
    {
        return [
            'parentName' => ['required', 'string', 'max:255'],
            'parentEmail' => ['required', 'email', 'max:255'],
            'parentPhone' => ['required', 'string', 'max:20'],
            'bookingReference' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $this->booking->update([
            'parent_name' => trim($validated['parentName']),
            'parent_email' => trim($validated['parentEmail']),
            'parent_phone' => trim($validated['parentPhone']),
            'booking_reference' => filled($validated['bookingReference'] ?? null)
                ? trim($validated['bookingReference'])
                : null,
        ]);

        $this->booking->refresh();
        $this->booking->loadCount('children');

        session()->flash('success', 'Parent information updated.');
        $this->dispatch('parent-saved', bookingId: $this->booking->id);
    }

    public function cancelUrl(): string
    {
        if (filled($this->returnUrl) && $this->isInternalUrl($this->returnUrl)) {
            return $this->returnUrl;
        }

        return route('admin.bookings.livewire');
    }

    protected function isInternalUrl(string $url): bool
    {
        if (str_starts_with($url, '//')) {
            return false;
        }

        return str_starts_with($url, url('/')) || str_starts_with($url, '/');
    }

    private function intakeDetailsFromNotes(?string $notes): array
    {
        $notes = trim((string) $notes);

        if ($notes === '') {
            return [[], null];
        }

        $json = json_decode($notes, true);
        if (is_array($json)) {
            $children = collect(data_get($json, 'children', []))
                ->map(fn ($child): string => trim((string) data_get($child, 'name', '')))
                ->filter()
                ->values()
                ->all();

            return [[
                'country' => data_get($json, 'parent.country'),
                'preferred_date' => data_get($json, 'preferred.date'),
                'preferred_time' => data_get($json, 'preferred.time'),
                'main_concerns' => data_get($json, 'main_concerns'),
                'children' => $children,
                'contract' => data_get($json, 'handoff_contract'),
            ], null];
        }

        $details = [];
        foreach ([
            'country' => '/^Country:\s*(.+)$/mi',
            'preferred_date' => '/^Preferred date:\s*(.+)$/mi',
            'preferred_time' => '/^Preferred time:\s*(.+)$/mi',
            'main_concerns' => '/^Main concerns:\s*(.+)$/mi',
        ] as $key => $pattern) {
            if (preg_match($pattern, $notes, $matches)) {
                $details[$key] = trim($matches[1]);
            }
        }

        if ($details !== []) {
            $sharedNotes = trim(preg_replace([
                '/^Country:\s*.+$/mi',
                '/^Preferred date:\s*.+$/mi',
                '/^Preferred time:\s*.+$/mi',
                '/^Main concerns:\s*.+$/mi',
            ], '', $notes) ?? '');

            return [$details, $sharedNotes !== '' ? $sharedNotes : null];
        }

        return [[], $notes];
    }
}
