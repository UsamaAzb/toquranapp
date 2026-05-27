<div class="vocab-game vocab-student-hub">
  <div class="vg-surface">
    <div class="vg-header">
      <div>
        <span class="vg-pill">Games</span>
        <h1 class="vg-title mt-2">Vocabulary Games</h1>
        <p class="mb-0 text-muted">Enabled folders and assigned games appear here.</p>
      </div>
    </div>

    @unless ($schemaReady)
      <div class="alert alert-warning mb-0">Vocabulary games are being prepared.</div>
    @else
      @if (! empty($tree))
        <div class="vg-student-browser">
          <div class="vg-student-bar">
            <nav class="vg-student-crumbs" aria-label="Vocabulary game folders">
              <button class="btn btn-sm btn-link p-0" type="button" wire:click="openSet(0)">All games</button>
              @foreach ($breadcrumbs as $crumb)
                <span>/</span>
                @if (! $loop->last)
                  <button class="btn btn-sm btn-link p-0" type="button" wire:click="openSet({{ (int) $crumb['id'] }})">{{ $crumb['title'] }}</button>
                @else
                  <strong>{{ $crumb['title'] }}</strong>
                @endif
              @endforeach
            </nav>

            @if ($currentNode)
              <button class="btn btn-sm btn-outline-primary" type="button" wire:click="openSet(0)">
                <i class="icon-base ti tabler-arrow-left me-1"></i>
                All games
              </button>
            @endif
          </div>

          <div class="vg-student-list">
            @forelse ($currentNodes as $node)
              @include('vocabulary.components.student-source-node', ['node' => $node])
            @empty
              <div class="vg-student-empty">
                <i class="icon-base ti tabler-folder-search"></i>
                <div>
                  <h5>No lessons here yet</h5>
                  <p class="mb-0 text-muted">When a lesson is enabled, it will appear in this folder.</p>
                </div>
              </div>
            @endforelse
          </div>
        </div>
      @else
        <div class="card">
          <div class="card-body text-center py-5">
            <i class="ti tabler-lock-open-2 d-block mb-3" style="font-size: 2rem;"></i>
            <h5>No vocabulary games yet</h5>
            <p class="text-muted mb-0">Enabled vocabulary folders and assigned games will appear here.</p>
          </div>
        </div>
      @endif
    @endunless
  </div>
</div>
