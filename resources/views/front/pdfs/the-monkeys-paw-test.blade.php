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
         <h2 class="header-title border-color" style="font-size:25px"> The Monkey's Paw  Test</h2>
         <div class="col-md-12 text-center page_inside_text">
<!--<h1 style="color:white;font-size:20px;line-height: 30px!important;">Summary</h1>-->
    </div>
    </div>
</div>
    
    

  <div class="secdiv"></div>

<div class="container box-div row justify-content-center">

    <div class="col-lg-9 col-md-10 col-12 mb-3">

	<iframe src="{{asset('public/uploads/pdfs/2-The Monkeys Paw from The Monkeys Paw Selection Test.pdf')}}"
			frameborder="0"
			marginheight="0"
			marginwidth="0"
			width="100%"
			height="500px"
			>
</iframe>
<!--<embed src="{{asset('public/uploads/pdfs/2-The Monkeys Paw from The Monkeys Paw Selection Test.pdf')}}" width="600" height="500" alt="pdf" />-->

 
   </div>
  

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