@extends('layouts/layoutMaster')

@section('title', 'Notice & Note')
@section('meta_description', 'Browse Notice and Note close-reading signposts and discussion prompts.')

@section('content')
  <link rel="stylesheet" href="{{ asset('public/front/css/custom.css') }}">
  @include('front.partials.resource-page-styles')
  <div class="resource-page" id="">
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

                  @if ($notice_note)
                    @foreach ($notice_note as $k => $note)
                      @if ($note->parent_id == 0)
                        <?php $note_id = $note->id; ?>
                        <div class="accordion-item panel panel-default">
                          <h2 class="accordion-header panel-heading " id="heading_{{ $note_id }}">




                            <button class="accordion-button unit-button  collapsed  col-md-9" type="button"
                              data-bs-toggle="collapse" data-bs-target="#collapse_{{ $note_id }}"
                              aria-expanded=" false " aria-controls="collapse_{{ $note_id }}">
                              <p>{{ $note->title }} </p>

                            </button>

                          </h2>
                          <!--<div id="collapse_{{ $k }}" class="accordion-collapse collapse @if ($k == 0) show @endif" aria-labelledby="heading_{{ $k }}" data-bs-parent="#accordionExample">-->
                          <div id="collapse_{{ $note_id }}" class="accordion-collapse collapse   "
                            aria-labelledby="heading_{{ $note_id }}" data-bs-parent="#accordionExample">
                            <div class="accordion-body panel-body lessons_body">


                              @if ($note->notice_note_desc)
                                @foreach ($note->notice_note_desc as $k => $note_desc)
                                  <a
                                    @if ($note_desc->slug != null) href="{{ url('notice-note/' . $note_desc->slug) }}" @else href="#" @endif>
                                    <div class="lesson_div ">

                                      {{ $note_desc->title }}
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
