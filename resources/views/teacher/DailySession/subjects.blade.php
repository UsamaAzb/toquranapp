@extends('layouts/layoutMaster')

@section('title', 'Versioned Routines')
@section('meta_description', 'Choose a subject to manage Versioned Routine templates, versions, and student assignments.')

@push('styles')
<style>
  .subject-card {
    border: 1px solid var(--bs-border-color);
    box-shadow: 0 0.375rem 0.875rem rgba(47, 43, 61, 0.08);
    transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
  }

  .subject-card:hover,
  .subject-card:focus-within {
    transform: translateY(-3px);
    border-color: rgba(var(--bs-primary-rgb), 0.32);
    box-shadow: 0 0.625rem 1.5rem rgba(47, 43, 61, 0.12);
  }

  [data-bs-theme="dark"] .subject-card:hover,
  [data-bs-theme="dark"] .subject-card:focus-within {
    box-shadow: 0 0.625rem 1.5rem rgba(0, 0, 0, 0.32);
  }

  .subject-icon {
    inline-size: 2.75rem;
    block-size: 2.75rem;
    font-weight: 600;
    font-size: 0.875rem;
  }

  .subject-card-link {
    color: inherit;
  }

  .subject-card-link:focus-visible {
    outline: 3px solid rgba(var(--bs-primary-rgb), 0.28);
    outline-offset: 4px;
    border-radius: 1rem;
  }
</style>
@endpush

@section('content')

@php
    $colors = ['primary', 'success', 'danger', 'info', 'warning'];
    $role = null;
@endphp

@role('admin')
    @php $role = 'admin'; @endphp
@endrole

@role('teacher')
    @php $role = 'teacher'; @endphp
@endrole

@php $role ??= 'teacher'; @endphp

{{-- Page Masthead --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-6">
  <div>
    <h4 class="mb-1">Versioned Routines</h4>
    <p class="mb-0 text-body-secondary">Choose a subject to manage routine templates and student assignments.</p>
  </div>
  <div>
    <span class="badge bg-label-primary">{{ count($subjects_list) }} {{ str('subject')->plural(count($subjects_list)) }}</span>
  </div>
</div>

{{-- Subject Card Grid --}}
<div class="row g-4">
  @forelse($subjects_list as $i => $item)
    @php
        $colorClass = $colors[$i % count($colors)];
        $initial = mb_strtoupper(mb_substr($item->title, 0, 1));
    @endphp
    <div class="col-12 col-sm-6 col-xl-4">
      <a href="{{ route('daily-sessions.get_sessions', ['auth_role' => $role, 'subject' => $item->id]) }}"
         class="subject-card-link text-decoration-none"
         aria-label="Manage Versioned Routines for {{ $item->title }}">
        <div class="card subject-card h-100">
          <div class="card-body d-flex flex-column gap-4">
            {{-- Top row: icon + badge --}}
            <div class="d-flex align-items-start justify-content-between gap-3">
              <span class="subject-icon avatar-initial rounded bg-label-{{ $colorClass }} text-{{ $colorClass }} d-inline-flex align-items-center justify-content-center">
                {{ $initial }}
              </span>
              <span class="badge bg-label-secondary">Versioned Routines</span>
            </div>

            {{-- Subject title + description --}}
            <div>
              <h5 class="mb-1">{{ $item->title }}</h5>
              <p class="mb-0 text-body-secondary small">Task templates and student assignments</p>
            </div>

            {{-- CTA line --}}
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
