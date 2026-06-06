@extends('layouts/layoutMaster')

@section('title', 'Points Lab')



@section('content')
<livewire:teacher.behavior-modal />

<livewire:teacher.session-agreement-reword-header  :student-id="$student->id" :teacher-subject-id="$teacherSubjectClassId"/>

<div class="mb-5" style="padding: 20px">
    <livewire:ui.points-progress
    :student-id="$student->id"
    :pending-gift-id="$pendingGift->id ?? null"
    :last-reached-gift-id="$lastReached->id ?? null"
    :allow-reached-click="false"
    :circle-view="true"
    label="Reward Points"
    />
    </div>
   <livewire:teacher.reward-discipline-points
      :student-id="$student->id"
      :teacher-subject-classes-id="$teacherSubjectClassId"
    />


@endsection
