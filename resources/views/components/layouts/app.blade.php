@extends('layouts/layoutMaster')

@section('title', $title ?? 'Booking Admin')

@section('content')
  {{ $slot }}
@endsection
