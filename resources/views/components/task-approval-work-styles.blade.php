@once
  @push('styles')
    <style>
      .w14-approval-work {
        --w14-approval-soft: rgba(var(--bs-primary-rgb), 0.08);
        --w14-approval-soft-strong: rgba(var(--bs-primary-rgb), 0.14);
        --w14-approval-mint-bg: color-mix(in srgb, var(--bs-success) 16%, transparent);
        --w14-approval-mint-text: color-mix(in srgb, var(--bs-success) 82%, var(--bs-heading-color));
      }

      .w14-approval-work [x-cloak] {
        display: none !important;
      }

      .w14-approval-hero .card-body {
        align-items: center;
        display: grid;
        gap: 1.25rem;
        grid-template-columns: minmax(0, 1fr) auto;
      }

      .w14-approval-title {
        color: var(--bs-heading-color);
        font-size: clamp(1.65rem, 2.4vw, 2.35rem);
        font-weight: 700;
        line-height: 1.08;
        margin: 0.25rem 0 0;
        overflow-wrap: anywhere;
      }

      .w14-approval-eyebrow {
        color: var(--bs-primary);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0;
        text-transform: uppercase;
      }

      .w14-approval-copy {
        color: var(--bs-secondary-color);
        margin: 0.65rem 0 0;
        max-width: 48rem;
      }

      .w14-approval-hero__actions,
      .w14-approval-section__actions,
      .w14-approval-submit__actions {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: flex-end;
        max-width: 100%;
        min-width: 0;
      }

      .w14-approval-section .card-header {
        align-items: center;
        display: grid;
        gap: 1rem;
        grid-template-columns: minmax(0, 1fr) auto;
      }

      .w14-approval-section .card-header > * {
        max-width: 100%;
        min-width: 0;
      }

      .w14-approval-subject-toggle {
        color: var(--bs-heading-color);
        display: block;
        inline-size: 100%;
        max-width: 100%;
        min-width: 0;
        text-align: start !important;
      }

      .w14-approval-subject-toggle__inner {
        align-items: center;
        display: flex;
        gap: 0.5rem;
        inline-size: 100%;
        justify-content: flex-start;
        max-width: 100%;
        min-width: 0;
      }

      .w14-approval-subject-toggle__inner .avatar {
        flex: 0 0 auto;
      }

      .w14-approval-subject-title {
        display: block;
        flex: 1 1 auto;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
        overflow-wrap: normal;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .w14-approval-chevron {
        display: inline-flex;
        transition: transform 0.16s ease;
      }

      .w14-approval-chevron--open {
        transform: rotate(90deg);
      }

      .w14-approval-task-title {
        display: block;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .w14-approval-task {
        display: grid;
        column-gap: 0.75rem;
        row-gap: 0.75rem;
        grid-template-columns: auto minmax(0, 1fr) auto;
        padding: 1rem 1.25rem;
      }

      .w14-approval-task + .w14-approval-task {
        border-top: 1px solid var(--bs-border-color);
      }

      .w14-approval-task:hover {
        background: var(--w14-approval-soft);
      }

      .w14-approval-task__check {
        align-items: flex-start;
        display: flex;
        justify-content: center;
        padding-top: 0.2rem;
      }

      .w14-approval-task__check-label {
        align-items: center;
        cursor: pointer;
        display: inline-flex;
        justify-content: center;
        min-height: 2.75rem;
        min-width: 2.75rem;
      }

      .w14-approval-task__check-label .form-check-input {
        cursor: pointer;
        margin: 0;
      }

      .w14-approval-task__meta {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.5rem;
        min-width: 0;
        max-width: 100%;
      }

      .w14-approval-meta-badge {
        align-items: center;
        display: inline-flex;
        max-width: 100%;
        min-width: 0;
      }

      .w14-approval-meta-badge__text {
        display: block;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .w14-approval-task__controls {
        align-items: flex-start;
        align-self: start;
        display: flex;
        flex: 0 0 auto;
        flex-wrap: nowrap;
        gap: 0.35rem;
        justify-content: flex-end;
        max-width: 100%;
        min-width: 0;
      }

      .w14-approval-points-badge {
        background: var(--w14-approval-mint-bg) !important;
        color: var(--w14-approval-mint-text) !important;
        white-space: nowrap;
      }

      .w14-approval-selected-badge {
        background: var(--w14-approval-mint-bg) !important;
        color: var(--w14-approval-mint-text) !important;
      }

      .w14-approval-task__editor {
        display: grid;
        gap: 0.25rem;
        grid-column: 2 / -1;
        justify-items: start;
        min-width: 0;
      }

      .w14-approval-points-input {
        inline-size: 8rem;
      }

      .w14-approval-submit {
        position: sticky;
        bottom: 1rem;
        z-index: 2;
      }

      .w14-approval-empty {
        padding: clamp(2.5rem, 8vw, 5rem) 1rem;
        text-align: center;
      }

      .w14-approval-empty .avatar {
        height: 4rem;
        margin: 0 auto 1rem;
        width: 4rem;
      }

      .w14-approval-result-list {
        margin: 0.5rem 0 0;
        padding-inline-start: 1rem;
      }

      @media (max-width: 991.98px) {
        .w14-approval-hero .card-body,
        .w14-approval-section .card-header {
          grid-template-columns: 1fr;
        }

        .w14-approval-hero__actions,
        .w14-approval-section__actions,
        .w14-approval-submit__actions {
          justify-content: flex-start;
        }
      }

      @media (max-width: 575.98px) {
        .w14-approval-work {
          margin-inline-end: -0.5rem;
          margin-inline-start: -0.5rem;
        }

        .w14-approval-hero .card-body {
          align-items: stretch;
          gap: 1rem;
          padding: 1.25rem;
        }

        .w14-approval-hero__actions {
          align-items: center;
          display: flex;
          flex-wrap: wrap;
          justify-content: flex-start;
          max-width: 100%;
          min-width: 0;
          width: 100%;
        }

        .w14-approval-section__actions {
          align-items: stretch;
          display: grid;
          grid-template-columns: 1fr;
          max-width: 100%;
          min-width: 0;
          width: 100%;
        }

        .w14-approval-hero__actions .badge {
          justify-content: center;
          max-width: 100%;
          min-width: 0;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
          width: auto;
        }

        .w14-approval-hero__actions .btn,
        .w14-approval-section__actions .btn {
          flex: 1 1 100%;
          justify-content: center;
          min-width: 0;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
          width: 100%;
        }

        .w14-approval-back-btn,
        .w14-approval-subject-approve {
          grid-column: 1 / -1;
        }

        .w14-approval-task__meta {
          display: grid;
          grid-template-columns: minmax(0, 1fr);
        }

        .w14-approval-task {
          grid-template-columns: auto minmax(0, 1fr) auto;
          padding: 1rem;
        }

        .w14-approval-task__controls {
          justify-content: flex-end;
        }

        .w14-approval-task__editor {
          grid-column: 1 / -1;
        }

        .w14-approval-points-input,
        .w14-approval-submit__actions .btn {
          width: 100%;
        }

        .w14-approval-submit {
          bottom: 0.5rem;
        }
      }

      @media (max-width: 375.98px) {
        .w14-approval-work {
          margin-inline-end: -0.625rem;
          margin-inline-start: -0.625rem;
        }
      }
    </style>
  @endpush
@endonce
