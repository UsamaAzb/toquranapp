@extends('layouts/layoutMaster')

@section('title', 'User View - Pages')

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sortablejs/sortable.js'])
@endsection

@push('styles')
<style>
  .reward-queue-card {
    overflow: hidden;
  }

  .reward-queue-toolbar {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--bs-border-color);
  }

  .reward-step-control {
    display: flex;
    align-items: flex-end;
    gap: 0.5rem;
  }

  .reward-step-control .form-control {
    width: 8rem;
  }

  .reward-privacy-card .teacher-privacy-menu {
    width: min(100%, 28rem);
    max-height: 16rem;
    overflow-y: auto;
  }

  .reward-privacy-card .privacy-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.5rem;
    background: var(--bs-primary-bg-subtle);
    color: var(--bs-primary);
  }

  .reward-queue-list {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .reward-queue-row {
    display: grid;
    grid-template-columns: 2.5rem 3.25rem minmax(0, 1fr) minmax(5.75rem, auto) auto;
    align-items: center;
    gap: 0.875rem;
    min-height: 5.25rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--bs-border-color);
    background: var(--bs-card-bg);
  }

  .reward-queue-row:first-child {
    border-top: 0;
  }

  .reward-queue-row.is-locked {
    background: var(--bs-body-bg);
  }

  .gift-drag-handle,
  .gift-lock-handle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border: 1px solid var(--bs-border-color);
    border-radius: 0.375rem;
    color: var(--bs-secondary-color);
  }

  .gift-drag-handle {
    cursor: grab;
    touch-action: none;
    background: var(--bs-card-bg);
  }

  .gift-drag-handle:active {
    cursor: grabbing;
  }

  .gift-grip-dots {
    width: 0.875rem;
    height: 1.25rem;
    background-image: radial-gradient(currentColor 1.4px, transparent 1.6px);
    background-size: 0.4375rem 0.4375rem;
  }

  .reward-gift-avatar {
    width: 3rem;
    height: 3rem;
    object-fit: contain;
    padding: 0.25rem;
    background: var(--bs-body-bg);
    border: 1px solid var(--bs-border-color);
  }

  .reward-gift-title {
    min-width: 0;
  }

  .reward-point-pill {
    justify-self: end;
    min-width: 5.75rem;
    text-align: end;
    font-weight: 600;
  }

  .gift-sortable-ghost {
    opacity: 0.45;
  }

  .gift-sortable-chosen {
    background: var(--bs-primary-bg-subtle);
  }

  @media (max-width: 575.98px) {
    .reward-queue-toolbar,
    .reward-step-control {
      align-items: stretch;
      flex-direction: column;
    }

    .reward-step-control .form-control {
      width: 100%;
    }

    .reward-queue-row {
      grid-template-columns: 2.5rem 3rem minmax(0, 1fr);
      gap: 0.75rem;
    }

    .reward-point-pill {
      grid-column: 2 / 4;
      justify-self: start;
      text-align: start;
    }

    .reward-queue-row .btn {
      grid-column: 2 / 4;
      justify-self: start;
    }
  }
</style>
@endpush


@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
@php
  $isLifecycleManagedStudent = $student->isLifecycleManaged();
  $studentStatusLabel = $student->lifecycleStatusLabel();
  $studentStatusTone = $student->lifecycleStatusTone();
  $familyWorkspaceUrl = $isLifecycleManagedStudent ? route('admin.families.show', $student->parent_id) : null;
@endphp
<div class="row">
  <!-- User Sidebar -->
  <div class="col-xl-4 col-lg-5 order-1 order-md-0">
    <!-- User Card -->
    <div class="card mb-6">
      <div class="card-body pt-12">

        <h5 class="pb-4 border-bottom mb-4">Account Details</h5>
        <div class="info-container">
          <ul class="list-unstyled mb-6">
            <li class="mb-2">
              <p class="h6">Parent Information:</p>
              <div>{{$student->parent->first_name}} {{$student->parent->last_name}}</div>
              <div>{{$student->parent->email}}</div>
                <div>{{$student->parent->phone}}</div>
            </li>
            <hr>
            <li class="mb-2">
              <p class="h6">Student Information:</p>
              <div>{{$student->first_name}} {{$student->last_name}}</div>
              <div>{{$student->age}} years</div>
              <div>{{$student->gradeLevel->title}}</div>
            </li>
              <hr>
            <li class="mb-2">
              <p class="h6">academic Information:</p>
              <div>{{ \App\Support\SchoolSystemOptions::display($student->school_system) ?? '-' }}</div>
              <div>{{$student->program->title}} ({{$student->program->code}})</div>
              <span>{{$student->services_type->title}} service</span>
            </li>
          <hr>
            <li class="mb-2">
              <span class="h6">Status:</span>
              @if ($isLifecycleManagedStudent)
                <span class="badge bg-label-{{ $studentStatusTone }}">{{ $studentStatusLabel }}</span>
              @else
                <span>{{ $studentStatusLabel }}</span>
              @endif
            </li>

          </ul>
          <div class="d-flex justify-content-center">
            <a href="javascript:;" class="btn btn-primary me-4" data-bs-target="#editUser" data-bs-toggle="modal">Edit</a>
            @if ($familyWorkspaceUrl)
              <a href="{{ $familyWorkspaceUrl }}" class="btn btn-label-secondary">Manage in Family Workspace</a>
            @else
              <livewire:admin.user-status-toggle :user="$student->user" />
            @endif
          </div>
        </div>
      </div>
    </div>
    <!-- /User Card -->
    <!-- Plan Card -->
    <div class="card mb-6 border border-2 border-primary rounded primary-shadow">
      <div class="card-body">
          <div>for fixed and flexiable payment</div>
        <div class="d-flex justify-content-between align-items-start">

          <span class="badge bg-label-primary">Standard</span>
          <div class="d-flex justify-content-center">
            <sub class="h5 pricing-currency mb-auto mt-1 text-primary">$</sub>
            <h1 class="mb-0 text-primary">99</h1>
            <sub class="h6 pricing-duration mt-auto mb-3 fw-normal">month</sub>
          </div>
        </div>
        <ul class="list-unstyled g-2 my-6">
          <li class="mb-2 d-flex align-items-center"><i class="icon-base ti tabler-circle-filled icon-10px text-secondary me-2"></i><span>10 Users</span></li>
          <li class="mb-2 d-flex align-items-center"><i class="icon-base ti tabler-circle-filled icon-10px text-secondary me-2"></i><span>Up to 10 GB storage</span></li>
          <li class="mb-2 d-flex align-items-center"><i class="icon-base ti tabler-circle-filled icon-10px text-secondary me-2"></i><span>Basic Support</span></li>
        </ul>
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="h6 mb-0">Days</span>
          <span class="h6 mb-0">26 of 30 Days</span>
        </div>
        <div class="progress mb-1 bg-label-primary" style="height: 6px;">
          <div class="progress-bar" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <small>4 days remaining</small>
        <div class="d-grid w-100 mt-6">
          <button class="btn btn-primary" data-bs-target="#upgradePlanModal" data-bs-toggle="modal">Upgrade Plan</button>
        </div>
      </div>
    </div>
    <!-- /Plan Card -->
  </div>
  <!--/ User Sidebar -->

  <!-- User Content -->
  <div class="col-xl-8 col-lg-7 order-0 order-md-1">
    <!-- User Pills -->
    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-md-row flex-wrap mb-6 row-gap-2">
        <li class="nav-item">
          <a class="nav-link " href="{{ route('admin.students.account', $student->id) }}"><i class="icon-base ti tabler-user-check icon-sm me-1_5"></i>Account</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('admin.students.security', $student->id) }}"><i class="icon-base ti tabler-lock icon-sm me-1_5"></i>Security</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('app/user/view/billing') }}"><i class="icon-base ti tabler-bookmark icon-sm me-1_5"></i>Billing & Plans</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('admin.students.show_reward', $student->id) }}"><i class="icon-base ti tabler-bell icon-sm me-1_5"></i>Reward System</a>
        </li>

      </ul>
    </div>

    <livewire:student-gift-create :student-id="$student->id" />
    <livewire:student-gift-edit :student-id="$student->id" />

    <div class="card mb-6 reward-privacy-card">
      <div class="card-body">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-4">
          <div class="d-flex gap-3">
            <span class="privacy-icon" aria-hidden="true">
              <i class="icon-base ti tabler-shield-lock icon-20px"></i>
            </span>
            <div>
              <h5 class="mb-1">Reward Privacy</h5>
              <p class="text-body-secondary mb-0">
                Teachers are hidden from gift names and images unless selected here.
              </p>
            </div>
          </div>
          <span class="badge bg-label-primary align-self-start">
            {{ count($rewardPrivacySelectedTeacherIds ?? []) }} allowed
          </span>
        </div>

        <form method="POST" action="{{ route('admin.student-gifts.reward-privacy') }}" class="mt-4">
          @csrf
          <input type="hidden" name="student_id" value="{{ $student->id }}">

          @if(($rewardPrivacyTeachers ?? collect())->isEmpty())
            <div class="alert alert-secondary mb-0">No teacher accounts are available yet.</div>
          @else
            <label class="form-label">Teachers who can see reward details</label>
            <div class="dropdown">
              <button
                class="btn btn-label-secondary dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside"
                aria-expanded="false"
              >
                {{ count($rewardPrivacySelectedTeacherIds ?? []) }} selected
              </button>
              <div class="dropdown-menu teacher-privacy-menu p-2">
                @foreach($rewardPrivacyTeachers as $teacher)
                  <label class="dropdown-item d-flex align-items-start gap-2 rounded">
                    <input
                      type="checkbox"
                      class="form-check-input mt-1"
                      name="teacher_user_ids[]"
                      value="{{ $teacher->id }}"
                      @checked(in_array((int) $teacher->id, $rewardPrivacySelectedTeacherIds ?? [], true))
                    >
                    <span style="min-width: 0;">
                      <span class="d-block fw-medium text-truncate">{{ $teacher->name ?: $teacher->email }}</span>
                      @if($teacher->email && $teacher->name)
                        <small class="d-block text-body-secondary text-truncate">{{ $teacher->email }}</small>
                      @endif
                    </span>
                  </label>
                @endforeach
              </div>
            </div>
            <div class="form-text">Unselected teachers see generic reward cards.</div>

            @error('teacher_user_ids')
              <small class="text-danger d-block mt-2">{{ $message }}</small>
            @enderror
            @error('teacher_user_ids.*')
              <small class="text-danger d-block mt-2">{{ $message }}</small>
            @enderror

            <button type="submit" class="btn btn-primary mt-3">
              <i class="icon-base ti tabler-device-floppy icon-18px me-1"></i>
              Save Privacy
            </button>
          @endif
        </form>
      </div>
    </div>

    <div class="card mb-6 reward-queue-card">
      <div class="reward-queue-toolbar">
        <div>
          <h5 class="mb-1">{{ $student->first_name }} Reward System</h5>
          <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-label-warning">{{ $studentgifts->where('status', \App\Models\StudentGift::STATUS_PENDING)->count() }} pending</span>
            <span class="badge bg-label-primary">{{ $studentgifts->where('status', \App\Models\StudentGift::STATUS_WAITING)->count() }} waiting</span>
            <span class="badge bg-label-danger">{{ $studentgifts->where('status', \App\Models\StudentGift::STATUS_REACHED)->count() }} reached</span>
            <span class="badge bg-label-success">{{ $studentgifts->where('status', \App\Models\StudentGift::STATUS_REDEEMED)->count() }} redeemed</span>
          </div>
        </div>
        <form id="gift-interval-form" class="reward-step-control" data-no-drag>
          <div>
            <label for="giftIntervalInput" class="form-label mb-1">Waiting gift step</label>
            <input
              id="giftIntervalInput"
              type="number"
              min="1"
              max="10000"
              class="form-control"
              value="100"
            >
          </div>
          <button type="submit" class="btn btn-label-primary">
            Rebuild Gap
          </button>
        </form>
      </div>
      <div>
        <ul class="reward-queue-list" id="gift-sortable">
          @foreach($studentgifts as $studentgift)
            @php
              $isWaitingGift = $studentgift->status === \App\Models\StudentGift::STATUS_WAITING;
              $statusBadge = match($studentgift->status) {
                \App\Models\StudentGift::STATUS_PENDING => 'bg-label-warning',
                \App\Models\StudentGift::STATUS_REACHED => 'bg-label-danger',
                \App\Models\StudentGift::STATUS_REDEEMED => 'bg-label-success',
                default => 'bg-label-primary',
              };
            @endphp
            <li
              class="reward-queue-row {{ $isWaitingGift ? 'gift-sortable-waiting' : 'is-locked' }}"
              data-id="{{ $studentgift->id }}"
              data-status="{{ $studentgift->status }}"
            >
              @if($isWaitingGift)
                <span
                  class="gift-drag-handle"
                  title="Reorder waiting gift"
                  aria-label="Reorder waiting gift"
                  role="button"
                  tabindex="0"
                >
                  <span class="gift-grip-dots" aria-hidden="true"></span>
                </span>
              @else
                <span class="gift-lock-handle opacity-50" aria-hidden="true">
                  <i class="icon-base ti tabler-lock icon-18px"></i>
                </span>
              @endif

              <img
                src="{{ $studentgift->imageUrl() }}"
                alt="{{ $studentgift->gift_name ?: 'Gift' }}"
                class="rounded reward-gift-avatar"
              >

              <div class="reward-gift-title">
                <h6 class="mb-1 text-truncate">{{ $studentgift->gift_name }}</h6>
                <span class="badge {{ $statusBadge }}">{{ $studentgift->status === \App\Models\StudentGift::STATUS_REACHED ? 'reached' : $studentgift->status }}</span>
              </div>

              <div class="reward-point-pill text-body-secondary">
                {{ $studentgift->points_required }} pts
              </div>

              @if($isWaitingGift || $studentgift->status === \App\Models\StudentGift::STATUS_PENDING)
                <button
                  type="button"
                  class="btn btn-sm btn-label-primary"
                  onclick="Livewire.dispatch('open-gift-editor', { id: {{ $studentgift->id }} })"
                  data-no-drag
                >
                  {{ $isWaitingGift ? 'Edit' : 'Replace' }}
                </button>
              @else
                <span></span>
              @endif
            </li>
          @endforeach
        </ul>
      </div>
    </div>


    <!-- /Activity Timeline -->

  </div>
  <!--/ User Content -->
</div>

<!-- Modal -->
@include('_partials/_modals/modal-edit-user')


<!-- for fixed and flexiable payment -->
@include('_partials/_modals/modal-upgrade-plan')
<!-- /Modal -->





<script>
(function () {
  const list = document.getElementById('gift-sortable');
  const intervalForm = document.getElementById('gift-interval-form');
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  const getWaitingOrder = () =>
    Array.from(list.querySelectorAll('.gift-sortable-waiting[data-id]')).map(el => parseInt(el.dataset.id, 10));

  async function saveOrder() {
    const res = await fetch("{{ route('admin.student-gifts.reorder') }}", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        order: getWaitingOrder(),
        student_id: {{ $student->id }}
      })
    });
    if (!res.ok) {
      const data = await res.json().catch(() => ({}));
      console.error('Save failed', data);
      alert(data.message || 'Save failed');
      window.location.reload();
      return;
    }
    window.location.reload();
  }

  function initWhenReady(start = Date.now()) {
    if (!list) return;

    if (window.Sortable) {
      window.Sortable.create(list, {
        animation: 150,
        draggable: '.gift-sortable-waiting',
        handle: '.gift-drag-handle',
        filter: 'a, button, input, textarea, select, [data-no-drag]',
        ghostClass: 'gift-sortable-ghost',
        chosenClass: 'gift-sortable-chosen',
        onEnd: saveOrder
      });
    } else if (Date.now() - start < 5000) {
      setTimeout(() => initWhenReady(start), 50);
    } else {
      console.warn('Sortable did not load');
    }
  }

  if (intervalForm) {
    intervalForm.addEventListener('submit', async function (event) {
      event.preventDefault();
      const input = document.getElementById('giftIntervalInput');
      const interval = parseInt(input && input.value ? input.value : '0', 10);

      const res = await fetch("{{ route('admin.student-gifts.bulk-interval') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          interval,
          student_id: {{ $student->id }}
        })
      });

      if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        alert(data.message || 'Interval update failed');
        return;
      }

      window.location.reload();
    });
  }

  initWhenReady();
})();
</script>
@endsection
