@extends('layouts/layoutMaster')

@section('title', 'ConsequenceAgreement')



@section('content')
      <div class=" row justify-content-center">@include('layouts/progress_bar')</div>

{{--
<livewire:student.student-tabs-header  :student-id="$student_id" />


 <div class="mb-5" style="padding: 20px">
    <livewire:ui.points-progress
    :student-id="$student_id"
    :pending-gift-id="$pendingGift->id ?? null"
    :last-reached-gift-id="$lastReached->id ?? null"
    :allow-reached-click="true"
    :circle-view="true"
    label="Reward Points"
    />
    </div>--}}
<livewire:admin.students.punishment-agreements-tabs 
    :student-id="$student_id" 
    />



@endsection