@extends('layouts/layoutMaster')

@section('title', 'Review Tasks')

@section('content')
  <livewire:parent.task-approval-work-view :student="$student->id" />
@endsection
