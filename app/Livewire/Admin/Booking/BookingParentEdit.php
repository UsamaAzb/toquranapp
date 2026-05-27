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

    public ?string $notes = null;

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
        $this->notes = $booking->notes;
    }

    public function render()
    {
        return view('livewire.admin.booking.booking-parent-edit', [
            'booking' => $this->booking,
        ])->layout('components.layouts.app', ['title' => 'Edit Booking Parent']);
    }

    protected function rules(): array
    {
        return [
            'parentName' => ['required', 'string', 'max:255'],
            'parentEmail' => ['required', 'email', 'max:255'],
            'parentPhone' => ['required', 'string', 'max:20'],
            'bookingReference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
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
            'notes' => filled($validated['notes'] ?? null)
                ? trim($validated['notes'])
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
}
