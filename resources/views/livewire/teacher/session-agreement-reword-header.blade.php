{{--<div class="col-12">
  <div class="nav-align-top w-100">
    <ul class="nav nav-pills d-flex justify-content-start align-items-center mb-6 gap-2 flex-wrap w-100">
      @foreach ($cards as $index => $card)
        <li class="nav-item ">
       <a 
  class="nav-link d-flex align-items-center {{ url()->current() === $card['link'] ? 'active' : '' }}" 
  href="{{ $card['link'] ?: 'javascript:void(0);' }}"
>
  <i class="icon-base {{ $card['icon'] }} icon-md me-1_5"></i>
  {{ $card['title'] }}
</a>

        </li>
      @endforeach
    </ul>
  </div>
</div>--}}



<div class="col-12">
  <div class="nav-align-top w-100">
    <ul class="nav nav-pills mb-3 row col-12 d-flex flex-wrap flex-column flex-lg-row  flex-md-row flex-sm-row  align-items-center justify-content-center">
      @foreach ($cards as $index => $card)
        <li class="col-xl-2 col-lg-3 col-md-4 col-sm-4 me-0 pe-0 col-11 mb-3 ">
       <a 
  class="nav-link flex-column align-items-center justify-content-center text-center p-5 {{ url()->current() === $card['link'] ? 'active shadow-sm ' : 'bg-label-light shadow-sm' }}" 
  href="{{ $card['link'] ?: 'javascript:void(0);' }}"
>
   
           {!! $card['icon'] !!}
           
 <span class="mt-2"> {{ $card['title'] }} </span> 
</a>

        </li>
      @endforeach
    </ul>
  </div>
</div>