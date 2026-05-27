<div class="col-12 mt-5">
  <!-- User Pills -->
  <div class="nav-align-top w-100">
    <ul class="nav nav-pills d-flex justify-content-start align-items-center mb-6 gap-2 flex-wrap w-100">
      @foreach ($cards as $index => $card)
        <li class="nav-item">
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
</div>
