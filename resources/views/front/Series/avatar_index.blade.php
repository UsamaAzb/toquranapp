@extends('layouts/layoutMaster')

@section('title', 'Avatar Episodes')
@section('meta_description', 'Browse Avatar episodes for story-based listening and language practice.')
@section('content')
  <link rel="stylesheet" href="{{asset('public/front/css/custom.css')}}">
  @include('front.partials.resource-page-styles')
<div class="resource-page" id="">
 

<div class="container-fluid box-div">
<div class=" row  mt-2 justify-content-center">
        <div class="col-lg-10 col-md-10 col-12">

    <!--<div class="stu_list ">-->
        <div class=" ">

     <div   class="stu_table row  mt-2 justify-content-center">
    <div class="col-md-offset-3 col-md-9 col-lg-9 col-11">
  <div class="accordion unit_group" id="accordionExample">
    @if($seasons)
    @foreach($seasons as $k=>$season)
    <?php $season_id=$season->id;  ?>
    <div class="accordion-item panel panel-default">
      <h2 class="accordion-header panel-heading " id="heading_{{$season_id}}" >

        
        
         <button class="accordion-button unit-button @if($season_id!=$story_d) collapsed @endif col-md-9" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{$season_id}}" aria-expanded="@if($season_id!=$story_d) true @else false @endif " aria-controls="collapse_{{$season_id}}">
         <p>{{$season->title}} </p> 

        </button>
    
      </h2>
   
      <div id="collapse_{{$season_id}}" class="accordion-collapse collapse @if($season_id==$story_d) show @endif" aria-labelledby="heading_{{$season_id}}" data-bs-parent="#accordionExample">
        <div class="accordion-body panel-body lessons_body">
         
          @if($season->series_episodes)
          @foreach($season->series_episodes as $k=>$episode)
<a   href="{{url('tv_series/avatar/'.$episode->slug)}}" >
          <div class="lesson_div ">

{{$episode->title}}
          </div>
          </a>
          @endforeach
          @endif

        </div>
      </div>
    </div>
    @endforeach
    @endif

  </div>


  </div>




</div>

</div>
</div>
  </div>
</div>
</div>
</div>
<div class="modal fade" id="video_Modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
              <button type="button" class="btn-close close_video_modal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
      <div class="modal-body">
        <div class="col-md-12 col-12 col-sm-12 show_video">

        </div>
      </div>

    </div>
  </div>
</div>
@endsection
@push('scripts')
@include('front.partials.resource-native-video-modal-script')
@endpush
