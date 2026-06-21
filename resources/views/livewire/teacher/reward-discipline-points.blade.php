<div
  class="w-100"
  wire:poll.30s.visible="refreshPointsLabState"
  wire:loading.class="pe-none"
  wire:target="setTab,confirmBehaviorWithDescription,selectModalBehavior,openBehaviorHistory,openPunishmentHistory,setHistoryTypeFilter,setHistoryTab,setSubjectFilter,applyHistoryDateRange,clearHistoryDateRange,loadMoreHistory,loadMoreTaskHistory,loadMoreBehaviorHistory,loadMorePunishmentHistory"
>
<div>
    @once
      @vite([
        'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.scss',
        'resources/assets/vendor/libs/moment/moment.js',
        'resources/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js',
      ])
    @endonce

    <style>
    .text-small{
            font-size: 10px;

    }
    .shadow_success{
        box-shadow: 0 .35rem .85rem rgba(40, 199, 111, .22) !important;
    }
    .shadow_warning{
        box-shadow: 0 .35rem .85rem rgba(255, 159, 67, .2) !important;
    }
    .shadow_danger{
        box-shadow: 0 .35rem .85rem rgba(255, 76, 81, .18) !important;
    }
   
        .behavior-clickable {
  cursor: pointer;
}

.behavior-readonly {
  cursor: default;
}

  .points-tab-button {
    border-radius: .5rem;
    font-weight: 700;
    min-height: 2.65rem;
  }

  .points-tab-button.active {
    border: 1px solid currentColor;
  }

  .behavior-count-pill {
    min-width: 1.65rem;
    height: 1.35rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 .42rem;
    font-weight: 800;
    font-size: .78rem;
    box-shadow: 0 .25rem .65rem rgba(67, 89, 113, .12);
  }

  .points-analysis-card .card-body {
    padding: 1.15rem 1.25rem 1rem;
  }

  .points-analysis-grid {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 1.15rem;
    align-items: start;
  }

  .points-analysis-metric {
    min-width: 0;
  }

  .points-analysis-metric-end {
    text-align: end;
  }

  .points-analysis-label {
    font-size: .82rem;
    font-weight: 700;
    color: var(--bs-heading-color);
    margin-bottom: .35rem;
  }

  .points-analysis-value {
    font-size: 1rem;
    font-weight: 800;
    line-height: 1.1;
    color: var(--bs-heading-color);
    white-space: nowrap;
  }

  .points-analysis-points {
    display: block;
    margin-top: .35rem;
  }

  .points-analysis-vs {
    position: absolute;
    inset-inline-start: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    z-index: 1;
  }

  [dir="rtl"] .points-analysis-vs {
    transform: translate(50%, -50%);
  }

  .points-analysis-vs .badge-divider-bg {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.65rem;
    height: 1.65rem;
    border-radius: 999px;
  }

  .points-analysis-progress {
    height: .58rem;
    border-radius: 999px;
    overflow: hidden;
  }

  /*.point-circle{*/
  /*  width: 38px;*/
  /*  height: 38px;*/
  /*  border-radius: 999px;*/
  /*  display: inline-flex;*/
  /*  align-items: center;*/
  /*  justify-content: center;*/
  /*  border: 1px solid #d1d5db;*/
  /*  cursor: pointer;*/
  /*  user-select: none;*/
  /*  font-weight: 600;*/
  /*}*/
  .points-theme-success .point-pill input:checked + .point-circle{
    border-width: 2px;
    background-color: color-mix(in sRGB, var(--bs-paper-bg) var(--bs-bg-label-tint-amount), var(--bs-success));
  }
  .points-theme-warning .point-pill input:checked + .point-circle{
    border-width: 2px;
    background-color: color-mix(in sRGB, var(--bs-paper-bg) var(--bs-bg-label-tint-amount), var(--bs-warning));
  }
  .points-theme-danger .point-pill input:checked + .point-circle{
    border-width: 2px;
    background-color: color-mix(in sRGB, var(--bs-paper-bg) var(--bs-bg-label-tint-amount), var(--bs-danger));
  }


  
  
  
  
  

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
  
   .w14-donut-responsive {
    width: 100px;
    height: 100px;
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
      width: 100px;
      height: 100px;
    }
      .student_points_circle{
        width: 100px !important;
      height: 100px !important;
    }
}
 @media (min-width: 1200px) and (max-width: 1350px) {
      .w14-donut-responsive {
      width: 100px !important;
      height: 100px !important;
    }
       .student_points_circle{
        width: 100px !important;
      height: 100px !important;
    }
}

  @media (max-width: 370px) {
      .behavior_nomobile{
          display: none !important;
      }
       .behavior_mobile{
          display: block !important;
      }
      
        .circle_nomobile{
          display: none !important;
      }
       .circle_mobile{
          display: block !important;
      }
      
      
      
     
      
  }
  @media (min-width: 435px) and (max-width: 575.98px) {
  .behavior-col {
    flex: 0 0 auto;
    width: 50% !important; /* زي col-6 */
  }
}

  @media (min-width: 768px) and (max-width: 1199.98px) {
    .points-analysis-card .card-body {
      padding-inline: 1.05rem;
    }

    .points-analysis-grid {
      gap: .85rem;
    }

    .points-analysis-label {
      font-size: .78rem;
    }

    .points-analysis-value {
      font-size: .92rem;
    }
  }

  .points-subject-control {
    display: none;
    align-items: center;
    justify-content: space-between;
    gap: .85rem;
    border: 1px solid color-mix(in srgb, var(--bs-primary) 14%, var(--bs-border-color));
    border-radius: .7rem;
    background: color-mix(in srgb, var(--bs-primary) 5%, var(--bs-paper-bg));
    box-shadow: 0 .35rem 1rem rgba(67, 89, 113, .08);
    padding: .65rem .75rem;
    margin-bottom: 1rem;
  }

  .points-subject-control__meta {
    display: inline-flex;
    align-items: center;
    gap: .6rem;
    min-width: 0;
  }

  .points-subject-control__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.15rem;
    height: 2.15rem;
    border-radius: .55rem;
    color: var(--bs-primary);
    background: color-mix(in srgb, var(--bs-primary) 14%, var(--bs-paper-bg));
    flex: 0 0 auto;
  }

  .points-subject-control__text {
    min-width: 0;
  }

  .points-subject-control__label {
    color: var(--bs-heading-color);
    font-weight: 800;
    line-height: 1.15;
  }

  .points-subject-control__hint {
    color: var(--bs-secondary-color);
    font-size: .78rem;
    font-weight: 600;
    line-height: 1.2;
    margin-top: .12rem;
  }

  .points-subject-picker {
    position: relative;
  }

  .points-subject-picker__button {
    align-items: center;
    border-color: color-mix(in srgb, var(--bs-primary) 24%, var(--bs-border-color));
    display: inline-flex;
    font-weight: 700;
    gap: .45rem;
    justify-content: center;
    min-height: 2.4rem;
  }

  .points-subject-picker__button.btn-icon {
    width: 2.4rem;
  }

  .points-subject-picker__menu {
    border: 1px solid color-mix(in srgb, var(--bs-primary) 18%, var(--bs-border-color));
    border-radius: .65rem;
    box-shadow: 0 .65rem 1.5rem rgba(67, 89, 113, .16);
    min-width: min(19rem, calc(100vw - 2rem));
    padding: .35rem;
  }

  .points-subject-picker__item {
    align-items: center;
    border-radius: .45rem;
    display: flex;
    gap: .5rem;
    min-height: 2.45rem;
    white-space: normal;
  }

  .points-subject-picker__item.is-active {
    background: color-mix(in srgb, var(--bs-primary) 12%, var(--bs-paper-bg));
    color: var(--bs-primary);
    font-weight: 800;
  }

  .points-subject-inline {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    margin-bottom: 1rem;
    padding-inline: .35rem;
  }

  .points-history-panel {
    border-top: 1px solid color-mix(in srgb, var(--bs-border-color) 72%, transparent);
    margin-top: 2rem;
    padding-top: 1.35rem;
  }

  .points-history-toolbar {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(17rem, .32fr);
    gap: .9rem;
    align-items: end;
    margin-bottom: 1.1rem;
  }

  .points-history-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.05rem;
  }

  .points-history-title {
    display: flex;
    align-items: center;
    gap: .65rem;
    margin-bottom: 0;
    min-width: 0;
  }

  .points-history-title h5 {
    color: var(--bs-heading-color);
    font-size: 1.08rem;
    font-weight: 800;
    line-height: 1.15;
  }

  .points-history-count {
    border-radius: .45rem;
    font-weight: 800;
    letter-spacing: 0;
    padding: .32rem .55rem;
  }

  .points-history-tabs {
    display: flex;
    align-items: center;
    gap: .22rem;
    border: 1px solid color-mix(in srgb, var(--bs-primary) 14%, var(--bs-border-color));
    border-radius: .75rem;
    background: color-mix(in srgb, var(--bs-primary) 5%, var(--bs-paper-bg));
    box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--bs-paper-bg) 72%, transparent);
    padding: .25rem;
    flex: 0 0 auto;
    margin: 0;
    width: max-content;
  }

  .points-history-tabs .nav-item {
    margin: 0;
  }

  .points-history-tabs .nav-link {
    align-items: center;
    border-radius: .58rem;
    color: var(--bs-heading-color);
    display: inline-flex;
    font-weight: 700;
    justify-content: center;
    min-height: 2.2rem;
    min-width: 6.6rem;
    padding: .42rem .95rem;
  }

  .points-history-tabs .nav-link.active {
    background: var(--bs-primary);
    box-shadow: 0 .45rem .9rem rgba(var(--bs-primary-rgb), .24);
    color: var(--bs-white);
  }

  .points-history-filters {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .9rem;
  }

  .points-history-filter-card {
    border: 0;
    border-radius: .5rem;
    padding: .95rem 1rem;
    background: var(--bs-paper-bg);
    box-shadow: 0 .35rem 1rem rgba(67, 89, 113, .1);
    text-align: start;
    min-height: 6.15rem;
    width: 100%;
    overflow: hidden;
    transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
  }

  .points-history-filter-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 .55rem 1.25rem rgba(67, 89, 113, .14);
  }

  .points-history-filter-card.is-active {
    border: 2px solid color-mix(in srgb, var(--history-filter-tone) 58%, white);
    background: color-mix(in srgb, var(--history-filter-tone) 8%, var(--bs-paper-bg));
    padding: calc(.95rem - 2px) calc(1rem - 2px);
  }

  .points-history-filter-top {
    display: flex;
    align-items: center;
    gap: .85rem;
    margin-bottom: .65rem;
  }

  .points-history-filter-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.4rem;
    height: 2.4rem;
    border-radius: .45rem;
    color: var(--history-filter-tone);
    background: color-mix(in srgb, var(--history-filter-tone) 15%, var(--bs-paper-bg));
  }

  .points-history-filter-value {
    font-size: 1.35rem;
    font-weight: 800;
    color: var(--bs-heading-color);
    line-height: 1;
  }

  .points-history-filter-label {
    color: var(--bs-secondary-color);
    font-weight: 600;
    line-height: 1.2;
    display: block;
    overflow-wrap: anywhere;
  }

  .points-history-filter-success { --history-filter-tone: var(--bs-success); }
  .points-history-filter-warning { --history-filter-tone: var(--bs-warning); }
  .points-history-filter-danger { --history-filter-tone: var(--bs-danger); }
  .points-history-filter-info { --history-filter-tone: var(--bs-info); }

  .points-history-date {
    min-width: 0;
  }

  .points-history-date-card {
    border: 0;
    border-radius: .5rem;
    background: var(--bs-paper-bg);
    box-shadow: 0 .35rem 1rem rgba(67, 89, 113, .1);
    padding: .82rem .9rem;
  }

  .points-history-list {
    display: grid;
    gap: .55rem;
  }

  .points-history-card {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: .85rem;
    align-items: center;
    border: 1px solid color-mix(in srgb, var(--history-accent, var(--bs-primary)) 16%, var(--bs-border-color));
    border-left-width: .24rem;
    border-radius: .5rem;
    background: var(--bs-paper-bg);
    box-shadow: 0 .28rem .85rem rgba(67, 89, 113, .08);
    padding: .85rem .95rem;
  }

  .points-history-card-positive { --history-accent: var(--bs-success); }
  .points-history-card-slip { --history-accent: var(--bs-warning); }
  .points-history-card-no-way { --history-accent: var(--bs-danger); }
  .points-history-card-consequences { --history-accent: var(--bs-info); }

  .points-history-main {
    min-width: 0;
  }

  .points-history-heading {
    display: flex;
    align-items: center;
    gap: .5rem;
    min-width: 0;
  }

  .points-history-name {
    font-weight: 800;
    color: var(--bs-heading-color);
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .points-history-note {
    color: var(--bs-secondary-color);
    margin-top: .28rem;
    line-height: 1.35;
  }

  .points-history-meta {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-top: .35rem;
    color: var(--bs-secondary-color);
    font-size: .78rem;
  }

  .points-history-side {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
  }

  .points-history-points {
    font-weight: 800;
    white-space: nowrap;
  }

  .points-history-icon {
    width: 2.1rem;
    height: 2.1rem;
    object-fit: contain;
  }

  @media (max-width: 700px) {
    .points-subject-control {
      display: flex;
      align-items: stretch;
      flex-direction: column;
      gap: .7rem;
      padding: .75rem;
      margin-inline: .5rem;
    }

    .points-subject-picker__button {
      width: 100%;
    }

    .points-subject-picker__menu {
      width: min(100%, 24rem);
    }

    .points-subject-inline {
      display: none;
    }
  }

  .points-history-task-card {
    min-height: 5.65rem;
    padding: .9rem 1rem;
  }

  .points-history-task-card .points-history-filter-top {
    margin-bottom: .55rem;
  }

  .points-history-filter-points {
    --history-filter-tone: var(--bs-primary);
  }

  .points-history-filter-points .points-history-filter-icon {
    background: color-mix(in srgb, var(--bs-primary) 14%, var(--bs-paper-bg));
    color: var(--bs-primary);
    height: 1.8rem;
    min-width: 2.75rem;
    width: auto;
    border-radius: 999px;
  }

  .points-history-points-mark {
    font-size: .72rem;
    font-weight: 900;
    letter-spacing: 0;
    line-height: 1;
    text-transform: uppercase;
  }

  @media (min-width: 701px) {
    .points-tab-row {
      display: grid !important;
      grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
      gap: .85rem;
      align-items: center;
      justify-content: center;
      width: min(56rem, 100%);
      margin-inline: auto;
      padding-inline: 0;
    }

    .points-tab-item {
      flex: none !important;
      margin-bottom: 1rem;
      max-width: none;
      padding-inline: 0 !important;
      width: auto !important;
    }

    .points-subject-inline {
      margin-bottom: 1rem;
      padding-inline: 0;
    }
  }

  @media (max-width: 575.98px) {
    .points-subject-picker__menu {
      width: 100%;
    }

    .points-history-head {
      display: block;
      margin-bottom: .85rem;
    }

    .points-history-tabs {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      width: 100%;
    }

    .points-history-tabs .nav-link {
      min-width: 0;
      width: 100%;
      text-align: center;
    }

    .points-history-title {
      align-items: flex-start;
      flex-wrap: wrap;
      margin-bottom: .75rem;
    }

    .points-history-toolbar {
      grid-template-columns: 1fr;
    }

    .points-history-date {
      min-width: 0;
      width: 100%;
    }

    .points-history-filters {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .65rem;
    }

    .points-history-filter-card {
      min-height: 5.35rem;
      padding: .8rem;
    }

    .points-history-filter-card.is-active {
      padding: calc(.8rem - 2px);
    }

    .points-history-filter-top {
      gap: .6rem;
      margin-bottom: .45rem;
    }

    .points-history-filter-icon {
      width: 2.1rem;
      height: 2.1rem;
    }

    .points-history-filter-value {
      font-size: 1.15rem;
    }

    .points-history-card {
      align-items: start;
      padding: .8rem .75rem;
    }

    .points-history-side {
      flex-direction: column;
      align-items: flex-end;
      gap: .35rem;
    }

    .points-history-icon {
      width: 1.8rem;
      height: 1.8rem;
    }
  }

  @media (min-width: 576px) and (max-width: 1199.98px) {
    .points-history-toolbar {
      grid-template-columns: 1fr;
    }

    .points-history-filters {
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: .65rem;
    }

    .points-history-filter-card {
      min-height: 5.5rem;
      padding: .75rem;
    }

    .points-history-filter-card.is-active {
      padding: calc(.75rem - 2px);
    }

    .points-history-filter-top {
      gap: .55rem;
      margin-bottom: .45rem;
    }

    .points-history-filter-icon {
      width: 2rem;
      height: 2rem;
    }

    .points-history-filter-value {
      font-size: 1.05rem;
    }

    .points-history-filter-label {
      font-size: .78rem;
    }

    .points-history-date-card {
      max-width: 24rem;
    }
  }

  @media (min-width: 1200px) {
    .points-subject-inline {
      margin-inline-start: 0;
    }

    .points-history-date-card {
      min-height: 6.15rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
  }
</style>
@php
  $behaviorModal = $this->behaviorModalView;
  $selectedSubjectTitle = 'All Subjects';
  if ($selectedSubjectId) {
    $selectedSubjectTitle = collect($subjectFilters ?? [])
      ->firstWhere('id', (int) $selectedSubjectId)['title'] ?? 'Selected subject';
  } elseif ($userRole === 'teacher') {
    $selectedSubjectTitle = $subjectFilters[0]['title'] ?? 'Selected subject';
  }
@endphp

@if($userRole !== 'teacher' && count($subjectFilters ?? []) > 0)
  <div class="points-subject-control mx-lg-12">
    <div class="points-subject-control__meta">
      <span class="points-subject-control__icon" aria-hidden="true">
        <i class="icon-base ti tabler-filter icon-18px"></i>
      </span>
      <div class="points-subject-control__text">
        <div class="points-subject-control__label">
          {{ $selectedSubjectId ? 'Subject filter' : 'All Subjects' }}
        </div>
          <div class="points-subject-control__hint">Behavior and task history</div>
      </div>
    </div>

      <div class="dropdown points-subject-picker">
        <button
          type="button"
          class="btn btn-label-primary points-subject-picker__button"
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
          <span class="text-truncate">{{ $selectedSubjectTitle }}</span>
          <i class="icon-base ti tabler-chevron-down icon-16px"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end points-subject-picker__menu">
          <button
            type="button"
            class="dropdown-item points-subject-picker__item {{ $selectedSubjectId === null ? 'is-active' : '' }}"
            wire:click="setSubjectFilter(null)"
          >
            <i class="icon-base ti tabler-layout-grid icon-16px"></i>
            <span>All Subjects</span>
          </button>
          @foreach($subjectFilters as $subject)
            <button
              wire:key="points-subject-filter-mobile-{{ $subject['id'] }}"
              type="button"
              class="dropdown-item points-subject-picker__item {{ (int) $selectedSubjectId === (int) $subject['id'] ? 'is-active' : '' }}"
              wire:click="setSubjectFilter({{ (int) $subject['id'] }})"
            >
              <i class="icon-base ti tabler-book-2 icon-16px"></i>
              <span>{{ $subject['title'] }}</span>
            </button>
          @endforeach
        </div>
      </div>
  </div>
@endif
    
    
    @if(($userRole === 'teacher') || ($userRole === 'parent'))



@php
  $tabColor = [
    'Positive' => 'success',
    'Slip'     => 'warning',
    'No Way'   => 'danger',
  ];
@endphp

<ul class="nav nav-pills mb-3 row col-12 d-flex flex-wrap flex-column flex-lg-row  flex-md-row flex-sm-row  align-items-center justify-content-center points-tab-row">
  @foreach (['Positive','Slip','No Way'] as $tab)
    @php $c = $tabColor[$tab]; @endphp
    <li class="col-xl-2 col-lg-3 col-md-4 col-sm-4 me-0 pe-0 col-11 mb-3 points-tab-item" wire:key="points-tab-top-{{ $tab }}">
      <button
        class="col-12 btn points-tab-button {{ $activeTab === $tab ? "active btn-{$c} shadow_{$c}" : "btn-label-{$c}" }}"
        type="button"
        wire:click="setTab('{{ $tab }}')"
      >
        {{ $tab === 'No Way' ? 'Red Flag' : $tab }}
      </button>
    </li>
  @endforeach
  @if($userRole !== 'teacher' && count($subjectFilters ?? []) > 0)
    <li class="points-subject-inline">
        <div class="dropdown points-subject-picker">
          <button
            type="button"
            class="btn btn-label-primary btn-icon points-subject-picker__button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            title="Subject filter: {{ $selectedSubjectTitle }}"
            aria-label="Subject filter"
          >
            <i class="icon-base ti tabler-filter icon-16px"></i>
          </button>
          <div class="dropdown-menu dropdown-menu-end points-subject-picker__menu">
            <button
              type="button"
              class="dropdown-item points-subject-picker__item {{ $selectedSubjectId === null ? 'is-active' : '' }}"
              wire:click="setSubjectFilter(null)"
            >
              <i class="icon-base ti tabler-layout-grid icon-16px"></i>
              <span>All Subjects</span>
            </button>
            @foreach($subjectFilters as $subject)
              <button
                wire:key="points-subject-filter-inline-{{ $subject['id'] }}"
                type="button"
                class="dropdown-item points-subject-picker__item {{ (int) $selectedSubjectId === (int) $subject['id'] ? 'is-active' : '' }}"
                wire:click="setSubjectFilter({{ (int) $subject['id'] }})"
              >
                <i class="icon-base ti tabler-book-2 icon-16px"></i>
                <span>{{ $subject['title'] }}</span>
              </button>
            @endforeach
          </div>
        </div>
    </li>
  @endif
</ul>









  
  
  
  
  @php
    $tabBehaviors = match($activeTab) {
        'Positive' => $positiveBehaviors,
        'Slip'     => $slipBehaviors,
        'No Way'   => $noWayBehaviors,
        default    => $positiveBehaviors,
    };
        $isPositive = $activeTab === 'Positive';

    
@endphp


  <div class="row g-8 px-lg-12 mt-2">
      
    @forelse($tabBehaviors as $item)
    
    <div class="col-sm-6 col-md-4 col-xl-3 col-lg-3 col-12 behavior-col" wire:key="behavior-card-top-{{ $activeTab }}-{{ $item['id'] }}">
 
      
      
      @php
  $activeColor = $tabColor[$activeTab] ?? 'primary';
@endphp
      
      
       <div
    class="py-3 card behavior-card h-100  shadow-sm position-relative {{ $item['teacher_desc'] 
        ? 'behavior-readonly border bg-label-' . $activeColor . ' text-' . $activeColor . ' border-' . $activeColor 
        : 'border-0 behavior-clickable' 
    }}
            "
    style="border-radius: 18px;"
   
   @if((int)$item['teacher_desc'] === 0)
    wire:click="openBehaviorHistory({{ (int)$item['id'] }})"
  @endif >

    <div class="card-body text-center p-3 d-flex flex-column justify-content-between">
        @if(($userRole === 'teacher')|| ($userRole === 'parent'))
        <button type="button"
        class="btn btn-icon btn-sm rounded-circle btn-outline-{{ $activeColor }} position-absolute top-0 end-0 m-3"
        wire:click.prevent.stop="startBehavior({{ $item['id'] }})"
        wire:loading.attr="disabled"
        wire:target="startBehavior"
        title="Add">
            @if((int)$item['teacher_desc'] === 0)
          @if($activeTab === 'Positive')
    <i class="ti tabler-plus" style="font-size: 11px;"></i>{{ $item['points'] }} 
@else
    <i class="ti tabler-minus" style="font-size: 11px;"></i>{{ $item['points'] }} 
    
    @endif
    
    @else
       @if($activeTab === 'Positive')
    <i class="ti tabler-plus" style="font-size: 11px;"></i>
@else
    <i class="ti tabler-minus" style="font-size: 11px;"></i>
    
    @endif
    
@endif

</button>
 @endif
      <div>
        <div class="mb-2">
          @if(!empty($item['discipline_icon_path']))
            <img
              src="{{ $this->assetPath($item['discipline_icon_path']) }}"
              alt="icon"
              class="img-fluid"
              width="40"
              height="40"
              decoding="async"
              style="width:40px;height:40px;object-fit:contain;"
            >
          @else
            <span class="ti {{ $isPositive ? 'tabler-thumb-up' : 'tabler-alert-triangle' }} fs-2"></span>
          @endif
        </div>

        <div class="fw-semibold small mb-1 text-truncate">
          {{ $item['title'] }}
        </div>
      </div>

@if($item['teacher_desc'] == 0)
      <!--<div class="small {{ $isPositive ? 'text-success' : 'text-danger' }} mt-2">-->
      <!--  {{ $isPositive ? '+' : '−' }}{{ $item['points'] }} pts-->
      <!--</div>-->
   <div class="text-small text-muted mt-2">
            Tap to view history
          </div>
        @if(!empty($behaviorCounts[$item['id']] ?? null))
          <span class="position-absolute bottom-0 end-0 behavior-count-pill bg-label-{{ $activeColor }} text-{{ $activeColor }} m-2">
            x{{ $behaviorCounts[$item['id']] }}
          </span>
        @endif
@else
 <div class="text-small text-muted mt-2">
           Tap to Customize
          </div>
      
      @endif
  </div>
</div>
</div>

    @empty
      <div class="col-12">
        <p class="text-muted mb-0">No behaviors defined yet.</p>
      </div>
    @endforelse
  </div>

  {{-- Toast / Banner تأكيد زي ClassDojo --}}
{{-- Toast / Banner Confirmation Message --}}
@if($recentAward && (( $userRole === 'teacher') || ($userRole === 'parent')))
  <div
   wire:key="behavior-toast-{{ $recentAward['id'] }}"
    x-data="{ show: true }"
    x-show="show"
    x-transition.duration.400ms
    x-init="setTimeout(() => { show = false; $wire.clearRecentAward(); }, 1500)"
    class="behavior-toast position-fixed top-50 start-50 translate-middle"
    style="z-index: 2000;"
  >
    <div
      class="card border-0 shadow-lg text-center px-5 py-4"
      style="border-radius: 0.45rem; background-color: #ffffff; width: min(21rem, calc(100vw - 2rem));"
    >
      <div class="d-flex flex-column align-items-center justify-content-center gap-3">
        <h4 class="fw-bold mb-1" style="color: #2b2b2b;">
          {{ $recentAward['student_name'] }}
        </h4>

        <p class="fs-6 mb-0" style="color: #333;">
          {{ $recentAward['type'] === 'Positive' ? '+' : '-' }}{{ $recentAward['points'] }}
          <span style="color: {{ $recentAward['type'] === 'Positive' ? '#28a745' : '#d9534f' }};">
            for {{ $recentAward['title'] }}
          </span>
        </p>

      </div>
    </div>
  </div>
@endif













@include('livewire.teacher.partials.behavior-history-list')










</div>

@endif





 @if($userRole === 'student')
 
     @php
  $tabColor = [
    'Positive' => 'success',
    'Slip'     => 'warning',
    'No Way'   => 'danger',
  ];
@endphp
  
   @php
  // --- القيم القادمة من الكلاس/الكونترولر ---
  $pos = (int)($total_post_point ?? 0);
  $neg = (int)($total_negative_point ?? 0);

  $sum = max(0, $pos + $neg);
  // نتعامل مع حالة صفر (مافيش نقاط) علشان ما نقسمش على صفر
  $posRatio = $sum > 0 ? $pos / $sum : 0;
  $negRatio = $sum > 0 ? $neg / $sum : 0;

  // إعدادات الرسم
  $size   = 100;        // حجم الـ SVG
  $stroke = 14;         // سُمك الحلقة (أعرض شوية زي ما طلبتي)
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
@endphp
  
  
   <ul class="nav nav-pills mb-3 row col-12 d-flex flex-wrap mt-3 flex-column flex-lg-row  flex-md-row flex-sm-row  align-items-center justify-content-center points-tab-row">
  @foreach (['Positive','Slip','No Way'] as $tab)
    @php $c = $tabColor[$tab]; @endphp
    <li class="col-xl-2 col-lg-3 col-md-4 col-sm-4 me-0 pe-0 col-11 mb-3 points-tab-item" wire:key="points-tab-secondary-{{ $tab }}">
      <button
        class="col-12 btn points-tab-button {{ $activeTab === $tab ? "active btn-{$c} shadow_{$c}" : "btn-label-{$c}" }}"
        type="button"
        wire:click="setTab('{{ $tab }}')"
      >
        {{ $tab === 'No Way' ? 'Red Flag' : $tab }}
      </button>
    </li>
  @endforeach
  @if(count($subjectFilters ?? []) > 0)
    <li class="points-subject-inline">
      <div class="dropdown points-subject-picker">
        <button
          type="button"
          class="btn btn-label-primary btn-icon points-subject-picker__button"
          data-bs-toggle="dropdown"
          aria-expanded="false"
          title="Subject filter: {{ $selectedSubjectTitle }}"
          aria-label="Subject filter"
        >
          <i class="icon-base ti tabler-filter icon-16px"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end points-subject-picker__menu">
          <button
            type="button"
            class="dropdown-item points-subject-picker__item {{ $selectedSubjectId === null ? 'is-active' : '' }}"
            wire:click="setSubjectFilter(null)"
          >
            <i class="icon-base ti tabler-layout-grid icon-16px"></i>
            <span>All Subjects</span>
          </button>
          @foreach($subjectFilters as $subject)
            <button
              wire:key="points-subject-filter-secondary-{{ $subject['id'] }}"
              type="button"
              class="dropdown-item points-subject-picker__item {{ (int) $selectedSubjectId === (int) $subject['id'] ? 'is-active' : '' }}"
              wire:click="setSubjectFilter({{ (int) $subject['id'] }})"
            >
              <i class="icon-base ti tabler-book-2 icon-16px"></i>
              <span>{{ $subject['title'] }}</span>
            </button>
          @endforeach
        </div>
      </div>
    </li>
  @endif
</ul>
  
  

  @php
  $tabBehaviors = match($activeTab) {
        'Positive' => $positiveBehaviors,
        'Slip'     => $slipBehaviors,
        'No Way'   => $noWayBehaviors,
        default    => $positiveBehaviors,
    };
    $isPositive = $activeTab === 'Positive';
  @endphp
  
  

<div class="row g-8 px-lg-12 mt-2">

  {{-- أول كارت: Punishments Count يظهر فقط في Slip و No Way --}}
  @if(in_array($activeTab, ['Slip', 'No Way']))
    @php
      $count = $activeTab === 'Slip' ? $slipPunishmentsCount : $noWayPunishmentsCount;
      $badgeColor = $activeTab === 'Slip' ? 'warning' : 'danger';
      $title = $activeTab === 'Slip' ? 'Slip Consequence' : 'Red Flag Consequence';
    @endphp

    <div class="col-sm-6 col-md-4 col-xl-3 col-lg-3 col-12">
      <div class="card behavior-card h-100 shadow-sm border-0 behavior-clickable position-relative bg-label-{{ $badgeColor }}"
           style="border-radius: 18px;"
           wire:click="openPunishmentHistory('{{ $activeTab }}')">

        @if($count !== 0)
          <span class="position-absolute bottom-0 end-0 behavior-count-pill bg-label-{{ $badgeColor }} text-{{ $badgeColor }} m-2">
            x{{ $count }}
          </span>
        @endif

        <div class="card-body text-center p-3 d-flex flex-column justify-content-between">
          <div>
            <div class="mb-2">
              <i class="ti tabler-alert-triangle fs-2 text-{{ $badgeColor }}"></i>
            </div>
            <div class="fw-semibold small mb-1 text-truncate">
              {{ $title }}
            </div>
          </div>

          <div class="text-small text-muted mt-2">
            Tap to view history
          </div>
        </div>

      </div>
    </div>
  @endif
  
  
  
  
  
  @if($activeTab === 'Positive')
  
  
  
  
  
  
  
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

<div class="col-sm-6 col-md-4 col-xl-3 col-lg-3 col-12 ">
  <div class="card h-100 bg-label-success points-analysis-card" style="border-radius: 18px;">

  

    <div class="card-body">

      <div class="points-analysis-grid">
        {{-- Positive --}}
        <div class="points-analysis-metric">
          <div class="points-analysis-label">Positive</div>
          <div class="points-analysis-value">{{ $posPct }}%</div>
          <small class="points-analysis-points text-body-secondary">{{ $pos }} pts</small>
        </div>

        {{-- VS --}}
        <div class="points-analysis-vs">
          <span class="badge-divider-bg bg-label-secondary">VS</span>
        </div>

        {{-- Negative --}}
        <div class="points-analysis-metric points-analysis-metric-end">
          <div class="points-analysis-label">Negative</div>
          <div class="points-analysis-value">{{ $negPct }}%</div>
          <small class="points-analysis-points text-body-secondary">{{ $negAbs }} pts</small>
        </div>
      </div>

      {{-- Progress bar stacked --}}
      <div class="d-flex align-items-center mt-3 mb-0">
        <div class="progress w-100 points-analysis-progress">
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
</div>

  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
       
    
    
    
    
    
    
    
    
    
    
    
    
    
       
    
    
    
    
    
    
  
  @endif
  
 @if(in_array($activeTab, ['Positive','Slip','No Way']))
  @php
    $badgeColor = match ($activeTab) {
      'Positive' => 'success',
      'Slip'     => 'warning',
      'No Way'   => 'danger',
      default    => 'primary',
    };
  @endphp
@endif


    @forelse($tabBehaviors as $item)
     @if((int)$item['teacher_desc'] === 0)
    <div class="col-sm-6 col-md-4 col-xl-3 col-lg-3 col-12 behavior-col" wire:key="behavior-card-secondary-{{ $activeTab }}-{{ $item['id'] }}">
  <div
      class="card behavior-card h-100 shadow-sm position-relative border-0 behavior-clickable"
    style="border-radius: 18px;"
   
  
    wire:click="openBehaviorHistory({{ (int)$item['id'] }})"
  >
   
      
      @if(!empty($behaviorCounts[$item['id']] ?? null))
        <span class="position-absolute bottom-0 end-0 behavior-count-pill bg-label-{{ $badgeColor }} text-{{ $badgeColor }} m-2">
          x{{ $behaviorCounts[$item['id']] }}
        </span>
      @endif
      
      
      
    <div class="card-body text-center p-3 d-flex flex-column justify-content-between">
      <div>
        <div class="mb-2">
          @if(!empty($item['discipline_icon_path']))
            <img
              src="{{ $this->assetPath($item['discipline_icon_path']) }}"
              alt="icon"
              class="img-fluid"
              width="40"
              height="40"
              decoding="async"
              style="width:40px;height:40px;object-fit:contain;"
            >
          @else
            <span class="ti {{ $isPositive ? 'tabler-thumb-up' : 'tabler-alert-triangle' }} fs-2"></span>
          @endif
        </div>

        <div class="fw-semibold small mb-1 text-truncate">
          {{ $item['title'] }}
        </div>
      </div>

      <!--<div class="small {{ $isPositive ? 'text-success' : 'text-danger' }} mt-2">-->
      <!--  {{ $isPositive ? '+' : '−' }}{{ $item['points'] }} pts-->
      <!--</div>-->
      
      
      
       <!--<div class="text-small  text-{{ $badgeColor }}  mt-2">-->
                  <div class="text-small  text-muted  mt-2">

            Tap to view history
          </div>
    </div>
  </div>
</div>
 @endif
    @empty
      <div class="col-12">
        <p class="text-muted mb-0">No behaviors defined yet.</p>
      </div>
    @endforelse
  </div>

  {{-- Toast / Banner تأكيد زي ClassDojo --}}
{{-- Toast / Banner Confirmation Message --}}
@if(false && $recentAward &&( ($userRole === 'teacher') || ($userRole === 'parent')))
  <div
   wire:key="behavior-toast-{{ $recentAward['id'] }}"
    x-data="{ show: true }"
    x-show="show"
    x-transition.duration.400ms
    x-init="setTimeout(() => show = false, 1500)"
    class="behavior-toast position-fixed top-50 start-50 translate-middle"
    style="z-index: 2000;"
  >
    <div
      class="card border-0 shadow-lg text-center py-4 px-5"
      style="border-radius: 30px; background-color: #ffffff; min-width: 420px;"
    >
      <div class="d-flex flex-column align-items-center justify-content-center gap-3">
        {{-- Monster Icon --}}
      {{--  <img src="{{ asset('images/monsters/monster-1.png') }}"
             alt="monster"
             style="height: 90px;">--}}

        {{-- Student Name --}}
        <h4 class="fw-bold mb-1" style="color: #2b2b2b;">
          {{ $recentAward['student_name'] }}
        </h4>

        {{-- Behavior Description --}}
        <p class="fs-5 mb-0" style="color: #333;">
          {{ $recentAward['type'] === 'Positive' ? '+' : '−' }}{{ $recentAward['points'] }}
          <span style="color: {{ $recentAward['type'] === 'Positive' ? '#28a745' : '#d9534f' }};">
            for {{ $recentAward['title'] }}
          </span>
        </p>

        {{-- Behavior Icon --}}
        @if($recentAward['icon_path'])
          <img
            src="{{ $this->assetPath($recentAward['icon_path']) }}"
            alt="icon"
            style="height: 60px;"
          >
        @endif
      </div>
    </div>
  </div>
@endif

    
    
    
    
    
    
    
    
    
    
@include('livewire.teacher.partials.behavior-history-list')
 

@endif






@if($userRole === 'parent')
  {{-- Modal لوصف السلوك --}}
  <div wire:ignore.self class="modal fade" id="behaviorDescModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Behavior for <span class="text-primary"> {{ $studentName }}</span></h5>
          <button type="button"
                  class="btn-close"
                  aria-label="Close"
                  data-bs-dismiss="modal"
                  wire:click="cancelBehaviorDescription">
          </button>
        </div>

        <div class="modal-body">
          <!--<p class="fw-semibold mb-1">{{ $studentName }}</p>-->

          @if($pendingBehaviorId)
            <p class="mb-2 text-body-secondary">
              {{ $pendingBehaviorTitle }}
          {{--    <span class="badge rounded-pill {{ $pendingType === 'Positive' ? 'bg-label-success' : 'bg-label-danger' }}">
                {{ $pendingType === 'Positive' ? '+' : '−' }}{{ $pendingBehaviorPoints }} pts
              </span>--}}
            </p>
          @endif



          <div class="mb-3">
            <label class="form-label">Behavior</label>

            <div class="dropdown">
              <button class="btn btn-outline-{{ $behaviorModal['color'] }} w-100 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="d-flex align-items-center gap-2">
                  @if(!empty($behaviorModal['selected']['icon_url']))
                    <img src="{{ $behaviorModal['selected']['icon_url'] }}" style="height:24px;width:24px;" alt="">
                  @endif
                  <span>{{ $behaviorModal['selected']['title'] ?? 'Select behavior' }}</span>
                </span>
                <i class="ti tabler-chevron-down icon-md"></i>
              </button>

              <ul class="dropdown-menu w-100" style="max-height: 320px; overflow:auto;">
                @forelse($behaviorModal['behaviors'] as $b)
                  <li>
                    <button
                      type="button"
                      class="dropdown-item d-flex align-items-center gap-2 {{ $behaviorModal['selected_id'] === (int) $b['id'] ? 'active' : '' }}"
                      wire:click="selectModalBehavior({{ (int) $b['id'] }})">
                      @if(!empty($b['icon_url']))
                        <img src="{{ $b['icon_url'] }}" style="height:22px;width:22px;" alt="">
                      @endif
                      <span>{{ $b['title'] }}</span>
                    </button>
                  </li>
                @empty
                  <li>
                    <span class="dropdown-item-text text-muted">No behaviors found.</span>
                  </li>
                @endforelse
              </ul>
            </div>

            @error('selectedBehaviorId')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <label class="form-label">Points</label>

            <div class="d-flex flex-wrap gap-2 points-theme-{{ $behaviorModal['color'] }}">
              @foreach($behaviorModal['point_options'] as $opt)
                <label class="point-pill" wire:key="points-{{ $behaviorModal['type'] }}-{{ $behaviorModal['selected_id'] ?: 'none' }}-{{ $opt }}">
                  <input type="radio" class="d-none" wire:model="pointsInput" value="{{ $opt }}">
                  <span class="btn btn-icon btn-sm rounded-circle btn-outline-{{ $behaviorModal['color'] }} point-circle">
                    {{ $behaviorModal['type'] === 'Positive' ? $opt : ('-'.$opt) }}
                  </span>
                </label>
              @endforeach
            </div>

            @error('pointsInput')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>
     
          
        <!--punishmentAgreements  -->
          
        @if(in_array($pendingType, ['Slip', 'No Way']))
  @php
    $punishColor = $pendingType === 'Slip' ? 'warning' : 'danger';
  @endphp

  <hr class="my-3">

  <div class="row g-3">
    <div class="col-12">
      <label class="form-label">Agreements (click to select)</label>

      <div class="d-flex flex-wrap gap-2">
          {{-- ✅ None option --}}
  <button type="button"
          class="btn btn-sm {{ $selectedPunishmentAgreementId ? 'btn-outline-secondary' : 'btn-secondary' }}"
          wire:click="clearPunishmentSelection">
    None
  </button>
        @forelse($punishmentAgreements as $ag)
          <button type="button"
                  wire:key="punishment-agreement-{{ $pendingType }}-{{ $ag['id'] }}"
                  class="btn btn-sm {{ (int)$selectedPunishmentAgreementId === (int)$ag['id'] ? "btn-{$punishColor}" : 'btn-outline-secondary' }}"
                  wire:click="selectPunishment({{ (int)$ag['id'] }})">
            {{ $ag['title'] }}
          </button>
        @empty
          <div class="alert alert-info w-100 mb-0">No agreements for this type.</div>
        @endforelse
      </div>

      @error('descriptionInput')
        <div class="text-danger small mt-1">{{ $message }}</div>
      @enderror
    </div>
  </div>
@endif
  



          <div class="mb-3 mt-3">
            <label class="form-label">Description</label>
            <textarea
              class="form-control"
              rows="3"
              wire:model="descriptionInput"
              placeholder="Write a short note about what the student did..."></textarea>
          </div>
          
     
          
          
          
          
          
          
        </div>

        <div class="modal-footer">
         {{-- <button type="button"
                  class="btn btn-outline-secondary"
                  data-bs-dismiss="modal"
                  wire:click="cancelBehaviorDescription">
            Cancel
          </button>--}}
          <button type="button"
                  class="btn btn-primary"
                  wire:click="confirmBehaviorWithDescription">
            Save
          </button>
        </div>
      </div>
    </div>
  </div>
@endif



<!--showBehaviorHistoryModal-->

{{-- Behavior History Modal --}}




{{-- Behavior History Modal (Bootstrap) --}}
<div wire:ignore.self class="modal fade" id="behaviorHistoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header">
                        <div class="d-flex align-items-start gap-2 flex-column flex-lg-row  flex-md-row flex-sm-row  ">

        <h5 class="modal-title">Behavior History</h5>
        
        
        <span class="badge bg-label-primary "> {{ count($behaviorHistory) }} / {{ $behaviorHistoryTotal }}</span>
        </div>
        
        <button type="button"
                class="btn-close"
                aria-label="Close"
                wire:click="closeBehaviorHistory"></button>
      </div>

      <div class="modal-body">
          
          
           <div class="list-group">
               
             

               
    @forelse($behaviorHistory as $row)
      @php
        $isPositive = ($row['type'] ?? 'Positive') === 'Positive';
        $rowTypeLabel = ($row['type'] ?? 'Positive') === 'No Way' ? 'Red Flag' : ($row['type'] ?? 'Positive');
        $createdAt  = \Carbon\Carbon::parse($row['created_at'])->format('d M');
      @endphp
      <div class="card border-0 mb-2 p-3 shadow-sm"
           >
        <div class="d-flex align-items-center justify-content-between gap-3">

  



          {{-- اليسار: العنوان + الوصف --}}
          <div class="d-flex flex-column" style="font-size:18px">
              <div class="d-flex align-items-start gap-2 flex-column flex-lg-row  flex-md-row flex-sm-row ">

                             <span class="badge @if($row['type'] == 'Positive') bg-label-success @elseif($row['type'] == 'Slip')  bg-label-warning @else bg-label-danger @endif"> {{ $rowTypeLabel }}</span>
            <div class="fw-semibold">
             {{ $row['title'] }}
            </div>
            </div>
            <div class="text-muted  ps-1">
              
             
               
              
    @if(!empty($row['agreement_title']))
        . <strong>Agreement:</strong> {{ $row['agreement_title'] }}
<br>
      {{--  @if(!empty($row['punishment_description']))
            — {!! nl2br(e($row['punishment_description'])) !!}
        @endif--}}
    @endif

    {{-- Behavior description --}}
    @if(!empty($row['description']))
        . {!! nl2br(e($row['description'])) !!}
    @endif
            
            </div>
            <div class="text-muted small ps-1">
                  {{ $createdAt }}
                </div>
          </div>

          {{-- اليمين: النقاط + الأيقونة --}}
          <div class="d-flex align-items-center gap-3">
            <div class="fw-bold {{ $isPositive ? 'text-success' : 'text-danger' }}">
              {{ $isPositive ? '+' : '−' }}{{ $row['points'] }} pts
            </div>

            @if(!empty($row['discipline_icon_path']))
              <img
                src="{{ $this->assetPath($row['discipline_icon_path']) }}"
                alt="icon"
                width="36"
                height="36"
                loading="lazy"
                decoding="async"
                style="width:36px;height:36px;object-fit:contain;"
              >
            @endif
          </div>
        </div>
      </div>
    @empty
          <p class="text-center text-muted mb-0">No records yet.</p>
        @endforelse
  </div>

  @if(count($behaviorHistory) < $behaviorHistoryTotal)
    <div class="text-center mt-3">
      <button type="button" class="btn btn-label-primary btn-sm" wire:click="loadMoreBehaviorHistory">
        Load 50 more
      </button>
    </div>
  @endif

        </div>
       </div>
       </div>   
        </div>  
          
          
          
         @if($userRole === 'student')

          <div wire:ignore.self class="modal fade" id="punishmentHistoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
        Consequence History ({{ $punishmentHistoryType === 'No Way' ? 'Red Flag' : $punishmentHistoryType }}) {{ count($punishmentHistory) }} / {{ $punishmentHistoryTotal }}
        </h5>
        <button type="button" class="btn-close" aria-label="Close"
                wire:click="closePunishmentHistory"></button>
      </div>

      <div class="modal-body">
        @if(empty($punishmentHistory))
          <p class="text-muted mb-0">No records yet.</p>
        @else
          <div class="list-group">
            @foreach($punishmentHistory as $row)
              @php
                $isPositive = ($row['type'] ?? 'Positive') === 'Positive';
                $rowTypeLabel = ($row['type'] ?? 'Positive') === 'No Way' ? 'Red Flag' : ($row['type'] ?? 'Positive');
                $createdAt  = \Carbon\Carbon::parse($row['created_at'])->format('d M');
              @endphp

              <div class="card border-0 mb-2 p-3 shadow-sm">
                <div class="d-flex align-items-center justify-content-between gap-3">
                  <div class="d-flex flex-column" style="font-size:18px">

                    <div class="d-flex align-items-start gap-2 flex-column flex-lg-row">
                      <span class="badge
                        @if($row['type'] == 'Positive') bg-label-success
                        @elseif($row['type'] == 'Slip') bg-label-warning
                        @else bg-label-danger
                        @endif
                      ">
                        {{ $rowTypeLabel }}
                      </span>

                      <div class="fw-semibold">{{ $row['title'] }}</div>
                    </div>

                    <div class="text-muted ps-1">
                      @if(!empty($row['agreement_title']))
                        . <strong>Agreement:</strong> {{ $row['agreement_title'] }}
                      @endif

                      @if(!empty($row['description']))
                        <br>. {!! nl2br(e($row['description'])) !!}
                      @endif
                    </div>

                    <div class="text-muted small ps-1">{{ $createdAt }}</div>
                  </div>

                  <div class="d-flex align-items-center gap-3">
                    <div class="fw-bold {{ $isPositive ? 'text-success' : 'text-danger' }}">
                      {{ $isPositive ? '+' : '−' }}{{ $row['points'] }} pts
                    </div>

                    @if(!empty($row['discipline_icon_path']))
                      <img
                        src="{{ $this->assetPath($row['discipline_icon_path']) }}"
                        alt="icon"
                        width="36"
                        height="36"
                        loading="lazy"
                        decoding="async"
                        style="width:36px;height:36px;object-fit:contain;"
                      >
                    @endif
                  </div>
                </div>
              </div>

            @endforeach
          </div>

          @if(count($punishmentHistory) < $punishmentHistoryTotal)
            <div class="text-center mt-3">
              <button type="button" class="btn btn-label-primary btn-sm" wire:click="loadMorePunishmentHistory">
                Load 50 more
              </button>
            </div>
          @endif
        @endif
      </div>
    </div>
  </div>
</div>
@endif
          
          
          
          
          
          
          
          
          
          
          
          
          
<script>
window.w14InitPointsHistoryDateRange = function(input) {
  if (!input || typeof window.jQuery === 'undefined' || !window.jQuery.fn.daterangepicker || typeof window.moment === 'undefined') {
    return;
  }

  const $input = window.jQuery(input);
  const componentRoot = input.closest('[wire\\:id]');
  const component = componentRoot ? window.Livewire.find(componentRoot.getAttribute('wire:id')) : null;
  const startDate = input.dataset.startDate ? window.moment(input.dataset.startDate, 'YYYY-MM-DD') : window.moment().startOf('month');
  const endDate = input.dataset.endDate ? window.moment(input.dataset.endDate, 'YYYY-MM-DD') : window.moment();

  if ($input.data('daterangepicker')) {
    $input.data('daterangepicker').remove();
  }

  $input.daterangepicker({
    showDropdowns: true,
    autoUpdateInput: false,
    startDate: startDate,
    endDate: endDate,
    opens: window.isRtl ? 'left' : 'right',
    locale: {
      format: 'MM/DD/YYYY',
      cancelLabel: 'Clear'
    }
  });

  if (input.dataset.startDate && input.dataset.endDate) {
    input.value = `${startDate.format('MM/DD/YYYY')} - ${endDate.format('MM/DD/YYYY')}`;
  }

  $input.off('apply.daterangepicker.w14History cancel.daterangepicker.w14History');
  $input.on('apply.daterangepicker.w14History', function(_event, picker) {
    input.value = `${picker.startDate.format('MM/DD/YYYY')} - ${picker.endDate.format('MM/DD/YYYY')}`;
    component && component.call(
      'applyHistoryDateRange',
      picker.startDate.format('YYYY-MM-DD'),
      picker.endDate.format('YYYY-MM-DD')
    );
  });

  $input.on('cancel.daterangepicker.w14History', function() {
    input.value = '';
    component && component.call('clearHistoryDateRange');
  });
};

document.addEventListener('livewire:initialized', () => {
  if (window.w14RewardDisciplinePointsInitialized) return;
  window.w14RewardDisciplinePointsInitialized = true;

  function cleanupBackdrops() {
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
  }

  // ✅ لأنك بتعملي $this->dispatch(...) => لازم window.addEventListener
  window.addEventListener('open-desc-modal', () => {
    const el = document.getElementById('behaviorDescModal');
    if (!el) return;

    const modal = bootstrap.Modal.getOrCreateInstance(el, { backdrop: 'static' });
    modal.show();
  });

  window.addEventListener('close-desc-modal', () => {
    const el = document.getElementById('behaviorDescModal');
    if (!el) return;

    const modal = bootstrap.Modal.getInstance(el);
    if (modal) modal.hide();

    setTimeout(cleanupBackdrops, 150);
  });

  window.addEventListener('open-behavior-history-modal', () => {
    const el = document.getElementById('behaviorHistoryModal');
    if (!el) return;

    const modal = bootstrap.Modal.getOrCreateInstance(el);
    modal.show();
  });

  window.addEventListener('close-behavior-history-modal', () => {
    const el = document.getElementById('behaviorHistoryModal');
    if (!el) return;

    const modal = bootstrap.Modal.getInstance(el);
    if (modal) modal.hide();

    setTimeout(cleanupBackdrops, 150);
  });

  // ✅ مهم جدًا: لو المستخدم قفل من X أو ضغط ESC أو أي سبب
  
  
  window.addEventListener('open-punishment-history-modal', () => {
    const el = document.getElementById('punishmentHistoryModal');
    if (!el) return;
    bootstrap.Modal.getOrCreateInstance(el).show();
  });

  window.addEventListener('close-punishment-history-modal', () => {
    const el = document.getElementById('punishmentHistoryModal');
    if (!el) return;
    bootstrap.Modal.getInstance(el)?.hide();
    setTimeout(cleanupBackdrops, 150);
  });

  Livewire.on('scroll-to-history', () => {
    const el = document.getElementById('student-behavior-history');
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  // تنظيف عام لو اتقفل من X أو ESC
  document.addEventListener('hidden.bs.modal', () => {
    setTimeout(cleanupBackdrops, 50);
  });

  
  
  

});





</script>

  




{{--
<script>
document.addEventListener('livewire:initialized', () => {

  function cleanupBackdrops() {
    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
  }

  Livewire.on('open-desc-modal', () => {
    const el = document.getElementById('behaviorDescModal');
    if (!el) return;

    const modal = bootstrap.Modal.getOrCreateInstance(el, { backdrop: 'static' });
    modal.show();
  });

  Livewire.on('close-desc-modal', () => {
    const el = document.getElementById('behaviorDescModal');
    if (!el) return;

    const modal = bootstrap.Modal.getInstance(el);
    if (modal) modal.hide();

    // ضمان تنظيف أي backdrop متروك
    setTimeout(cleanupBackdrops, 150);
  });

  Livewire.on('open-behavior-history-modal', () => {
    const el = document.getElementById('behaviorHistoryModal');
    if (!el) return;

    const modal = bootstrap.Modal.getOrCreateInstance(el);
    modal.show();
  });

  Livewire.on('close-behavior-history-modal', () => {
    const el = document.getElementById('behaviorHistoryModal');
    if (!el) return;

    const modal = bootstrap.Modal.getInstance(el);
    if (modal) modal.hide();

    setTimeout(cleanupBackdrops, 150);
  });

});
--}}










</div>
</div>
