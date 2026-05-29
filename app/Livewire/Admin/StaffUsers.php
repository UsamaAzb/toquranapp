<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Services\CredentialService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class StaffUsers extends Component
{
    use WithPagination;

    private const STAFF_ROLES = [
        'super_admin' => 'Superadmin',
        'admin' => 'Admin',
        'customer_support' => 'Customer Support',
        'teacher' => 'Teacher',
    ];

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $roleFilter = 'all';

    public string $statusFilter = 'all';

    public int $perPage = 10;

    public ?int $editingUserId = null;

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $phone = '';

    public string $role = 'teacher';

    public string $status = 'active';

    public string $password = '';

    public string $passwordConfirmation = '';

    public ?string $generatedPassword = null;

    public array $revealedPasswordUserIds = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasRole('super_admin'), 403);

        $this->ensureStaffRolesExist();
        $this->normalizeFilters();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function createStaffUser(CredentialService $credentials): void
    {
        $this->editingUserId = null;
        $data = $this->validate($this->rules());

        $user = new User;
        $plainPassword = $this->resolvedPassword();
        $user->forceFill(['password' => Hash::make($plainPassword)]);
        $this->fillStaffUser($user, $data);
        $credentials->generateAndStore($user, $plainPassword);
        $user->assignRole($data['role']);

        $this->generatedPassword = $plainPassword;
        $this->resetForm(keepGeneratedPassword: true);
        $this->resetPage();

        session()->flash('success', 'Staff user created.');
    }

    public function editStaffUser(int $userId): void
    {
        $user = $this->staffUserQuery()->findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->firstName = (string) ($user->first_name ?? '');
        $this->lastName = (string) ($user->last_name ?? '');
        $this->email = (string) $user->email;
        $this->phone = (string) ($user->phone ?? '');
        $this->role = $this->primaryStaffRole($user) ?? 'teacher';
        $this->status = (string) ($user->status ?? 'active');
        $this->password = '';
        $this->passwordConfirmation = '';
        $this->generatedPassword = null;
    }

    public function updateStaffUser(CredentialService $credentials): void
    {
        abort_unless($this->editingUserId !== null, 422);

        $user = $this->staffUserQuery()->findOrFail($this->editingUserId);
        $data = $this->validate($this->rules($user));

        $this->guardSelfAndLastSuperadmin($user, $data['role'], $data['status']);

        $this->fillStaffUser($user, $data);
        $user->syncRoles([$data['role']]);

        if ($data['password'] !== null && $data['password'] !== '') {
            $credentials->generateAndStore($user, $data['password']);
            $this->generatedPassword = $data['password'];
        } else {
            $this->generatedPassword = null;
        }

        $this->resetForm();

        session()->flash('success', 'Staff user updated.');
    }

    public function toggleStatus(int $userId): void
    {
        $user = $this->staffUserQuery()->findOrFail($userId);
        $newStatus = ($user->status ?? 'active') === 'active' ? 'inactive' : 'active';

        $this->guardSelfAndLastSuperadmin($user, $this->primaryStaffRole($user) ?? 'teacher', $newStatus);

        $user->forceFill(['status' => $newStatus])->save();

        session()->flash('success', $newStatus === 'active' ? 'Staff user activated.' : 'Staff user deactivated.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function filterByRole(string $role): void
    {
        $this->roleFilter = $this->roleFilter === $role ? 'all' : $role;
        $this->resetPage();
    }

    public function togglePasswordVisibility(int $userId): void
    {
        $user = $this->staffUserQuery()->findOrFail($userId);
        $visibleIds = array_map('intval', $this->revealedPasswordUserIds);

        if (in_array((int) $user->id, $visibleIds, true)) {
            $this->revealedPasswordUserIds = array_values(array_diff($visibleIds, [(int) $user->id]));

            return;
        }

        $this->revealedPasswordUserIds[] = (int) $user->id;
    }

    public function passwordIsVisible(int $userId): bool
    {
        return in_array($userId, array_map('intval', $this->revealedPasswordUserIds), true);
    }

    public function render(): View
    {
        $this->normalizeFilters();

        return view('livewire.admin.staff-users', [
            'staffUsers' => $this->staffUserQuery()->paginate($this->perPage),
            'roleOptions' => self::STAFF_ROLES,
            'stats' => $this->staffStats(),
        ])->layout('components.layouts.app', ['title' => 'Staff Users']);
    }

    protected function rules(?User $user = null): array
    {
        $userId = $user?->id;

        return [
            'firstName' => ['required', 'string', 'max:120'],
            'lastName' => ['nullable', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['required', Rule::in(array_keys(self::STAFF_ROLES))],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => [$user ? 'nullable' : 'required', 'string'],
        ];
    }

    public function visiblePassword(User $user): string
    {
        return app(CredentialService::class)->reveal($user) ?? 'Not stored';
    }

    protected function fillStaffUser(User $user, array $data): void
    {
        $firstName = trim((string) $data['firstName']);
        $lastName = trim((string) ($data['lastName'] ?? ''));
        $fullName = trim($firstName.' '.$lastName);

        $attributes = [
            'name' => $fullName !== '' ? $fullName : trim((string) $data['email']),
            'email' => trim((string) $data['email']),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ];

        foreach ([
            'first_name' => $firstName,
            'last_name' => $lastName !== '' ? $lastName : null,
            'phone' => trim((string) ($data['phone'] ?? '')) ?: null,
            'status' => $data['status'],
        ] as $column => $value) {
            if (Schema::hasColumn($user->getTable(), $column)) {
                $attributes[$column] = $value;
            }
        }

        $user->forceFill($attributes)->save();
    }

    protected function staffUserQuery(): Builder
    {
        return User::query()
            ->with('roles')
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', array_keys(self::STAFF_ROLES)))
            ->when($this->search !== '', function (Builder $query): void {
                $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($this->search)).'%';

                $query->where(function (Builder $nested) use ($term): void {
                    $nested->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);

                    if (Schema::hasColumn('users', 'phone')) {
                        $nested->orWhere('phone', 'like', $term);
                    }
                });
            })
            ->when($this->roleFilter !== 'all', function (Builder $query): void {
                $query->role($this->roleFilter);
            })
            ->when($this->statusFilter !== 'all' && Schema::hasColumn('users', 'status'), function (Builder $query): void {
                $query->where('status', $this->statusFilter);
            })
            ->when(
                Schema::hasColumn('users', 'status'),
                fn (Builder $query) => $query->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'inactive' THEN 1 ELSE 2 END")
            )
            ->orderBy('name');
    }

    protected function staffStats(): array
    {
        return collect(self::STAFF_ROLES)
            ->map(fn (string $label, string $role): array => [
                'role' => $role,
                'label' => $label,
                'count' => User::role($role)->count(),
                'active' => $this->roleFilter === $role,
            ])
            ->values()
            ->all();
    }

    protected function primaryStaffRole(User $user): ?string
    {
        return $user->roles
            ->pluck('name')
            ->first(fn (string $role): bool => array_key_exists($role, self::STAFF_ROLES));
    }

    protected function guardSelfAndLastSuperadmin(User $user, string $newRole, string $newStatus): void
    {
        $currentUserId = (int) auth()->id();
        $currentRole = $this->primaryStaffRole($user);
        $removesSuperadminAccess = $currentRole === 'super_admin'
            && ($newRole !== 'super_admin' || $newStatus !== 'active');

        if ((int) $user->id === $currentUserId && ($newRole !== 'super_admin' || $newStatus !== 'active')) {
            throw ValidationException::withMessages([
                'role' => 'You cannot remove your own active superadmin access.',
            ]);
        }

        if ($removesSuperadminAccess && $this->activeSuperadminCount() <= 1) {
            throw ValidationException::withMessages([
                'role' => 'At least one active superadmin account must remain.',
            ]);
        }
    }

    protected function activeSuperadminCount(): int
    {
        return User::role('super_admin')
            ->when(Schema::hasColumn('users', 'status'), fn (Builder $query) => $query->where('status', 'active'))
            ->count();
    }

    protected function resolvedPassword(): string
    {
        return $this->password;
    }

    protected function resetForm(bool $keepGeneratedPassword = false): void
    {
        $generatedPassword = $this->generatedPassword;

        $this->reset([
            'editingUserId',
            'firstName',
            'lastName',
            'email',
            'phone',
            'password',
            'passwordConfirmation',
        ]);

        $this->role = 'teacher';
        $this->status = 'active';
        $this->generatedPassword = $keepGeneratedPassword ? $generatedPassword : null;
        $this->resetValidation();
    }

    protected function normalizeFilters(): void
    {
        if (! array_key_exists($this->roleFilter, self::STAFF_ROLES) && $this->roleFilter !== 'all') {
            $this->roleFilter = 'all';
        }

        if (! in_array($this->statusFilter, ['all', 'active', 'inactive', 'suspended'], true)) {
            $this->statusFilter = 'all';
        }

        if (! in_array($this->perPage, [10, 25, 50], true)) {
            $this->perPage = 10;
        }

        if (! array_key_exists($this->role, self::STAFF_ROLES)) {
            $this->role = 'teacher';
        }
    }

    protected function ensureStaffRolesExist(): void
    {
        foreach (array_keys(self::STAFF_ROLES) as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
    }
}
