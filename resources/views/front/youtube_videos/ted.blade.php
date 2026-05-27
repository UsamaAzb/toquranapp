@extends('layouts/layoutMaster')

@section('title', 'TED Talks')
@section('meta_description', 'Watch TED talks for listening, vocabulary, speaking, and reflection practice.')
@section('content')
@include('front.partials.resource-page-styles')
@include('front.partials.assigned-resource-back-button')
<style>
/*    @media only screen and (min-width:800px){*/
/*    .youtube-iframe{*/
/*        height: 75%!important;*/
/*    width: 75%!important;*/
/*    top: 0!important;*/
/*    left: 11%!important;*/
/*}*/
/*    }*/
    .modal-dialog{
        min-width:75%;
    }
    
    @media only screen and (max-width:800px){
          .modal-dialog{
        min-width:90%!important;
    }
    }
</style>
<div class="resource-page">
   <div class="img-header header-overlay ">
    <div class="container">
         <h2 class="header-title border-color" >TED</h2>
    </div>
</div>
      <div class="container-fluid box-div row justify-content-center">
                    
<div class="row col-11  mt-4 mb-4">
    @if($videos?->count() > 0)
@foreach($videos as $video)
  @include('front.partials.resource-video-card', ['video' => $video, 'providerLabel' => 'TED'])
@endforeach
@endif
</div>
</div>
</div>
</div>

<!-- video popup -->
					

						
		<div class="modal fade resource-video-modal" id="surahvideoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg ">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close close_video" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

      <!--<div class="youtube-wrapper">-->
	<div class="youtube">
		<div class="play-button"></div>
	</div>
<!--</div>  -->


      </div>
      
    </div>
  </div>
</div>




@endsection


@push('scripts')
  @include('front.partials.resource-video-modal-script')
@endpush
