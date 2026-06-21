@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
@endphp

@php
$totalCount = $totalCount ?? 0;
$completedCount = $completedCount ?? 0;
@endphp

<style>
    /*.progress_bar{*/
    /*    width:100%;*/
    /*}*/
    .break_text{
        white-space: normal !important;
    word-wrap: break-word !important;
    word-break: break-all !important;
        
    }
    .breadcrumb-title{
        
    }
    #navbar-collapse ol.breadcrumb {
        --bs-breadcrumb-item-padding-x: 0.2rem;
        --bs-breadcrumb-divider: "|";
    }
    .breadcrumb-item{
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.2;
        font-family: 'Public Sans', sans-serif;
        color: var(--bs-body-color);
        display: inline-flex;
        align-items: center;
    }
    .breadcrumb-item a {
        color: var(--bs-secondary-color);
        font-weight: 500;
        font-family: 'Public Sans', sans-serif;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
    }
    .breadcrumb-item a:hover {
    color: var(--bs-heading-color) !important;
    text-decoration: underline;
}
    .breadcrumb-item.breadcrumb-current,
    .breadcrumb-item:last-child > a {
        font-weight: 700;
        color: var(--bs-heading-color) !important;
    }
    .breadcrumb-current {
        max-width: min(42vw, 34rem);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .breadcrumb-item + .breadcrumb-item::before {
        color: var(--bs-secondary-color);
        font-size: 0.78em;
        padding-right: 0.55rem;
    }
    .breadcrumb-mobile-back {
        display: none !important;
    }

    .breadcrumb-mobile-back + .breadcrumb-item::before {
        display: none !important;
    }
.w14-bar-wrap small {
  white-space: nowrap !important;
  display: inline-block!important;
}

#navbar-collapse > ul.navbar-nav {
  flex-wrap: nowrap;
  gap: 0.25rem;
}

/* Keep the inline breadcrumb + points bar only on wider screens. */
@media (min-width: 901px) {

  /* nav عادي */
  #navbar-collapse {
    display: flex;
    align-items: center;
  }

  /* مجموعة breadcrumb + bar */
  #navbar-collapse .center-group {
    display: flex;
    align-items: center;
    flex: 1;              /* تاخد المساحة اللي في النص بين اليسار واليمين */
    margin-left: 0.75rem;
    gap: 1rem;
  }

  /* breadcrumb تبدأ من الشمال عادي */
  #navbar-collapse .center-group ol.breadcrumb {
    margin: 0;
    flex: 0 0 auto;       /* حجمها على قدّ الكلام بس */
  }

  /* البار ياخد باقى المساحة ويتوسّط فيها */
  #navbar-collapse .center-group .progress_bar {
    flex: 1;                              /* ياخد باقى المساحة */
    margin-left: 0 !important;
    display: flex;
    justify-content: center;              /* البار نفسه في نص المساحة */
  }

  #navbar-collapse .center-group .progress_bar > * {
    max-width: 100%;                     /* عدّلي الرقم حسب ما تحبي */
    width: 85%;
  }

  /* الجزء اليمين (الثيم + البروفايل) على أقصى اليمين */
  #navbar-collapse > ul.navbar-nav {
    margin-left: auto;
  }
}




@media (max-width: 900px) {

  /* خليه يسمح باللفّ */
  #navbar-collapse {
    flex-wrap: wrap;
    align-items: center;
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
  }

  /* نكوّن عمود لل breadcrumb + progress = مجموعة واحدة */
  #navbar-collapse .center-group {
    order: 1;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    align-self: center;
    width: calc(100% - 6.5rem);
    max-width: calc(100% - 6.5rem);
    min-width: 0;
    margin-right: auto;
    margin-left: 0.25rem;
    margin-top: 0;
  }

  /* breadcrumb أصغر ومظبوط */
  #navbar-collapse .center-group ol.breadcrumb {
    margin: 0;
    padding: 0;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    width: 100%;
    min-width: 0;
    text-align: left;
    flex-wrap: nowrap;
    overflow: hidden;
    min-height: 2rem;
  }

  #navbar-collapse .center-group ol.breadcrumb .breadcrumb-item {
    min-width: 0;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  #navbar-collapse .center-group ol.breadcrumb .breadcrumb-item a {
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: bottom;
  }

  /* progress تحت breadcrumb */
  #navbar-collapse .center-group .progress_bar {
    margin-top: 0.25rem !important;
    margin-left: 0 !important;
    display: none !important;
  }

  /* نخلي البار عرضه ثابت وجميل */
  #navbar-collapse .center-group .progress_bar > * {
    /*max-width: 360px;*/
    width: 300px;
  }

  /* الجزء اليمين (profile + theme) يفضل يمين ومتماسك */
  #navbar-collapse > ul.navbar-nav {
    order: 2;
    margin-left: auto;
    align-self: center;   /* ده اللي يخليه في النص رأسيًا */
    flex-shrink: 0;
  }
  .breadcrumb-item {
    font-size: 0.85rem;
}

  .breadcrumb-current {
    max-width: 100%;
  }
}
@media (max-width: 900px) {
    .progress_bar_mobile{
        display:block !important;
    }
}

@media (max-width: 600px) {
   #navbar-collapse .center-group {
       width: calc(100% - 6.1rem);
       max-width: calc(100% - 6.1rem);
   }

   .breadcrumb-item{
       font-size: 0.78rem !important;
   }

   .breadcrumb-item + .breadcrumb-item::before {
       padding-right: 0.45rem;
   }

   #navbar-collapse .center-group ol.breadcrumb.breadcrumb-has-mobile-back {
       position: relative;
       padding-left: 1.85rem;
   }

   .breadcrumb-mobile-back {
       display: inline-flex !important;
       align-items: center;
       justify-content: center;
       position: absolute;
       left: 0.15rem;
       top: 50%;
       transform: translateY(-50%);
       font-size: 0.82rem !important;
       margin-right: 0;
       line-height: 1;
   }

   .breadcrumb-mobile-back::before {
       display: none !important;
   }

   .breadcrumb-mobile-back a {
       display: inline-flex;
       align-items: center;
       justify-content: center;
       inline-size: 1.45rem;
       block-size: 1.45rem;
       border-radius: 999px;
       color: var(--bs-primary);
       background: rgba(var(--bs-primary-rgb), 0.08);
   }

   .breadcrumb-mobile-back a::before {
       content: "←";
       display: block;
       font-size: 0.85rem;
       font-weight: 700;
       line-height: 1;
       transform: translateY(-0.5px);
   }

   .breadcrumb-mobile-back a:hover {
       color: var(--bs-primary) !important;
       background: rgba(var(--bs-primary-rgb), 0.14);
   }

   /* At small sizes show only the current page (last breadcrumb item). */
   .breadcrumb-compact-hide {
       display: none !important;
   }
   .breadcrumb-current::before,
   .breadcrumb-compact-hide + .breadcrumb-item::before {
       display: none !important;
   }
   .breadcrumb-has-mobile-back .breadcrumb-compact-hide + .breadcrumb-item:last-child > a::before {
       content: none;
   }
   .breadcrumb-compact-hide + .breadcrumb-item:last-child > a::before {
       content: "←";
       margin-right: 0.35rem;
       font-weight: 700;
       color: currentColor;
   }
}

@media (max-width: 500px) {
    
    .layout-navbar {
        /*block-size: unset !important;*/
        /*justify-content: start!important;*/
        /*align-items: start !important;*/
    }
    .progress_bar{
        display: none !important;
    }
    .content-bar-mobile{
        display:block !important;
                margin-top: 23px;

    }
    
    /* #navbar-collapse {*/
    /*    display: flex;*/
    /*    flex-direction: column;*/
    /*    align-items: stretch;*/
    /*}*/

    /*#navbar-collapse > ul.navbar-nav {*/
    /*    order: 1;*/
    /*    align-self: flex-end;*/
    /*    margin-left: 0;*/
    /*    margin-top: 5px; */
    /*}*/
    /*.layout-menu-toggle {*/
    /*    margin-top: 8px !important;  */
    /*}*/

    /*#navbar-collapse .center-group {*/
    /*    order: 2;*/
    /*    width: 100%;*/
    /*    margin-bottom: 6px;*/
    /*    display: flex;*/
    /*    justify-content: flex-start;*/
    /*}*/

    /*#navbar-collapse .center-group ol.breadcrumb {*/
    /*    font-size: 0.9rem;*/
    /*}*/
}
@media (max-width: 550px) {
/*.layout-menu-toggle{*/
/*    margin: 0 !important;*/
/*}*/
   #navbar-collapse .center-group {
       width: calc(100% - 6.2rem);
       max-width: calc(100% - 6.2rem);
   }
   .breadcrumb-item{
       font-size: 0.76rem!important;
   }
   .avatar-online{
       --bs-avatar-size: 2rem !important;
   }
    
}
@media (max-width: 420px) {
    #navbar-collapse .center-group {
        width: calc(100% - 5.35rem);
        max-width: calc(100% - 5.35rem);
        margin-left: 0.1rem;
    }
    #navbar-collapse .center-group ol.breadcrumb {
        justify-content: flex-start;
        text-align: left;
    }
}

@media (max-width: 380px) {
    #navbar-collapse .center-group {
        width: calc(100% - 5.5rem);
        max-width: calc(100% - 5.5rem);
        margin-left: 0.1rem;
    }

    .breadcrumb-item {
        font-size: 0.76rem !important;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        padding-right: 0.4rem;
    }
}

</style>
<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
<div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4 ms-0">
  <a href="{{ url('/') }}" class="app-brand-link">
    <span class="app-brand-logo demo">@include('_partials.macros')</span>
    <span class="app-brand-text demo menu-text fw-bold">{{ config('variables.templateName') }}</span>
  </a>

  <!-- Display menu close icon only for horizontal-menu with navbar-full -->
  @if (isset($menuHorizontal))
  <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
    <i class="icon-base ti tabler-x icon-sm d-flex align-items-center justify-content-center"></i>
  </a>
  @endif
</div>
@endif

<!-- ! Not required for layout-without-menu -->
@if (!isset($navbarHideToggle))
<div
  class="layout-menu-toggle navbar-nav align-items-xl-center me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
  <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
    <i class="icon-base ti tabler-menu-2 icon-md"></i>
  </a>
</div>
@endif





<div class="navbar-nav-right d-flex align-items-xl-center align-items-lg-center align-items-md-center align-items-sm-center align-items-start " id="navbar-collapse">

<div class="center-group">
@if(isset($breadcrumb_links))
  @php
    $breadcrumbCount = count($breadcrumb_links);
    $mobileBackUrl = null;
    foreach ($breadcrumb_links as $breadcrumbUrl) {
        if ($breadcrumbUrl) {
            $mobileBackUrl = $breadcrumbUrl;
        }
    }
  @endphp
  <ol class="breadcrumb mb-0{{ $mobileBackUrl && $breadcrumbCount > 1 ? ' breadcrumb-has-mobile-back' : '' }}">
    @if($mobileBackUrl && $breadcrumbCount > 1)
      <li class="breadcrumb-item breadcrumb-mobile-back">
        <a href="{{ $mobileBackUrl }}" aria-label="Go back">
        </a>
      </li>
    @endif
    @foreach($breadcrumb_links as $label => $url)
      @php
        $compactClass = $breadcrumbCount > 1 && ! $loop->last ? ' breadcrumb-compact-hide' : '';
      @endphp
      @if($url)
        <li class="breadcrumb-item breadcrumb-title active{{ $compactClass }}">
          <a href="{{ $url }}">{{ $label }}</a>
        </li>
      @else
        <li class="breadcrumb-item breadcrumb-current active{{ $compactClass }}" aria-current="page" title="{{ $label }}">
          {{ $label }}
        </li>
      @endif
    @endforeach
  </ol>
@endif

@if(isset($show_bar) && ($show_bar=="true"))
<div class="ms-auto progress_bar">
 <livewire:ui.points-progress
    :student-id="$student->id"
    :pending-gift-id="$pendingGift->id ?? null"
    :last-reached-gift-id="$lastReached->id ?? null"
    :allow-reached-click="true"
    :circle-view="false"
    :bar-view="true"
    label="Reward Points"
    />
</div>
@endif

</div>



  <ul class="navbar-nav flex-row align-items-center ms-auto">
      
        @if ($configData['hasCustomizer'] == true)
  <!-- Style Switcher -->
  <!--<div class="navbar-nav align-items-center">-->
    <li class="nav-item dropdown me-2 me-xl-0">
      <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);"
        data-bs-toggle="dropdown">
        <i class="icon-base ti tabler-sun icon-md theme-icon-active"></i>
        <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
        <li>
          <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light"
            aria-pressed="false">
            <span><i class="icon-base ti tabler-sun icon-22px me-3" data-icon="sun"></i>Light</span>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark" aria-pressed="true">
            <span><i class="icon-base ti tabler-moon-stars icon-22px me-3" data-icon="moon-stars"></i>Dark</span>
          </button>
        </li>
       
      </ul>
    </li>
  <!--</div>-->
  <!-- / Style Switcher-->
  @endif
      
      
      
      
      
    <!-- User -->
    @php
      $navbarUser = Auth::user();
      $navbarFirstName = '';

      if ($navbarUser) {
        $navbarFirstName = trim((string) ($navbarUser->first_name ?? ''));

        if ($navbarFirstName === '') {
          $navbarFirstName = trim((string) ($navbarUser->student?->first_name ?? ''));
        }

        if ($navbarFirstName === '') {
          $navbarFirstName = trim((string) ($navbarUser->parent_user?->first_name ?? ''));
        }

        if ($navbarFirstName === '') {
          $navbarFirstName = trim((string) ($navbarUser->name ?? ''));
        }
      }
    @endphp
    <li class="nav-item navbar-dropdown dropdown-user dropdown">
      <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
        <div class="avatar avatar-online">
          <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}" alt
            class="rounded-circle" />
        </div>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <div class="dropdown-item mt-0"
          >
            <div class="d-flex align-items-center">
              <div class="flex-shrink-0 me-2">
                <div class="avatar avatar-online">
                  <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}"
                    alt class="rounded-circle" />
                </div>
              </div>
              <div class="flex-grow-1">
                <h6 class="mb-0">
                  @if (Auth::check())
                  {{ $navbarFirstName }}
                  
                  @endif
                </h6>
              </div>
            </div>
          </div>
        </li>
        
        
       
        @if (Auth::check())
        <li>
          <a class="dropdown-item" href="{{ route('logout') }}"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Logout</span>
          </a>
        </li>
        <form method="POST" id="logout-form" action="{{ route('logout') }}">
          @csrf
        </form>
        @else
        <li>
          <div class="d-grid px-2 pt-2 pb-1">
            <a class="btn btn-sm btn-danger d-flex"
              href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}" target="_blank">
              <small class="align-middle">Login</small>
              <i class="icon-base ti tabler-login ms-2 icon-14px"></i>
            </a>
          </div>
        </li>
        @endif
      </ul>
    </li>
    <!--/ User -->
  </ul>
</div>
{{-- @endif --}}
