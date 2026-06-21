<div class="row g-6">
  <div class="col-12">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-6">
      <div>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <h4 class="mb-0">Starter Catalog Installer</h4>
          <details class="intake-info intake-info--inline">
            <summary class="intake-info__trigger" aria-label="Open starter catalog info">
              <i class="icon-base ti tabler-info-circle icon-18px"></i>
            </summary>
            <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
              Installs the reviewed My Deen Journey and Well Being starter automation catalog for one selected teacher.
            </div>
          </details>
        </div>
        <p class="text-muted mb-0 mt-1">Preview first, then install for a single active teacher. Existing catalog rows are verified instead of duplicated.</p>
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

  @if ($installerError)
    <div class="col-12">
      <div class="alert alert-danger mb-0" role="alert">
        {{ $installerError }}
      </div>
    </div>
  @endif

  <div class="col-12 col-sm-6 col-xl-4">
    <div class="card card-border-shadow-primary h-100">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar me-4">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="icon-base ti tabler-package icon-28px"></i>
            </span>
          </div>
          <div>
            <h4 class="mb-0">{{ number_format($catalogSummary->sum(fn ($group) => count($group['entries']))) }}</h4>
            <p class="mb-0">Catalog Items</p>
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
            <h4 class="mb-0">{{ number_format($teachers->count()) }}</h4>
            <p class="mb-0">Active Teachers</p>
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
              <i class="icon-base ti tabler-database icon-28px"></i>
            </span>
          </div>
          <div>
            <h4 class="mb-0">{{ number_format($selectedTeacherRegistryCount) }}</h4>
            <p class="mb-0">Selected Teacher Rows</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="card h-100">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Install For Teacher</h5>
      </div>
      <div class="card-body pt-4">
        <div class="mb-4">
          <label class="form-label" for="starter-catalog-teacher">Teacher</label>
          <select id="starter-catalog-teacher" class="form-select @error('teacherId') is-invalid @enderror" wire:model.live="teacherId">
            <option value="">Choose active teacher</option>
            @foreach ($teachers as $teacher)
              <option value="{{ $teacher->id }}">{{ $teacher->name ?: 'Teacher' }} - {{ $teacher->email }}</option>
            @endforeach
          </select>
          @error('teacherId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>

        <div class="d-grid gap-3">
          <button type="button" class="btn btn-outline-primary" wire:click="previewCatalog" wire:loading.attr="disabled">
            <i class="icon-base ti tabler-eye me-1"></i>
            Preview
          </button>

          <div class="form-check">
            <input class="form-check-input @error('confirmInstall') is-invalid @enderror" type="checkbox" value="1" id="starter-catalog-confirm" wire:model.live="confirmInstall">
            <label class="form-check-label" for="starter-catalog-confirm">
              Install or verify this catalog for the selected teacher.
            </label>
            @error('confirmInstall') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
          </div>

          <button type="button" class="btn btn-primary" wire:click="installCatalog" wire:loading.attr="disabled">
            <i class="icon-base ti tabler-package me-1"></i>
            Install Starter Catalog
          </button>
        </div>

        <hr class="my-4">

        <div class="small text-muted">
          <div><strong>Database:</strong> {{ $databaseName ?: 'unknown' }}</div>
          <div><strong>Registry:</strong> {{ $registryTableExists ? 'ready' : 'missing' }}</div>
          <div class="mt-2">This page installs one teacher at a time. It does not run the all-teachers command.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-8">
    <div class="card h-100">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Catalog Contents</h5>
      </div>
      <div class="card-body pt-4">
        <div class="row g-4">
          @foreach ($catalogSummary as $group)
            <div class="col-12 col-lg-6">
              <div class="border rounded p-3 h-100">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                  <h6 class="mb-0">{{ $group['subject'] }}</h6>
                  <span class="badge bg-label-primary">{{ count($group['entries']) }} items</span>
                </div>
                <div class="d-grid gap-2">
                  @foreach ($group['entries'] as $entry)
                    <div class="d-flex justify-content-between gap-3 small">
                      <span>{{ $entry['title'] }}</span>
                      <span class="text-muted text-nowrap">{{ $entry['type'] }}</span>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  @if ($previewResult || $installResult)
    <div class="col-12">
      <div class="row g-6">
        @foreach ([['title' => 'Preview Result', 'result' => $previewResult, 'color' => 'info'], ['title' => 'Install Result', 'result' => $installResult, 'color' => 'success']] as $panel)
          @if ($panel['result'])
            <div class="col-12 col-xl-6">
              <div class="card h-100">
                <div class="card-header border-bottom">
                  <div class="d-flex justify-content-between align-items-center gap-3">
                    <h5 class="card-title mb-0">{{ $panel['title'] }}</h5>
                    @if ($lastTeacherLabel)
                      <span class="badge bg-label-{{ $panel['color'] }}">{{ $lastTeacherLabel }}</span>
                    @endif
                  </div>
                </div>
                <div class="card-body pt-4">
                  <div class="row g-3 mb-4">
                    <div class="col-4">
                      <div class="border rounded p-3 text-center">
                        <div class="h4 mb-0">{{ number_format($panel['result']['created'] ?? 0) }}</div>
                        <div class="small text-muted">Create</div>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="border rounded p-3 text-center">
                        <div class="h4 mb-0">{{ number_format($panel['result']['updated'] ?? 0) }}</div>
                        <div class="small text-muted">Verify</div>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="border rounded p-3 text-center">
                        <div class="h4 mb-0">{{ number_format($panel['result']['skipped'] ?? 0) }}</div>
                        <div class="small text-muted">Skip</div>
                      </div>
                    </div>
                  </div>

                  <div class="border rounded p-3 bg-body-tertiary" style="max-height: 320px; overflow: auto;">
                    @forelse (($panel['result']['messages'] ?? []) as $message)
                      <div class="small mb-2">- {{ $message }}</div>
                    @empty
                      <div class="small text-muted">No messages returned.</div>
                    @endforelse
                  </div>
                </div>
              </div>
            </div>
          @endif
        @endforeach
      </div>
    </div>
  @endif
</div>
