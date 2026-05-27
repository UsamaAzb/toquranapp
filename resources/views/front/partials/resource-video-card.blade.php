@php
  $imageUrl = \App\Helpers\Helpers::publicAsset($video->image ?? null);
  $providerLabel = $providerLabel ?? 'Video';
@endphp

<div class="col-lg-4 col-md-6 col-12 mb-4">
  <div class="card video_card h-100">
    <a class="surah_video_block h-100"
       href="#"
       data-bs-toggle="modal"
       data-bs-target="#surahvideoModal"
       data-id="{{ $video->id }}"
       data-embed="{{ $video->video_link }}"
       aria-label="Open {{ $video->title }}">
      <span class="resource-video-thumb {{ $imageUrl ? '' : 'is-missing' }}">
        @if($imageUrl)
          <img src="{{ $imageUrl }}"
               alt="{{ $video->title }}"
               loading="lazy"
               onerror="this.closest('.resource-video-thumb').classList.add('is-missing')">
        @endif
        <span class="resource-video-fallback">{{ $providerLabel }}</span>
      </span>
      <div class="card-body video_body d-flex flex-column justify-content-center">
        <strong class="card-text video_title">{{ $video->title }}</strong>
      </div>
    </a>
  </div>
</div>
