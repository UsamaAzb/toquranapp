@extends('layouts/layoutMaster')

@section('title', 'ConsequenceAgreement')



@section('content')


<livewire:teacher.session-agreement-reword-header  :student-id="$student_id" :teacher-subject-id="$teachersubjectid"/>
<div class="mb-5" style="padding: 20px">
    <livewire:ui.points-progress
    :student-id="$student_id"
    :pending-gift-id="$pendingGift->id ?? null"
    :last-reached-gift-id="$lastReached->id ?? null"
    :allow-reached-click="false"
    :circle-view="true"
 :teacher-subject-id="$teachersubjectid"
    label="Reward Points"
    />
    </div>
<livewire:admin.students.punishment-agreements-tabs :student-id="$student_id" :teacher-subject-id="$teachersubjectid"/>



@endsection