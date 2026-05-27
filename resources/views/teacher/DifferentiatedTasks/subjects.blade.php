@extends('layouts/layoutMaster')

@section('title', 'Differentiated Tasks')
@section('meta_description', 'Choose a subject to manage scheduled Differentiated Tasks and task versions.')

@push('styles')
<style>
  .dt-subject-card {
    border: 1px solid var(--bs-border-color);
    box-shadow: 0 0.375rem 0.875rem rgba(47, 43, 61, 0.08);
    transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
  }

  .dt-subject-card:hover,
  .dt-subject-card:focus-within {
    border-color: rgba(var(--bs-primary-rgb), 0.32);
    box-shadow: 0 0.625rem 1.5rem rgba(47, 43, 61, 0.12);
    transform: translateY(-3px);
  }

  .dt-subject-icon {
    block-size: 2.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    inline-size: 2.75rem;
  }

  .dt-subject-card-link {
    color: inherit;
  }
</style>
@endpush

@section('content')
@php
  $colors = ['primary', 'success', 'danger', 'info', 'warning'];
  $role = 'teacher';
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6">
  <div>
    <h4 class="mb-1">Differentiated Tasks</h4>
    <p class="mb-0 text-body-secondary">Choose a subject to manage scheduled tasks, versions, and assignments.</p>
  </div>
  <span class="badge bg-label-primary">{{ count($subjects_list) }} {{ str('subject')->plural(count($subjects_list)) }}</span>
</div>

<div class="row g-4">
  @forelse($subjects_list as $i => $item)
    @php
      $colorClass = $colors[$i % count($colors)];
      $initial = mb_strtoupper(mb_substr($item->title, 0, 1));
    @endphp
    <div class="col-12 col-sm-6 col-xl-4">
      <a href="{{ route('differentiated-tasks.get_tasks', ['auth_role' => $role, 'subject' => $item->id]) }}"
         class="dt-subject-card-link text-decoration-none"
         aria-label="Manage Differentiated Tasks for {{ $item->title }}">
        <div class="card dt-subject-card h-100">
          <div class="card-body d-flex flex-column gap-4">
            <div class="d-flex align-items-start justify-content-between gap-3">
              <span class="dt-subject-icon avatar-initial rounded bg-label-{{ $colorClass }} text-{{ $colorClass }} d-inline-flex align-items-center justify-content-center">
                {{ $initial }}
              </span>
              <span class="badge bg-label-secondary">Differentiated Tasks</span>
            </div>
            <div>
              <h5 class="mb-1">{{ $item->title }}</h5>
              <p class="mb-0 text-body-secondary small">Scheduled task versions by student need</p>
            </div>
            <div class="d-flex align-items-center text-primary fw-medium mt-auto">
              <span>Manage tasks</span>
              <i class="ti tabler-arrow-right ms-2"></i>
            </div>
          </div>
        </div>
      </a>
    </div>
  @empty
    <div class="col-12">
      <div class="bg-lighter rounded p-4 d-flex align-items-start gap-3">
        <span class="avatar-initial rounded bg-label-warning d-inline-flex align-items-center justify-content-center p-2">
          <i class="ti tabler-alert-triangle"></i>
        </span>
        <div>
          <h6 class="mb-1">No subjects assigned</h6>
          <p class="mb-0 text-body-secondary">Subjects will appear here once they are assigned to your classes.</p>
        </div>
      </div>
    </div>
  @endforelse
</div>
@endsection
