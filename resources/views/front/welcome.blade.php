<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
<link rel="shortcut icon" href="{{asset('images/favicon.png')}}">

  <title>Welcome</title>

  <!-- Bootstrap core CSS -->
  <link href="{{asset('assets/front/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">

  <!-- Custom fonts for this template -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:200,200i,300,300i,400,400i,600,600i,700,700i,900,900i" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Merriweather:300,300i,400,400i,700,700i,900,900i" rel="stylesheet">
  <link href="{{asset('assets/front/vendor/fontawesome-free/css/all.min.css')}}" rel="stylesheet" type="text/css">

  <!-- Custom styles for this template -->
  <link href="{{asset('assets/front/css/coming-soon.min.css')}}" rel="stylesheet">
<style>
.quiz{
  color: #e59338;
    font-family: 'Merriweather';
}
.sumbit:hover, .sumbit:focus, .sumbit:active
{
-webkit-box-shadow: 15px 15px 20px rgba(0,0, 0, 0.4);
-moz-box-shadow: 15px 15px 20px rgba(0,0, 0, 0.4);
box-shadow: 15px 15px 20px rgba(0,0, 0, 0.4);
-webkit-transform: scale(1.025);
-moz-transform: scale(1.025);
transform: scale(1.025);
}
.masthead-content{
    padding-left: 0px !important;
    padding-right: 5rem !important;;
}
.masthead-content h1 {
        font-size: 2.5rem !important;
}
.masthead{
    /*width: 42.5rem;*/

}
.sumbit{
    border-top-right-radius: 0px !important;
    margin-left: 210px !important;
        font-weight: 500!important;

}
.bg-img
{
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 100%;

    background-size: cover;
}
.youtube {
    background-color: #000;
    margin-bottom: 30px;
    position: relative;
    padding-top: 56.25%;
    overflow: hidden;
    cursor: pointer;
}
.youtube-iframe{
     position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    left: 0;


}
</style>
</head>

<body>

  <div class="overlay"></div>
  <img class="bg-img" src="{{asset('images/welcome.webp')}}" >
  <!--<video playsinline="playsinline" autoplay="autoplay" muted="muted" loop="loop">-->
  <!--  <source src="{{asset('assets/front/mp4/bg.mp4')}}" type="video/mp4">-->
  <!--</video>-->

  <div class="masthead">
    <div class="masthead-bg"></div>
    <div class="container h-100">
      <div class="row h-100">
        <div class="col-12 my-auto">
          <div class="masthead-content text-white py-5 py-md-0">
            <h1 class="mb-3">Welcome {{$name}}!</h1>
             @if($take_quiz==1)

                <p class="mb-5 quiz">
              You have already taken the test. Once the test has been thoroughly reviewed and assessed, we will promptly notify you via email with the placement results.
In the meantime, if you have any questions or concerns, please don't hesitate to reach out to us..
              </p>

              <div class="input-group input-group-newsletter">
                      <div class="input-group-append">
   <button class="btn btn-secondary sumbit video_block"  type="button"  style ="color:white;"><a style ="color:white;" href="{{url('en/contact')}}">contact us</a> </button>
                      </div>
                    </div>
              @else

              @if($stu->course_id==2)
            <p >This is an English placement test which will determine your English level.
              <strong style="display: block;">Instructions:</strong>
                        <ul>
                            <li>
                               The test is one hour and a half long.
                            </li>
                             <li>
                                There are many types of questions: multiple choice, fill in the gaps, writing, and listening.
                            </li>
                            <li>
                                 You can go back to the questions any time you want.
                                 </li>

                            <li>
                              In the listening questions, you can listen to the records 3 times maximum.
                            </li>

                            <li>
                            In the paragraph completing questions, insert your answer below the paragraph, not inside it.
                            </li>
                           <li>
                               In the multiple choice questions, choose one answer only.
                           </li>
                           <li>
                             When time is up, the test will end and your answers will be saved.
                            </li>
                             <li>
                             You can take this test one time only.
                        </li>
                        <li>
                        Before you start the test, please watch the instructions video.
                        </li>
                        </ul>
                         Good Luck.
                    </p>

                    <div class="input-group input-group-newsletter">
                      <div class="input-group-append">
  <button class="btn btn-secondary sumbit video_block"  type="button" id="submit-button"data-toggle="modal" data-target="#exampleModal" style ="color:white;">Instructions Video </button>
                      </div>
                    </div>

                    @endif

                    <!--kids G1 G2 G3 G4 G5 -->
            @if(($stu->course_id==1) && ($stu->grade_id>=8))
          <p >This is an English placement test which will determine your English level.
              <strong style="display: block;">Instructions:</strong>
                        <ul>
                            <li>
                               The test is an hour long.
                            </li>
                             <li>
There are many types of questions: multiple choice, fill in the gaps, writing, and listening.
</li>
                            <li>
                                 You can go back to the questions any time you want, so leave any question you can't answer and come back to it later, in order not to waste your time.
                                 </li>

                            <li>
                              In the listening questions, you can listen to the records 3 times maximum.
                            </li>

                            <li>
                            In the paragraph completing questions, insert your answer below the paragraph, not inside it.
                            </li>
                           <li>
                               In the multiple choice questions, choose one answer only.
                           </li>
                           <li>In the speaking question, click on the recording button, allow microphone access, and start recording your answer. Don't worry if you make mistakes while speaking; just go on until you finish.  </li> 
                           
                           <li>Your parents can only help you understand the questions if you have any difficulties, but they can't give you any answers.</li>
                           <li>
When time is up, the test will end and your answers will be automatically saved.</li>
                         
                             <li>
                             You can take this test one time only.
                        </li>
                        </ul>
                         Good Luck.
                    </p>
        <div class="input-group input-group-newsletter">
          <div class="input-group-append">
        <button class="btn btn-secondary sumbit "  type="button" id="submit-button"style ="color:white;">        <a type="button" style ="color:white;" class="" href="{{route('front.quiz.start',$id)}}">Start Test</a>
 </button>
          </div>
        </div>



            @endif

            <!--kids G6 G7 -->
        @if(($stu->course_id==1) && (($stu->grade_id==1) || ($stu->grade_id==2) ))
        <p >This is an English placement test which will determine your English level.
              <strong style="display: block;">Instructions:</strong>
                        <ul>
                            <li>
                               The test is an hour long.
                            </li>
                             <li>
There are many types of questions: multiple choice, fill in the gaps, writing, and listening.
</li>
                            <li>
                                 You can go back to the questions any time you want, so leave any question you can't answer and come back to it later, in order not to waste your time.
                                 </li>

                            <li>
                              In the listening questions, you can listen to the records 3 times maximum.
                            </li>

                            <li>
                            In the paragraph completing questions, insert your answer below the paragraph, not inside it.
                            </li>
                           <li>
                               In the multiple choice questions, choose one answer only.
                           </li>
                           <li>In the speaking question, click on the recording button, allow microphone access, and start recording your answer. Don't worry if you make mistakes while speaking; just go on until you finish.  </li> 
                           
                           <li>Your parents can only help you understand the questions if you have any difficulties, but they can't give you any answers.</li>
                           <li>
When time is up, the test will end and your answers will be automatically saved.</li>
                         
                             <li>
                             You can take this test one time only.
                        </li>
                        </ul>
                         Good Luck.
                    </p>
 <div class="input-group input-group-newsletter">
          <div class="input-group-append">
        <button class="btn btn-secondary sumbit "  type="button" id="submit-button"style ="color:white;">        <a type="button" style ="color:white;" class="" href="{{route('front.quiz.start',$id)}}">Start Test</a>
 </button>
          </div>
        </div>
        @endif



        <!--int G1 G2 G3 G4 G5 -->
  @if(($stu->course_id==3) && ($stu->grade_id>=8))
  <p >This is an English placement test which will determine your English level.
              <strong style="display: block;">Instructions:</strong>
                        <ul>
                            <li>
                               The test is an hour long.
                            </li>
                             <li>
There are many types of questions: multiple choice, fill in the gaps, writing, and listening.
</li>
                            <li>
                                 You can go back to the questions any time you want, so leave any question you can't answer and come back to it later, in order not to waste your time.
                                 </li>

                            <li>
                              In the listening questions, you can listen to the records 3 times maximum.
                            </li>

                            <li>
                            In the paragraph completing questions, insert your answer below the paragraph, not inside it.
                            </li>
                           <li>
                               In the multiple choice questions, choose one answer only.
                           </li>
                           <li>In the speaking question, click on the recording button, allow microphone access, and start recording your answer. Don't worry if you make mistakes while speaking; just go on until you finish.  </li> 
                           
                           <li>Your parents can only help you understand the questions if you have any difficulties, but they can't give you any answers.</li>
                           <li>
When time is up, the test will end and your answers will be automatically saved.</li>
                         
                             <li>
                             You can take this test one time only.
                        </li>
                        </ul>
                         Good Luck.
                    </p>
                     <div class="input-group input-group-newsletter">
          <div class="input-group-append">
        <button class="btn btn-secondary sumbit "  type="button" id="submit-button"style ="color:white;">        <a type="button" style ="color:white;" class="" href="{{route('front.quiz.start',$id)}}">Start Test</a>
 </button>
          </div>
        </div>

  @endif

  <!--int G6 G7 8 9 10 11 12-->
  @if(($stu->course_id==3) && ($stu->grade_id<=7)  )
  <p >This is an English placement test which will determine your English level.
              <strong style="display: block;">Instructions:</strong>
                        <ul>
                            <li>
                               The test is an hour long.
                            </li>
                             <li>
There are many types of questions: multiple choice, fill in the gaps, writing, and listening.
</li>
                            <li>
                                 You can go back to the questions any time you want, so leave any question you can't answer and come back to it later, in order not to waste your time.
                                 </li>

                            <li>
                              In the listening questions, you can listen to the records 3 times maximum.
                            </li>

                            <li>
                            In the paragraph completing questions, insert your answer below the paragraph, not inside it.
                            </li>
                           <li>
                               In the multiple choice questions, choose one answer only.
                           </li>
                           <li>In the speaking question, click on the recording button, allow microphone access, and start recording your answer. Don't worry if you make mistakes while speaking; just go on until you finish.  </li> 
                           
                           <li>Your parents can only help you understand the questions if you have any difficulties, but they can't give you any answers.</li>
                           <li>
When time is up, the test will end and your answers will be automatically saved.</li>
                         
                             <li>
                             You can take this test one time only.
                        </li>
                        </ul>
                         Good Luck.
                    </p>
                     <div class="input-group input-group-newsletter">
          <div class="input-group-append">
        <button class="btn btn-secondary sumbit "  type="button" id="submit-button"style ="color:white;">        <a type="button" style ="color:white;" class="" href="{{route('front.quiz.start',$id)}}">Start Test</a>
 </button>
          </div>
        </div>

  @endif

             @endif



          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- <div class="social-icons">
    <ul class="list-unstyled text-center mb-0">
      <li class="list-unstyled-item">
        <a href="#">
          <i class="fab fa-twitter"></i>
        </a>
      </li>
      <li class="list-unstyled-item">
        <a href="#">
          <i class="fab fa-facebook-f"></i>
        </a>
      </li>
      <li class="list-unstyled-item">
        <a href="#">
          <i class="fab fa-instagram"></i>
        </a>
      </li>
    </ul>
  </div> -->
<div class="modal fade" id="exampleModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Instruction</h5>
        <button type="button" class="close close_video" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <div class="youtube" >

	</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary close_video" data-dismiss="modal">Close</button>
        <a type="button" style ="color:white;" class="btn btn-primary" href="{{route('front.quiz.start',$id)}}">Start Test</a>
      </div>
    </div>
  </div>
</div>




  <!-- Bootstrap core JavaScript -->
  <script src="{{asset('assets/front/vendor/jquery/jquery.min.js')}}"></script>
  <script src="{{asset('assets/front/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

  <!-- Custom scripts for this template -->
  <script src="{{asset('assets/front/js/coming-soon.min.js')}}"></script>
<script>
$(".video_block").on('click', function (e) {
				  var video_embed = 'https://www.youtube.com/embed/ymrYVZGe93g?rel=0&showinfo=0&autoplay=1';

				  $( ".youtube" ).empty();
             	var youtube = $( ".youtube" );
					var iframe = document.createElement( "iframe" );
							iframe.setAttribute( "frameborder", "0" );
							iframe.setAttribute( "allowfullscreen", "" );
							iframe.setAttribute( "allow", "autoplay" );

							iframe.setAttribute( "src", video_embed );
                            $(iframe).addClass("youtube-iframe");


			            	youtube.innerHTML = "";
							youtube.append( iframe );


});
    $(".close_video").click(function() {
$( ".youtube" ).empty();
    // $('.youtube-iframe').each(function(index) {
    //     $(this).attr('src').replace("&autoplay=1", "&autoplay=0");
    //     $(this).attr('src', $(this).attr('src'));
    //     return false;
    //   });

    });



</script>
</body>

</html>
