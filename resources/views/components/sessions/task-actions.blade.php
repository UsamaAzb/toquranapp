@php
  $variant = $variant ?? 'top';
  $isPhone = $variant === 'phone';
  $taskId = (int) ($t['id'] ?? 0);
  $defaultPoints = (int) ($t['default_points'] ?? 0);
  $maxPoints = (int) ($t['max'] ?? $t['default_points'] ?? 0);
  $isStudent = auth()->user()?->hasRole('student');
  $isParent = auth()->user()?->hasRole('parent');
  $showStudentReadyActions = !$isCompleted && !$isInReview && $isStudent;
  $showStudentPinAction = !$isCompleted && $isStudent && (!$isPhone || !$isInReview);
  $showParentCompleteAction = !$isCompleted && !$isInReview && $isParent;
  $showParentApproveAction = !$isPhone && !$isCompleted && $isInReview && $isParent;
  $putToReviewAction = "putToReview({$taskId})";
  $pinAction = "openCompleteModal({$taskId}, {$defaultPoints}, {$maxPoints})";
@endphp

@if($showStudentReadyActions)
  <button
    type="button"
    class="btn btn-sm btn-primary session-task-complete-btn"
    wire:click="{{ $putToReviewAction }}"
    wire:loading.attr="disabled"
    wire:target="{{ $putToReviewAction }}">
    <span wire:loading.remove wire:target="{{ $putToReviewAction }}">Complete</span>
    <span wire:loading wire:target="{{ $putToReviewAction }}">Sending...</span>
  </button>
@endif

@if($showStudentPinAction)
  <button
    type="button"
    wire:click="{{ $pinAction }}"
    wire:loading.attr="disabled"
    wire:target="{{ $pinAction }}"
    class="btn btn-sm btn-icon btn-label-primary session-task-pin-btn"
    title="Complete with PIN"
    aria-label="Complete with PIN">
    <i class="ti tabler-key"></i>
  </button>
@endif

@if($showParentCompleteAction)
  <button
    type="button"
    wire:click="openParentTaskPointsModal({{ $taskId }}, 'complete')"
    wire:loading.attr="disabled"
    wire:target="openParentTaskPointsModal({{ $taskId }}, 'complete')"
    class="btn btn-sm btn-primary session-task-complete-btn">
    Complete
  </button>
@endif

@if($showParentApproveAction)
  <button
    type="button"
    wire:click="openParentTaskPointsModal({{ $taskId }}, 'approve')"
    wire:loading.attr="disabled"
    wire:target="openParentTaskPointsModal({{ $taskId }}, 'approve')"
    class="btn btn-sm btn-primary session-task-complete-btn">
    Approve
  </button>
@endif
