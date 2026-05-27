@php
use Illuminate\Support\Facades\Route;
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/blankLayout')

@section('title', 'Forgot Password')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover">
  <!-- Logo -->
  <x-authentication-card-logo />
  <!-- /Logo -->
  <div class="authentication-inner row m-0">
    <!-- /Left Text -->
    <div class="d-none d-xl-flex col-xl-8 p-0">
      <div class="auth-cover-bg d-flex justify-content-center align-items-center">
        <img
          src="{{ asset('assets/img/illustrations/auth-forgot-password-illustration-' . $configData['theme'] . '.png') }}"
          alt="auth-forgot-password-cover" class="my-5 auth-illustration d-lg-block d-none"
          data-app-light-img="illustrations/auth-forgot-password-illustration-light.png"
          data-app-dark-img="illustrations/auth-forgot-password-illustration-dark.png" />
        <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['theme'] . '.png') }}"
          alt="auth-forgot-password-cover" class="platform-bg"
          data-app-light-img="illustrations/bg-shape-image-light.png"
          data-app-dark-img="illustrations/bg-shape-image-dark.png" />
      </div>
    </div>
    <!-- /Left Text -->

    <!-- Forgot Password -->
    <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
      <div class="w-px-400 mx-auto mt-12 mt-5">
        <h4 class="mb-1">Reset Your Password</h4>
        @if (session('status'))
        <p class="mb-6">The reset request was accepted.</p>
        <div class="alert alert-success mb-6" role="status">
          <div class="fw-semibold mb-1">Reset link sent</div>
          <div>{{ session('status') }}</div>
        </div>
        <div class="text-center">
          @if (Route::has('login'))
          <a href="{{ route('login') }}" class="btn btn-label-primary d-grid w-100">
            Back to login
          </a>
          @endif
        </div>
        @else
        <p class="mb-6">Enter the account email and we will send a secure reset link.</p>
        <form id="formAuthentication" class="mb-6" action="{{ route('password.email') }}" method="POST">
          @csrf
          <div class="mb-6">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
              placeholder="john@example.com" value="{{ old('email', request('email')) }}" autocomplete="email" autofocus />
            @error('email')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <button id="password-reset-submit" type="submit" class="btn btn-primary d-grid w-100">
            <span data-reset-idle>Send Reset Link</span>
            <span data-reset-loading class="d-none">
              <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              Sending...
            </span>
          </button>
        </form>
        <div class="text-center">
          @if (Route::has('login'))
          <a href="{{ route('login') }}" class="d-flex justify-content-center">
            <i class="icon-base ti tabler-chevron-left scaleX-n1-rtl me-1_5"></i>
            Back to login
          </a>
          @endif
        </div>
        @endif
      </div>
    </div>
    <!-- /Forgot Password -->
  </div>
</div>
@endsection

@section('page-script')
<script>
  document.getElementById('formAuthentication')?.addEventListener('submit', function () {
    const button = document.getElementById('password-reset-submit');

    if (!button) {
      return;
    }

    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    button.querySelector('[data-reset-idle]')?.classList.add('d-none');
    button.querySelector('[data-reset-loading]')?.classList.remove('d-none');
  });
</script>
@endsection
