@push('styles')
  @vite('resources/assets/vendor/libs/select2/select2.scss')
@endpush

@push('scripts')
  @vite('resources/assets/vendor/libs/select2/select2.js')
@endpush

<div class="card mb-6">
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <h5 class="mb-1">Subject Access</h5>
      <p class="text-body-secondary mb-0">Manage this student's class subjects, access, and assigned teacher.</p>
    </div>
    <span class="badge bg-label-primary">{{ $studentSubjects->where('status', 'active')->count() }} active</span>
  </div>

  <div class="list-group list-group-flush">
    @forelse ($studentSubjects as $studentSubject)
      @php
        $gradeLevelSubject = $studentSubject->gradeLevelSubject;
        $subject = $gradeLevelSubject?->subject;
        $isActive = $studentSubject->status === 'active';
        $subjectName = $subject?->title ?? ('Subject #'.($gradeLevelSubject?->subject_id ?? '-'));
        $teacherAssignment = $teacherAssignments[(int) $studentSubject->class_subject_id] ?? null;
        $assignedTeacherId = $teacherAssignment['teacher_id'] ?? null;
      @endphp
      <div class="list-group-item px-4 py-4" wire:key="student-subject-{{ $studentSubject->id }}">
        <div class="row g-3 align-items-center">
          <div class="col-12 col-xxl-3">
            <div class="fw-medium">{{ $subjectName }}</div>
            <div class="small text-body-secondary">Subject ID {{ $gradeLevelSubject?->subject_id ?? '-' }}</div>
            <div class="d-flex flex-wrap gap-1 mt-1">
              <span class="badge bg-label-secondary">{{ $gradeLevelSubject?->type ?? 'standard' }}</span>
              <span class="badge bg-label-{{ $gradeLevelSubject?->status === 'active' ? 'success' : 'secondary' }}">
                {{ $gradeLevelSubject?->status ?? 'unknown' }}
              </span>
            </div>
          </div>

          <div class="col-12 col-xxl-6">
            <label class="form-label small mb-1" for="student-subject-teacher-{{ $studentSubject->id }}">Teacher</label>
            <div class="position-relative" wire:ignore>
              <select
                id="student-subject-teacher-{{ $studentSubject->id }}"
                class="form-select tq-student-subject-teacher-select"
                data-student-subject-id="{{ $studentSubject->id }}"
                data-placeholder="Choose teacher"
              >
                <option value="">No teacher assigned</option>
                @foreach ($teachers as $teacher)
                  <option value="{{ $teacher->id }}" @selected((int) $assignedTeacherId === (int) $teacher->id)>
                    {{ $teacher->name }}{{ $teacher->email ? ' - '.$teacher->email : '' }}
                  </option>
                @endforeach
              </select>
            </div>
            @if ($teacherAssignment)
              <div class="small text-body-secondary mt-1">
                {{ ucfirst($teacherAssignment['status']) }} assignment
              </div>
            @elseif (! $studentSubject->class_subject_id)
              <div class="small text-warning mt-1">Class subject will be created on assignment.</div>
            @endif
            @error('teacher') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>

          <div class="col-12 col-xxl-3 d-flex flex-wrap justify-content-between justify-content-xxl-end align-items-center gap-2">
            <span class="badge bg-label-{{ $isActive ? 'success' : 'secondary' }}">
              {{ $isActive ? 'Active' : 'Inactive' }}
            </span>
            <button
              type="button"
              class="btn btn-sm {{ $isActive ? 'btn-label-secondary' : 'btn-primary' }}"
              wire:click="toggleSubject({{ $studentSubject->id }})"
              wire:loading.attr="disabled"
              wire:target="toggleSubject({{ $studentSubject->id }})"
            >
              {{ $isActive ? 'Deactivate' : 'Activate' }}
            </button>
          </div>
        </div>
      </div>
    @empty
      <div class="text-center text-body-secondary py-5">
        No subject rows exist for this student yet.
      </div>
    @endforelse
  </div>
</div>

@script
<script>
  const initStudentSubjectTeacherSelects = () => {
    if (!window.jQuery || !jQuery.fn.select2) return;

    jQuery('.tq-student-subject-teacher-select').each(function () {
      const $select = jQuery(this);
      const studentSubjectId = Number($select.data('student-subject-id'));

      if ($select.data('select2')) {
        $select.select2('destroy');
      }

      $select.select2({
        width: '100%',
        placeholder: $select.data('placeholder') || 'Choose teacher',
        dropdownParent: $select.parent(),
        allowClear: true,
        minimumResultsForSearch: 0
      });

      $select.off('change.tq-student-subject-teacher').on('change.tq-student-subject-teacher', function () {
        const teacherId = this.value === '' ? null : Number(this.value);
        $wire.assignTeacherToSubject(studentSubjectId, teacherId);
      });
    });
  };

  initStudentSubjectTeacherSelects();

  Livewire.hook('morphed', initStudentSubjectTeacherSelects);
  $wire.on('student-subject-teacher-selects-refresh', initStudentSubjectTeacherSelects);
</script>
@endscript
