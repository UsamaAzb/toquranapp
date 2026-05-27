<!DOCTYPE html>
<html>

<head>
    <link rel="shortcut icon" href="https://app.toquran.org/assets/img/favicon/favicon.ico">

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>{{ $title ?? 'Listen & Read Chapter' }} | Week 14</title>
    <meta name="description" content="Read or listen to this Listen & Read chapter.">
	<style type="text/css">
		html {
			overflow: auto;
		}
		
		html,
		body,
		
		iframe {
			margin: 0px;
			padding: 0px;
			height: 100%;
			border: none;
		}
		
		iframe {
			display: block;
			width: 100%;
			border: none;
			overflow-y: auto;
			overflow-x: hidden;
					    position: relative;

		}
		.go_back{
		    position: absolute;
    z-index: 1000;
    top: 52px;
    left: 5%;

		}
		.back_but{
		    position: absolute;
    z-index: 1000;
    top: 2px;
    left: 1%;
		    color: #ffffff!important;
    border-width: 1px!important;
    /*border-color: #007edb;*/
    border-radius: 4px;
    font-size: 13px;
    font-family: 'Poppins',Helvetica,Arial,Lucida,sans-serif!important;
    font-weight: 300!important;
    padding-right: 13px;
    padding-left: 13px;
    /*background-color: #007edb;*/
    padding-top: 8px!important;
    padding-bottom: 8px!important;
    transition: all 300ms ease 0ms;
    -webkit-transition-property: all!important;
    transition-property: all!important;
    /*border: 1px solid #007edb;*/
    text-decoration: none;
		}
		.go_back a {
   border: 1px solid #1600a9;
    border-radius: 22px;
    padding-left: 20px;
    padding-right: 20px;
    padding-top: 8px;
    padding-bottom: 8px;
    background-color: rgb(22 0 169);
    color: #fff;
    box-shadow: 4px 5px 3px 3px #1600a952;
		    
		}
		
    .audio-div{
    /*   position: fixed;*/
    /*cursor: pointer;*/
    /*top: 91px;*/
    /*z-index: 2000;*/
    /*left: 101px;*/
    position: fixed;
    cursor: pointer;
    top: inherit;
    z-index: 2000;
    left: 1px;
    bottom: -4px;

    }
    .audio-div:hover{
       transform: scale(1.2);
    }
    .audio{
   /*display: block;*/
   /* position: absolute;*/
   /* top: 100px;*/
   /* left: 0;*/
    /*z-index: 2000;*/
    /*width: 100%;*/
    /*height: 100%;*/
  

    }
       .play{
  /*width: 220px;*/
  /*  height: 40px;*/
  /*  transform: rotate(90deg);*/
  /*  z-index: 2000;*/
  /*  position: fixed;*/
  /*  top: 230px;*/
  /*  left: 16px;*/
  
  width: 75%;
    height: 40px;
    /* transform: rotate(90deg); */
    z-index: 2000;
    position: fixed;
    top: initial;
    left: 51px;
    bottom: 6px;
    

    }
    
    .audio-player-div{
    /*      position: absolute;*/
    /*top: 7px;*/
    /*left: 66px;*/
    /*z-index: 2000;*/
    /*width: 50%;*/
    /*height: 50%;*/
    }
audio::-webkit-media-controls-play-button {
   display:none !important;
}

audio::-webkit-media-controls-panel {
  background-color: rgb(255, 97, 116)!important;
  color:#fff !important;
  /*height:50%!important;*/

}
@media only screen and (max-width:545px){
       .play{
 width: 76%;
    height: 48px;
    z-index: 2000;
    position: fixed;
    bottom: 0px;
    left: 60px;
    transform: rotate(360deg);
    top: initial;


    }
      .audio-div{
      position: fixed;
    cursor: pointer;
    bottom: -5px;
    z-index: 2000;
    left: 10px;
    top: initial;


    }
}
@media only screen and (min-width:546px) and (max-width:1100px){
       .play{
 width: 76%;
    height: 48px;
    z-index: 2000;
    position: fixed;
    bottom: 0px;
    left: 76px;
    transform: rotate(360deg);
    top: initial;


    }
      .audio-div{
      position: fixed;
    cursor: pointer;
    bottom: -5px;
    z-index: 2000;
    left: 26px;
    top: initial;


    }
}

/*audio::-webkit-media-controls-mute-button {*/
/*   display:none !important;*/

/*}*/
/*audio::-webkit-media-controls-volume-slider,audio::-webkit-media-controls-volume-slider-container*/

/* {*/
/*  background-color: #fd4085!important;*/

/*}*/

	</style>
</head>

<body>
    
    <?php $story_id=$story->id; ?>
    @php
        $resourceReturnTarget = app(\App\Services\Library\ResourceReturnTargetResolver::class)
            ->resolveFromRequest(request(), url('reading/listen-read?d='.$story_id));
    @endphp
    
    @if((($chapter->audio!="") || ($chapter->audio!=Null)) && (($chapter->iframe_link != Null)  || ($chapter->iframe_link!="")) )
   <!--<a class="back_but" href="{{url('reading/listen-read?d='.$story_id)}}"><svg width="40px" id="_x31_08" enable-background="new 0 0 512 512" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><linearGradient id="lg1"><stop offset="0" stop-opacity=".25"/><stop offset="1" stop-opacity="0"/></linearGradient><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="271.575" x2="323.875" xlink:href="#lg1" y1="172.725" y2="225.025"/><linearGradient id="SVGID_2_" gradientUnits="userSpaceOnUse" x1="201.6" x2="440.58" xlink:href="#lg1" y1="186" y2="424.98"/><path id="Background" d="m256 6c138.1 0 250 111.9 250 250 0 138.1-111.9 250-250 250-138.1 0-250-111.9-250-250 0-138.1 111.9-250 250-250z" fill="#2196f3"/><g id="Shadow"><path d="m283.8 169.2c0-2.5-.8-4.7-2.2-6.5l52.3 52.3c-14.4-5.5-31.5-8.5-50.1-10z" fill="url(#SVGID_1_)"/><path d="m485.2 356c-12.6 28.8-30.5 54.8-52.4 76.8-16.5 16.5-35.3 30.7-55.9 42.1l-165.9-165.9c6.6 5.7 17.2 1.1 17.2-7.9 0-2.1-.5-4.3-1.4-6.2s-2.2-3.7-3.8-5.1l-41.9-36.2c-5.4-4.6-8.4-11.3-8.4-18.4s3.1-13.8 8.4-18.4l41.9-36.2c3.3-2.8 5.2-7 5.2-11.3 0-2.9-1.1-5.3-2.8-7.1l18.6 18.6-53.8 46.5c-4.9 4.2-4.9 11.6-.1 15.7l76.4 66c6.7 5.8 17.2 1.1 17.2-7.9v-36.6c47.2 5.4 65.6 25.6 48.9 79-2.2 7 6.3 12.4 12.2 8.1 19-13.8 36.2-40.2 36.2-66.8 0-20.9-5.5-36.6-15.4-48.4z" fill="url(#SVGID_2_)"/></g><g id="Icon" fill="#fff"><path id="Arrow02_5_" d="m190.2 227.3 76.4-66c6.7-5.7 17.2-1.1 17.2 7.9v35.9c56 4.4 97.2 22.7 97.2 79.7 0 26.6-17.2 53-36.2 66.8-5.9 4.3-14.4-1.1-12.2-8.1 16.7-53.4-1.7-73.6-48.9-79v36.6c0 9-10.5 13.7-17.2 7.9l-76.4-66c-4.8-4.1-4.8-11.5.1-15.7z"/><path id="Arrow01_5_" d="m134.6 243 76.4 66c6.6 5.7 17.2 1.1 17.2-7.9 0-4.3-1.9-8.5-5.2-11.3l-41.9-36.2c-5.4-4.6-8.4-11.3-8.4-18.4s3.1-13.8 8.4-18.4l41.9-36.2c3.3-2.8 5.2-7 5.2-11.3 0-9-10.5-13.7-17.2-7.9l-76.4 66c-4.8 4.1-4.8 11.5 0 15.6z"/></g></svg></a>-->
   
   
   <a class="back_but" href="{{url('reading/listen-read?d='.$story_id)}}" data-return-url="{{ $resourceReturnTarget }}" onclick="return window.w14ResourceBack(event, this.href);"><svg width="40px" id="_x31_08" enable-background="new 0 0 512 512" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><linearGradient id="lg1"><stop offset="0" stop-opacity=".25"/><stop offset="1" stop-opacity="0"/></linearGradient><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="271.575" x2="323.875" xlink:href="#lg1" y1="172.725" y2="225.025"/><linearGradient id="SVGID_2_" gradientUnits="userSpaceOnUse" x1="201.6" x2="440.58" xlink:href="#lg1" y1="186" y2="424.98"/><path id="Background" d="m256 6c138.1 0 250 111.9 250 250 0 138.1-111.9 250-250 250-138.1 0-250-111.9-250-250 0-138.1 111.9-250 250-250z" fill="#2196f3"/><g id="Shadow"><path d="m283.8 169.2c0-2.5-.8-4.7-2.2-6.5l52.3 52.3c-14.4-5.5-31.5-8.5-50.1-10z" fill="url(#SVGID_1_)"/><path d="m485.2 356c-12.6 28.8-30.5 54.8-52.4 76.8-16.5 16.5-35.3 30.7-55.9 42.1l-165.9-165.9c6.6 5.7 17.2 1.1 17.2-7.9 0-2.1-.5-4.3-1.4-6.2s-2.2-3.7-3.8-5.1l-41.9-36.2c-5.4-4.6-8.4-11.3-8.4-18.4s3.1-13.8 8.4-18.4l41.9-36.2c3.3-2.8 5.2-7 5.2-11.3 0-2.9-1.1-5.3-2.8-7.1l18.6 18.6-53.8 46.5c-4.9 4.2-4.9 11.6-.1 15.7l76.4 66c6.7 5.8 17.2 1.1 17.2-7.9v-36.6c47.2 5.4 65.6 25.6 48.9 79-2.2 7 6.3 12.4 12.2 8.1 19-13.8 36.2-40.2 36.2-66.8 0-20.9-5.5-36.6-15.4-48.4z" fill="url(#SVGID_2_)"/></g><g id="Icon" fill="#fff"><path id="Arrow02_5_" d="m190.2 227.3 76.4-66c6.7-5.7 17.2-1.1 17.2 7.9v35.9c56 4.4 97.2 22.7 97.2 79.7 0 26.6-17.2 53-36.2 66.8-5.9 4.3-14.4-1.1-12.2-8.1 16.7-53.4-1.7-73.6-48.9-79v36.6c0 9-10.5 13.7-17.2 7.9l-76.4-66c-4.8-4.1-4.8-11.5.1-15.7z"/><path id="Arrow01_5_" d="m134.6 243 76.4 66c6.6 5.7 17.2 1.1 17.2-7.9 0-4.3-1.9-8.5-5.2-11.3l-41.9-36.2c-5.4-4.6-8.4-11.3-8.4-18.4s3.1-13.8 8.4-18.4l41.9-36.2c3.3-2.8 5.2-7 5.2-11.3 0-9-10.5-13.7-17.2-7.9l-76.4 66c-4.8 4.1-4.8 11.5 0 15.6z"/></g></svg></a>
   
   
   
<div class="audio-player-div">
<div class="audio-div">
        <svg class='player_audio' width="50px" enable-background="new 0 0 32 32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g id="Layer_1"><path d="m16 1c-8.28 0-15 6.72-15 15s6.72 15 15 15 15-6.72 15-15-6.72-15-15-15zm5.75 15.92-7.65 5.1c-.63.42-1.48-.03-1.48-.79v-10.46c0-.76.85-1.21 1.48-.79l7.65 5.1c.65.44.65 1.4 0 1.84z" fill="#ff6174"/></g></svg>
        </div>
        <div class="audio">
           <audio class="mt-4 col-12 play" controls  controlsList="nodownload noplaybackrate">
  <source  src="{{asset($chapter->audio)}}" type="audio/ogg">
  <source src="{{asset($chapter->audio)}}" type="audio/mpeg">
Your browser does not support the audio element.
</audio>
</div>
</div>

     	<iframe src="{{$chapter->iframe_link}}"
			frameborder="0"
			marginheight="0"
			marginwidth="0"
			width="100%"
			height="100%"
			scrolling="auto">
        </iframe>
   
 
    
    
    @elseif((($chapter->iframe_link != Null)  || ($chapter->iframe_link!=""))  && (($chapter->audio=="") || ($chapter->audio==Null)) )
        <!--<a class="back_but" href="{{url('reading/listen-read?d='.$story_id)}}"><svg width="40px" id="_x31_08" enable-background="new 0 0 512 512" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><linearGradient id="lg1"><stop offset="0" stop-opacity=".25"/><stop offset="1" stop-opacity="0"/></linearGradient><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="271.575" x2="323.875" xlink:href="#lg1" y1="172.725" y2="225.025"/><linearGradient id="SVGID_2_" gradientUnits="userSpaceOnUse" x1="201.6" x2="440.58" xlink:href="#lg1" y1="186" y2="424.98"/><path id="Background" d="m256 6c138.1 0 250 111.9 250 250 0 138.1-111.9 250-250 250-138.1 0-250-111.9-250-250 0-138.1 111.9-250 250-250z" fill="#2196f3"/><g id="Shadow"><path d="m283.8 169.2c0-2.5-.8-4.7-2.2-6.5l52.3 52.3c-14.4-5.5-31.5-8.5-50.1-10z" fill="url(#SVGID_1_)"/><path d="m485.2 356c-12.6 28.8-30.5 54.8-52.4 76.8-16.5 16.5-35.3 30.7-55.9 42.1l-165.9-165.9c6.6 5.7 17.2 1.1 17.2-7.9 0-2.1-.5-4.3-1.4-6.2s-2.2-3.7-3.8-5.1l-41.9-36.2c-5.4-4.6-8.4-11.3-8.4-18.4s3.1-13.8 8.4-18.4l41.9-36.2c3.3-2.8 5.2-7 5.2-11.3 0-2.9-1.1-5.3-2.8-7.1l18.6 18.6-53.8 46.5c-4.9 4.2-4.9 11.6-.1 15.7l76.4 66c6.7 5.8 17.2 1.1 17.2-7.9v-36.6c47.2 5.4 65.6 25.6 48.9 79-2.2 7 6.3 12.4 12.2 8.1 19-13.8 36.2-40.2 36.2-66.8 0-20.9-5.5-36.6-15.4-48.4z" fill="url(#SVGID_2_)"/></g><g id="Icon" fill="#fff"><path id="Arrow02_5_" d="m190.2 227.3 76.4-66c6.7-5.7 17.2-1.1 17.2 7.9v35.9c56 4.4 97.2 22.7 97.2 79.7 0 26.6-17.2 53-36.2 66.8-5.9 4.3-14.4-1.1-12.2-8.1 16.7-53.4-1.7-73.6-48.9-79v36.6c0 9-10.5 13.7-17.2 7.9l-76.4-66c-4.8-4.1-4.8-11.5.1-15.7z"/><path id="Arrow01_5_" d="m134.6 243 76.4 66c6.6 5.7 17.2 1.1 17.2-7.9 0-4.3-1.9-8.5-5.2-11.3l-41.9-36.2c-5.4-4.6-8.4-11.3-8.4-18.4s3.1-13.8 8.4-18.4l41.9-36.2c3.3-2.8 5.2-7 5.2-11.3 0-9-10.5-13.7-17.2-7.9l-76.4 66c-4.8 4.1-4.8 11.5 0 15.6z"/></g></svg></a>-->
        
                <a class="back_but" href="{{url('reading/listen-read?d='.$story_id)}}" data-return-url="{{ $resourceReturnTarget }}" onclick="return window.w14ResourceBack(event, this.href);"><svg width="40px" id="_x31_08" enable-background="new 0 0 512 512" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><linearGradient id="lg1"><stop offset="0" stop-opacity=".25"/><stop offset="1" stop-opacity="0"/></linearGradient><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="271.575" x2="323.875" xlink:href="#lg1" y1="172.725" y2="225.025"/><linearGradient id="SVGID_2_" gradientUnits="userSpaceOnUse" x1="201.6" x2="440.58" xlink:href="#lg1" y1="186" y2="424.98"/><path id="Background" d="m256 6c138.1 0 250 111.9 250 250 0 138.1-111.9 250-250 250-138.1 0-250-111.9-250-250 0-138.1 111.9-250 250-250z" fill="#2196f3"/><g id="Shadow"><path d="m283.8 169.2c0-2.5-.8-4.7-2.2-6.5l52.3 52.3c-14.4-5.5-31.5-8.5-50.1-10z" fill="url(#SVGID_1_)"/><path d="m485.2 356c-12.6 28.8-30.5 54.8-52.4 76.8-16.5 16.5-35.3 30.7-55.9 42.1l-165.9-165.9c6.6 5.7 17.2 1.1 17.2-7.9 0-2.1-.5-4.3-1.4-6.2s-2.2-3.7-3.8-5.1l-41.9-36.2c-5.4-4.6-8.4-11.3-8.4-18.4s3.1-13.8 8.4-18.4l41.9-36.2c3.3-2.8 5.2-7 5.2-11.3 0-2.9-1.1-5.3-2.8-7.1l18.6 18.6-53.8 46.5c-4.9 4.2-4.9 11.6-.1 15.7l76.4 66c6.7 5.8 17.2 1.1 17.2-7.9v-36.6c47.2 5.4 65.6 25.6 48.9 79-2.2 7 6.3 12.4 12.2 8.1 19-13.8 36.2-40.2 36.2-66.8 0-20.9-5.5-36.6-15.4-48.4z" fill="url(#SVGID_2_)"/></g><g id="Icon" fill="#fff"><path id="Arrow02_5_" d="m190.2 227.3 76.4-66c6.7-5.7 17.2-1.1 17.2 7.9v35.9c56 4.4 97.2 22.7 97.2 79.7 0 26.6-17.2 53-36.2 66.8-5.9 4.3-14.4-1.1-12.2-8.1 16.7-53.4-1.7-73.6-48.9-79v36.6c0 9-10.5 13.7-17.2 7.9l-76.4-66c-4.8-4.1-4.8-11.5.1-15.7z"/><path id="Arrow01_5_" d="m134.6 243 76.4 66c6.6 5.7 17.2 1.1 17.2-7.9 0-4.3-1.9-8.5-5.2-11.3l-41.9-36.2c-5.4-4.6-8.4-11.3-8.4-18.4s3.1-13.8 8.4-18.4l41.9-36.2c3.3-2.8 5.2-7 5.2-11.3 0-9-10.5-13.7-17.2-7.9l-76.4 66c-4.8 4.1-4.8 11.5 0 15.6z"/></g></svg></a>
        
     	<iframe src="{{$chapter->iframe_link}}"
			frameborder="0"
			marginheight="0"
			marginwidth="0"
			width="100%"
			height="100%"
			scrolling="auto">
        </iframe>
   
   <!--  	<iframe src="https://app.toquran.org/public/uploads/background/Historical Background- Apartheid.pdf#toolbar=0"-->
			<!--frameborder="0"-->
			<!--marginheight="0"-->
			<!--marginwidth="0"-->
			<!--width="100%"-->
			<!--height="100%"-->
			<!--scrolling="auto">-->
   <!--     </iframe>-->
    @endif

@include('front.partials.resource-back-script')

<script src="{{asset('public/js/jquery-2.1.4.min.js')}}"></script>
<script>

$(document).on('click','.player_audio',function(){

// $('.player_audio').click(function() {
    var audio=$('.play');
  if (audio.get(0).paused == false) {
            audio.get(0).pause();
            console.log('pause');
            $('.audio-div').empty();
           
            
                $('.audio-div').append('<svg class="player_audio" width="50px" enable-background="new 0 0 32 32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g id="Layer_1"><path d="m16 1c-8.28 0-15 6.72-15 15s6.72 15 15 15 15-6.72 15-15-6.72-15-15-15zm5.75 15.92-7.65 5.1c-.63.42-1.48-.03-1.48-.79v-10.46c0-.76.85-1.21 1.48-.79l7.65 5.1c.65.44.65 1.4 0 1.84z" fill="#ff6174"/></g></svg>');
            
//   $(".play").css({"display":"none"});
  } 
  else {
      audio.get(0).play();
                  console.log('play');

      $('.audio-div').empty();
      $('.audio-div').append('<svg class="player_audio" width="50px"   id="gradient" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><linearGradient id="linear-gradient" gradientUnits="userSpaceOnUse" x1="4" x2="60" y1="31.928" y2="31.928"><stop offset="0" stop-color="#ffa68d"/><stop offset="1" stop-color="#fd3a84"/></linearGradient><path d="m32 4a28.03075 28.03075 0 0 0 -28 28c1.537 37.146 54.46871 37.1352 56.00005-.00021a28.03078 28.03078 0 0 0 -28.00005-27.99979zm0 50.62a22.64914 22.64914 0 0 1 -22.62-22.62c1.2465-30.00781 43.99808-29.99906 45.24.00017a22.64917 22.64917 0 0 1 -22.62 22.61983zm0-43.24a20.64323 20.64323 0 0 0 -20.62 20.62c1.13253 27.35526 40.11164 27.3473 41.24-.00016a20.64324 20.64324 0 0 0 -20.62-20.61984zm-2.1 31.96a3.00241 3.00241 0 0 1 -3 3h-2.13a3.00883 3.00883 0 0 1 -3-3v-22.68a3.00883 3.00883 0 0 1 3-3h2.13a3.00241 3.00241 0 0 1 3 3zm12.33 0a3.00883 3.00883 0 0 1 -3 3h-2.13a3.00241 3.00241 0 0 1 -3-3v-22.68a3.00241 3.00241 0 0 1 3-3h2.13a3.00883 3.00883 0 0 1 3 3zm-14.33-22.68v22.68a1.003 1.003 0 0 1 -1 1h-2.13a1.00292 1.00292 0 0 1 -1-1v-22.68a1.00292 1.00292 0 0 1 1-1h2.13a1.003 1.003 0 0 1 1 1zm12.33 0v22.68a1.00292 1.00292 0 0 1 -1 1h-2.13a1.003 1.003 0 0 1 -1-1v-22.68a1.003 1.003 0 0 1 1-1h2.13a1.00292 1.00292 0 0 1 1 1z" fill="url(#linear-gradient)"/></svg>');
   
//   $(".play").css({"display":"block","position": "absolute","top": "146px","left": "50px","z-index":"2000",'width':'17%'});
   
   
   
  }
});

</script>
</body>

</html>
