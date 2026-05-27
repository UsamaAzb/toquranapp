<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;

class UserStatusToggle extends Component
{
    public User $user;

    public function toggleStatus()
    {
        $this->user->status = $this->user->status === 'active' ? 'inactive' : 'active';
        $this->user->save();
    }

    public function render()
    {
        return view('livewire.admin.user-status-toggle');
    }
}
