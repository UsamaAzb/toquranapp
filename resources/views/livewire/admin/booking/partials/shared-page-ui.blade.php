@once
  <style>
    .queue-page-actions {
      align-items: center;
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
    }

    .queue-page-actions > .btn,
    .queue-page-actions > a {
      align-items: center;
      display: inline-flex;
      justify-content: center;
      min-height: 2.625rem;
      white-space: nowrap;
    }

    .queue-page-link {
      overflow: visible;
      position: relative;
    }

    .queue-page-link__badge {
      align-items: center;
      background: var(--bs-danger);
      border: 2px solid var(--bs-body-bg);
      border-radius: 999px;
      box-shadow: 0 10px 20px color-mix(in srgb, var(--bs-danger) 24%, transparent);
      color: #fff;
      display: inline-flex;
      font-size: 0.7rem;
      font-weight: 700;
      height: 1.35rem;
      inset-block-start: -0.55rem;
      inset-inline-end: -0.55rem;
      justify-content: center;
      line-height: 1;
      min-width: 1.35rem;
      padding: 0 0.3rem;
      pointer-events: none;
      position: absolute;
      z-index: 2;
    }

    .intake-info {
      display: inline-block;
      position: relative;
    }

    .intake-info--inline {
      vertical-align: middle;
    }

    .intake-info__trigger {
      align-items: center;
      background: transparent;
      border: 0;
      border-radius: 999px;
      color: var(--bs-secondary-color);
      cursor: pointer;
      display: inline-flex;
      justify-content: center;
      list-style: none;
      min-height: 1.75rem;
      min-width: 1.75rem;
      padding: 0.125rem;
    }

    .intake-info__trigger::-webkit-details-marker {
      display: none;
    }

    .intake-info[open] .intake-info__trigger,
    .intake-info__trigger:hover {
      color: var(--bs-primary);
    }

    .intake-info__panel {
      background: var(--bs-body-bg);
      border: 1px solid var(--bs-border-color);
      border-radius: 8px;
      box-shadow: 0 10px 24px rgba(15, 23, 42, 0.14);
      color: var(--bs-body-color);
      font-size: 0.8125rem;
      line-height: 1.45;
      max-width: min(24rem, calc(100vw - 2rem));
      min-width: 220px;
      padding: 0.625rem 0.75rem;
      position: absolute;
      right: 0;
      top: calc(100% + 0.4rem);
      z-index: 6;
    }

    .intake-info__panel--header {
      left: 0;
      right: auto;
    }

    .intake-info__panel--footer {
      direction: ltr;
      right: 0;
      text-align: left;
    }

    .intake-info__panel--ltr {
      direction: ltr;
      text-align: left;
    }

    @media (max-width: 575.98px) {
      .intake-info__panel,
      .intake-info__panel--header,
      .intake-info__panel--footer {
        left: 0;
        max-width: min(18rem, 80vw);
        right: auto;
      }
    }
  </style>

  <script>
    (() => {
      if (window.__intakeInfoClickAwayBound) {
        return;
      }

      window.__intakeInfoClickAwayBound = true;

      document.addEventListener('click', (event) => {
        document.querySelectorAll('.intake-info[open]').forEach((detail) => {
          if (!detail.contains(event.target)) {
            detail.removeAttribute('open');
          }
        });
      });
    })();
  </script>
@endonce
