
@extends('front.layouts.master')
@section('content')
   


      <div class="container-fluid box-div">

<div class="row justify-content-md-center justify-content-sm-center justify-content-center stu_div">
  <div class="col-md-12 col-sm-12 col-12 ">
          <div class="">


  
  


<!--<div class="stu_list">-->
    <div class="">

     <div class="stu_table">

    <div class="d-flex justify-content-center row">
        <div class="col-md-11 col-lg-8 col-12 row mt-3">

                  <div class="card card-primary card-tabs">
                    <!-- tabs -->
                    <div class="card-header p-0 pt-1 row" >
                        <div class=" col-md-4">
                              @foreach($all_levels as $lev)
                <div class="nav-item level-li mt-2">
                  <p class="nav-link level-link active @if($lev->id== $level->id) current_level @endif" aria-current="page" >{{$lev->title}}</p>
                </div>
                @endforeach
                        </div>
                      <ul class="nav nav-tabs mt-2 col-md-8" id="custom-tabs-one-tab" role="tablist">
                        @foreach($units as $k=> $unit)
                        <li  role="presentation"  class="nav-item ">
                          <a  @if($k == 0) class=" nav-link units active " aria-selected="true" @endif class="nav-link units" id="{{$unit->title}}-tap" data-bs-toggle="tab" data-bs-target="#tab-{{$unit->id}}"  type="button" role="tab" aria-controls="#tab-{{ $unit->id }}" >{{$unit->title}}</a>
                        </li>
                        @endforeach
                      </ul>
                    </div>

                    <!-- body -->

                  <div class="card-body">
                    <div class="tab-content" id="custom-tabs-one-tabContent">
                      @foreach($units as $ke=>$unit)

                      <!-- sec info -->
                      @if($unit->audio_lessons)
                      <div @if($ke == 0) class="tab-pane active" @else class="tab-pane" @endif id="tab-{{$unit->id}}" role="tabpanel" aria-labelledby="tab-{{$unit->id}}" tabindex="0" >

                        @foreach($unit->audio_lessons as $less)
                        @if($less->type=="audio")
                        <div class="col-md-12 row">
                            <div class="col-md-2 col-sm-2" style="max-width: 90px;">
                            <label style="padding-top: 12px;">{{$less->title}}</label>
                            </div>
                       <div class="col-md-10 col-sm-9">
                        <audio class="col-md-12 col-sm-12" controls controlsList="nodownload" preload="none">
                         <source src="{{asset($less->file)}}" type="audio/mpeg" >
                         Your browser does not support the audio element.
                         </audio>
                         </div>
                         </div>
                         <hr class="hr-quiz">

                         @endif
                        @endforeach

</div>
@endif
                  @endforeach <!--End sec info-->



                  </div>


                    <!-- /.card -->
                  </div>

                </div>
</div>
</div>
</div></div>
        
        </div>
        </div>
</div>
@endsection