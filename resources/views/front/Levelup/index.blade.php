@extends('layouts/layoutMaster')

@section('title', 'Level Up Tutorials')
@section('meta_description', 'Browse short tutorial lessons for targeted skill-building and review.')
@section('content')
  <link rel="stylesheet" href="{{ asset('public/front/css/custom.css') }}">
  @include('front.partials.resource-page-styles')

  <div class="resource-page">
    <div class="topic_title_sec col-md-12">
      <div class="container">

      </div>
    </div>

    <div class="container-fluid box-div">
      <div class=" row  mt-2 justify-content-center">
        <div class="col-lg-10 col-md-10 col-12">

          <!--<div class="stu_list ">-->
          <div class=" ">

            <div class="stu_table row  mt-2 justify-content-center">
              <div class="col-md-offset-3 col-md-9 col-lg-9 col-11">
                <div class="accordion unit_group" id="accordionExample">
                  @if ($levelups)
                    <div class="accordion-item panel panel-default">
                      <h2 class="accordion-header panel-heading " id="heading_0">

                        <button class="accordion-button unit-button  col-md-9" type="button" data-bs-toggle="collapse"
                          data-bs-target="#collapse_0" aria-expanded=" true  " aria-controls="collapse_0">
                          <p>Level up Tutorials</p>

                        </button>

                      </h2>
                      <div id="collapse_0" class="accordion-collapse collapse  show " aria-labelledby="heading_0"
                        data-bs-parent="#accordionExample">
                        <div class="accordion-body panel-body lessons_body">


                          @foreach ($levelups as $k => $level)
                            <a
                              @if ($level->slug != null) href="{{ url('tutriols/level-up/' . $level->slug) }}" @else href="#" @endif>
                              <div class="lesson_div ">

                                {{ $level->title }}
                              </div>
                            </a>
                          @endforeach

                        </div>
                      </div>
                    </div>
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
  <div class="modal fade" id="video_Modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
