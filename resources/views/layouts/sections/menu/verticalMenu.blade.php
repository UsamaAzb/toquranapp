@php
use Illuminate\Support\Facades\Route;
$configData = Helper::appClasses();
$currentRouteName = Route::currentRouteName();
$currentPath = request()->path();
$user = auth()->user();
$canManageAdminBookings = auth()->check() && $user?->hasAnyRole(['admin', 'super_admin']);
$canAccessTransferredFamilies = auth()->check() && $user?->hasAnyRole(['admin', 'super_admin', 'customer_support']);
@endphp
<style>
.menu-item.active > .menu-link {
    background: var(--bs-primary) !important;
    color: #fff!important;   /* أي لون أنتِ عايزاه */
    font-weight: 600!important;
}

.tq-brand-logo {
    display: block;
    height: auto;
    max-height: 3.5rem;
    max-width: 11rem;
    object-fit: contain;
    width: 100%;
}
</style>
<aside id="layout-menu" class="layout-menu menu-vertical menu"
  @foreach ($configData['menuAttributes'] as $attribute => $value)
    {{ $attribute }}="{{ $value }}"
  @endforeach
>
  {{-- ! Hide app brand if navbar-full --}}
  @if (!isset($navbarFull))
    <div class="app-brand demo">
        <span class="app-brand-logo demo">
          <img class="tq-brand-logo" src="{{ asset('assets/img/logo/logo.png') }}" width="169" height="56" alt="To Quran">
        </span>
        <!--<span class="app-brand-text demo menu-text fw-bold ms-3">{{ config('variables.templateName') }}</span>-->
      
      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
        <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
        <i class="icon-base ti tabler-x d-block d-xl-none"></i>
      </a>
    </div>
  @endif

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1" id="vertical_menu">
       @if ($canManageAdminBookings)
       <li class="menu-item ">
            <a href="{{ rtrim(config('app.public_website_url', 'https://toquran.org'), '/') }}/book-trial"
               class="menu-link"
                target="_blank" 
            >
                <i class="menu-icon icon-base ti tabler-shield-lock"></i>
              <div>Book Consultation</div>
            </a>
          </li>
       
          
             
          
          <li class="menu-item {{ $currentRouteName === 'admin.bookings.livewire' ? 'active' : '' }}">
            <a href="{{url('admin/bookings')}}"
               class="menu-link"
               
            >
                <i class="menu-icon icon-base ti tabler-clipboard-list"></i>
              <div>Booking</div>
            </a>
          </li>
          @role('super_admin')
            <li class="menu-item {{ $currentRouteName === 'admin.staff.index' ? 'active' : '' }}">
              <a href="{{ route('admin.staff.index') }}"
                 class="menu-link"
              >
                <i class="menu-icon icon-base ti tabler-user-cog"></i>
                <div>Staff Users</div>
              </a>
            </li>
          @endrole
          <li class="menu-item {{ $currentRouteName === 'admin.teacher-class-assignments.index' ? 'active' : '' }}">
            <a href="{{ route('admin.teacher-class-assignments.index') }}"
               class="menu-link"
            >
              <i class="menu-icon icon-base ti tabler-school"></i>
              <div>Teacher Assignments</div>
            </a>
          </li>
          <li class="menu-item {{ $currentRouteName === 'admin.starter-catalog-installer.index' ? 'active' : '' }}">
            <a href="{{ route('admin.starter-catalog-installer.index') }}"
               class="menu-link"
            >
              <i class="menu-icon icon-base ti tabler-stack-push"></i>
              <div>Starter Catalog</div>
            </a>
          </li>
          <li class="menu-item {{ $currentRouteName === 'admin.library.index' || str_starts_with($currentPath, 'admin/library') ? 'active' : '' }}">
            <a href="{{ route('admin.library.index') }}"
               class="menu-link"
            >
              <i class="menu-icon icon-base ti tabler-library"></i>
              <div>Library</div>
            </a>
          </li>
           <li class="menu-item {{ str_starts_with($currentPath, 'admin/calendar') ? 'active' : '' }}">
            <a href="{{url('admin/calendar')}}"
               class="menu-link"
               
            >
                <i class="menu-icon icon-base ti tabler-calendar"></i>
              <div>Calendar</div>
            </a>
          </li>
          
        
    
          
          
      @endif    
      @if ($canAccessTransferredFamilies)
        <li class="menu-item {{ $currentRouteName === 'admin.bookings.transferred' ? 'active' : '' }}">
          <a href="{{ route('admin.bookings.transferred') }}"
             class="menu-link"
          >
            <i class="menu-icon icon-base ti tabler-users-group"></i>
            <div>Transferred Families</div>
          </a>
        </li>
      @endif
      @role('parent')
        <li class="menu-item {{ $currentRouteName === 'parent.students' ? 'active' : '' }}">
          <a href="{{ route('parent.students') }}" class="menu-link">
            <i class="menu-icon icon-base ti tabler-users"></i>
            <div>My Children</div>
          </a>
        </li>
        @foreach($parentMenuStudents as $menuStudent)
          @php
            $menuStudentId = (int) $menuStudent->id;
            $isActiveChild = $parentActiveStudentId === $menuStudentId;
            $menuStudentName = trim($menuStudent->first_name.' '.$menuStudent->last_name);
          @endphp
          <li class="menu-item has-sub {{ $isActiveChild ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
              <i class="menu-icon icon-base ti tabler-user"></i>
              <div>{{ $menuStudentName }}</div>
            </a>
            <ul class="menu-sub">
              <li class="menu-item {{ $isActiveChild && str_starts_with($currentPath, 'student/workplace') ? 'active' : '' }}">
                <a href="{{ route('student.workplace', $menuStudentId) }}" class="menu-link">
                  <div>Workspace</div>
                </a>
              </li>
              <li class="menu-item {{ $isActiveChild && str_starts_with($currentPath, 'parent/reward-discpline') ? 'active' : '' }}">
                <a href="{{ route('parent.reward-discpline', $menuStudentId) }}" class="menu-link">
                  <div>Points Lab</div>
                </a>
              </li>
              <li class="menu-item {{ $isActiveChild && str_starts_with($currentPath, 'student/journey/board') ? 'active' : '' }}">
                <a href="{{ route('student.journey.board', $menuStudentId) }}" class="menu-link">
                  <div>Rewards</div>
                </a>
              </li>
              <li class="menu-item {{ $isActiveChild && $currentRouteName === 'parent.task-approvals' ? 'active' : '' }}">
                <a href="{{ route('parent.task-approvals', $menuStudentId) }}" class="menu-link">
                  <div>Reviews</div>
                </a>
              </li>
            </ul>
          </li>
        @endforeach
      @endrole
     @role('teacher')
       <li class="menu-item {{ str_starts_with($currentPath, 'teacher/classes') ? 'active' : '' }}">
            <a href="{{url('teacher/classes')}}"
               class="menu-link"
               
            >
                <i class="menu-icon icon-base ti tabler-books"></i>
              <div>My Classes</div>
            </a>
          </li>
       
             <li class="menu-item {{ str_starts_with($currentPath, 'teacher/library') ? 'active' : '' }}">
            <a href="{{url('teacher/library')}}"
               class="menu-link"

            >
                <i class="menu-icon icon-base ti tabler-files"></i>
              <div>Library</div>
            </a>
          </li>

          <li class="menu-item has-sub {{ str_starts_with($currentPath, 'teacher/daily-sessions') || str_starts_with($currentPath, 'teacher/differentiated-tasks') || str_starts_with($currentPath, 'teacher/series-tasks') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
              <i class="menu-icon icon-base ti tabler-bolt"></i>
              <div>Automation</div>
            </a>
            <ul class="menu-sub">
              <li class="menu-item {{ str_starts_with($currentPath, 'teacher/daily-sessions') ? 'active' : '' }}">
                <a href="{{ route('daily-sessions.get_subjects', ['auth_role' => 'teacher']) }}" class="menu-link">
                  <div>Versioned Routines</div>
                </a>
              </li>
              <li class="menu-item {{ str_starts_with($currentPath, 'teacher/differentiated-tasks') ? 'active' : '' }}">
                <a href="{{ route('differentiated-tasks.get_subjects', ['auth_role' => 'teacher']) }}" class="menu-link">
                  <div>Differentiated Tasks</div>
                </a>
              </li>
              <li class="menu-item {{ str_starts_with($currentPath, 'teacher/series-tasks') ? 'active' : '' }}">
                <a href="{{ route('series-tasks.subjects', ['auth_role' => 'teacher']) }}" class="menu-link">
                  <div>Series Tasks</div>
                </a>
              </li>
            </ul>
          </li>

              <li class="menu-item">
            <a href="javascript:void(0);"
               class="menu-link disabled"
               aria-disabled="true"
               tabindex="-1"
               style="pointer-events: none; opacity: .7;"
            >
                <i class="menu-icon icon-base ti tabler-pacman"></i>
              <div>Quranic Arabic Games</div>
              <span class="badge bg-label-warning ms-auto">Soon</span>
            </a>
          </li>
      @endrole 
      
      
      @role('student')
      @php
      $studentId=auth()->user()->student->id 
      @endphp
      
      <li class="menu-item {{ str_starts_with($currentPath, 'student/workplace') ? 'active' : '' }}">
            <a href="{{url('student/workplace')}}"
               class="menu-link"
               
            >
              
                <i class="menu-icon icon-base ti tabler-briefcase"></i>
              <div>My Workplace</div>
            </a>
          </li>
       <li class="menu-item {{ str_starts_with($currentPath, 'student/classes') ? 'active' : '' }}">
            <a href="{{url('student/classes')}}"
               class="menu-link"
               
            >
                <i class="menu-icon icon-base ti tabler-books"></i>
              <div>My Subjects</div>
            </a>
          </li>
        <li class="menu-item {{ str_starts_with($currentPath, 'student/journey/board/'.$studentId) ? 'active' : '' }}">
            <a href="{{url('student/journey/board/'.$studentId)}}"
               class="menu-link"
                
            >
                <i class="menu-icon icon-base ti tabler-bell"></i>
              <div>Rewards</div>
            </a>
          </li>
          {{--<li class="menu-item ">
            <a href="{{url('student/consequence-agreement/'.$studentId)}}"
               class="menu-link"
                 
            >
                <i class="menu-icon icon-base ti tabler-heart-handshake"></i>
              <div>Consequence Agreement</div>
            </a>
          </li>--}}
          <li class="menu-item {{ str_starts_with($currentPath, 'student/reward-discpline/'.$studentId) ? 'active' : '' }}">
            <a href="{{url('student/reward-discpline/'.$studentId)}}"
               class="menu-link"
                
            >
                <i class="menu-icon icon-base ti tabler-brand-google-fit"></i>
              <div>Points Lab</div>
            </a>
          </li>
             
          
              <li class="menu-item">
            <a href="javascript:void(0);"
               class="menu-link disabled"
               aria-disabled="true"
               tabindex="-1"
               style="pointer-events: none; opacity: .7;"
            >
                <i class="menu-icon icon-base ti tabler-pacman"></i>
              <div>Quranic Arabic Games</div>
              <span class="badge bg-label-warning ms-auto">Soon</span>
            </a>
          </li>
      @endrole
      
      

  </ul>
</aside>
