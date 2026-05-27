@extends('layouts/layoutMaster')

@section('title', 'Reward Discipline Points')



@section('content')

<livewire:parent.behavior-modal />

<div class="mb-5" style="padding: 20px">
    <livewire:ui.points-progress
    :student-id="$student->id"
    :pending-gift-id="$pendingGift->id ?? null"
    :last-reached-gift-id="$lastReached->id ?? null"
    :allow-reached-click="false"
    :circle-view="true"
    :show-reward-details-toggle="true"
    label="Reward Points"
    />
    </div>
   <livewire:teacher.reward-discipline-points
      :student-id="$student->id"
    />


@endsection
