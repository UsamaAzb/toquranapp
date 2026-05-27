<script>
  (() => {
    const modal = document.getElementById('video_Modal');

    if (!modal) {
      return;
    }

    const container = modal.querySelector('.show_video');
    const mimeTypes = {
      m4v: 'video/mp4',
      mp4: 'video/mp4',
      ogg: 'video/ogg',
      ogv: 'video/ogg',
      webm: 'video/webm',
    };

    const videoMimeType = (url) => {
      const cleanUrl = String(url || '').split('?')[0].split('#')[0];
      const extension = cleanUrl.includes('.') ? cleanUrl.split('.').pop().toLowerCase() : '';

      return mimeTypes[extension] || 'video/mp4';
    };

    const normalizeVideoUrl = (rawUrl) => {
      const value = String(rawUrl || '').trim();

      if (!value) {
        return '';
      }

      try {
        const url = new URL(value, window.location.origin);

        if (!['http:', 'https:'].includes(url.protocol)) {
          return '';
        }

        return url.href;
      } catch (error) {
        return '';
      }
    };

    const clearVideo = () => {
      if (!container) {
        return;
      }

      const player = container.querySelector('.video_player');

      if (player) {
        player.pause();
        player.currentTime = 0;
      }

      container.innerHTML = '';
    };

    const buildVideo = (rawUrl) => {
      if (!container) {
        return;
      }

      const videoUrl = normalizeVideoUrl(rawUrl);

      container.innerHTML = '';

      if (!videoUrl) {
        const warning = document.createElement('div');
        warning.className = 'alert alert-warning mb-0';
        warning.textContent = 'This video is not available.';
        container.appendChild(warning);
        return;
      }

      const video = document.createElement('video');
      video.className = 'col-md-12 col-12 col-sm-12 video_player';
      video.controls = true;
      video.preload = 'none';

      const source = document.createElement('source');
      source.src = videoUrl;
      source.type = videoMimeType(videoUrl);

      video.appendChild(source);
      video.appendChild(document.createTextNode('Your browser does not support the video tag.'));
      container.appendChild(video);
    };

    document.addEventListener('click', (event) => {
      const trigger = event.target.closest('.videomodel');

      if (!trigger) {
        return;
      }

      buildVideo(trigger.getAttribute('data-video'));
    });

    modal.addEventListener('hidden.bs.modal', clearVideo);

    document.addEventListener('click', (event) => {
      if (event.target.closest('.close_video_modal')) {
        clearVideo();
      }
    });
  })();
</script>
