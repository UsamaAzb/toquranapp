<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        @include('front.layouts.head')
    </head>
   <body class="page-template-default page color-custom style-simple button-default layout-full-width no-content-padding no-shadows header-transparent header-fw minimalist-header-no sticky-header sticky-tb-color ab-hide subheader-both-center menu-link-color menuo-right menuo-no-borders footer-copy-center mobile-tb-center mobile-side-slide mobile-mini-mr-ll tablet-sticky mobile-header-mini mobile-sticky "style="height: 100vh !important;">
             <div class="wrapper">
        @include('front.layouts.header')

        @yield('content')
        @include('front.layouts.footer')
      </div>
    </body>
</html>
