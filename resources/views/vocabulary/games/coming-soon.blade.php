@extends('layouts/layoutMaster')

@section('title', 'Quranic Arabic Games')

@section('content')
<div class="row justify-content-center">
  <div class="col-xl-7 col-lg-8 col-md-10">
    <div class="card">
      <div class="card-body p-4 p-md-5 text-center">
        <div class="badge bg-label-warning mb-3">Coming Soon</div>
        <div class="d-inline-flex align-items-center justify-content-center bg-label-primary rounded mb-4" style="width: 4.5rem; height: 4.5rem;">
          <i class="icon-base ti tabler-device-gamepad-2 icon-36px"></i>
        </div>
        <h1 class="h3 mb-3">Quranic Arabic Games</h1>
        <p class="text-body-secondary mb-4">
          Short practice games will be added after the launch content is ready. For now, please continue from My Workplace and your assigned subjects.
        </p>
        <a href="{{ auth()->user()?->hasAnyRole(['student', 'parent']) ? route('student.workplace') : route('dashboard') }}" class="btn btn-primary">
          <i class="icon-base ti tabler-arrow-left me-1"></i>
          Back
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
