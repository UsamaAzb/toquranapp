<!DOCTYPE html>
<html>

<head>
	<title>{{ $title ?? 'Level Up Tutorial' }} | To Quran</title>
    <meta name="description" content="Review this Level Up tutorial lesson.">
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
    top: 106px;
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
	</style>
	
</head>

<body>
    @php
        $resourceReturnTarget = app(\App\Services\Library\ResourceReturnTargetResolver::class)
            ->resolveFromRequest(request(), url('tutriols/level-up'));
    @endphp
    
    <!--<div class="go_back">-->
    <!--      <a class="" href="{{url('reading/listen-read')}}">Go Back </a>-->
    <!--    </div>-->
        <a class="back_but"
           href="{{ url('tutriols/level-up') }}"
           data-return-url="{{ $resourceReturnTarget }}"
           onclick="return window.w14ResourceBack(event, this.href);">
  <!--<svg width="20px" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><g id="Layer_50" data-name="Layer 50"><path d="m76.65332 96.54834a2 2 0 0 0 3.26568-1.54834v-90a2 2 0 0 0 -3.26563-1.54834l-55.05176 45a1.99944 1.99944 0 0 0 0 3.09668zm-.73432-87.33057v81.56446l-49.89166-40.78223z"/></g></svg>-->
  Go Back</a>
      @if(($level->iframe_link != Null)  || ($level->iframe_link!=""))

	<iframe src="{{$level->iframe_link}}"
			frameborder="0"
			marginheight="0"
			marginwidth="0"
			width="100%"
			height="100%"
			scrolling="auto">
</iframe>
@endif

@include('front.partials.resource-back-script')

</body>

</html>
