<?php

namespace App\Livewire\Admin;

use App\Models\ParentModel;
use App\Models\TaskPinHash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;   // for hashing

class CompleteTaskPin extends Component
{
    public int $parentId;   // passed from Blade

    public string $pin = '';

    public int $userId;

    public bool $hasPin = false;

    public function mount(int $parentId): void
    {
        $this->parentId = $parentId;
        $parent = ParentModel::findOrFail($this->parentId);
        $this->userId = $parent->user_id;
        $row = TaskPinHash::firstWhere('user_id', $this->userId);
        if ($row) {
            // Show decrypted PIN in the input
            try {
                $this->pin = (string) ($row->pin_unhash ?? '');
                $this->hasPin = true;
            } catch (\Throwable $e) {
                // corrupted/rotated key: fail safe and ask user to re-save
                $this->pin = '';
                $this->hasPin = false;
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            // 'pin' => ['required','string','size:4'],
            'pin' => ['required', 'regex:/^[0-9]{4}$/'],
            // If you also want at least one UPPERCASE + symbol, add:
            // 'pin' => ['required','string','min:4','regex:/^(?=.*[A-Z])(?=.*[^A-Za-z0-9]).+$/'],
        ]);

        $cipher = Crypt::encryptString($this->pin);

        // One row per user; create or update the same row.
        TaskPinHash::updateOrCreate(
            ['user_id' => $this->userId],
            [
                'pin_unhash' => $this->pin,          // المطلوب تخزينه كنصّ صريح
                'pin_hash' => Hash::make($this->pin), // الهاش للتحقق لاحقًا
            ]
        );

        // Optional: small UX feedback
        $this->hasPin = true;
        $this->dispatch('pin-saved', message: 'PIN saved successfully.');
        session()->flash('pinSaved', 'PIN saved successfully.');

    }

    public function render()
    {
        return view('livewire.admin.complete-task-pin');
    }
}
