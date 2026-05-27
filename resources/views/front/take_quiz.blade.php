<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
<meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>TC | placement test </title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{asset('assets/admin/plugins/fontawesome-free/css/all.min.css')}}">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bbootstrap 4 -->
  <link rel="stylesheet" href="{{asset('assets/admin/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">
  <!-- iCheck -->
  <link rel="stylesheet" href="{{asset('assets/admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{asset('assets/admin/dist/css/adminlte.min.css')}}">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="{{asset('assets/admin/plugins/overlayScrollbars/css/OverlayScrollbars.min.css')}}">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="{{asset('assets/admin/plugins/daterangepicker/daterangepicker.css')}}">
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.4.0/min/dropzone.min.css">
  <link rel="stylesheet" href="{{asset('assets/front/css/record_style.css')}}">
    </head>
    <link rel="shortcut icon" href="{{asset('images/favicon.png')}}">


<style>
.upload_record:disabled,
.upload_record[disabled]{
     color: rgb(0, 85, 217)!important;
    background-color: rgb(217, 232, 255)!important;
}
.no_connection_content{
    box-shadow: none !important;
    background-color: #fff0 !important;
    border: 0 !important;
}
.modal-backdrop.show {
    opacity: 1 !important;
}
.modal-backdrop {
    background-color: #4a4949 !important;
}
@media only screen and (max-width: 600px) {
  audio {
    max-width: 250px!important;
  }
}
audio:hover, audio:focus, audio:active
{
-webkit-box-shadow: 15px 15px 20px rgba(0,0, 0, 0.4);
-moz-box-shadow: 15px 15px 20px rgba(0,0, 0, 0.4);
box-shadow: 15px 15px 20px rgba(0,0, 0, 0.4);
-webkit-transform: scale(1.05);
-moz-transform: scale(1.05);
transform: scale(1.05);
}


audio
{
  width: 347px;
    background-color: #f1f3f4;
-webkit-transition:all 0.5s linear;
-moz-transition:all 0.5s linear;
-o-transition:all 0.5s linear;
transition:all 0.5s linear;
-moz-box-shadow: 2px 2px 4px 0px #006773;
-webkit-box-shadow:  2px 2px 4px 0px #006773;
box-shadow: 2px 2px 4px 0px #006773;
-moz-border-radius:7px 7px 7px 7px ;
-webkit-border-radius:7px 7px 7px 7px ;
border-radius:7px 7px 7px 7px ;
}



body {
  /*background-image: url("{{asset('public/quiz/q1.jpg')}}");*/
  background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(9,9,121,1) 35%, rgba(0,212,255,1) 100%);
  height: 100%;
  /* Center and scale the image nicely */
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
    background-color: #eee
}
.nav-link{
    font-family: cursive!important;
    color: #ce2009 !important;
}
.card-header{
        background-color: #f2f1f0 !important;
    border-bottom: 7px solid #004c76 !important;
}
[class*=icheck-]>input:first-child+label::before {
    width:15px !important;
    height:15px !important;

}
 [class*=icheck-]>input:first-child:checked+label::after {
     top:-3px !important;
     left:-3px !important;
 }

 hr.hr-quiz{
  width: 84%;
border: 1px dashed #a5ccd3;
}
.blank{
  border:0px;
  color: #c9372a !important;
  background-color: #ffe4c400;


}
.count_blank{

}
.blank_option{padding-bottom: 5px;
}
.blank:focus{
    outline: none;
}
.hquiz{
    color: #333;
    margin: 0 0 10px;
    /* font-size: 31px; */
    font-weight: 600;
    margin-left: 15px;

}
.div_hquiz{
    border-bottom: 7px solid #004c76;
    background-color: #f2f1f0;
        margin-top: 20px;

}
.question {
    margin-top: 14px;

}
.countdown{
    border: 4px solid gray;
    width: 90px;
    height: 90px;
    line-height: 85px;
    border-radius: 50%;
    text-align: center;
    font-size: 25px;
   color: #004c76;
    font-weight: 600;
}
.option-label{
    border: 1px solid #9e9e9e;
    border-radius: 8px;
    background: #fafafa;
    padding: 16px 8px 16px 13px;
    font-weight: normal;
    float: none;
    max-width: 436px;
    box-sizing: border-box;
    width: auto;
    margin-bottom: 10px;
}
.option-label:hover{
    border-color: #0091ea;

}
.option-input:hover{
        border-color: #0091ea;

}
label.radio {
    cursor: pointer
}

label.radio input {
    position: absolute;
    top: 0;
    left: 0;
    visibility: hidden;
    pointer-events: none
}

label.radio span {
    padding: 4px 0px;
    border: 1px solid red;
    display: inline-block;
    color: red;
    width: 100px;
    text-align: center;
    border-radius: 3px;
    margin-top: 7px;
    text-transform: uppercase
}

label.radio input:checked+span {
    border-color: red;
    background-color: red;
    color: #fff
}

.ans {
    margin-left: 36px !important
}

.btn:focus {
    outline: 0 !important;
    box-shadow: none !important
}

.btn:active {
    outline: 0 !important;
    box-shadow: none !important
}
.left{
  font-size: 25px;
    font-style: oblique;
    font-family: auto;
    color:darkred;
}
</style>
<body class="hold-transition sidebar-mini layout-fixed">
      <div class="wrapper">
<div class="container mt-5">
    <div class="d-flex justify-content-center row">
        <div class="col-md-11 col-lg-11">
            <div class="col-md-12">
                <div class="question bg-white p-3 border-bottom">
                    <div class="d-flex flex-row  align-items-center mcq div_hquiz">
                        <h3 class="col-md-10 col-sm-9 hquiz">English Placement Test</h3>
                        <div class="col-md-2 col-sm-2 row justify-content-center">
<!--<div id="message_no" class="alert alert-danger align-items-center alert-dismissible fade show succ_cart_msg" role="alert" style="display: none; position: fixed; right: 5px; z-index: 1000; opacity: 500;"> </div>-->

<div id="message" class="alert alert-success align-items-center alert-dismissible fade show succ_cart_msg" role="alert" style="display: none; position: fixed; right: 5px; z-index: 1000; opacity: 500;"> </div>
<input type="hidden" id="check_net" value="">
                        <!--<div class="left">Remaining Time:</div>-->
                           <div class="countdown">
                               </div>
                    </div>
                </div>
                <div class="question bg-white p-3 border-bottom">
                  <div class="card card-primary card-tabs">
                    <!-- tabs -->
                    <div class="card-header p-0 pt-1" >
                      <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                          <!--kids 3steps-->
                          @if($course_id==1)
                        @foreach($sections as $k=> $section)
                        @if($section->id<=3)
                        <li  role="presentation"  class="nav-item ">
                          <a  @if($k == 0) class=" nav-link active" @endif class="nav-link " id="{{$section->title}}-tap" data-toggle="pill" href="#tab-{{$section->id}}" role="tab" aria-controls="#tab-{{ $section->id }}" >{{$section->title}}</a>
                        </li>
                        @endif
                        @endforeach
                        @endif
                        
                        
                          @if($course_id==3)
                        @foreach($sections as $k=> $section)
                        @if($section->id<=3)
                        <li  role="presentation"  class="nav-item ">
                          <a  @if($k == 0) class=" nav-link active" @endif class="nav-link " id="{{$section->title}}-tap" data-toggle="pill" href="#tab-{{$section->id}}" role="tab" aria-controls="#tab-{{ $section->id }}" >{{$section->title}}</a>
                        </li>
                        @endif
                        @endforeach
                        @endif
                        
                          @if($course_id==2)
                        @foreach($sections as $k=> $section)
                        
                        <li  role="presentation"  class="nav-item ">
                          <a  @if($k == 0) class=" nav-link active" @endif class="nav-link " id="{{$section->title}}-tap" data-toggle="pill" href="#tab-{{$section->id}}" role="tab" aria-controls="#tab-{{ $section->id }}" >{{$section->title}}</a>
                        </li>
                       
                        @endforeach
                        @endif
                      </ul>
                    </div>

                    <!-- body -->
                    <form id="quizForm" class="col-sm-12 col-md-12" action="{{route('front.quiz.store')}}" method="get" enctype="multipart/form-data" data-parsley-validate>

                    <div class="card-body">
                      <input type="hidden" name="id" value="{{$id}}"/>
                      <div class="tab-content" id="custom-tabs-one-tabContent">
                          
                          
                        @foreach($sections as $ke=>$sec)

                        <!-- sec info -->
                        <div @if($ke == 0) class="tab-pane active" @else class="tab-pane" @endif id="tab-{{$sec->id}}" role="tabpanel"  >
                          {!!$sec->sec_desc !!}
                          @if($sec->audio)
                          <audio controls  preload="none">
                           <source src="{{asset($sec->audio)}}" type="audio/mpeg">
                           Your browser does not support the audio element.
                           </audio>
                          @endif

                          @if($sec->video)
                          <video width="320" height="240" controls>
                          <source src="{{asset($sec->video)}}" type="video/mp4">
                          Your browser does not support the video tag.
                        </video>
                          @endif

                          <!-- quizzes -->
                        @foreach($quizzes as $q=>$quiz)
                          @if($quiz->section_id == $sec->id)
                          <div class="col-sm-12 col-md-12">
                          {!!$quiz->description!!}
                          </div>
                          @if($quiz->question_type =='True Or False')
                            <?php  
                                 $options= json_decode($quiz->option_text);
                                 $emp_answer=json_decode($quiz->emp_answer);
                           ?>
                          <div class="col-sm-12 col-md-12">
                            <div class="form-group clearfix">
                              @foreach($options as $r=>$option)
                               @if(!empty($emp_answer))
                               @foreach($emp_answer as $emp_ans)
                                @if($option==$emp_ans)
                              <div class="icheck-primary option-label">
<input class="option-input answers_input" type="radio" data-ques_type="True Or False" data-stu_id="{{$id}}" data-quiz_id="{{$quiz->emp_quiz_id}}" value="{{$quiz->emp_quiz_id}}_{{$option}}" id="radio{{$quiz->emp_quiz_id}}_{{$r}}" name="r_{{$quiz->emp_quiz_id}}" checked>
                              <label  style="font-weight:500 !important;font-family:emoji !important;font-size: 17px !important;" for="radio{{$quiz->emp_quiz_id}}_{{$r}}">
                                {{$option}}
                              </label>
                            </div>
                            @else 
                            <div class="icheck-primary option-label">
<input class="option-input answers_input" type="radio" data-ques_type="True Or False" data-stu_id="{{$id}}" data-quiz_id="{{$quiz->emp_quiz_id}}" value="{{$quiz->emp_quiz_id}}_{{$option}}" id="radio{{$quiz->emp_quiz_id}}_{{$r}}" name="r_{{$quiz->emp_quiz_id}}" >
                              <label  style="font-weight:500 !important;font-family:emoji !important;font-size: 17px !important;" for="radio{{$quiz->emp_quiz_id}}_{{$r}}">
                                {{$option}}
                              </label>
                            </div>
                            
                            @endif
                             @endforeach
                             
                               @else
                                <div class="icheck-primary option-label">
<input class="option-input answers_input" type="radio" data-ques_type="True Or False"  data-stu_id="{{$id}}" data-quiz_id="{{$quiz->emp_quiz_id}}" value="{{$quiz->emp_quiz_id}}_{{$option}}" id="radio{{$quiz->emp_quiz_id}}_{{$r}}" name="r_{{$quiz->emp_quiz_id}}" >
                              <label  style="font-weight:500 !important;font-family:emoji !important;font-size: 17px !important;" for="radio{{$quiz->emp_quiz_id}}_{{$r}}">
                                {{$option}}
                              </label>
                            </div> 
                            
                             @endif
                              @endforeach
                            </div>
                           </div>
                              <hr class="hr-quiz">

                          @endif

                        @if($quiz->question_type =='Single Or Multi Choice')
                        <?php
                        $options= json_decode($quiz->option_text);
                        $emp_answer=json_decode($quiz->emp_answer);
                        ?>
                        <div class="col-sm-12 col-md-12">
                        <div class="form-group clearfix">
                          @foreach($options as $c=>$option)
                          
                          
                            <div class="icheck-primary option-label form-check">
<input class="form-check-input answers_input" type="checkbox" data-ques_type="Single Or Multi Choice" data-stu_id="{{$id}}" data-quiz_id="{{$quiz->emp_quiz_id}}" value="{{$quiz->emp_quiz_id}}_{{$option}}" id="{{$quiz->emp_quiz_id}}_{{$c}}" name="r_{{$quiz->emp_quiz_id}}[]" @if(!empty($emp_answer)) @foreach($emp_answer as $emp_ans) @if($option==$emp_ans) checked @endif @endforeach @endif >
                              <label class="form-check-label" style="font-weight:500 !important;font-family:emoji !important;font-size: 17px !important;" for="{{$quiz->emp_quiz_id}}_{{$c}}">
                                {{$option}}
                              </label>
                            </div>
                           
                             
                             
                             
                             
                            @endforeach
                        </div>
                       </div>
                          <hr class="hr-quiz">

                      @endif

                      @if($quiz->question_type =='Free Answer')
                      <textarea data-ques_type="Free Answer" class="form-control   answers_input" rows="4" class="form-control  " value=""  name="freeanswer_{{$quiz->emp_quiz_id}}" id="free_answer_{{$quiz->emp_quiz_id}}" data-stu_id="{{$id}}" data-quiz_id="{{$quiz->emp_quiz_id}}" >
                          
                          @if(($quiz->emp_answer !=null)|| ($quiz->emp_answer !=""))
                          {{$quiz->emp_answer}}
                          @endif
                          
                      </textarea>
                                             <hr class="hr-quiz">

                      @endif
                      @if($quiz->question_type =='Fill In Blank')
                     
                      <?php  
                      $options= json_decode($quiz->option_text); 
                      $emp_answer=json_decode($quiz->emp_answer);
                      ?>
                      @foreach($options as $f=>$option)
                    <div class="blank_option col-md-12 option-label">
                        @if($emp_answer)
                         @foreach($emp_answer as $em=>$emp_ans)
                      @if($f==$em)
                  <span class="count_blank">  {{$f+1}}. </span><input data-ques_type="Fill In Blank" data-stu_id="{{$id}}" data-quiz_id="{{$quiz->emp_quiz_id}}" placeholder="_________" class="blank answers_input" type="text" value="{{$emp_ans}}" id="f_{{$quiz->emp_quiz_id}}_{{$f}}" name="f_{{$quiz->emp_quiz_id}}[]" >
                  @endif
                  @endforeach
                  @else 
                  <span class="count_blank">  {{$f+1}}. </span><input data-ques_type="Fill In Blank" data-stu_id="{{$id}}" data-quiz_id="{{$quiz->emp_quiz_id}}" placeholder="_________" class="blank answers_input" type="text" value="" id="f_{{$quiz->emp_quiz_id}}_{{$f}}" name="f_{{$quiz->emp_quiz_id}}[]" >
                  
                  @endif
                    </div>
                      @endforeach
                                               <hr class="hr-quiz">

                      @endif


                      @if($quiz->question_type =='Record')
                      <div id="row">
                          @if($quiz->emp_answer)
  <div class="col-md-9 free_answer_quiz ">
                                                    <?php
                                                    $emp_answer=$quiz->emp_answer;
                                                    $emp_answer='public/'.$emp_answer;
                                                    ?>
                                                    <audio controls="controls">
                                                    <source src="{{asset($emp_answer)}}" type="audio/mpeg">
                                                    Your browser does not support the audio tag.</audio>
                                                </div>
                                                
                                                @else

<div class="css-10mtgs1 col-md-8">

  <!-- recordbox -->
  <div class="css-s5xdrg col-md-12 recordbox_{{$quiz->emp_quiz_id}}">
    <!-- record btn -->
    <div class="recording_btn_{{$quiz->emp_quiz_id}} col-md-12 row">

    <button type="button" class="css-1q80go0 ezfki8j0 col-md-2 record-button recordbut_{{$quiz->emp_quiz_id}}" id="recordButton" data-quiz_id="{{$quiz->emp_quiz_id}}">
      <i size="16" class="css-vih333 efou2fk0">
        <svg width="16" height="16" preserveAspectRatio="none" viewBox="0 0 24 24"><path fill="#0055D9" d="M17.34 11.661c.375 0 .662.275.662.635 0 2.944-2.295 5.422-5.34 5.74v1.693h2.405c.376 0 .662.276.662.636s-.286.635-.662.635H8.934c-.375 0-.662-.275-.662-.635s.287-.636.662-.636h2.405v-1.694C8.294 17.718 6 15.24 6 12.296c0-.36.287-.635.662-.635s.662.275.662.635c0 2.478 2.096 4.49 4.677 4.49 2.581 0 4.677-2.012 4.677-4.49 0-.36.287-.635.662-.635zM12 3c2.03 0 3.685 1.588 3.685 3.536v5.76c0 1.949-1.654 3.537-3.684 3.537-2.03-.021-3.684-1.588-3.684-3.558V6.536C8.317 4.588 9.97 3 12 3z"></path></svg>
      </i>
      Record
    </button>
    <div class="css-1otgxk4 col-md-10">
      <p class="css-ve9zs5">You can record one time only</p>
    </div>
  </div>
  <!-- end record_btn -->
  <!-- stop_pause btn -->
  <div class="stop_pause_btn_{{$quiz->emp_quiz_id}} col-md-4" style="display:none;">
<button type="button" class="col-md-2 css-r29ycs ezfki8j0 pause-button pausebut_{{$quiz->emp_quiz_id}}" id="pauseButton" data-quiz_id="{{$quiz->emp_quiz_id}}">
  <i size="24" class="css-16r7llb efou2fk0 pause-icon_{{$quiz->emp_quiz_id}}">
    <svg width="24" height="24" preserveAspectRatio="none" viewBox="0 0 24 24"><path fill="#4D6182" d="M19.464 21V3H14.33v18zm-10.33 0V3H4v18z"></path></svg>
    </i>

    <i size="24" class="css-16r7llb efou2fk0 resume-icon_{{$quiz->emp_quiz_id}}"style="display:none;">
    <svg width="24" height="24" preserveAspectRatio="none" viewBox="0 0 24 24">
      <path fill="#4D6182" d="m12 5c-3.86599325 0-7 3.13400675-7 7 0 1.8565154.73749788 3.6369928 2.05025253 4.9497475 1.31275465 1.3127546 3.09323207 2.0502525 4.94974747 2.0502525 3.8659932 0 7-3.1340068 7-7 0-3.86599325-3.1340068-7-7-7z"></path></svg>
    </i>
  </button>
  <button type="button" class="col-md-2 css-o1wny5 ezfki8j0 stop-button stopbut_{{$quiz->emp_quiz_id}}" id="stopButton" data-quiz_id="{{$quiz->emp_quiz_id}}">
    <i size="24" class="css-16r7llb efou2fk0">
      <svg width="24" height="24" preserveAspectRatio="none" viewBox="0 0 24 24"><path fill="#FE3030" d="M21 21V3H3v18z"></path></svg>
    </i>
  </button>
  <div class="css-1otgxk4 record-pause-resume-massage">
    <div class="css-ny1adc record-msg_{{$quiz->emp_quiz_id}}">
      <span class="css-1lf1lm0"></span>
      <p class="css-z4k8dv">Recording……</p>
    </div>
    <div class="css-ny1adc resume-msg_{{$quiz->emp_quiz_id}}" style="display:none;">
<p class="css-1of55lx ">Press Resume to continue recording</p>
</div>
  </div>
</div>
<!-- end stop_pause btn -->
</div>
<!-- end record box -->
<div class="css-s5xdrg audio-box audio-box_{{$quiz->emp_quiz_id}} row" style="display:none">
  <div class="record_list col-md-12 row"id="recordingsList_{{$quiz->emp_quiz_id}}" >

<div class="col-md-8 col-sm-8 ">
  <audio src="" class="col-md-12 col-sm-12 uploaded_audio audio_{{$quiz->emp_quiz_id}}" controls="true"></audio>
</div>
{{--<div class="col-md-1 col-sm-1 ">
  <button  class="col-md-12 col-sm-12 delete_record trash_{{$quiz->emp_quiz_id}}" id="delete_record" data-quid="{{$quiz->emp_quiz_id}}">
  <i class="fas fa-trash"></i>
</button>
</div>--}}
<div class="col-md-3 col-sm-3 "><button  class=" col-md-12 col-sm-12 upload_record save_{{$quiz->emp_quiz_id}}" id="upload_record_{{$quiz->emp_quiz_id}}" data-quid="{{$quiz->emp_quiz_id}}" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
Click to upload
</button>
</div>

  </div>
  <p class="css-ve9zs5 saving-msg_{{$quiz->emp_quiz_id}} saving_record col-md-12" style="display:none">You can record one time only</p>
</div>

</div>
@endif
                       <!-- <button class="record-button recordbut_{{$quiz->emp_quiz_id}}"id="recordButton" data-quiz_id="{{$quiz->emp_quiz_id}}">Record</button>
                       <button class="pause-button pausebut_{{$quiz->emp_quiz_id}}" id="pauseButton" data-quiz_id="{{$quiz->emp_quiz_id}}" disabled>Pause</button>
                       <button class="stop-button stopbut_{{$quiz->emp_quiz_id}}" id="stopButton" data-quiz_id="{{$quiz->emp_quiz_id}}" disabled>Stop</button> -->
                      </div>
                      <input data-ques_type="Record" data-stu_id="{{$id}}" data-quiz_id="{{$quiz->emp_quiz_id}}" type="hidden" value="" class="answers_input" id="record_file_{{$quiz->emp_quiz_id}}" name='record_{{$quiz->emp_quiz_id}}'/>
<input value="{{$id}}" id="stu_id" type="hidden">
<input value="{{$quiz->emp_quiz_id}}" id="record_quiz_id" type="hidden">


                      <!-- <div id="formats">Format: start recording to see sample rate</div>
                      <p><strong>Recordings:</strong></p> -->
                      <!-- <ol class="record_list"id="recordingsList_{{$quiz->emp_quiz_id}}"></ol> -->
                       @endif


                    @endif
                    @endforeach <!--End Quiz-->
                    <input type="hidden" value="" id="current_quiz_id" name='current_quiz_id'/>

                      </div>
                    @endforeach <!--End sec info-->
                    
                    <input type="hidden" name="quiz_time" id="quiz_time" value="{{$time}}">
                        <div class="form-group row justify-content-md-center">
                           <div class="col-sm-2 col-md-2" style="margin-top:17px">
                             <button type="submit" class="btn btn-warning sumbit-btn" id="add_section"   style="display:none" >submit</button>
                           </div>
                         </div>

                      </div>
                    </div>

                  </form>
                    <div class="d-flex flex-row justify-content-between align-items-center p-3 bg-white">
                      <button class="btn btn-primary d-flex align-items-center btn-danger prevtab" type="button">
                        <i class="fa fa-angle-left mt-1 mr-1"></i>
                        &nbsp;Previous
                      </button>
                      <button class="btn btn-primary border-success align-items-center btn-success nexttab"  type="button">
                        Next<i class="fa fa-angle-right ml-2"></i>
                      </button>
                    </div>

                    <!-- /.card -->
                  </div>




                  <footer class="main-footer" style="margin-left:0px !important;">
                      <strong>Copyright &copy; Mr. Usama Azb. </strong>
                      All rights reserved.
                      <div class="float-right d-none d-sm-inline-block">
                      </div>
                    </footer>







                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->



<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
     
      <div class="modal-body text-center">
       <button class="btn btn-primary" type="button" disabled>
  <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
  Loading...
</button>
      </div>
      
    </div>
  </div>
</div>



<div class="modal fade" id="no_connectionmodal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content no_connection_content">
     
      <div class="modal-body text-center">
       <div id="message_no" class="alert alert-danger align-items-center alert-dismissible fade show succ_cart_msg" role="alert" > </div>
      </div>
      
    </div>
  </div>
</div>







  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="{{asset('assets/admin/plugins/jquery/jquery.min.js')}}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{asset('assets/admin/plugins/jquery-ui/jquery-ui.min.js')}}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script> -->
<script src="{{asset('assets/admin/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('assets/admin/plugins/moment/moment.min.js')}}"></script>
<script src="{{asset('assets/admin/plugins/daterangepicker/daterangepicker.js')}}"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="{{asset('assets/admin/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js')}}"></script>

<!-- overlayScrollbars -->
<script src="{{asset('assets/admin/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{asset('assets/admin/dist/js/adminlte.js')}}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!-- <script src="{{asset('assets/admin/dist/js/pages/dashboard.js')}}"></script> -->
<!-- AdminLTE for demo purposes -->
<script src="{{asset('assets/admin/dist/js/demo.js')}}"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>

<script src="{{asset('assets/admin/tinymce/js/tinymce/tinymce.min.js')}}" ></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.4.0/dropzone.js"></script>
<script>
// document.addEventListener('contextmenu', event => event.preventDefault());

</script>
<script src="{{asset('assets/front/js/recorder.js')}}"></script>

@stack('record')
@stack('timer')
<script>
// $(document).ready(function(){

//     $(window).on('beforeunload',function(){
//         alert('hai');
//     });

// });
// work
//     window.addEventListener('beforeunload', (event) => {
//   event.preventDefault();
//   event.returnValue = '';
 

// });

// end work
// window.onbeforeunload = function (event) {
//     var message = 'Please click on Save button to leave this page';
//     if (typeof event == 'undefined') {
//         event = window.event;
//     }
//     if (event) {
//         event.returnValue = message;
//     }
//     return message;
// };

</script>

<script>


  


function bootstrapTabControl(){
  var i, items = $('.nav-link'), pane = $('.tab-pane');

  $('.nav-item').on('click', function(){
      $('.sumbit-btn').css('display','none');
      $('.nexttab').prop('disabled', false);
      });
      $('#custom-tabs-one-tab li:last').on('click', function(){
        $('.sumbit-btn').css('display','block');
        $('.nexttab').prop('disabled', true);

      });
  // next
  $('.nexttab').on('click', function(){

      for(i = 0; i < items.length; i++){
          if($(items[i]).hasClass('active') == true){
            if(items.length -2 == i ){
              $('.sumbit-btn').css('display','block');
              $('.nexttab').prop('disabled', true);
            }
            else{
              $('.sumbit-btn').css('display','none');
              $('.nexttab').prop('disabled', false);

            }
              break;
          }


      }
      if(i < items.length - 1){
 document.body.scrollTop = 0; // For Safari
  document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
          // for tab
          $(items[i]).removeClass('active');
          $(items[i+1]).addClass('active');
          // for pane
          $(pane[i]).removeClass('show active');
          $(pane[i+1]).addClass('show active');

      }

  });
  // Prev
  $('.prevtab').on('click', function(){
    $('.sumbit-btn').css('display','none');
    $('.nexttab').prop('disabled', false);

      for(i = 0; i < items.length; i++){
          if($(items[i]).hasClass('active') == true){
              break;
          }
      }
      if(i != 0){
          document.body.scrollTop = 0; // For Safari
  document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
          // for tab
          $(items[i]).removeClass('active');
          $(items[i-1]).addClass('active');
          // for pane
          $(pane[i]).removeClass('show active');
          $(pane[i-1]).addClass('show active');
      }
  });
}
bootstrapTabControl();

</script>



<script type="text/javascript">
var useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

tinymce.init({
  selector: 'textarea.tinymce-editor',
  plugins: 'image code print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars emoticons',
  imagetools_cors_hosts: ['picsum.photos'],
  menubar: 'file edit view insert format tools table help',
  toolbar: 'image code |undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor codesample | ltr rtl',
  toolbar_sticky: true,
  autosave_ask_before_unload: true,
  autosave_interval: '30s',
  autosave_prefix: '{path}{query}-{id}-',
  autosave_restore_when_empty: false,
  autosave_retention: '2m',
  image_advtab: true,



  file_picker_types: 'image',
     images_upload_handler: function (blobInfo, success, failure) {
         let data = new FormData();
         data.append('file', blobInfo.blob(), blobInfo.filename());
         axios.post('/admin/upload/images', data)
             .then(function (res) {
                 success(res.data.location);
             })
             .catch(function (err) {
                 failure('HTTP Error: ' + err.message);
             });
     }
,





  link_list: [
    { title: 'My page 1', value: 'http://www.tinymce.com' },
    { title: 'My page 2', value: 'http://www.moxiecode.com' }
  ],
  image_list: [
    { title: 'My page 1', value: 'http://www.tinymce.com' },
    { title: 'My page 2', value: 'http://www.moxiecode.com' }
  ],
  image_class_list: [
    { title: 'None', value: '' },
    { title: 'Some class', value: 'class-name' }
  ],
  importcss_append: true,
  file_picker_callback: function (callback, value, meta) {
    /* Provide file and text for the link dialog */
    if (meta.filetype === 'file') {
      callback('https://www.google.com/logos/google.jpg', { text: 'My text' });
    }

    /* Provide image and alt text for the image dialog */
    if (meta.filetype === 'image') {
      callback('https://www.google.com/logos/google.jpg', { alt: 'My alt text' });
    }

    /* Provide alternative source and posted for the media dialog */
    if (meta.filetype === 'media') {
      callback('movie.mp4', { source2: 'alt.ogg', poster: 'https://www.google.com/logos/google.jpg' });
    }
  },
  templates: [
        { title: 'New Table', description: 'creates a new table', content: '<div class="mceTmpl"><table width="98%%"  border="0" cellspacing="0" cellpadding="0"><tr><th scope="col"> </th><th scope="col"> </th></tr><tr><td> </td><td> </td></tr></table></div>' },
    { title: 'Starting my story', description: 'A cure for writers block', content: 'Once upon a time...' },
    { title: 'New list with dates', description: 'New List with dates', content: '<div class="mceTmpl"><span class="cdate">cdate</span><br /><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>' }
  ],
  template_cdate_format: '[Date Created (CDATE): %m/%d/%Y : %H:%M:%S]',
  template_mdate_format: '[Date Modified (MDATE): %m/%d/%Y : %H:%M:%S]',
  height: 300,
  image_caption: true,
  quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
  noneditable_noneditable_class: 'mceNonEditable',
  toolbar_mode: 'sliding',
  contextmenu: 'link image imagetools table',
  skin: useDarkMode ? 'oxide-dark' : 'oxide',
  content_css: useDarkMode ? 'dark' : 'default',
  content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
 });
 </script>






<script>


var quiz_time=$('#quiz_time').val();


var timer2 = quiz_time;
var interval = setInterval(function() {
  var timer = timer2.split(':');
  //by parsing integer, I avoid all extra string processing
  var minutes = parseInt(timer[0], 10);
  var seconds = parseInt(timer[1], 10);
    var online = navigator.onLine;  
          if (!online) { 
            $('.countdown').html(minutes + ':' + seconds);
             timer2 = minutes + ':' + seconds;
            $('#quiz_time').val(timer2);
          } 
else{
  --seconds;
  minutes = (seconds < 0) ? --minutes : minutes;
  if (minutes < 0){ clearInterval(interval);
    $('#quizForm').submit();
  }
  seconds = (seconds < 0) ? 59 : seconds;
  seconds = (seconds < 10) ? '0' + seconds : seconds;
  //minutes = (minutes < 10) ?  minutes : minutes;
  $('.countdown').html(minutes + ':' + seconds);
  timer2 = minutes + ':' + seconds;
  $('#quiz_time').val(timer2);
}
}, 1000);

console.log(timer2);
</script>

<script>
//webkitURL is deprecated but nevertheless
URL = window.URL || window.webkitURL;

var gumStream; 						//stream from getUserMedia()
var rec; 							//Recorder.js object
var input; 							//MediaStreamAudioSourceNode we'll be recording

// shim for AudioContext when it's not avb.
var AudioContext = window.AudioContext || window.webkitAudioContext;
var audioContext //audio context to help us record


$('.record-button').on('click',function(e){
  e.preventDefault();
// get buttons of this record
  var quiz_id=$(this).data('quiz_id');
  var recordbtuclass='.recordbut_'+quiz_id;
  var pausebtuclass='.pausebut_'+quiz_id;
  var stopbtuclass='.stopbut_'+quiz_id;

  var constraints = { audio: true, video:false };

// other record disabled
  $('.record-button').attr("disabled", true);
  $('.pause-button').attr("disabled", true);
  $('.stop-button').attr("disabled", true);

// this record record disable and pause,stop not disable
  $(recordbtuclass).attr("disabled", true);
  $(pausebtuclass).attr("disabled", false);
  $(stopbtuclass).attr("disabled", false);

// div of this record button and pause,stop
var recording_btn_class='.recording_btn_'+quiz_id;
var stop_pause_btn_class='.stop_pause_btn_'+quiz_id;

// record btn display none ,and pause and stop block
$(recording_btn_class).css('display','none');
$(stop_pause_btn_class).css('display','contents');

  navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
		console.log("getUserMedia() success, stream created, initializing Recorder.js ...");
    audioContext = new AudioContext();

    gumStream = stream;

    input = audioContext.createMediaStreamSource(stream);

    rec = new Recorder(input,{numChannels:1})

    rec.record()

    console.log("Recording started");
$('#current_quiz_id').val(quiz_id);
  }).catch(function(err) {
      //enable the record button if getUserMedia() fails


      $(recordbtuclass).attr("disabled", false);
      $(pausebtuclass).attr("disabled", true);
      $(stopbtuclass).attr("disabled", true);

  });
});

$('.pause-button').on('click',function(e){
  e.preventDefault();
  var quiz_id=$(this).data('quiz_id');
  var pausebtuclass='pausebut_'+quiz_id;
  var pause_icon='.pause-icon_'+quiz_id;
  var resume_icon='.resume-icon_'+quiz_id;
  var record_msg='.record-msg_'+quiz_id;
  var resume_msg='.resume-msg_'+quiz_id;


  console.log("pauseButton clicked rec.recording=",rec.recording );
  if (rec.recording){
    //paused
    rec.stop();
    $(pause_icon).css('display','none');
    $(resume_icon).css('display','block');
    $(record_msg).css('display','none');
    $(resume_msg).css('display','block');

    // document.getElementsByClassName(pausebtuclass)[0].innerHTML = "Resume";
    // Now working

  }else{
    //resume
    rec.record();
    $(pause_icon).css('display','block');
    $(resume_icon).css('display','none');
    $(resume_msg).css('display','none');
    $(record_msg).css('display','flex');

    // document.getElementsByClassName(pausebtuclass)[0].innerHTML = "Pause";
  }
});

$('.stop-button').on('click',function(e){
  e.preventDefault();

  console.log("stopButton clicked");
  var quiz_id=$(this).data('quiz_id');
  var recordbtuclass='.recordbut_'+quiz_id;
  var pausebtuclass='.pausebut_'+quiz_id;
  var stopbtuclass='.stopbut_'+quiz_id;
  var audio_box_class='.audio-box_'+quiz_id;
  var record_box_class='.recordbox_'+quiz_id;
  var stop_pause_class='.stop_pause_btn_'+quiz_id;
  var saving_msg='.saving-msg_'+quiz_id;

    $(stop_pause_class).css('display','none');
    $(record_box_class).css('display','none');
    $(audio_box_class).css('display','block');
    $(saving_msg).css('display','block');

    console.log(stop_pause_class);
    console.log(record_box_class);
    console.log(audio_box_class);

	//disable the stop button, enable the record too allow for new recordings
//   $('.record-button').attr("disabled", false);
  
  
  
  
  
  
  
  
 //  $('.record_list').each(function(i, obj) {
 //    if ($(this).is(':empty')){
 //      var id = $(this).attr("id");
 //      var res = id.split("_");
 //      var qu_id = res[1];
 //      var recordbtu='.recordbut_'+qu_id;
 //      console.log(recordbtu);
 //      $(recordbtu).attr("disabled", false);
 //  }
 //  else{
 //    var id = $(this).attr("id");
 //    var res = id.split("_");
 //    var qu_id = res[1];
 //    var recordbtu='.recordbut_'+qu_id;
 //    $(recordbtu).attr("disabled", true);
 //  }
 // });
  // $('.pause-button').attr("disabled", true);
  // $('.stop-button').attr("disabled", true);
  //
  // $(recordbtuclass).attr("disabled", true);
  // $(pausebtuclass).attr("disabled", true);
  // $(stopbtuclass).attr("disabled", true);
  var pausebtuclass='pausebut_'+quiz_id;
	//reset button just in case the recording is stopped while paused
  // document.getElementsByClassName(pausebtuclass)[0].innerHTML = "Pause";
   // Now working

	// pauseButton.innerHTML="Pause";

	//tell the recorder to stop the recording
	rec.stop();

	//stop microphone access
	gumStream.getAudioTracks()[0].stop();

	//create the wav blob and pass it on to createDownloadLink
	rec.exportWAV(createDownloadLink);
});

function createDownloadLink(blob) {
  // src of audio
	var url = URL.createObjectURL(blob);
	// var au = document.createElement('audio');
	// var li = document.createElement('li');
	// var link = document.createElement('a');
  var quiz_id=  $('#current_quiz_id').val();
  console.log(quiz_id);
  var audio_box_class='.audio-box_'+quiz_id;
  var record_box_class='.recordbox_'+quiz_id;
  var record_btn='.recording_btn_'+quiz_id;

    var audio_class='.audio_'+quiz_id;
    var trash_class='.trash_'+quiz_id;
    var save_class='.save_'+quiz_id;

	//name of .wav file to use during upload and download (without extendion)
	var filename = new Date().toISOString();

	//add controls to the <audio> element
  $(audio_class).attr("src", url);

	// au.controls = true;
	// au.src = url;
  // au.setAttribute("class","uploaded_audio");
 // li.appendChild(au);



	//save to disk link
	// link.href = url;
	// link.download = filename+".wav";
	// link.innerHTML = "Save to disk";

	//add the new audio element to li
	// li.appendChild(au);

	//add the filename to the li
	// li.appendChild(document.createTextNode(filename+".wav "));

	//add the save to disk link to li
	// li.appendChild(link);

	//upload link
	// var upload = document.createElement('a');
	// upload.href="#";
  // upload.setAttribute("class","upload_record");
	// upload.innerHTML = "Save";

	$(save_class).on("click", function(e){
    e.preventDefault();
              $(save_class).prop('disabled', true);
              $('#staticBackdrop').modal({backdrop: 'static', keyboard: false}, 'show');

// $('#staticBackdrop').modal('show');
// ('#staticBackdrop').modal({
//       backdrop: 'static',
//       keyboard: false
// })
// $('#staticBackdrop').modal({backdrop:'static'});
		  var xhr=new XMLHttpRequest();
      // xhr.responseType = "json";

		  xhr.onload=function(e) {
        // upload.innerHTML = "Saving....";

		      if(this.readyState === 4) {
		          // console.log("Server returned: ",e.target.responseText);
              var response=xhr.responseText;
              var obj = JSON.parse(response);

              var record_file=obj.res;
              // var quiz_id=$("#recordButton").data("quiz_id");
              var record_file_id='#record_file_'+quiz_id;
              $(record_file_id).val(record_file);
              $('#staticBackdrop').modal('hide');
            $(save_class).html(" <i class='fas fa-check-circle'></i> Done") ;
              // upload.href="javascript:void(0)";


              // $('#record').data('link',record_file); //setter


var id=$('#stu_id').val();
// var quiz_id=$('#record_quiz_id').val();
console.log(quiz_id);
var record_quiz_id="#record_file_"+quiz_id;
        var answer=record_file;
        console.log(answer);
    $.ajax({
      url:"{{url('question/auto-save')}}",
      type: "get",
      data: {
          "id":id,
          "quiz_id":quiz_id,
          "answer":answer,
     
      },
      dataType : 'json',
      success: function(result){


console.log(result);
      }
      });


		      }
		  };
		  var fd=new FormData();
		  fd.append("audio_data",blob, filename);
      // fd.append('_token',"{{ csrf_token() }}");
		  xhr.open("post","{{url('quiz/record')}}",true);
		  xhr.send(fd);
  $('.record-button').attr("disabled", false);



	})
  // var delete_rec = document.createElement('a');
  // delete_rec.href = '#';
  // delete_rec.innerHTML = "<i class='fas fa-trash'></i>";
  // delete_rec.setAttribute("class","delete_record");
  //
  // delete_rec.setAttribute("id","delete_record");
  // delete_rec.setAttribute("data-quid",quiz_id);
  //
  //
  // li.appendChild(document.createTextNode (" "));//add a space in between
  // li.appendChild(delete_rec);
  //
	// li.appendChild(document.createTextNode (" "));//add a space in between
	// li.appendChild(upload);//add the upload link to li



	//add the li element to the ol
  // var recordingsList_q='recordingsList_'+quiz_id;
  // document.getElementById(recordingsList_q).appendChild(li);

	// recordingsList.appendChild(li);

  $(trash_class).on("click", function(e){
    e.preventDefault();

    // var quiz_id=  $('#current_quiz_id').val();
  var quiz_id=  $(this).data('quid');
console.log(quiz_id);
    var record_file_id='#record_file_'+quiz_id;
    var record_file=$(record_file_id).val();
    var recordingsList_quiz='#recordingsList_'+quiz_id;
    // $(recordingsList_quiz).empty();
    var audio_box_class='.audio-box_'+quiz_id;
      var record_box_class='.recordbox_'+quiz_id;
      var record_btn='.recording_btn_'+quiz_id;

    $(audio_box_class).css('display','none');
    $(record_btn).css('display','flex');
    $(record_box_class).css('display','flex');

    var recordbtuclass='.recordbut_'+quiz_id;
    var pausebtuclass='.pausebut_'+quiz_id;
    var stopbtuclass='.stopbut_'+quiz_id;
    $(save_class).html("Save") ;
    var saving_msg='.saving-msg_'+quiz_id;
    $(saving_msg).css('display','none');
    var audio_class='.audio_'+quiz_id;

  $(audio_class).attr("src", '');

  	//disable the stop button, enable the record too allow for new recordings
    // $(recordbtuclass).attr("disabled", false);
    // $(pausebtuclass).attr("disabled", true);
    // $(stopbtuclass).attr("disabled", true);

    if(record_file!=''){
      console.log(record_box_class);
      $.ajax({
      url:"{{url('delete/record-file')}}",
      type: "get",
      data: {
      record_file: record_file,
      _token: '{{csrf_token()}}'
      },
      dataType : 'json',
      success: function(result){
        $(record_file_id).val('');


console.log(result);
      }
      });
    }
  });

}

</script>


<!--auto_save_ques-->



    <script> 
  
//       // Function to check internet connection 
      function checkInternetConnection() { 
  // Detecting the internet connection 
          var check_net=$('#check_net').val();
      
      // Detecting the internet connection 
          var online = navigator.onLine;  
          if (!online) { 
  $("#message").empty();
            $("#message").css('display','none');
            $("#message_no").css('display','block');
            var m1="<p class='text-center'>No Internet Connection Found</p><p>Don't worry, your answers and your remaining time are saved. When the internet connection is restored, refresh the page and you'll be able to continue the test.</p></br>";
            var m2='<p>لا تقلق، سيتم حفظ إجاباتك والوقت المتبقي لديك. عند استعادة الاتصال بالإنترنت، قم بإعادة تحميل الصفحة وستتمكن من متابعة الاختبار.</p>'
             $("#message_no").html(m1+m2); 
                           $('#no_connectionmodal').modal({backdrop: 'static', keyboard: false}, 'show');

             $('#check_net').val('no_connect');
          } 
        else  { 
            if(check_net=="no_connect"){
  $("#message_no").empty();
            $("#message_no").css('display','none');
            $("#message").css('display','block');
            // Showing alert when connection is available 
            $("#message").html("Connection available!"); 
            $('#check_net').val('connect');
                          $('#no_connectionmodal').modal('hide');

             $("#message").fadeTo(2000, 500).slideUp(500, function() {
    $(".message").slideUp(500);
  });
          }
        }
      } 
  // Setting interval to 3 seconds 
      setInterval(checkInternetConnection, 1000); 
     
      function save_change_time() {
          var change_quiz_time=$('#quiz_time').val();
           var stu_id=$('#stu_id').val();
           
           $.ajax({
      url:"{{url('question/auto-save-change-time')}}",
      type: "get",
      data: {
          "stu_id":stu_id,
          "change_quiz_time":change_quiz_time,
          
     
      },
      dataType : 'json',
      success: function(result){


// console.log(result);
      }
      });
      }
   
      // Setting interval to 3 seconds 
      setInterval(save_change_time, 60000); 
      
      
      
    </script> 



<script>
 $('.answers_input').on("change", function(e){
    e.preventDefault();
 
      
    
    var id=$(this).data('stu_id');
        var quiz_id=$(this).data('quiz_id');
var ques_type=$(this).data('ques_type');
if(ques_type=='True Or False'){
    var answer=$(this).val();

}
if(ques_type=="Single Or Multi Choice"){
    var option_name='r_'+quiz_id+'[]';
    var answer =[];
    // var values = $("input[name='r_1[]']")
    //           .map(function(){return $(this).val();}).get();
    // var answer = $("input[name='"+option_name+"']")
    //           .map(function(){return $(this).val();}).get();
              
               $("input:checkbox[name='"+option_name+"']:checked").each(function() { 
                answer.push($(this).val()); 
            }); 

}
if(ques_type=="Free Answer"){
   var answer=$(this).val();
}
if(ques_type=="Fill In Blank"){
    
        var option_name='f_'+quiz_id+'[]';
    var answer = $("input[name='"+option_name+"']")
              .map(function(){return $(this).val();}).get();
}
if(ques_type=="Record"){
    var answer=$(this).val();
}
        $.ajax({
      url:"{{url('question/auto-save')}}",
      type: "get",
      data: {
          "id":id,
          "quiz_id":quiz_id,
          "answer":answer,
     
      },
      dataType : 'json',
      success: function(result){


console.log(result);
      }
      });
 });
</script>


  </div>
    </body>
</html>

