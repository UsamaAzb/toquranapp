@push('styles')
  @once
    <link rel="preload" as="image" href="{{ $bgUrl ?: asset('images/journey/background34.webp') }}">
  @endonce
@endpush

<div wire:poll.10s.visible="refreshTaskState">


<style>

    /*.btn-primary{*/
    /*  background-color: #2092ec !important;  */
    /*  border-color: #2092ec!important;*/
    /*}*/
    /* .btn-primary:hover{*/
    /*  background-color: #2092ec !important;  */
    /*  border-color: #2092ec!important;*/
    /*}*/
    @media (max-width: 500px) {

        .weekly-progress {
            padding: 8px !important;
            top: -103px !important;
        right: 0px !important;
        }
    }
</style>








<div class="background-container d-flex  align-items-center">
    @php
      $journeyProgressPct = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;
    @endphp

    <div class="weekly-progress card shadow-sm d-flex align-items-center gap-2 mt-1 ">
        <small class="fw-medium ">
          {{ $completedCount }}/{{ $totalCount }} islands done
        </small>

        <div class="progress island-progress">
          <div class="progress-bar bg-success inside-island-progress"
               role="progressbar"
               aria-valuenow="{{ $journeyProgressPct }}"
               aria-valuemin="0"
               aria-valuemax="100"
               style="width: {{ $journeyProgressPct }}%">
          </div>
        </div>
      </div>

    <!-- <div class="weekly-progress card shadow-sm">-->
    <!--    <div class="card-body d-flex align-items-center">-->
    <!--        <i class="bi bi-calendar-week me-2"></i>-->
    <!--        <div>-->
    <!--            <small>{{ $completedCount }}/{{ $totalCount }}  islands done</small>-->
    <!--        </div>-->
    <!--        <div class="progress island-progress">-->
    <!--      <div class="progress-bar bg-success inside-island-progress"-->
    <!--           role="progressbar"-->
    <!--           style="width: {{ ($completedCount / $totalCount) * 100 }}%">-->
    <!--      </div>-->
    <!--    </div>-->
    <!--    </div>-->
    <!--</div>-->


  {{--<div style="position: absolute;top: 20px;right: auto;width: 90%;">
  <livewire:ui.points-progress
    :student-id="$studentId"
    :pending-gift-id="$pendingGiftId"
    :last-reached-gift-id="$lastReachedGiftId"
    :allow-reached-click="true"
    :circle-view="false"
    label="Reward Points"
/>
</div>--}}


    <!--<div class="topic-track d-flex flex-lg-row flex-column align-items-center ">-->




        <div
  class="topic-track d-flex flex-lg-row flex-column align-items-center justify-content-center"
  x-data="{ hasScroll: false }"
  x-init="
     $nextTick(() => {
        hasScroll = ($el.scrollWidth > $el.clientWidth);
     });

     window.addEventListener('resize', () => {
        hasScroll = ($el.scrollWidth > $el.clientWidth);
     });
  "
  :class="hasScroll ? 'justify-content-start' : 'justify-content-center'"
>






        @foreach ($session['tasks'] as $index => $topic)
            @php
              $topicStatus = $topic['pivot']['status'] ?? null;
              $topicCompleted = $topicStatus === 'completed';
              $topicPoints = $topicCompleted
                ? ($topic['pivot']['student_points'] ?? null)
                : ($topic['points'] ?? null);
            @endphp
            <div class="topic-block d-flex align-items-center position-relative">

          {{--    @if( (($topic['pivot']['flag'] ?? null) === 'up-next') && (($topic['pivot']['status'] ?? null) !== 'completed') )
      <div class="up-next">UP NEXT!</div>
      <div class="up-next-line"></div>
    @endif --}}

    <button type="button"
          class="topic-circle text-center @if($topicCompleted) topic-circle-completed @endif"
          wire:key="journey-topic-{{ $topic['id'] }}"
          wire:click="openTask({{ $topic['id'] }})">



           @if($topicCompleted)
            <span class="journey-topic-status-mark" aria-label="{{ __('Completed') }}">
              <i class="ti tabler-check"></i>
            </span>
           @endif
           <span class="journey-topic-title">
             {{ \Illuminate\Support\Str::limit(ucfirst($topic['title']), 32, '...') }}
           </span>
@if($topicPoints !== null)
<span class="{{ $topicCompleted ? 'text-success' : 'text-muted' }}">{{ $topicPoints }} Pts </span>
@endif



                </button>
                @if (!$loop->last)
                        <div class="topic-line topic-line-vertical d-lg-none"></div>
                    @endif

                    @if (!$loop->last)
                        <div class="topic-line topic-line-horizontal d-none d-lg-block"></div>
                    @endif
            </div>
        @endforeach
    </div>

</div>













{{--<div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true" wire:ignore.self x-data="{ show: @entangle('showTaskModal') }" x-show="show" x-init="$watch('show', value => { if (value) { $('#taskModal').modal('show') } else { $('#taskModal').modal('hide') } })"> --}}


<div class="modal"
     id="taskModal"
     tabindex="-1"
     aria-hidden="true"
     wire:ignore.self>
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content journey-task-modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          {{ $currentTask['title'] ?? 'Task' }}
        </h5>
         <button type="button"
                class="btn-close"
                aria-label="Close"
                onclick="window.w14CloseJourneyModal?.('taskModal')">
        </button>
      </div>

      <div class="modal-body p-4 journey-task-modal-body">
        @php
          $taskDescription = trim($currentTask['description'] ?? '');
        @endphp

        <div class="journey-task-brief">
          <div class="journey-task-brief-copy">
            <div class="journey-task-brief-heading">
              <span class="journey-task-brief-icon">
                <i class="ti tabler-map-pin"></i>
              </span>
              <div class="journey-task-section-label">{{ __('Task brief') }}</div>
            </div>
            @if($taskDescription !== '')
              <p class="journey-task-description mb-0">{!! nl2br(e($taskDescription)) !!}</p>
            @else
              <p class="mb-0 text-muted">{{ __('No extra notes for this task.') }}</p>
            @endif
          </div>
        </div>

     <div class="journey-task-attachments">
          <!--<h6>Attachments</h6>-->

          @if(!empty($currentAttachments))
  <div class="journey-attachment-list">



  @foreach($currentAttachments as $f)
       @php
        $path = $f['path'] ?? '';
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $name = $f['name'] ?? ($path ? basename($path) : 'Attachment');

        $imageExts = ['jpg','jpeg','png','gif','webp','svg'];
        $isImage   = in_array($ext, $imageExts, true);
        $attachmentIcon = match (true) {
          ($f['type'] ?? null) === 'youtube' => 'ti tabler-brand-youtube',
          ($f['type'] ?? null) === 'vocabulary_game' => 'ti tabler-balloon',
          ($f['type'] ?? null) === 'link' => 'ti tabler-link',
          $isImage => 'ti tabler-photo',
          $ext === 'pdf' => 'ti tabler-file-type-pdf',
          in_array($ext, ['doc', 'docx'], true) => 'ti tabler-file-type-doc',
          in_array($ext, ['xls', 'xlsx', 'csv'], true) => 'ti tabler-file-spreadsheet',
          in_array($ext, ['ppt', 'pptx'], true) => 'ti tabler-file-type-ppt',
          in_array($ext, ['mp4', 'mov', 'webm'], true) => 'ti tabler-video',
          default => 'ti tabler-file-description',
        };
        $semanticToneClass = match (($f['type'] ?? null)) {
          'youtube' => 'journey-attachment-semantic-youtube',
          'vocabulary_game' => 'journey-attachment-semantic-link',
          'link' => 'journey-attachment-semantic-link',
          default => '',
        };
        $thumbClass = $semanticToneClass !== ''
          ? $semanticToneClass
          : 'journey-attachment-thumb-' . ($loop->index % 6);
        $attachmentKey = 'journey-task-'
          .(int) $currentTaskId
          .'-attachment-'
          .(string) ($f['id'] ?? $loop->index)
          .'-'
          .md5(implode('|', [
            (string) ($f['path'] ?? ''),
            (string) ($f['url'] ?? ''),
            (string) $name,
          ]));
      @endphp

      <button
        type="button"
        wire:key="{{ $attachmentKey }}"
        class="journey-attachment-chip journey-attachment-chip-button text-decoration-none"
        title="{{ $name }}"
        wire:click="openAttachmentStudyViewer({{ (int) $currentTaskId }}, {{ (int) ($f['id'] ?? 0) }})"
        onclick="event.stopPropagation();">
        <span class="journey-attachment-icon {{ $thumbClass }}" aria-hidden="true">
          <i class="{{ $attachmentIcon }}"></i>
        </span>
        <span class="journey-attachment-name">{{ $name }}</span>
      </button>


    @endforeach
  </div>
          @else
            <p class="journey-task-empty-attachments mb-0">{{ __('No attachments for this mission.') }}</p>
          @endif
        </div>


      </div>

      <div class="modal-footer journey-task-actions">
        @if($currentTaskId)
        @php
        $taskStatus = $currentTask['pivot']['status'] ?? null;
        $approvalSource = $currentTask['pivot']['approval_source'] ?? null;
        $isCompleted = $taskStatus === 'completed';
        $isInReview = in_array($taskStatus, ['in_review', 'pending'], true);
        $sourceDetail = match ($approvalSource) {
          'trusted_child_auto' => __('Approved by trusted child auto'),
          'parent_direct_completion', 'parent_approval' => __('Approved by Parent'),
          'teacher_approval' => __('Approved by Teacher'),
          'student_pin' => __('Approved by PIN'),
          default => null,
        };
        $sourceBorderClass = match ($approvalSource) {
          'student_pin' => 'border-primary',
          'parent_direct_completion', 'parent_approval' => 'border-info',
          'teacher_approval' => 'border-success',
          'trusted_child_auto' => 'border-warning',
          default => 'border-success',
        };
        @endphp

        @if(!empty($currentAttachments))
        <button
            type="button"
            class="btn btn-primary waves-effect journey-start-island-btn"
            wire:click="openAttachmentStudyViewer({{ (int) $currentTaskId }})"
            onclick="event.stopPropagation();" >
            <span class="journey-start-island-icon" aria-hidden="true"></span>
            <span>{{ __('Start island') }}</span>
        </button>
        @endif

            @if( $isCompleted )
        @if($sourceDetail)
          <div class="dropdown">
            <button
              type="button"
              class="badge bg-label-success border border-2 {{ $sourceBorderClass }} p-2 d-inline-flex align-items-center"
              data-bs-toggle="dropdown"
              aria-expanded="false"
              aria-label="{{ $sourceDetail }}"
              onclick="event.stopPropagation();">
              <i class="ti tabler-check me-1"></i>{{ __('Completed') }}
            </button>
            <div class="dropdown-menu dropdown-menu-end p-2 shadow-sm">
              <span class="small text-muted text-nowrap">{{ $sourceDetail }}</span>
            </div>
          </div>
        @else
          <span class="badge bg-label-success border border-2 {{ $sourceBorderClass }} p-2">
            <i class="ti tabler-check me-1"></i>{{ __('Completed') }}
          </span>
        @endif

@endif
        @if($isInReview)
        <span class="journey-task-state-pill journey-task-state-pill-review">
          <i class="ti tabler-hourglass me-1"></i> {{ __('In review') }}
        </span>
        @endif
        @if(!$isCompleted && !$isInReview && auth()->user()?->hasRole('student'))
        <button
            type="button"
            class="btn btn-primary waves-effect journey-task-complete-btn"
            wire:click="putToReview({{ $currentTaskId }})"
            onclick="event.stopPropagation();" >
            {{ __('Complete') }}
        </button>
         @endif
        @if(!$isCompleted && !$isInReview && auth()->user()?->hasRole('student'))
        <button
            type="button"
            class="btn btn-icon btn-label-primary waves-effect journey-task-pin-btn"
            wire:click="openCompleteModal({{ $currentTaskId }}, {{ (int) ($currentTask['points'] ?? 0) }}, {{ (int) ($currentTask['max'] ?? $currentTask['points'] ?? 0) }})"
            title="{{ __('Complete with PIN') }}"
            aria-label="{{ __('Complete with PIN') }}"
            onclick="event.stopPropagation();" >
            <i class="ti tabler-key"></i>
        </button>
         @endif
        @if(!$isCompleted && !$isInReview && auth()->user()?->hasRole('parent'))
        <button
            type="button"
            class="btn btn-primary waves-effect journey-task-complete-btn"
            wire:click="openParentDirectCompleteModal({{ $currentTaskId }})"
            onclick="event.stopPropagation();" >
            {{ __('Complete') }}
        </button>
         @endif
        @if(!$isCompleted && $isInReview && auth()->user()?->hasRole('parent'))
        <button
            type="button"
            class="btn btn-primary waves-effect journey-task-complete-btn"
            wire:click="openParentReviewApprovalModal({{ $currentTaskId }})"
            onclick="event.stopPropagation();" >
            {{ __('Approve') }}
        </button>
         @endif




        @endif

      </div>

    </div>
  </div>
</div>


<livewire:student.attachment-study-viewer
  :student-id="$studentId"
  surface="journey"
  wire:key="student-journey-attachment-study-viewer-{{ $sessionId }}-{{ $studentId }}" />



<!-- parent direct completion modal -->

<div>
  <div class="modal"
       id="parentDirectCompleteModal"
       tabindex="-1"
       role="dialog"
       wire:ignore.self>
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button"
                  class="btn-close"
                  aria-label="Close"
                  onclick="window.w14CloseJourneyModal?.('parentDirectCompleteModal')">
          </button>
        </div>
        <form wire:submit.prevent="confirmParentDirectCompletion" autocomplete="off">
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Task Points</label>
              <input type="number"
                     class="form-control @error('parentDirectPoints') is-invalid @enderror"
                     wire:model.live="parentDirectPoints"
                     min="0"
                     @if($parentDirectMaxPoints) max="{{ $parentDirectMaxPoints }}" @endif>
              @error('parentDirectPoints')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror

              @if($parentDirectMaxPoints)
                <small class="text-primary">Max points for this task: {{ $parentDirectMaxPoints }}</small>
              @endif
            </div>
          </div>
          <div class="modal-footer d-flex justify-content-end">
            <button type="button" class="btn btn-label-secondary" onclick="window.w14CloseJourneyModal?.('parentDirectCompleteModal')">Cancel</button>
            <button type="submit" class="btn btn-primary">OK</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- pin modal -->

<div>

  <div class="modal"
  id="completePinModal"
     tabindex="-1"
     role="dialog"
     wire:ignore.self >


    <div class="modal-dialog modal-sm  modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
   <button type="button"
                  class="btn-close"
                  aria-label="Close"
                  onclick="window.w14CloseJourneyModal?.('completePinModal')">
          </button>
        </div>
        <form wire:submit.prevent="confirmTaskCompletionWithPin" autocomplete="off">

          <div class="modal-body">



              <div class="mb-3">
  <label class="form-label">Task Points</label>
  <input type="number"
         class="form-control @error('currentTaskDefaultPoint') is-invalid @enderror"
         value="{{ $currentTaskDefaultPoint ?? 0 }}"
         min="0"
         @if($currentTaskMaxPoint) max="{{ $currentTaskMaxPoint }}" @endif
         readonly
  >
  @error('currentTaskDefaultPoint')
    <div class="invalid-feedback">{{ $message }}</div>
  @enderror

  @if($currentTaskMaxPoint)
    <small class="text-primary">
      Max points for this task: {{ $currentTaskMaxPoint }}
    </small>
  @endif
</div>




            <div class="mb-3">
              <label class="form-label">PIN</label>



                     <input
                         id="pinField"
                         type="text"
                         class="form-control"
                         wire:model.live="pinInput"
                         maxlength="4"
                         autocomplete="off"
    inputmode="numeric"
    pattern="\d*"
                         @keydown.enter.prevent
                      style="-webkit-text-security: disc; text-security: disc;" >


              @error('pinInput') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            @if($pinErrorMessage ?? false)
              <div class="alert alert-danger py-2">{{ $pinErrorMessage }}</div>
            @endif
          </div>

    {{--      <div class="modal-footer">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
              <span wire:loading.remove>Confirm</span>
              <span wire:loading>Processing...</span>
            </button>
          </div>--}}


        </form>
      </div>
    </div>
  </div>
</div>

{{--<div id="confetti-overlay" style="position:fixed; left:0; top:0; width:100vw; height:100vh; pointer-events:none; z-index:2000;">
  <canvas id="confetti-canvas" style="width:100vw; height:100vh; display:block;"></canvas>
</div>--}}




{{--<div x-data="confettiPlayer()" x-init="init()" class="position-fixed top-0 start-0 w-100 h-100 pointer-events-none" style="z-index: 1055;">
  <canvas x-ref="canvas" class="w-100 h-100"></canvas>
</div> --}}






<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">-->
<style>


.main-bar {
    height: auto;
    background-color: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 94%, var(--bs-primary));
    display: flex;
    align-items: center;
    padding: 12px 15px;
}

.island-progress {
    height: 7px;
   width: clamp(80px, 18vw, 140px); height: 6px;
}

.inside-island-progress {
    height: 7px;
}
.background-container {
    --w14-journey-panel-bg: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 94%, white 6%);
    --w14-journey-panel-border: rgba(var(--bs-primary-rgb), 0.16);
    --w14-journey-text: var(--bs-heading-color);
    --w14-journey-muted: var(--bs-secondary-color);
    --w14-journey-island-surface: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 97%, white 3%);
    --w14-journey-island-surface-alt: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 92%, var(--bs-primary));
    --w14-journey-line: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 86%, var(--bs-primary));
    --w14-journey-shadow: 0 6px 16px rgba(0, 0, 0, 0.14);
}

[data-bs-theme="dark"] .background-container {
    --w14-journey-panel-bg: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 88%, white 12%);
    --w14-journey-panel-border: rgba(var(--bs-primary-rgb), 0.24);
    --w14-journey-muted: color-mix(in sRGB, var(--bs-heading-color) 74%, var(--bs-primary));
    --w14-journey-island-surface: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 78%, white 22%);
    --w14-journey-island-surface-alt: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 70%, var(--bs-primary));
    --w14-journey-line: rgba(var(--bs-primary-rgb), 0.34);
    --w14-journey-shadow: 0 10px 28px rgba(0, 0, 0, 0.3);
}

.journey-task-modal-content {
    overflow: hidden;
    box-shadow: 0 0.35rem 1.25rem rgba(0, 0, 0, 0.16);
}

[data-bs-theme="dark"] .journey-task-modal-content {
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.34);
}

.journey-task-modal-body {
    display: grid;
    gap: 0.9rem;
    min-width: 0;
    overflow-x: hidden;
    overflow-y: auto;
}

.journey-task-brief {
    padding: 1rem;
    border: 1px solid rgba(var(--bs-primary-rgb), 0.16);
    border-radius: 0.85rem;
    background:
        radial-gradient(circle at top right, rgba(var(--bs-primary-rgb), 0.12), transparent 36%),
        color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 94%, var(--bs-primary));
    color: var(--bs-body-color);
    min-width: 0;
    overflow: hidden;
}

.journey-task-brief-heading {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    margin-bottom: 0.5rem;
}

.journey-task-brief-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    inline-size: 1.65rem;
    block-size: 1.65rem;
    border-radius: 999px;
    color: var(--bs-primary);
    background: rgba(var(--bs-primary-rgb), 0.12);
    font-size: 0.9rem;
}

.journey-task-brief-copy {
    min-width: 0;
    max-width: 100%;
    overflow-wrap: anywhere;
    word-break: break-word;
}

.journey-task-description {
    max-width: 100%;
    overflow-wrap: anywhere;
    word-break: break-word;
    line-height: 1.45;
}

.journey-task-section-label {
    margin-bottom: 0.25rem;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0;
    color: var(--bs-primary);
    text-transform: uppercase;
}

.journey-task-attachments {
    min-width: 0;
    padding-top: 0.1rem;
}

.journey-task-empty-attachments {
    color: var(--bs-secondary-color);
    font-size: 0.82rem;
}

.journey-attachment-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.journey-attachment-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    width: min(100%, 12.25rem);
    min-width: 0;
    padding: 0.35rem 0.55rem 0.35rem 0.35rem;
    border: 1px solid var(--bs-border-color);
    border-radius: 0.375rem;
    background: var(--bs-paper-bg, var(--bs-card-bg));
    color: var(--bs-heading-color);
    font-weight: 500;
    line-height: 1.2;
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.06);
    vertical-align: middle;
    transition: background-color 0.16s ease-in-out, border-color 0.16s ease-in-out, box-shadow 0.16s ease-in-out;
}

.journey-attachment-chip-button {
    appearance: none;
    cursor: pointer;
    font: inherit;
    text-align: left;
}

.journey-attachment-chip:hover,
.journey-attachment-chip:focus-visible {
    background: rgba(var(--bs-primary-rgb), 0.08);
    border-color: rgba(var(--bs-primary-rgb), 0.45);
    box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.1);
    color: var(--bs-heading-color);
}

.journey-attachment-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    inline-size: 1.55rem;
    block-size: 1.55rem;
    border-radius: 0.25rem;
    font-size: 1rem;
}

.journey-attachment-name {
    display: block;
    max-width: 100%;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: inherit;
}

.journey-attachment-thumb-0 {
    background: rgba(var(--bs-primary-rgb), 0.16);
    color: var(--bs-primary);
}

.journey-attachment-thumb-1 {
    background: rgba(var(--bs-info-rgb), 0.16);
    color: var(--bs-info);
}

.journey-attachment-thumb-2 {
    background: rgba(var(--bs-success-rgb), 0.16);
    color: var(--bs-success);
}

.journey-attachment-thumb-3 {
    background: rgba(var(--bs-danger-rgb), 0.13);
    color: var(--bs-danger);
}

.journey-attachment-thumb-4 {
    background: rgba(var(--bs-warning-rgb), 0.16);
    color: var(--bs-warning);
}

.journey-attachment-thumb-5 {
    background: color-mix(in srgb, var(--bs-primary) 14%, transparent);
    color: color-mix(in srgb, var(--bs-primary), var(--bs-danger) 35%);
}

.journey-attachment-semantic-link {
    background: rgba(var(--bs-success-rgb), 0.16);
    color: var(--bs-success);
}

.journey-attachment-semantic-youtube {
    background: rgba(var(--bs-danger-rgb), 0.14);
    color: var(--bs-danger);
}

@media (max-width: 575.98px) {
    #taskModal .modal-dialog {
        margin: 0.75rem;
    }

    #taskModal .modal-content {
        max-height: calc(100dvh - 1.5rem);
    }

    #taskModal .modal-header {
        padding: 0.85rem 1rem;
    }

    #taskModal .modal-body {
        padding: 0.85rem !important;
    }

    #taskModal .modal-footer {
        padding: 0.75rem 0.85rem 0.9rem;
        gap: 0.5rem;
    }

    .journey-task-brief {
        padding: 0.8rem;
        border-radius: 0.65rem;
    }

    .journey-task-brief-icon {
        inline-size: 1.5rem;
        block-size: 1.5rem;
    }

    .journey-task-description {
        font-size: 0.8rem;
    }

    .journey-attachment-chip {
        width: min(100%, 10.75rem);
    }

}

.journey-task-actions {
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: max-content;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem 1.35rem;
}

.journey-start-island-btn,
.journey-task-complete-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 2.55rem;
    border-radius: 0.55rem;
    font-weight: 700;
    box-shadow: 0 0.35rem 0.8rem rgba(var(--bs-primary-rgb), 0.22);
}

.journey-start-island-btn {
    gap: 0.45rem;
    padding-inline: 1.25rem;
}

.journey-start-island-icon {
    display: inline-grid;
    place-items: center;
    inline-size: 1.25rem;
    block-size: 1.25rem;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.22);
    color: currentColor;
    line-height: 1;
}

.journey-start-island-icon::before {
    content: "";
    inline-size: 0;
    block-size: 0;
    margin-inline-start: 0.12rem;
    border-block: 0.28rem solid transparent;
    border-inline-start: 0.44rem solid currentColor;
}

.journey-task-pin-btn {
    min-inline-size: 2.55rem;
    min-height: 2.55rem;
    border-radius: 0.55rem;
}

@media (max-width: 575.98px) {
    .journey-task-actions {
        grid-auto-flow: column;
        grid-auto-columns: auto;
        grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr) auto;
        padding: 0.75rem 0.85rem 0.9rem;
        gap: 0.45rem;
    }

    .journey-start-island-btn {
        grid-column: 1;
        inline-size: 100%;
        min-inline-size: 0;
        padding-inline: 0.8rem;
    }

    .journey-task-complete-btn {
        grid-column: 2;
        inline-size: 100%;
        min-inline-size: 0;
        padding-inline: 0.8rem;
    }

    .journey-task-pin-btn {
        grid-column: 3;
    }
}

@media (max-width: 420px) {
    .journey-task-actions {
        grid-template-columns: auto minmax(0, 1fr) auto;
    }

    .journey-start-island-btn {
        inline-size: 2.55rem;
        padding-inline: 0;
    }

    .journey-start-island-btn > span:last-child {
        position: absolute;
        inline-size: 1px;
        block-size: 1px;
        overflow: hidden;
        clip: rect(0 0 0 0);
        white-space: nowrap;
    }
}

.journey-task-state-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 2.25rem;
    padding: 0.55rem 1rem;
    border-radius: 999px;
    font-weight: 700;
    line-height: 1;
}

.journey-task-state-pill-review {
    color: #ff8a00;
    background: rgba(255, 138, 0, 0.12);
    border: 1px solid rgba(255, 138, 0, 0.26);
}

#completePinModal {
    z-index: 1090;
}

#completePinModal .modal-content {
    border: 1px solid rgba(var(--bs-primary-rgb), 0.18);
    box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, 0.25);
}




/* wrapper جه جوه layout vuexy */
.layout-wrapper {
  position: relative;   /* مهم علشان الـ pseudo-element */
  min-height: 100vh;
}

@media (min-width: 993px) {
  html:has(.background-container),
  body:has(.background-container) {
    overflow-y: hidden;
  }

  body:has(.background-container) .layout-wrapper,
  body:has(.background-container) .layout-page,
  body:has(.background-container) .content-wrapper {
    min-height: 100dvh;
    max-height: 100dvh;
    overflow-y: clip;
  }
}

/* الخلفية الحقيقية */
.layout-wrapper::before{
  content:"";
  position: fixed;
  inset: 0;
  /*background: url('https://atomlearning.com/cdn-cgi/image/width=1350,format=auto,quality=100/https://assets.atomlearning.com/media/cad03f18-f1b6-41fa-864b-d9d3512d43bf')*/
  /*            no-repeat center center;*/
  background:
    linear-gradient(135deg, rgba(217, 237, 244, 0.34), rgba(242, 212, 189, 0.22)),
    url('{{ $bgUrl ?: asset('images/journey/background34.webp') }}')
    no-repeat center center;
    /*background: var(--page-bg) no-repeat center center;*/

  background-size: cover;
  z-index: -1;          /* ورا كل المحتوى */
}






/*.background-container {*/
/*    background: url('https://atomlearning.com/cdn-cgi/image/width=1350,format=auto,quality=100/https://assets.atomlearning.com/media/cad03f18-f1b6-41fa-864b-d9d3512d43bf') no-repeat center center;*/
/*    background-size: cover;*/
/*    min-height: 100vh;*/
/*    position: relative;*/
/*    display: flex;*/
/*    justify-content: center;*/
/*    align-items: center;*/
/*    padding: 20px;*/
/*    overflow-x: auto;*/
/*}*/
  .background-container{
       min-height: calc(100vh - 300px); /* حسب ارتفاع الـ navbar */
    display: flex;
    justify-content: center;   /* center horizontally */
    align-items: center;       /* center vertically */
    width: 100%;
    position: relative;

}



/* Weekly progress box */
.weekly-progress {
    position: absolute;
    top: 0px;
    right: 20px;
    /*width: 220px;*/
    /*background-color: #ffffffee;*/
    background: var(--w14-journey-panel-bg);
    border: 1px solid var(--w14-journey-panel-border);
    color: var(--w14-journey-text);
    border-radius: 12px;
    padding: 18px;
    box-shadow: var(--w14-journey-shadow);
    z-index: 10;
    font-size: 0.9rem;
    font-weight: 600;
}
.Workspace{
     position: absolute;
    top: 20px;
    left: 20px;
    background: var(--w14-journey-panel-bg);
    border: 1px solid var(--w14-journey-panel-border);
    border-radius: 12px;
    padding: 4px;
    box-shadow: var(--w14-journey-shadow);
    z-index: 10;
    font-size: 0.9rem;
    font-weight: 600;
    padding-left: 17px;
    padding-right: 17px;
}
.Workspace a{
    color: var(--w14-journey-text);
    text-decoration: none;
}
/* Topic timeline container */
.topic-track {
    display: flex;
    align-items: center;
    /* justify-content: center; */
    flex-wrap: nowrap;
    width: 100%;
justify-content: flex-start;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 0.85rem;
    margin: -0.85rem;
    scrollbar-width: thin;
    /* padding: 2rem; */
}

/* Topic circle */
.topic-circle {
    width: 160px;
    height: 160px;
    border: 0;
    background: linear-gradient(145deg, var(--w14-journey-island-surface), var(--w14-journey-island-surface-alt));
    border-radius: 50%;
    box-shadow: var(--w14-journey-shadow);
    color: var(--w14-journey-text);
    display: inline-flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    position: relative;
    cursor: pointer;
    appearance: none;
    font: inherit;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    padding: 10px;
    text-align: center;
}

.topic-circle-completed {
    border: 5px solid var(--bs-primary);
}

.journey-topic-status-mark {
    position: absolute;
    top: 0.45rem;
    left: 0.45rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    inline-size: 1.5rem;
    block-size: 1.5rem;
    border-radius: 999px;
    color: var(--bs-success);
    background: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 92%, var(--bs-success));
    box-shadow: 0 0.15rem 0.45rem rgba(0, 0, 0, 0.12);
    border: 2px solid currentColor;
    font-size: 0.85rem;
    line-height: 1;
}

.topic-circle:hover {
    transform: scale(1.05);
    box-shadow: 0 0.75rem 1.75rem rgba(var(--bs-primary-rgb), 0.2), var(--w14-journey-shadow);
    /*border: 3px solid #cc0000;*/
    border: 3px solid var(--bs-primary);
}

.topic-circle .journey-topic-title {
    margin-top: 0.8rem;
    margin-bottom: 0.2rem;
    font-weight: 700;
    font-size: 1rem;
    color: var(--w14-journey-text);
    max-width: 7.5rem;
    overflow-wrap: anywhere;
}

/* New topic badge */
.topic-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: var(--bs-danger);
    color: var(--bs-danger-contrast, #fff);
    padding: 4px 10px;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* Line between topics */
.topic-line {
    width: 86px;
    height: 8px;
    background-color: var(--w14-journey-line);
    margin: 0 0px;
    flex-shrink: 0;
    border-radius: 4px;
    box-shadow: 0 0.35rem 0.8rem rgba(0, 0, 0, 0.14);
}


/* UP NEXT label and arrow */
.up-next {
    position: absolute;
    top: -59px;
    left: 30%;
    background: var(--bs-danger);
    color: var(--bs-danger-contrast, #fff);
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 5px;
    white-space: nowrap;
    z-index: 5;
}


.up-next-line {
    width: 4px;
    height: 56px;
    background-color: var(--bs-danger);
    position: absolute;
    top: -56px;
    left: 72px;
    z-index: 4;
}

/* Mobile responsive */
@media (max-width: 992px) {

    .topic-track {
        flex-direction: column;
        align-items: center;
        overflow-x: visible;
        padding: 0;
        margin: 0;
        /*margin-top: 150px;*/

    }

.topic-block{
      flex-direction: column !important;
}
    .topic-circle {
        /* margin: 30px 0; */
        z-index: 2;
    }

    .topic-line {
        width: 4px;
        height: 40px;
        background-color: var(--w14-journey-line);
    }

    .up-next {
      top: -50px;
  left: 71%;
  transform: translateX(-50%);
    }

    .up-next-line {
      width: 4px;
  height: 46px;
  top: -46px;
  left: 50%;
  transform: translateX(-50%);
    }


    .weekly-progress {
    position: absolute;
    top: 0px;
    right: 20px;
    /*width: 220px;*/
    /*background-color: #ffffffee;*/
    background: var(--w14-journey-panel-bg);
    border: 1px solid var(--w14-journey-panel-border);
    color: var(--w14-journey-text);
    border-radius: 12px;
    padding: 18px;
    box-shadow: var(--w14-journey-shadow);
    z-index: 10;
    font-size: 0.9rem;
    font-weight: 600;
}
    .Workspace{
    position: fixed;
    top: 20px;
    left: 20px;
    background: var(--w14-journey-panel-bg);
    border: 1px solid var(--w14-journey-panel-border);
    border-radius: 12px;
    padding: 4px;
    box-shadow: var(--w14-journey-shadow);
    z-index: 10;
    font-size: 0.9rem;
    font-weight: 600;
    padding-left: 17px;
    padding-right: 17px;
}

}
</style>
<!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>-->
<!-- أضف هذا السطر في ملف journey.blade.php أو في ملف الـ layout الرئيسي -->
<!-- <script src="https://cdn.jsdelivr.net/npm/confetti-canvas@1.0.1/dist/confetti-canvas.min.js"></script> -->

<!-- استبدل سطر تضمين confetti-canvas.min.js بهذا السطر -->

<!--<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js" defer></script>-->







<script>
  (function () {
    const focusPinField = () => {
      const pin = document.getElementById('pinField');
      if (!pin) return;
      pin.focus();
      pin.select?.();
    };

    const bindPinShownFocus = () => {
      const pinModalEl = document.getElementById('completePinModal');
      if (!pinModalEl || pinModalEl.dataset.w14PinShownBound === 'true') return;

      pinModalEl.addEventListener('shown.bs.modal', focusPinField);
      pinModalEl.dataset.w14PinShownBound = 'true';
    };

    document.addEventListener('livewire:init', bindPinShownFocus);
    document.addEventListener('livewire:navigated', bindPinShownFocus);
    bindPinShownFocus();
    // دالة مساعدة ترجع Bootstrap Modal instance
    const getModal = (id) => {
      const el = document.getElementById(id);
      if (!el) return null;
      if (id === 'completePinModal') {
        return bootstrap.Modal.getOrCreateInstance(el, { backdrop: false });
      }
      return bootstrap.Modal.getOrCreateInstance(el);
    };

    const journeyModalIds = ['taskModal', 'completePinModal', 'parentDirectCompleteModal'];

    const hasTaskQueryParam = () => {
      const url = new URL(window.location.href);
      return url.searchParams.has('task');
    };

    const removeTaskQueryParam = () => {
      const url = new URL(window.location.href);
      if (!url.searchParams.has('task')) return;

      url.searchParams.delete('task');
      window.history.replaceState({}, '', url.toString());
    };

    const hasOpenJourneyModal = () => journeyModalIds.some((id) => {
      const el = document.getElementById(id);
      return el && el.classList.contains('show');
    });

    const saveLayoutCollapsedPreference = () => {
      if (!window.config?.enableMenuLocalStorage || window.Helpers?.isSmallScreen?.()) {
        return;
      }

      try {
        localStorage.setItem(
          `templateCustomizer-${window.templateName}--LayoutCollapsed`,
          String(window.Helpers.isCollapsed())
        );
      } catch (error) {}
    };

    const bindVuexyLayoutToggles = () => {
      document.querySelectorAll('.layout-menu-toggle').forEach((toggle) => {
        if (toggle.dataset.w14JourneyLayoutToggleBound === 'true') {
          return;
        }

        const freshToggle = toggle.cloneNode(true);
        freshToggle.dataset.w14JourneyLayoutToggleBound = 'true';
        freshToggle.addEventListener('click', (event) => {
          event.preventDefault();
          window.Helpers?.toggleCollapsed?.();
          saveLayoutCollapsedPreference();
        });

        toggle.replaceWith(freshToggle);
      });
    };

    const bindVuexyHoverToggleReveal = () => {
      const layoutMenu = document.getElementById('layout-menu');
      if (!layoutMenu || layoutMenu.dataset.w14JourneyHoverRevealBound === 'true') {
        return;
      }

      let revealTimer = null;

      layoutMenu.onmouseenter = () => {
        clearTimeout(revealTimer);
        revealTimer = setTimeout(() => {
          if (!window.Helpers?.isSmallScreen?.()) {
            document.querySelector('.layout-menu-toggle')?.classList.add('d-block');
          }
        }, window.Helpers?.isSmallScreen?.() ? 0 : 300);
      };

      layoutMenu.onmouseleave = () => {
        clearTimeout(revealTimer);
        document.querySelector('.layout-menu-toggle')?.classList.remove('d-block');
      };

      layoutMenu.dataset.w14JourneyHoverRevealBound = 'true';
    };

    const restoreVuexyLayoutMenu = () => {
      const helpers = window.Helpers;
      const layoutMenu = document.getElementById('layout-menu');
      if (!helpers || !layoutMenu) return;

      document.documentElement.classList.remove('layout-transitioning', 'layout-menu-hover');
      document.querySelectorAll('.layout-overlay, .drag-target, #layout-menu').forEach((el) => {
        el.style.removeProperty('pointer-events');
      });

      helpers._unbindMenuMouseEvents?.();
      helpers._bindMenuMouseEvents?.();
      helpers.update?.();
      bindVuexyLayoutToggles();
      bindVuexyHoverToggleReveal();

      window.dispatchEvent(new Event('resize'));
    };

    const releaseModalChrome = () => {
      removeTaskQueryParam();

      journeyModalIds.forEach((id) => {
        const el = document.getElementById(id);
        if (!el || el.classList.contains('show')) return;

        bootstrap.Modal.getInstance(el)?.dispose();
        el.style.removeProperty('display');
        el.removeAttribute('aria-modal');
        el.removeAttribute('role');
        el.setAttribute('aria-hidden', 'true');
      });

      if (hasOpenJourneyModal()) {
        return;
      }

      document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove());
      document.body.classList.remove('modal-open');
      document.body.style.removeProperty('overflow');
      document.body.style.removeProperty('padding-right');
      setTimeout(restoreVuexyLayoutMenu, 0);
      setTimeout(restoreVuexyLayoutMenu, 250);
    };

    const hardReleaseHiddenModal = (id) => {
      const el = document.getElementById(id);
      if (!el || el.classList.contains('show')) return;

      bootstrap.Modal.getInstance(el)?.dispose();
      el.style.removeProperty('display');
      el.removeAttribute('aria-modal');
      el.removeAttribute('role');
      el.setAttribute('aria-hidden', 'true');
    };

    window.w14CloseJourneyModal = function(id) {
      const modal = getModal(id);
      modal?.hide();

      setTimeout(() => {
        hardReleaseHiddenModal(id);
        releaseModalChrome();
      }, 350);
    };

    const bindJourneyModalRelease = () => {
      journeyModalIds.forEach((id) => {
        const el = document.getElementById(id);
        if (!el || el.dataset.w14JourneyReleaseBound === 'true') return;

        el.addEventListener('hidden.bs.modal', () => {
          hardReleaseHiddenModal(id);
          releaseModalChrome();
        });

        el.dataset.w14JourneyReleaseBound = 'true';
      });
    };

    const registerJourneyBridge = () => {
      if (window.w14JourneyModalBridgeInitialized || !window.Livewire) return;
      window.w14JourneyModalBridgeInitialized = true;

      bindPinShownFocus();
      bindJourneyModalRelease();
      document.addEventListener('livewire:navigated', bindPinShownFocus);
      document.addEventListener('livewire:navigated', () => {
        bindJourneyModalRelease();
        setTimeout(releaseModalChrome, 150);
      });

      Livewire.on('open-task-modal', () => {
        getModal('taskModal')?.show();
      });

      Livewire.on('close-task-modal', () => {
        window.w14CloseJourneyModal('taskModal');
      });

      Livewire.on('open-pin-modal', () => {
        const modal = getModal('completePinModal');
        modal?.show();

        setTimeout(() => {
          const pinInput = document.getElementById('pinField');
          if (pinInput) pinInput.focus();
        }, 200);
      });

      Livewire.on('close-pin-modal', () => {
        window.w14CloseJourneyModal('completePinModal');
      });

      Livewire.on('open-parent-direct-complete-modal', () => {
        getModal('parentDirectCompleteModal')?.show();
      });

      Livewire.on('close-parent-direct-complete-modal', () => {
        window.w14CloseJourneyModal('parentDirectCompleteModal');
      });

      window.addEventListener('pin-modal-opened', focusPinField);
      window.addEventListener('pageshow', () => {
        bindJourneyModalRelease();
        setTimeout(releaseModalChrome, 150);
      });
    };

    if (window.Livewire) {
      registerJourneyBridge();
    }

    document.addEventListener('livewire:init', registerJourneyBridge, { once: true });
    document.addEventListener('livewire:initialized', registerJourneyBridge, { once: true });
  })();
</script>





</div>
