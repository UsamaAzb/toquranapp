<div class="row g-6">
  <div class="col-12">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-6">
      <div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <h4 class="mb-0">Staff Users</h4>
          <details class="intake-info intake-info--inline">
            <summary class="intake-info__trigger" aria-label="Open staff users info">
              <i class="icon-base ti tabler-info-circle icon-18px"></i>
            </summary>
            <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
              Superadmin-only launch control for internal app users who process intake, support families, or teach.
            </div>
          </details>
        </div>
      </div>
    </div>
  </div>

  @include('livewire.admin.booking.partials.shared-page-ui')

  @if (session()->has('success'))
    <div class="col-12">
      <div class="alert alert-success alert-dismissible mb-0" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  @endif

  @if ($generatedPassword)
    <div class="col-12">
      <div class="alert alert-warning mb-0" role="alert">
        <div class="fw-semibold mb-1">One-time password</div>
        <code>{{ $generatedPassword }}</code>
      </div>
    </div>
  @endif

  @foreach ($stats as $stat)
    <div class="col-12 col-sm-6 col-xl-3">
      <button type="button"
        class="card card-border-shadow-primary h-100 w-100 text-start {{ $stat['active'] ? 'border border-2 border-primary shadow-lg' : 'border-0' }}"
        wire:click="filterByRole('{{ $stat['role'] }}')">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="icon-base ti tabler-user-cog icon-28px"></i>
              </span>
            </div>
            <div>
              <h4 class="mb-0">{{ number_format($stat['count']) }}</h4>
              <p class="mb-0">{{ $stat['label'] }}</p>
            </div>
          </div>
        </div>
      </button>
    </div>
  @endforeach

  <div class="col-12 col-xl-4">
    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">{{ $editingUserId ? 'Edit Staff User' : 'Create Staff User' }}</h5>
      </div>
      <div class="card-body pt-4">
        <form wire:submit="{{ $editingUserId ? 'updateStaffUser' : 'createStaffUser' }}" class="row g-4">
          <div class="col-12 col-md-6">
            <label class="form-label" for="staff-first-name">First Name</label>
            <input id="staff-first-name" type="text" class="form-control @error('firstName') is-invalid @enderror" wire:model.defer="firstName">
            @error('firstName') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="staff-last-name">Last Name</label>
            <input id="staff-last-name" type="text" class="form-control @error('lastName') is-invalid @enderror" wire:model.defer="lastName">
            @error('lastName') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12">
            <label class="form-label" for="staff-email">Email</label>
            <input id="staff-email" type="email" class="form-control @error('email') is-invalid @enderror" wire:model.defer="email">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12">
            <label class="form-label" for="staff-phone">Phone</label>
            <input id="staff-phone" type="tel" class="form-control @error('phone') is-invalid @enderror" wire:model.defer="phone">
            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="staff-role">Role</label>
            <select id="staff-role" class="form-select @error('role') is-invalid @enderror" wire:model.defer="role">
              @foreach ($roleOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12 col-md-6">
            <label class="form-label" for="staff-status">Status</label>
            <select id="staff-status" class="form-select @error('status') is-invalid @enderror" wire:model.defer="status">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12">
            <label class="form-label" for="staff-password">{{ $editingUserId ? 'New Password' : 'Password' }}</label>
            <div class="input-group" x-data="{ visible: false }">
              <input id="staff-password" :type="visible ? 'text' : 'password'" class="form-control @error('password') is-invalid @enderror" wire:model.defer="password" autocomplete="new-password">
              <button class="btn btn-outline-secondary" type="button" x-on:click="visible = ! visible" :aria-label="visible ? 'Hide password' : 'Show password'">
                <i class="icon-base ti" :class="visible ? 'tabler-eye-off' : 'tabler-eye'"></i>
              </button>
            </div>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12">
            <label class="form-label" for="staff-password-confirmation">Confirm Password</label>
            <div class="input-group" x-data="{ visible: false }">
              <input id="staff-password-confirmation" :type="visible ? 'text' : 'password'" class="form-control @error('passwordConfirmation') is-invalid @enderror" wire:model.defer="passwordConfirmation" autocomplete="new-password">
              <button class="btn btn-outline-secondary" type="button" x-on:click="visible = ! visible" :aria-label="visible ? 'Hide password confirmation' : 'Show password confirmation'">
                <i class="icon-base ti" :class="visible ? 'tabler-eye-off' : 'tabler-eye'"></i>
              </button>
            </div>
            @error('passwordConfirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12 d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary">
              {{ $editingUserId ? 'Save Staff User' : 'Create Staff User' }}
            </button>
            @if ($editingUserId)
              <button type="button" class="btn btn-label-secondary" wire:click="cancelEdit">Cancel</button>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-8">
    <div class="card">
      <div class="card-header border-bottom">
        <div class="row g-3 align-items-center">
          <div class="col-12 col-lg">
            <h5 class="card-title mb-0">Internal Staff</h5>
          </div>
          <div class="col-12 col-lg-4">
            <input type="search" class="form-control" placeholder="Search staff" wire:model.live.debounce.300ms="search">
          </div>
          <div class="col-6 col-lg-3">
            <select class="form-select" wire:model.live="roleFilter">
              <option value="all">All Roles</option>
              @foreach ($roleOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-6 col-lg-3">
            <select class="form-select" wire:model.live="statusFilter">
              <option value="all">All Statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>
        </div>
      </div>

      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Role</th>
              <th>Status</th>
              <th>Contact</th>
              <th>Password</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($staffUsers as $staffUser)
              @php
                $staffRole = $staffUser->roles->pluck('name')->first(fn ($name) => array_key_exists($name, $roleOptions));
                $statusValue = $staffUser->status ?? 'active';
              @endphp
              <tr wire:key="staff-user-{{ $staffUser->id }}">
                <td>
                  <div class="fw-semibold">{{ $staffUser->name }}</div>
                  <small class="text-body-secondary">#{{ $staffUser->id }}</small>
                </td>
                <td>
                  <span class="badge bg-label-primary">{{ $roleOptions[$staffRole] ?? 'Staff' }}</span>
                </td>
                <td>
                  <span class="badge {{ $statusValue === 'active' ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ ucfirst($statusValue) }}
                  </span>
                </td>
                <td>
                  <div>{{ $staffUser->email }}</div>
                  @if ($staffUser->phone)
                    <small class="text-body-secondary">{{ $staffUser->phone }}</small>
                  @endif
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    @if ($this->passwordIsVisible((int) $staffUser->id))
                      <code>{{ $this->visiblePassword($staffUser) }}</code>
                    @else
                      <code aria-label="Password hidden">••••••••</code>
                    @endif
                    <button type="button" class="btn btn-sm btn-label-secondary" wire:click="togglePasswordVisibility({{ $staffUser->id }})">
                      {{ $this->passwordIsVisible((int) $staffUser->id) ? 'Hide' : 'Show' }}
                    </button>
                  </div>
                </td>
                <td class="text-end">
                  <div class="d-inline-flex gap-2">
                    <button type="button" class="btn btn-sm btn-label-primary" wire:click="editStaffUser({{ $staffUser->id }})">
                      Edit
                    </button>
                    <button type="button" class="btn btn-sm {{ $statusValue === 'active' ? 'btn-label-warning' : 'btn-label-success' }}" wire:click="toggleStatus({{ $staffUser->id }})">
                      {{ $statusValue === 'active' ? 'Deactivate' : 'Activate' }}
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-body-secondary py-5">No staff users match the current filters.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="card-footer">
        {{ $staffUsers->links() }}
      </div>
    </div>
  </div>
</div>
