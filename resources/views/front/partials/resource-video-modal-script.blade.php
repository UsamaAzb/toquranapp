<script>
  (() => {
    const modal = document.getElementById('surahvideoModal');

    if (!modal) {
      return;
    }

    const normalizeEmbedUrl = (rawUrl) => {
      const value = String(rawUrl || '').trim();

      if (!value) {
        return '';
      }

      try {
        const withProtocol = value.startsWith('http') ? value : `https://${value}`;
        const url = new URL(withProtocol);
        const host = url.hostname.replace(/^www\./, '');

        if (host === 'youtube.com' && url.pathname === '/watch') {
          const id = url.searchParams.get('v');
          return id ? `https://www.youtube.com/embed/${id}` : withProtocol;
        }

        if (host === 'youtu.be') {
          const id = url.pathname.replace(/^\/+/, '');
          return id ? `https://www.youtube.com/embed/${id}` : withProtocol;
        }

        if (host === 'embed.ted.com') {
          return withProtocol.replace('/talks/lang/en/', '/talks/');
        }

        return withProtocol;
      } catch (error) {
        return value;
      }
    };

    const getYouTubeEmbedId = (url) => {
      const host = url.hostname.replace(/^www\./, '');

      if (!['youtube.com', 'youtube-nocookie.com'].includes(host)) {
        return '';
      }

      const [, embedSegment, videoId] = url.pathname.split('/');

      return embedSegment === 'embed' && videoId ? videoId : '';
    };

    const buildEmbedSrc = (embed) => {
      try {
        const url = new URL(embed);
        const youtubeId = getYouTubeEmbedId(url);

        url.searchParams.set('cc_load_policy', '1');
        url.searchParams.set('loop', '1');
        url.searchParams.set('rel', '0');

        if (youtubeId) {
          url.searchParams.set('playlist', youtubeId);
        }

        return url.toString();
      } catch (error) {
        const separator = embed.includes('?') ? '&' : '?';

        return `${embed}${separator}cc_load_policy=1&loop=1&rel=0`;
      }
    };

    const buildVideo = (trigger) => {
      const container = modal.querySelector('.youtube');
      const embed = normalizeEmbedUrl(trigger?.getAttribute('data-embed'));

      if (!container) {
        return;
      }

      container.innerHTML = '';

      if (!embed) {
        container.innerHTML = '<div class="alert alert-warning mb-0">This video link is not available.</div>';
        return;
      }

      const iframe = document.createElement('iframe');
      iframe.className = 'youtube-iframe';
      iframe.src = buildEmbedSrc(embed);
      iframe.title = trigger?.getAttribute('aria-label') || 'Video';
      iframe.setAttribute('frameborder', '0');
      iframe.setAttribute('scrolling', 'no');
      iframe.setAttribute('allow', 'autoplay; fullscreen; picture-in-picture; encrypted-media; web-share');
      iframe.setAttribute('allowfullscreen', 'allowfullscreen');
      iframe.setAttribute('webkitallowfullscreen', 'webkitallowfullscreen');
      iframe.setAttribute('mozallowfullscreen', 'mozallowfullscreen');
      container.appendChild(iframe);
    };

    modal.addEventListener('show.bs.modal', (event) => {
      buildVideo(event.relatedTarget);
    });

    modal.addEventListener('hidden.bs.modal', () => {
      const container = modal.querySelector('.youtube');

      if (container) {
        container.innerHTML = '';
      }
    });
  })();
</script>
