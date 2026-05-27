@extends('layouts/layoutMaster')

@section('title', 'Peer Coach')
@section('meta_description', 'Browse peer-coaching activities and structured discussion tasks.')
@section('content')
  <link rel="stylesheet" href="{{asset('public/front/css/custom.css')}}">
  @include('front.partials.resource-page-styles')
<div class="resource-page" id="">
  <div class="topic_title_sec col-md-12">
    <div class="container">
{{--<div class="col-md-12 p-2 mt-5">
  <div class="col-md-3 mt-3 mb-4">

  <a class="back_but" href="{{url($lang.'/subject/language-literature/'.$grade)}}" >
  <i class="fa-solid fa-caret-left back-arr"></i>
  {{ucfirst($grade)}}</a>
  </div>
  <div class="col-md-12 text-center page_inside_text">
<h5>{{ucfirst($grade)}}</h5>
  </div>

  <div class="col-md-12 text-center page_inside_text">
<h1>Units</h1>
  </div>
  <div class="col-md-12 text-center page_inside_text mt-3">
<h2>Language and Literature, units, independant reading, digital library, and more</h2>
  </div>
</div>--}}
</div>
</div>

<div class="container-fluid box-div">
<div class=" row  mt-2 justify-content-center">
        <div class="col-lg-10 col-md-10 col-12">

    <!--<div class="stu_list ">-->
        <div class=" ">

     <div   class="stu_table row  mt-2 justify-content-center">
    <div class="col-md-offset-3 col-md-9 col-lg-9 col-11">
  <div class="accordion unit_group" id="accordionExample">
      
    @if($peer_coach)
    @foreach($peer_coach as $k=>$peer)
    @if($peer->parent_id==0)
    
    <?php $peer_id=$peer->id;  ?>
    <div class="accordion-item panel panel-default">
      <h2 class="accordion-header panel-heading " id="heading_{{$peer_id}}" >

      
        
        
         <button class="accordion-button unit-button  collapsed  col-md-9" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{$peer_id}}" aria-expanded=" false " aria-controls="collapse_{{$peer_id}}">
         <p>{{$peer->title}} </p> 

        </button>
    
      </h2>
      <!--<div id="collapse_{{$k}}" class="accordion-collapse collapse @if($k==0) show @endif" aria-labelledby="heading_{{$k}}" data-bs-parent="#accordionExample">-->
      <div id="collapse_{{$peer_id}}" class="accordion-collapse collapse   " aria-labelledby="heading_{{$peer_id}}" data-bs-parent="#accordionExample">
        <div class="accordion-body panel-body lessons_body">
         

          @if($peer->peer_desc)
          @foreach($peer->peer_desc as $k=>$desc)
<a  @if($desc->slug!= Null) href="{{url('peer-coach/'.$desc->slug)}}" @else href="#"  @endif>
          <div class="lesson_div ">

{{$desc->title}}
          </div>
          </a>
          @endforeach
          @endif

        </div>
      </div>
    </div>
    @endif

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
