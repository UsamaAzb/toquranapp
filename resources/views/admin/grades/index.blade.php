@php
$configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- <h1 class="h3">Grades Management</h1> -->
        <a href="{{ route('grades.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add New Grade
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
      <h5 class="card-header">Grades Management</h5>
      <div class="table-responsive text-nowrap">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Name</th>
                            <th>Subjects</th>
                            <th>Classes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($grades as $grade)
                            <tr>
                                <td>{{ $grade->level_order }}</td>
                                <td><span class="fw-medium">{{ $grade->title }}</span></td>
                                <td>{{ $grade->subjects->count() }}</td>
                                <td>{{ $grade->classes->count() }}</td>



                                  <td>
                                    <div class="dropdown">
                                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="icon-base ti tabler-dots-vertical"></i>
                                      </button>
                                      <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('grades.edit', $grade->id) }}"><i class="icon-base ti tabler-pencil me-1"></i>
                                          Edit</a>
                                        <a class="dropdown-item" href="javascript:void(0);"   onclick="event.preventDefault();
                                                  if(confirm('Are you sure you want to delete this grade?')) {
                                                      document.getElementById('delete-grade-{{ $grade->id }}').submit();
                                                  }"><i class="icon-base ti tabler-trash me-1"></i>
                                          Delete</a>
                                          <form id="delete-grade-{{ $grade->id }}" action="{{ route('grades.destroy', $grade) }}" method="POST" class="d-none">
                                              @csrf
                                              @method('DELETE')
                                          </form>
                                      </div>
                                    </div>
                                  </td>







                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No grades found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
