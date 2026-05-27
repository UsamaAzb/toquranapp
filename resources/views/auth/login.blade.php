@php
  $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/loginLayout')

@section('title', 'Login')

{{-- ممكن نستخدم شوية CSS بسيط جوه الصفحة عشان نعمل شكل زي Vuexy --}}
@section('content')
<style>
  body {
    background: linear-gradient(135deg, #f3f0ff 0%, #fdfbff 60%, #f5f5ff 100%);
  }

  .auth-wrapper {
    min-height: 100vh;
  }

  .auth-card {
    border-radius: 18px;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
    border: none;
  }

  .auth-logo img {
max-width: 125px;
  width: 100%;
  height: auto;  }

  .auth-title {
    font-weight: 600;
    font-size: 1.35rem;
  }

  .auth-subtitle {
    color: #6b7280;
    font-size: 0.95rem;
  }

  .form-label {
    font-weight: 500;
    font-size: 0.9rem;
  }

  .form-control {
    border-radius: 10px;
    padding-block: 0.6rem;
  }

  .form-control:focus {
    border-color: #1B365D;
    box-shadow: 0 0 3px 1px rgb(27 54 93 / 34%);
  }

  .btn-primary {
    background: #1B365D;
    border-color: #1B365D;
    font-weight: 500;
    padding-block: 0.7rem;
    border-radius: 6px;
    color: white;
  }

  .btn-primary:hover {
    background: #1B365D;
    border-color: #1B365D;
  }

  .link-soft {
    color: #1B365D;
    font-weight: 500;
    text-decoration: none;
  }

  .link-soft:hover {
    text-decoration: underline;
    color: #5b21b6;
  }

  .auth-footer-text {
    font-size: 0.9rem;
    color: #6b7280;
  }

  .auth-footer-text a {
    font-weight: 500;
  }

  .input-group-text {
    background-color: #f9fafb;
    border-radius: 0 10px 10px 0;
  }

  .invalid-feedback {
    display: block;
    font-size: 0.8rem;
  }
</style>

<div class="container-xxl auth-wrapper d-flex align-items-center justify-content-center">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-4 py-md-5">

      <!-- Login -->
      <div class="card auth-card">
        <div class="card-body px-4 px-md-5 py-4 py-md-5">

          <!-- Logo + Title -->
          <div class="text-center mb-4">
            <a href="{{ url('/login') }}" class="d-inline-flex align-items-center justify-content-center gap-2 text-decoration-none">
              <span class="auth-logo d-inline-flex align-items-center justify-content-center">
                <img src="{{ asset('assets/img/logo/logo.webp') }}" alt="To Quran Logo" width="169" height="32" fetchpriority="high">
              </span>
              {{-- لو حابة تكتبي اسم البراند جنب اللوجو --}}
              {{-- <span class="fw-bold text-dark ms-1">To Quran</span> --}}
            </a>
          </div>

          <h4 class="auth-title mb-1 text-center">
            Welcome to {{ config('variables.templateName', 'To Quran') }}!
          </h4>
          <p class="auth-subtitle mb-4 text-center">
            Please sign in to your account and start the adventure
          </p>

          @if ($errors->any())
            <div class="alert alert-danger mt-2 mb-3 py-2">
              {{ $errors->first() }}
            </div>
          @endif

          <form id="formAuthentication"
                class="mb-3"
                method="POST"
                action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
              <label for="email" class="form-label">Email or Username</label>
              <input
                type="text"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="Enter your email or username"
                autofocus
                required
              >
            {{--  @error('email')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
              @enderror--}}
            </div>

            {{-- Password --}}
            <div class="mb-3">
              <label class="form-label" for="password">Password</label>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password"
                  class="form-control @error('password') is-invalid @enderror"
                  name="password"
                  placeholder="••••••••••••"
                  required
                  autocomplete="current-password"
                  aria-describedby="password-toggle"
                >
                <span class="input-group-text" id="password-toggle" role="button" tabindex="0" aria-label="Show password">
                  <svg width="20px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 932.05 932.05" style="enable-background:new 0 0 932.05 932.05;" xml:space="preserve"><g><g><path d="M146.375,658.475l121.7-121.699c-7.9-22.101-12.2-45.9-12.2-70.7c0-116.101,94.1-210.1,210.1-210.1 c24.801,0,48.601,4.3,70.7,12.2l83.101-83.1c-48.5-15.3-100.2-23.5-153.7-23.5c-205.6,0-382.8,121.2-464.2,296.1 c-2.5,5.3-2.5,11.5,0,16.9C35.375,546.475,85.075,609.375,146.375,658.475z"/><path d="M785.675,273.675l-121.7,121.7c7.9,22.1,12.2,45.9,12.2,70.7c0,116.1-94.1,210.1-210.1,210.1 c-24.8,0-48.601-4.3-70.7-12.2l-83.1,83.101c48.5,15.3,100.199,23.5,153.699,23.5c205.601,0,382.801-121.2,464.2-296.101 c2.5-5.3,2.5-11.5,0-16.899C896.675,385.675,846.975,322.775,785.675,273.675z"/><path d="M466.075,600.575c74.3,0,134.5-60.2,134.5-134.5c0-2.3-0.101-4.7-0.2-7l-141.3,141.3 C461.375,600.475,463.675,600.575,466.075,600.575z"/><path d="M466.075,331.675c-74.3,0-134.5,60.2-134.5,134.5c0,2.3,0.1,4.7,0.2,7l141.3-141.3 C470.675,331.675,468.375,331.675,466.075,331.675z"/><path d="M178.875,682.475l-63.2,63.2c-19.5,19.5-19.5,51.2,0,70.7c9.8,9.8,22.6,14.6,35.4,14.6c12.8,0,25.6-4.899,35.4-14.6 l84.4-84.4l86.2-86.199l56.1-56.101l176.601-176.6l56.1-56.101l107.4-107.399l63.199-63.2c19.5-19.5,19.5-51.2,0-70.7 c-9.8-9.8-22.6-14.6-35.399-14.6s-25.601,4.9-35.4,14.6l-84.399,84.4l-86.2,86.2l-56.101,56.1l-176.6,176.6l-56.1,56.101 L178.875,682.475z"/></g></g></svg>
                </span>
              </div>
            {{--    @error('password')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
              @enderror  --}}
            </div>

            {{-- Remember + Forgot --}}
            {{--  <div class="d-flex justify-content-between align-items-center mb-4">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="remember"
                  name="remember"
                  {{ old('remember') ? 'checked' : '' }}
                >
                <label class="form-check-label" for="remember">
                  Remember Me
                </label>
              </div>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="link-soft">
                  Forgot Password?
                </a>
              @endif 
            </div>--}}

            {{-- Button --}}
            <div class="mb-2">
              <button class="btn btn-primary d-grid w-100" type="submit">
                Login
              </button>
            </div>
          </form>

          {{-- Footer text --}}
          {{-- لو لسه مش عايز تفتح التسجيل، سيبها متعلقة أو امسح البلوك ده --}}
          {{-- 
          <p class="text-center auth-footer-text mb-0">
            <span>New on our platform?</span>
            <a href="{{ url('auth/register-basic') }}" class="link-soft ms-1">
              Create an account
            </a>
          </p>
          --}}

        </div>
      </div>
      <!-- /Login -->
    </div>
  </div>
</div>



 
    
    

@endsection
