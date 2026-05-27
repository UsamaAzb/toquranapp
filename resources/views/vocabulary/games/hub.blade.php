@php
  $configData = Helper::appClasses();
  $breadcrumb_links = [
    'Vocab Games' => null,
  ];
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Vocabulary Games')

@section('page-style')
  @vite(['resources/css/vocabulary-games.css'])
@endsection

@section('content')
  <div class="container-fluid">
    @livewire('student.vocabulary-games-hub')
  </div>
@endsection
