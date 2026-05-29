@push('styles')
  @vite('resources/assets/vendor/libs/select2/select2.scss')
@endpush

@push('scripts')
  @vite('resources/assets/vendor/libs/select2/select2.js')
@endpush

<div class="row g-6">
  <div class="col-12">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-6">
      <div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <h4 class="mb-0">Teacher Class Assignments</h4>
          <details class="intake-info intake-info--inline">
            <summary class="intake-info__trigger" aria-label="Open teacher assignments info">
              <i class="icon-base ti tabler-info-circle icon-18px"></i>
            </summary>
            <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
              Launch control for assigning active teachers to existing To Quran class subjects.
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

  <div class="col-12 col-sm-6 col-xl-4">
    <div class="card card-border-shadow-primary h-100">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="icon-base ti tabler-check icon-28px"></i>
            </span>
          </div>
          <div>
            <h4 class="mb-0">{{ number_format($stats['current']) }}</h4>
            <p class="mb-0">Current Assignments</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-4">
    <div class="card card-border-shadow-warning h-100">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="icon-base ti tabler-user-off icon-28px"></i>
            </span>
          </div>
          <div>
            <h4 class="mb-0">{{ number_format($stats['inactive']) }}</h4>
            <p class="mb-0">Inactive Assignments</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-sm-6 col-xl-4">
    <div class="card card-border-shadow-info h-100">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-info">
              <i class="icon-base ti tabler-users icon-28px"></i>
            </span>
          </div>
          <div>
            <h4 class="mb-0">{{ number_format($stats['teachers']) }}</h4>
            <p class="mb-0">Assigned Teachers</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Assign Teacher</h5>
      </div>
      <div class="card-body pt-4">
        <form wire:submit="assignTeacher" class="row g-4">
          <div class="col-12">
            <label class="form-label" for="assignment-teacher">Teacher</label>
            <div class="position-relative" wire:ignore>
              <select id="assignment-teacher" class="form-select tq-assignment-select @error('teacherId') is-invalid @enderror" data-property="teacherId" data-placeholder="Choose teacher">
                <option value="">Choose teacher</option>
                @foreach ($teachers as $teacher)
                  <option value="{{ $teacher->id }}">{{ $teacher->name }} - {{ $teacher->email }}</option>
                @endforeach
              </select>
            </div>
            @error('teacherId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>

          <div class="col-12">
            <label class="form-label" for="assignment-class">Class</label>
            <div class="position-relative" wire:ignore>
              <select id="assignment-class" class="form-select tq-assignment-select @error('classId') is-invalid @enderror" data-property="classId" data-placeholder="Choose class">
                <option value="">Choose class</option>
                @foreach ($classes as $class)
                  <option value="{{ $class->id }}">{{ $class->title }}{{ $class->grade_name ? ' - '.$class->grade_name : '' }}</option>
                @endforeach
              </select>
            </div>
            @error('classId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>

          <div class="col-12">
            <label class="form-label" for="assignment-subject">Class Subject</label>
            <div class="position-relative" wire:ignore>
              <select id="assignment-subject" class="form-select tq-assignment-select @error('subjectId') is-invalid @enderror" data-property="subjectId" data-placeholder="Choose class subject">
                <option value="">Choose class subject</option>
                @foreach ($subjects as $subject)
                  <option value="{{ $subject->id }}">{{ $subject->title }}</option>
                @endforeach
              </select>
            </div>
            @error('subjectId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>

          <div class="col-12">
            <button type="submit" class="btn btn-primary">
              Save Assignment
            </button>
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
            <h5 class="card-title mb-0">Assigned Teachers</h5>
          </div>
          <div class="col-12 col-lg-4">
            <input type="search" class="form-control" placeholder="Search assignments" wire:model.live.debounce.300ms="search">
          </div>
          <div class="col-12 col-lg-3">
            <select class="form-select" wire:model.live="statusFilter">
              <option value="current">Current</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="archived">Archived</option>
              <option value="all">All Statuses</option>
            </select>
          </div>
        </div>
      </div>

      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>Teacher</th>
              <th>Class</th>
              <th>Class Subject</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($assignments as $assignment)
              <tr wire:key="teacher-class-assignment-{{ $assignment->id }}">
                <td>
                  <div class="fw-semibold">{{ $assignment->teacher_name ?: $assignment->teacher?->name }}</div>
                  @if ($assignment->teacher?->email)
                    <small class="text-body-secondary">{{ $assignment->teacher->email }}</small>
                  @endif
                </td>
                <td>
                  <div>{{ $assignment->class_name ?: $assignment->class?->title }}</div>
                  @if ($assignment->grade_name)
                    <small class="text-body-secondary">{{ $assignment->grade_name }}</small>
                  @endif
                </td>
                <td>
                  <span class="badge bg-label-primary">{{ $assignment->subject_name ?: $assignment->subject?->title }}</span>
                </td>
                <td>
                  <span class="badge {{ in_array($assignment->status, ['current', 'active'], true) ? 'bg-label-success' : 'bg-label-secondary' }}">
                    {{ ucfirst($assignment->status) }}
                  </span>
                </td>
                <td class="text-end">
                  @if (in_array($assignment->status, ['current', 'active'], true))
                    <button type="button" class="btn btn-sm btn-label-warning" wire:click="deactivateAssignment({{ $assignment->id }})">
                      Deactivate
                    </button>
                  @else
                    <button type="button" class="btn btn-sm btn-label-success" wire:click="reactivateAssignment({{ $assignment->id }})">
                      Reactivate
                    </button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-body-secondary py-5">No teacher assignments match the current filters.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="card-footer">
        {{ $assignments->links() }}
      </div>
    </div>
  </div>
</div>

@script
<script>
  const initTeacherAssignmentSelects = () => {
    if (!window.jQuery || !jQuery.fn.select2) return;

    jQuery('.tq-assignment-select').each(function () {
      const $select = jQuery(this);
      const property = $select.data('property');

      if ($select.data('select2')) {
        $select.select2('destroy');
      }

      $select.select2({
        width: '100%',
        placeholder: $select.data('placeholder') || 'Choose',
        dropdownParent: $select.parent(),
        allowClear: true,
        minimumResultsForSearch: 0
      });

      $select.off('change.tq-assignment').on('change.tq-assignment', function () {
        const value = this.value === '' ? null : Number(this.value);
        $wire.set(property, value);
      });
    });
  };

  initTeacherAssignmentSelects();

  Livewire.hook('morphed', initTeacherAssignmentSelects);

  $wire.on('teacher-assignment-form-reset', () => {
    jQuery('.tq-assignment-select').val(null).trigger('change.select2');
  });
</script>
@endscript
