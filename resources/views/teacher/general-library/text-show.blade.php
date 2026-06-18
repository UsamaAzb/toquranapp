@extends('layouts/layoutMaster')

@section('title', $resource->title)

@section('content')
  <div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between gap-2">
      <div>
        <h5 class="mb-1">{{ $resource->title }}</h5>
        <div class="text-body-secondary">{{ $resource->description ?: 'Library text source' }}</div>
      </div>
      <a href="{{ route('teacher.get_library', $resource->folder ? ['folder' => (int) $resource->folder->id] : []) }}" class="btn btn-outline-secondary">
        Back
      </a>
    </div>
    <div class="card-body">
      <div class="border rounded bg-body-tertiary p-4" style="white-space: pre-wrap; line-height: 1.9;">
        {{ $resource->text_content ?: $resource->description }}
      </div>
    </div>
  </div>
@endsection
