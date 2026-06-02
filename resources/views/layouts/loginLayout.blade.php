<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ config('variables.templateName', 'To Quran') }} | @yield('title', 'Login')</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
    content="Sign in to your To Quran account." />
  <meta name="keywords"
    content="To Quran, login" />
  <meta property="og:title" content="{{ config('variables.templateName', 'To Quran') }} | @yield('title', 'Login')" />
  <meta property="og:url" content="{{ url()->current() }}" />
  <meta property="og:image" content="{{ asset('assets/img/logo/logo.png') }}" />
  <meta property="og:description"
    content="Sign in to your To Quran account." />
  <meta property="og:site_name"
    content="{{ config('variables.templateName', 'To Quran') }}" />
  <meta name="robots" content="noindex, nofollow" />
  
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
  @include('pwa.meta')
  <link rel="preload" as="image" href="{{ asset('assets/img/logo/logo.png') }}" />


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
      rel="stylesheet">
  @vite(['resources/css/responsive-form-controls.css'])
    </head>
    <body class="hold-transition sidebar-mini layout-fixed sidebar-collapse">
      <div class="wrapper">
        @yield('content')
       

<script src="https://code.jquery.com/jquery-3.6.1.min.js" ></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" ></script>
@vite(['resources/js/responsive-form-controls.js'])


<script>
  (function($){
    $(function(){
      var $toggle = $('#password-toggle');
      var $input  = $('#password');

      // Safety: لو العنصر مش موجود ما تعملش حاجة
      if (!$toggle.length || !$input.length) return;

      // وظيفة تبديل النوع والأيقونة
      function togglePassword(show) {
        if (show) {
          $input.attr('type', 'text');
          // بدّل الأيقونة — حسب مكتبتك ممكن تستبدلي الأصناف
          $toggle.find('i')
                 .removeClass('tabler-eye-off')
                 .addClass('tabler-eye');
          $toggle.attr('aria-label', 'Hide password');
          $toggle.attr('aria-pressed', 'true');
        } else {
          $input.attr('type', 'password');
          $toggle.find('i')
                 .removeClass('tabler-eye')
                 .addClass('tabler-eye-off');
          $toggle.attr('aria-label', 'Show password');
          $toggle.attr('aria-pressed', 'false');
        }
      }

      // إبدأ بالحالة الافتراضية (مخفي)
      togglePassword(false);

      // تعامل مع الكليك
      $toggle.on('click', function(e){
        e.preventDefault();
        var isShown = $input.attr('type') === 'text';
        togglePassword(!isShown);
      });

      // تعامل مع الكيبورد (Enter / Space) للـ accessibility
      $toggle.on('keydown', function(e){
        if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
          e.preventDefault();
          $(this).trigger('click');
        }
      });

      // اختياري: لو عايز تخلي الحقل يرجع ل password لما يخرج الفوكس
      // $input.on('blur', function(){ togglePassword(false); });

    });
  })(jQuery);
</script>

      </div>
    </body>
</html>
