<div>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div class="card-title m-0">
      <h5 class="mb-1">Consequence Agreements</h5>
      <p class="card-subtitle">Mistakes Levels:</p> 
    </div>

    
    
    
    @hasanyrole('admin|super_admin')
    <button type="button"
      class="btn btn-sm btn-primary "
      data-bs-toggle="modal"
      data-bs-target="#addPunishmentModal" style="width: 47px; height: 34px;" title="Add Consequence Agreements" >
    <i class="ti tabler-plus"></i>
  </button>
  @endhasanyrole
  </div>

  <div class="card-body">
    {{-- Tabs --}}
    <ul class="nav nav-tabs  pb-8 d-flex flex-column flex-lg-row  flex-md-row flex-sm-row  align-items-lg-center align-items-md-center align-items-sm-center align-items-start  flex-lg-nowrap gap-2 w-100 mt-3 mb-3" role="tablist">
      <!--@php-->
      <!--  $tabItems = [-->
      <!--      ['slug' => 'minor-slip',         'label' => 'Minor Slip'],-->
      <!--      ['slug' => 'significant-choice', 'label' => 'Significant Choice'],-->
      <!--      ['slug' => 'serious-action',     'label' => 'Serious Action'],-->
      <!--  ];-->
      <!--@endphp-->
      
      
      
      @php
  $tabItems = collect($types)->map(fn($meta, $id) => [
      'id' => $id,
      'slug' => $meta['slug'],
      'label' => $meta['title'],
  ])->values()->all();
@endphp


      @foreach($tabItems as $t)
        <li class="nav-item" wire:key="agreement-tab-{{ $t['slug'] }}">
        
          
          
          
          <a href="javascript:void(0);"
   class="nav-link btn d-flex flex-column align-items-center justify-content-center {{ $activeType === $t['slug'] ? 'active' : '' }}"
   role="tab"
   aria-controls="tab-{{ $t['slug'] }}"
   aria-selected="{{ $activeType === $t['slug'] ? 'true' : 'false' }}"
   wire:click.prevent="setActiveType('{{ $t['slug'] }}')"
>
  <h6 class="tab-widget-title mb-0 mt-2">{{ $t['label'] }}</h6>
</a>

          
          
        </li>
      @endforeach
    </ul>

    {{-- Tab panes --}}
    <div class="tab-content p-0 ms-0 ms-sm-2">

      @foreach($tabItems as $t)
        <div class="tab-pane {{ $activeType === $t['slug'] ? 'show active' : '' }}"  id="tab-{{ $t['slug'] }}" role="tabpanel">
          @if($activeType === $t['slug'])
            @if (count($agreements) === 0)
              <div class="alert alert-info my-3">
                No agreements added for the <strong>{{ ucfirst($t['label']) }}</strong> type yet.
              </div>
            @else
            
              
              <ul class="list-group list-group-flush border-top mt-3">
  @foreach($agreements as $item)
  @hasanyrole('admin|super_admin')
    <li class="list-group-item d-flex justify-content-between align-items-center py-3" wire:key="agreement-admin-{{ $item['id'] }}">
      <div>
        <h6 class="mb-0 fw-semibold">{{ $item['title'] }}</h6>
     
      </div>
        
      <span class="badge rounded-pill 
            {{ $item['status'] === 'active' ? 'bg-label-success text-success' : 'bg-label-secondary text-muted' }}">
        {{ ucfirst($item['status']) }}
      </span>
    
    </li>
      @endhasanyrole
       @hasanyrole('teacher|student')
       @if($item['status'] === 'active')
           <li class="list-group-item d-flex justify-content-between align-items-center py-3" wire:key="agreement-viewer-{{ $item['id'] }}">
      <div>
        <h6 class="mb-0 fw-semibold">{{ $item['title'] }}</h6>
      </div>
    </li>
       @endif
      @endhasanyrole
      
  @endforeach
</ul>

              
              
            @endif
          @endif
        </div>
      @endforeach

    </div>
  </div>

  {{-- Add Punishment Modal for admin --}}
  
  @hasanyrole('admin|super_admin')
  <div class="modal fade" id="addPunishmentModal" tabindex="-1" aria-labelledby="addPunishmentLabel" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addPunishmentLabel">Add Consequence  Agreement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form wire:submit.prevent="save">
          <div class="modal-body">
            @if ($successMessage)
              <div class="alert alert-success">{{ $successMessage }}</div>
            @endif

            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Type</label>
                <select class="form-select" wire:model="form.punishment_type_id">
                  @foreach($types as $id => $meta)
                    <option value="{{ $id }}">{{ $meta['title'] }}</option>
                  @endforeach
                </select>
                @error('form.punishment_type_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-8">
                <label class="form-label"> title</label>
                <input type="text" class="form-control" placeholder="e.g., No phone for 30 minutes" wire:model="form.title">
                @error('form.title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>

              <div class="col-12">
                <label class="form-label">Quick suggestions</label>
                <div class="d-flex flex-wrap gap-2">
                  @foreach($suggestions as $s)
                    <!--<button type="button" class="btn btn-sm btn-outline-secondary"-->
                    <!--        wire:click="addSuggestion('{{ addslashes($s) }}')">{{ $s }}</button>-->
                    
                    
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            wire:click="addSuggestion({{ \Illuminate\Support\Js::from($s) }})">{{ $s }}</button>
                    

                  @endforeach
                </div>
                <small class="text-muted d-block mt-2">Click a suggestion to fill the input above.</small>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  @endhasanyrole
</div>

{{-- ================= TEACHER CARD ================= --}}
 @hasanyrole('teacher|student')
<div class="card mt-4">
  <div class="card-header  d-flex justify-content-between align-items-start flex-column flex-lg-row  flex-md-row flex-sm-row">
    <div class="card-title m-0 mb-3">
        <h5 class="mb-1">
        @role('teacher') Consequence Record @else Consequence  Recorded  @endrole
      </h5>
          <p class="card-subtitle">
        @role('teacher') This Consequence Record for {{ $student?->first_name . $student?->last_name?? '' }} 
        @else This Consequence Record for all subjects
        @endrole
      </p>
    </div>
    @role('teacher')

    <button type="button"
            class="btn btn-outline-primary rounded-circle p-0 d-inline-flex align-items-center justify-content-center "
            data-bs-toggle="modal"
            data-bs-target="#teacherApplyPunishmentModal"
            wire:click="$set('apply.punishment_type_id', {{ $form['punishment_type_id'] ?? 'null' }}); $dispatch('load-apply-agreements')" style="width: 34px; height: 34px;">
      <i class="ti tabler-plus"></i> 
    </button>
     @endrole
  </div>

  <div class="card-body">
    @if (count($applications) === 0)
      <div class="alert alert-secondary">No Consequences have been recorded yet.</div>
    @else
      <ul class="list-group list-group-flush border-top">
        @foreach($applications as $row)
        @php
  $badge = match($row['type']) {
      'Minor Slip' => 'bg-label-info',
      'Significant Choice' => 'bg-label-warning',
      'Serious Action' => 'bg-label-danger'
  };
@endphp
          <li class="list-group-item  py-3">
            <div class="me-3 d-flex justify-content-between align-items-start">
              <div class="d-flex align-items-start gap-2 flex-column flex-lg-row  flex-md-row flex-sm-row ">
               <span class="badge {{ $badge }}">{{ $row['type'] }}</span>

                <h6 class="mb-0">{{ $row['agreement'] }}</h6>
                          

              </div>
               <small class="text-muted">{{ $row['date'] }}</small>
            </div>
              @role('student')
        <small class="text-muted d-block">
          Subject: <strong>{{ $row['subject'] }}</strong>
        </small>
        @endrole
             @if($row['description'])
                <small class="text-muted d-block mt-1">{{ $row['description'] }}</small>
              @endif
          </li>
        @endforeach
      </ul>
    @endif
  </div>
</div>
@endhasanyrole
{{-- ================= TEACHER MODAL ================= --}}
@role('teacher')
  <div class="modal fade" id="teacherApplyPunishmentModal" tabindex="-1" aria-labelledby="teacherApplyPunishmentLabel" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="teacherApplyPunishmentLabel">Apply Punishment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form wire:submit.prevent="saveApplication">
          <div class="modal-body">
            @if ($applySuccess)
              <div class="alert alert-success">{{ $applySuccess }}</div>
            @endif

            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Type</label>
                <select class="form-select" wire:model="apply.punishment_type_id" wire:change="loadApplyAgreements">
                  @foreach($types as $id => $meta)
                    <option value="{{ $id }}">{{ $meta['title'] }}</option>
                  @endforeach
                </select>
                @error('apply.punishment_type_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-8">
                <label class="form-label">Agreements (click to select)</label>
                <div class="d-flex flex-wrap gap-2">
                  @forelse($applyAgreements as $ag)
                    <button type="button"
                            class="btn btn-sm {{ (int)($apply['punishment_agreement_id'] ?? 0) === $ag['id'] ? 'btn-primary' : 'btn-outline-secondary' }}"
                            wire:click="selectAgreement({{ $ag['id'] }})">
                      {{ $ag['title'] }}
                    </button>
                  @empty
                    <div class="alert alert-info w-100 mb-0">No agreements for this type.</div>
                  @endforelse
                </div>
                @error('apply.punishment_agreement_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>

              <div class="col-12">
                <label class="form-label">Description (optional)</label>
                <textarea class="form-control" rows="2" placeholder="Short note..." wire:model="apply.description"></textarea>
                @error('apply.description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endrole




</div>
