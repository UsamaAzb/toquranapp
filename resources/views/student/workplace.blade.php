@extends('layouts/layoutMaster')
@php
$configData = Helper::appClasses();
@endphp

@section('title', 'My Workplace')



<style>
  .w14-tooltip-behavior {
    position:absolute; z-index:5; top:-8px; left:50%;
    transform:translate(-50%,-100%);
    background:#2092ec; color:#fff; padding:.45rem .6rem;
    font-size:.85rem; border-radius:.6rem; box-shadow:0 6px 16px rgba(0,0,0,.08);
    opacity:0; pointer-events:none; transition:opacity .15s ease; white-space:nowrap;
  }
  .w14-tooltip-behavior.red  { background:#ef5350; }
  .w14-tooltip-behavior.red::after { border-top-color:#ef5350 !important; }
  .w14-tooltip-behavior::after{
    content:""; position:absolute; bottom:-6px; left:50%; transform:translateX(-50%);
    border:6px solid transparent; border-top-color:#2092ec;
  }
  .w14-donut-wrap { position:relative; display:inline-block; }

  .w14-subject-grid {
    align-items: stretch;
  }

  .w14-subject-card {
    position: relative;
    height: 100%;
    overflow: hidden;
    border: 1px solid rgba(108, 117, 125, 0.14);
    border-radius: 0.75rem;
    box-shadow: 0 0.45rem 1.2rem rgba(43, 50, 64, 0.08);
    transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
  }

  .w14-subject-card::before {
    content: "";
    position: absolute;
    inset-block: 0;
    inset-inline-start: 0;
    width: 0.35rem;
    background: var(--w14-subject-accent, #2092ec);
  }

  .w14-subject-card:hover,
  .w14-subject-card:focus-within {
    transform: translateY(-2px);
    border-color: color-mix(in srgb, var(--w14-subject-accent, #2092ec) 38%, transparent);
    box-shadow: 0 0.7rem 1.4rem rgba(43, 50, 64, 0.12);
  }

  [data-bs-theme="dark"] .w14-subject-card,
  .dark-style .w14-subject-card {
    border-color: color-mix(in srgb, var(--w14-subject-accent, #2092ec) 24%, rgba(255, 255, 255, 0.08));
    box-shadow: 0 0.45rem 1.2rem rgba(0, 0, 0, 0.22);
  }

  [data-bs-theme="dark"] .w14-subject-card:hover,
  [data-bs-theme="dark"] .w14-subject-card:focus-within,
  .dark-style .w14-subject-card:hover,
  .dark-style .w14-subject-card:focus-within {
    box-shadow: 0 0.7rem 1.4rem rgba(0, 0, 0, 0.3);
  }

  .w14-subject-card-link {
    display: flex;
    align-items: center;
    min-height: 7.6rem;
    color: inherit;
    text-decoration: none;
  }

  .w14-subject-card-link:hover {
    color: inherit;
  }

  .w14-subject-card .card-body {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr);
    align-items: center;
    gap: 1rem;
    width: 100%;
    padding: 1.25rem 1.35rem;
  }

  .w14-subject-task-badge {
    position: absolute;
    top: 0.7rem;
    inset-inline-end: 0.75rem;
    z-index: 1;
    min-width: 1.65rem;
    height: 1.65rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 0.45rem;
    border-radius: 999px;
    background: color-mix(in srgb, var(--w14-subject-accent, #2092ec) 14%, white);
    color: var(--w14-subject-accent, #2092ec);
    font-size: 0.78rem;
    font-weight: 800;
    line-height: 1;
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--w14-subject-accent, #2092ec) 22%, transparent);
  }

  .w14-subject-review-badge {
    position: absolute;
    bottom: 0.7rem;
    inset-inline-end: 0.75rem;
    z-index: 1;
    min-width: 1.65rem;
    height: 1.65rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 0.45rem;
    border-radius: 999px;
    background: color-mix(in srgb, #ff9f1c 16%, white);
    color: #d97912;
    font-size: 0.78rem;
    font-weight: 800;
    line-height: 1;
    box-shadow: inset 0 0 0 1px color-mix(in srgb, #ff9f1c 28%, transparent);
  }

  .w14-subject-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 0.85rem;
    background: color-mix(in srgb, var(--w14-subject-accent, #2092ec) 13%, white);
    color: var(--w14-subject-accent, #2092ec);
    font-size: 1.65rem;
  }

  [data-bs-theme="dark"] .w14-subject-icon,
  .dark-style .w14-subject-icon {
    background: color-mix(in srgb, var(--w14-subject-accent, #2092ec) 22%, #252a3d);
  }

  [data-bs-theme="dark"] .w14-subject-task-badge,
  .dark-style .w14-subject-task-badge {
    background: color-mix(in srgb, var(--w14-subject-accent, #2092ec) 28%, #252a3d);
  }

  [data-bs-theme="dark"] .w14-subject-review-badge,
  .dark-style .w14-subject-review-badge {
    background: color-mix(in srgb, #ff9f1c 28%, #252a3d);
  }

  .w14-subject-title {
    margin: 0;
    color: var(--bs-heading-color, #2b3240);
    font-size: 1.03rem;
    font-weight: 700;
    line-height: 1.28;
  }

  .w14-subject-tone-language { --w14-subject-accent: #2092ec; }
  .w14-subject-tone-wellbeing { --w14-subject-accent: #22b573; }
  .w14-subject-tone-math { --w14-subject-accent: #ff9f1c; }
  .w14-subject-tone-science { --w14-subject-accent: #7c5cff; }
  .w14-subject-tone-arts { --w14-subject-accent: #e05297; }
  .w14-subject-tone-humanities { --w14-subject-accent: #00a6a6; }
  .w14-subject-tone-default { --w14-subject-accent: #64748b; }

  @media (max-width: 575.98px) {
    .w14-subject-grid {
      row-gap: 0.85rem;
    }

    .w14-subject-card-link {
      min-height: 6.6rem;
    }

    .w14-subject-card .card-body {
      gap: 0.75rem;
      padding: 1rem;
    }

    .w14-subject-icon {
      width: 2.8rem;
      height: 2.8rem;
      border-radius: 0.75rem;
      font-size: 1.4rem;
    }
  }
  
   .w14-donut-responsive {
    width: 160px;
    height: 160px;
  }

  /*@media (min-width: 576px) {*/
  /*  .w14-donut-responsive {*/
  /*    width: 130px;*/
  /*    height: 130px;*/
  /*  }*/
  /*}*/

  /*@media (min-width: 992px) {*/
  /*  .w14-donut-responsive {*/
  /*    width: 160px;*/
  /*    height: 160px;*/
  /*  }*/
  /*}*/
  
  @media (min-width: 992px) and (max-width: 1100px) {
      .w14-donut-responsive {
      width: 135px;
      height: 135px;
    }
      .student_points_circle{
        width: 135px !important;
      height: 135px !important;
    }
}
 @media (min-width: 1200px) and (max-width: 1350px) {
      .w14-donut-responsive {
      width: 135px !important;
      height: 135px !important;
    }
       .student_points_circle{
        width: 135px !important;
      height: 135px !important;
    }
}

  @media (max-width: 370px) {
      .behavior_nomobile{
          display: none !important;
      }
       .behavior_mobile{
          display: block !important;
      }
  }

  @media (max-width: 575.98px) {
      .circle_nomobile{
          display: none !important;
      }
       .circle_mobile{
          display: block !important;
      }
  }

  .tq-workplace-progress .badge {
    width: 3rem;
    height: 3rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
  }

  .tq-workplace-progress .badge svg {
    display: none;
  }

  .tq-workplace-progress .badge .icon-base {
    font-size: 1.55rem;
  }

  .tq-workplace-progress .row.gx-0 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(8.75rem, 1fr));
    gap: 1rem 1.25rem;
  }

  .tq-workplace-progress .row.gx-0 > [class*="col-"] {
    width: auto !important;
    max-width: none;
    flex: initial;
    margin-bottom: 0 !important;
  }

  .tq-workplace-progress .badge.me-4 {
    margin-inline-end: 0.85rem !important;
  }

  .tq-workplace-progress .card-info {
    min-width: 0;
  }

  @media (min-width: 768px) and (max-width: 1199.98px) {
    .tq-workplace-welcome .tq-welcome-illustration {
      padding-inline-end: 1.25rem !important;
    }
  }

  @media (max-width: 575.98px) {
    .circle_mobile .small > div {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 0.25rem 0.5rem;
    }

    .circle_mobile .small .badge {
      width: auto !important;
      min-width: 4.7rem;
      flex: 0 0 auto;
    }

    .circle_mobile .small small {
      white-space: nowrap;
    }
  }
</style>

@section('content')

  
 @php
  // --- القيم القادمة من الكلاس/الكونترولر ---
  $pos = (int)($total_post_point ?? 0);
  $neg = (int)($total_negative_point ?? 0);

  $sum = max(0, $pos + $neg);
  // نتعامل مع حالة صفر (مافيش نقاط) علشان ما نقسمش على صفر
  $posRatio = $sum > 0 ? $pos / $sum : 0;
  $negRatio = $sum > 0 ? $neg / $sum : 0;

  // إعدادات الرسم
  $size   = 160;        // حجم الـ SVG
  $stroke = 22;         // سُمك الحلقة (أعرض شوية زي ما طلبتي)
  $r      = ($size / 2) - ($stroke / 2);
  $circ   = 2 * M_PI * $r;

  // أطوال القطاعين
  $posLen = $circ * $posRatio;       // طول الأخضر
  $negLen = $circ * $negRatio;       // طول الأحمر

  // ألوان
  $greenDark  = '#2092EC';           // Positive
  $redDark    = '#ef5350cf';           // Needs-work
  $ringBg     = '#e9ecef';           // خلفية فاتحة محايدة

  // نص الوسط (اختياري): نسبة الإيجابي من إجمالي السلوكيات
  $centerPct = $sum > 0 ? round($posRatio * 100) : 0;
  $totalReviewBySubject = (int) $student_subjects->sum(fn ($subject) => (int) ($subject->in_review_task_count ?? 0));
@endphp

<x-browser-push-control context="student" />

<div class="row g-6 mb-6">
    
      <div class="col-xl-4 col-lg-4 col-md-6 col-12">
    <div class="card h-100 tq-workplace-welcome">
      <div class="d-flex  row  card-body">
        <div class="col-7">
        
              <div class="text-uppercase text-primary small fw-semibold mb-1">My Deen Journey</div>
              <h5 class="mb-2">Welcome back,<span class="h4"> {{$student->first_name}} </span></h5>
              
        <p>Finish tasks, earn rewards, and collect points.</p>
      
            <!--<h5 class="card-title mb-0">Congratulations {{$student->first_name}}! 🎉</h5>-->
            
            <!--<h4 class="mb-2">You Have <span class="text-primary">{{$current_point}} Points</span> </h4>-->
            <!--<a href="javascript:;" class="btn btn-primary">View Sales</a>-->
         
        </div>
        <div class="col-5 text-center text-sm-left">
          <div class="tq-welcome-illustration pb-0 px-0 px-md-4">
            <img class="tq-welcome-avatar" src="{{ asset('assets/img/illustrations/happy_character.svg') }}" height="140" alt="view sales" />
          </div>
        </div>
      </div>
    </div>
  </div>
    
    
    
   <div class=" col-xl-4 col-lg-4 col-md-6 col-12 circle_nomobile" >
      <div class="card h-100">
          <div class="d-flex align-items-start  card-body pb-0">
               <div class="col-6">
            <h5 class="mb-1">Reward Progress</h5>
            <small class="text-body-secondary d-block mb-3">My Deen Journey rewards</small>

          
          <div class="  text-sm-left d-flex flex-column">
         <div class="small mb-4">
    <div><span class="badge bg-label-success  col-xl-5 col-lg-6 col-md-5 col-4" style="background:#18c37e">Currently</span> <small class="text-body-secondary"> {{ $current_point }} pts </small></div>
    <div class="mt-1"><span class="badge bg-label-warning  col-xl-5 col-lg-6 col-md-5 col-4" style="">Goal</span>  <small class="text-body-secondary"> {{ $target }} pts</small></div>
  </div>
  
    <!--<a href="{{url('/student/journey/board/'.$student->id)}}" type="button" class="btn-sm col-6 btn btn-primary waves-effect">Let's Go</a>-->

  
          </div>
         </div>
         
         <div class="col-6 text-end">
    <livewire:ui.points-progress
    :student-id="$student->id"
    :pending-gift-id="$pendingGift->id ?? null"
    :last-reached-gift-id="$lastReached->id ?? null"
    :allow-reached-click="true"
    :circle-view="true"
    :bar-view="false"
    label="Reward Points"
    />
    </div>
     </div>
     
     <div class="card-footer ">
    <a href="{{url('/student/journey/board/'.$student->id)}}" type="button" class="btn-sm col-6 btn btn-primary waves-effect">
      <i class="icon-base ti tabler-gift me-1"></i> My Rewards
    </a>
    </div>
     
    </div>
  </div> 
    
 
    
     <div class=" col-xl-4 col-lg-4 col-md-6 col-12 circle_mobile"style="display:none" >
      <div class="card h-100">
          <div class="d-flex align-items-start row card-body">
               <div class="col-lg-6 col-md-6 col-sm-6 col-12">
            <h5 class="mb-1">Reward Progress</h5>
            <small class="text-body-secondary d-block mb-3">My Deen Journey rewards</small>

          
          <div class="  text-sm-left d-flex flex-column">
         <div class="small mb-4">
    <div><span class="badge bg-label-success  col-xl-5 col-lg-6 col-md-5 col-4" style="background:#18c37e">Currently</span> <small class="text-body-secondary"> {{ $current_point }} pts </small></div>
    <div class="mt-1"><span class="badge bg-label-warning  col-xl-5 col-lg-6 col-md-5 col-4" style="">Goal</span>  <small class="text-body-secondary"> {{ $target }} pts</small></div>
  </div>
  
      

  
          </div>
         </div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-12 text-center mt-lg-0 mt-md-0 mt-sm-0 mt-5">

         
    <livewire:ui.points-progress
    :student-id="$student->id"
    :pending-gift-id="$pendingGift->id ?? null"
    :last-reached-gift-id="$lastReached->id ?? null"
    :allow-reached-click="true"
    :circle-view="true"
    :bar-view="false"
    label="Reward Points"
    />
    </div>
     </div>
     
     <div class="card-footer ">
    <a href="{{url('/student/journey/board/'.$student->id)}}" type="button" class="btn-sm col-12 btn btn-primary waves-effect">
      <i class="icon-base ti tabler-gift me-1"></i> My Rewards
    </a>
    </div>
     
    </div>
  </div> 
    
    
   

 @php
  // --- القيم القادمة من الكلاس/الكونترولر ---
  $pos = (int) ($total_post_point ?? 0);
  $neg = (int) ($total_negative_point ?? 0);

  // لو الـ negative بييجي بالسالب نخليه موجب للحساب والعرض
  $negAbs = abs($neg);

  // إجمالي النقاط
  $sum = $pos + $negAbs;

  // نسب مئوية (مع حماية القسمة على صفر)
  $posPct = $sum > 0 ? round(($pos / $sum) * 100, 1) : 0;
  $negPct = $sum > 0 ? round(($negAbs / $sum) * 100, 1) : 0;

  // اختياري: نخلي مجموع الـ widths = 100% بالظبط
  if ($sum > 0) {
      $negPct = round(100 - $posPct, 1);
  }
@endphp



       <div class=" col-xl-4 col-lg-4 col-md-12 col-12  " >
           
           @role('student')
<a href="{{url('/student/reward-discpline/'.$student->id)}}">
    @endrole
      @role('parent')
<a href="{{url('/parent/reward-discpline/'.$student->id)}}">
    @endrole
 <div class="card h-100">
      <div class="d-flex align-items-start row card-body">
        
            <h5 class="mb-1">My Points</h5>
            <small class="text-body-secondary d-block mb-3">Points follow-up</small>

    <div class="row">
        {{-- Positive --}}
        <div class="col-4">
          <div class="d-flex gap-2 align-items-center mb-0">
            <!--<span class="badge bg-label-info p-1 rounded">-->
            <!--  <i class="icon-base ti tabler-thumb-up icon-sm"></i>-->
            <!--</span>-->
            <svg  width="8px" height="8px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 31.955 31.955" style="enable-background:new 0 0 31.955 31.955;" xml:space="preserve"><g><path style="fill: #28c76f;" d="M27.25,4.655C20.996-1.571,10.88-1.546,4.656,4.706C-1.571,10.96-1.548,21.076,4.705,27.3 c6.256,6.226,16.374,6.203,22.597-0.051C33.526,20.995,33.505,10.878,27.25,4.655z" fill="#28c76f"></path><path style="fill: r#28c76f" d="M13.288,23.896l-1.768,5.207c2.567,0.829,5.331,0.886,7.926,0.17l-0.665-5.416 C17.01,24.487,15.067,24.5,13.288,23.896z M8.12,13.122l-5.645-0.859c-0.741,2.666-0.666,5.514,0.225,8.143l5.491-1.375 C7.452,17.138,7.426,15.029,8.12,13.122z M28.763,11.333l-4.965,1.675c0.798,2.106,0.716,4.468-0.247,6.522l5.351,0.672 C29.827,17.319,29.78,14.193,28.763,11.333z M11.394,2.883l1.018,5.528c2.027-0.954,4.356-1.05,6.442-0.288l1.583-5.137 C17.523,1.94,14.328,1.906,11.394,2.883z" fill="#28c76f"></path><circle style="fill: #28c76f" cx="15.979" cy="15.977" r="6.117" fill="#28c76f"></circle></g></svg>

            <h6 class="mb-0">Positive</h6>
          </div>

          <h5 class="mb-0 pt-1" style="font-size: 15px;">{{ $posPct }}%</h5>
          <small class="text-body-secondary">{{ $pos }} pts</small>
        </div>

        {{-- VS --}}
        <div class="col-4">
          <div class="divider divider-vertical">
            <div class="divider-text ">
              <span class="badge-divider-bg bg-label-secondary">VS</span>
            </div>
          </div>
        </div>

        {{-- Negative --}}
        <div class="col-4 text-end">
          <div class="d-flex gap-2 justify-content-end align-items-center mb-0">
              <svg  width="8px" height="8px"  version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 31.955 31.955" style="enable-background:new 0 0 31.955 31.955;" xml:space="preserve"><g><path style="fill: rgb(239, 83, 80);" d="M27.25,4.655C20.996-1.571,10.88-1.546,4.656,4.706C-1.571,10.96-1.548,21.076,4.705,27.3 c6.256,6.226,16.374,6.203,22.597-0.051C33.526,20.995,33.505,10.878,27.25,4.655z" fill="#030104"></path><path style="fill: rgb(239, 83, 80);" d="M13.288,23.896l-1.768,5.207c2.567,0.829,5.331,0.886,7.926,0.17l-0.665-5.416 C17.01,24.487,15.067,24.5,13.288,23.896z M8.12,13.122l-5.645-0.859c-0.741,2.666-0.666,5.514,0.225,8.143l5.491-1.375 C7.452,17.138,7.426,15.029,8.12,13.122z M28.763,11.333l-4.965,1.675c0.798,2.106,0.716,4.468-0.247,6.522l5.351,0.672 C29.827,17.319,29.78,14.193,28.763,11.333z M11.394,2.883l1.018,5.528c2.027-0.954,4.356-1.05,6.442-0.288l1.583-5.137 C17.523,1.94,14.328,1.906,11.394,2.883z" fill="#030104"></path><circle style="fill: rgb(239, 83, 80);" cx="15.979" cy="15.977" r="6.117" fill="#030104"></circle></g></svg>
            <h6 class="mb-0">Negative</h6>
            <!--<span class="badge bg-label-primary p-1 rounded">-->
            <!--  <i class="icon-base ti tabler-thumb-down icon-sm"></i>-->
            <!--</span>-->
          </div>

          <h5 class="mb-0 pt-1" style="font-size: 15px;">{{ $negPct }}%</h5>
          <small class="text-body-secondary">{{ $negAbs }} pts</small>
        </div>
      </div>

      {{-- Progress bar stacked --}}
      <div class="d-flex align-items-center mt-3 mb-2">
        <div class="progress w-100" style="height: 10px; border-radius: 999px; overflow: hidden;">
          <div
            class="progress-bar bg-success"
            style="width: {{ $posPct }}%"
            role="progressbar"
            aria-valuenow="{{ $posPct }}"
            aria-valuemin="0"
            aria-valuemax="100"
            title="Positive: {{ $posPct }}% ({{ $pos }} pts)"
          ></div>

          <div
            class="progress-bar bg-danger"
            style="width: {{ $negPct }}%"
            role="progressbar"
            aria-valuenow="{{ $negPct }}"
            aria-valuemin="0"
            aria-valuemax="100"
            title="Negative: {{ $negPct }}% ({{ $negAbs }} pts)"
          ></div>
        </div>
      </div>
</div>
</div>
</a>
</div>













<div class="col-lg-8 col-md-12">
    <div class="card h-100 tq-workplace-progress">
      <div class="card-header d-flex justify-content-between">
        <h5 class="card-title mb-0">My Progress</h5>
        <!--<small class="text-body-secondary">Updated 1 month ago</small>-->
      </div>
      <div class="card-body  row d-flex align-items-center">
        <div class="row gx-0">
          <div class="col-md-3 col-6 mb-lg-0 mb-xl-0 mb-md-0 mb-sm-0 mb-4">
            <div class="d-flex align-items-start">
                 <div class="badge p-2 bg-label-primary me-4 rounded">
            <i class="icon-base ti tabler-checklist"></i>
            <svg width="30px" height="30px" viewBox="-48 0 480 480" xmlns="http://www.w3.org/2000/svg"><path d="m344 48h-8v-8c-.027344-22.082031-17.917969-39.9726562-40-40h-256c-22.082031.0273438-39.9726562 17.917969-40 40v352c.0273438 22.082031 17.917969 39.972656 40 40h8v8c.027344 22.082031 17.917969 39.972656 40 40h256c22.082031-.027344 39.972656-17.917969 40-40v-352c-.027344-22.082031-17.917969-39.972656-40-40zm-72-32v36.6875l-10.34375-10.34375c-3.125-3.121094-8.1875-3.121094-11.3125 0l-10.34375 10.34375v-36.6875zm-256 376v-352c0-13.253906 10.746094-24 24-24h184v56c0 3.234375 1.949219 6.152344 4.9375 7.390625s6.429688.550781 8.71875-1.734375l18.34375-18.34375 18.34375 18.34375c2.289062 2.285156 5.730469 2.972656 8.71875 1.734375s4.9375-4.15625 4.9375-7.390625v-56h8c13.253906 0 24 10.746094 24 24v352c0 13.253906-10.746094 24-24 24h-256c-13.253906 0-24-10.746094-24-24zm352 48c0 13.253906-10.746094 24-24 24h-256c-13.253906 0-24-10.746094-24-24v-8h232c22.082031-.027344 39.972656-17.917969 40-40v-328h8c13.253906 0 24 10.746094 24 24zm0 0" fill="#000000" style="fill: rgb(32, 146, 236);"></path><path d="m127.449219 77.238281c-1.152344-3.125-4.117188-5.214843-7.449219-5.238281h-64c-4.417969 0-8 3.582031-8 8v64c0 4.417969 3.582031 8 8 8h64c4.417969 0 8-3.582031 8-8v-44.6875l21.65625-21.65625-11.3125-11.3125zm-15.449219 58.761719h-48v-48h48v4.6875l-16 16-10.34375-10.34375-11.3125 11.3125 16 16c3.125 3.121094 8.1875 3.121094 11.3125 0l10.34375-10.34375zm0 0" fill="#000000" style="fill: rgb(32, 146, 236);"></path><path d="m138.34375 178.34375-10.894531 10.894531c-1.152344-3.125-4.117188-5.214843-7.449219-5.238281h-64c-4.417969 0-8 3.582031-8 8v64c0 4.417969 3.582031 8 8 8h64c4.417969 0 8-3.582031 8-8v-44.6875l21.65625-21.65625zm-26.34375 69.65625h-48v-48h48v4.6875l-16 16-10.34375-10.34375-11.3125 11.3125 16 16c3.125 3.121094 8.1875 3.121094 11.3125 0l10.34375-10.34375zm0 0" fill="#000000" style="fill: rgb(32, 146, 236);"></path><path d="m120 288h-64c-4.417969 0-8 3.582031-8 8v64c0 4.417969 3.582031 8 8 8h64c4.417969 0 8-3.582031 8-8v-64c0-4.417969-3.582031-8-8-8zm-8 64h-48v-48h48zm0 0" fill="#000000" style="fill: rgb(32, 146, 236);"></path><path d="m160 120h80v16h-80zm0 0" fill="#000000" style="fill: rgb(32, 146, 236);"></path><path d="m160 88h48v16h-48zm0 0" fill="#000000" style="fill: rgb(32, 146, 236);"></path><path d="m160 232h80v16h-80zm0 0" fill="#000000" style="fill: rgb(32, 146, 236);"></path><path d="m160 200h48v16h-48zm0 0" fill="#000000" style="fill: rgb(32, 146, 236);"></path><path d="m160 336h80v16h-80zm0 0" fill="#000000" style="fill: rgb(32, 146, 236);"></path><path d="m160 304h48v16h-48zm0 0" fill="#000000" style="fill: rgb(32, 146, 236);"></path><path d="m224 304h16v16h-16zm0 0" fill="#000000" style="fill: rgb(32, 146, 236);"></path></svg>
            </div>
              <div class="card-info">
                <h5 class="mb-0 text-primary">{{ $AssignedTaskStudentCount ?? 0 }}</h5>
                <span class="text-dark">To Do Tasks</span>
              </div>
            </div>
              <!--<a href ="{{url('student/classes')}}" type="button" class="btn-sm mt-1 btn btn-primary waves-effect" >Let's Do It</a>-->
          </div>
          
          <div class="col-md-3 col-6 mb-lg-0 mb-xl-0 mb-md-0 mb-sm-0 mb-4">
            <div class="d-flex align-items-start">
<div class="badge p-2 bg-label-success  rounded me-4">
            <i class="icon-base ti tabler-circle-check"></i>
            
            <svg width="30px" height="30px" id="Layer_1" enable-background="new 0 0 512 512" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="m439.312 92.906-81.855-81.856c-1.313-1.313-3.093-2.05-4.95-2.05h-216.965c-3.866 0-7 3.134-7 7v50.904h-50.904c-3.866 0-7 3.134-7 7v422.096c0 3.866 3.134 7 7 7h298.821c3.866 0 7-3.134 7-7v-50.904h50.904c3.866 0 7-3.134 7-7v-340.241c-.001-1.856-.738-3.636-2.051-4.949zm-79.805-60.007 57.956 57.956h-57.956zm9.951 456.101h-284.82v-408.096h43.904v357.191c0 3.866 3.134 7 7 7h233.917v43.905zm-226.916-57.904v-408.096h202.965v74.855c0 3.866 3.134 7 7 7h74.855v326.24h-284.82zm257.333-277.63c0 3.866-3.134 7-7 7h-113.62c-3.866 0-7-3.134-7-7s3.134-7 7-7h113.62c3.866 0 7 3.135 7 7zm0 32.309c0 3.866-3.134 7-7 7h-113.62c-3.866 0-7-3.134-7-7s3.134-7 7-7h113.62c3.866 0 7 3.134 7 7zm-228.555-16.918c2.238-3.152 6.608-3.894 9.76-1.657l16.569 11.759 31.004-32.716c2.658-2.806 7.09-2.926 9.896-.266 2.806 2.659 2.925 7.089.266 9.896l-35.183 37.125c-1.366 1.441-3.217 2.185-5.083 2.185-1.409 0-2.826-.424-4.049-1.292l-21.523-15.274c-3.152-2.237-3.894-6.607-1.657-9.76zm228.555 75.81c0 3.866-3.134 7-7 7h-113.62c-3.866 0-7-3.134-7-7s3.134-7 7-7h113.62c3.866 0 7 3.134 7 7zm0 32.308c0 3.866-3.134 7-7 7h-113.62c-3.866 0-7-3.134-7-7s3.134-7 7-7h113.62c3.866 0 7 3.134 7 7zm0 58.892c0 3.866-3.134 7-7 7h-113.62c-3.866 0-7-3.134-7-7s3.134-7 7-7h113.62c3.866 0 7 3.134 7 7zm0 32.308c0 3.866-3.134 7-7 7h-113.62c-3.866 0-7-3.134-7-7s3.134-7 7-7h113.62c3.866 0 7 3.135 7 7zm-205.375-83.083-21.523-15.274c-3.152-2.237-3.895-6.607-1.657-9.76 2.238-3.152 6.608-3.894 9.76-1.657l16.569 11.759 31.004-32.716c2.658-2.806 7.09-2.926 9.896-.266 2.806 2.659 2.925 7.089.266 9.896l-35.183 37.125c-1.366 1.441-3.217 2.185-5.083 2.185-1.409-.001-2.826-.425-4.049-1.292zm47.5 43.286c2.806 2.659 2.925 7.089.266 9.896l-35.183 37.125c-1.366 1.441-3.217 2.185-5.083 2.185-1.409 0-2.826-.424-4.049-1.292l-21.522-15.274c-3.153-2.237-3.895-6.607-1.657-9.76 2.238-3.152 6.608-3.894 9.76-1.657l16.569 11.759 31.004-32.716c2.658-2.806 7.089-2.926 9.895-.266z" fill="#000000" style="fill: rgb(40, 199, 111);"></path></svg>
    
        </div>
        <div class="card-info">
                <h5 class="mb-0 text-success">{{ $CompletedTaskStudentCount ?? 0 }}</h5>
                <span class="text-dark">Finished Tasks</span>
              </div>

            </div>
                                    <!--<span class="badge   btn-sm bg-label-success waves-effect "  >Well Done</span>-->

          </div>
          <div class="col-md-3 col-6 mb-lg-0 mb-xl-0 mb-md-0 mb-sm-0 mb-4">
            <div class="d-flex align-items-start">
              <div class="badge p-2 bg-label-warning rounded me-4">
                <i class="icon-base ti tabler-clock-hour-4"></i>
              </div>
              <div class="card-info">
                <h5 class="mb-0 text-warning">{{ $totalReviewBySubject }}</h5>
                <span class="text-dark">In Review</span>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-6 mb-lg-0 mb-xl-0 mb-md-0 mb-sm-0 mb-4">
            <div class="d-flex align-items-start">
<div class="badge p-2 bg-label-info  rounded me-4">
            <i class="icon-base ti tabler-gift"></i>
            
            
           
            
            
            <svg width="30px" height="30px" id="Capa_1" enable-background="new 0 0 510.892 510.892" viewBox="0 0 510.892 510.892" xmlns="http://www.w3.org/2000/svg"><g><path d="m444.816 230.393h-165.466c-4.143 0-7.5 3.358-7.5 7.5s3.357 7.5 7.5 7.5h10.603v187.933c0 4.142 3.357 7.5 7.5 7.5s7.5-3.358 7.5-7.5v-187.933h132.363v238.632c0 6.543-5.323 11.867-11.866 11.867h-120.498v-30.604c0-4.142-3.357-7.5-7.5-7.5s-7.5 3.358-7.5 7.5v30.604h-50.508v-211.908c0-4.142-3.358-7.5-7.5-7.5s-7.5 3.358-7.5 7.5v211.908h-120.496c-6.543 0-11.867-5.323-11.867-11.867v-81.311c0-4.142-3.358-7.5-7.5-7.5s-7.5 3.358-7.5 7.5v81.311c0 14.814 12.053 26.867 26.867 26.867h321.501c14.814 0 26.866-12.052 26.866-26.867v-246.132c.001-4.142-3.357-7.5-7.499-7.5z" fill="#000000" style="fill: rgb(0, 186, 209);"></path><path d="m94.527 381.718c2.856 0 5.716-1.067 7.921-3.21l53.654-52.167c2.97-2.888 3.037-7.636.149-10.605-2.887-2.971-7.636-3.036-10.605-.149l-51.048 49.633-37.323-38.388c-1.092-1.123-1.681-2.604-1.659-4.17s.653-3.031 1.776-4.123l71.215-69.24c.002-.002.005-.004.007-.007l19.841-19.292 9.013-8.763 41.411 42.592-30.314 29.474c-2.97 2.888-3.037 7.636-.149 10.605 2.89 2.972 7.636 3.037 10.605.149l53.828-52.335 43.883-42.666c.001-.001.003-.003.004-.004l112.262-109.148c4.491-4.367 4.594-11.576.226-16.071l-39.857-40.994c-3.885-3.996-9.094-6.239-14.666-6.318-5.556-.065-10.842 2.018-14.838 5.903l-47.71 46.387c-.568-10.084-4.614-20.015-12.191-27.808l-46.52-47.846c-8.088-8.319-18.931-12.99-30.533-13.153-11.587-.152-22.571 4.201-30.89 12.289l-4.487 4.363c-24.265 23.592-25.505 62.072-3.332 87.173-4.256 1.341-8.177 3.656-11.47 6.857l-12.3 11.959c-3.351 3.258-5.737 7.138-7.169 11.28-24.467-22.877-62.972-22.722-87.239.872l-4.487 4.363c-8.319 8.088-12.99 18.931-13.153 30.533-.163 11.601 4.201 22.572 12.289 30.89l46.52 47.846c7.577 7.793 17.388 12.119 27.452 12.971l-47.708 46.385c-8.249 8.02-8.435 21.255-.415 29.503l39.857 40.994c2.225 2.287 5.186 3.436 8.15 3.436zm115.107-128.345-41.411-42.592 25.632-24.921 6.15-5.979c.002-.002.004-.004.007-.007l19.475-18.935 41.411 42.592-28.82 28.021zm120.685-200.193c1.123-1.092 2.619-1.675 4.17-1.659 1.566.022 3.03.652 4.123 1.776l37.323 38.387-104.282 101.39-41.411-42.592zm-172.331-25.769 4.487-4.363c5.446-5.295 12.648-8.15 20.223-8.045 7.595.107 14.694 3.165 19.989 8.611 10.544 10.845 10.765 28.026.503 39.114-2.813 3.041-2.629 7.786.411 10.599 3.042 2.813 7.786 2.628 10.599-.411 6.802-7.351 10.587-16.453 11.382-25.756l23.626 24.3c10.928 11.24 10.677 29.273-.558 40.204l-40.883 39.749c-1.274-4.676-3.712-8.981-7.184-12.551l-3.598-3.701 15.841-15.401c2.97-2.888 3.037-7.636.149-10.605-2.887-2.971-7.636-3.036-10.605-.149l-15.841 15.402-12.245-12.594 16.758-16.293c2.97-2.888 3.037-7.636.149-10.605-2.888-2.97-7.636-3.037-10.605-.149l-17.534 17.047-6.022-6.194c-8.981-9.238-13.828-21.419-13.647-34.302s5.368-24.926 14.605-33.907zm-27.101 105.988 12.299-11.959c2.718-2.643 6.242-3.958 9.762-3.958 3.653 0 7.303 1.416 10.044 4.236l26.834 27.6c5.383 5.536 5.259 14.418-.275 19.803l-6.036 5.868-6.268 6.094c-2.682 2.608-6.191 4.028-9.961 3.963-3.741-.053-7.237-1.559-9.845-4.241l-8.823-9.075c-.001-.001-.002-.002-.003-.003s-.002-.002-.003-.003l-18.005-18.518c-5.382-5.537-5.257-14.423.28-19.807zm-98.896 16.515 4.487-4.363c9.359-9.099 21.493-13.628 33.617-13.628 12.58 0 25.148 4.877 34.589 14.587l6.022 6.194-39.07 37.987c-5.446 5.295-12.625 8.159-20.223 8.045-7.595-.107-14.694-3.165-19.989-8.611s-8.152-12.628-8.045-20.223 3.166-14.693 8.612-19.988zm45.954 88.058-23.635-24.309c10.442-.586 20.212-4.855 27.783-12.217l38.295-37.234 12.245 12.594-15.841 15.401c-2.97 2.888-3.037 7.636-.149 10.605 1.47 1.513 3.423 2.272 5.378 2.272 1.884 0 3.77-.706 5.228-2.123l15.841-15.402 3.598 3.701c3.472 3.571 7.706 6.129 12.344 7.534l-40.887 39.753c-11.244 10.92-29.274 10.663-40.2-.575z" fill="#000000" style="fill: rgb(0, 186, 209);"></path><path d="m383.383 152.979c0-4.142-3.357-7.5-7.5-7.5s-7.5 3.358-7.5 7.5v7.72c0 4.142 3.357 7.5 7.5 7.5s7.5-3.358 7.5-7.5z" fill="#000000" style="fill: rgb(0, 186, 209);"></path><path d="m388.781 181.352c0 4.142 3.357 7.5 7.5 7.5h7.72c4.143 0 7.5-3.358 7.5-7.5s-3.357-7.5-7.5-7.5h-7.72c-4.142 0-7.5 3.357-7.5 7.5z" fill="#000000" style="fill: rgb(0, 186, 209);"></path><path d="m375.629 194.249c-4.143 0-7.5 3.358-7.5 7.5v7.72c0 4.142 3.357 7.5 7.5 7.5s7.5-3.358 7.5-7.5v-7.72c0-4.142-3.357-7.5-7.5-7.5z" fill="#000000" style="fill: rgb(0, 186, 209);"></path><path d="m347.51 173.597c-4.143 0-7.5 3.358-7.5 7.5s3.357 7.5 7.5 7.5h7.721c4.143 0 7.5-3.358 7.5-7.5s-3.357-7.5-7.5-7.5z" fill="#000000" style="fill: rgb(0, 186, 209);"></path><path d="m422.802 89.544c4.357 2.775 8.006 6.488 11.157 11.352 2.322 3.584 6.233 5.704 10.496 5.704.075 0 .152-.001.229-.002 4.444-.079 8.468-2.413 10.762-6.244 2.605-4.349 6.198-7.829 10.984-10.636 3.852-2.26 6.208-6.264 6.302-10.711.093-4.422-2.077-8.498-5.805-10.901 0 0-.001-.001-.001-.001-4.837-3.118-8.681-6.861-11.426-11.126-2.312-3.593-6.251-5.756-10.536-5.785-.029 0-.058 0-.088 0-4.246 0-8.171 2.102-10.512 5.632-2.899 4.371-6.848 8.208-11.734 11.403-3.614 2.363-5.757 6.346-5.731 10.655.025 4.336 2.231 8.321 5.903 10.66zm22.057-21.616c3.005 4 6.683 7.601 10.983 10.752-4.519 3.093-8.324 6.798-11.366 11.065-3.159-4.276-6.779-7.933-10.811-10.923 4.345-3.196 8.093-6.844 11.194-10.894z" fill="#000000" style="fill: rgb(0, 186, 209);"></path><path d="m486.394 107.059c-2.399-2.343-5.673-3.617-9.065-3.531-3.38.1-6.622 1.591-8.896 4.09l-25.775 28.332c-3.207 3.525-4.132 8.477-2.412 12.922 1.719 4.445 5.733 7.486 10.478 7.937l38.048 3.613c.39.037.78.056 1.169.056 3 0 5.944-1.087 8.224-3.066 2.563-2.225 4.124-5.441 4.281-8.82.764-16.409-4.635-30.383-16.052-41.533zm-29.28 35.29 20.624-22.669c6.493 7.141 9.708 15.544 9.775 25.556z" fill="#000000" style="fill: rgb(0, 186, 209);"></path><path d="m496.086 177.393c-2.605-2.126-6.016-3.119-9.35-2.721l-37.506 4.462c-4.706.56-8.626 3.678-10.229 8.138-1.604 4.46-.568 9.36 2.703 12.789l26.016 27.268h.001c2.339 2.451 5.622 3.855 9.012 3.855h.007c3.375-.001 6.642-1.401 8.963-3.839 11.19-11.754 16.202-25.681 14.896-41.395-.278-3.324-1.922-6.442-4.513-8.557zm-19.421 37.581-20.563-21.553 29.688-3.532c.198 9.405-2.804 17.656-9.125 25.085z" fill="#000000" style="fill: rgb(0, 186, 209);"></path></g></svg>

        </div>
        <div class="card-info">
                <h5 class="mb-0 text-info">{{$RedeemedGift ?? 0}}</h5>
                <span class="text-dark">Gifts Earned</span>
              </div>
            </div>
                      <!--<span class="btn-sm  btn bg-label-info waves-effect" >Celebrate </span>-->

          </div>
          <div class="col-md-3 col-6 mb-lg-0 mb-xl-0 mb-md-0 mb-sm-0 mb-4">
            <div class="d-flex align-items-start">
<div class="badge p-2 bg-label-warning  rounded me-4">
            <i class="icon-base ti tabler-star"></i>
            <svg width="30px" height="30px" id="Capa_1" enable-background="new 0 0 511.661 511.661" viewBox="0 0 511.661 511.661" xmlns="http://www.w3.org/2000/svg"><path d="m446.163 138.607h-66.543c6.622-7.626 10.644-17.568 10.644-28.437v-66.733c0-23.951-19.486-43.437-43.437-43.437h-6.258c-33.846 0-61.562 26.731-63.157 60.189-3.914-1.972-8.329-3.09-13.003-3.09h-17.155c-4.674 0-9.089 1.118-13.003 3.09-1.596-33.458-29.312-60.189-63.158-60.189h-6.258c-23.951 0-43.437 19.486-43.437 43.437v66.733c0 10.869 4.022 20.811 10.644 28.437h-66.543c-11.504 0-20.864 9.36-20.864 20.864v57.176c0 6.267 5.098 11.365 11.365 11.365h18.203v259.001c0 13.591 11.058 24.648 24.649 24.648h313.958c13.591 0 24.649-11.057 24.649-24.648v-259.001h18.203c6.267 0 11.365-5.098 11.365-11.365v-57.176c0-11.504-9.36-20.864-20.864-20.864zm-118.893 74.405h-20.689v-59.405h20.689zm-107.189 0v-59.405h6.747 20.426 17.155 20.426 6.747v59.405zm-35.689 0v-59.405h20.689v59.405zm-60.84-34.202c0-13.897 11.306-25.203 25.203-25.203h16.08 4.557v225.575l-19.546-9.845c-2.122-1.069-4.626-1.069-6.748 0l-19.546 9.845zm175.529-40.203h-9.282c2.302-4.155 3.616-8.93 3.616-14.007v-5.162h22.094c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5h-22.094v-17.564h53.412c10.869 0 20.811-4.022 28.437-10.644v33.94c0 15.68-12.757 28.437-28.437 28.437h-12.057zm-6.747-75.373c0-26.597 21.638-48.234 48.234-48.234h6.258c15.68 0 28.437 12.757 28.437 28.437s-12.757 28.437-28.437 28.437h-54.493v-8.64zm-45.081 8.866h17.155c7.724 0 14.007 6.283 14.007 14.007v38.493c0 7.723-6.283 14.007-14.007 14.007h-17.155c-7.724 0-14.007-6.284-14.007-14.007v-38.494c0-7.723 6.283-14.006 14.007-14.006zm-82.419-57.1h6.258c26.597 0 48.234 21.638 48.234 48.234v8.64h-16.274c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h15.193v17.564h-22.094c-4.142 0-7.5 3.358-7.5 7.5s3.358 7.5 7.5 7.5h22.094v5.162c0 5.077 1.315 9.851 3.616 14.007h-9.282-35.689-12.057c-15.68 0-28.437-12.757-28.437-28.437v-33.94c7.626 6.622 17.568 10.644 28.437 10.644h6.256c4.142 0 7.5-3.358 7.5-7.5s-3.358-7.5-7.5-7.5h-6.256c-15.68 0-28.437-12.757-28.437-28.437s12.758-28.437 28.438-28.437zm-105.2 144.471c0-3.233 2.631-5.864 5.864-5.864h51.966c-5.569 6.9-8.913 15.667-8.913 25.203v34.202h-48.917zm29.568 327.542v-259.001h19.35v156.362c0 4.127 2.098 7.883 5.612 10.048 3.513 2.164 7.813 2.348 11.499.492l20.809-10.481 20.809 10.481c1.688.851 3.504 1.272 5.315 1.272 2.143 0 4.278-.591 6.183-1.765 3.514-2.165 5.612-5.921 5.612-10.048v-156.361h28.189 1.739v197.032c0 4.142 3.358 7.5 7.5 7.5s7.5-3.358 7.5-7.5v-197.032h53.021v268.649h-53.02v-39.655c0-4.142-3.358-7.5-7.5-7.5s-7.5 3.358-7.5 7.5v39.655h-115.469c-5.32 0-9.649-4.328-9.649-9.648zm333.257 0c0 5.32-4.329 9.648-9.649 9.648h-115.469v-268.649h1.739 28.189v10.831c0 4.142 3.358 7.5 7.5 7.5s7.5-3.358 7.5-7.5v-85.236h4.557 16.08c13.897 0 25.203 11.306 25.203 25.203v124.372l-19.546-9.845c-2.122-1.069-4.626-1.069-6.748 0l-19.546 9.845v-32.376c0-4.142-3.358-7.5-7.5-7.5s-7.5 3.358-7.5 7.5v37.569c0 4.127 2.098 7.883 5.612 10.048 3.513 2.164 7.812 2.349 11.499.492l20.809-10.481 20.81 10.481c1.688.851 3.504 1.272 5.315 1.272 2.143 0 4.278-.591 6.183-1.765 3.514-2.165 5.612-5.921 5.612-10.048v-80.362h19.35zm29.568-274.001h-48.917v-34.202c0-9.537-3.344-18.303-8.913-25.203h51.966c3.233 0 5.864 2.631 5.864 5.864z" fill="#000000" style="fill: rgb(255, 167, 38);"></path></svg>
            
            <!--<i class="icon-base ti tabler-credit-card icon-28px"></i>-->
        </div>              <div class="card-info">
                <h5 class="mb-0 text-warning">{{$ReachedGift ?? 0}}</h5>
                <span class="text-dark">Awaiting Claim</span>
              </div>
            </div>
              <!--<a href ="{{url('student/journey/board/'.$student->id)}}" type="button" class=" btn mt-1 btn-sm btn-warning waves-effect" >Claim Now</a>-->

          </div>
          
        </div>
      </div>
    </div>

  
   </div>
  
  
  








  

  <div class="col-xl-4 col-lg-4">
    <div class="card h-100">
      <div class="d-flex align-items-start card-body">
        <div class="badge p-2 bg-label-warning rounded me-4">
          <i class="icon-base ti tabler-device-gamepad-2 icon-28px"></i>
        </div>
        <div>
          <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <h5 class="mb-0">Quranic Arabic Games</h5>
            <span class="badge bg-label-warning">Coming Soon</span>
          </div>
          <p class="mb-0 text-body-secondary">Short practice games will appear here after launch content is ready.</p>
        </div>
      </div>
    </div>
  </div>
  
  
  
  
  </div>
  
  
  
  
  
  

   <div class="row g-3 g-sm-4 mt-5 w14-subject-grid">
@foreach($student_subjects as $k=>$student_subject)
@php
  $subjectDisplay = $student_subject->subject_display ?? ['id' => 0, 'title' => 'Subject'];
  $subjectId = (int) ($subjectDisplay['id'] ?? 0);
  $subjectTitle = (string) ($subjectDisplay['title'] ?? 'Subject');
  $subjectVisual = $subjectDisplay['visual'] ?? ['icon' => 'ti tabler-school', 'tone' => 'default'];
  $subjectIcon = (string) ($subjectVisual['icon'] ?? 'ti tabler-school');
  $subjectTone = (string) ($subjectVisual['tone'] ?? 'default');
  $openTaskCount = (int) ($student_subject->open_task_count ?? 0);
  $inReviewTaskCount = (int) ($student_subject->in_review_task_count ?? 0);
  $subjectHref = route('student.sessions', ['student_subject_id' => $student_subject->id, 'student_id' => $student->id]);
@endphp

<div class="col-12 col-sm-6 col-lg-4 col-xl-3">
         <div class="card mb-0 w14-subject-card w14-subject-tone-{{ $subjectTone }}">
           @if($openTaskCount > 0)
           <span
             class="w14-subject-task-badge"
             aria-label="{{ trans_choice('{1} :count task to do|[2,*] :count tasks to do', $openTaskCount, ['count' => $openTaskCount]) }}"
           >{{ $openTaskCount }}</span>
           @endif
           @if($inReviewTaskCount > 0)
           <span
             class="w14-subject-review-badge"
             aria-label="{{ trans_choice('{1} :count task in review|[2,*] :count tasks in review', $inReviewTaskCount, ['count' => $inReviewTaskCount]) }}"
           >{{ $inReviewTaskCount }}</span>
           @endif
           <a href="{{ $subjectHref }}" class="w14-subject-card-link" aria-label="{{ __('Open :subject tasks', ['subject' => $subjectTitle]) }}">
               <div class="card-body">
                 <span class="w14-subject-icon" aria-hidden="true">
                   <i class="{{ $subjectIcon }}"></i>
                 </span>
                 <div class="min-w-0">
                 <div class="text-center img_box d-none">
             @if($subjectId == 1)
             <svg  width="45"height="45" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"><path style="fill:#E8EAE9;" d="M48,237.867c-19.2,0-35.2,38.4-35.2,86.4s16,86.4,35.2,86.4h458.667c-32-57.6-32-115.2,0-172.8H48z" /><path style="fill:#F3705A;" d="M512,410.667c0,7.467-5.333,12.8-12.8,12.8H48c-32,0-48-50.133-48-99.2s16-99.2,48-99.2h451.2 c7.467,0,12.8,5.333,12.8,12.8l0,0c0,7.467-5.333,12.8-12.8,12.8H48c-6.4,0-21.333,25.6-21.333,73.6s16,73.6,21.333,73.6h451.2 C506.667,397.867,512,403.2,512,410.667L512,410.667z"/><polygon style="fill:#FFD15C;" points="227.2,470.4 174.933,430.933 123.733,470.4 123.733,324.267 227.2,324.267 "/><path style="fill:#E8EAE9;" d="M36.267,55.467H400c38.4,0,70.4,30.933,70.4,70.4l0,0c0,38.4-30.933,70.4-70.4,70.4H36.267l0,0 C64,152.533,64,98.133,36.267,55.467L36.267,55.467z"/><path style="fill:#66C6B9;" d="M42.667,209.067H396.8c44.8,0,83.2-33.067,86.4-77.867c3.2-49.067-35.2-89.6-83.2-89.6H42.667 c-7.467,0-12.8,6.4-12.8,12.8l0,0c0,7.467,6.4,12.8,12.8,12.8H396.8c29.867,0,56.533,21.333,58.667,51.2 c3.2,34.133-23.467,61.867-56.533,61.867H42.667c-7.467,0-12.8,6.4-12.8,12.8l0,0C28.8,202.667,35.2,209.067,42.667,209.067z"/></svg>
               <!--<img src="https://toquran.org/public/front/images/4645292.png" alt="unit" class="wp-image-75238 col-md-7 img-fluid" style="width:30%">-->
             @elseif($subjectId == 15)
             
             <!-- icon666.com - MILLIONS OF FREE VECTOR ICONS --><svg width="45"height="45" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 511.999 511.999" style="enable-background:new 0 0 511.999 511.999;" xml:space="preserve"><g><path style="fill:#E93234;" d="M258.405,195.953c-1.318-2.174-3.65-3.53-6.192-3.599c-24.618-0.677-48.029-11.747-64.231-30.369 c-1.648-1.894-4.136-2.836-6.626-2.506c-2.49,0.329-4.648,1.885-5.746,4.143l-32.951,67.699c-1.23,2.528-0.94,5.533,0.753,7.778 c1.692,2.246,4.502,3.352,7.271,2.865l41.522-7.302l19.87,37.182c1.303,2.438,3.842,3.956,6.598,3.956c0.047,0,0.095,0,0.142-0.001 c2.811-0.053,5.356-1.678,6.586-4.206l33.333-68.486C259.848,200.821,259.723,198.127,258.405,195.953z"/><path style="fill:#E93234;" d="M366.732,231.32l-32.951-67.699c-1.099-2.258-3.257-3.814-5.746-4.143s-4.977,0.612-6.626,2.506 c-16.201,18.623-39.612,29.692-64.231,30.369c-2.542,0.07-4.873,1.426-6.192,3.599c-1.318,2.174-1.443,4.868-0.33,7.154 l33.334,68.486c1.23,2.528,3.774,4.153,6.586,4.206c0.048,0.001,0.095,0.001,0.143,0.001c2.756,0,5.295-1.517,6.597-3.956 l19.872-37.183l41.522,7.302c2.766,0.487,5.579-0.62,7.271-2.865C367.672,236.853,367.963,233.848,366.732,231.32z"/></g><g><path style="fill:#B32425;" d="M258.405,195.953c-1.318-2.174-3.65-3.53-6.192-3.599c-6.244-0.172-12.409-1.016-18.4-2.478 l-31.401,63.889l9.662,18.08c1.303,2.438,3.842,3.956,6.598,3.956c0.047,0,0.095,0,0.142-0.001 c2.811-0.053,5.356-1.678,6.586-4.206l33.333-68.486C259.848,200.821,259.723,198.127,258.405,195.953z"/><path style="fill:#B32425;" d="M335.739,237.925l22.97,4.039c2.766,0.487,5.579-0.62,7.271-2.865 c1.692-2.246,1.983-5.25,0.752-7.778l-32.951-67.699c-1.099-2.258-3.257-3.814-5.746-4.143s-4.977,0.612-6.626,2.506 c-4.578,5.262-9.733,9.92-15.326,13.912L335.739,237.925z"/></g><path style="fill:#DB9975;" d="M345.142,324.006l24.815-76.204c3.243-9.964,13.11-15.966,23.445-14.27 c6.018,0.987,11.308,4.528,14.514,9.715c3.206,5.188,4.008,11.504,2.2,17.328l-8.213,26.452c-0.369,1.187-1.028,2.265-1.917,3.134 l-42.5,41.515c-1.438,1.404-3.325,2.13-5.231,2.13c-1.36,0-2.727-0.37-3.947-1.126C345.382,330.861,344.076,327.281,345.142,324.006 z"/><path style="fill:#F7B081;" d="M352.254,333.805c-1.36,0-2.727-0.37-3.947-1.126c-2.925-1.818-4.232-5.397-3.165-8.672 l24.815-76.204c2.649-8.138,9.716-13.627,17.847-14.444l-26.859,94.936l-3.462,3.381 C356.048,333.079,354.159,333.805,352.254,333.805z"/><g><path style="fill:#DB9975;" d="M387.611,282.589l16.491-53.106c3.246-10.456,14.094-16.506,24.695-13.771 c5.45,1.406,10.132,5.04,12.844,9.972c2.712,4.931,3.274,10.832,1.542,16.186l-11.954,36.961c-0.807,2.494-2.859,4.384-5.41,4.982 c-2.55,0.598-5.23-0.183-7.061-2.056c-1.327-1.358-3.102-2.118-4.999-2.14c-1.93-0.025-3.692,0.695-5.049,2.022l-8.724,8.522 c-1.437,1.404-3.325,2.13-5.23,2.13c-1.343,0-2.695-0.361-3.903-1.1C387.931,289.403,386.596,285.86,387.611,282.589z"/><path style="fill:#DB9975;" d="M362.331,362.768l56.907-71.627c2.222-2.796,2.015-6.831-0.48-9.385 c-1.941-1.987-2.622-4.887-1.767-7.531l21.527-66.556c3.158-9.767,13.161-15.779,23.266-13.978 c5.791,1.031,10.881,4.493,13.967,9.501c3.085,5.008,3.888,11.111,2.203,16.747l-11.567,38.691 c-10.9,36.46-32.627,67.967-62.831,91.115l-30.814,23.616c-1.351,1.036-2.953,1.543-4.549,1.543c-1.947,0-3.884-0.757-5.339-2.238 C360.205,369.973,359.98,365.727,362.331,362.768z"/></g><g><path style="fill:#F7B081;" d="M387.611,282.589l16.491-53.106c2.467-7.947,9.33-13.337,17.133-14.271l-22.106,75.657 c-1.293,0.933-2.826,1.421-4.375,1.421c-1.343,0-2.695-0.361-3.903-1.1C387.931,289.403,386.596,285.86,387.611,282.589z"/><path style="fill:#F7B081;" d="M369.251,374.821c-0.352,0.05-0.706,0.085-1.059,0.085c-1.947,0-3.884-0.757-5.339-2.238 c-2.648-2.695-2.871-6.94-0.521-9.898l56.907-71.627c2.222-2.796,2.015-6.831-0.48-9.385c-1.941-1.987-2.622-4.887-1.767-7.531 l21.527-66.556c2.669-8.25,10.225-13.801,18.598-14.246L420.26,311.177L369.251,374.821z"/></g><path style="fill:#FFC89F;" d="M274.545,425.391c0.144-1.493,3.75-36.903,23.927-65.567c17.962-25.519,43.07-37.178,49.628-39.9 l50.154-48.99c8.705-8.504,22.706-8.34,31.209,0.366c0.288,0.295,0.567,0.596,0.834,0.903c6.514,7.456,6.345,18.656,0.093,26.334 l-62.198,76.369l53.101-52.636c8.862-8.784,15.446-19.598,19.182-31.503l8.657-27.586l-0.018,0.017 c1.051-2.915,2.04-5.862,2.934-8.853l15.798-52.843c0.016-0.053,0.032-0.105,0.049-0.158c3.42-10.584,13.882-17.059,24.882-15.395 c6.511,0.984,12.255,4.747,15.757,10.323c3.503,5.576,4.399,12.383,2.46,18.675l-23.641,76.717 c-1.207,3.918-2.407,7.916-3.622,11.963c-10.889,36.274-23.232,77.388-52.984,101.683l-55.076,44.976v53.589 c0,4.132-3.35,7.482-7.482,7.482h-86.197c-4.132,0-7.482-3.35-7.482-7.482v-77.768C274.51,425.868,274.522,425.629,274.545,425.391z "/><g><path style="fill:#FFB98A;" d="M430.389,298.537c6.252-7.677,6.423-18.878-0.093-26.334c-0.268-0.307-0.547-0.609-0.834-0.903 c-1.026-1.05-2.135-1.969-3.302-2.77l-59.779,75.62c-5.588,7.033-4.797,24.669,1.81,30.756L430.389,298.537z"/><polygon style="fill:#FFB98A;" points="368.192,374.905 368.192,374.905 368.195,374.901 "/><path style="fill:#FFB98A;" d="M508.535,196.272c-3.142-5.002-8.09-8.538-13.775-9.924l-29.89,96.866 c-11.85,38.453-23.022,81.611-54.189,107.061l-60.819,47.22v73.862h18.327c4.132,0,7.482-3.35,7.482-7.482v-53.589l55.076-44.976 c29.752-24.295,42.094-65.409,52.984-101.683c1.215-4.047,2.415-8.045,3.622-11.963l23.641-76.717 C512.934,208.656,512.037,201.849,508.535,196.272z"/></g><path style="fill:#DB9975;" d="M166.856,324.006l-24.816-76.204c-3.243-9.964-13.11-15.966-23.445-14.27 c-6.018,0.987-11.308,4.528-14.514,9.715c-3.206,5.188-4.008,11.504-2.2,17.328l8.213,26.452c0.369,1.187,1.028,2.265,1.917,3.134 l42.5,41.515c1.438,1.404,3.325,2.13,5.23,2.13c1.36,0,2.727-0.37,3.947-1.126C166.616,330.861,167.922,327.281,166.856,324.006z"/><path style="fill:#F7B081;" d="M159.744,333.805c1.36,0,2.727-0.37,3.947-1.126c2.925-1.818,4.232-5.397,3.165-8.672l-24.816-76.204 c-2.649-8.138-9.716-13.627-17.847-14.444l26.859,94.936l3.462,3.381C155.951,333.079,157.839,333.805,159.744,333.805z"/><g><path style="fill:#DB9975;" d="M124.387,282.589l-16.491-53.106c-3.246-10.456-14.094-16.506-24.695-13.771 c-5.45,1.406-10.132,5.04-12.844,9.972c-2.713,4.931-3.274,10.832-1.542,16.186l11.953,36.961c0.807,2.494,2.859,4.384,5.41,4.982 c2.55,0.598,5.231-0.183,7.061-2.056c1.327-1.358,3.102-2.118,4.999-2.14c1.931-0.025,3.692,0.695,5.049,2.022l8.724,8.522 c1.437,1.404,3.325,2.13,5.231,2.13c1.343,0,2.695-0.361,3.903-1.1C124.068,289.403,125.403,285.86,124.387,282.589z"/><path style="fill:#DB9975;" d="M149.667,362.768L92.76,291.142c-2.222-2.796-2.015-6.831,0.48-9.385 c1.941-1.987,2.622-4.887,1.767-7.531L73.48,207.67c-3.158-9.767-13.161-15.779-23.266-13.978 c-5.791,1.031-10.881,4.493-13.967,9.501c-3.085,5.008-3.888,11.111-2.203,16.747l11.567,38.691 c10.9,36.46,32.627,67.967,62.831,91.115l30.814,23.616c1.351,1.036,2.953,1.543,4.549,1.543c1.947,0,3.884-0.757,5.339-2.238 C151.794,369.973,152.017,365.727,149.667,362.768z"/></g><g><path style="fill:#F7B081;" d="M124.387,282.589l-16.491-53.106c-2.467-7.947-9.329-13.337-17.133-14.271l22.106,75.657 c1.293,0.933,2.826,1.421,4.375,1.421c1.343,0,2.695-0.361,3.903-1.1C124.068,289.403,125.403,285.86,124.387,282.589z"/><path style="fill:#F7B081;" d="M142.746,374.821c0.352,0.05,0.705,0.085,1.059,0.085c1.947,0,3.884-0.757,5.339-2.238 c2.648-2.695,2.871-6.94,0.521-9.898l-56.907-71.627c-2.222-2.796-2.015-6.831,0.48-9.385c1.941-1.987,2.622-4.887,1.767-7.531 l-21.527-66.556c-2.669-8.25-10.225-13.801-18.598-14.246l36.857,117.752L142.746,374.821z"/></g><path style="fill:#FFC89F;" d="M237.454,425.391c-0.144-1.493-3.75-36.903-23.927-65.567c-17.962-25.519-43.07-37.178-49.628-39.9 l-50.154-48.99c-8.705-8.504-22.706-8.34-31.209,0.366c-0.288,0.295-0.566,0.596-0.834,0.903 c-6.514,7.456-6.345,18.656-0.093,26.334l62.198,76.369l-53.101-52.636c-8.862-8.784-15.446-19.598-19.182-31.503l-8.657-27.586 l0.018,0.017c-1.051-2.915-2.04-5.862-2.934-8.853l-15.798-52.843c-0.016-0.053-0.032-0.105-0.049-0.158 c-3.42-10.584-13.882-17.059-24.882-15.395c-6.511,0.984-12.255,4.747-15.757,10.323c-3.503,5.576-4.399,12.383-2.46,18.675 l23.641,76.717c1.207,3.918,2.407,7.916,3.622,11.963c10.889,36.274,23.233,77.388,52.984,101.683l55.076,44.976v53.589 c0,4.132,3.35,7.482,7.482,7.482h86.197c4.132,0,7.482-3.35,7.482-7.482v-77.768C237.489,425.868,237.477,425.629,237.454,425.391z" /><path style="fill:#FFB98A;" d="M213.527,359.823c-17.962-25.519-43.07-37.178-49.628-39.9l-50.154-48.99 c-8.705-8.504-22.706-8.34-31.209,0.366c-0.288,0.295-0.566,0.597-0.834,0.903c-0.003,0.003-0.005,0.007-0.008,0.01l60.314,62.091 c0,0,28.399,10.433,47.667,37.808c19.268,27.375,22.597,61.977,22.597,61.977v55.577v21.692h17.735c4.132,0,7.482-3.35,7.482-7.482 v-77.768c0-0.239-0.012-0.478-0.035-0.716C237.311,423.897,233.704,388.488,213.527,359.823z"/><path style="fill:#FFD039;" d="M254.696,0.641c-56.991,0-103.356,46.365-103.356,103.356s46.365,103.356,103.356,103.356 s103.356-46.365,103.356-103.356S311.686,0.641,254.696,0.641z"/><path style="fill:#F5BA3D;" d="M254.696,0.641c-4.945,0-9.809,0.357-14.57,1.032c50.115,7.103,88.786,50.279,88.786,102.325 s-38.671,95.222-88.786,102.325c4.763,0.675,9.625,1.032,14.57,1.032c56.991,0,103.356-46.365,103.356-103.356 S311.686,0.641,254.696,0.641z"/><path style="fill:#E93234;" d="M307.947,88.729c-1.427-4.389-5.15-7.527-9.716-8.19l-22.624-3.288l-10.118-20.501 c-2.043-4.138-6.178-6.709-10.794-6.709c-4.615,0-8.751,2.571-10.794,6.709l0,0L233.783,77.25l-22.623,3.288 c-4.567,0.662-8.291,3.802-9.717,8.191c-1.427,4.39-0.259,9.118,3.046,12.34l16.371,15.958l-3.865,22.532 c-0.78,4.548,1.054,9.059,4.789,11.773c3.734,2.713,8.59,3.064,12.677,0.917l20.235-10.639l20.237,10.639 c1.776,0.934,3.699,1.395,5.612,1.395c2.485,0,4.954-0.779,7.064-2.311c3.733-2.713,5.568-7.224,4.788-11.773l-3.864-22.532 l16.372-15.959C308.207,97.847,309.374,93.119,307.947,88.729z"/></svg>
                            <!--<img src="https://toquran.org/public/front/images/4645292.png" alt="unit" class="wp-image-75238 col-md-7 img-fluid" style="width:30%">-->

                @endif
             </div>
                 <p class="w14-subject-title">{{ $subjectTitle }}</p>
                 </div>
               </div>
             </a>
             </div>
 </div>


@endforeach

  </div>

   
  
  
  
  
      <!--hero_section-->
  {{--<div class="card p-0 mb-6">
    <div class="card-body d-flex flex-column flex-md-row justify-content-between p-0 pt-6">
      <div class="app-academy-md-25 card-body py-0 pt-6 ps-12">
        <img src="{{ asset('assets/img/illustrations/bulb-' . $configData['theme'] . '.png') }}" class="img-fluid app-academy-img-height scaleX-n1-rtl" alt="Bulb in hand" data-app-light-img="illustrations/bulb-light.png" data-app-dark-img="illustrations/bulb-dark.png" height="90" />
      </div>
      <div class="app-academy-md-50 card-body d-flex align-items-md-center flex-column text-md-center mb-6 py-6">
        <span class="card-title mb-4 lh-lg px-md-12 h4 text-heading">
          Education, talents, and career<br />
          opportunities. <span class="text-primary text-nowrap">All in one place</span>.
        </span>
        <p class="mb-4">
          Grow your skill with the most reliable online courses and certifications in<br />
          marketing, information technology, programming, and data science.
        </p>
        <div class="d-flex align-items-center justify-content-between app-academy-md-80">
          <input type="search" placeholder="Find your course" class="form-control me-4" />
          <button type="submit" class="btn btn-primary btn-icon"><i class="icon-base ti tabler-search icon-22px"></i></button>
        </div>
      </div>
      <div class="app-academy-md-25 d-flex align-items-end justify-content-end">
        <img src="{{ asset('assets/img/illustrations/pencil-rocket.png') }}" alt="pencil rocket" height="188" class="scaleX-n1-rtl" />
      </div>
    </div>
  </div>--}}
  
  
  </div>
  
 <script>
(function () {
  const wrap   = document.getElementById('sbDonutWrap');
  const tip    = document.getElementById('sbTooltip');
  const hitAll = document.getElementById('sbHitAll');

  const pos = {{ $pos }};
  const neg = {{ $neg }};
  const sum = {{ $sum }};
  const posRatio = {{ $posRatio }};
  const posAngle = 360 * posRatio;            // حد فاصل بين الأخضر والأحمر

  const txtPos = `Positive points: ${pos}`;
  const txtNeg = `Needs-work points: ${neg}`;

  function showTip(text, isRed){
    tip.textContent = text;
    if (isRed) tip.classList.add('red'); else tip.classList.remove('red');
    tip.style.opacity = '1';
  }
  function hideTip(){ tip.style.opacity = '0'; }

  function angleFromCenter(evt, svgEl){
    const rect = svgEl.getBoundingClientRect();
    const cx = rect.left + rect.width/2;
    const cy = rect.top  + rect.height/2;
    const x = evt.clientX - cx;
    const y = evt.clientY - cy;
    let deg = Math.atan2(y, x) * 180 / Math.PI; // -180..180
    if (deg < 0) deg += 360;
    deg = (deg + 90) % 360;                      // مواءمة تدوير الدائرة
    return deg;
  }

  // أظهر الرسالة المناسبة حسب زاوية المؤشر
  hitAll.addEventListener('mousemove', (e) => {
    if (sum === 0) { hideTip(); return; }
    const deg = angleFromCenter(e, hitAll);
    if (deg <= posAngle) showTip(txtPos, false);
    else                 showTip(txtNeg, true);
  });
  hitAll.addEventListener('mouseenter', () => { if (sum>0) tip.style.opacity='1'; });
  hitAll.addEventListener('mouseleave', hideTip);

  // للموبايل
  hitAll.addEventListener('click', (e) => {
    if (sum === 0) return;
    const deg = angleFromCenter(e, hitAll);
    if (deg <= posAngle) showTip(txtPos, false);
    else                 showTip(txtNeg, true);
  });
  document.addEventListener('click', (e)=> { if (!wrap.contains(e.target)) hideTip(); });
})();
</script> 
  
  
  @endsection
