@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4">My Classes</h1>

    <div class="row">
        @forelse(Auth::user()->teacherClasses as $class)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{{ $class->title }}</h5>
                        <h6 class="card-subtitle mb-2 text-muted">Grade {{ $class->grade->order }} - {{ $class->academic_year }}</h6>

                        <div class="d-flex justify-content-between my-3">
                            <div>
                                <span class="badge bg-info">{{ $class->subjects->count() }} Subjects</span>
                            </div>
                            <div>
                                <span class="badge bg-secondary">{{ $class->students->count() }} Students</span>
                            </div>
                        </div>

                        <p class="card-text">{{ Str::limit($class->title, 100) }}</p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="{{ route('teacher.classes.show', $class) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-eye me-1"></i> View Class
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    You are not assigned to any classes yet.
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
