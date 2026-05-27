<script>
  (function () {
    @if(auth()->user()?->hasAnyRole(['student', 'parent']))
      document.querySelectorAll('.back_but, .w14-assigned-resource-back').forEach(function (button) {
        button.remove();
      });

      return;
    @endif

    const markerKey = 'w14.resource.sameTabReferrer';
    const currentUrl = window.location.href;

    function safeSameOriginUrl(value) {
      if (!value) {
        return null;
      }

      try {
        const url = new URL(value, window.location.origin);

        if (url.origin !== window.location.origin) {
          return null;
        }

        return url.href;
      } catch (error) {
        return null;
      }
    }

    const referrerUrl = safeSameOriginUrl(document.referrer);

    if (referrerUrl && window.history.length > 1) {
      try {
        window.sessionStorage.setItem(markerKey, currentUrl);
      } catch (error) {
        // Private browsing can block sessionStorage; fallback navigation still works.
      }
    }

    function hasSameTabMarker() {
      try {
        return window.sessionStorage.getItem(markerKey) === currentUrl;
      } catch (error) {
        return false;
      }
    }

    function queryReturnTarget() {
      try {
        return new URLSearchParams(window.location.search).get('return_to');
      } catch (error) {
        return null;
      }
    }

    function fallbackTarget(button, href) {
      return safeSameOriginUrl(button && button.dataset ? button.dataset.returnUrl : null)
        || safeSameOriginUrl(queryReturnTarget())
        || safeSameOriginUrl(href)
        || referrerUrl
        || safeSameOriginUrl('/');
    }

    window.w14ResourceBack = function (event, href) {
      const button = event ? event.currentTarget : null;
      const explicitReturnTarget = safeSameOriginUrl(queryReturnTarget());
      const target = fallbackTarget(button, href);

      if (event) {
        event.preventDefault();
      }

      if (hasSameTabMarker() && referrerUrl && window.history.length > 1) {
        try {
          window.sessionStorage.removeItem(markerKey);
        } catch (error) {
          // No-op.
        }

        window.history.back();

        return false;
      }

      if (explicitReturnTarget) {
        try {
          window.sessionStorage.removeItem(markerKey);
        } catch (error) {
          // No-op.
        }

        window.location.assign(explicitReturnTarget);

        return false;
      }

      if (target) {
        window.location.assign(target);
      }

      return false;
    };

    document.querySelectorAll('.back_but').forEach(function (button) {
      const explicitTarget = fallbackTarget(button, button.getAttribute('href'));

      if (explicitTarget) {
        button.dataset.returnUrl = explicitTarget;
      }

      button.removeAttribute('aria-disabled');
      button.removeAttribute('tabindex');
      button.title = button.title || 'Go back';
      button.style.pointerEvents = '';
      button.style.opacity = '';
      button.style.cursor = '';
    });
  })();
</script>
