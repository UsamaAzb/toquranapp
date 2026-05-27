@extends('layouts/layoutMaster')

@section('title', 'Class Sessions')
@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sortablejs/sortable.js'])
@endsection




@section('vendor-style')
  @vite(['resources/assets/vendor/libs/dropzone/dropzone.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite(['resources/assets/vendor/libs/dropzone/dropzone.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
  @vite(['resources/assets/js/forms-file-upload.js'])
@endsection








@section('content')
@push('styles')
<style>
  .w14-teacher-quick-actions {
    max-width: 100%;
  }

  .w14-teacher-student-action-row {
    max-width: 100%;
    min-width: 0;
  }

  .w14-teacher-student-action-main {
    flex: 1 1 12rem;
    min-width: 0;
  }

  .w14-teacher-student-action-buttons {
    max-width: 100%;
    min-width: 0;
  }

  .w14-teacher-session-workspace {
    padding: 3rem;
  }

  .w14-teacher-points-block {
    padding: 20px;
  }

  @media (max-width: 575.98px) {
    .w14-teacher-session-workspace {
      padding: 1.75rem 0.5rem 2.5rem;
    }

    .w14-teacher-session-workspace .sessions-board-shell {
      padding-inline: 0 !important;
    }

    .w14-teacher-points-block {
      padding-inline: 0.5rem;
    }
  }

  @media (max-width: 430px) {
    .w14-teacher-quick-actions {
      margin-inline: 0.5rem !important;
    }

    .w14-teacher-student-action-row {
      display: grid !important;
      grid-template-columns: minmax(0, 1fr);
    }

    .w14-teacher-student-action-buttons {
      display: grid !important;
      grid-template-columns: minmax(0, 1fr);
      justify-content: stretch !important;
      width: 100%;
    }

    .w14-teacher-student-action-buttons .btn {
      justify-content: center;
      max-width: 100%;
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      width: 100%;
    }
  }
</style>
@endpush

<livewire:teacher.behavior-modal />

<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 2000">
  <div id="livewireToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-header">
      <strong class="me-auto" id="toastTitle">Notification</strong>
      <small>now</small>
      <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body" id="toastBody">Hello!</div>
  </div>
</div>





<livewire:teacher.session-agreement-reword-header  :student-id="$student_id" :teacher-subject-id="$teachersubjectid"/>

@if($teacherSubjectClass)
  <div class="card mt-4 mx-5 w14-teacher-quick-actions">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <h5 class="mb-1">Student quick actions</h5>
        <p class="mb-0 text-muted">Review tasks or add behavior points from the active subject context.</p>
      </div>
      <span class="badge bg-label-primary">{{ ($teacher_students ?? collect())->count() }} students</span>
    </div>
    <div class="card-body">
      <div class="d-flex flex-column gap-2">
    @foreach(($teacher_students ?? collect()) as $teacherStudent)
      @php
        $reviewCount = (int) (($reviewCounts ?? collect())->get($teacherStudent->id, 0));
      @endphp
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 border rounded p-2 w14-teacher-student-action-row" wire:key="teacher-student-actions-{{ $teacherStudent->id }}">
        <div class="w14-teacher-student-action-main">
          <div class="fw-semibold text-truncate" title="{{ $teacherStudent->display_name }}">{{ $teacherStudent->display_name }}</div>
          <div class="small text-muted">{{ $reviewCount }} task{{ $reviewCount === 1 ? '' : 's' }} in review</div>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-end w14-teacher-student-action-buttons">
          <a
            href="{{ route('teacher.task-approvals', ['student' => $teacherStudent->id, 'subject' => $teacherSubjectClass->subject_id]) }}"
            class="btn btn-sm {{ $reviewCount > 0 ? 'btn-warning' : 'btn-label-secondary' }}">
            <i class="ti tabler-checks me-1"></i> Review tasks
          </a>
          @livewire('teacher.add-behavior-button', [
            'studentId' => $teacherStudent->id,
            'teacherSubjectClassesId' => $teacherSubjectClass->id,
            'buttonClass' => 'btn btn-sm btn-label-primary',
            'iconClass' => 'ti tabler-plus',
            'label' => 'Add behavior',
            'showLabel' => true,
          ], key('teacher-add-behavior-'.$teacherSubjectClass->id.'-'.$teacherStudent->id))
        </div>
      </div>
    @endforeach
      </div>
    </div>
  </div>
@endif


<div class="mb-5 col-12 mt-5 w14-teacher-points-block">
    <livewire:ui.points-progress
    :student-id="$student_id"
    :pending-gift-id="$pendingGift->id ?? null"
    :last-reached-gift-id="$lastReached->id ?? null"
    :allow-reached-click="false"
    :circle-view="true"
    label="Reward Points"
    />
    </div>


<div class="w14-teacher-session-workspace">
<livewire:teacher.add-session :teacherSubjectClassId="$teachersubjectid" />

<livewire:teacher.sessions-board :teacherSubjectClassId="$teachersubjectid" />
<livewire:teacher.show-session-task />
<livewire:teacher.attachment-study-viewer :teacherSubjectClassId="$teachersubjectid" />
</div>
<script>
  window.addEventListener('livewire:init', () => {
    Livewire.on('toast', ({ type, message }) => {
      const el = document.getElementById('livewireToast');
      const title = document.getElementById('toastTitle');
      const body = document.getElementById('toastBody');

      title.textContent = (type === 'success') ? 'Success' : 'Notice';
      body.textContent = message ?? '';

      const toast = new bootstrap.Toast(el);
      toast.show();
    });
  });
</script>

@endsection
