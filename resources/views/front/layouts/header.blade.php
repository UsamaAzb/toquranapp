<div id="Header_wrapper" style="height:30px;">
<header id="Header">
        <div id="Top_bar">
            <div class="container">
                <div class="column one">
                    <div class="top_bar_left clearfix row col-12"style="width:100%">
                        <div class="logo col-1">
                            <a id="logo" href="{{url('/en')}}" data-data-padding="15" style="padding: 8px 0;!important">
                                <img class="logo-main scale-with-grid" src="{{asset('images/TU-05.png')}}" data-retina="{{asset('images/TU-05.png')}}" data-data-no-retina style="width:60px !important">
                                <img class="logo-sticky scale-with-grid" src="{{asset('images/TU-05.png')}}" data-retina="{{asset('images/TU-05.png')}}" data-data-no-retina>
                                <img class="logo-mobile scale-with-grid" src="{{asset('images/TU-05.png')}}" data-retina="{{asset('images/TU-05.png')}}" data-data-no-retina style="width:60px !important">
                                <img class="logo-mobile-sticky scale-with-grid" src="{{asset('images/TU-05.png')}}" data-retina="{{asset('images/TU-05.png')}}" data-data-no-retina style="width:60px !important"></a>
                        </div>

                        <div class="menu_wrapper col-10">
                            <nav id="menu">
                                <ul id="menu-menu" class="menu menu-main">
                                     @if(Auth::check())
                                      @if(Auth::user()->hasRole('master'))
                                            @php
                                             $pivot_id=Auth::user()->pivot_id;
                                    
                                            @endphp
                                            
                                            
                        <li  class="nav-item"><a href="{{url('course/level?u='.$pivot_id.'&lang=en')}}" class="nav-link " >Book Audio</a></li>

                        <li  class="nav-item"><a href="{{url('reading/listen-read')}}" class="nav-link " >Listen & Read</a></li>

                        <li  class="nav-item"><a href="{{url('tutriols/level-up')}}" class="nav-link " >Level up Tutorials</a></li>

                         <li  class="nav-item"><a href="{{url('course/notice-note')}}" class="nav-link " >Notice & Note</a></li>

                        <li  class="nav-item"><a href="{{url('course/peer-coach')}}" class="nav-link " >Peer Coach</a></li>

<li class="">
                                        <a class="nav-link ">TV Series</a>
                                        <ul class="sub-menu">
                                            <li>
                                                <a href="{{url('tv_series/friends')}}"><span>Friends</span></a>
                                            </li>
                                            <!--<li>-->
                                            <!--    <a href="{{url('tv_series/avatar')}}"><span>Avatar</span></a>-->
                                            <!--</li>-->
                                            
                                        </ul>
                                       </li>
                                  <li  class="nav-item"><a href="{{url('videos/ted')}}" class="nav-link " >TED</a></li>

                                <li  class="nav-item"><a href="{{url('videos/court')}}" class="nav-link " >Court</a></li>

                                  <li  class="nav-item"><a href="{{url('course/radio')}}" class="nav-link " >Radio</a></li>

<li  class="nav-item"><a href="{{url('course/sat')}}" class="nav-link " >SAT</a></li>
                               <li  class="nav-item"><a href="{{url('course/grammar')}}" class="nav-link " >Grammar</a></li>
                             <li  class="nav-item"><a href="{{url('course/background')}}" class="nav-link " >Background</a></li>

                                            
                                            
                                             @endif
                                              @endif
                                              
                                              
                                        
                                        
                                                @if(Auth::check())
                                      @if(Auth::user()->hasRole('intschool'))
                                            @php
                                             $pivot_id=Auth::user()->pivot_id;
                                    
                                            @endphp
                                            
                                            

                        <li  class="nav-item"><a href="{{url('reading/listen-read')}}" class="nav-link " >Listen & Read</a></li>

                        <li  class="nav-item"><a href="{{url('tutriols/level-up')}}" class="nav-link " >Level up Tutorials</a></li>

                         <li  class="nav-item"><a href="{{url('course/notice-note')}}" class="nav-link " >Notice & Note</a></li>

                        <li  class="nav-item"><a href="{{url('course/peer-coach')}}" class="nav-link " >Peer Coach</a></li>





<li  class="nav-item"><a href="{{url('course/sat')}}" class="nav-link " >SAT</a></li>
                               <li  class="nav-item"><a href="{{url('course/grammar')}}" class="nav-link " >Grammar</a></li>
                             <li  class="nav-item"><a href="{{url('course/background')}}" class="nav-link " >Background</a></li>
                              @php
                                             $pivot_id=Auth::user()->pivot_id;
                                    
                                           
{{$stu=App\Models\Student::where('id',$pivot_id)->first();}}

@endphp
              @if ($stu->follow_up !=null)  
              @if($stu->follow_up->google_sheet !="")
              <li  class="nav-item"><a href="{{$stu->follow_up->google_sheet}}" target="_blank" class="nav-link " >Follow-up Sheet</a></li>
               @endif
              @endif
                                            
                                            
                                             @endif
                                              @endif
                                        
                                              
                                              
                                              
                                       @if(Auth::check())
                                      @if(Auth::user()->hasRole('student'))
                                            @php
                                             $pivot_id=Auth::user()->pivot_id;
                                    
                                            @endphp
@if(Auth::user()->student->course_id==2)
                    <li  class="nav-item"><a href="{{url('course/level?u='.$pivot_id.'&lang=en')}}" class="nav-link " >Book Audio</a></li>
              @endif
                                             @php

{{$stu=App\Models\Student::where('id',$pivot_id)->first();}}

@endphp
              @if ($stu->follow_up !=null)  
              @if($stu->follow_up->google_sheet !="")
              <li  class="nav-item"><a href="{{$stu->follow_up->google_sheet}}" target="_blank" class="nav-link " >Follow-up Sheet</a></li>
              @else
{{--<li  class="nav-item">
    <a type="button" class="nav-link follow_up_modal" data-bs-toggle="modal" data-bs-target="follow_up_Modal">Follow-up Sheet</a>
    </li>--}}
               @endif
              @else
{{--<li  class="nav-item">
    <a type="button" class="nav-link follow_up_modal" data-bs-toggle="modal" data-bs-target="follow_up_Modal">Follow-up Sheet</a>
    </li>--}}

              @endif
                        <li  class="nav-item"><a href="{{url('reading/listen-read')}}" class="nav-link " >Listen & Read</a></li>
                        <li  class="nav-item"><a href="{{url('tutriols/level-up')}}" class="nav-link " >Level up Tutorials</a></li>
                         <li  class="nav-item"><a href="{{url('course/notice-note')}}" class="nav-link " >Notice & Note</a></li>
                        <li  class="nav-item"><a href="{{url('course/peer-coach')}}" class="nav-link " >Peer Coach</a></li>
                             <li  class="nav-item"><a href="{{url('course/background')}}" class="nav-link " >Background</a></li>


@if(Auth::user()->student->course_id==2)

   <li class="">
                                        <a class="nav-link ">TV Series</a>
                                        <ul class="sub-menu">
                                            <li>
                                                <a href="{{url('tv_series/friends')}}"><span>Friends</span></a>
                                            </li>
                                            <!--<li>-->
                                            <!--    <a href="{{url('tv_series/avatar')}}"><span>Avatar</span></a>-->
                                            <!--</li>-->
                                            
                                        </ul>
                                       </li>
 @endif

                                  @if(Auth::user()->student->course_id==2)
                                  <li  class="nav-item"><a href="{{url('videos/ted')}}" class="nav-link " >TED</a></li>

                                <li  class="nav-item"><a href="{{url('videos/court')}}" class="nav-link " >Court</a></li>
                                                                  <li  class="nav-item"><a href="{{url('course/radio')}}" class="nav-link " >Radio</a></li>

 @endif

 @endif
  @endif
   @if(Auth::check())
                                      @if(Auth::user()->hasRole('student'))
                      @if(Auth::user()->student->course_id==3)
                                            @if(Auth::user()->student->intschool_type!=2)
                                            


                                <li  class="nav-item"><a href="{{url('course/sat')}}" class="nav-link " >SAT</a></li>
                                 @endif

                               <li  class="nav-item"><a href="{{url('course/grammar')}}" class="nav-link " >Grammar</a></li>

 @endif
 @endif
 @endif
  
                                    @if(Auth::check())
                                    <li  class=""><a href="{{url('en/logout')}}" class="action_button" >Log out</a></li>

                                    @else
                                    <li  class=""><a href="{{url('en/login')}}" class="action_button" >Log in</a></li>
                                    
                                    @endif
                                </ul>
                            </nav>
                            <a class="responsive-menu-toggle " href="#"><i class="icon-menu-fine"></i></a>
                        </div>

                    </div>
                   
                </div>
            </div>
        </div>



    </header>
    
    <div class="modal " id="follow_up_Modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="follow_up_ModalLabel" aria-modal="true" role="dialog" >
    
    
   
  <div class="modal-dialog modal-dialog-centered" >
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Modal body text goes here.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
    
    
    
    </div>
