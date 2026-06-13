@extends('layouts/layoutMaster')

@section('title', $resource->title)

@section('content')
  <div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between gap-2">
      <div>
        <h5 class="mb-1">{{ $resource->title }}</h5>
        <div class="text-body-secondary">{{ $resource->original_filename ?: 'Library file' }}</div>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('teacher.get_library', $resource->folder ? ['folder' => (int) $resource->folder->id] : []) }}" class="btn btn-outline-secondary">
          Back
        </a>
        @if($fileAvailable)
          <a href="{{ $downloadUrl }}" class="btn btn-primary">Download</a>
        @endif
      </div>
    </div>
    <div class="card-body">
      @if($fileAvailable)
        <iframe src="{{ $fileUrl }}" class="w-100 border rounded" style="min-height:70vh" title="{{ $resource->title }}"></iframe>
      @else
        <div class="alert alert-warning mb-0">This Library file is not available.</div>
      @endif
    </div>
  </div>
@endsection
