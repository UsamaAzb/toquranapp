@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')
@section('content')
<div class="container-fluid">
    <div class="card">
        <h5 class="card-header">Students</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Grade</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($classes as $student)
                        <tr>
                            <td>{{ $student->id }}</td>
                            <td>{{ trim(($student->first_name ?? '').' '.($student->last_name ?? '')) ?: 'Unnamed student' }}</td>
                            <td>{{ $student->gradeLevel->title ?? '-' }}</td>
                            <td>{{ $student->status ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
