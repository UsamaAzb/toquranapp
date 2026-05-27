@push('styles')
  @once
    @include('components.sessions.board-styles')

    <style>
      .automated-task-board {
        --w14-session-surface: var(--bs-paper-bg, var(--bs-card-bg));
        --w14-session-text: var(--bs-heading-color);
        --w14-session-muted: var(--bs-secondary-color);
        --w14-session-soft-border: color-mix(in sRGB, var(--bs-border-color) 76%, transparent);
        --w14-session-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.06);
      }

      [data-bs-theme="dark"] .automated-task-board {
        --w14-session-surface: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 92%, white 8%);
        --w14-session-muted: color-mix(in sRGB, var(--bs-heading-color) 76%, var(--bs-primary));
        --w14-session-soft-border: rgba(var(--bs-primary-rgb), 0.22);
        --w14-session-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.22);
      }

      .automated-task-board .nav-link {
        border-radius: 0.5rem;
      }

      .automated-task-board .template-card {
        border: 1px solid rgba(67, 89, 113, 0.12);
      }

      .automated-task-board .template-card + .template-card {
        margin-top: 1.5rem;
      }

      .automated-task-board .automation-template-header {
        background: rgba(67, 89, 113, 0.018);
        border-bottom: 1px solid rgba(67, 89, 113, 0.055);
        padding-block: 1rem;
      }

      [data-bs-theme="dark"] .automated-task-board .automation-template-header {
        background: rgba(255, 255, 255, 0.025);
        border-bottom-color: rgba(var(--bs-primary-rgb), 0.1);
      }

      .automated-task-board .automation-template-body {
        background: transparent;
        padding-block-start: 1rem;
      }

      .automated-task-board .automation-template-alert {
        margin-bottom: 1rem;
      }

      .automated-task-board .version-pill {
        align-items: center;
        background: transparent;
        border: 0;
        color: var(--bs-body-color);
        display: inline-flex;
        flex: 1 1 auto;
        justify-content: flex-start;
        max-width: 100%;
        min-width: 0;
        min-height: 2.375rem;
        padding: 0.5rem 0.7rem;
        text-align: left;
      }

      .automated-task-board .nav-pills .version-pill.active,
      .automated-task-board .nav-pills .show > .version-pill {
        background: transparent;
        box-shadow: none;
        color: var(--bs-heading-color);
      }

      @media (max-width: 767.98px) {
        .automated-task-board .version-pill {
          width: auto;
        }
      }

      .automated-task-board .version-pill-name {
        flex: 1 1 auto;
        min-width: 0;
        text-align: left;
      }

      .automated-task-board .version-pill-count,
      .automated-task-board .version-pill-status {
        flex: 0 0 auto;
      }

      .automated-task-board .version-nav-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        max-width: 100%;
        margin-inline: -0.25rem;
        padding-bottom: 0.25rem;
        padding-inline: 0.25rem;
        scrollbar-width: thin;
      }

      .automated-task-board .version-nav-scroll .nav {
        max-width: 100%;
        width: auto;
      }

      .automated-task-board .version-nav-scroll .nav-item {
        flex: 0 1 auto;
      }

      .automated-task-board .automation-version-summary {
        align-items: center;
        background: transparent;
        border: 1px solid rgba(67, 89, 113, 0.14);
        border-radius: 0.625rem;
        display: inline-flex;
        max-width: 100%;
        min-width: 9.5rem;
        overflow: hidden;
        transition: background-color 0.16s ease, border-color 0.16s ease, box-shadow 0.16s ease;
        width: auto;
      }

      .automated-task-board .automation-version-summary.is-active {
        background: color-mix(in sRGB, var(--bs-primary) 12%, var(--bs-body-bg));
        border-color: rgba(var(--bs-primary-rgb), 0.46);
        box-shadow: 0 0.375rem 1rem rgba(var(--bs-primary-rgb), 0.08);
      }

      .automated-task-board .automation-version-summary.is-active .version-pill-name {
        color: var(--bs-primary);
      }

      .automated-task-board .automation-version-count,
      .automated-task-board .automation-version-status {
        align-items: center;
        display: inline-flex;
        flex: 0 0 auto;
      }

      .automated-task-board .automation-version-assign {
        align-self: stretch;
        border-left: 1px solid rgba(67, 89, 113, 0.12);
        border-radius: 0;
        flex: 0 0 auto;
        min-height: 2.375rem;
        width: 2.25rem;
      }

      .automated-task-board .automation-version-summary.is-active .automation-version-assign {
        border-left-color: rgba(var(--bs-primary-rgb), 0.16);
      }

      .automated-task-board .automation-inline-signal {
        align-items: center;
        display: inline-flex;
        font-size: 0.8125rem;
        font-weight: 600;
        gap: 0.125rem;
        line-height: 1;
        white-space: nowrap;
      }

      .automated-task-board .automation-inline-signal i {
        font-size: 1rem;
      }

      .automated-task-board .automation-inline-signal--success {
        color: var(--bs-success);
      }

      .automated-task-board .automation-inline-signal--warning {
        color: var(--bs-warning);
      }

      .automated-task-board .automation-inline-signal--info {
        color: var(--bs-info);
      }

      .automated-task-board .automation-inline-signal--secondary {
        color: var(--bs-secondary-color);
      }

      .automated-task-board .automated-task-overlay {
        align-items: center;
        background: rgba(67, 89, 113, 0.58);
        display: flex;
        inset: 0;
        justify-content: center;
        overflow-y: auto;
        padding: 1.5rem;
        position: fixed;
        z-index: 1095;
      }

      .automated-task-board .automated-task-overlay-card {
        max-height: calc(100vh - 3rem);
        overflow: hidden;
        width: 100%;
      }

      .automated-task-board .automated-task-overlay-card > .card-body {
        overflow-y: auto;
      }

      .automated-task-board .automated-task-student-grid {
        display: grid;
        gap: 0.875rem;
        grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
      }

      .automated-task-board .automated-task-student-card {
        background: var(--bs-body-bg);
        border: 1px solid rgba(67, 89, 113, 0.14);
        border-radius: 1rem;
        height: 100%;
        min-width: 0;
        overflow: hidden;
        padding: 1rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
      }

      .automated-task-board .automated-task-student-card-header {
        align-items: flex-start;
        display: flex;
        gap: 0.75rem;
        justify-content: space-between;
        min-width: 0;
      }

      .automated-task-board .automated-task-student-copy {
        flex: 1 1 auto;
        min-width: 0;
        width: 0;
      }

      .automated-task-board .automated-task-student-name {
        display: block;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .automated-task-board .automated-task-student-meta {
        display: block;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .automated-task-board .automated-task-student-card.is-selected {
        border-color: rgba(var(--bs-primary-rgb), 0.45);
        box-shadow: 0 0.5rem 1.25rem rgba(var(--bs-primary-rgb), 0.12);
        transform: translateY(-1px);
      }

      .automated-task-board .automated-task-student-card .form-check-input {
        flex: 0 0 auto;
        height: 1.05rem;
        width: 1.05rem;
      }

      .automated-task-board .automated-task-section-card {
        border: 1px solid rgba(67, 89, 113, 0.12);
      }

      .automated-task-board .automated-task-section-header {
        align-items: flex-start;
        display: flex;
        gap: 0.75rem;
        justify-content: space-between;
        margin-bottom: 0.75rem;
      }

      .automated-task-board .automated-task-section-title-group {
        align-items: center;
        display: flex;
        flex: 1 1 auto;
        gap: 0.5rem;
        min-width: 0;
      }

      .automated-task-board .automated-task-section-title {
        margin-bottom: 0;
        min-width: 0;
      }

      .automated-task-board .automated-task-section-count {
        flex: 0 0 auto;
        margin-top: 0;
      }

      .automated-task-board .selection-scroll {
        max-height: 34rem;
        overflow-y: auto;
      }

      @media (max-width: 374.98px) {
        .automated-task-board .automated-task-overlay {
          align-items: stretch;
          padding: 0.75rem;
        }

        .automated-task-board .automated-task-overlay-card {
          max-height: calc(100vh - 1.5rem);
        }

        .automated-task-board .automated-task-student-grid {
          grid-template-columns: minmax(0, 1fr);
        }

        .automated-task-board .automated-task-student-card {
          padding: 0.875rem;
        }
      }

      .automated-task-board .atask-task-panel {
        border: 1px solid var(--w14-session-soft-border);
        border-radius: 0.5rem;
        overflow: hidden;
        background: var(--w14-session-surface);
        box-shadow: var(--w14-session-shadow);
      }

      .automated-task-board .atask-task-panel + .atask-task-panel {
        margin-top: 0.75rem;
      }

      .automated-task-board .atask-task-panel-inner {
        background: color-mix(in srgb, var(--bs-primary) 4%, var(--w14-session-surface));
        padding: 0.875rem 1rem;
      }

      .automated-task-board .atask-task-title-row {
        align-items: start;
        display: grid;
        gap: 0.65rem;
        grid-template-columns: minmax(0, 1fr) auto;
      }

      .automated-task-board .atask-task-identity {
        flex: 1 1 auto;
        min-width: 0;
      }

      .automated-task-board .atask-task-title-line {
        align-items: center;
        display: flex;
        flex-wrap: nowrap;
        gap: 0.35rem;
        min-height: 1.45rem;
      }

      .automated-task-board .atask-task-name {
        color: var(--w14-session-text);
        font-weight: 600;
        display: block;
        flex: 1 1 auto;
        font-size: 0.9375rem;
        line-height: 1.25;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .automated-task-board .atask-task-meta-line {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem 0.45rem;
        margin-top: 0.35rem;
        min-width: 0;
      }

      .automated-task-board .atask-task-type {
        align-items: center;
        display: inline-flex;
        flex: 0 1 auto;
        font-family: inherit;
        font-size: 0.75rem;
        font-weight: 600;
        height: 1.35rem;
        line-height: 1.05;
        max-width: min(100%, 9rem);
        min-width: 0;
        overflow: hidden;
        padding-block: 0;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .automated-task-board .atask-task-points {
        align-items: center;
        color: var(--w14-session-muted);
        display: inline-flex;
        flex: 0 0 auto;
        font-family: inherit;
        font-size: 0.75rem;
        font-weight: 600;
        min-height: 1.35rem;
        white-space: nowrap;
      }

      .automated-task-board .atask-task-description {
        color: var(--w14-session-muted);
        font-size: 0.875rem;
        margin-top: 0.375rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
      }

      .automated-task-board .atask-task-attachments {
        padding: 0 1rem 0.875rem;
      }

      .automated-task-board .atask-attachment-timeline {
        display: grid;
        gap: 0;
      }

      .automated-task-board .atask-attachment-group {
        display: grid;
        grid-template-columns: 1.5rem minmax(0, 1fr);
        gap: 0.75rem;
        padding: 0.85rem 0;
        position: relative;
      }

      .automated-task-board .atask-attachment-group::before {
        background: var(--w14-session-soft-border);
        bottom: -0.15rem;
        content: "";
        left: 0.45rem;
        position: absolute;
        top: 2rem;
        width: 1px;
      }

      .automated-task-board .atask-attachment-group:last-child::before {
        display: none;
      }

      .automated-task-board .atask-attachment-dot {
        border-radius: 999px;
        box-shadow: 0 0 0 4px var(--w14-session-surface);
        height: 0.78rem;
        margin-left: 0.09rem;
        margin-top: 0.32rem;
        width: 0.78rem;
        z-index: 1;
      }

      .automated-task-board .atask-attachment-dot--files {
        background: var(--bs-primary);
      }

      .automated-task-board .atask-attachment-dot--youtube {
        background: var(--bs-success);
      }

      .automated-task-board .atask-attachment-dot--links {
        background: var(--bs-danger);
      }

      .automated-task-board .atask-attachment-group-body {
        min-width: 0;
      }

      .automated-task-board .atask-attachment-group-head {
        align-items: center;
        display: flex;
        gap: 0.5rem;
        justify-content: space-between;
        margin-bottom: 0.6rem;
        min-width: 0;
      }

      .automated-task-board .atask-attachment-chip-wrap {
        min-width: 0;
      }

      .automated-task-board .atask-attachment-chip-wrap .session-attachment-chip,
      .automated-task-board .atask-version-task-panel .session-attachment-chip {
        max-width: min(100%, 15rem);
        width: auto;
      }

      .automated-task-board .automation-workspace-row {
        --automation-split-border: rgba(67, 89, 113, 0.1);
      }

      @media (min-width: 1200px) {
        .automated-task-board .automation-version-panel {
          border-left: 1px solid var(--automation-split-border);
          padding-left: 1rem;
        }
      }

      .automated-task-board .atask-task-action {
        align-items: center;
        display: inline-flex;
        flex: 0 0 auto;
        min-height: 2rem;
      }

      .automated-task-board .atask-task-actions {
        align-items: center;
        align-self: start;
        display: inline-flex;
        flex: 0 0 auto;
        gap: 0.2rem;
        min-height: 1.45rem;
      }

      .automated-task-board .atask-attachment-count {
        align-items: center;
        color: var(--bs-secondary-color);
        display: inline-flex;
        flex: 0 0 auto;
        font-family: inherit;
        font-size: 0.75rem;
        font-weight: 600;
        gap: 0.125rem;
        min-height: 1.35rem;
        min-width: 0;
        white-space: nowrap;
      }

      [data-bs-theme="dark"] .automated-task-board .atask-task-panel-inner {
        background: color-mix(in srgb, var(--bs-primary) 7%, var(--w14-session-surface));
      }

      .automated-task-board .atask-attachment-count i {
        color: var(--bs-secondary-color);
        font-size: 0.8125rem;
        line-height: 1;
      }

      .automated-task-board .atask-task-toggle i {
        transition: transform 0.2s ease;
      }

      .automated-task-board .atask-task-toggle[aria-expanded="false"] i {
        transform: rotate(-90deg);
      }

      .automated-task-board .atask-attachment-group-label {
        font-size: 0.75rem;
        letter-spacing: 0.02em;
      }

      .automated-task-board .automation-compact-header {
        align-items: center;
        display: grid;
        gap: 0.5rem;
        grid-template-columns: minmax(0, 1fr) auto;
        min-width: 0;
      }

      .automated-task-board .automation-compact-title {
        font-weight: 600;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .automated-task-board .automation-template-actions {
        align-items: center;
        display: inline-flex;
        flex: 0 0 auto;
        gap: 0.25rem;
      }

      .automated-task-board .automation-template-status-row {
        align-items: center;
        display: flex;
        flex-wrap: nowrap;
        gap: 0.4rem;
        margin-top: 0.55rem;
        min-width: 0;
        width: 100%;
      }

      .automated-task-board .automation-recurrence-text {
        color: var(--bs-secondary-color);
        display: inline-block;
        flex: 0 1 auto;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.2;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .automated-task-board .automation-template-state {
        align-items: center;
        display: inline-flex;
        flex: 0 0 auto;
        font-size: 0.875rem;
        line-height: 1;
      }

      .automated-task-board .automation-template-state--warning {
        color: var(--bs-warning);
      }

      .automated-task-board .automation-template-state--secondary {
        color: var(--bs-secondary-color);
      }

      .automated-task-board .automation-settings-trigger {
        flex: 0 0 auto;
      }

      .automated-task-board .automation-template-toggle i {
        transition: transform 0.16s ease;
      }

      .automated-task-board .automation-template-toggle.is-expanded i {
        transform: rotate(180deg);
      }

      .automated-task-board .automation-settings-dialog {
        max-width: min(38rem, calc(100vw - 1.5rem));
      }

      .automated-task-board .automation-create-panel {
        background: var(--bs-body-bg);
        border: 1px solid rgba(67, 89, 113, 0.12);
        border-radius: 0.5rem;
        padding: 1rem;
      }

      .automated-task-board .automation-version-panel {
        min-width: 0;
      }

      .automated-task-board .automation-main-tasks-panel {
        min-width: 0;
      }

      .automated-task-board .automation-version-panel-head,
      .automated-task-board .automation-main-tasks-panel-head {
        align-items: center;
        border-bottom: 1px solid rgba(67, 89, 113, 0.1);
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-bottom: 1rem;
        min-height: 2.25rem;
        padding-block: 0.9375rem;
      }

      .automated-task-board .automation-version-panel-title,
      .automated-task-board .automation-main-tasks-panel-title {
        align-items: center;
        display: flex;
        gap: 0.5rem;
        line-height: 1;
        min-width: 0;
      }

      .automated-task-board .automation-version-panel-title .card-title,
      .automated-task-board .automation-main-tasks-panel-title .card-title {
        align-items: center;
        display: flex;
        line-height: 1.35;
        margin-bottom: 0;
        min-height: 1.75rem;
      }

      .automated-task-board .automation-version-panel-title .intake-info,
      .automated-task-board .automation-main-tasks-panel-title .intake-info {
        align-items: center;
        display: inline-flex;
        line-height: 1;
      }

      .automated-task-board .automation-version-panel-title .intake-info__trigger,
      .automated-task-board .automation-main-tasks-panel-title .intake-info__trigger {
        min-height: 1.75rem;
        min-width: 1.75rem;
      }

      .automated-task-board .automation-version-panel-head > .d-flex,
      .automated-task-board .automation-main-tasks-panel-head > .d-flex {
        align-items: center;
      }

      .automated-task-board .automation-version-detail {
        border-bottom: 1px solid rgba(67, 89, 113, 0.12);
        margin-bottom: 1rem;
        padding: 0 0 0.65rem;
      }

      .automated-task-board .automation-version-title-row {
        align-items: center;
        display: flex;
        gap: 0.5rem;
        justify-content: space-between;
        min-height: 2.25rem;
      }

      .automated-task-board .automation-version-title-input {
        background: transparent;
        border: 0;
        border-bottom: 1px solid transparent;
        border-radius: 0;
        color: var(--bs-heading-color);
        display: block;
        flex: 1 1 auto;
        font-size: 1.05rem;
        font-weight: 600;
        line-height: 1.35;
        min-width: 0;
        overflow: hidden;
        padding: 0;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 100%;
      }

      .automated-task-board .automation-version-title-input:focus {
        border-bottom-color: rgba(var(--bs-primary-rgb), 0.42);
        box-shadow: none;
        outline: 0;
      }

      .automated-task-board .automation-version-title-input[disabled] {
        color: var(--bs-heading-color);
        opacity: 1;
      }

      @media (max-width: 575.98px) {
        .automated-task-board .automation-version-panel {
          margin-inline: 0;
        }

        .automated-task-board .automation-main-tasks-panel {
          margin-inline: 0;
        }

        .automated-task-board .version-nav-scroll {
          margin-inline: 0;
          overflow-x: visible;
          padding-inline: 0;
        }

        .automated-task-board .version-nav-scroll .nav {
          flex-direction: column !important;
          max-width: 100%;
          width: 100%;
        }

        .automated-task-board .version-nav-scroll .nav-item {
          flex-basis: auto;
          margin-right: 0 !important;
          width: 100%;
        }

        .automated-task-board .automation-version-summary {
          max-width: 100%;
          width: 100%;
        }

        .automated-task-board .atask-attachment-chip-wrap .session-attachment-chip,
        .automated-task-board .atask-version-task-panel .session-attachment-chip {
          max-width: min(100%, 15rem);
          width: min(100%, 15rem);
        }

        .automated-task-board .automation-version-panel-head,
        .automated-task-board .automation-main-tasks-panel-head,
        .automated-task-board .automation-version-detail {
          padding-inline: 0;
        }

        .automated-task-board .automation-version-panel-title,
        .automated-task-board .automation-main-tasks-panel-title {
          align-items: flex-start;
        }

        .automated-task-board .automation-version-panel-title .card-title,
        .automated-task-board .automation-main-tasks-panel-title .card-title {
          line-height: 1.35;
        }

        .automated-task-board .automation-compact-header {
          align-items: start;
          gap: 0.5rem;
        }

        .automated-task-board .automation-template-header {
          padding: 0.875rem 1rem;
        }

        .automated-task-board .automation-template-body {
          padding: 0.75rem 0.65rem 0.9rem;
        }

        .automated-task-board .automation-template-actions {
          gap: 0.125rem;
        }

        .automated-task-board .version-pill {
          min-width: 0;
        }

        .automated-task-board .selection-scroll {
          padding-right: 0 !important;
        }

        .automated-task-board .atask-version-task-panel {
          border-left: 0;
          border-radius: 0;
          border-right: 0;
          box-shadow: none;
          margin-inline: 0;
        }

        .automated-task-board .atask-version-task-header {
          padding-inline: 0.75rem;
        }

        .automated-task-board .atask-task-panel {
          border-left: 0;
          border-radius: 0;
          border-right: 0;
          box-shadow: none;
        }

        .automated-task-board .atask-task-panel-inner {
          padding-inline: 0.75rem;
        }
      }

      .automated-task-board [x-cloak] {
        display: none !important;
      }

      @media (max-width: 374.98px) {
        .automated-task-board .atask-task-panel-inner {
          padding: 0.75rem 0.65rem;
        }

        .automated-task-board .atask-task-title-row {
          gap: 0.45rem;
          grid-template-columns: minmax(0, 1fr) auto;
        }

        .automated-task-board .atask-task-meta-line {
          gap: 0.25rem 0.4rem;
        }

        .automated-task-board .atask-task-type {
          max-width: 6.75rem;
        }

        .automated-task-board .atask-task-action {
          min-height: 1.85rem;
          min-width: 1.85rem;
          padding: 0;
        }

        .automated-task-board .atask-task-attachments {
          padding: 0 0.75rem 0.75rem;
        }

        .automated-task-board .atask-attachment-group {
          gap: 0.55rem;
          grid-template-columns: 1.15rem minmax(0, 1fr);
        }

        .automated-task-board .atask-attachment-group::before {
          left: 0.34rem;
        }
      }

      .automated-task-board .atask-version-task-panel {
        border: 1px solid var(--w14-session-soft-border);
        border-radius: 0.5rem;
        overflow: hidden;
        background: var(--w14-session-surface);
        box-shadow: var(--w14-session-shadow);
        margin-bottom: 0.75rem;
      }

      .automated-task-board .atask-version-task-panel + .atask-version-task-panel {
        margin-top: 0.75rem;
      }

      .automated-task-board .atask-version-task-header {
        padding: 0.875rem 1rem;
      }

      .automated-task-board .atask-version-task-shell {
        align-items: flex-start;
        display: flex;
        gap: 1rem;
        justify-content: space-between;
      }

      .automated-task-board .atask-version-task-main {
        flex: 1 1 auto;
        min-width: 0;
      }

      .automated-task-board .atask-version-task-title-line {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        min-height: 2.25rem;
      }

      .automated-task-board .atask-version-task-check {
        align-items: center;
        display: inline-flex;
        gap: 0.5rem;
        margin: 0;
        min-width: 0;
        padding-left: 0;
      }

      .automated-task-board .atask-version-task-check .form-check-input {
        flex: 0 0 auto;
        float: none;
        margin: 0;
      }

      .automated-task-board .atask-version-task-check .form-check-label {
        min-width: 0;
      }

      .automated-task-board .atask-version-task-action {
        align-items: center;
        display: flex;
        flex: 0 0 auto;
        min-height: 2.25rem;
      }

      @media (max-width: 575.98px) {
        .automated-task-board .atask-version-task-shell {
          flex-direction: column;
          gap: 0.75rem;
        }

        .automated-task-board .atask-version-task-action {
          min-height: 0;
        }
      }

      .intake-info {
        display: inline-block;
        position: relative;
      }

      .intake-info--inline {
        vertical-align: middle;
      }

      .intake-info--label .intake-info__trigger {
        min-height: 1rem;
        min-width: 1rem;
        padding: 0;
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
        top: calc(100% + 0.375rem);
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
      }
    </style>

    <script>
      (() => {
        if (window.__intakeInfoClickAwayBound) return;
        window.__intakeInfoClickAwayBound = true;
        document.addEventListener('click', (event) => {
          document.querySelectorAll('.intake-info[open]').forEach((detail) => {
            if (!detail.contains(event.target)) detail.removeAttribute('open');
          });
        });
      })();
    </script>
  @endonce
@endpush

@php
  $weekdayOptions = [
    'mon' => 'Mon',
    'tue' => 'Tue',
    'wed' => 'Wed',
    'thu' => 'Thu',
    'fri' => 'Fri',
    'sat' => 'Sat',
    'sun' => 'Sun',
  ];
@endphp

<div class="automated-task-board">
  <div class="card mb-6">
    <div class="card-header">
      <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
          <h5 class="card-title mb-0">Versioned Routines</h5>
          <details class="intake-info intake-info--inline">
            <summary class="intake-info__trigger" aria-label="About Versioned Routines">
              <i class="icon-base ti tabler-info-circle icon-18px"></i>
            </summary>
            <div class="intake-info__panel">
              Author one template, prepare versions, assign students, and publish only when the participating versions are ready.
            </div>
          </details>
        </div>
        <ul class="nav nav-pills flex-column flex-md-row flex-wrap gap-2 mb-0">
          <li class="nav-item">
            <a
              href="{{ $this->scopeUrl('working') }}"
              class="nav-link {{ $templateScope === 'working' ? 'active' : 'bg-label-light' }}">
              Working queue
              <span class="badge bg-label-primary ms-2">{{ $scopeCounts['working'] }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a
              href="{{ $this->scopeUrl('archived') }}"
              class="nav-link {{ $templateScope === 'archived' ? 'active' : 'bg-label-light' }}">
              Archived
              <span class="badge bg-label-secondary ms-2">{{ $scopeCounts['archived'] }}</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <div class="card-body">
      @if($boardFeedback)
        <div class="alert alert-{{ $boardFeedback['tone'] }} alert-dismissible fade show mb-4" role="alert">
          {{ $boardFeedback['message'] }}
          <button type="button" class="btn-close" wire:click="dismissBoardFeedback" aria-label="Dismiss notice"></button>
        </div>
      @endif

      <details class="mb-4">
        <summary class="text-primary fw-semibold">Teacher notes and guardrails</summary>
        <div class="small text-muted mt-2">
          Draft and active templates stay in the working queue. Archive removes clutter without deleting subscriptions, assignments, or generation history. Version changes only affect new generations, and publish checks focus on versions that currently have active subscribed students assigned.
        </div>
      </details>

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
          <div class="fw-semibold">Versioned Routines</div>
          <div class="small text-muted">Create recurring routine tracks, then expand only the routine you want to edit.</div>
        </div>
        <button
          type="button"
          class="btn btn-primary rounded-pill"
          wire:click="toggleCreateTemplateForm"
          aria-expanded="{{ $createTemplateOpen ? 'true' : 'false' }}">
          <i class="icon-base ti tabler-plus me-1"></i>{{ $createTemplateOpen ? 'Close create form' : 'Create Routine' }}
        </button>
      </div>

      @if($createTemplateOpen)
        <div class="automation-create-panel mt-4">
          <div class="row g-3 align-items-end">
            <div class="col-lg-4">
              <label class="form-label" for="draft-template-title">Template title</label>
              <input type="text" id="draft-template-title" class="form-control" wire:model.live="draftTemplate.title" placeholder="Reading cycle">
            </div>
            <div class="col-lg-2">
              <label class="form-label" for="draft-template-recurrence">Recurrence</label>
              <select id="draft-template-recurrence" class="form-select" wire:model.live="draftTemplate.recurrence_kind">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
              </select>
            </div>
            <div class="col-lg-2">
              @if($draftTemplate['recurrence_kind'] === 'daily')
                <div class="d-flex align-items-center gap-1 mb-2">
                  <label class="form-label mb-0" for="draft-template-interval">Interval</label>
                  <details class="intake-info intake-info--inline intake-info--label">
                    <summary class="intake-info__trigger" aria-label="Interval applies to daily only">
                      <i class="icon-base ti tabler-info-circle icon-16px"></i>
                    </summary>
                    <div class="intake-info__panel">Applies to daily recurrence only.</div>
                  </details>
                </div>
                <input type="number" min="1" max="12" id="draft-template-interval" class="form-control" wire:model.live="draftTemplate.recurrence_interval">
              @else
                <label class="form-label">&nbsp;</label>
                <div class="form-control bg-label-light" disabled>Every 1 {{ $draftTemplate['recurrence_kind'] === 'weekly' ? 'week' : 'month' }}</div>
              @endif
            </div>
            <div class="col-lg-4">
              @if($draftTemplate['recurrence_kind'] === 'weekly')
                <label class="form-label">Weekdays</label>
                <div class="d-flex flex-wrap gap-2">
                  @foreach($weekdayOptions as $weekdayValue => $weekdayLabel)
                    <label class="badge bg-label-primary rounded-pill px-3 py-2">
                      <input type="checkbox" class="form-check-input me-2" value="{{ $weekdayValue }}" wire:model.live="draftTemplate.recurrence_weekdays">
                      {{ $weekdayLabel }}
                    </label>
                  @endforeach
                </div>
              @elseif($draftTemplate['recurrence_kind'] === 'monthly')
                <label class="form-label" for="draft-template-day-of-month">Day of month</label>
                <input type="number" min="1" max="31" id="draft-template-day-of-month" class="form-control" wire:model.live="draftTemplate.recurrence_day_of_month">
                <small class="text-muted">If this day does not exist in a month, the task runs on the last day of that month.</small>
              @else
                <label class="form-label">Schedule</label>
                <div class="form-control bg-label-light">Runs every {{ (int) ($draftTemplate['recurrence_interval'] ?? 1) }} day(s).</div>
              @endif
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button type="button" class="btn btn-primary rounded-pill" wire:click="createTemplate">
                Create template
              </button>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>

  @forelse($templates as $template)
    @php
      $activeVersionId = $activeVersionByTemplate[$template->id] ?? $template->versions->first()?->id;
      $activeVersion = $template->versions->firstWhere('id', $activeVersionId) ?? $template->versions->first();
      $templateForm = $templateForms[$template->id] ?? [];
      $isArchivedTemplate = $template->isArchived();
      $displayTitle = $isArchivedTemplate ? $template->title : ($templateForm['title'] ?? $template->title);
      $displayKind = $isArchivedTemplate ? $template->recurrence_kind : ($templateForm['recurrence_kind'] ?? 'daily');
      $displayInterval = (int) ($isArchivedTemplate ? ($template->recurrence_interval ?? 1) : ($templateForm['recurrence_interval'] ?? 1));
      $displayDayOfMonth = $isArchivedTemplate ? $template->recurrence_day_of_month : ($templateForm['recurrence_day_of_month'] ?? 1);
      $displayWeekdays = collect($isArchivedTemplate
          ? explode(',', (string) $template->recurrence_weekdays)
          : ($templateForm['recurrence_weekdays'] ?? []))
        ->filter()
        ->map(fn ($day) => [
          '0' => 'Sun',
          '1' => 'Mon',
          '2' => 'Tue',
          '3' => 'Wed',
          '4' => 'Thu',
          '5' => 'Fri',
          '6' => 'Sat',
          'sun' => 'Sun',
          'mon' => 'Mon',
          'tue' => 'Tue',
          'wed' => 'Wed',
          'thu' => 'Thu',
          'fri' => 'Fri',
          'sat' => 'Sat',
        ][strtolower($day)] ?? ucfirst((string) $day))
        ->implode(', ');
    @endphp

    <div class="card template-card mb-6" wire:key="template-{{ $template->id }}" x-data="{ expanded: @js(isset($expandedTemplates[$template->id])) }">
      <div class="card-header automation-template-header">
        <div class="min-width-0">
          <div class="automation-compact-header">
            <span class="automation-compact-title" title="{{ $displayTitle }}">{{ $displayTitle }}</span>
            <div class="automation-template-actions">
              @if($template->isArchived())
                <button
                  type="button"
                  class="btn btn-sm btn-outline-secondary rounded-pill"
                  wire:click="restoreTemplate({{ $template->id }})"
                  wire:loading.attr="disabled"
                  wire:target="restoreTemplate({{ $template->id }})">
                  <span wire:loading.remove wire:target="restoreTemplate({{ $template->id }})">Restore</span>
                  <span wire:loading wire:target="restoreTemplate({{ $template->id }})"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span></span>
                </button>
              @else
                <button
                  type="button"
                  class="btn btn-sm btn-icon btn-text-secondary automation-settings-trigger"
                  wire:click="openSettings({{ $template->id }})"
                  aria-label="Template settings">
                  <i class="icon-base ti tabler-settings icon-20px"></i>
                </button>
              @endif
              <div class="dropdown">
                <button type="button" class="btn btn-sm btn-icon btn-text-secondary" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Template actions">
                  <i class="icon-base ti tabler-dots-vertical icon-20px"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width: 0">
                  @unless($template->isArchived())
                    <li>
                      <button type="button" class="dropdown-item text-primary" wire:click="saveTemplate({{ $template->id }})" wire:loading.attr="disabled" wire:target="saveTemplate({{ $template->id }})">
                        <span wire:loading.remove wire:target="saveTemplate({{ $template->id }})"><i class="icon-base ti tabler-device-floppy me-2"></i>Save details</span>
                        <span wire:loading wire:target="saveTemplate({{ $template->id }})"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...</span>
                      </button>
                    </li>
                  @endunless
                  @if($template->isDraft())
                    <li>
                      <button type="button" class="dropdown-item text-success" wire:click="publishTemplate({{ $template->id }})" wire:loading.attr="disabled" wire:target="publishTemplate({{ $template->id }})">
                        <span wire:loading.remove wire:target="publishTemplate({{ $template->id }})"><i class="icon-base ti tabler-cloud-upload me-2"></i>Publish</span>
                        <span wire:loading wire:target="publishTemplate({{ $template->id }})"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Publishing...</span>
                      </button>
                    </li>
                  @endif
                  @if($template->isActive())
                    <li>
                      <button type="button" class="dropdown-item text-warning" wire:click="sendTemplateToDraft({{ $template->id }})" wire:loading.attr="disabled" wire:target="sendTemplateToDraft({{ $template->id }})">
                        <span wire:loading.remove wire:target="sendTemplateToDraft({{ $template->id }})"><i class="icon-base ti tabler-file-pencil me-2"></i>Return to draft</span>
                        <span wire:loading wire:target="sendTemplateToDraft({{ $template->id }})"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Moving...</span>
                      </button>
                    </li>
                  @endif
                  <li>
                    <button type="button" class="dropdown-item text-info" wire:click="openHistoryPanel({{ $template->id }})" wire:loading.attr="disabled" wire:target="openHistoryPanel({{ $template->id }})">
                      <i class="icon-base ti tabler-history me-2"></i>View generated history
                    </button>
                  </li>
                  @unless($template->isArchived())
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <button type="button" class="dropdown-item text-danger" wire:click="archiveTemplate({{ $template->id }})" wire:loading.attr="disabled" wire:target="archiveTemplate({{ $template->id }})">
                        <span wire:loading.remove wire:target="archiveTemplate({{ $template->id }})"><i class="icon-base ti tabler-archive me-2"></i>Archive</span>
                        <span wire:loading wire:target="archiveTemplate({{ $template->id }})"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Archiving...</span>
                      </button>
                    </li>
                  @endunless
                </ul>
              </div>
              <button
                type="button"
                class="btn btn-sm btn-icon btn-text-secondary automation-template-toggle"
                :class="{ 'is-expanded': expanded }"
                @click="expanded = ! expanded"
                :aria-expanded="expanded.toString()"
                aria-label="Toggle template details">
                <i class="icon-base ti tabler-chevron-down icon-20px"></i>
              </button>
            </div>
          </div>
          <div class="automation-template-status-row">
            <span class="automation-recurrence-text" title="@if($displayKind === 'weekly'){{ $displayWeekdays !== '' ? 'Weekly - '.$displayWeekdays : 'Weekly' }}@elseif($displayKind === 'monthly')Monthly - Day {{ $displayDayOfMonth ?? 1 }}@else Every {{ $displayInterval }} day(s) @endif">
              @if($displayKind === 'weekly')
                {{ $displayWeekdays !== '' ? 'Weekly - '.$displayWeekdays : 'Weekly' }}
              @elseif($displayKind === 'monthly')
                Monthly - Day {{ $displayDayOfMonth ?? 1 }}
              @else
                Every {{ $displayInterval }} day(s)
              @endif
            </span>
            @if($template->isActive())
              @include('livewire.teacher.automated-task-validation-badge', ['passes' => true, 'messages' => [], 'bare' => true])
            @elseif($template->isArchived())
              <span class="automation-template-state automation-template-state--secondary" title="Archived" aria-label="Archived">
                <i class="icon-base ti tabler-archive"></i>
              </span>
            @else
              <span class="automation-template-state automation-template-state--warning" title="Draft" aria-label="Draft">
                <i class="icon-base ti tabler-file-pencil"></i>
              </span>
            @endif
          </div>
        </div>
      </div>

      @if(isset($settingsOpen[$template->id]))
        <div class="automated-task-overlay" tabindex="-1" wire:keydown.escape="closeSettings({{ $template->id }})" x-data x-init="setTimeout(() => $el.focus(), 50)">
          <div class="card automated-task-overlay-card automation-settings-dialog" role="dialog" aria-modal="true" aria-labelledby="template-settings-title-{{ $template->id }}">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
              <div class="min-width-0">
                <h5 id="template-settings-title-{{ $template->id }}" class="card-title mb-1">Template settings</h5>
                <div class="small text-muted text-truncate" title="{{ $displayTitle }}">{{ $displayTitle }}</div>
              </div>
              <button type="button" class="btn btn-text-secondary rounded-pill btn-icon" wire:click="closeSettings({{ $template->id }})" aria-label="Close settings">
                <i class="icon-base ti tabler-x icon-18px"></i>
              </button>
            </div>

            <div class="card-body">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label" for="template-{{ $template->id }}-title">Template title</label>
                  @if($isArchivedTemplate)
                    <input type="text" id="template-{{ $template->id }}-title" class="form-control" value="{{ $template->title }}" disabled>
                  @else
                    <input type="text" id="template-{{ $template->id }}-title" class="form-control" wire:model.live="templateForms.{{ $template->id }}.title">
                  @endif
                </div>

                <div class="col-12 col-md-6">
                  <label class="form-label" for="template-{{ $template->id }}-recurrence">Recurrence</label>
                  @if($isArchivedTemplate)
                    <select id="template-{{ $template->id }}-recurrence" class="form-select" disabled>
                      <option>{{ ucfirst((string) $template->recurrence_kind) }}</option>
                    </select>
                  @else
                    <select id="template-{{ $template->id }}-recurrence" class="form-select" wire:model.live="templateForms.{{ $template->id }}.recurrence_kind">
                      <option value="daily">Daily</option>
                      <option value="weekly">Weekly</option>
                      <option value="monthly">Monthly</option>
                    </select>
                  @endif
                </div>

                <div class="col-12 col-md-6">
                  @if($displayKind === 'daily')
                    <label class="form-label" for="template-{{ $template->id }}-interval">Interval</label>
                    @if($isArchivedTemplate)
                      <input type="number" min="1" max="12" id="template-{{ $template->id }}-interval" class="form-control" value="{{ $displayInterval }}" disabled>
                    @else
                      <input type="number" min="1" max="12" id="template-{{ $template->id }}-interval" class="form-control" wire:model.live="templateForms.{{ $template->id }}.recurrence_interval">
                    @endif
                  @else
                    <label class="form-label">Interval</label>
                    <div class="form-control bg-label-light" disabled>Every 1 {{ $displayKind === 'weekly' ? 'week' : 'month' }}</div>
                  @endif
                </div>

                <div class="col-12">
                  @if($displayKind === 'weekly')
                    <label class="form-label">Weekdays</label>
                    @if($isArchivedTemplate)
                      <div class="form-control bg-label-light">{{ $displayWeekdays !== '' ? $displayWeekdays : 'No weekdays selected' }}</div>
                    @else
                      <div class="d-flex flex-wrap gap-2">
                        @foreach($weekdayOptions as $weekdayValue => $weekdayLabel)
                          <label class="badge bg-label-primary rounded-pill px-3 py-2">
                            <input type="checkbox" class="form-check-input me-2" value="{{ $weekdayValue }}" wire:model.live="templateForms.{{ $template->id }}.recurrence_weekdays">
                            {{ $weekdayLabel }}
                          </label>
                        @endforeach
                      </div>
                    @endif
                  @elseif($displayKind === 'monthly')
                    <label class="form-label" for="template-{{ $template->id }}-day-of-month">Day of month</label>
                    @if($isArchivedTemplate)
                      <input type="number" min="1" max="31" id="template-{{ $template->id }}-day-of-month" class="form-control" value="{{ $displayDayOfMonth }}" disabled>
                    @else
                      <input type="number" min="1" max="31" id="template-{{ $template->id }}-day-of-month" class="form-control" wire:model.live="templateForms.{{ $template->id }}.recurrence_day_of_month">
                    @endif
                    <small class="text-muted">If this day does not exist in a month, the task runs on the last day of that month.</small>
                  @else
                    <label class="form-label">Schedule</label>
                    <div class="form-control bg-label-light">Runs every {{ $displayInterval }} day(s).</div>
                  @endif
                </div>
              </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-text-secondary rounded-pill" wire:click="closeSettings({{ $template->id }})">Close</button>
              @unless($isArchivedTemplate)
                <button type="button" class="btn btn-primary rounded-pill" wire:click="saveTemplate({{ $template->id }})" wire:loading.attr="disabled" wire:target="saveTemplate({{ $template->id }})">
                  <span wire:loading.remove wire:target="saveTemplate({{ $template->id }})">Save settings</span>
                  <span wire:loading wire:target="saveTemplate({{ $template->id }})"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...</span>
                </button>
              @endunless
            </div>
          </div>
        </div>
      @endif

      <div class="card-body automation-template-body" x-show="expanded" x-cloak>
          @if(! empty($publishErrors[$template->id]))
            <div class="alert alert-warning d-flex flex-column gap-2 automation-template-alert">
              <div class="fw-semibold">Publish is blocked until these issues are fixed</div>
              <ul class="mb-0 ps-3">
                @foreach($publishErrors[$template->id] as $message)
                  <li>{{ $message }}</li>
                @endforeach
              </ul>
            </div>
          @endif

        <div class="row g-3 automation-workspace-row">
          <div class="col-12 col-xl-4">
            <section class="automation-main-tasks-panel">
              <div class="automation-main-tasks-panel-head">
                <div class="automation-main-tasks-panel-title">
                  <h6 class="card-title mb-0">Main tasks</h6>
                  <details class="intake-info intake-info--inline">
                    <summary class="intake-info__trigger" aria-label="About Main tasks">
                      <i class="icon-base ti tabler-info-circle icon-18px"></i>
                    </summary>
                    <div class="intake-info__panel">
                      Author the reusable task library once, then decide which version includes which task.
                    </div>
                  </details>
                </div>
                <button
                  type="button"
                  class="btn btn-sm btn-icon btn-text-primary"
                  wire:click="openMainTaskModal({{ $template->id }})"
                  aria-label="Add main task">
                  <i class="icon-base ti tabler-plus icon-20px"></i>
                </button>
              </div>
              <div>
                @forelse($template->mainTasks as $mainTask)
                  @php
                    $mainTaskAttachmentCount = $mainTask->attachments->count();
                    $mainTaskAttachmentCountLabel = $mainTaskAttachmentCount > 999 ? '999+' : (string) $mainTaskAttachmentCount;
                  @endphp
                    <div class="atask-task-panel" wire:key="main-task-{{ $mainTask->id }}">
                      <div class="atask-task-panel-inner">
                        <div class="atask-task-title-row">
                          <div class="atask-task-identity">
                            <div class="atask-task-title-line">
                              <span class="atask-task-name" title="{{ $mainTask->title }}">{{ $mainTask->title }}</span>
                            </div>
                            <div class="atask-task-meta-line">
                              @if($mainTask->taskType)
                                <span class="badge bg-label-info rounded-pill atask-task-type" title="{{ $mainTask->taskType->title }}">{{ $mainTask->taskType->title }}</span>
                              @endif
                              @if($mainTask->attachments->isNotEmpty())
                                <span class="atask-attachment-count" title="{{ $mainTaskAttachmentCount }} attachment{{ $mainTaskAttachmentCount === 1 ? '' : 's' }}" aria-label="{{ $mainTaskAttachmentCount }} attachment{{ $mainTaskAttachmentCount === 1 ? '' : 's' }}">
                                  <i class="icon-base ti tabler-paperclip"></i>{{ $mainTaskAttachmentCountLabel }}
                                </span>
                              @endif
                              <span class="atask-task-points">
                                {{ (int) ($mainTask->default_points ?? 0) }} / {{ (int) ($mainTask->max_points ?? 0) }} pts
                              </span>
                            </div>
                          </div>
                          <div class="atask-task-actions">
                            <button type="button"
                                    class="btn btn-text-secondary rounded-pill btn-icon atask-task-action"
                                    wire:click="openMainTaskModal({{ $template->id }}, {{ $mainTask->id }})"
                                    aria-label="Edit main task">
                              <i class="ti tabler-edit"></i>
                            </button>
                            <button type="button"
                                    class="btn btn-sm btn-icon btn-text-secondary atask-task-toggle atask-task-action"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#main-task-body-{{ $mainTask->id }}"
                                    aria-expanded="false"
                                    aria-label="Toggle task details">
                              <i class="icon-base ti tabler-chevron-down icon-16px"></i>
                            </button>
                          </div>
                        </div>
                      </div>

                      <div id="main-task-body-{{ $mainTask->id }}" class="collapse">
                      @if(filled($mainTask->description))
                        <div class="atask-task-description" style="padding:0 1rem;">{{ $mainTask->description }}</div>
                      @endif

                      @if($mainTask->attachments->isNotEmpty())
                        @php
                          $groupedFiles = $mainTask->attachments->where('type', 'file');
                          $groupedLinks = $mainTask->attachments->where('type', 'link');
                          $groupedYoutubes = $mainTask->attachments->where('type', 'youtube');
                        @endphp

                        <div class="atask-task-attachments atask-attachment-timeline">
                          @if($groupedFiles->isNotEmpty())
                            <div class="atask-attachment-group">
                              <span class="atask-attachment-dot atask-attachment-dot--files" aria-hidden="true"></span>
                              <div class="atask-attachment-group-body">
                                <div class="atask-attachment-group-head">
                                  <span class="fw-semibold">Files</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 atask-attachment-chip-wrap">
                                  @foreach($groupedFiles as $attachment)
                                    <x-sessions.attachment-chip
                                      :attachment="[
                                        'id' => $attachment->id,
                                        'type' => $attachment->type,
                                        'name' => $attachment->title ?: 'Attachment',
                                        'path' => $attachment->path ?? $attachment->url ?? '',
                                        'url' => $attachment->isFile()
                                          ? \Illuminate\Support\Facades\Storage::disk('public')->url((string) $attachment->path)
                                          : (string) ($attachment->url ?? $attachment->path),
                                      ]"
                                      :template-id="$template->id"
                                      :variant-index="$loop->index" />
                                  @endforeach
                                </div>
                              </div>
                            </div>
                          @endif

                          @if($groupedYoutubes->isNotEmpty())
                            <div class="atask-attachment-group">
                              <span class="atask-attachment-dot atask-attachment-dot--youtube" aria-hidden="true"></span>
                              <div class="atask-attachment-group-body">
                                <div class="atask-attachment-group-head">
                                  <span class="fw-semibold">YouTube</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 atask-attachment-chip-wrap">
                                  @foreach($groupedYoutubes as $attachment)
                                    <x-sessions.attachment-chip
                                      :attachment="[
                                        'id' => $attachment->id,
                                        'type' => $attachment->type,
                                        'name' => $attachment->title ?: 'Attachment',
                                        'path' => $attachment->path ?? $attachment->url ?? '',
                                        'url' => $attachment->isFile()
                                          ? \Illuminate\Support\Facades\Storage::disk('public')->url((string) $attachment->path)
                                          : (string) ($attachment->url ?? $attachment->path),
                                      ]"
                                      :template-id="$template->id"
                                      :variant-index="$loop->index" />
                                  @endforeach
                                </div>
                              </div>
                            </div>
                          @endif

                          @if($groupedLinks->isNotEmpty())
                            <div class="atask-attachment-group">
                              <span class="atask-attachment-dot atask-attachment-dot--links" aria-hidden="true"></span>
                              <div class="atask-attachment-group-body">
                                <div class="atask-attachment-group-head">
                                  <span class="fw-semibold">Links</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 atask-attachment-chip-wrap">
                                  @foreach($groupedLinks as $attachment)
                                    <x-sessions.attachment-chip
                                      :attachment="[
                                        'id' => $attachment->id,
                                        'type' => $attachment->type,
                                        'name' => $attachment->title ?: 'Attachment',
                                        'path' => $attachment->path ?? $attachment->url ?? '',
                                        'url' => $attachment->isFile()
                                          ? \Illuminate\Support\Facades\Storage::disk('public')->url((string) $attachment->path)
                                          : (string) ($attachment->url ?? $attachment->path),
                                      ]"
                                      :template-id="$template->id"
                                      :variant-index="$loop->index" />
                                  @endforeach
                                </div>
                              </div>
                            </div>
                          @endif
                        </div>
                      @endif
                    </div>
                  </div>
                @empty
                  <div class="text-muted">No main tasks yet. Add the reusable task definitions first.</div>
                @endforelse
              </div>
            </section>
          </div>

          <div class="col-12 col-xl-8">
            <section class="automation-version-panel">
              <div class="automation-version-panel-head">
                <div class="automation-version-panel-title">
                  <h6 class="card-title mb-0">Versions</h6>
                  <details class="intake-info intake-info--inline">
                    <summary class="intake-info__trigger" aria-label="About versions">
                      <i class="icon-base ti tabler-info-circle icon-18px"></i>
                    </summary>
                    <div class="intake-info__panel">
                      Only participating versions need to pass publish validation. Preparation versions can stay incomplete until students are assigned.
                    </div>
                  </details>
                </div>
                <div class="d-flex gap-1">
                  <button
                    type="button"
                    class="btn btn-sm btn-icon btn-text-primary"
                    wire:click="addVersion({{ $template->id }})"
                    wire:loading.attr="disabled"
                    wire:target="addVersion({{ $template->id }})"
                    aria-label="Add version">
                    <span wire:loading.remove wire:target="addVersion({{ $template->id }})"><i class="icon-base ti tabler-plus icon-20px"></i></span>
                    <span wire:loading wire:target="addVersion({{ $template->id }})"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></span>
                  </button>
                </div>
              </div>
              <div>
                @if($template->versions->isEmpty())
                  <div class="text-muted">Add a version to start configuring selections.</div>
                @else
                  <div class="version-nav-scroll">
                    <ul class="nav nav-pills flex-row flex-nowrap flex-md-wrap mb-4 row-gap-2 justify-content-start">
                    @foreach($template->versions as $version)
                      @php
                        $versionDiagnosis = $diagnostics[$template->id][$version->id] ?? ['passes' => false, 'errors' => []];
                        $versionAssignmentCount = $versionAssignmentCounts[$version->id] ?? 0;
                      @endphp
                      <li class="nav-item me-2 mb-2" wire:key="version-pill-{{ $version->id }}">
                        <div class="automation-version-summary {{ $activeVersion?->id === $version->id ? 'is-active' : '' }}">
                          <button
                            type="button"
                            class="nav-link version-pill {{ $activeVersion?->id === $version->id ? 'active' : '' }}"
                            wire:click="setActiveVersion({{ $template->id }}, {{ $version->id }})">
                            <span class="version-pill-name d-inline-block text-truncate align-middle" title="{{ $version->display_name }}">{{ $version->display_name }}</span>
                            <span class="automation-version-count ms-2">
                              @include('livewire.teacher.automated-task-status-badge', [
                                'label' => 'Assigned',
                                'tone' => 'info',
                                'count' => $versionAssignmentCount,
                                'iconOnly' => true,
                                'bare' => true,
                              ])
                            </span>
                            <span class="automation-version-status ms-2">@include('livewire.teacher.automated-task-validation-badge', ['passes' => $versionDiagnosis['passes'], 'messages' => $versionDiagnosis['errors'], 'bare' => true])</span>
                          </button>
                          <button
                            type="button"
                            class="btn btn-sm btn-icon btn-text-info automation-version-assign"
                            wire:click="openAssignmentModal({{ $template->id }}, {{ $version->id }})"
                            title="Manage students"
                            aria-label="Manage students for {{ $version->display_name }}">
                            <i class="icon-base ti tabler-user-plus icon-16px"></i>
                          </button>
                        </div>
                      </li>
                    @endforeach
                  </ul>
                  </div>

                  @if($activeVersion)
                    @php
                      $activeDiagnosis = $diagnostics[$template->id][$activeVersion->id] ?? ['passes' => false, 'errors' => []];
                      $activeVersionAssignmentCount = $versionAssignmentCounts[$activeVersion->id] ?? 0;
                      $affectedStudentNames = $versionAffectedStudentNames[$activeVersion->id] ?? [];
                      $affectedStudentPreview = empty($affectedStudentNames)
                        ? 'No currently affected students.'
                        : implode(', ', $affectedStudentNames).($activeVersionAssignmentCount > count($affectedStudentNames) ? ', and '.($activeVersionAssignmentCount - count($affectedStudentNames)).' more' : '');
                      $deleteConfirmation = $activeVersionAssignmentCount === 0
                        ? "Delete {$activeVersion->display_name}? No students are currently assigned to this version. Already-generated rows stay unchanged."
                        : "Delete {$activeVersion->display_name}? {$activeVersionAssignmentCount} assigned student(s) will become unassigned from today onward: {$affectedStudentPreview}. Already-generated rows stay unchanged.";
                    @endphp

                    <div class="automation-version-detail">
                      <div class="automation-version-title-row">
                        <div class="flex-grow-1" style="min-width: 0;">
                          <label class="visually-hidden" for="version-name-{{ $activeVersion->id }}">Version name</label>
                          <input
                            type="text"
                            id="version-name-{{ $activeVersion->id }}"
                            class="form-control automation-version-title-input"
                            value="{{ $activeVersion->display_name }}"
                            wire:change="renameVersion({{ $activeVersion->id }}, $event.target.value)">
                        </div>
                        <button
                          type="button"
                          class="btn btn-text-danger rounded-pill btn-icon flex-shrink-0"
                          wire:click="deleteVersion({{ $activeVersion->id }})"
                          wire:confirm="{{ $deleteConfirmation }}"
                          aria-label="Delete version">
                          <i class="ti tabler-trash"></i>
                        </button>
                      </div>
                    </div>

                    <div class="selection-scroll pe-1">
                      @forelse($template->mainTasks as $mainTask)
                        @php
                          $form = $versionTaskForms[$activeVersion->id][$mainTask->id] ?? [
                            'enabled' => false,
                            'description_override' => null,
                          ];

                          $selectedVersionTask = $activeVersion->versionTasks->firstWhere('main_task_id', $mainTask->id);
                          $rowDiagnosis = $selectedVersionTask
                            ? [
                                'passes' => $selectedVersionTask->passesMeaningfulContentRule(),
                                'errors' => $selectedVersionTask->passesMeaningfulContentRule()
                                  ? []
                                  : ['This version row needs a description, an attachment, or both before it can be saved.'],
                              ]
                            : ['passes' => false, 'errors' => []];
                        @endphp

                        @include('livewire.teacher.automated-task-version-task-row', [
                          'template' => $template,
                          'version' => $activeVersion,
                          'mainTask' => $mainTask,
                          'form' => $form,
                          'diagnosis' => $rowDiagnosis,
                        ])
                      @empty
                        <div class="text-muted">Create at least one main task before configuring this version.</div>
                      @endforelse
                    </div>

                    @if(! empty($activeDiagnosis['errors']))
                      <div class="alert alert-warning mt-3 mb-0">
                        <div class="fw-semibold mb-2">This version is not ready for assignment yet</div>
                        <ul class="mb-0 ps-3">
                          @foreach($activeDiagnosis['errors'] as $message)
                            <li>{{ $message }}</li>
                          @endforeach
                        </ul>
                      </div>
                   @endif
                 @endif
                @endif
              </div>
            </section>
          </div>
           </div>
         </div>
      </div>
   @empty
    <div class="card">
      <div class="card-body text-center py-5 text-muted">
        No Automated Task templates yet for this subject.
      </div>
    </div>
  @endforelse

  <livewire:teacher.automated-task-main-task-modal wire:key="automated-task-main-task-modal" />
  <livewire:teacher.automated-task-assignment-modal wire:key="automated-task-assignment-modal" />
  <livewire:teacher.automated-task-generated-history-panel wire:key="automated-task-generated-history-panel" />

  <div class="modal fade" id="imageAttachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="imageAttachmentTitle">Image</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center">
          <img id="imageAttachmentImg" src="" class="img-fluid teacher-daily-image-preview" alt="">
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
  @once
    <script>
      (function () {
        if (window.w14AutomatedTasksImageAttachmentInitialized) return;
        window.w14AutomatedTasksImageAttachmentInitialized = true;

        document.addEventListener('click', function (event) {
          const link = event.target.closest('.automated-task-board .image-attachment');
          if (!link) return;

          const modalEl = document.getElementById('imageAttachmentModal');
          const titleEl = document.getElementById('imageAttachmentTitle');
          const imgEl = document.getElementById('imageAttachmentImg');

          if (!modalEl || !titleEl || !imgEl) return;

          event.preventDefault();
          event.stopPropagation();

          titleEl.textContent = link.getAttribute('data-img-title') || 'Image';
          imgEl.src = link.getAttribute('data-img-src') || '';

          bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }, true);

        document.addEventListener('hidden.bs.modal', function (event) {
          if (event.target?.id !== 'imageAttachmentModal') return;

          const imgEl = document.getElementById('imageAttachmentImg');
          if (imgEl) {
            imgEl.src = '';
          }
        });
      })();
    </script>
  @endonce
@endpush
