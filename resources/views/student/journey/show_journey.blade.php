@php
$configData = Helper::appClasses();
@endphp
@extends('layouts/layoutMaster')

@section('title', 'My islands')


@section('content')



    
    
      <div class=" row justify-content-center">@include('layouts/progress_bar')</div>

    
      <livewire:student.journey
        :student-id="$student_id"
        :sessionId="$sessionId"
        :auto-open-task-id="((int) request()->query('task')) ?: null"
        :auto-open-mode="in_array(request()->query('complete'), ['pin', 'parent'], true) ? request()->query('complete') : null"
      />

      @if(request()->filled('task') && in_array(request()->query('complete'), ['pin', 'parent'], true))
        @push('scripts')
        <script>
          (() => {
            const taskId = @json((int) request()->query('task'));
            const mode = @json(request()->query('complete'));

            const openCompletionFlow = () => {
              if (!taskId || !window.Livewire) return;

              window.Livewire.dispatch(
                mode === 'parent' ? 'open-parent-direct-complete-modal-requested' : 'open-complete-modal',
                { taskId }
              );

              const url = new URL(window.location.href);
              url.searchParams.delete('task');
              url.searchParams.delete('complete');
              window.history.replaceState({}, '', url.toString());
            };

            if (window.Livewire) {
              setTimeout(openCompletionFlow, 200);
              return;
            }

            document.addEventListener('livewire:init', () => {
              setTimeout(openCompletionFlow, 200);
            }, { once: true });
          })();
        </script>
        @endpush
      @endif

      @if(request()->filled('task') && !in_array(request()->query('complete'), ['pin', 'parent'], true))
        @push('scripts')
        <script>
          setTimeout(() => {
            const taskModalEl = document.getElementById('taskModal');
            if (!taskModalEl || !window.bootstrap) return;
            bootstrap.Modal.getOrCreateInstance(taskModalEl).show();

            const url = new URL(window.location.href);
            url.searchParams.delete('task');
            window.history.replaceState({}, '', url.toString());
          }, 150);
        </script>
        @endpush
      @endif

 



  <!--/ Card layout -->
@endsection
