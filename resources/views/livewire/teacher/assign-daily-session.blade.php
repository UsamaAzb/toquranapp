<div>

@if($show)
<div class="modal show"
     style="display:block; background:rgba(0,0,0,.5); position:fixed; inset:0;"
     role="dialog" aria-modal="true">

  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Assign to students</h5>
        <button type="button" class="btn-close" wire:click="$set('show', false)"></button>
      </div>

      <div class="modal-body">

        <div class="text-muted mb-3">
          Choose students (same subject). Students are grouped by class.
        </div>

        @error('selected')
          <div class="alert alert-danger py-2">{{ $message }}</div>
        @enderror

        @forelse($groupedStudents as $className => $list)
          <div class="mb-4">
            <div class="fw-semibold mb-2">{{ $className }}</div>

            <div class="row">
              @foreach($list as $s)
                <div class="col-md-6 mb-2">
                  <label class="d-flex align-items-start gap-2">
                    <input class="form-check-input" type="checkbox" wire:model="selected" value="{{ $s['id'] }}">
<div>
  <div>{{ $s['student_label'] ?? $s['name'] }}</div>

  @if(!empty($s['assigned_elsewhere']))
    <div class="text-primary small">assigned to another automated task set</div>
  @endif

  @if(!empty($s['is_assigned_current']))
    <div class="text-primary small">assigned</div>
  @endif
</div>
                  </label>
                </div>
              @endforeach
            </div>
          </div>
          <hr>
        @empty
          <div class="text-muted">No students found for this subject.</div>
        @endforelse

      </div>

      <div class="modal-footer">
        <button class="btn btn-label-secondary" wire:click="$set('show', false)">Close</button>
        <button class="btn btn-primary" wire:click="saveAssign">Save</button>
      </div>

    </div>
  </div>
</div>
@endif
</div>
