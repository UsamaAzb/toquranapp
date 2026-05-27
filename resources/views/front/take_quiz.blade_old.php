@extends('front.layouts.master')
@section('content')
<style>

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
  background-image: url("{{asset('quiz/2384075.jpg')}}");
  height: 100%;
  /* Center and scale the image nicely */
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
    background-color: #eee
}
[class*=icheck-]>input:first-child+label::before {
    width:15px !important;
        height:15px !important;

}
 [class*=icheck-]>input:first-child:checked+label::after {
     top:-3px !important;
          left:-3px !important;

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
.countdown{
  font-size: 25px;
    font-style: oblique;
    font-family: auto;
    color:darkred;
}
.left{
  font-size: 25px;
    font-style: oblique;
    font-family: auto;
    color:darkred;
}
</style>
<div class="container mt-5">
    <div class="d-flex justify-content-center row">
        <div class="col-md-10 col-lg-10">
            <div class="border">
                <div class="question bg-white p-3 border-bottom">
                    <div class="d-flex flex-row  align-items-center mcq">
                        <h4 class="col-md-9">English Quiz</h4>
                        <div class="col-md-3 row">

                        <div class="left">Time Left :</div>


                        <div class="countdown"></div>
</h4>
                    </div>
                </div>
                <div class="question bg-white p-3 border-bottom">
                  <div class="card card-primary card-tabs">
                    <!-- tabs -->
                    <div class="card-header p-0 pt-1" style="background-color:#75b1bca6 !important;">
                      <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                        @foreach($sections as $k=> $section)
                        <li  role="presentation"  class="nav-item ">
                          <a  @if($k == 0) class=" nav-link active" @endif class="nav-link " id="{{$section->title}}-tap" data-toggle="pill" href="#tab-{{$section->id}}" role="tab" aria-controls="#tab-{{ $section->id }}" >{{$section->title}}</a>
                        </li>
                        @endforeach
                      </ul>
                    </div>

                    <!-- body -->
                    <form id="quizForm" class="col-sm-12 col-md-12" action="{{route('front.quiz.store')}}" method="post" enctype="multipart/form-data" data-parsley-validate>
                      @csrf
                    <div class="card-body">
                      <input type="hidden" name="id" value="{{$id}}"/>
                      <div class="tab-content" id="custom-tabs-one-tabContent">
                        @foreach($sections as $ke=>$sec)

                        <!-- sec info -->
                        <div @if($ke == 0) class="tab-pane active" @else class="tab-pane" @endif id="tab-{{$sec->id}}" role="tabpanel"  >
                          {!!$sec->sec_desc !!}
                          @if($sec->audio)
                          <audio controls>
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
                          <?php  $options= json_decode($quiz->option_text); ?>
                          <div class="col-sm-12 col-md-12">
                            <div class="form-group clearfix">
                              @foreach($options as $r=>$option)
                              <div class="icheck-primary ">
                              <input type="radio" value="{{$quiz->id}}_{{$option}}" id="radio{{$quiz->id}}_{{$r}}" name="r_{{$quiz->id}}" >
                              <label  style="font-weight:500 !mportant;font-family:emoji !important;font-size: 19px !important;" for="radio{{$quiz->id}}_{{$r}}">
                                {{$option}}
                              </label>
                            </div>
                              @endforeach
                            </div>
                           </div>
                          @endif

                        @if($quiz->question_type =='Single Or Multi Choice')
                        <?php  $options= json_decode($quiz->option_text); ?>
                        <div class="col-sm-12 col-md-12">
                        <div class="form-group clearfix">
                          @foreach($options as $c=>$option)
                            <div class="icheck-primary ">
                              <input type="checkbox" value="{{$quiz->id}}_{{$option}}" id="{{$quiz->id}}_{{$c}}" name="r_{{$quiz->id}}[]" >
                              <label style="font-weight:500 !important;font-family:emoji !important;font-size: 19px !important;" for="{{$quiz->id}}_{{$c}}">
                                {{$option}}
                              </label>
                            </div>
                            @endforeach
                        </div>
                       </div>
                      @endif

                      @if($quiz->question_type =='Free Answer')
                      <textarea rows="4" class="form-control  " value=""  name="freeanswer_{{$quiz->id}}" id="free_answer_{{$quiz->id}}" ></textarea>
                      @endif

                    @endif
                    @endforeach <!--End Quiz-->

                      </div>
                    @endforeach <!--End sec info-->
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
                        &nbsp;previous
                      </button>
                      <button class="btn btn-primary border-success align-items-center btn-success nexttab"  type="button">
                        Next<i class="fa fa-angle-right ml-2"></i>
                      </button>
                    </div>

                    <!-- /.card -->
                  </div>




                  <footer class="main-footer" style="margin-left:0px !important;">
                      <strong>Copyright &copy; 2021 <a href="">OA-IntSchool</a>.</strong>
                      All rights reserved.
                      <div class="float-right d-none d-sm-inline-block">
                        <b>Version</b> 3.0.1
                      </div>
                    </footer>







                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('timer')
<script>
var timer2 = "15:01";
var interval = setInterval(function() {
  var timer = timer2.split(':');
  //by parsing integer, I avoid all extra string processing
  var minutes = parseInt(timer[0], 10);
  var seconds = parseInt(timer[1], 10);
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
}, 1000);
</script>
@endpush
