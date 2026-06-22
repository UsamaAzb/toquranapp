@extends('layouts/layoutMaster')

@section('title', 'My Children')

@push('styles')
  <style>
    .w14-parent-page {
      --w14-parent-card-min: 17rem;
    }

    .w14-parent-hero {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 1.25rem;
      align-items: end;
    }

    .w14-parent-hero__eyebrow {
      color: var(--bs-primary);
      font-size: 0.78rem;
      font-weight: 700;
      letter-spacing: 0;
      text-transform: uppercase;
    }

    .w14-parent-hero__title {
      color: var(--bs-heading-color);
      font-size: clamp(1.8rem, 3vw, 2.75rem);
      font-weight: 700;
      line-height: 1.05;
      margin: 0.35rem 0 0;
    }

    .w14-parent-hero__copy {
      color: var(--bs-secondary-color);
      font-size: 1rem;
      margin: 0.75rem 0 0;
      max-width: 42rem;
    }

    .w14-parent-grid {
      display: grid;
      gap: 1rem;
      grid-template-columns: repeat(auto-fit, minmax(var(--w14-parent-card-min), 1fr));
    }

    .w14-parent-child-card {
      min-width: 0;
      overflow: hidden;
    }

    .w14-parent-child-card__media {
      align-items: center;
      background:
        radial-gradient(circle at 50% 35%, rgba(var(--bs-primary-rgb), 0.12), transparent 48%),
        var(--bs-body-bg);
      border-bottom: 1px solid var(--bs-border-color);
      display: flex;
      justify-content: center;
      min-height: 9rem;
      padding: 1.25rem;
    }

    .w14-parent-child-card__media img {
      height: clamp(6.25rem, 18vw, 8rem);
      max-width: 100%;
      object-fit: contain;
    }

    .w14-parent-child-card__body {
      display: grid;
      gap: 1rem;
      min-width: 0;
    }

    .w14-parent-child-card__name-row {
      align-items: flex-start;
      display: flex;
      gap: 0.75rem;
      justify-content: space-between;
      min-width: 0;
    }

    .w14-parent-child-card__name {
      color: var(--bs-heading-color);
      font-size: 1.35rem;
      font-weight: 700;
      line-height: 1.15;
      margin: 0;
      min-width: 0;
      overflow-wrap: anywhere;
    }

    .w14-parent-action-grid {
      display: grid;
      gap: 0.625rem;
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .w14-parent-action-grid .btn,
    .w14-parent-action-grid .dropdown > .btn {
      align-items: center;
      display: inline-flex;
      justify-content: center;
      min-height: 2.75rem;
      min-width: 0;
      white-space: nowrap;
      width: 100%;
    }

    .w14-parent-action-grid .dropdown {
      min-width: 0;
    }

    .w14-parent-open-workspace {
      justify-self: stretch;
    }

    .w14-parent-settings-menu {
      max-height: min(28rem, 72vh);
      min-width: min(24rem, calc(100vw - 2rem));
      overflow: auto;
    }

    .w14-parent-settings-row {
      align-items: center;
      display: flex;
      gap: 1rem;
      justify-content: space-between;
      min-width: 0;
      padding: 0.75rem 1rem;
    }

    .w14-parent-settings-row__name {
      font-weight: 600;
      min-width: 0;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    @media (max-width: 767.98px) {
      .w14-parent-hero {
        align-items: stretch;
        grid-template-columns: 1fr;
      }

      .w14-parent-hero__actions {
        justify-content: flex-start !important;
      }
    }

    @media (max-width: 430px) {
      .w14-parent-page {
        --w14-parent-card-min: 100%;
      }

      .w14-parent-action-grid {
        grid-template-columns: 1fr;
      }

      .w14-parent-action-grid .dropdown,
      .w14-parent-action-grid .dropdown > .btn,
      .w14-parent-action-grid .btn {
        width: 100%;
      }

      .w14-parent-settings-row {
        align-items: stretch;
        flex-direction: column;
      }
    }
  </style>
@endpush

@section('content')
  <livewire:parent.behavior-modal />

  @php
    $visibleStudentsCount = count($students);
    $reviewCountsCollection = collect($reviewCounts ?? []);
    $totalReviewCount = $reviewCountsCollection->sum();
  @endphp

  <div class="w14-parent-page">
    @if (session()->has('warning'))
      <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <section class="w14-parent-hero mb-4" aria-labelledby="children-title">
      <div>
        <div class="w14-parent-hero__eyebrow">My Deen Journey</div>
        <h1 class="w14-parent-hero__title" id="children-title">Welcome, {{ $parentFirstName }}.</h1>
        <p class="w14-parent-hero__copy">
          Review tasks, add points, follow rewards, and open each child's workspace from one calm place.
        </p>
      </div>

      <div class="w14-parent-hero__actions d-flex flex-wrap align-items-center justify-content-end gap-2">
        <span class="badge bg-label-primary rounded-pill px-3 py-2">
          {{ $visibleStudentsCount }} {{ \Illuminate\Support\Str::plural('child', $visibleStudentsCount) }}
        </span>
        <span class="badge {{ $totalReviewCount > 0 ? 'bg-label-warning' : 'bg-label-secondary' }} rounded-pill px-3 py-2">
          {{ $totalReviewCount }} in review
        </span>

        @if($visibleStudentsCount > 0)
          <div class="dropdown">
            <button
              type="button"
              class="btn btn-label-secondary btn-icon rounded-pill"
              data-bs-toggle="dropdown"
              data-bs-auto-close="outside"
              aria-expanded="false"
              aria-label="Trusted child settings"
            >
              <i class="ti tabler-settings"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end w14-parent-settings-menu">
              <h6 class="dropdown-header">Trusted child settings</h6>
              @foreach ($students as $stu)
                <div class="w14-parent-settings-row">
                  <span class="w14-parent-settings-row__name" title="{{ $stu->display_name }}">{{ $stu->first_name }}</span>
                  @livewire('parent.trusted-child-approval-setting', [
                    'studentId' => $stu->id,
                  ], key('trusted-child-'.$stu->id))
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </section>

    <x-browser-push-control context="parent" />

    @if ($visibleStudentsCount === 0)
      <section class="card">
        <div class="card-body text-center py-5">
          <div class="avatar mx-auto mb-3">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="ti tabler-users"></i>
            </span>
          </div>
          <h2 class="h5 mb-2">No children are visible yet.</h2>
          <p class="text-muted mb-0">When children are linked to this parent account, their workspaces will appear here.</p>
        </div>
      </section>
    @else
      <section class="w14-parent-grid" aria-label="Children">
        @foreach ($students as $stu)
          @php
            $teacherSubjectClassesId = $stu->teacher_subject_classes_id;
            $reviewCount = (int) $reviewCountsCollection->get($stu->id, 0);
            $reviewTone = $reviewCount > 0 ? 'warning' : 'secondary';
          @endphp

          <article class="card h-100 w14-parent-child-card">
            <div class="w14-parent-child-card__media" aria-hidden="true">
              <img src="{{ asset('assets/img/illustrations/happy_character.svg') }}" alt="">
            </div>

            <div class="card-body w14-parent-child-card__body">
              <div class="w14-parent-child-card__name-row">
                <div class="min-w-0">
                  <div class="text-uppercase text-muted small fw-semibold mb-1">My Deen Journey</div>
                  <h2 class="w14-parent-child-card__name" title="{{ $stu->display_name }}">{{ $stu->first_name }}</h2>
                </div>
                <span class="badge bg-label-{{ $reviewTone }} rounded-pill flex-shrink-0">
                  {{ $reviewCount }} review
                </span>
              </div>

              <div class="w14-parent-action-grid">
                <a
                  href="{{ route('parent.task-approvals', ['student' => $stu->id]) }}"
                  class="btn {{ $reviewCount > 0 ? 'btn-warning' : 'btn-label-secondary' }}"
                  aria-label="Review tasks for {{ $stu->first_name }}"
                >
                  <i class="ti tabler-checks me-1"></i>
                  Review
                </a>

                @livewire('parent.add-behavior-button', [
                  'studentId' => $stu->id,
                  'teacherSubjectClassesId' => $teacherSubjectClassesId ?? null,
                  'buttonClass' => 'btn btn-label-primary',
                  'iconClass' => 'ti tabler-plus',
                  'label' => 'Add points',
                  'showLabel' => true,
                ], key('parent-add-behavior-'.$stu->id))

                <a
                  href="{{ route('parent.reward-discpline', $stu->id) }}"
                  class="btn btn-label-info"
                  aria-label="Open points lab for {{ $stu->first_name }}"
                >
                  <i class="ti tabler-chart-bar me-1"></i>
                  Points Lab
                </a>

                <a
                  href="{{ route('student.journey.board', $stu->id) }}"
                  class="btn btn-label-success"
                  aria-label="Open rewards for {{ $stu->first_name }}"
                >
                  <i class="ti tabler-gift me-1"></i>
                  Rewards
                </a>
              </div>

              <a
                class="btn btn-primary w14-parent-open-workspace"
                href="{{ route('student.workplace', $stu->id) }}"
                aria-label="Open workspace for {{ $stu->first_name }}"
              >
                <i class="ti tabler-briefcase me-1"></i>
                Open workspace
              </a>
            </div>
          </article>
        @endforeach
      </section>
    @endif
  </div>
@endsection
