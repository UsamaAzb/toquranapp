@extends('layouts/layoutMaster')

@section('title', 'Task Approval')

@section('content')
  <livewire:teacher.task-approval-work-view :student="$studentId" :subject="$subjectId" />
@endsection
