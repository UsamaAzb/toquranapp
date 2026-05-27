<script>
  (function () {
    if (window.w14SessionsBoardReturnInitialized) {
      window.w14ScrollRequestedTask?.();
      return;
    }

    window.w14SessionsBoardReturnInitialized = true;

    const returnAnchorKey = 'w14SessionsBoardReturnAnchor';

    const scrollOffset = () => {
      const navbar = document.querySelector('.layout-navbar, .navbar-detached, .w14-app-topbar');
      const navbarHeight = navbar ? Math.ceil(navbar.getBoundingClientRect().height) : 0;

      return Math.max(104, navbarHeight + 36);
    };

    const scrollToElement = (element) => {
      const top = element.getBoundingClientRect().top + window.pageYOffset - scrollOffset();

      window.scrollTo({
        top: Math.max(0, top),
        behavior: 'smooth',
      });
    };

    const scrollAfterRender = (element) => {
      requestAnimationFrame(() => {
        requestAnimationFrame(() => scrollToElement(element));
      });
    };

    const scrollWhenAvailable = (resolveElement, options = {}) => {
      const initial = resolveElement();

      if (initial) {
        scrollAfterRender(initial);
        options.onDone?.();
        return;
      }

      if (!document.body) {
        options.onDone?.();
        return;
      }

      let done = false;
      let observer = null;
      let timeoutId = null;

      const finish = (element = null) => {
        if (done) {
          return;
        }

        done = true;
        observer?.disconnect();

        if (timeoutId) {
          window.clearTimeout(timeoutId);
        }

        if (element) {
          scrollAfterRender(element);
        }

        options.onDone?.();
      };

      observer = new MutationObserver(() => {
        const element = resolveElement();

        if (element) {
          finish(element);
        }
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true,
      });

      timeoutId = window.setTimeout(() => finish(), options.timeoutMs ?? 1500);
    };

    const requestedTaskElement = () => {
      const hash = window.location.hash || '';

      if (!hash.startsWith('#task-')) {
        return null;
      }

      try {
        return document.getElementById(decodeURIComponent(hash.slice(1)));
      } catch (error) {
        return null;
      }
    };

    let taskScrollInFlight = false;

    const scrollRequestedTask = () => {
      const hash = window.location.hash || '';

      if (!hash.startsWith('#task-') || taskScrollInFlight) {
        return;
      }

      taskScrollInFlight = true;

      scrollWhenAvailable(requestedTaskElement, {
        onDone: () => {
          taskScrollInFlight = false;
        },
      });
    };

    window.w14ScrollRequestedTask = scrollRequestedTask;

    const rememberReturnCard = (event) => {
      const link = event.target.closest('[data-session-return-anchor]');
      if (!link) return;

      const card = link.closest('.session-card');
      if (!card?.id) return;

      try {
        sessionStorage.setItem(returnAnchorKey, card.id);
      } catch (error) {}
    };

    const restoreReturnCard = () => {
      let cardId = null;

      try {
        cardId = sessionStorage.getItem(returnAnchorKey);
        sessionStorage.removeItem(returnAnchorKey);
      } catch (error) {}

      if (!cardId) return;

      scrollWhenAvailable(() => document.getElementById(cardId));
    };

    const restoreBoardScrollTargets = () => {
      restoreReturnCard();
      scrollRequestedTask();
    };

    document.addEventListener('click', rememberReturnCard, true);
    window.addEventListener('pageshow', restoreBoardScrollTargets);
    document.addEventListener('livewire:navigated', restoreBoardScrollTargets);
    requestAnimationFrame(restoreBoardScrollTargets);
  })();
</script>
