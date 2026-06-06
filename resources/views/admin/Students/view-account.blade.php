@extends('layouts/layoutMaster')

@section('title', 'Student Account')

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/sortablejs/sortable.js'])
@endsection


@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
@php
  $isLifecycleManagedStudent = $student->isLifecycleManaged();
  $studentStatusLabel = $student->lifecycleStatusLabel();
  $studentStatusTone = $student->lifecycleStatusTone();
  $familyWorkspaceUrl = $isLifecycleManagedStudent ? route('admin.families.show', $student->parent_id) : null;
@endphp
<div class="row">
  <!-- User Sidebar -->
  <div class="col-xl-4 col-lg-5 order-1 order-md-0">
    <!-- User Card -->
    <div class="card mb-6">
      <div class="card-body pt-12">

        <h5 class="pb-4 border-bottom mb-4">Account Details</h5>
        <div class="info-container">
          <ul class="list-unstyled mb-6">
            <li class="mb-2">
              <p class="h6">Parent Information:</p>
              <div>{{$student->parent->first_name}} {{$student->parent->last_name}}</div>
              <div>{{$student->parent->email}}</div>
                <div>{{$student->parent->phone}}</div>
            </li>
            <hr>
            <li class="mb-2">
              <p class="h6">Student Information:</p>
              <div>{{$student->first_name}} {{$student->last_name}}</div>
              <div>{{$student->age}} years</div>
              <div> <label>Username: </label>{{$student->student_email}} </div>
                <div class="text-body-secondary">Password support is managed from the Family Workspace.</div>

            
            </li>
              <hr>
            <li class="mb-2">
              <p class="h6">academic Information:</p>
                <div>{{$student->gradeLevel->title}}</div>
              <div>{{ \App\Support\SchoolSystemOptions::display($student->school_system) ?? '-' }}</div>
              <div>{{$student->program->title}} ({{$student->program->code}})</div>
              <span>{{$student->services_type->title}} service</span>
            </li>
          <hr>
            <li class="mb-2">
              <span class="h6">Status:</span>
              @if ($isLifecycleManagedStudent)
                <span class="badge bg-label-{{ $studentStatusTone }}">{{ $studentStatusLabel }}</span>
              @else
                <span>{{ $studentStatusLabel }}</span>
              @endif
            </li>

          </ul>
          <div class="d-flex justify-content-center">
            <a href="javascript:;" class="btn btn-primary me-4" data-bs-target="#editUser" data-bs-toggle="modal">Edit</a>
            @if ($familyWorkspaceUrl)
              <a href="{{ $familyWorkspaceUrl }}" class="btn btn-label-secondary">Manage in Family Workspace</a>
            @else
              <livewire:admin.user-status-toggle :user="$student->user" />
            @endif

            <!--<a href="javascript:;" class="btn btn-label-danger suspend-user">Suspend</a>-->
          </div>
        </div>
      </div>
    </div>
    <!-- /User Card -->
    <!-- Plan Card -->
    <div class="card mb-6 border border-2 border-primary rounded primary-shadow">
      <div class="card-body">
          <div>for fixed and flexiable payment</div>
        <div class="d-flex justify-content-between align-items-start">

          <span class="badge bg-label-primary">Standard</span>
          <div class="d-flex justify-content-center">
            <sub class="h5 pricing-currency mb-auto mt-1 text-primary">$</sub>
            <h1 class="mb-0 text-primary">99</h1>
            <sub class="h6 pricing-duration mt-auto mb-3 fw-normal">month</sub>
          </div>
        </div>
        <ul class="list-unstyled g-2 my-6">
          <li class="mb-2 d-flex align-items-center"><i class="icon-base ti tabler-circle-filled icon-10px text-secondary me-2"></i><span>10 Users</span></li>
          <li class="mb-2 d-flex align-items-center"><i class="icon-base ti tabler-circle-filled icon-10px text-secondary me-2"></i><span>Up to 10 GB storage</span></li>
          <li class="mb-2 d-flex align-items-center"><i class="icon-base ti tabler-circle-filled icon-10px text-secondary me-2"></i><span>Basic Support</span></li>
        </ul>
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="h6 mb-0">Days</span>
          <span class="h6 mb-0">26 of 30 Days</span>
        </div>
        <div class="progress mb-1 bg-label-primary" style="height: 6px;">
          <div class="progress-bar" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <small>4 days remaining</small>
        <div class="d-grid w-100 mt-6">
          <button class="btn btn-primary" data-bs-target="#upgradePlanModal" data-bs-toggle="modal">Upgrade Plan</button>
        </div>
      </div>
    </div>
    <!-- /Plan Card -->
  </div>
  <!--/ User Sidebar -->

  <!-- User Content -->
  <div class="col-xl-8 col-lg-7 order-0 order-md-1">
    <!-- User Pills -->
    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-md-row flex-wrap mb-6 row-gap-2">
        <li class="nav-item">
          <a class="nav-link active" href="javascript:void(0);"><i class="icon-base ti tabler-user-check icon-sm me-1_5"></i>Account</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('admin.students.security', $student->id) }}"><i class="icon-base ti tabler-lock icon-sm me-1_5"></i>Security</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('app/user/view/billing') }}"><i class="icon-base ti tabler-bookmark icon-sm me-1_5"></i>Billing & Plans</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('admin.students.show_reward', $student->id) }}"><i class="icon-base ti tabler-bell icon-sm me-1_5"></i>Rewards</a>
        </li>
      
      </ul>
    </div>
    <!--/ User Pills -->


<livewire:admin.students.subject-manager :student-id="$student->id" />
<livewire:admin.students.punishment-agreements-tabs :student-id="$student->id" />
<livewire:admin.students.reward-discipline-points :student-id="$student->id" />





  <!--<div class="card">-->
  <!--      <div class="card-header d-flex justify-content-between">-->
  <!--        <div class="card-title m-0">-->
  <!--          <h5 class="mb-1">Punishment Agreements</h5>-->
            <!--<p class="card-subtitle">Yearly Earnings Overview</p>-->
  <!--        </div>-->
          
  <!--      </div>-->
  <!--      <div class="card-body">-->
  <!--        <ul class="nav nav-tabs widget-nav-tabs pb-8 gap-4 mx-1 d-flex flex-nowrap" role="tablist">-->
  <!--          <li class="nav-item">-->
  <!--            <a href="javascript:void(0);"-->
  <!--              class="nav-link btn active d-flex flex-column align-items-center justify-content-center" role="tab"-->
  <!--              data-bs-toggle="tab" data-bs-target="#navs-orders-id" aria-controls="navs-orders-id"-->
  <!--              aria-selected="true">-->
               
  <!--              <h6 class="tab-widget-title mb-0 mt-2">Orders</h6>-->
  <!--            </a>-->
  <!--          </li>-->
  <!--          <li class="nav-item">-->
  <!--            <a href="javascript:void(0);"-->
  <!--              class="nav-link btn d-flex flex-column align-items-center justify-content-center" role="tab"-->
  <!--              data-bs-toggle="tab" data-bs-target="#navs-sales-id" aria-controls="navs-sales-id"-->
  <!--              aria-selected="false">-->
              
  <!--              <h6 class="tab-widget-title mb-0 mt-2">Sales</h6>-->
  <!--            </a>-->
  <!--          </li>-->
  <!--          <li class="nav-item">-->
  <!--            <a href="javascript:void(0);"-->
  <!--              class="nav-link btn d-flex flex-column align-items-center justify-content-center" role="tab"-->
  <!--              data-bs-toggle="tab" data-bs-target="#navs-profit-id" aria-controls="navs-profit-id"-->
  <!--              aria-selected="false">-->
  <!--              <h6 class="tab-widget-title mb-0 mt-2">Profit</h6>-->
  <!--            </a>-->
  <!--          </li>-->
  <!--          <li class="nav-item">-->
  <!--            <a href="javascript:void(0);"-->
  <!--              class="nav-link btn d-flex flex-column align-items-center justify-content-center" role="tab"-->
  <!--              data-bs-toggle="tab" data-bs-target="#navs-income-id" aria-controls="navs-income-id"-->
  <!--              aria-selected="false">-->
  <!--              <h6 class="tab-widget-title mb-0 mt-2">Income</h6>-->
  <!--            </a>-->
  <!--          </li>-->
            
  <!--        </ul>-->
  <!--        <div class="tab-content p-0 ms-0 ms-sm-2">-->
  <!--          <div class="tab-pane fade show active" id="navs-orders-id" role="tabpanel">-->
  <!--            <div id="earningReportsTabsOrders"></div>-->
  <!--          </div>-->
  <!--          <div class="tab-pane fade" id="navs-sales-id" role="tabpanel">-->
  <!--            <div id="earningReportsTabsSales"></div>-->
  <!--          </div>-->
  <!--          <div class="tab-pane fade" id="navs-profit-id" role="tabpanel">-->
  <!--            <div id="earningReportsTabsProfit"></div>-->
  <!--          </div>-->
  <!--          <div class="tab-pane fade" id="navs-income-id" role="tabpanel">-->
  <!--            <div id="earningReportsTabsIncome"></div>-->
  <!--          </div>-->
  <!--        </div>-->
  <!--      </div>-->
  <!--    </div>-->


    <!-- Activity Timeline -->
    <!--<div class="card mb-6">-->
    <!--  <h5 class="card-header">User Activity Timeline</h5>-->
    <!--  <div class="card-body pt-1">-->
    <!--    <ul class="timeline mb-0">-->
    <!--      <li class="timeline-item timeline-item-transparent">-->
    <!--        <span class="timeline-point timeline-point-primary"></span>-->
    <!--        <div class="timeline-event">-->
    <!--          <div class="timeline-header mb-3">-->
    <!--            <h6 class="mb-0">12 Invoices have been paid</h6>-->
    <!--            <small class="text-body-secondary">12 min ago</small>-->
    <!--          </div>-->
    <!--          <p class="mb-2">Invoices have been paid to the company</p>-->
    <!--          <div class="d-flex align-items-center mb-2">-->
    <!--            <div class="badge bg-lighter rounded d-flex align-items-center">-->
    <!--              <img src="{{ asset('assets/img/icons/misc/pdf.png') }}" alt="img" width="15" class="me-2" />-->
    <!--              <span class="h6 mb-0 text-body">invoices.pdf</span>-->
    <!--            </div>-->
    <!--          </div>-->
    <!--        </div>-->
    <!--      </li>-->
    <!--      <li class="timeline-item timeline-item-transparent">-->
    <!--        <span class="timeline-point timeline-point-success"></span>-->
    <!--        <div class="timeline-event">-->
    <!--          <div class="timeline-header mb-3">-->
    <!--            <h6 class="mb-0">Client Meeting</h6>-->
    <!--            <small class="text-body-secondary">45 min ago</small>-->
    <!--          </div>-->
    <!--          <p class="mb-2">Project meeting with john @10:15am</p>-->
    <!--          <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">-->
    <!--            <div class="d-flex flex-wrap align-items-center mb-50">-->
    <!--              <div class="avatar avatar-sm me-2">-->
    <!--                <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />-->
    <!--              </div>-->
    <!--              <div>-->
    <!--                <p class="mb-0 small fw-medium">Lester McCarthy (Client)</p>-->
    <!--                <small>CEO of {{ config('variables.creatorName') }}</small>-->
    <!--              </div>-->
    <!--            </div>-->
    <!--          </div>-->
    <!--        </div>-->
    <!--      </li>-->
    <!--      <li class="timeline-item timeline-item-transparent">-->
    <!--        <span class="timeline-point timeline-point-info"></span>-->
    <!--        <div class="timeline-event">-->
    <!--          <div class="timeline-header mb-3">-->
    <!--            <h6 class="mb-0">Create a new project for client</h6>-->
    <!--            <small class="text-body-secondary">2 Day Ago</small>-->
    <!--          </div>-->
    <!--          <p class="mb-2">6 team members in a project</p>-->
    <!--          <ul class="list-group list-group-flush">-->
    <!--            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap border-top-0 p-0">-->
    <!--              <div class="d-flex flex-wrap align-items-center">-->
    <!--                <ul class="list-unstyled users-list d-flex align-items-center avatar-group m-0 me-2">-->
    <!--                  <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="Vinnie Mostowy" class="avatar pull-up">-->
    <!--                    <img class="rounded-circle" src="{{ asset('assets/img/avatars/5.png') }}" alt="Avatar" />-->
    <!--                  </li>-->
    <!--                  <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="Allen Rieske" class="avatar pull-up">-->
    <!--                    <img class="rounded-circle" src="{{ asset('assets/img/avatars/12.png') }}" alt="Avatar" />-->
    <!--                  </li>-->
    <!--                  <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="Julee Rossignol" class="avatar pull-up">-->
    <!--                    <img class="rounded-circle" src="{{ asset('assets/img/avatars/6.png') }}" alt="Avatar" />-->
    <!--                  </li>-->
    <!--                  <li class="avatar">-->
    <!--                    <span class="avatar-initial rounded-circle pull-up" data-bs-toggle="tooltip" data-bs-placement="bottom" title="3 more">+3</span>-->
    <!--                  </li>-->
    <!--                </ul>-->
    <!--              </div>-->
    <!--            </li>-->
    <!--          </ul>-->
    <!--        </div>-->
    <!--      </li>-->
    <!--    </ul>-->
    <!--  </div>-->
    <!--</div>-->
    <!-- /Activity Timeline -->

  
  </div>
  <!--/ User Content -->
</div>

<!-- Modal -->
@include('_partials/_modals/modal-edit-user')

<!-- for fixed and flexiable payment -->
@include('_partials/_modals/modal-upgrade-plan')
<!-- /Modal -->




@endsection
