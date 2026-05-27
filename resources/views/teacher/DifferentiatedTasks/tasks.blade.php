@extends('layouts/layoutMaster')

@section('title', isset($subjectTitle) ? $subjectTitle.' - Differentiated Tasks' : 'Differentiated Tasks')
@section('meta_description', 'Create, configure, and activate Differentiated Tasks for this subject.')

@section('content')
  <livewire:teacher.differentiated-tasks-board :subject-id="$subjectid" />
@endsection
