@extends('front.layouts.master')
@section('content')
<style>
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
  background-image: url("{{asset('public/quiz/q1.jpg')}}");
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
    font-size: 31px;
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
<div class="container mt-5">
    <div class="d-flex justify-content-center row">
        <div class="col-md-11 col-lg-11">
            <div class="col-md-12">
                <div class="question bg-white p-3 border-bottom">
                    <div class="d-flex flex-row  align-items-center mcq div_hquiz">
                        <h1 class="col-md-10 hquiz">English Placement Test</h1>
                        <div class="col-md-2 row">

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
                        @foreach($sections as $k=> $section)
                        <li  role="presentation"  class="nav-item ">
                          <a  @if($k == 0) class=" nav-link active" @endif class="nav-link " id="{{$section->title}}-tap" data-toggle="pill" href="#tab-{{$section->id}}" role="tab" aria-controls="#tab-{{ $section->id }}" >{{$section->title}}</a>
                        </li>
                        @endforeach
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
                              <div class="icheck-primary option-label">
                              <input class="option-input" type="radio" value="{{$quiz->id}}_{{$option}}" id="radio{{$quiz->id}}_{{$r}}" name="r_{{$quiz->id}}" >
                              <label  style="font-weight:500 !important;font-family:emoji !important;font-size: 17px !important;" for="radio{{$quiz->id}}_{{$r}}">
                                {{$option}}
                              </label>
                            </div>
                              @endforeach
                            </div>
                           </div>
                              <hr class="hr-quiz">

                          @endif

                        @if($quiz->question_type =='Single Or Multi Choice')
                        <?php  $options= json_decode($quiz->option_text); ?>
                        <div class="col-sm-12 col-md-12">
                        <div class="form-group clearfix">
                          @foreach($options as $c=>$option)
                            <div class="icheck-primary option-label">
                              <input type="checkbox" value="{{$quiz->id}}_{{$option}}" id="{{$quiz->id}}_{{$c}}" name="r_{{$quiz->id}}[]" >
                              <label style="font-weight:500 !important;font-family:emoji !important;font-size: 17px !important;" for="{{$quiz->id}}_{{$c}}">
                                {{$option}}
                              </label>
                            </div>
                            @endforeach
                        </div>
                       </div>
                          <hr class="hr-quiz">

                      @endif

                      @if($quiz->question_type =='Free Answer')
                      <textarea rows="4" class="form-control  " value=""  name="freeanswer_{{$quiz->id}}" id="free_answer_{{$quiz->id}}" ></textarea>
                                             <hr class="hr-quiz">

                      @endif
                      @if($quiz->question_type =='Fill In Blank')
                      <?php  $options= json_decode($quiz->option_text); ?>
                      @foreach($options as $f=>$option)
                    <div class="blank_option col-md-12 option-label">
                  <span class="count_blank">  {{$f+1}}. </span><input placeholder="_________" class="blank" type="text" value="" id="f_{{$quiz->id}}_{{$f}}" name="f_{{$quiz->id}}[]" >
                    </div>
                      @endforeach
                                               <hr class="hr-quiz">

                      @endif


                      @if($quiz->question_type =='Record')
                      <div id="controls">
                       <button class="record-button recordbut_{{$quiz->id}}"id="recordButton" data-quiz_id="{{$quiz->id}}">Record</button>
                       <button class="pause-button pausebut_{{$quiz->id}}" id="pauseButton" data-quiz_id="{{$quiz->id}}" disabled>Pause</button>
                       <button class="stop-button stopbut_{{$quiz->id}}" id="stopButton" data-quiz_id="{{$quiz->id}}" disabled>Stop</button>
                      </div>
                      <input type="hidden" value="" id="record_file_{{$quiz->id}}" name='record_{{$quiz->id}}'/>

                      <!-- <div id="formats">Format: start recording to see sample rate</div>
                      <p><strong>Recordings:</strong></p> -->
                      <ol class="record_list"id="recordingsList_{{$quiz->id}}"></ol>
                       @endif


                    @endif
                    @endforeach <!--End Quiz-->
                    <input type="hidden" value="" id="current_quiz_id" name='current_quiz_id'/>

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
@endsection
@push('timer')
<script>
var timer2 = "120:01";
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
@push('record')
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
  var quiz_id=$(this).data('quiz_id');
  var recordbtuclass='.recordbut_'+quiz_id;
  var pausebtuclass='.pausebut_'+quiz_id;
  var stopbtuclass='.stopbut_'+quiz_id;
  console.log("recordButton clicked");
  var constraints = { audio: true, video:false };


  $('.record-button').attr("disabled", true);
  $('.pause-button').attr("disabled", true);
  $('.stop-button').attr("disabled", true);


  $(recordbtuclass).attr("disabled", true);
  $(pausebtuclass).attr("disabled", false);
  $(stopbtuclass).attr("disabled", false);
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
  console.log("pauseButton clicked rec.recording=",rec.recording );
  if (rec.recording){
    //paused
    rec.stop();
    document.getElementsByClassName(pausebtuclass)[0].innerHTML = "Resume"; // Now working

  }else{
    //resume
    rec.record();
    document.getElementsByClassName(pausebtuclass)[0].innerHTML = "Pause"; // Now working
  }
});

$('.stop-button').on('click',function(e){
  console.log("stopButton clicked");
  var quiz_id=$(this).data('quiz_id');
  var recordbtuclass='.recordbut_'+quiz_id;
  var pausebtuclass='.pausebut_'+quiz_id;
  var stopbtuclass='.stopbut_'+quiz_id;
	//disable the stop button, enable the record too allow for new recordings

  $('.record_list').each(function(i, obj) {
    if ($(this).is(':empty')){
      var id = $(this).attr("id");
      var res = id.split("_");
      var qu_id = res[1];
      var recordbtu='.recordbut_'+qu_id;
      console.log(recordbtu);
      $(recordbtu).attr("disabled", false);
  }
  else{
    var id = $(this).attr("id");
    var res = id.split("_");
    var qu_id = res[1];
    var recordbtu='.recordbut_'+qu_id;
    console.log('fff');
    $(recordbtu).attr("disabled", true);
  }
 });
  $('.pause-button').attr("disabled", true);
  $('.stop-button').attr("disabled", true);

  $(recordbtuclass).attr("disabled", true);
  $(pausebtuclass).attr("disabled", true);
  $(stopbtuclass).attr("disabled", true);
  var pausebtuclass='pausebut_'+quiz_id;
	//reset button just in case the recording is stopped while paused
  document.getElementsByClassName(pausebtuclass)[0].innerHTML = "Pause"; // Now working

	// pauseButton.innerHTML="Pause";

	//tell the recorder to stop the recording
	rec.stop();

	//stop microphone access
	gumStream.getAudioTracks()[0].stop();

	//create the wav blob and pass it on to createDownloadLink
	rec.exportWAV(createDownloadLink);
});

function createDownloadLink(blob) {
	var url = URL.createObjectURL(blob);
	var au = document.createElement('audio');
	var li = document.createElement('li');
	var link = document.createElement('a');
  var quiz_id=  $('#current_quiz_id').val();

  // var quiz_id=$("#recordButton").data("quiz_id");
	//name of .wav file to use during upload and download (without extendion)
	var filename = new Date().toISOString();

	//add controls to the <audio> element
	au.controls = true;
	au.src = url;

	//save to disk link
	// link.href = url;
	// link.download = filename+".wav";
	// link.innerHTML = "Save to disk";

	//add the new audio element to li
	li.appendChild(au);

	//add the filename to the li
	// li.appendChild(document.createTextNode(filename+".wav "));

	//add the save to disk link to li
	li.appendChild(link);

	//upload link
	var upload = document.createElement('a');
	upload.href="#";
  upload.setAttribute("class","upload_record");
	upload.innerHTML = "Upload";

	upload.addEventListener("click", function(event){
		  var xhr=new XMLHttpRequest();
      // xhr.responseType = "json";

		  xhr.onload=function(e) {
		      if(this.readyState === 4) {
		          // console.log("Server returned: ",e.target.responseText);
              var response=xhr.responseText;
              var obj = JSON.parse(response);

              var record_file=obj.res;
              // var quiz_id=$("#recordButton").data("quiz_id");
              var record_file_id='#record_file_'+quiz_id;
              $(record_file_id).val(record_file);
              upload.innerHTML = "Uploaded";
              upload.href="javascript:void(0)";


              // $('#record').data('link',record_file); //setter


		      }
		  };
		  var fd=new FormData();
		  fd.append("audio_data",blob, filename);
      // fd.append('_token',"{{ csrf_token() }}");
		  xhr.open("post","{{url('quiz/record')}}",true);
		  xhr.send(fd);


	})


	li.appendChild(document.createTextNode (" "));//add a space in between
	li.appendChild(upload);//add the upload link to li

  var delete_rec = document.createElement('a');
  delete_rec.href = '#';
  delete_rec.innerHTML = "delete";
  delete_rec.setAttribute("class","delete_record");

  // delete_rec.setAttribute( "data-link", 'ssasaf' );
  delete_rec.setAttribute("id","delete_record");
  delete_rec.setAttribute("data-quid",quiz_id);

  // delete_rec.data( "qu_id",quiz_id );

  li.appendChild(document.createTextNode (" "));//add a space in between
  li.appendChild(delete_rec);

	//add the li element to the ol
  var recordingsList_q='recordingsList_'+quiz_id;
  document.getElementById(recordingsList_q).appendChild(li);

	// recordingsList.appendChild(li);

  delete_rec.addEventListener("click", function(event){
    // var quiz_id=  $('#current_quiz_id').val();
  var quiz_id=  delete_rec.getAttribute('data-quid');;

    var record_file_id='#record_file_'+quiz_id;
    var record_file=$(record_file_id).val();
    var recordingsList_quiz='#recordingsList_'+quiz_id;
    $(recordingsList_quiz).empty();

    var recordbtuclass='.recordbut_'+quiz_id;
    var pausebtuclass='.pausebut_'+quiz_id;
    var stopbtuclass='.stopbut_'+quiz_id;
    console.log(recordbtuclass);
  	//disable the stop button, enable the record too allow for new recordings
    $(recordbtuclass).attr("disabled", false);
    $(pausebtuclass).attr("disabled", true);
    $(stopbtuclass).attr("disabled", true);

    if(record_file!=''){
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
@endpush
