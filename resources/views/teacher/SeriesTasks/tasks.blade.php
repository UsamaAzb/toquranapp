@extends('layouts/layoutMaster')

@section('title', isset($subjectTitle) ? $subjectTitle.' - Series Tasks' : 'Series Tasks')
@section('meta_description', 'Create, configure, and activate Series Tasks for this subject.')

@section('content')
  <livewire:teacher.series-tasks-board :subject-id="$subjectid" />
@endsection
