<!-- Basic Page Needs -->

<meta charset="utf-8">
<title>English Coures</title>
<meta name="description" content="English Coures">
<meta name="author" content="">

<!-- Mobile Specific Metas -->
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

<!-- Favicons -->
<link rel="shortcut icon" href="{{ asset('images/favicon.png') }}">

<!-- FONTS -->
<link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Roboto:100,300,400,400italic,700'>
<link rel='stylesheet' href='http://fonts.googleapis.com/css?family=Red+Hat+Display:100,300,400,400italic,700'>
<link rel='stylesheet' href='http://fonts.googleapis.com/css?family=DM+Serif+Display:400,400italic,700,700italic,900'>
<!-- CSS -->
<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">-->

<link rel='stylesheet' href="{{ asset('public/plugins/bootstrap5-2/css/bootstrap.min.css') }}">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css"
  integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A=="
  crossorigin="anonymous" referrerpolicy="no-referrer" />

<link rel='stylesheet' href="{{ asset('public/css/global.css') }}">
<link rel='stylesheet' href="{{ asset('public/css/structure.css') }}">
<link rel='stylesheet' href="{{ asset('public/css/language3.css') }}">
<link rel='stylesheet' href="{{ asset('public/css/custom.css') }}">
<!-- Revolution Slider -->
<!--<link rel="stylesheet" href="{{ asset('public/plugins/rs-plugin-6.custom/css/rs6.css') }}">-->
<style>
  .box-div {
    margin-top: 86px;
  }

  .stu_list {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 1px solid rgba(0, 0, 0, .125);
    border-radius: 0.25rem;
    box-shadow: 0px 0px 7px 2px #a5969678;
  }

  .stu_table {
    padding: 1rem 1rem;

  }

  #Top_bar {
    background-color: #24252a;
    height: auto;

  }

  @media only screen and (max-width: 600px) {
    audio {
      max-width: 250px !important;
    }
  }

  audio:hover,
  audio:focus,
  audio:active {
    -webkit-box-shadow: 15px 15px 20px rgba(0, 0, 0, 0.4);
    -moz-box-shadow: 15px 15px 20px rgba(0, 0, 0, 0.4);
    box-shadow: 15px 15px 20px rgba(0, 0, 0, 0.4);
    -webkit-transform: scale(1.05);
    -moz-transform: scale(1.05);
    transform: scale(1.05);
  }


  audio {
    width: 347px;
    background-color: #f1f3f4;
    -webkit-transition: all 0.5s linear;
    -moz-transition: all 0.5s linear;
    -o-transition: all 0.5s linear;
    transition: all 0.5s linear;
    -moz-box-shadow: 2px 2px 4px 0px #006773;
    -webkit-box-shadow: 2px 2px 4px 0px #006773;
    box-shadow: 2px 2px 4px 0px #006773;
    -moz-border-radius: 7px 7px 7px 7px;
    -webkit-border-radius: 7px 7px 7px 7px;
    border-radius: 7px 7px 7px 7px;
  }



  /*body {*/
  /*background-image: url("{{ asset('public/quiz/q1.jpg') }}");*/
  /*  height: 100%;*/
  /* Center and scale the image nicely */
  /*  background-position: center;*/
  /*  background-repeat: no-repeat;*/
  /*  background-size: cover;*/
  /*    background-color: #1396de9e;*/
  /*}*/
  #Top_bar .menu .nav-link {
    padding: 6px 14px !important;
  }

  .nav-link.units {
    font-family: cursive !important;
    color: #ce2009 !important;
  }

  .card-header {
    /*height: 59px;*/
    background-color: #f2f1f0 !important;
    border-bottom: 7px solid #004c76 !important;

  }

  [class*=icheck-]>input:first-child+label::before {
    width: 15px !important;
    height: 15px !important;

  }

  [class*=icheck-]>input:first-child:checked+label::after {
    top: -3px !important;
    left: -3px !important;
  }

  hr.hr-quiz {
    width: 84%;
    border: 1px dashed #a5ccd3;
    margin-top: 20px;

  }

  .logout {
    color: black;
  }

  .logout-user {
    list-style: none;

  }

  .logout-user a {
    font-size: 18px;
    color: #004c76;
  }

  .user {
    font-size: 17px !important;
    text-align: end !important;
    color: #004c76 !important;
  }

  .drop-logout.dropdown-menu {
    right: 0 !important;
    width: 72%;
    margin: auto;
    left: 62px;
  }

  .nav-levels {
    border-radius: 6px;
    box-shadow: 4px 6px 4px #004c76;
  }

  .level-link {
    font-size: 24px;
  }

  .current_level {
    /*border-bottom: 4px solid #004c76;*/
    color: #1c82bb !important;
    width: fit-content;

  }


  @media (min-width:768px) and (max-width:992px) {
    .navbar-brand {
      margin-left: 0 !important;
    }

    .user {
      text-align: start !important;
    }

    .level-li {
      padding-left: 20px !important;
    }
  }

  @media (min-width:425px) and (max-width:768px) {
    .navbar-brand {
      margin-left: 0 !important;
    }

    .user {
      text-align: start !important;
    }

    .level-li {
      padding-left: 20px !important;
    }
  }

  @media (max-width:375px) {
    .navbar-brand {
      margin-left: 0 !important;
    }

    .user {
      text-align: start !important;
    }

    .level-li {
      padding-left: 20px !important;
    }
  }
</style>
