@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Grade Details: {{ $grade->title }}</h1>
        <div>
            <a href="{{ route('grades.edit', $grade) }}" class="btn btn-primary me-2">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <a href="{{ route('grades.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Grades
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Grade Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 30%">Name:</th>
                            <td>{{ $grade->title }}</td>
                        </tr>
                        <tr>
                            <th>Level:</th>
                            <td>{{ $grade->level_order }}</td>
                        </tr>
                        <tr>
                            <th>Code:</th>
                            <td>{{ $grade->code ?? 'No code assigned' }}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $grade->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $grade->updated_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Subjects ({{ $grade->subjects->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($grade->subjects->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($grade->subjects as $subject)
                                        <tr>
                                            <td>{{ $subject->code }}</td>
                                            <td>{{ $subject->title }}</td>
                                            <td>{{ Str::limit($subject->description, 50) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No subjects assigned to this grade.</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Classes ({{ $grade->classes->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($grade->classes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Academic Year</th>
                                        <th>Students</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($grade->classes as $class)
                                        <tr>
                                            <td>{{ $class->title }}</td>
                                            <td>{{ $class->academicYear->title ?? $class->academic_year_id }}</td>
                                            <td>{{ $class->students->count() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No classes assigned to this grade.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
