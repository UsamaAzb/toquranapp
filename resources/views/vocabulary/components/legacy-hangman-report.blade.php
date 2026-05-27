@if ($legacyReportOpen)
  <div class="border-top p-3 p-md-4">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
      <div>
        <h5 class="mb-1">Legacy Floatie report</h5>
        <p class="text-muted small mb-0">Read-only scan of old game categories before owner-approved import or hiding.</p>
      </div>
      <button class="btn btn-outline-secondary" type="button" wire:click="toggleLegacyReport">Close</button>
    </div>
    <div class="table-responsive">
      <table class="table table-sm">
        <thead>
          <tr>
            <th>Source</th>
            <th>Category</th>
            <th class="text-end">Words</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($legacyReport as $row)
            <tr wire:key="legacy-row-{{ $row['source'] ?? 'source' }}-{{ $row['id'] ?? $loop->index }}">
              <td><span class="badge bg-label-secondary">{{ $row['source'] }}</span></td>
              <td>{{ $row['name'] }}</td>
              <td class="text-end">{{ $row['words_count'] }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-muted text-center">No legacy game tables were found in this environment.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endif
