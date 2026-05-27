@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- <h1 class="h3">Edit Grade: {{ $grade->title }}</h1> -->
        <a href="{{ route('grades.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Grades
        </a>
    </div>

    <div class="card shadow-sm">
      <h5 class="card-header">Edit {{ $grade->title }}</h5>

        <div class="card-body">
            <form action="{{ route('grades.update', $grade) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                  <label for="title" class="form-label">Grade Name</label>

                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $grade->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="level_order" class="form-label">Grade Level</label>
                    <input type="number" class="form-control @error('level_order') is-invalid @enderror" id="level_order" name="level_order" value="{{ old('level_order', $grade->level_order) }}" min="1" max="12" required>
                    @error('level_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="code" class="form-label">Code</label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $grade->code) }}">
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                <div class="mb-3">
                    <label class="form-label">Subjects</label>
                    <div class="card">
                        <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                            @foreach($subjects as $subject)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="subjects[]" value="{{ $subject->id }}" id="subject-{{ $subject->id }}"
                                        {{ in_array($subject->id, old('subjects', $selectedSubjects)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="subject-{{ $subject->id }}">
                                        {{ $subject->title }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Update Grade
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
