<div class="card mb-6">
  <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <h5 class="mb-1">Subject Access</h5>
      <p class="text-body-secondary mb-0">Manage which configured grade-level subjects are active for this student.</p>
    </div>
    <span class="badge bg-label-primary">{{ $studentSubjects->where('status', 'active')->count() }} active</span>
  </div>

  <div class="table-responsive">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>Subject</th>
          <th>Grade Setup</th>
          <th>Student Access</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($studentSubjects as $studentSubject)
          @php
            $gradeLevelSubject = $studentSubject->gradeLevelSubject;
            $subject = $gradeLevelSubject?->subject;
            $isActive = $studentSubject->status === 'active';
            $subjectName = $subject?->title ?? ('Subject #'.($gradeLevelSubject?->subject_id ?? '-'));
          @endphp
          <tr wire:key="student-subject-{{ $studentSubject->id }}">
            <td>
              <div class="fw-medium">{{ $subjectName }}</div>
              <div class="small text-body-secondary">Subject ID {{ $gradeLevelSubject?->subject_id ?? '-' }}</div>
            </td>
            <td>
              <span class="badge bg-label-secondary">{{ $gradeLevelSubject?->type ?? 'standard' }}</span>
              <span class="badge bg-label-{{ $gradeLevelSubject?->status === 'active' ? 'success' : 'secondary' }}">
                {{ $gradeLevelSubject?->status ?? 'unknown' }}
              </span>
            </td>
            <td>
              <span class="badge bg-label-{{ $isActive ? 'success' : 'secondary' }}">
                {{ $isActive ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="text-end">
              <button
                type="button"
                class="btn btn-sm {{ $isActive ? 'btn-label-secondary' : 'btn-primary' }}"
                wire:click="toggleSubject({{ $studentSubject->id }})"
                wire:loading.attr="disabled"
                wire:target="toggleSubject({{ $studentSubject->id }})"
              >
                {{ $isActive ? 'Deactivate' : 'Activate' }}
              </button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-center text-body-secondary py-5">
              No subject rows exist for this student yet.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
