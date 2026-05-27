<!DOCTYPE html>
<html>

<head>
	<title>{{ $title ?? 'Series Audio' }} | Week 14</title>
    <meta name="description" content="Listen to this series audio resource.">
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
    top: 52px;
    left: 5%;
		    color: #ffffff!important;
    border-width: 1px!important;
    border-color: #007edb;
    border-radius: 4px;
    font-size: 13px;
    font-family: 'Poppins',Helvetica,Arial,Lucida,sans-serif!important;
    font-weight: 300!important;
    padding-right: 13px;
    padding-left: 13px;
    background-color: #007edb;
    padding-top: 8px!important;
    padding-bottom: 8px!important;
    transition: all 300ms ease 0ms;
    -webkit-transition-property: all!important;
    transition-property: all!important;
    border: 1px solid #007edb;
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
       position: fixed;
    cursor: pointer;
    top: 91px;
    z-index: 2000;
    left: 101px;

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
  width: 220px;
    height: 40px;
    transform: rotate(90deg);
    z-index: 2000;
    position: absolute;
    top: 230px;
    left: 16px;

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
@media only screen and (max-width:512px){
       .play{
 width: 67%;
    height: 48px;
    z-index: 2000;
    position: absolute;
    bottom: 1px;
    left: 14%;

    }
      .audio-div{
       position: fixed;
    cursor: pointer;
    bottom: -5px;
    z-index: 2000;
    left: 8%;

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
   <a class="back_but" href="{{url('reading/listen-read?d='.$story_id)}}" data-return-url="{{ $resourceReturnTarget }}" onclick="return window.w14ResourceBack(event, this.href);">Go Back</a>
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
        <a class="back_but" href="{{url('reading/listen-read?d='.$story_id)}}" data-return-url="{{ $resourceReturnTarget }}" onclick="return window.w14ResourceBack(event, this.href);">Go Back</a>
     	<iframe src="{{$chapter->iframe_link}}"
			frameborder="0"
			marginheight="0"
			marginwidth="0"
			width="100%"
			height="100%"
			scrolling="auto">
        </iframe>
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
