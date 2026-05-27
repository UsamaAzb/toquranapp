@extends('layouts/layoutMaster')

@section('title', isset($subjectTitle) ? $subjectTitle.' - Versioned Routines' : 'Versioned Routines')
@section('meta_description', 'Create, review, assign, and publish Versioned Routine templates for this subject.')








@section('content')



    <livewire:teacher.automated-tasks-board :subject-id="$subjectid" />


@endsection
