
<div>
    <style>/* مثلاً في ملف custom.css بتاعك */
.behavior-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.08);
  transition: all 0.15s ease-in-out;
}
.icon-picker-menu {
    border-radius: 18px;
    max-height: 260px;
    overflow-y: auto;
    background: #ffffff;
}

.icon-picker-menu::-webkit-scrollbar {
    width: 6px;
}
.icon-picker-menu::-webkit-scrollbar-thumb {
    border-radius: 8px;
    background-color: rgba(0,0,0,0.15);
}

.icon-option {
    border-radius: 14px;
}

.icon-option:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(0,0,0,0.06);
}



    .shadow_success{
        box-shadow: 0px 0px 4px 4px #69e4a0  !important;
    }
    .shadow_warning{
        box-shadow: 0px 0px 4px 4px #e5d8aa  !important;
    }
    .shadow_danger{
        box-shadow: 0px 0px 4px 4px #ecbcbe  !important;
    }
   
        .behavior-clickable {
  cursor: pointer;
}

.behavior-readonly {
  cursor: default;
}
.behavior-drag-handle {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.85rem;
  height: 1.85rem;
  border-radius: 999px;
  cursor: grab;
  line-height: 1;
  color: var(--bs-secondary-color);
  background: color-mix(in sRGB, var(--bs-paper-bg) 88%, var(--bs-primary) 12%);
}
.behavior-drag-handle:active {
  cursor: grabbing;
}
.behavior-drag-handle:hover {
  color: var(--bs-primary);
  background: color-mix(in sRGB, var(--bs-paper-bg) 78%, var(--bs-primary) 22%);
}
.behavior-sortable-ghost .behavior-card {
  opacity: .65;
}
.behavior-sortable-chosen .behavior-card {
  box-shadow: 0 8px 22px rgba(0,0,0,0.12);
}
  .points-theme-success .point-pill input:checked + .point-circle{
    border-width: 2px;
    background-color: color-mix(in sRGB, var(--bs-paper-bg) var(--bs-bg-label-tint-amount), var(--bs-success));
  }
  .points-theme-warning .point-pill input:checked + .point-circle{
    border-width: 2px;
    background-color: color-mix(in sRGB, var(--bs-paper-bg) var(--bs-bg-label-tint-amount), var(--bs-warning));
  }
  .points-theme-danger .point-pill input:checked + .point-circle{
    border-width: 2px;
    background-color: color-mix(in sRGB, var(--bs-paper-bg) var(--bs-bg-label-tint-amount), var(--bs-danger));
  }



</style>
@php
  $assetPath = function (?string $path): ?string {
    if (! $path) {
      return null;
    }

    $normalizedPath = ltrim($path, '/');

    if (\Illuminate\Support\Str::startsWith($normalizedPath, ['http://', 'https://'])) {
      return $normalizedPath;
    }

    return asset(ltrim(
      \Illuminate\Support\Str::startsWith($normalizedPath, 'public/')
        ? \Illuminate\Support\Str::after($normalizedPath, 'public/')
        : $normalizedPath,
      '/'
    ));
  };
@endphp
  {{-- Tabs --}}
   <div class="card p-5 mt-5">
   <div class="card-title m-0">
      <h5 class="mb-1">Points Lab</h5>
      <p class="card-subtitle">Student behaviors</p>
    </div>
  <div class="d-flex flex-column flex-lg-row  flex-md-row flex-sm-row  align-items-lg-center align-items-md-center align-items-sm-center align-items-start justify-content-between flex-lg-nowrap gap-2 w-100 mt-3 mb-3">
      
  @php
  $tabColor = [
    'Positive' => 'success',
    'Slip'     => 'warning',
    'No Way'   => 'danger',
  ];
  $tabLabel = [
    'Positive' => 'Positive',
    'Slip'     => 'Slip',
    'No Way'   => 'Red Flag',
  ];
@endphp

<ul class="nav nav-pills mb-3 row col-12 d-flex flex-wrap flex-column flex-lg-row  flex-md-row flex-sm-row  align-items-center">
  @foreach (['Positive','Slip','No Way'] as $tab)
    @php $c = $tabColor[$tab]; @endphp
    <li class="col-lg-2 col-md-3 col-sm-4 me-0 pe-0 col-11 mb-3">
      <button
        class="col-12 btn {{ $activeTab === $tab ? "active btn-{$c} shadow_{$c}" : "btn-label-{$c}" }}"
        type="button"
        wire:click="setTab('{{ $tab }}')"
      >
        {{ $tabLabel[$tab] }}
      </button>
    </li>
  @endforeach
</ul>
  
  
  @php
  $items =
    $activeTab === 'Positive' ? $positiveBehaviors :
    ($activeTab === 'Slip' ? $slipBehaviors : $noWayBehaviors);

  $tabColor =
    $activeTab === 'Positive' ? 'success' :
    ($activeTab === 'Slip' ? 'warning' : 'danger');

  $isPositive = $activeTab === 'Positive';
@endphp
  
  

 
</div>
  {{-- محتوى الـ Tabs --}}

 {{-- زرار إضافة سلوك --}}
  <div class="d-flex justify-content-end mb-3">
    <button
      class="btn btn-sm btn-{{ $tabColor }} d-inline-flex align-items-center gap-1"
      wire:click="openCreate('{{ $activeTab }}')"
    >
      <i class="ti tabler-plus"></i>
      Add behavior
    </button>
  </div>

  <div class="row g-3 behavior-sort-list" data-behavior-type="{{ $activeTab }}">
    @forelse($items as $item)
    @if( $item['teacher_desc'] == 0)
      @php
        $isStudentBehavior = !is_null($item['student_id'] ?? null);
        $isArchivedBehavior = ($item['status'] ?? 'active') !== 'active';
        $canEditBehavior = $isStudentBehavior && ! $isArchivedBehavior;
      @endphp
      <div
        class="col-12 col-sm-6 col-md-4 col-lg-3 {{ $canEditBehavior ? '' : 'behavior-sortable-locked' }}"
        wire:key="behavior-{{ $activeTab }}-{{ $item['id'] }}"
        data-behavior-id="{{ $item['id'] }}"
        data-behavior-owned="{{ $canEditBehavior ? '1' : '0' }}"
      >
          
        <div
          class="card behavior-card h-100 shadow-sm border-{{ $tabColor }} {{ $isArchivedBehavior ? 'opacity-75 bg-label-secondary' : '' }}"
          style="cursor: {{ $canEditBehavior ? 'pointer' : 'default' }}; border-radius: 18px;"
          @if($canEditBehavior)
          wire:click="openEdit({{ $item['id'] }})"
          @endif
        >
        
            
           {{-- زرار الحذف X --}}
           
    @if($canEditBehavior)
    <span
      class="position-absolute top-0 start-0 m-1 behavior-drag-handle"
      style="line-height: 1;"
      title="Drag to reorder"
      role="button"
      tabindex="0"
      aria-label="Drag to reorder behavior"
      onclick="event.stopPropagation()"
    >
      <span class="ti tabler-grip-vertical fs-6"></span>
    </span>

    <button
      type="button"
      class="btn btn-sm btn-link text-muted position-absolute top-0 end-0 p-1"
      style="line-height: 1;"
      title="Remove behavior"
      wire:click.stop="confirmDelete({{ $item['id'] }})"
    >
      <span class="ti tabler-x fs-5"></span>
    </button>
    @elseif($isArchivedBehavior)
    <span class="badge bg-label-secondary position-absolute top-0 start-0 m-2">
      Archived
    </span>

    <button
      type="button"
      class="btn btn-sm btn-link text-primary position-absolute top-0 end-0 p-1"
      style="line-height: 1;"
      title="Restore behavior"
      wire:click.stop="restoreBehavior({{ $item['id'] }})"
    >
      <span class="ti tabler-restore fs-5"></span>
    </button>
    @endif
            
          <div class="card-body text-center p-3">
            <div class="mb-2">
              @if(!empty($item['discipline_icon_path']))
                <img
                  src="{{ $assetPath($item['discipline_icon_path']) }}"
                  alt="icon"
                  class="img-fluid"
                  style="max-height:40px;"
                >
              @else
                <span class="ti {{ $isPositive ? 'tabler-thumb-up' : 'tabler-alert-triangle' }} fs-2"></span>
              @endif
            </div>

            <div class="fw-semibold small mb-1 text-truncate">
              {{ $item['title'] }}
            </div>

            <div class="small text-{{ $tabColor }}">
              {{ $isPositive ? '+' : '−' }}{{ $item['points'] }} pts
            </div>
          </div>
        </div>
        
      </div>
      @endif
    @empty
      <div class="col-12">
        <p class="text-muted mb-0">No behaviors yet.</p>
      </div>
    @endforelse
    
    
  </div>
</div>
  {{-- Modal --}}
  {{-- Modal --}}
<div wire:ignore.self class="modal fade" id="behaviorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md  modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          {{ $editingId ? 'Edit behavior' : 'Add behavior' }}
        </h5>
        <button type="button" class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close">
        </button>
      </div>

      <div class="modal-body">
          
          
        <div class="mb-3 text-center">
          @php
              $currentIconPath = null;
              if ($formDisciplineIconId) {
                  $currentIcon = collect($icons)->firstWhere('id', $formDisciplineIconId);
                  $currentIconPath = $currentIcon['path'] ?? null;
              }
          @endphp

          <div
            class="icon-picker position-relative d-inline-block"
            x-data="{ iconPickerOpen: false }"
            x-on:keydown.escape.window="iconPickerOpen = false"
            x-on:click.outside="iconPickerOpen = false">
            <button
              type="button"
              class="btn btn-outline-secondary d-flex align-items-center justify-content-center icon-picker-trigger"
              style="border-radius: 16px; padding: 6px 12px; min-width: 70px;"
              x-on:click="iconPickerOpen = !iconPickerOpen"
            >
              @if($currentIconPath)
                <img
                  src="{{ $assetPath($currentIconPath) }}"
                  alt="icon"
                  class="img-fluid me-1"
                  width="32"
                  height="32"
                  decoding="async"
                  style="max-height: 32px;">
              @else
                <span class="ti tabler-photo fs-4 me-1"></span>
              @endif

              <span class="ti tabler-chevron-down fs-6"></span>
            </button>

            <template x-if="iconPickerOpen">
              <div
                class="icon-picker-menu card shadow-lg border-0 position-absolute"
                style="top: 105%; left: 50%; transform: translateX(-50%); z-index: 1080; min-width: 240px;"
              >
                <div class="card-body p-2">
                  <div class="row g-2">
                    @foreach($icons as $icon)
                      <div class="col-3">
                        <button
                          type="button"
                          class="btn btn-light w-100 p-1 icon-option {{ $formDisciplineIconId == $icon['id'] ? 'border border-primary' : '' }}"
                          x-on:click="iconPickerOpen = false; $wire.selectIcon({{ $icon['id'] }})"
                        >
                          <img
                            src="{{ $assetPath($icon['path'] ?? null) }}"
                            alt="icon"
                            class="img-fluid"
                            loading="lazy"
                            decoding="async"
                            width="28"
                            height="28"
                            style="max-height: 28px;"
                          >
                        </button>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>

          
        {{-- Title --}}
        <div class="mb-3">
          <label class="form-label">Title</label>
          <input type="text" class="form-control" wire:model="formTitle">
          @error('formTitle')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        {{-- Points --}}
        <div class="mb-3">
          <label class="form-label">Points</label>
          <input type="number" class="form-control" wire:model="formPoints" min="1" max="100">
          @error('formPoints')<small class="text-danger">{{ $message }}</small>@enderror
        </div>

        {{-- Type --}}
        <div class="mb-3">
          <label class="form-label">Type</label>
          <select class="form-select" wire:model="formType">
            <option value="Positive">Positive</option>
            <option value="Slip">Slip</option>
            <option value="No Way">Red Flag</option>
          </select>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button"
                class="btn btn-outline-secondary"
                data-bs-dismiss="modal">
          Cancel
        </button>
        <button type="button" class="btn btn-primary" wire:click="save">
          Save
        </button>
      </div>
    </div>
  </div>
</div>


{{-- Delete Confirm Modal --}}
<div wire:ignore.self class="modal fade" id="behaviorDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">Remove behavior</h5>
        <button type="button" class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
                wire:click="cancelDelete">
        </button>
      </div>

      <div class="modal-body">
        <p class="mb-0">
          Remove this behavior from the active list? If it already has history, it will be archived and the old history will stay unchanged.
        </p>
      </div>

      <div class="modal-footer border-0 pt-0 d-flex justify-content-center">
       {{-- <button type="button"
                class="btn btn-outline-secondary btn-sm"
                data-bs-dismiss="modal"
                wire:click="cancelDelete">
          Cancel
        </button>--}}
        <button type="button"
                class="btn btn-danger btn-md"
                wire:click="deleteBehavior">
          Remove
        </button>
      </div>
    </div>
  </div>
</div>



 
<script>
  (function () {
    if (window.w14AdminStudentBehaviorInitialized) return;
    window.w14AdminStudentBehaviorInitialized = true;

    document.addEventListener('livewire:initialized', () => {
      const callComponent = (el, method) => {
        const root = el?.closest('[wire\\:id]');
        const componentId = root?.getAttribute('wire:id');

        if (!componentId) {
          return;
        }

        Livewire.find(componentId)?.call(method);
      };

      const behaviorModalEl = document.getElementById('behaviorModal');

      behaviorModalEl?.addEventListener('hidden.bs.modal', () => {
        if (behaviorModalEl.dataset.w14ProgrammaticClose === '1') {
          delete behaviorModalEl.dataset.w14ProgrammaticClose;
          return;
        }

        callComponent(behaviorModalEl, 'closeBehaviorModal');
      });

      Livewire.on('open-behavior-modal', () => {
        const el = document.getElementById('behaviorModal');
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        modal.show();
      });

      Livewire.on('close-behavior-modal', () => {
        const el = document.getElementById('behaviorModal');
        el.dataset.w14ProgrammaticClose = '1';
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        modal.hide();
      });

      Livewire.on('open-behavior-delete-modal', () => {
        const el = document.getElementById('behaviorDeleteModal');
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        modal.show();
      });

      Livewire.on('close-behavior-delete-modal', () => {
        const el = document.getElementById('behaviorDeleteModal');
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        modal.hide();
      });

      const initBehaviorSortables = (startedAt = Date.now()) => {
        if (typeof window.Sortable === 'undefined') {
          if (!window.w14AdminStudentBehaviorSortableRetry && Date.now() - startedAt < 5000) {
            window.w14AdminStudentBehaviorSortableRetry = true;
            window.setTimeout(() => {
              window.w14AdminStudentBehaviorSortableRetry = false;
              initBehaviorSortables(startedAt);
            }, 100);
          }

          return;
        }

        document.querySelectorAll('.behavior-sort-list').forEach((list) => {
          const type = list.dataset.behaviorType;

          if (list._w14BehaviorSortable && list._w14BehaviorSortableType === type) {
            return;
          }

          if (list._w14BehaviorSortable) {
            list._w14BehaviorSortable.destroy();
          }

          list._w14BehaviorSortableType = type;
          list._w14BehaviorSortable = window.Sortable.create(list, {
            animation: 150,
            draggable: '[data-behavior-owned="1"]',
            handle: '.behavior-drag-handle',
            ghostClass: 'behavior-sortable-ghost',
            chosenClass: 'behavior-sortable-chosen',
            filter: 'a, button, input, textarea, select, [data-no-drag]',
            preventOnFilter: true,
            onEnd() {
              const ids = Array.from(list.querySelectorAll('[data-behavior-owned="1"][data-behavior-id]'))
                .map((node) => Number.parseInt(node.getAttribute('data-behavior-id'), 10))
                .filter((id) => Number.isInteger(id) && id > 0);

              const root = list.closest('[wire\\:id]');
              const componentId = root?.getAttribute('wire:id');

              if (componentId && ids.length) {
                Livewire.find(componentId)?.call('reorderBehaviors', type, ids);
              }
            }
          });
        });
      };

      initBehaviorSortables();

      Livewire.hook('morph.updated', () => {
        window.requestAnimationFrame(initBehaviorSortables);
      });
    });
  })();
</script>

  
</div>
