@extends('front.layouts.master')
@section('content')
  <!--<link rel="stylesheet" href="{{asset('public/front/css/custom.css')}}">-->
<style>
    .player_audio{
               position: sticky;
    cursor: pointer;
    top: 7px;

    }
    .player_audio:hover{
       transform: scale(1.2);
}
</style>
<div class="" id="">
    
    <div class="img-header header-overlay ">
    <div class="container">
         <h2 class="header-title border-color" >{{$story->title}}</h2>
         <div class="col-md-12 text-center page_inside_text">
<h1 style="color:white;font-size:20px;line-height: 0px!important;">{{$chapter->title}}</h1>
    </div>
    </div>
</div>
    
    
   {{-- <div class="topic_title_sec col-md-12">
      <div class="container">
  <div class="col-md-12 p-2 mt-5">
    <div class="col-md-3 mt-3 mb-4">
      <?php  $parent_url=$lang.'/subject/language-literature/'.$grade.'/units'; ?>
<a class="back_but" href="{{url($parent_url)}}" >
<i class="fa-solid fa-caret-left back-arr"></i>
  Units</a>
    </div>

    <div class="col-md-12 text-center page_inside_text">
<h5>{{ucfirst($grade)}}</h5>
    </div>

    <div class="col-md-12 text-center page_inside_text">
<h1>{{$chapter->title}}</h1>
    </div>
   
  </div>
</div>
  </div>--}}
  <div class="secdiv"></div>
  @include("https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07_ese_roguewave_01_en_us.xhtml")
  
<div class="container box-div row justify-content-center">
    @if(($chapter->iframe_link != Null)  || ($chapter->iframe_link!=""))
    
    <div class="col-lg-9 col-md-10 col-12">

                                        
                                        
	<div class="youtube" data-embed="nbxSHzEk_i4">
	<!--	<div class="play-button"></div>-->
	<!--<img src="https://teacherusama.com/public/images/frame.jpg">-->
	<iframe src="{{url($chapter->iframe_link)}}?rel=0&showinfo=0" height="100%" width="100%" title="Iframe Example"></iframe>
	
</div>
 
   </div>
  
@elseif(($chapter->audio) && ($chapter->text))
<div class="col-md-10 col-12 row">
    
    <div class="col-md-2 col-2 mt-3 audio-div">
        <!--<button class='player_audio'> -->
        
        <svg class='player_audio' width="50px" enable-background="new 0 0 32 32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g id="Layer_1"><path d="m16 1c-8.28 0-15 6.72-15 15s6.72 15 15 15 15-6.72 15-15-6.72-15-15-15zm5.75 15.92-7.65 5.1c-.63.42-1.48-.03-1.48-.79v-10.46c0-.76.85-1.21 1.48-.79l7.65 5.1c.65.44.65 1.4 0 1.84z" fill="#ff6174"/></g></svg>
        
        <!--</button>-->
   </div>
           <audio class="mt-4 col-12 play" controls style="display:none">
  <source  src="{{asset($chapter->audio)}}" type="audio/ogg">
  <source src="{{asset($chapter->audio)}}" type="audio/mpeg">
Your browser does not support the audio element.
</audio>

    
<div class="col-md-10 col-10 ">


<p>{!!$chapter->text!!}</p>
</div>
</div>
@endif
</div>
</div>







@endsection
@push('audio_play')
<script type="text/javascript">
 $(function (){
//  $(".secdiv").load("https://cdn2.trunity.org/literature/into_lit/g7/student/epub/hmh_ngl20na_g7u1_sete_en_student/OPS/s9ml/cards/07_ese_roguewave_01_en_us.xhtml");
});
</script>
<script>
$(document).on('click','.player_audio',function(){

// $('.player_audio').click(function() {
    var audio=$('.play');
  if (audio.get(0).paused == false) {
            audio.get(0).pause();
            console.log('pause');
            $('.audio-div').empty();
           
            
                $('.audio-div').append('<svg class="player_audio" width="50px" enable-background="new 0 0 32 32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g id="Layer_1"><path d="m16 1c-8.28 0-15 6.72-15 15s6.72 15 15 15 15-6.72 15-15-6.72-15-15-15zm5.75 15.92-7.65 5.1c-.63.42-1.48-.03-1.48-.79v-10.46c0-.76.85-1.21 1.48-.79l7.65 5.1c.65.44.65 1.4 0 1.84z" fill="#ff6174"/></g></svg>');
            
  } else {
      audio.get(0).play();
                  console.log('play');

      $('.audio-div').empty();
      $('.audio-div').append('<svg class="player_audio" width="50px"   id="gradient" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><linearGradient id="linear-gradient" gradientUnits="userSpaceOnUse" x1="4" x2="60" y1="31.928" y2="31.928"><stop offset="0" stop-color="#ffa68d"/><stop offset="1" stop-color="#fd3a84"/></linearGradient><path d="m32 4a28.03075 28.03075 0 0 0 -28 28c1.537 37.146 54.46871 37.1352 56.00005-.00021a28.03078 28.03078 0 0 0 -28.00005-27.99979zm0 50.62a22.64914 22.64914 0 0 1 -22.62-22.62c1.2465-30.00781 43.99808-29.99906 45.24.00017a22.64917 22.64917 0 0 1 -22.62 22.61983zm0-43.24a20.64323 20.64323 0 0 0 -20.62 20.62c1.13253 27.35526 40.11164 27.3473 41.24-.00016a20.64324 20.64324 0 0 0 -20.62-20.61984zm-2.1 31.96a3.00241 3.00241 0 0 1 -3 3h-2.13a3.00883 3.00883 0 0 1 -3-3v-22.68a3.00883 3.00883 0 0 1 3-3h2.13a3.00241 3.00241 0 0 1 3 3zm12.33 0a3.00883 3.00883 0 0 1 -3 3h-2.13a3.00241 3.00241 0 0 1 -3-3v-22.68a3.00241 3.00241 0 0 1 3-3h2.13a3.00883 3.00883 0 0 1 3 3zm-14.33-22.68v22.68a1.003 1.003 0 0 1 -1 1h-2.13a1.00292 1.00292 0 0 1 -1-1v-22.68a1.00292 1.00292 0 0 1 1-1h2.13a1.003 1.003 0 0 1 1 1zm12.33 0v22.68a1.00292 1.00292 0 0 1 -1 1h-2.13a1.003 1.003 0 0 1 -1-1v-22.68a1.003 1.003 0 0 1 1-1h2.13a1.00292 1.00292 0 0 1 1 1z" fill="url(#linear-gradient)"/></svg>');
   
  }
});

</script>
@endpush