@extends('layouts/layoutMaster')

@section('title', 'User View - Pages')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('page-style')
@vite('resources/assets/vendor/scss/pages/page-user-view.scss')
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/cleave-zen/cleave-zen.js', 'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/modal-edit-user.js', 'resources/assets/js/modal-enable-otp.js', 'resources/assets/js/app-user-view.js', 'resources/assets/js/app-user-view-security.js'])
@endsection

@section('content')
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
              <div>{{$student->gradeLevel->title}}</div>
            </li>
              <hr>
            <li class="mb-2">
              <p class="h6">academic Information:</p>
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
          </div>
        </div>
      </div>
        </div>
    <!-- /User Card -->
    <!-- Plan Card -->
    <div class="card mb-6 border border-2 border-primary rounded primary-shadow">
      <div class="card-body">
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
      <ul class="nav nav-pills flex-column flex-md-row mb-6 flex-wrap row-gap-2">
        <li class="nav-item">
          <a class="nav-link" href="{{ route('admin.students.account', $student->id) }}"><i class="icon-base ti tabler-user-check me-1_5 icon-sm"></i>Account</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="javascript:void(0);"><i class="icon-base ti tabler-lock me-1_5 icon-sm"></i>Security</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('app/user/view/billing') }}"><i class="icon-base ti tabler-bookmark me-1_5 icon-sm"></i>Billing & Plans</a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="{{ route('admin.students.show_reward', $student->id) }}"><i class="icon-base ti tabler-bell icon-sm me-1_5"></i>Rewards</a>
        </li>

      </ul>
    </div>
    <!--/ User Pills -->

    <!-- Change Password -->
    <div class="card mb-6">
      <h5 class="card-header">Change Password</h5>
      <div class="card-body">
        <form id="formChangePassword" method="POST" onsubmit="return false">
          <div class="alert alert-warning alert-dismissible" role="alert">
            <h5 class="alert-heading mb-1">Ensure that these requirements are met</h5>
            <span>Minimum 8 characters long, uppercase & symbol</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <div class="row gx-6">
            <div class="mb-4 col-12 col-sm-6 form-password-toggle form-control-validation">
              <label class="form-label" for="newPassword">New Password</label>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" id="newPassword" name="newPassword" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
              </div>
            </div>

            <div class="mb-4 col-12 col-sm-6 form-password-toggle form-control-validation">
              <label class="form-label" for="confirmPassword">Confirm New Password</label>
              <div class="input-group input-group-merge">
                <input class="form-control" type="password" name="confirmPassword" id="confirmPassword" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                <span class="input-group-text cursor-pointer"><i class="icon-base ti tabler-eye-off"></i></span>
              </div>
            </div>
            <div>
              <button type="submit" class="btn btn-primary me-2">Change Password</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <!--/ Change Password -->


    <livewire:admin.reward-pin :student-id="$student->id" />
    <livewire:admin.complete-task-pin :parent-id="$student->parent->id" />
  
<!-- reward system pin -->
  <!-- <div class="card mb-6">
    <div class="card-header">
      <h5 class="mb-0">Task Completion PIN</h5>
      <span class="card-subtitle mt-0">Keep your account secure with authentication step.</span>
    </div>
    <div class="card-body pt-0">
      <h6 class="mb-1">PIN Code</h6>
      <div class="mb-4">
        <div class="d-flex w-100 action-icons">
          <input id="defaultInput" class="form-control me-4" type="text" placeholder="Minimum 4 characters long, uppercase & symbol" />
          <a href="javascript:;" class="btn btn-icon btn-text-secondary save_pin" ><i class="icon-base ti tabler-save icon-22px"></i></a>

          <a href="javascript:;" class="btn btn-icon btn-text-secondary" data-bs-target="#enableOTP" data-bs-toggle="modal"><i class="icon-base ti tabler-edit icon-22px"></i></a>
          <a href="javascript:;" class="btn btn-icon btn-text-secondary"><i class="icon-base ti tabler-trash icon-22px"></i></a>
        </div>
      </div>

    </div>
  </div> -->




    <!-- Two-steps verification -->
    <!-- <div class="card mb-6">
      <div class="card-header">
        <h5 class="mb-0">Two-steps verification</h5>
        <span class="card-subtitle mt-0">Keep your account secure with authentication step.</span>
      </div>
      <div class="card-body pt-0">
        <h6 class="mb-1">SMS</h6>
        <div class="mb-4">
          <div class="d-flex w-100 action-icons">
            <input id="defaultInput" class="form-control me-4" type="text" placeholder="+1(968) 945-8832" />
            <a href="javascript:;" class="btn btn-icon btn-text-secondary" data-bs-target="#enableOTP" data-bs-toggle="modal"><i class="icon-base ti tabler-edit icon-22px"></i></a>
            <a href="javascript:;" class="btn btn-icon btn-text-secondary"><i class="icon-base ti tabler-trash icon-22px"></i></a>
          </div>
        </div>
        <p class="mb-0">
          Two-factor authentication adds an additional layer of security to your account by requiring more than just a password to log in.
          <a href="javascript:void(0);" class="text-primary">Learn more.</a>
        </p>
      </div>
    </div> -->
    <!--/ Two-steps verification -->

    <!-- Recent Devices -->
    <div class="card mb-6">
      <h5 class="card-header">Recent Devices</h5>
      <div class="table-responsive table-border-bottom-0">
        <table class="table">
          <thead>
            <tr>
              <th class="text-truncate">Browser</th>
              <th class="text-truncate">Device</th>
              <th class="text-truncate">Location</th>
              <th class="text-truncate">Recent Activities</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="text-truncate"><i class="icon-base ti tabler-brand-windows icon-md text-info me-4"></i> <span class="text-heading">Chrome on Windows</span></td>
              <td class="text-truncate">HP Spectre 360</td>
              <td class="text-truncate">Switzerland</td>
              <td class="text-truncate">10, July 2021 20:07</td>
            </tr>
            <tr>
              <td class="text-truncate"><i class="icon-base ti tabler-device-mobile icon-md text-danger me-4"></i> <span class="text-heading">Chrome on iPhone</span></td>
              <td class="text-truncate">iPhone 12x</td>
              <td class="text-truncate">Australia</td>
              <td class="text-truncate">13, July 2021 10:10</td>
            </tr>
            <tr>
              <td class="text-truncate"><i class="icon-base ti tabler-brand-android icon-md text-success me-4"></i> <span class="text-heading">Chrome on Android</span></td>
              <td class="text-truncate">Oneplus 9 Pro</td>
              <td class="text-truncate">Dubai</td>
              <td class="text-truncate">14, July 2021 15:15</td>
            </tr>
            <tr>
              <td class="text-truncate"><i class="icon-base ti tabler-brand-apple icon-md me-4"></i> <span class="text-heading">Chrome on MacOS</span></td>
              <td class="text-truncate">Apple iMac</td>
              <td class="text-truncate">India</td>
              <td class="text-truncate">16, July 2021 16:17</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!--/ Recent Devices -->
  </div>
  <!--/ User Content -->
</div>
@include('_partials/_modals/modal-edit-user')
<!-- Modals -->
@include('_partials/_modals/modal-enable-otp')
<!-- /Modals -->

@endsection
