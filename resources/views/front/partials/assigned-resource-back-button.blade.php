@if(request()->filled('return_to') && ! auth()->user()?->hasAnyRole(['student', 'parent']))
  @php
    $assignedResourceReturnTarget = app(\App\Services\Library\ResourceReturnTargetResolver::class)
      ->resolveFromRequest(request(), url('/'));
  @endphp

  <a
    class="back_but w14-assigned-resource-back"
    href="{{ $assignedResourceReturnTarget }}"
    data-return-url="{{ $assignedResourceReturnTarget }}"
    onclick="return window.w14ResourceBack(event, this.href);"
    aria-label="Back to assigned task"
  >
    <i class="ti tabler-arrow-left"></i>
  </a>

  @once
    <style>
      .w14-assigned-resource-back {
        position: fixed;
        z-index: 1080;
        top: 5.25rem;
        left: 1rem;
        width: 2.35rem;
        height: 2.35rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: #1f8ef1;
        color: #fff !important;
        text-decoration: none;
        box-shadow: 0 8px 18px rgba(31, 142, 241, .28);
      }

      .w14-assigned-resource-back:hover,
      .w14-assigned-resource-back:focus {
        color: #fff !important;
        transform: translateY(-1px);
      }

      @media (max-width: 768px) {
        .w14-assigned-resource-back {
          top: 4.5rem;
          left: .75rem;
        }
      }
    </style>
  @endonce

  @include('front.partials.resource-back-script')
@endif
