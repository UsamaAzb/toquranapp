<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Amazing English | Log in</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{asset('assets/admin/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{asset('assets/admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('assets/admin/dist/css/adminlte.min.css')}}">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <style>
  body{

  background-image: url(/loginn/sso-work-og.jpg) !important;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
  background-position: center;
   background-repeat: no-repeat;
    }
    .error{
      color:#da1919 !important;
    }
    .login-user{
      ms-flex-align: center;
      align-items: center !important;
    display: flex;
    height: 100vh!important;
    justify-content: center;
    }

      .card:hover, .card:focus, .card:active
      {
      -webkit-box-shadow: 15px 15px 20px rgba(0,0, 0, 0.4);
      -moz-box-shadow: 15px 15px 20px rgba(0,0, 0, 0.4);
      box-shadow: 15px 15px 20px rgba(0,0, 0, 0.4);
      -webkit-transform: scale(1.025);
      -moz-transform: scale(1.025);
      transform: scale(1.025);
      }
    }
  </style>
</head>
<body class="hold-transition login-user">
<div class="login-box">
  <div class="login-logo">
    <b>User Login</b>
  </div>
  <!-- /.login-logo -->
  <div class="card"style="background-color: #f5f7f97d !important;border: 1px solid red;box-shadow: 14px 12px 3px rgb(0 0 0 / 13%), 0 1px 3px rgb(0 0 0 / 20%)!important;">
    <div class="card-body login-card-body" style="background-color: #e8bfbf38 !important">

      <form action="{{route('front.dologin')}}" method="get">
        @if(session()->has('master_errors'))
        <div class="alert  alert-dismissible error" role="alert"><span type="button" class="close error" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></span><strong>Error!</strong>
        {{ session()->get('master_errors')}}
      </div>
         @endif
        <div class="input-group mb-3">
          <input style="color:#4545ec !important;"type="string" class="form-control @error('user_name') is-invalid @enderror" placeholder="User Name" value="" name="user_name">
          <div class="input-group-append">
            <div class="input-group-text">
<i class="fas fa-user"></i>            </div>
          </div>
          @error('user_name')
           <div class="alert alert-danger">{{ $message }}</div>
           @enderror
        </div>
        <div class="input-group mb-3">
          <input  style="color:#4545ec !important;" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" value="" name="password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
          @error('password')
           <div class="alert alert-danger">{{ $message }}</div>
           @enderror
        </div>
        <div class="row">

          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-danger btn-block">Sign In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>




    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="{{asset('assets/admin/plugins/jquery/jquery.min.js')}}"></script>
<!-- Bootstrap 4 -->
<script src="{{asset('assets/admin/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{asset('assets/admin/dist/js/adminlte.min.js')}}"></script>

</body>
</html>
