@extends('layouts/layoutMaster')
@section('title',  'Hangman')
@section('content')


   <!-- <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Montserrat:wght@400;500&display=swap" rel="stylesheet">-->
   <!--<link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&amp;display=swap" rel="stylesheet">-->
    <style>
        /* Reset and Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #fdfdfd;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
            padding: 20px;
        }
        .secure-text{
            -webkit-text-security: disc;
        }
         .no-secure-text{
            -webkit-text-security: none;
        }
        .file-input {
      display: none;
    }

    /* تصميم الحاوية المخصصة */
    .custom-file-input {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 10px 20px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.3s ease;
    }

    .custom-file-input:hover {
      background-color: #0056b3;
    }

    /* أيقونة (باستخدام Font Awesome) */
    .custom-file-input i {
      font-size: 18px;
    }
 .responsive-image-container {
    max-width: 100%; /* Ensures the container does not exceed the parent width */
    height: 250px;    /* Maintains aspect ratio of the container */
    overflow: hidden; /* Ensures the image doesn't overflow the container */
  }
  img {
  max-width: 70%;
  height: 100%;
}
        .container {
            max-width: 700px;
            background: #ffffff;
            padding: 50px 30px;
            border-radius: 12px;
            box-shadow: 1px 1px 8px rgb(0 0 0 / 78%);
            animation: fadeIn 1s ease-out;
            width: 100%;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        p {
            font-size: 1rem;
            color: #555;
            margin-bottom: 30px;
        }

        /* Instructions */
        .instructions_en {
            text-align: left;
            margin-bottom: 30px;
            margin-left: 20px;

        }

        .instructions_en li {
            margin-bottom: 7px;
            font-size: 1rem;
            color: #2c3e50;
        }
         
 .instructions_ar {
            text-align: right;
            margin-bottom: 30px;
            margin-right: 20px;
            font-family: Almarai !important;

        }

        .instructions_ar li {
            margin-bottom: 7px;
            font-size: 1rem;
            color: #2c3e50;
                        font-family: Almarai !important;

        }
        /* Buttons */
        .btn-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            /*margin-top: 20px;*/
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            font-size: 1rem;
            font-weight: 500;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            min-width: 160px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .btn:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            transform: translateY(-3px);
        }

        /*.btn-primary {*/
        /*    background-color: #2c3e50;*/
        /*}*/

        /*.btn-primary:hover {*/
        /*    background-color: #1a252f;*/
        /*}*/

        .btn-secondary {
            background-color: #b88942;
        }

        .btn-secondary:hover {
            /*background-color: #a0784b;*/
                background-color: #a16e35;

        }
        .ar-btn{
       font-family: Almarai !important;

        }
.difficult_level {
    padding-top: 0px !important;
    padding-bottom: 0px !important;
}
.difficult_level button {
    border: 0;
    background-color: #ff000000;
    color: gray;
}
.active_easy {
    color: #39cc87 !important;
    /*border-bottom: 2px solid #39cc87 !important;*/
    font-weight: 500;
}
.active_medium {
    color: #ffa047 !important;
    /*border-bottom: 2px solid #ffa047 !important;*/
    font-weight: 500;
}
.active_hard {
    color: #d83e4b !important;
    /*border-bottom: 2px solid #d83e4b !important;*/
    font-weight: 500;
}
.et_pb_counter_container {
    margin-bottom: 0;
    background-color: #efefef !important;
    border-radius: 8px 8px 8px 8px;
    overflow: hidden;
    color: #fff;
    margin-bottom: 10px;
    position: relative;
    display: block;
    /* padding: 7px; */
    width: 32%;
    margin-inline: auto;
    margin-top: 3px;
}
.et_pb_counter_amount {
    padding-top: 0px;
    padding-bottom: 0px;
    opacity: 1;
    -webkit-animation: slideWidth 1s cubic-bezier(.77,0,.175,1) 1;
    animation: slideWidth 1s cubic-bezier(.77,0,.175,1) 1;
    position: relative;
    border-radius: 8px 8px 8px 8px;
    overflow: hidden;
    background-color: #39cc87;
    float: left;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.6em;
    text-align: right;
    /*padding: 3px;*/
    display: block;
    min-height: 5px;
    z-index: 2;
}
.et_pb_counter_amount.overlay {
    color: #39cc87;
    /*background-color: transparent !important;*/
    position: absolute !important;
    top: 0;
    left: 0;
    z-index: 1;
}
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }

            .container {
                padding: 30px 20px;
            }

            .btn {
                font-size: 0.9rem;
                padding: 10px 20px;
            }
             .responsive-image-container {
    max-width: 100%; /* Ensures the container does not exceed the parent width */
    height: auto !important;    /* Maintains aspect ratio of the container */
    overflow: hidden; /* Ensures the image doesn't overflow the container */
  }
  img {
  max-width: 40%;
  height: 100%;
}
.et_pb_counter_container {

    width:80% !important;
}
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 1.8rem;
            }

            .btn-group {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
                font-size: 0.85rem;
            }
                  .responsive-image-container {
    max-width: 100%; /* Ensures the container does not exceed the parent width */
    height: auto !important;    /* Maintains aspect ratio of the container */
    overflow: hidden; /* Ensures the image doesn't overflow the container */
  }
  img {
  max-width: 50%;
  height: 100%;
        }
            .et_pb_counter_container {

    width:80% !important;
}
        }
    </style>

    <div class="container">
        <!-- Header -->
        <h1> Hangman</h1>
        <div class="responsive-image-container">

        <img src="{{asset('images/hangman.png')}}">
</div>
       <!--<h1>Choose a Category</h1>-->
    <div class="categories">
        <form id="start_game" action="{{url('hangman/begin')}}" method="post" enctype="multipart/form-data" data-parsley-validate>
            @csrf
        <select id="category" class="form-select mb-3" name="cid">
           <option value="0" data-type="c" data-catname="Customized">Customized</option>

                @foreach($difficulty_categories as $difficulty_category)
                   <option data-type="dl" value="{{ $difficulty_category->id }}" data-catname="{{ $difficulty_category->name }}">{{ $difficulty_category->name }}</option>
                @endforeach
                
                  @foreach($group_words as $group_word)
                  <option data-type="gw" value="{{ $group_word->id }}" data-catname="{{ $group_word->name }}">{{ $group_word->name }}</option>
                @endforeach
            </select>
    <input type="hidden"  name="ct" id="cat_type" value="">
        <input type="hidden"  name="l" id="level_type" value="">

    <div class="Customized_word row">
        <div class="col-lg-10 col-md-10 col-sm-12 col-12 mb-2">
            
            
           <div class="input-group mb-3">
  <input class="form-control english_only error_word_input secure-text" name="w" id="word" type="text" placeholder="Type your secret word(s) here..." aria-label="Enter word" autocomplete="off">
  <input type="hidden" id="check_eye" value="slash">
   <span id="pass_eye_id" class="input-group-text" id="basic-addon1">
    <svg  xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="eye-slash" id="eye-slash" viewBox="0 0 16 16">
      <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7.029 7.029 0 0 0 2.79-.588zM5.21 3.088A7.028 7.028 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474L5.21 3.089z"/>
      <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829l-2.83-2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12-.708.708z"/>
    </svg>
    
    
    
      <svg  xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="eye-fill"  id="eye-fill" viewBox="0 0 16 16" style="display:none">
     <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
    </svg>
    
  </span>
</div>
    
    

    
    
    
    <!--<input class="form-control mb-3" type="text" placeholder="Enter Category name" aria-label="Enter word">-->
    <span class="text-danger  error_word text-start" id="error_word" style="display:none">This is field is required</span>
    <span class="text-danger error_english_only  text-start" id="error_english_only" style="display:none"></span>
        <span class="text-danger   text-start" id="error_length_char" style="display:none"></span>

</div>
<!--      <div class="col-lg-1 col-md-1 col-sm-2 col-2 d-flex align-items-center justify-content-center mb-2">-->
          
<!--           <label for="upload_img" class=" text-start ">-->
<!--  <svg width="30" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 308.8 308.8" style="enable-background:new 0 0 308.8 308.8;" xml:space="preserve"><g><path style="fill:#4A566E;" d="M35.6,18.8h180c19.6,0,35.6,16,35.6,35.6v185.2c0,19.6-16,35.6-35.6,35.6h-180 C16,275.2,0,259.2,0,239.6V54C0,34.8,16,18.8,35.6,18.8z"/><path style="fill:#00B594;" d="M116.4,186.4l-52.8-52.8L0,197.2v13.2v28.8c0,19.6,16,35.6,35.6,35.6h180c19.6,0,35.6-16,35.6-35.6 v-28.8v-39.6l-59.6-60L116.4,186.4z"/><circle style="fill:#FFCC03;" cx="114.8" cy="103.6" r="22.4"/><circle style="fill:#FFFFFF;" cx="251.2" cy="232.4" r="57.6"/></g><g><path style="fill:#00B594;" d="M224,240.8c-4.4,0-8.4-3.6-8.4-8.4s3.6-8.4,8.4-8.4h54.4c4.8,0,8.4,3.6,8.4,8.4s-3.6,8.4-8.4,8.4 H224z"/><path style="fill:#00B594;" d="M259.6,259.6c0,4.8-3.6,8.4-8.4,8.4s-8.4-3.6-8.4-8.4v-54c0-4.4,3.6-8.4,8.4-8.4 c4.4,0,8.4,3.6,8.4,8.4V259.6z"/></g></svg>-->
 
<!--  </label>-->
<!--  <input type="file" id="upload_img" class="file-input" name="img" accept="image/png, image/gif, image/jpeg">-->
<!--</div>-->
    
    
    
    
<!--       <div class="col-lg-1 col-md-1 col-sm-2 col-2 d-flex align-items-center justify-content-center mb-2">-->
          
<!--           <label for="upload_sound" class=" text-start ">-->
<!--<svg width="30px" id="Layer_1" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" data-name="Layer 1"><linearGradient id="GradientFill_1" gradientUnits="userSpaceOnUse" x1="256" x2="256" y1="461.937" y2="50.063"><stop offset="0" stop-color="#6c54a3"/><stop offset="1" stop-color="#00b1d2"/></linearGradient><path d="m54.975 366.1h52.5v-220.216h-52.5a55.053 55.053 0 0 0 -54.975 54.99v110.242a55.047 55.047 0 0 0 54.975 54.984zm79.335 8.308 105.276 76.785a54.445 54.445 0 0 0 32.208 10.745 55.3 55.3 0 0 0 25.108-6.146 53.9 53.9 0 0 0 30.066-49.014v-301.565a54.977 54.977 0 0 0 -87.383-44.413l-105.275 76.776v236.831zm377.69-118.408a13.423 13.423 0 0 0 -13.425-13.424h-47.475v-47.484a13.421 13.421 0 0 0 -26.842 0v47.479h-47.488a13.421 13.421 0 0 0 0 26.841h47.485v47.488a13.421 13.421 0 0 0 26.842 0v-47.488h47.478a13.417 13.417 0 0 0 13.425-13.412z" fill="url(#GradientFill_1)" fill-rule="evenodd"/></svg>-->
 
<!--  </label>-->
<!--  <input type="file" id="upload_sound" class="file-input" name="upload_sound" accept="audio/*">-->
<!--</div>-->
    
    
    
<!--     <div class=" row g-0">-->
<!--    <label for="upload_img" class="col-lg-3 col-md-3 col-sm-4 col-form-label text-start mb-2">Upload Sound</label>-->
<!--    <div class="col-lg-9 col-md-9 col-sm-8">-->
<!--        <input class="form-control" id="upload_sound" name="upload_sound" type="file">-->
<!--    </div>-->
<!--</div>-->





</div>
   
    <!--  <audio id="audioPlayer" controls>-->
    <!--    <source src="{{asset ('public/camb_words_api/pcrecord/art_room.mp3') }}" type="audio/mpeg">-->
    <!--    Your browser does not support the audio element.-->
    <!--</audio>-->
    <div class="text-center difficult_level" >
<button class="level_btn active_easy easy l_active" data-type="easy">Easy&nbsp; &nbsp; &nbsp;&nbsp;</button> <button class="level_btn medium" data-type="medium"> Medium&nbsp;&nbsp; &nbsp; &nbsp;</button>  <button class="level_btn hard " data-type="hard"> Hard</button></div>

<span class="et_pb_counter_container">
        <span class="et_pb_counter_amount " style="width: 30%;"><span class="et_pb_counter_amount_number"><span class="et_pb_counter_amount_number_inner"></span></span></span>
        <span class="et_pb_counter_amount  overlay" style="width: 30%;"><span class="et_pb_counter_amount_number"><span class="et_pb_counter_amount_number_inner"></span></span></span>
        </span>
<br>
        <!-- Buttons -->
        <div class="btn-group">
            <button class="btn btn-primary" type="submit" id="start-button" aria-label="Start Game">Start Game</button>
        </div>
        </form>
    </div>
     @endsection
     
     
  


     
    @push('scripts')
    
     <script src="{{asset('public/js/jquery-2.1.4.min.js')}}"></script>
    <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>-->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
  console.log('kkj');


      $('#category').change(function () {
          var cat_name=$('#category option:selected').data('catname');
        //   console.log(cat_name);
        if (cat_name =="Customized") {
          $('.Customized_word').css('display','-webkit-box');
        } else {
          $('.Customized_word').css('display','none');
        }
      });
    



$('#pass_eye_id').click(function () {
  // Toggle the SVG classes for eye/eye-slash
   var check_eye = $('#check_eye').val();
  if(check_eye=="slash")
  {
      
        $('#eye-slash').css('display','none');
    $('#eye-fill').css('display','block');
  

$('#check_eye').val('eye');
 
   
    // Change input field class
    $('#word').removeClass('secure-text');
    $('#word').addClass('no-secure-text');
  } else {
        
 $('#eye-fill').css('display','none');
      $('#eye-slash').css('display','block');
   
  

$('#check_eye').val('slash');
   
    $('#word').removeClass('no-secure-text');
    $('#word').addClass('secure-text');
  }
});

$('#word').on('input', function() {
    var value = $(this).val();
//   $('#error_length_char').empty();
    // Check if the value length is greater than 15 or contains non-alphabetic characters
    if (value.length > 20) {
      // Truncate to 15 characters and remove invalid characters
      $(this).val(value.substring(0, 20));
             $('#error_length_char').css('display','block');
    //   $('#error_length_char').html('max length is 15 characters')
    }
    
    if ( !/^[A-Za-z\s-]*$/.test(value)) {
      // Truncate to 15 characters and remove invalid characters
      $(this).val(value.replace(/[^A-Za-z\s-]/g, ''));
       $('#error_length_char').css('display','block');
            // $('#error_length_char').html('invaild input');

    }
  });
    $(document).keydown(function (e) {
    if (e.keyCode === 13) { // Check if the Enter key is pressed
      $('#start-button').click(); // Trigger the button click
    }
    });
        $('#start-button').on('click', function (e) {
            
            //   e.preventDefault();
         $('#error_length_char').empty();
          const category_id =   $("#category :selected").val();
            var word= $("#word").val();
            var cat_name=$('#category option:selected').data('catname');
            var cat_type=$('#category option:selected').data('type');
            // var level_type=$('#level_type').val();
          var level=$(".l_active").data('type');
                    $('#level_type').val(level);
        //   console.log($('#level_type').val());
          var check="true";
        if (cat_name =="Customized") {
             if (word =="") {
                 $('#error_word').css('display','block');
                 $('.error_word_input').addClass('is-invalid');
        // $('.error_phone_helper_input').css('border-color','#e35569');
          var check="false";


             }
             else{
             $('#error_word').css('display','none');
          $('.error_word_input').removeClass('is-invalid');
          var check="true";


             }
        }
        
                $('#cat_type').val($('#category option:selected').data('type'));

        
        if( check=="true")
       {
          $("#start_game").submit();
              
           
       }

        });
        
        
        $(document).on("keyup input",".english_only",function() {

// $(".english_only").on("keyup", function(){
    var check=/^[ A-Za-z_0-9@./#&+-]*$/i.test($(this).val().trim());
    if(check==false){
                 $('#error_english_only').css('display','block');
                                  $('#error_word').css('display','none');

     $(this).parent().find(".error_english_only").html('Write in english only ');
     return false;
    }
    else{
                         $('#error_english_only').css('display','none');

        $(this).parent().find(".error_english_only").empty();
        return true;
    }
  });
function check_english(input){
return(/^[ A-Za-z_0-9@./#&+-]*$/i.test(input.trim()));
}
        

        $('.level_btn').click(function(e){
  e.preventDefault();
  var level=$(this).data('type');
    $(".level_btn").removeClass("l_active");
    $(this).addClass("l_active");
  if(level=='easy'){
      $('#level_type').val('easy');
    if($( ".difficult_level button" ).hasClass( "active_medium" )){
      $( ".difficult_level button" ).removeClass( "active_medium" );
    }
    if($( ".difficult_level button" ).hasClass( "active_hard" )){
      $( ".difficult_level button" ).removeClass( "active_hard" );
    }
    $( ".difficult_level .easy" ).addClass( "active_easy" );
          $(".et_pb_counter_amount").css('width','30%');

    $(".et_pb_counter_amount").css('background-color','#39cc87');
  }
  if(level=='medium'){
      $('#level_type').val('medium');
    if($( ".difficult_level button" ).hasClass( "active_easy" )){
      $( ".difficult_level button" ).removeClass( "active_easy" );
    }
    if($( ".difficult_level button" ).hasClass( "active_hard" )){
      $( ".difficult_level button" ).removeClass( "active_hard" );
    }
    $( ".difficult_level .medium" ).addClass( "active_medium" );
          $(".et_pb_counter_amount").css('width','60%');

        $(".et_pb_counter_amount").css('background-color','#ffa047');

  }
  if(level=='hard'){
            $('#level_type').val('hard');

    if($( ".difficult_level button" ).hasClass( "active_easy" )){
      $( ".difficult_level button" ).removeClass( "active_easy" );
    }
    if($( ".difficult_level button" ).hasClass( "active_medium" )){
      $( ".difficult_level button" ).removeClass( "active_medium" );
    }
    $( ".difficult_level .hard" ).addClass( "active_hard" );
      $(".et_pb_counter_amount").css('width','100%');
            $(".et_pb_counter_amount").css('background-color','#d83e4b');

  }

    });
        
    });
    </script>
    
    @endpush
  
     
    
    

