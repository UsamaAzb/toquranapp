@php
  $pct = $pctNormalized ?? 0;
@endphp
@php
  $clickable = $allowReachedClick && !empty($lastReachedGiftId);
@endphp
<div wire:poll.3s.visible="refreshProgressIfChanged">
    @once
    <style>
    .w14-bar-layout{
        display:flex;
        gap:0.75rem;
        align-items:center;
        --w14-points-track-bg: rgba(var(--bs-primary-rgb), 0.16);
        --w14-points-label-bg: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 92%, var(--bs-primary));
        --w14-points-label-color: var(--bs-heading-color);
        --w14-points-label-border: rgba(var(--bs-primary-rgb), 0.18);
        --w14-points-label-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    [data-bs-theme="dark"] .w14-bar-layout {
        --w14-points-track-bg: rgba(var(--bs-primary-rgb), 0.28);
        --w14-points-label-bg: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 88%, white 12%);
        --w14-points-label-color: var(--bs-heading-color);
        --w14-points-label-border: rgba(var(--bs-primary-rgb), 0.26);
        --w14-points-label-shadow: 0 4px 14px rgba(0, 0, 0, 0.24);
    }
    .point_ratio{
        white-space: nowrap !important;
    }
    .student_points_circle{
        width:120px;
        height:120px;
    }
  .w14-tooltip {
    position:absolute; z-index:5; top:-8px; left:50%;
    transform:translate(-50%,-100%);
    background: var(--bs-success) !important; color: var(--bs-white); padding:.4rem .6rem;
    font-size:.85rem; border-radius:.6rem; box-shadow:0 6px 16px rgba(0,0,0,.08);
    opacity:0; pointer-events:none; transition:opacity .15s ease;
    white-space:nowrap;
  }
  .w14-tooltip::after{
    content:""; position:absolute; bottom:-6px; left:50%; transform:translateX(-50%);
    border:6px solid transparent; border-top-color: var(--bs-success) !important;
  }
  .w14-donut-wrap { position:relative; display:inline-block; }
  
  
 
  /* لو عندك style قديم سيبيه، بس زوّدي دول */
  .w14-bar-wrap {
    position: relative;
    width: 100%;
    /*max-width: 420px;    */
  }

  .w14-bar-track {
    width: 100%;
    height: 16px;
    border-radius: 999px;
    overflow: hidden;
    background: var(--w14-points-track-bg) !important;
  }

  .w14-bar-segment {
    height: 100%;
  }

  .w14-bar-tooltip {
    position:absolute;
    z-index:5;
    bottom: 100%;
    left:50%;
    transform:translate(-50%,-8px);
    background:var(--bs-primary);      /* لون التولتيب – عدليه لو حابة */
    color:#fff;
    padding:.35rem .6rem;
    font-size:.8rem;
    border-radius:.6rem;
    box-shadow:0 6px 16px rgba(0,0,0,.08);
    opacity:0;
    pointer-events:none;
    transition:opacity .15s ease;
    white-space:nowrap;
  }
  .w14-bar-tooltip::after{
    content:"";
    position:absolute;
    top:100%;
    left:50%;
    transform:translateX(-50%);
    border:6px solid transparent;
    border-top-color:var(--bs-primary);
  }

  .w14-bar-hit {
    position:absolute;
    top:0;
    bottom:0;
    cursor:pointer;
  }
  @media (max-width: 900px) {
      .w14-bar-layout {
          flex-direction: column;
          align-items: stretch;
          gap: 0.5rem;
          width: 100%;
      }
      .content-bar-mobile {
          gap: 0.5rem !important;
          align-items: stretch !important;
      }
      .point_ratio,
      .mobile_point_ratio {
          white-space: nowrap !important;
          width: fit-content !important;
          align-self: flex-start !important;
          padding: 0.42rem 0.8rem !important;
          color: var(--w14-points-label-color) !important;
          background: var(--w14-points-label-bg) !important;
          border: 1px solid var(--w14-points-label-border) !important;
          border-radius: 999px !important;
          box-shadow: var(--w14-points-label-shadow) !important;
          line-height: 1.15 !important;
          font-size: 0.95rem !important;
          font-weight: 600 !important;
      }
      .w14-bar-wrap {
          width: 100%;
      }
      .w14-bar-track {
          height: 14px;
      }
  }

  @media (min-width: 551px) and (max-width: 900px) {
      .mobile_point_ratio {
          display: none !important;
      }
      .point_ratio {
          display: inline-flex !important;
      }
  }

  @media (max-width: 500px) {
      .mobile_point_ratio {
          display: inline-flex !important;
      }
      .point_ratio {
          display: none !important;
      }
      .w14-bar-layout {
          gap: 0.35rem;
      }
}
</style>
@endonce

  
  
  

     @if($barView)
     @php
  // نضمن القيم
  $pct     = max(0, min(100, (float)($pctNormalized ?? 0)));
  $current = (int)($current ?? 0);
  $target  = (int)($total ?? 0);
  $remain  = max(0, $target - $current);

  // ألوان البار (نفس ألوان الدائرة)
  $greenDark  = 'var(--bs-primary)';
  $greenLight = $greenLight ?? '#bfeed3';
@endphp

<div class="w14-bar-layout content-bar-mobile">
     <small class="mobile_point_ratio card" style="display:none">   {{ $current }} / {{ $target }} points
     
     </small>
     <span class="point_ratio fw-semibold">  
     
     {{ $current }} / {{ $target }} points
     
     </span>
  
  <div class="w14-bar-wrap" id="w14BarWrap">
    {{-- Tooltip --}}
    <div class="w14-bar-tooltip" id="w14BarTooltip"></div>
@role('student')
      <a href="{{ url('student/journey/board/'.$studentId) }}" class="d-block text-decoration-none" aria-label="Open reward board">
@endrole
 @role('teacher')
      <a href="{{ url('teacher/journey/board/'.$studentId.'/'.$teacherSubjectId) }}" class="d-block text-decoration-none" aria-label="Open reward board">
@endrole 

    {{-- مسار البار --}}
    <div class="w14-bar-track bg-label-primary" >
      {{-- الجزء المنجَز (أخضر داكن) --}}
      <div class="w14-bar-segment"
           style="background: {{ $greenDark }}; width: {{ $pct }}%;"></div>
    </div>

    {{-- مناطق الهِت للـ hover --}}
    <div id="w14BarHitEarned"
         class="w14-bar-hit"
         style="left:0; width: {{ $pct }}%;">
        
    </div>

    <div id="w14BarHitRemain"
         class="w14-bar-hit"
         style="left: {{ $pct }}%; right:0;">
        
    </div>
    </a>
  </div>
  
  
 {{-- <div class="d-flex justify-content-between mb-1 small text-muted mt-1">
    <span>
        <svg width="8px" height="8px"  version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 31.955 31.955" style="enable-background:new 0 0 31.955 31.955;" xml:space="preserve"><g><path style="fill: rgb(24, 195, 126);" d="M27.25,4.655C20.996-1.571,10.88-1.546,4.656,4.706C-1.571,10.96-1.548,21.076,4.705,27.3 c6.256,6.226,16.374,6.203,22.597-0.051C33.526,20.995,33.505,10.878,27.25,4.655z" fill="#030104"></path><path style="fill: rgb(24, 195, 126);" d="M13.288,23.896l-1.768,5.207c2.567,0.829,5.331,0.886,7.926,0.17l-0.665-5.416 C17.01,24.487,15.067,24.5,13.288,23.896z M8.12,13.122l-5.645-0.859c-0.741,2.666-0.666,5.514,0.225,8.143l5.491-1.375 C7.452,17.138,7.426,15.029,8.12,13.122z M28.763,11.333l-4.965,1.675c0.798,2.106,0.716,4.468-0.247,6.522l5.351,0.672 C29.827,17.319,29.78,14.193,28.763,11.333z M11.394,2.883l1.018,5.528c2.027-0.954,4.356-1.05,6.442-0.288l1.583-5.137 C17.523,1.94,14.328,1.906,11.394,2.883z" fill="#030104"></path><circle style="fill: rgb(24, 195, 126);" cx="15.979" cy="15.977" r="6.117" fill="#030104"></circle></g></svg>
         {{ $current }}
        
        </span>
    <span>
        <svg  width="8px" height="8px"  version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 31.955 31.955" style="enable-background:new 0 0 31.955 31.955;" xml:space="preserve"><g><path style="fill: rgb(191, 238, 211);" d="M27.25,4.655C20.996-1.571,10.88-1.546,4.656,4.706C-1.571,10.96-1.548,21.076,4.705,27.3 c6.256,6.226,16.374,6.203,22.597-0.051C33.526,20.995,33.505,10.878,27.25,4.655z" fill="#030104"></path><path style="fill: rgb(191, 238, 211);" d="M13.288,23.896l-1.768,5.207c2.567,0.829,5.331,0.886,7.926,0.17l-0.665-5.416 C17.01,24.487,15.067,24.5,13.288,23.896z M8.12,13.122l-5.645-0.859c-0.741,2.666-0.666,5.514,0.225,8.143l5.491-1.375 C7.452,17.138,7.426,15.029,8.12,13.122z M28.763,11.333l-4.965,1.675c0.798,2.106,0.716,4.468-0.247,6.522l5.351,0.672 C29.827,17.319,29.78,14.193,28.763,11.333z M11.394,2.883l1.018,5.528c2.027-0.954,4.356-1.05,6.442-0.288l1.583-5.137 C17.523,1.94,14.328,1.906,11.394,2.883z" fill="#030104"></path><circle style="fill: rgb(191, 238, 211);" cx="15.979" cy="15.977" r="6.117" fill="#030104"></circle></g></svg>
        
        {{ $target }}
        
        </span>
  </div>--}}
</div>


    
    
    
    
    @elseif($circleView)
   
    
    @php
  $pct = (float)($pctNormalized ?? 0);

  // أرقام العرض
  $current = (int)($current ?? 0);
  $target  = (int)($total?? 0);
$remain  = max(0, $target - $current);
  // إعدادات الدائرة
  $size = 90;            
  $stroke = 10;          

  $radius = ($size / 2) - ($stroke / 2);
    $r =$radius ;
  $circ   = 2 * M_PI * $radius;
  $dash   = $circ;        // الطول الكامل للمحيط
  $offset1 = $dash * (1 - max(0,min(100,$pct))/100);  // الصيغة القياسية

// أطوال الأجزاء
  $doneRatio = max(0, min(1, $pct / 100));
  $doneLen   = $circ * $doneRatio;        // طول القوس المنجَز
 $remainLen = max(0, $circ - $doneLen);

  // حد أدنى صغير علشان جزء الفاتح يبقى قابل للهوفر لو مش صفر تمامًا (اختياري)
  $epsilon = 0.5; // بوحدة البكسل على المحيط
  if ($remainLen > 0 && $remainLen < $epsilon) { $remainLen = $epsilon; }
  // الداش/أوفست للرسم
  $dash   = $circ;
  $offset2 = $dash * (1 - $doneRatio);     // لقوس المنجَز (كما قبل)

  // بداية القوس المتبقي = بعد نهاية المنجَز مباشرة
  $remainOffset = $dash - $doneLen;




  $greenDark  = '#18c37e';  // أخضر داكن (المنجَز)
  $greenLight = '#bfeed3';  // أخضر فاتح (المتبقي)
@endphp









<div class="d-inline-flex align-items-center ">

  <div class="  rounded-3 text-center w14-donut-wrap" id="w14DonutWrap">
    {{-- Tooltip --}}
    <div class="w14-tooltip" id="w14Tooltip"></div>

    <div class="student_points_circle" >
      <svg
        width="100%" height="100%"
        viewBox="0 0 {{ $size }} {{ $size }}"
        aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int)round($pct) }}"
        role="progressbar">

        {{-- 1) حلقة الخلفية «المتبقي» أخضر فاتح --}}
        <circle
          cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}"
          fill="none"
          stroke="{{ $greenLight }}" stroke-width="{{ $stroke }}"
        />

        {{-- 2) حلقة «المنجَز» أخضر داكن --}}
        <circle
          id="w14ArcDone"
          cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}"
          fill="none"
          stroke="{{ $greenDark }}" stroke-width="{{ $stroke }}"
          stroke-linecap="butt" 
          stroke-dasharray="{{ $dash }}"
          stroke-dashoffset="{{ $offset2 }}"
          style="transform: rotate(-90deg); transform-origin: 50% 50%; transition: stroke-dashoffset .6s ease;"
          data-tooltip="حصلت على: {{ $current }} نقطة"
        />

        {{-- 3) طبقة تفاعلية للمتبقي (علشان tooltip للجزء الفاتح) --}}
       
       
        
       



{{-- دائرة تفاعلية واحدة تغطي الحلقة كلها --}}
<circle
  id="w14HitAll"
  cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}"
  fill="none"
  stroke="transparent"
  stroke-width="{{ $stroke + 20}}"
  stroke-dasharray="{{ $circ }} {{ $circ }}"
  stroke-dashoffset="0"
  style="transform: rotate(-90deg); transform-origin: 50% 50%; pointer-events: stroke;"
/>




      </svg>
    </div>

    {{-- نص الوسط --}}
    <div class="position-absolute top-50 start-50 translate-middle text-center">
           @role('student|parent')


          <div class="fw-bold" style="font-size:18px">{{ (int)round($pct) }}%</div>
      
      <div class="small text-muted">{{ $current }} / {{ $target }}</div>

          
          
@endrole
 @role('teacher')
      <a href="{{ url('teacher/journey/board/'.$studentId.'/'.$teacherSubjectId) }}" class="d-block text-decoration-none" aria-label="Open reward board">
          <div class="fw-bold" style="font-size:1.6rem">{{ (int)round($pct) }}%</div>
      <div class="small text-muted">{{ $current }} / {{ $target }}</div>
      </a>
@endrole
      
    </div>

    {{-- اسم الهدية القادمة (اختياري) --}}
    @if(!empty($nextGiftName))
      <div class="mt-2 small text-muted">{{ $nextGiftName }}</div>
    @endif
  </div>

  {{-- وصف جانبي اختياري --}}
  
 {{-- <div class="small text-muted">
    <div>Progress to next gift</div>
    <div class="fw-semibold">{{ $current }} of {{ $target }} pts</div>
  </div>--}}
  
</div>

<!--bar in reward system-->
  @else
<div class="d-flex align-items-center points-progress-root" style=" border-radius: 6px;">

@if(($reachedCount ?? 0) > 0)
  @php
  $overlayText = $reachedCount > 1 ? '+'.($reachedCount - 1) : ($reachedCount === 0 ? '0' : null);
@endphp
@php
  $isSingleReached = ($reachedCount === 1 && !empty($lastReachedGiftId));

@endphp
<div class="me-3 position-relative d-flex align-items-center justify-content-center"
   style="width:42px;height:42px;">

<div class="gift-badge bg-label-primary gift-pulse d-flex align-items-center justify-content-center rounded-circle {{ $clickable ? 'cursor-pointer' : 'cursor-default ' }}" @if($clickable) wire:click="openRedeemModal({{ (int) $lastReachedGiftId }})" role="button" tabindex="0" @else aria-disabled="true" @endif >

  <!--@php $leftIcon = ($reachedCount === 0) ? 'gifts/default_gift.png' : ($lastReachedGiftImage ?? 'gifts/default_gift.png'); @endphp-->
  <!--<img src="{{ asset('public/storage/'.$leftIcon) }}" alt="Gift" style="width:22px;height:22px;object-fit:contain;">-->
  
  
  @php
    $hasReached = $reachedCount !== 0 && !empty($lastReachedGiftImage);
@endphp

@if ($hasReached)
    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($lastReachedGiftImage, '/')) }}"
         alt="Gift"
         style="width:22px;height:22px;object-fit:contain;">
@else
   <svg style="position: absolute;left: 20%;top: 17%;bottom: 17%;right: 20%;" class="w14-icon reward-icon" width="60%" height="60%"  version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 66.139 66.139" style="enable-background:new 0 0 66.139 66.139;" xml:space="preserve"><g><path style="fill:#231F20;" d="M43.185,20.412c2.896,0.42,5.332-0.977,8.112-1.099c0.1,0.706,0.186,1.31,0.287,2.021 c-0.988,0.293-1.836,0.545-2.684,0.796c-0.008,0.131-0.017,0.263-0.025,0.394c1.809,0.13,3.615,0.313,5.425,0.377 c2.778,0.098,3.112,0.357,3.259,3.163c0.064,1.239-0.047,2.486,0.005,3.726c0.068,1.612-0.157,3.017-2.204,3.468 c-0.281,6.301-0.576,12.498-0.825,18.695c-0.09,2.229-0.083,4.463-0.093,6.695c-0.009,1.816-0.017,3.634,0.048,5.449 c0.054,1.504-0.314,2.068-1.703,2.041c-6.616-0.131-13.232-0.311-19.848-0.443c-0.9-0.018-1.804,0.212-2.706,0.205 c-5.712-0.046-11.424-0.097-17.134-0.229c-0.864-0.02-1.717-0.474-2.749-0.779c-0.31-10.439,0.713-20.819,0.825-31.141 c-0.849-0.507-1.856-0.785-2.256-1.436c-0.422-0.687-0.368-1.733-0.341-2.616c0.056-1.814,0.184-3.629,0.372-5.436 c0.227-2.19,0.542-2.465,2.753-2.485c2.4-0.021,4.802,0.025,7.202,0.036c0.813,0.004,1.625-0.012,2.437-0.019 c0.036-0.16,0.071-0.321,0.107-0.481c-0.98-0.291-1.957-0.59-2.941-0.868c-0.738-0.208-1.57-0.386-1.308-1.417 c0.241-0.948,1.08-0.963,1.826-0.789c1.761,0.41,3.504,0.899,5.759,1.488c-1.573-1.755-2.922-3.106-4.09-4.599 c-1.725-2.206-3.351-4.421-4.013-7.31c-0.781-3.413,0.172-4.871,3.618-6.987c2.353-1.446,4.8-0.862,6.994,0.638 c2.622,1.794,3.788,4.515,4.531,7.463c0.3,1.193,0.515,2.408,0.778,3.663c1.376-1.327,2.687-2.654,4.066-3.905 c1.647-1.495,3.525-2.2,5.673-1.134c2.202,1.092,3.413,3.031,3.404,5.384c-0.006,1.665-0.656,3.351-1.17,4.98 C44.316,18.75,43.722,19.475,43.185,20.412z M18.433,35.769c0.033,0.137,0.066,0.274,0.099,0.411 c-1.705,0.093-3.411,0.185-5.232,0.284c0.007,0.919,0.013,1.602,0.02,2.406c1.935,0.17,3.72,0.327,5.505,0.483 c-1.375,0.394-2.766,0.515-4.128,0.781c-2,0.391-2.097,0.637-1.539,2.871c1.252,0.26,2.503,0.52,3.754,0.78 c-0.013,0.075-0.026,0.149-0.04,0.224c-1.277,0.156-2.554,0.311-3.937,0.479c0.01,1.228,0.019,2.194,0.029,3.449 c2.585,0.129,5.126,0.255,7.667,0.382c-0.003,0.069-0.006,0.138-0.009,0.208c-2.533,0.231-5.066,0.463-7.768,0.71 c-0.113,1.335-0.205,2.429-0.315,3.728c2.809,0.113,5.313,0.215,7.817,0.316c-1.792,0.419-3.583,0.691-5.384,0.844 c-1.982,0.167-3.098,0.692-2.342,3.512c2.209-0.019,4.524-0.038,6.839-0.057c0.002,0.13,0.005,0.261,0.008,0.392 c-2.32,0.437-4.641,0.874-6.944,1.307c-0.072,0.545-0.161,0.87-0.142,1.188c0.017,0.304,0.146,0.601,0.263,1.038 c2.107,0.091,4.146,0.18,6.185,0.268c-0.661,0.243-1.313,0.297-1.965,0.366c-0.738,0.078-1.479,0.142-2.211,0.261 c-0.548,0.089-1.084,0.254-1.626,0.385c5.556,0.962,11.028,0.947,16.765,0.691c-0.29-10.326-0.57-20.284-0.846-30.081 c-0.465-0.227-0.605-0.339-0.76-0.365c-4.456-0.756-8.879,0.204-13.32,0.285c-0.818,0.015-1.559,0.536-1.479,1.814 C15.073,35.342,16.753,35.555,18.433,35.769z M38.699,33.081c0.026,3.167,0.035,6.041,0.079,8.914 c0.051,3.297,0.112,6.594,0.208,9.89c0.101,3.471,0.254,6.94,0.367,10.411c0.037,1.122,0.526,1.643,1.697,1.607 c1.484-0.046,2.971,0.032,4.457,0.03c2.124-0.004,4.249-0.032,6.46-0.05c0.418-10.324,0.822-20.292,1.229-30.335 C48.251,32.749,43.57,33.015,38.699,33.081z M36.936,63.455c-0.021-2.583-0.05-4.89-0.058-7.197 c-0.008-2.309,0.009-4.617,0.007-6.926c0-0.905-0.03-1.81-0.029-2.714c0.001-3.537,0.064-7.074-0.001-10.609 c-0.06-3.214-0.328-6.424-0.369-9.637c-0.028-2.216-0.615-2.888-2.746-2.6c-0.486,0.066-0.953,0.313-1.438,0.347 c-1.306,0.092-1.677,0.917-1.649,2.03c0.068,2.72,0.174,5.439,0.264,8.158c0.243,7.348,0.482,14.697,0.731,22.044 c0.08,2.357,0.189,4.714,0.288,7.145C33.848,63.48,35.395,63.467,36.936,63.455z M30.65,20.715 c0.049-0.255,0.145-0.551,0.156-0.851c0.166-4.089-0.633-8.056-1.727-11.949c-0.697-2.483-2.328-4.318-4.71-5.471 c-0.993-0.48-1.893-0.465-2.848-0.041c-2.309,1.024-3.166,2.754-2.49,5.215c0.216,0.788,0.581,1.548,0.968,2.273 c2.392,4.488,6.26,7.617,10.042,10.82C30.131,20.787,30.344,20.718,30.65,20.715z M10.84,25.971 c1.992,0.101,3.913,0.199,5.834,0.296c-1.724,0.492-3.431,0.708-5.135,0.943c-0.913,0.126-1.249,0.609-0.921,1.521 c3.665,0.226,7.262,0.449,10.86,0.671c-1.165,0.4-2.319,0.406-3.474,0.42c-1.16,0.015-2.328-0.033-3.478,0.081 c-1.046,0.104-2.074,0.388-3.11,0.593c5.818,0.388,11.557,0.33,17.553,0.013c-0.189-2.344-0.352-4.367-0.511-6.342 c-0.414-0.148-0.642-0.301-0.871-0.301c-5.052-0.012-10.103-0.004-15.155-0.008C11.062,23.858,10.843,24.719,10.84,25.971z M38.523,30.737c5.925,0.058,11.552,0.688,17.148-0.071c-0.017-2.048-0.031-3.723-0.046-5.526 c-5.818-0.363-11.377-0.771-17.152-0.338C38.49,26.747,38.504,28.53,38.523,30.737z M32.913,21.596 c3.03-0.601,5.557-0.855,7.773-2.279c2.64-1.696,3.668-5.383,2.218-8.059c-1.052-1.938-2.625-2.329-4.511-1.168 c-1.681,1.035-2.977,2.341-3.495,4.323C34.315,16.643,33.673,18.858,32.913,21.596z"/></g></svg>

@endif

  
  
</div>

@if(!is_null($overlayText))
  <span class="gift-count fw-bold position-absolute top-0 start-100 translate-middle badge badge-center rounded-pill bg-primary text-white">{{ $overlayText }}</span>
@endif

@if($reachedCount === 1)
  <span class="position-absolute small fw-semibold text-primary"
        style="left:50%;transform:translateX(-50%);bottom:-18px;white-space:nowrap;">
    Claim
  </span>
@endif
</div>





  @endif


    {{-- (2) الشريط --}}
    <div class="flex-grow-1 position-relative">
    {{--    @role('student')
      <a href="{{ url('student/journey/board/'.$studentId) }}" class="d-block text-decoration-none" aria-label="Open reward board">
@endrole
 @role('teacher')
      <a href="{{ url('teacher/journey/board/'.$studentId.'/'.$teacherSubjectId) }}" class="d-block text-decoration-none" aria-label="Open reward board">
@endrole
--}}
      <div class="progress progress-pill shadow-sm bg-label-primary"
           aria-valuenow="{{ $current }}"
           aria-valuemin="0"
           aria-valuemax="{{ $total }}"
           style="height:17px;border-radius:999px;position:relative;">
        <div class="progress-bar {{ ($status ?? null) === 'reached' ? 'bg-success' : '' }}"
             role="progressbar"
             style="
               position:relative;
               width: {{ number_format($pct,2) }}%;
               
               @if(($status ?? '') !== 'reached')
                 background-color:var(--bs-primary) ;
               @endif
             ">
          {{-- رقم الطالب في منتصف الجزء الذهبي --}}
          <span class="position-absolute start-50 top-50 translate-middle fw-semibold small {{ ($status ?? '') === 'reached' ? 'text-white' : 'text-white' }}"
                style="pointer-events:none;user-select:none;">
            {{ $current }}
          </span>
        </div>

        {{-- هدف الهدية داخل نهاية البار --}}
        <span class="position-absolute fw-semibold small text-dark"
              style="right:12px;top:50%;transform:translateY(-50%);color:#fff;text-shadow:0 1px 2px rgba(0,0,0,.35);pointer-events:none;user-select:none;z-index:2;">
          {{ $total }}
        </span>
        @if(isset($floorPoints) && isset($current) && $current == $floorPoints)
          <span class="position-absolute fw-semibold small text-dark"
                style="left:12px;top:50%;transform:translateY(-50%);color:#fff;text-shadow:0 1px 2px rgba(0,0,0,.35);pointer-events:none;user-select:none;z-index:2;">
            {{ $floorPoints }}
          </span>
        @endif



      </div>
  {{--  </a>  --}}
    </div>

    {{-- (3) أيقونة الهدف الحالي (pending) على اليمين بمسافة --}}

    <div class="ms-3 position-relative justify-content-center d-flex flex-column align-items-center" style="opacity:.9">
       @role('student') 
       <!--<a href="{{ url('student/journey/board/'.$studentId) }}" class="ms-3 d-flex align-items-center justify-content-center rounded-circle"-->
       <!--    style="width:42px;height:42px;box-shadow:0 4px 10px rgba(0,0,0,.25);border:2px solid rgba(255,255,255,.25);background-color:var(--bs-primary)">-->
           
           <div  class="ms-3 d-flex align-items-center justify-content-center rounded-circle bg-label-primary"
           style="width:42px;height:42px;box-shadow:0 4px 10px rgba(0,0,0,.25);border:2px solid rgba(255,255,255,.25);">
           
           @if($icon)
             <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($icon, '/')) }}" alt="Gift" style="width:27px;height:27px;object-fit:contain;">
           @else
           <!--<img src="{{ asset('/public/storage/gifts/default_gift.png') }}" alt="Gift" style="width:27px;height:27px;object-fit:contain;">-->


<svg style="position: absolute;left: 30%;top: 17%;bottom: 17%;right: 20%;" class="w14-icon reward-icon" width="60%" height="60%"  version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 66.139 66.139" style="enable-background:new 0 0 66.139 66.139;" xml:space="preserve"><g><path style="fill:#231F20;" d="M43.185,20.412c2.896,0.42,5.332-0.977,8.112-1.099c0.1,0.706,0.186,1.31,0.287,2.021 c-0.988,0.293-1.836,0.545-2.684,0.796c-0.008,0.131-0.017,0.263-0.025,0.394c1.809,0.13,3.615,0.313,5.425,0.377 c2.778,0.098,3.112,0.357,3.259,3.163c0.064,1.239-0.047,2.486,0.005,3.726c0.068,1.612-0.157,3.017-2.204,3.468 c-0.281,6.301-0.576,12.498-0.825,18.695c-0.09,2.229-0.083,4.463-0.093,6.695c-0.009,1.816-0.017,3.634,0.048,5.449 c0.054,1.504-0.314,2.068-1.703,2.041c-6.616-0.131-13.232-0.311-19.848-0.443c-0.9-0.018-1.804,0.212-2.706,0.205 c-5.712-0.046-11.424-0.097-17.134-0.229c-0.864-0.02-1.717-0.474-2.749-0.779c-0.31-10.439,0.713-20.819,0.825-31.141 c-0.849-0.507-1.856-0.785-2.256-1.436c-0.422-0.687-0.368-1.733-0.341-2.616c0.056-1.814,0.184-3.629,0.372-5.436 c0.227-2.19,0.542-2.465,2.753-2.485c2.4-0.021,4.802,0.025,7.202,0.036c0.813,0.004,1.625-0.012,2.437-0.019 c0.036-0.16,0.071-0.321,0.107-0.481c-0.98-0.291-1.957-0.59-2.941-0.868c-0.738-0.208-1.57-0.386-1.308-1.417 c0.241-0.948,1.08-0.963,1.826-0.789c1.761,0.41,3.504,0.899,5.759,1.488c-1.573-1.755-2.922-3.106-4.09-4.599 c-1.725-2.206-3.351-4.421-4.013-7.31c-0.781-3.413,0.172-4.871,3.618-6.987c2.353-1.446,4.8-0.862,6.994,0.638 c2.622,1.794,3.788,4.515,4.531,7.463c0.3,1.193,0.515,2.408,0.778,3.663c1.376-1.327,2.687-2.654,4.066-3.905 c1.647-1.495,3.525-2.2,5.673-1.134c2.202,1.092,3.413,3.031,3.404,5.384c-0.006,1.665-0.656,3.351-1.17,4.98 C44.316,18.75,43.722,19.475,43.185,20.412z M18.433,35.769c0.033,0.137,0.066,0.274,0.099,0.411 c-1.705,0.093-3.411,0.185-5.232,0.284c0.007,0.919,0.013,1.602,0.02,2.406c1.935,0.17,3.72,0.327,5.505,0.483 c-1.375,0.394-2.766,0.515-4.128,0.781c-2,0.391-2.097,0.637-1.539,2.871c1.252,0.26,2.503,0.52,3.754,0.78 c-0.013,0.075-0.026,0.149-0.04,0.224c-1.277,0.156-2.554,0.311-3.937,0.479c0.01,1.228,0.019,2.194,0.029,3.449 c2.585,0.129,5.126,0.255,7.667,0.382c-0.003,0.069-0.006,0.138-0.009,0.208c-2.533,0.231-5.066,0.463-7.768,0.71 c-0.113,1.335-0.205,2.429-0.315,3.728c2.809,0.113,5.313,0.215,7.817,0.316c-1.792,0.419-3.583,0.691-5.384,0.844 c-1.982,0.167-3.098,0.692-2.342,3.512c2.209-0.019,4.524-0.038,6.839-0.057c0.002,0.13,0.005,0.261,0.008,0.392 c-2.32,0.437-4.641,0.874-6.944,1.307c-0.072,0.545-0.161,0.87-0.142,1.188c0.017,0.304,0.146,0.601,0.263,1.038 c2.107,0.091,4.146,0.18,6.185,0.268c-0.661,0.243-1.313,0.297-1.965,0.366c-0.738,0.078-1.479,0.142-2.211,0.261 c-0.548,0.089-1.084,0.254-1.626,0.385c5.556,0.962,11.028,0.947,16.765,0.691c-0.29-10.326-0.57-20.284-0.846-30.081 c-0.465-0.227-0.605-0.339-0.76-0.365c-4.456-0.756-8.879,0.204-13.32,0.285c-0.818,0.015-1.559,0.536-1.479,1.814 C15.073,35.342,16.753,35.555,18.433,35.769z M38.699,33.081c0.026,3.167,0.035,6.041,0.079,8.914 c0.051,3.297,0.112,6.594,0.208,9.89c0.101,3.471,0.254,6.94,0.367,10.411c0.037,1.122,0.526,1.643,1.697,1.607 c1.484-0.046,2.971,0.032,4.457,0.03c2.124-0.004,4.249-0.032,6.46-0.05c0.418-10.324,0.822-20.292,1.229-30.335 C48.251,32.749,43.57,33.015,38.699,33.081z M36.936,63.455c-0.021-2.583-0.05-4.89-0.058-7.197 c-0.008-2.309,0.009-4.617,0.007-6.926c0-0.905-0.03-1.81-0.029-2.714c0.001-3.537,0.064-7.074-0.001-10.609 c-0.06-3.214-0.328-6.424-0.369-9.637c-0.028-2.216-0.615-2.888-2.746-2.6c-0.486,0.066-0.953,0.313-1.438,0.347 c-1.306,0.092-1.677,0.917-1.649,2.03c0.068,2.72,0.174,5.439,0.264,8.158c0.243,7.348,0.482,14.697,0.731,22.044 c0.08,2.357,0.189,4.714,0.288,7.145C33.848,63.48,35.395,63.467,36.936,63.455z M30.65,20.715 c0.049-0.255,0.145-0.551,0.156-0.851c0.166-4.089-0.633-8.056-1.727-11.949c-0.697-2.483-2.328-4.318-4.71-5.471 c-0.993-0.48-1.893-0.465-2.848-0.041c-2.309,1.024-3.166,2.754-2.49,5.215c0.216,0.788,0.581,1.548,0.968,2.273 c2.392,4.488,6.26,7.617,10.042,10.82C30.131,20.787,30.344,20.718,30.65,20.715z M10.84,25.971 c1.992,0.101,3.913,0.199,5.834,0.296c-1.724,0.492-3.431,0.708-5.135,0.943c-0.913,0.126-1.249,0.609-0.921,1.521 c3.665,0.226,7.262,0.449,10.86,0.671c-1.165,0.4-2.319,0.406-3.474,0.42c-1.16,0.015-2.328-0.033-3.478,0.081 c-1.046,0.104-2.074,0.388-3.11,0.593c5.818,0.388,11.557,0.33,17.553,0.013c-0.189-2.344-0.352-4.367-0.511-6.342 c-0.414-0.148-0.642-0.301-0.871-0.301c-5.052-0.012-10.103-0.004-15.155-0.008C11.062,23.858,10.843,24.719,10.84,25.971z M38.523,30.737c5.925,0.058,11.552,0.688,17.148-0.071c-0.017-2.048-0.031-3.723-0.046-5.526 c-5.818-0.363-11.377-0.771-17.152-0.338C38.49,26.747,38.504,28.53,38.523,30.737z M32.913,21.596 c3.03-0.601,5.557-0.855,7.773-2.279c2.64-1.696,3.668-5.383,2.218-8.059c-1.052-1.938-2.625-2.329-4.511-1.168 c-1.681,1.035-2.977,2.341-3.495,4.323C34.315,16.643,33.673,18.858,32.913,21.596z"/></g></svg>



           @endif
      {{--</a>--}}
      </div>
      @endrole
      @role('teacher')
       <!--<a href="{{ url('teacher/journey/board/'.$studentId.'/'.$teacherSubjectId) }}" class="ms-3 d-flex align-items-center justify-content-center rounded-circle"-->
       <!--    style="width:42px;height:42px;box-shadow:0 4px 10px rgba(0,0,0,.25);border:2px solid rgba(255,255,255,.25);">-->
           <div  class="ms-3 d-flex align-items-center justify-content-center rounded-circle bg-label-primary"
           style="width:42px;height:42px;box-shadow:0 4px 10px rgba(0,0,0,.25);border:2px solid rgba(255,255,255,.25);">
           @if($icon)
             <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(ltrim($icon, '/')) }}" alt="Gift" style="width:27px;height:27px;object-fit:contain;">
           @else
           <!--<img src="{{ asset('/public/storage/gifts/default_gift.png') }}" alt="Gift" style="width:27px;height:27px;object-fit:contain;">-->

<svg style="position: absolute;left: 30%;top: 17%;bottom: 17%;right: 20%;" class="w14-icon reward-icon" width="60%" height="60%"  version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 66.139 66.139" style="enable-background:new 0 0 66.139 66.139;" xml:space="preserve"><g><path style="fill:#231F20;" d="M43.185,20.412c2.896,0.42,5.332-0.977,8.112-1.099c0.1,0.706,0.186,1.31,0.287,2.021 c-0.988,0.293-1.836,0.545-2.684,0.796c-0.008,0.131-0.017,0.263-0.025,0.394c1.809,0.13,3.615,0.313,5.425,0.377 c2.778,0.098,3.112,0.357,3.259,3.163c0.064,1.239-0.047,2.486,0.005,3.726c0.068,1.612-0.157,3.017-2.204,3.468 c-0.281,6.301-0.576,12.498-0.825,18.695c-0.09,2.229-0.083,4.463-0.093,6.695c-0.009,1.816-0.017,3.634,0.048,5.449 c0.054,1.504-0.314,2.068-1.703,2.041c-6.616-0.131-13.232-0.311-19.848-0.443c-0.9-0.018-1.804,0.212-2.706,0.205 c-5.712-0.046-11.424-0.097-17.134-0.229c-0.864-0.02-1.717-0.474-2.749-0.779c-0.31-10.439,0.713-20.819,0.825-31.141 c-0.849-0.507-1.856-0.785-2.256-1.436c-0.422-0.687-0.368-1.733-0.341-2.616c0.056-1.814,0.184-3.629,0.372-5.436 c0.227-2.19,0.542-2.465,2.753-2.485c2.4-0.021,4.802,0.025,7.202,0.036c0.813,0.004,1.625-0.012,2.437-0.019 c0.036-0.16,0.071-0.321,0.107-0.481c-0.98-0.291-1.957-0.59-2.941-0.868c-0.738-0.208-1.57-0.386-1.308-1.417 c0.241-0.948,1.08-0.963,1.826-0.789c1.761,0.41,3.504,0.899,5.759,1.488c-1.573-1.755-2.922-3.106-4.09-4.599 c-1.725-2.206-3.351-4.421-4.013-7.31c-0.781-3.413,0.172-4.871,3.618-6.987c2.353-1.446,4.8-0.862,6.994,0.638 c2.622,1.794,3.788,4.515,4.531,7.463c0.3,1.193,0.515,2.408,0.778,3.663c1.376-1.327,2.687-2.654,4.066-3.905 c1.647-1.495,3.525-2.2,5.673-1.134c2.202,1.092,3.413,3.031,3.404,5.384c-0.006,1.665-0.656,3.351-1.17,4.98 C44.316,18.75,43.722,19.475,43.185,20.412z M18.433,35.769c0.033,0.137,0.066,0.274,0.099,0.411 c-1.705,0.093-3.411,0.185-5.232,0.284c0.007,0.919,0.013,1.602,0.02,2.406c1.935,0.17,3.72,0.327,5.505,0.483 c-1.375,0.394-2.766,0.515-4.128,0.781c-2,0.391-2.097,0.637-1.539,2.871c1.252,0.26,2.503,0.52,3.754,0.78 c-0.013,0.075-0.026,0.149-0.04,0.224c-1.277,0.156-2.554,0.311-3.937,0.479c0.01,1.228,0.019,2.194,0.029,3.449 c2.585,0.129,5.126,0.255,7.667,0.382c-0.003,0.069-0.006,0.138-0.009,0.208c-2.533,0.231-5.066,0.463-7.768,0.71 c-0.113,1.335-0.205,2.429-0.315,3.728c2.809,0.113,5.313,0.215,7.817,0.316c-1.792,0.419-3.583,0.691-5.384,0.844 c-1.982,0.167-3.098,0.692-2.342,3.512c2.209-0.019,4.524-0.038,6.839-0.057c0.002,0.13,0.005,0.261,0.008,0.392 c-2.32,0.437-4.641,0.874-6.944,1.307c-0.072,0.545-0.161,0.87-0.142,1.188c0.017,0.304,0.146,0.601,0.263,1.038 c2.107,0.091,4.146,0.18,6.185,0.268c-0.661,0.243-1.313,0.297-1.965,0.366c-0.738,0.078-1.479,0.142-2.211,0.261 c-0.548,0.089-1.084,0.254-1.626,0.385c5.556,0.962,11.028,0.947,16.765,0.691c-0.29-10.326-0.57-20.284-0.846-30.081 c-0.465-0.227-0.605-0.339-0.76-0.365c-4.456-0.756-8.879,0.204-13.32,0.285c-0.818,0.015-1.559,0.536-1.479,1.814 C15.073,35.342,16.753,35.555,18.433,35.769z M38.699,33.081c0.026,3.167,0.035,6.041,0.079,8.914 c0.051,3.297,0.112,6.594,0.208,9.89c0.101,3.471,0.254,6.94,0.367,10.411c0.037,1.122,0.526,1.643,1.697,1.607 c1.484-0.046,2.971,0.032,4.457,0.03c2.124-0.004,4.249-0.032,6.46-0.05c0.418-10.324,0.822-20.292,1.229-30.335 C48.251,32.749,43.57,33.015,38.699,33.081z M36.936,63.455c-0.021-2.583-0.05-4.89-0.058-7.197 c-0.008-2.309,0.009-4.617,0.007-6.926c0-0.905-0.03-1.81-0.029-2.714c0.001-3.537,0.064-7.074-0.001-10.609 c-0.06-3.214-0.328-6.424-0.369-9.637c-0.028-2.216-0.615-2.888-2.746-2.6c-0.486,0.066-0.953,0.313-1.438,0.347 c-1.306,0.092-1.677,0.917-1.649,2.03c0.068,2.72,0.174,5.439,0.264,8.158c0.243,7.348,0.482,14.697,0.731,22.044 c0.08,2.357,0.189,4.714,0.288,7.145C33.848,63.48,35.395,63.467,36.936,63.455z M30.65,20.715 c0.049-0.255,0.145-0.551,0.156-0.851c0.166-4.089-0.633-8.056-1.727-11.949c-0.697-2.483-2.328-4.318-4.71-5.471 c-0.993-0.48-1.893-0.465-2.848-0.041c-2.309,1.024-3.166,2.754-2.49,5.215c0.216,0.788,0.581,1.548,0.968,2.273 c2.392,4.488,6.26,7.617,10.042,10.82C30.131,20.787,30.344,20.718,30.65,20.715z M10.84,25.971 c1.992,0.101,3.913,0.199,5.834,0.296c-1.724,0.492-3.431,0.708-5.135,0.943c-0.913,0.126-1.249,0.609-0.921,1.521 c3.665,0.226,7.262,0.449,10.86,0.671c-1.165,0.4-2.319,0.406-3.474,0.42c-1.16,0.015-2.328-0.033-3.478,0.081 c-1.046,0.104-2.074,0.388-3.11,0.593c5.818,0.388,11.557,0.33,17.553,0.013c-0.189-2.344-0.352-4.367-0.511-6.342 c-0.414-0.148-0.642-0.301-0.871-0.301c-5.052-0.012-10.103-0.004-15.155-0.008C11.062,23.858,10.843,24.719,10.84,25.971z M38.523,30.737c5.925,0.058,11.552,0.688,17.148-0.071c-0.017-2.048-0.031-3.723-0.046-5.526 c-5.818-0.363-11.377-0.771-17.152-0.338C38.49,26.747,38.504,28.53,38.523,30.737z M32.913,21.596 c3.03-0.601,5.557-0.855,7.773-2.279c2.64-1.696,3.668-5.383,2.218-8.059c-1.052-1.938-2.625-2.329-4.511-1.168 c-1.681,1.035-2.977,2.341-3.495,4.323C34.315,16.643,33.673,18.858,32.913,21.596z"/></g></svg>



           @endif
      {{--</a>--}}
      </div>
      @endrole
      
      
    {{--  <span class="position-absolute small fw-semibold text-dark"
            style="left:50%;transform:translateX(-50%);bottom:-18px;white-space:nowrap;">
        Coming Up
      </span>--}}
    </div>




{{-- Bootstrap Modal for PIN --}}
{{-- اجعليه خارج البار وبداخله wire:ignore.self --}}
<div id="pinModal" class="modal fade" tabindex="-1" aria-hidden="true" wire:ignore.self>
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form wire:submit.prevent="redeem">
        <div class="modal-header">

          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="text"
                 wire:model="pin"
                 class="form-control w14-pin-input @error('pin') is-invalid @enderror"
                 id="pinInput"
                 name="w14_reward_pin_{{ $studentId }}_{{ $redeemGiftId ?? 'new' }}"
                 autocomplete="off"
                 autocapitalize="off"
                 spellcheck="false"
                 data-lpignore="true"
                 data-1p-ignore="true"
                 data-form-type="other"
                 inputmode="numeric"
                 pattern="[0-9]*"
                 placeholder="Enter PIN">
          @error('pin')
            <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="modal-footer d-flex align-items-center justify-content-center">

          <button type="submit" class="btn btn-primary col-10">Claim</button>
        </div>
      </form>
    </div>
  </div>
</div>
</div>
@endif



@once
<style>
  .points-progress-root .w14-pin-input {
    -webkit-text-security: disc;
    text-security: disc;
    letter-spacing: 0.28em;
  }

  .points-progress-root .w14-pin-input::placeholder {
    letter-spacing: 0;
  }
  /* حجم/شكل الخلفية الذهبية */
  .points-progress-root .gift-badge{
    width:42px;height:42px;
    /*background:*/
    /*  radial-gradient(ellipse at 30% 30%, #ffcf5a 0%, #f7a325 70%, #a85a19 100%);*/
    border:2px solid rgba(255,255,255,.25);
    box-shadow:
      0 4px 10px rgba(0,0,0,.25),
      inset 0 0 0 3px rgba(255,255,255,.18);
    position: relative;
    will-change: transform, box-shadow;
    /*background-color:var(--bs-primary);*/
  }

  /* وهج نابض للهدية reached */
  .points-progress-root .gift-badge.gift-pulse{
    animation: giftPulseGlow 1.6s ease-in-out infinite;
  }
  @keyframes giftPulseGlow{
    0%{
      transform:scale(1);
      box-shadow:
        0 4px 10px rgba(0,0,0,.25),
        inset 0 0 0 3px rgba(255,255,255,.18),
        0 0 0 0 rgba(247,163,37,0),
        0 0 0 0 rgba(255,207,90,0);
    }
    45%{
      transform:scale(1.04);
      box-shadow:
        0 4px 10px rgba(0,0,0,.25),
        inset 0 0 0 3px rgba(255,255,255,.18),
        0 0 22px 6px rgba(247,163,37,.55),
        0 0 36px 10px rgba(255,207,90,.35);
    }
    100%{
      transform:scale(1);
      box-shadow:
        0 4px 10px rgba(0,0,0,.25),
        inset 0 0 0 3px rgba(255,255,255,.18),
        0 0 0 0 rgba(247,163,37,0),
        0 0 0 0 rgba(255,207,90,0);
    }
  }
  @media (prefers-reduced-motion: reduce){
    .points-progress-root .gift-badge.gift-pulse{ animation:none; }
  }

  /* عداد بدون خلفية – يتشابك مع الخلفية الذهبية */
/*  .points-progress-root .gift-count{*/
/*    position:absolute;*/
/*    top:-6px; left:-2px;      */
/*    font-size:17px; line-height:1;*/
/*    font-family: sans-serif;*/
/*  }*/


</style>
@endonce





@once
@if($allowReachedClick)
<script>
  (function () {
    if (window.w14PointsProgressInitialized) return;
    window.w14PointsProgressInitialized = true;

    document.addEventListener('livewire:initialized', () => {
    const MODAL_ID = 'pinModal';
    const INPUT_SELECTOR = '#pinInput';

    // فتح المودال
    window.addEventListener('pin-modal:open', () => {
      const el = document.getElementById(MODAL_ID);
      if (!el) { console.warn('Modal not found:', MODAL_ID); return; }
      const modal = bootstrap.Modal.getOrCreateInstance(el);
      modal.show();


// focus on pin input


const onShown = () => {
        el.removeEventListener('shown.bs.modal', onShown);
        const input = el.querySelector(INPUT_SELECTOR);
        if (input) {
          input.focus();
          // لو عايز يحدّد أي أرقام مكتوبة:
          input.select?.();
        }
      };

      el.addEventListener('shown.bs.modal', onShown, { once: true });
      setTimeout(() => {
              const input = el.querySelector(INPUT_SELECTOR);
              if (document.activeElement !== input) {
                input?.focus();
                input?.select?.();
              }
            }, 80);




    });

    // غلق المودال
    window.addEventListener('pin-modal:close', () => {
      const el = document.getElementById(MODAL_ID);
      if (!el) return;
      (bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el)).hide();
    });


  });
  })();
</script>
@endif
@endonce




@if($barView)

<script>
  (function () {
    const wrap      = document.getElementById('w14BarWrap');
    if (!wrap) return;

    const tip       = document.getElementById('w14BarTooltip');
    const hitEarned = document.getElementById('w14BarHitEarned');
    const hitRemain = document.getElementById('w14BarHitRemain');

    const current = {{ $current }};
    const remain  = {{ $remain }};

    const txtEarned = `Currently: ${current}`;
    const txtRemain = `${remain} to go`;

    function showTip(text) {
      tip.textContent = text;
      tip.style.opacity = '1';
    }
    function hideTip() {
      tip.style.opacity = '0';
    }

    // الجزء الأخضر الغامق
    hitEarned.addEventListener('mouseenter', () => showTip(txtEarned));
    hitEarned.addEventListener('mouseleave', hideTip);

    // الجزء الفاتح (المتبقي)
    hitRemain.addEventListener('mouseenter', () => showTip(txtRemain));
    hitRemain.addEventListener('mouseleave', hideTip);

    // دعم click (للموبايل)
    hitEarned.addEventListener('click', () => showTip(txtEarned));
    hitRemain.addEventListener('click', () => showTip(txtRemain));

    document.addEventListener('click', (e) => {
      if (!wrap.contains(e.target)) hideTip();
    });
  })();
</script>
@endif





@if($circleView)
<!--circul script-->
<script>
(function () {
  const wrap   = document.getElementById('w14DonutWrap');
  const tip    = document.getElementById('w14Tooltip');
  const hitAll = document.getElementById('w14HitAll');

  // أرقامك من السيرفر
  const current = {{ $current }};
  const remain  = {{ $remain }};
  const pct     = {{ (int)round($pct) }};      // 0..100
  const doneRatio = Math.max(0, Math.min(1, pct / 100));
  const doneAngle = 360 * doneRatio;           // زاوية الجزء الغامق

  const txtEarned = `Current points: ${current}`;
  const txtRemain = ` ${remain} points to go`;

  function showTip(text){ tip.textContent = text; tip.style.opacity = '1'; }
  function hideTip(){ tip.style.opacity = '0'; }

  // نحدد هل المؤشر فوق الغامق أم الفاتح حسب زاويته
  function angleFromCenter(evt, svgEl){
    const rect = svgEl.getBoundingClientRect();
    const cx = rect.left + rect.width/2;
    const cy = rect.top  + rect.height/2;
    const x = evt.clientX - cx;
    const y = evt.clientY - cy;

    // atan2: زاوية من محور +X، ضد عقارب الساعة (راديان)
    let deg = Math.atan2(y, x) * 180 / Math.PI; // -180..180
    // حوّل لنطاق 0..360 من محور +X
    if (deg < 0) deg += 360;
    // إحنا لفّينا الدائرة -90deg علشان تبدأ من الساعة 12 وتمشي مع عقارب الساعة
    // علشان نطابق ده: ننقل البداية 90 درجة ونمشي مع عقارب الساعة
    deg = (deg + 90) % 360;

    return deg; // 0 عند أعلى الدائرة، يزيد مع عقارب الساعة
  }

  // أبقِ الرسالة ظاهرة طول الوقوف، واختفِ عند الخروج
  hitAll.addEventListener('mousemove', (e) => {
    const deg = angleFromCenter(e, hitAll);
    if (deg <= doneAngle) {
      showTip(txtEarned);  // داخل الجزء الغامق
    } else {
      showTip(txtRemain);  // داخل الجزء الفاتح
    }
  });
  hitAll.addEventListener('mouseenter', () => { tip.style.opacity = '1'; });
  hitAll.addEventListener('mouseleave', hideTip);

  // للموبايل: click يثبّت الرسالة لحظيًا
  hitAll.addEventListener('click', (e) => {
    const deg = angleFromCenter(e, hitAll);
    if (deg <= doneAngle) showTip(txtEarned); else showTip(txtRemain);
  });

  // إخفاء عند الضغط خارج العنصر
  document.addEventListener('click', (e)=> {
    if (!wrap.contains(e.target)) hideTip();
  });
})();
</script>
@endif







</div>
