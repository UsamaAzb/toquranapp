<button 
    wire:click="toggleStatus" 
    class="btn 
        @if($user->status === 'active') btn-success 
        @else btn-danger 
        @endif">
    {{ ucfirst($user->status) }}
</button>
