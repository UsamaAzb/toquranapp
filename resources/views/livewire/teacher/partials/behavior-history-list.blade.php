<div class="points-history-panel" id="student-behavior-history" wire:key="student-behavior-history">
  <div class="points-history-toolbar">
    <div>
      <div class="points-history-head">
        <div class="points-history-title">
          <h5 class="mb-0 fw-semibold">Student History</h5>
          <span class="badge bg-label-primary points-history-count">
            {{ $historyTab === 'tasks' ? count($taskHistoryRows) : count($sessionDisciplines) }}
            /
            {{ $historyTab === 'tasks' ? $taskHistoryTotal : $historyTotal }}
          </span>
        </div>

        <ul class="nav nav-pills points-history-tabs" aria-label="Student history sections">
          <li class="nav-item">
            <button
              type="button"
              class="nav-link {{ $historyTab === 'behavior' ? 'active' : '' }}"
              wire:click="setHistoryTab('behavior')"
            >
              Behavior
            </button>
          </li>
          <li class="nav-item">
            <button
              type="button"
              class="nav-link {{ $historyTab === 'tasks' ? 'active' : '' }}"
              wire:click="setHistoryTab('tasks')"
            >
              Tasks
            </button>
          </li>
        </ul>
      </div>

      @if($historyTab === 'behavior')
        <div class="points-history-filters" aria-label="History filters">
          @foreach($this->historyFilters as $filter)
            <button
              type="button"
              class="points-history-filter-card points-history-filter-{{ $filter['color'] }} {{ $historyTypeFilter === $filter['value'] ? 'is-active' : '' }}"
              wire:click="setHistoryTypeFilter('{{ $filter['value'] }}')"
              aria-pressed="{{ $historyTypeFilter === $filter['value'] ? 'true' : 'false' }}"
            >
              <span class="points-history-filter-top">
                <span class="points-history-filter-icon">
                  <i class="icon-base ti {{ $filter['icon'] }} icon-24px"></i>
                </span>
                <span class="points-history-filter-value">
                  {{ number_format((int) ($historyFilterCounts[$filter['value']] ?? 0)) }}
                </span>
              </span>
              <span class="points-history-filter-label">{{ $filter['label'] }}</span>
            </button>
          @endforeach
        </div>
      @else
        <div class="points-history-filters" aria-label="Task history summary">
          <div class="points-history-filter-card points-history-filter-success points-history-task-card">
            <span class="points-history-filter-top">
              <span class="points-history-filter-icon">
                <i class="icon-base ti tabler-checks icon-24px"></i>
              </span>
              <span class="points-history-filter-value">{{ number_format((int) ($taskHistorySummary['completed'] ?? 0)) }}</span>
            </span>
            <span class="points-history-filter-label">Completed tasks</span>
          </div>

          <div class="points-history-filter-card points-history-filter-points points-history-task-card">
            <span class="points-history-filter-top">
              <span class="points-history-filter-icon">
                <span class="points-history-points-mark">pts</span>
              </span>
              <span class="points-history-filter-value">{{ number_format((int) ($taskHistorySummary['points'] ?? 0)) }}</span>
            </span>
            <span class="points-history-filter-label">Task points</span>
          </div>
        </div>
      @endif
    </div>

    <div class="points-history-date-card {{ $historyTab === 'tasks' ? 'd-none' : '' }}">
      <div class="points-history-date">
        <label class="form-label small mb-1" for="points-history-date-{{ $this->getId() }}">Date range</label>
        <div class="input-group input-group-sm">
          <span class="input-group-text"><i class="ti tabler-calendar"></i></span>
          <input
            id="points-history-date-{{ $this->getId() }}"
            type="text"
            class="form-control"
            placeholder="This day to this day"
            value="{{ $this->historyDateValue }}"
            data-start-date="{{ $historyStartDate }}"
            data-end-date="{{ $historyEndDate }}"
            x-data
            x-init="window.w14InitPointsHistoryDateRange && window.w14InitPointsHistoryDateRange($el)"
          >
          @if($historyStartDate || $historyEndDate)
            <button type="button" class="btn btn-outline-secondary" wire:click="clearHistoryDateRange">
              <i class="ti tabler-x"></i>
            </button>
          @endif
        </div>
      </div>
    </div>
  </div>

  @if($historyTab === 'tasks')
    @if(count($taskHistoryRows))
      <div class="points-history-list">
        @foreach($taskHistoryRows as $row)
          <div class="points-history-card points-history-card-consequences" wire:key="task-history-row-{{ $row['pivot_id'] ?? $loop->index }}">
            <div class="points-history-main">
              <div class="points-history-heading">
                <span class="badge bg-label-info">{{ $row['source'] }}</span>
                <div class="points-history-name">{{ $row['title'] }}</div>
              </div>

              <div class="points-history-meta">
                @if(!empty($row['date']))
                  <span>{{ $row['date'] }}</span>
                @endif
                <span>{{ $row['subject'] }}</span>
              </div>
            </div>

            <div class="points-history-side">
              <div class="points-history-points text-success">+{{ $row['points'] }} pts</div>
              <i class="icon-base ti tabler-check icon-24px text-success"></i>
            </div>
          </div>
        @endforeach
      </div>

      @if(count($taskHistoryRows) < $taskHistoryTotal)
        <div class="text-center mt-3">
          <button type="button" class="btn btn-label-primary btn-sm" wire:click="loadMoreTaskHistory">
            Load 25 more
          </button>
        </div>
      @endif
    @else
      <div class="card border-0 shadow-sm p-4 text-center text-muted">
        No completed tasks match these filters.
      </div>
    @endif
  @elseif(count($sessionDisciplines))
    <div class="points-history-list">
      @foreach($sessionDisciplines as $row)
        @php
          $rowType = $row['type'] ?? 'Positive';
          $rowTypeLabel = $rowType === 'No Way' ? 'Red Flag' : $rowType;
          $isPositive = $rowType === 'Positive';
          $createdAt = \Carbon\Carbon::parse($row['created_at'])->format('d M');
          $historyClass = match($rowType) {
            'Slip' => 'points-history-card-slip',
            'No Way' => 'points-history-card-no-way',
            'Consequences' => 'points-history-card-consequences',
            default => 'points-history-card-positive',
          };
          $badgeClass = match($rowType) {
            'Slip' => 'bg-label-warning',
            'No Way' => 'bg-label-danger',
            'Consequences' => 'bg-label-info',
            default => 'bg-label-success',
          };
        @endphp

        <div class="points-history-card {{ $historyClass }}" wire:key="history-row-{{ $row['id'] ?? $loop->index }}">
          <div class="points-history-main">
            <div class="points-history-heading">
              <span class="badge {{ $badgeClass }}">{{ $rowTypeLabel }}</span>
              <div class="points-history-name">{{ $row['title'] }}</div>
            </div>

            @if(!empty($row['agreement_title']) || !empty($row['description']))
              <div class="points-history-note">
                @if(!empty($row['agreement_title']))
                  <span><strong>Agreement:</strong> {{ $row['agreement_title'] }}</span>
                @endif

                @if(!empty($row['description']))
                  <span>{{ !empty($row['agreement_title']) ? ' - ' : '' }}{!! nl2br(e($row['description'])) !!}</span>
                @endif
              </div>
            @endif

            <div class="points-history-meta">
              <span>{{ $createdAt }}</span>
              @if(!empty($row['subject_name']))
                <span>{{ $row['subject_name'] }}</span>
              @endif
            </div>
          </div>

          <div class="points-history-side">
            <div class="points-history-points {{ $isPositive ? 'text-success' : 'text-danger' }}">
              {{ $isPositive ? '+' : '-' }}{{ $row['points'] }} pts
            </div>

            @if(!empty($row['discipline_icon_path']))
              <img
                class="points-history-icon"
                src="{{ $this->assetPath($row['discipline_icon_path']) }}"
                alt=""
                loading="lazy"
              >
            @endif
          </div>
        </div>
      @endforeach
    </div>

    @if(count($sessionDisciplines) < $historyTotal)
      <div class="text-center mt-3">
        <button type="button" class="btn btn-label-primary btn-sm" wire:click="loadMoreHistory">
          Load 50 more
        </button>
      </div>
    @endif
  @else
    <div class="card border-0 shadow-sm p-4 text-center text-muted">
      No behavior records match these filters.
    </div>
  @endif
</div>
