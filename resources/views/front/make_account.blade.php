<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Sign Up</title>

    <!-- Font Icon -->
    <link rel="stylesheet" href="{{asset('assets/front/fonts/material-icon/css/material-design-iconic-font.min.css')}}">
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <!-- Main css -->
    <link rel="stylesheet" href="{{asset('assets/front/css/style.css')}}">
</head>
<body>
  <div class="container-fluid ">
    <div class="sucsess_res col-md-3 col-sm-6" style="position:absolute;display: none;">
      <div class="alert alert-success alert-dismissible fade show" role="alert">
      <strong>Thank You For Subscribing.</strong>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    </div>

<!-- <img src="{{asset('assets/front/img/signup-bg.jpg')}}" class="img_body" /> -->
    <div class="main d-flex justify-content-center row">

<section class="signup   align-items-center justify-content-center justify-content-sm-center">            <!-- <img src="images/signup-bg.jpg" alt=""> -->
            <div class="container col-md-6 col-sm-11">
                <div class="signup-content">
                    <form   id="signup-form" class="signup-form">
                        <h2 class="form-title">Registration</h2>
                        <div class="form-group">
                            <input type="text" class="form-input error_name_input" name="name" id="name" placeholder="Full Name"/>
                            <span class="text-danger error" id="error_name"style="display:none">Please enter your name</span>
                        </div>
                        <div class="form-group">
                            <input type="email" class="form-input error_email_input" name="email" id="email" placeholder="Email"/>
                            <span class="text-danger error" id="error_email"style="display:none"></span>

                        </div>
                        <div class="form-group">
                            <input type="tel" class="form-input error_phone_input" name="phone" id="phone" placeholder="WhatsApp Number"/>
                            <span class="text-danger error" id="error_phone"style="display:none"></span>

                        </div>
                        <div class="form-group">
                            <input type="number" class="form-input error_age_input" name="age" id="age" placeholder="Age"/>
                            <span class="text-danger error" id="error_age"style="display:none">Please enter your age</span>

                        </div>

                          <div class="form-group ">
                            <div class=" gender-div">

                            <label class="gender-label">Gender</label>
                            <div class="form-check form-check-inline">
                              <input class="form-check-input check-radio" type="radio" name="gender" id="gender_male" value="male">
                              <label class="form-check-label gender-type" for="gender_male">Male</label>
                            </div>
                            <div class="form-check form-check-inline">
                              <input class="form-check-input check-radio" type="radio" name="gender" id="gender_female" value="female">
                              <label class="form-check-label gender-type" for="gender_female">Female</label>
                            </div>

                          </div>
                          <span class="text-danger error" id="error_gender"style="display:none">Please choose your gender</span>

                          </div>
                          <div class="form-group ">
                            <div class=" meeting-div mb-2">
                            <label class="gender-label">Meeting Venue</label>

                          <select class="form-select form-select-lg select_meeting" name="meeting" id="meeting"aria-label=".form-select-sm example">
                            <option  value="0">Select</option>

                            @foreach($meeting as $met)
                            <option  value="{{$met}}">{{$met}}</option>
                            @endforeach
                            </select>
                            </div>
                            <span class="text-danger error" id="error_meeting"style="display:none">Please Select One</span>

                            </div>
                        <div class="form-group">
                            <button type="button" id="submit_new_account" class="form-submit" >SignUp</button>
                        </div>
                    </form>

                </div>
            </div>
        </section>

    </div>
</div>
    <!-- JS -->
    <script src="{{asset('assets/admin/plugins/jquery/jquery.min.js')}}"></script>
    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    <!-- <script src="vendor/jquery/jquery.min.js"></script> -->
    <script src="{{asset('assets/front/js/main.js')}}"></script>
    <script>

    $(document).ready(function () {
    $('#submit_new_account').click(function(e){
      e.preventDefault();
      $('#error_name').css('display','none');
      $('#error_email').css('display','none');
      $('#error_phone').css('display','none');
      $('#error_gender').css('display','none');
      $('#error_age').css('display','none');
      $('.error_name_input').css('border-color','#ebebeb');
      $('.error_email_input').css('border-color','#ebebeb');
      $('.error_phone_input').css('border-color','#ebebeb');
      $('.error_age_input').css('border-color','#ebebeb');
      $('.gender-div').css('border-color','#ebebeb');
      $('#error_meeting').css('display','none');
      $('.meeting-div').css('border-color','#ebebeb');
      $('#error_email').empty();
      $('#error_phone').empty();
      $('.sucsess_res').css('display','none');

      var name=$('#name').val();
      var email=$('#email').val();
      var phone=$('#phone').val();
      var age=$('#age').val();
      var gender='';
      var token='{{csrf_token()}}';
var meeting=$('#meeting').val();

      // $(document).on("change",'input[type=radio][name=gender]',function() {
      //    gender=this.value;
      // });

      if($("input[name='gender']").is(':checked')){
        gender=$("input[name='gender']:checked").val()
      }
      if(name==''){
        $('#error_name').css('display','block');
        $('.error_name_input').css('border-color','#e35569');

      }
       if(email==""){
        $('#error_email').css('display','block');
        $('#error_email').html('Please enter your email');

        $('.error_email_input').css('border-color','#e35569');

      }
      if(email!=""){
        if (IsEmail(email) == false) {
          $('#error_email').empty();
          $('#error_email').css('display','block');
          $('#error_email').html('invalid email');

          $('.error_email_input').css('border-color','#e35569');
         }
      }
       if(phone==""){
        $('#error_phone').css('display','block');
        $('#error_phone').html('Please enter you phone number');

        $('.error_phone_input').css('border-color','#e35569');

      }
      if(phone!=""){
        if ((IsPhone(phone) == false)||(phone.length !== 11) ) {
          $('#error_phone').empty();
          $('#error_phone').css('display','block');
          $('#error_phone').html('invalid phone number');

          $('.error_phone_input').css('border-color','#e35569');
         }
         // if (phone.length !== 11) {
         //
         // }

      }



     if(gender==""){
        $('#error_gender').css('display','block');
        $('.gender-div').css('border-color','#e35569');


      }
       if(age=="")
      {
         $('#error_age').css('display','block');
         $('.error_age_input').css('border-color','#e35569');
      }
      if(meeting==0)
     {
        $('#error_meeting').css('display','block');
        $('.meeting-div').css('border-color','#e35569');
     }
    if((name!='') && (email!="") && (IsEmail(email) == true) &&(phone!="") &&(IsPhone(phone) == true)&&(gender!="")&&(age!="")){

      $.ajax({
          type: "get",
          url: "{{url('make-account')}}",
          dataType: 'json',
          data: {
          'name':name,
          'email':email,
          'phone':phone,
          'age':age,
          'gender':gender,
          "_token": "{{ csrf_token() }}",
          'meeting':meeting,

          },
          success: function(data){
           if(data['res']=="email"){
             $('#error_email').empty();
             $('#error_email').css('display','block');
             $('#error_email').html('This email already exists');

             $('.error_email_input').css('border-color','#e35569');
           }
           if(data['res']=="phone"){
             $('#error_phone').empty();
             $('#error_phone').css('display','block');
             $('#error_phone').html('This number already exists');

             $('.error_phone_input').css('border-color','#e35569');
           }
           if(data['res']=="success"){
             $('#signup-form')[0].reset();
$('.sucsess_res').css('display','block');
           }
         },

          error: function (xhr, status, error) {
             console.log(status);
           }
          });// close ajax
    }

    });
    function IsEmail(email) {
          var regex =
// /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
/^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+(com)+$/;

// var emailblockReg =
//      /^([\w-\.]+@(?!gmail.com)(?!yahoo.com)([\w-]+\.)+[\w-]{2,4})?$/;

          if (!regex.test(email)) {
              return false;
          }
          else {
              return true;
          }
      }
      function IsPhone(phone) {
      var ch=Number(  phone.substring(0, 3));
      var regex=/^[0-9-+]+$/;
  //           var regex =
  // /^([0-9]{11})|(\([0-9]{3}\)\s+[0-9]{3}\-[0-9]{4})/;
            if (!regex.test(phone) ) {
                return false;
            }
         //     else if(ch==10 ){
         //       console.log('10');
         //      return true;
         //    }
         //    else if(ch==11 ){
         //      console.log('11');
         //
         //     return true;
         //   }
         //   else if(ch==12 ){
         //     console.log('12');
         //
         //    return true;
         //  }
         //  else if(ch==15 ){
         //    console.log('15');
         //
         //   return true;
         // }
            else {
                return true;
            }
        }
    });

    </script>
</body>
</html>
