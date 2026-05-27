<style>
  [x-cloak] {
    display: none !important;
  }

  .sessions-board-shell,
  .daily-sessions-board-shell {
    --w14-session-surface: var(--bs-paper-bg, var(--bs-card-bg));
    --w14-session-text: var(--bs-heading-color);
    --w14-session-muted: var(--bs-secondary-color);
    --w14-session-border: var(--bs-border-color);
    --w14-session-soft-border: color-mix(in sRGB, var(--bs-border-color) 76%, transparent);
    --w14-session-primary-soft: rgba(var(--bs-primary-rgb), 0.09);
    --w14-session-primary-border: rgba(var(--bs-primary-rgb), 0.18);
    --w14-session-primary-hover: rgba(var(--bs-primary-rgb), 0.08);
    --w14-session-shadow: 0 0.35rem 1rem rgba(24, 28, 50, 0.08);
    --w14-session-shadow-hover: 0 0.75rem 1.65rem rgba(24, 28, 50, 0.13);
    --w14-session-card-accent: var(--bs-primary);
  }

  [data-bs-theme="dark"] .sessions-board-shell,
  [data-bs-theme="dark"] .daily-sessions-board-shell {
    --w14-session-surface: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-card-bg)) 92%, white 8%);
    --w14-session-muted: color-mix(in sRGB, var(--bs-heading-color) 76%, var(--bs-primary));
    --w14-session-soft-border: rgba(var(--bs-primary-rgb), 0.22);
    --w14-session-primary-border: rgba(var(--bs-primary-rgb), 0.28);
    --w14-session-primary-hover: rgba(var(--bs-primary-rgb), 0.14);
    --w14-session-shadow: 0 0.5rem 1.35rem rgba(0, 0, 0, 0.28);
    --w14-session-shadow-hover: 0 0.8rem 1.85rem rgba(0, 0, 0, 0.38);
  }

  .island-progress {
    height: 7px;
    width: 120px;
  }

  .inside-island-progress {
    height: 7px;
  }

  .session-title-input:focus {
    background-color: var(--w14-session-primary-soft);
    border-radius: 4px;
    transition: background-color 0.15s ease-in-out;
  }

  .edit_close {
    transform: none !important;
  }

  .teacher-session-image-preview,
  .student-session-image-preview,
  .teacher-daily-image-preview {
    max-width: 100%;
    width: auto !important;
    max-height: min(75vh, 900px);
    object-fit: contain;
  }

  .sessions-board-shell .session-card {
    position: relative;
    border: 1px solid color-mix(in sRGB, var(--w14-session-card-accent) 22%, var(--w14-session-soft-border));
    border-radius: 0.5rem;
    overflow: hidden;
    background: var(--w14-session-surface);
    color: var(--w14-session-text);
    box-shadow: var(--w14-session-shadow);
    scroll-margin-top: 6.5rem;
    transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
  }

  .daily-sessions-board-shell .session-card,
  .daily-sessions-board-shell .main-daily-card {
    border: 1px solid var(--w14-session-soft-border);
    border-radius: 0.5rem;
    overflow: hidden;
    background: var(--w14-session-surface);
    color: var(--w14-session-text);
    box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.06);
    scroll-margin-top: 6.5rem;
  }

  [data-bs-theme="dark"] .daily-sessions-board-shell .session-card,
  [data-bs-theme="dark"] .daily-sessions-board-shell .main-daily-card {
    box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.22);
  }

  .sessions-board-shell .session-card::before {
    content: "";
    position: absolute;
    inset-block: 0;
    inset-inline-start: 0;
    width: 0.28rem;
    background: linear-gradient(180deg, var(--w14-session-card-accent), color-mix(in sRGB, var(--w14-session-card-accent) 48%, white));
  }

  .sessions-board-shell .session-card::after {
    display: none;
  }

  .sessions-board-shell .session-card:hover {
    transform: translateY(-1px);
    border-color: color-mix(in sRGB, var(--w14-session-card-accent) 38%, var(--w14-session-soft-border));
    box-shadow: var(--w14-session-shadow-hover);
  }

  .sessions-board-shell .session-card > * {
    position: relative;
    z-index: 1;
  }

  .sessions-board-shell .session-card-state-empty {
    --w14-session-card-accent: var(--bs-secondary);
  }

  .sessions-board-shell .session-card-state-ready {
    --w14-session-card-accent: var(--bs-info);
  }

  .sessions-board-shell .session-card-state-progress {
    --w14-session-card-accent: var(--bs-warning);
  }

  .sessions-board-shell .session-card-state-review {
    --w14-session-card-accent: var(--bs-warning);
  }

  .sessions-board-shell .session-card-state-complete {
    --w14-session-card-accent: var(--bs-success);
  }

  .sessions-board-shell .session-card-header {
    line-height: 1.35;
    padding: 1.1rem 1.15rem 1.1rem 1.35rem;
  }

  .sessions-board-shell .session-card-head-grid {
    display: block;
    line-height: 1.35;
  }

  .daily-sessions-board-shell .session-card-header,
  .daily-sessions-board-shell .main-daily-card > .accordion-header {
    padding: 1rem;
  }

  .sessions-board-shell .session-title-wrap,
  .daily-sessions-board-shell .session-title-wrap {
    flex: 1 1 auto;
    min-width: 0;
    overflow: hidden;
  }

  .sessions-board-shell .session-title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.85rem;
    min-width: 0;
  }

  .sessions-board-shell .session-title-text,
  .daily-sessions-board-shell .session-title-text {
    display: block;
    width: 100%;
    max-width: min(58vw, 42rem);
    color: var(--w14-session-text);
    letter-spacing: 0;
  }

  .sessions-board-shell .session-title-row .session-title-text {
    flex: 1 1 auto;
    min-width: 0;
  }

  .sessions-board-shell .session-title-input,
  .daily-sessions-board-shell .session-title-input {
    color: var(--w14-session-text);
  }

  .sessions-board-shell .session-section-title,
  .daily-sessions-board-shell .session-section-title {
    color: var(--w14-session-text);
  }

  .sessions-board-shell .session-title-wrap small {
    white-space: nowrap;
  }

  .sessions-board-shell .session-progress-inline {
    border-left: 1px solid color-mix(in sRGB, var(--w14-session-card-accent) 20%, var(--w14-session-soft-border));
  }

  .sessions-board-shell .session-card .text-muted,
  .sessions-board-shell .session-card .session-task-description {
    color: color-mix(in sRGB, var(--w14-session-muted) 86%, var(--w14-session-card-accent));
  }

  .sessions-board-shell .session-card .island-progress {
    background: color-mix(in sRGB, var(--w14-session-card-accent) 10%, var(--bs-border-color));
  }

  .sessions-board-shell .session-card .inside-island-progress {
    background-color: var(--w14-session-card-accent);
  }

  .sessions-board-shell .session-meta-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    line-height: 1.2;
    margin-top: 0.25rem;
    min-width: 0;
  }

  .sessions-board-shell .session-meta-row .ti {
    line-height: 1;
  }

  .sessions-board-shell .session-date,
  .sessions-board-shell .session-progress-inline,
  .sessions-board-shell .session-review-hint {
    flex: 0 0 auto;
  }

  .sessions-board-shell .session-date {
    display: inline-flex;
    align-items: center;
    color: color-mix(in sRGB, var(--w14-session-muted) 86%, var(--w14-session-card-accent));
    margin-bottom: 0;
  }

  .sessions-board-shell .session-progress-inline {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    line-height: 1.2;
    padding-left: 0.5rem;
  }

  .sessions-board-shell .session-review-hint {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    min-height: 1.45rem;
    padding: 0.22rem 0.52rem;
    border: 1px solid rgba(var(--bs-warning-rgb), 0.22);
    border-radius: 999px;
    background: rgba(var(--bs-warning-rgb), 0.11);
    color: color-mix(in sRGB, var(--bs-warning) 72%, var(--w14-session-text));
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
  }

  .sessions-board-shell .session-publish-hint-published {
    border-color: rgba(var(--bs-success-rgb), 0.24);
    background: rgba(var(--bs-success-rgb), 0.11);
    color: color-mix(in sRGB, var(--bs-success) 76%, var(--w14-session-text));
  }

  .sessions-board-shell .session-publish-hint-draft {
    border-color: rgba(var(--bs-info-rgb), 0.24);
    background: rgba(var(--bs-info-rgb), 0.11);
    color: color-mix(in sRGB, var(--bs-info) 76%, var(--w14-session-text));
  }

  .sessions-board-shell .session-review-hint .ti,
  .sessions-board-shell .session-task-review-pill .ti {
    font-size: 0.95rem;
    line-height: 1;
  }

  .sessions-board-shell .session-actions,
  .daily-sessions-board-shell .session-actions {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
    min-width: max-content;
  }

  .sessions-board-shell .session-actions {
    align-self: flex-start;
    padding-top: 0;
  }

  .sessions-board-shell .session-toggle-btn,
  .sessions-board-shell .session-journey-icon-btn {
    border-radius: 0.5rem;
  }

  .sessions-board-shell .session-toggle-btn,
  .daily-sessions-board-shell .session-toggle-btn {
    min-width: 4.75rem;
  }

  .daily-sessions-board-shell .session-toggle-btn {
    border-radius: 0.5rem !important;
  }

  .sessions-board-shell .session-toggle-icon {
    margin-left: 0.25rem;
  }

  .sessions-board-shell .session-toggle-btn .tabler-caret-down,
  .daily-sessions-board-shell .session-toggle-btn .tabler-caret-down {
    transition: transform 0.18s ease-in-out;
  }

  .sessions-board-shell .session-toggle-btn:not(.collapsed) .tabler-caret-down,
  .daily-sessions-board-shell .session-toggle-btn:not(.collapsed) .tabler-caret-down {
    transform: rotate(180deg);
  }

  .sessions-board-shell .session-status-icon-btn,
  .sessions-board-shell .session-journey-icon-btn,
  .daily-sessions-board-shell .session-status-icon-btn {
    width: 2.25rem;
    min-width: 2.25rem;
    height: 2.25rem;
    padding: 0;
    justify-content: center;
  }

  .sessions-board-shell .session-status-icon-passive {
    cursor: default;
    pointer-events: none;
  }

  .daily-sessions-board-shell .session-status-icon-btn {
    border-radius: 0.5rem !important;
  }

  .sessions-board-shell .session-task-panel {
    border: 0;
    border-radius: 0;
    background: var(--w14-session-surface);
    overflow: hidden;
  }

  .sessions-board-shell .accordion-collapse,
  .sessions-board-shell .accordion-body,
  .sessions-board-shell .session-task-row {
    background: var(--w14-session-surface);
  }

  .daily-sessions-board-shell .session-task-panel {
    border: 1px solid var(--w14-session-soft-border);
    border-radius: 0.5rem;
    background: var(--w14-session-surface);
    overflow: hidden;
  }

  .sessions-board-shell .session-add-task-strip,
  .daily-sessions-board-shell .session-add-task-strip {
    min-height: 2.75rem;
    background: var(--w14-session-primary-soft);
    border-bottom: 1px solid var(--w14-session-primary-border);
  }

  .sessions-board-shell .session-add-task-strip {
    cursor: pointer;
  }

  .sessions-board-shell .session-add-task-strip:focus-visible,
  .daily-sessions-board-shell .session-add-task-strip:focus-visible {
    outline: 2px solid rgba(var(--bs-primary-rgb), 0.45);
    outline-offset: -2px;
  }

  .sessions-board-shell .session-add-task-btn,
  .daily-sessions-board-shell .session-add-task-btn {
    width: 1.8rem;
    height: 1.8rem;
    color: var(--bs-primary);
    border-color: var(--bs-primary);
    background: var(--w14-session-surface);
  }

  .sessions-board-shell .session-add-task-label,
  .daily-sessions-board-shell .session-add-task-label {
    color: var(--bs-primary);
    font-weight: 500;
  }

  .sessions-board-shell .session-task-row {
    margin-bottom: 0;
    scroll-margin-top: 7.5rem;
  }

  .sessions-board-shell .session-task-list {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .sessions-board-shell .session-task-item {
    background: var(--w14-session-surface);
  }

  .daily-sessions-board-shell .session-task-row {
    margin-bottom: 0.75rem;
    scroll-margin-top: 7.5rem;
  }

  .sessions-board-shell .session-task-row + .session-task-row {
    padding-top: 0.8rem;
    border-top: 1px solid color-mix(in sRGB, var(--w14-session-soft-border) 82%, transparent);
  }

  .daily-sessions-board-shell .session-task-row + .session-task-row {
    padding-top: 0.75rem;
    border-top: 1px solid var(--w14-session-soft-border);
  }

  .sessions-board-shell .session-task-row:last-child,
  .daily-sessions-board-shell .session-task-row:last-child {
    margin-bottom: 0;
  }

  .daily-sessions-board-shell .timeline-event {
    width: 100%;
  }

  .sessions-board-shell .session-task-event {
    width: 100%;
    padding-block: 0.15rem 0.85rem;
  }

  .daily-sessions-board-shell .timeline .timeline-item.timeline-item-transparent .timeline-event {
    inset-block-start: -0.7rem;
  }

  .daily-sessions-board-shell .timeline .timeline-item.border-dashed::before,
  .daily-sessions-board-shell .timeline .timeline-item::before {
    display: none !important;
  }

  .daily-sessions-board-shell .timeline .timeline-item {
    border-left: 0 !important;
  }

  .sessions-board-shell .session-task-title {
    color: var(--w14-session-text);
    min-width: 0;
    font-size: 0.98rem;
    line-height: 1.25;
  }

  .daily-sessions-board-shell .session-task-title {
    color: var(--w14-session-text) !important;
    min-width: 0;
  }

  .sessions-board-shell .session-task-description {
    color: var(--w14-session-muted);
    max-width: 72rem;
    font-size: 0.9rem;
    line-height: 1.45;
    overflow-wrap: anywhere;
  }

  :is(.sessions-board-shell, .daily-sessions-board-shell) .session-task-brief {
    min-width: 0;
    width: 100%;
    max-width: none;
    overflow: hidden;
    padding: 0.9rem;
    border: 1px solid color-mix(in sRGB, var(--w14-session-card-accent) 18%, var(--w14-session-soft-border));
    border-radius: 0.7rem;
    background: transparent;
  }

  :is(.sessions-board-shell, .daily-sessions-board-shell) .session-task-brief-heading {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    margin-bottom: 0.5rem;
  }

  :is(.sessions-board-shell, .daily-sessions-board-shell) .session-task-brief-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    inline-size: 1.55rem;
    block-size: 1.55rem;
    border-radius: 999px;
    color: var(--w14-session-card-accent);
    background: color-mix(in sRGB, var(--w14-session-card-accent) 12%, transparent);
    font-size: 0.88rem;
  }

  :is(.sessions-board-shell, .daily-sessions-board-shell) .session-task-brief-label {
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 0;
    color: var(--w14-session-card-accent);
    text-transform: uppercase;
  }

  :is(.sessions-board-shell, .daily-sessions-board-shell) .session-task-brief-description {
    max-width: 100%;
    color: color-mix(in sRGB, var(--w14-session-muted) 88%, var(--w14-session-card-accent));
    word-break: break-word;
  }

  .daily-sessions-board-shell .session-task-description {
    color: var(--w14-session-muted) !important;
  }

  .sessions-board-shell .session-task-points {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.22rem 0.52rem;
    border-radius: 999px;
    background: rgba(var(--bs-danger-rgb), 0.09);
    color: var(--bs-danger);
    font-size: 0.74rem;
    font-weight: 800;
    line-height: 1;
    white-space: nowrap;
  }

  .daily-sessions-board-shell .session-task-points {
    font-size: 0.75rem;
    white-space: nowrap;
  }

  .sessions-board-shell .session-task-points-earned {
    background: rgba(var(--bs-success-rgb), 0.12);
    color: var(--bs-success);
  }

  .sessions-board-shell .session-task-topline {
    display: grid;
    grid-template-columns: 0.85rem minmax(0, 1fr) auto;
    align-items: center;
    column-gap: 0.72rem;
    min-width: 0;
  }

  .sessions-board-shell .session-task-dot {
    align-self: center;
    display: inline-flex;
    height: 0.72rem;
    min-height: 0.72rem;
    min-width: 0.72rem;
    padding: 0;
    width: 0.72rem;
    border-radius: 999px;
  }

  .sessions-board-shell .session-task-dot-primary {
    background: var(--bs-primary);
  }

  .sessions-board-shell .session-task-dot-success {
    background: var(--bs-success);
  }

  .sessions-board-shell .session-task-dot-danger {
    background: var(--bs-danger);
  }

  .sessions-board-shell .session-task-dot-info {
    background: var(--bs-info);
  }

  .sessions-board-shell .session-task-body {
    padding: 0.75rem 0 0.35rem 0;
  }

  .sessions-board-shell .session-task-top-meta {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: nowrap;
    gap: 0.45rem;
    flex: 0 0 auto;
  }

  .sessions-board-shell .session-task-action-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-block: 0.55rem 0.7rem;
  }

  .sessions-board-shell .session-task-action-row-top {
    align-items: center;
    margin-block: 0;
  }

  .sessions-board-shell .session-task-action-row-top > .dropdown {
    display: inline-flex;
    align-items: center;
  }

  .sessions-board-shell .session-task-action-row-phone {
    display: none;
  }

  .sessions-board-shell .session-task-row-review .session-task-pin-btn {
    display: none;
  }

  .sessions-board-shell .session-task-state-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.32rem;
    min-height: 1.62rem;
    padding: 0.28rem 0.58rem;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 800;
    line-height: 1;
    white-space: nowrap;
  }

  .sessions-board-shell .session-task-complete-pill {
    border: 2px solid var(--bs-success);
    background: rgba(var(--bs-success-rgb), 0.1);
    color: var(--bs-success);
  }

  .sessions-board-shell .session-task-source-pin {
    border-color: var(--bs-primary);
  }

  .sessions-board-shell .session-task-source-parent {
    border-color: var(--bs-info);
  }

  .sessions-board-shell .session-task-source-teacher,
  .sessions-board-shell .session-task-source-default {
    border-color: var(--bs-success);
  }

  .sessions-board-shell .session-task-source-auto {
    border-color: var(--bs-warning);
  }

  .sessions-board-shell .session-task-complete-pill .ti {
    font-size: 1rem;
    line-height: 1;
  }

  .sessions-board-shell .session-task-review-pill {
    border: 1px solid rgba(var(--bs-warning-rgb), 0.24);
    background: rgba(var(--bs-warning-rgb), 0.12);
    color: color-mix(in sRGB, var(--bs-warning) 74%, var(--w14-session-text));
  }

  .sessions-board-shell .session-task-complete-btn {
    min-width: 5.9rem;
    min-height: 2.05rem;
    border-radius: 0.45rem;
    box-shadow: 0 0.28rem 0.65rem rgba(var(--bs-primary-rgb), 0.2);
  }

  .sessions-board-shell .session-task-pin-btn {
    width: 2.05rem;
    min-width: 2.05rem;
    height: 2.05rem;
    min-height: 2.05rem;
    padding: 0;
    border-radius: 0.45rem;
    background: rgba(var(--bs-primary-rgb), 0.11);
    border-color: rgba(var(--bs-primary-rgb), 0.18);
    color: var(--bs-primary);
    box-shadow: none;
  }

  .sessions-board-shell .session-task-pin-btn:hover,
  .sessions-board-shell .session-task-pin-btn:focus-visible {
    background: rgba(var(--bs-primary-rgb), 0.18);
    border-color: rgba(var(--bs-primary-rgb), 0.32);
    color: var(--bs-primary);
  }

  .sessions-board-shell .session-task-event,
  .sessions-board-shell .session-task-body,
  .sessions-board-shell .session-task-row-review .session-task-event,
  .sessions-board-shell .session-task-row-completed .session-task-event {
    background: var(--w14-session-surface);
  }

  .session-attachment-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    max-width: min(230px, 100%);
    min-width: 0;
    padding: 0.35rem 0.55rem 0.35rem 0.35rem;
    border: 1px solid var(--w14-session-soft-border);
    border-radius: 0.375rem;
    background: var(--w14-session-surface);
    color: var(--w14-session-text);
    font-weight: 500;
    line-height: 1.2;
    vertical-align: middle;
    transition: background-color 0.16s ease-in-out, border-color 0.16s ease-in-out, box-shadow 0.16s ease-in-out;
  }

  .session-attachment-chip-button {
    appearance: none;
    border-color: var(--w14-session-soft-border);
    cursor: pointer;
    font: inherit;
    text-align: left;
  }

  .session-attachment-chip:hover,
  .session-attachment-chip:focus-visible {
    background: var(--w14-session-primary-hover);
    border-color: rgba(var(--bs-primary-rgb), 0.55);
    box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.1);
    color: var(--w14-session-text);
  }

  .session-attachment-icon {
    width: 1.55rem;
    height: 1.55rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    border-radius: 0.25rem;
    font-size: 1rem;
  }

  .session-attachment-name {
    display: block;
    max-width: 100%;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: inherit;
  }

  .session-attachment-thumb-0 {
    background: rgba(var(--bs-primary-rgb), 0.16);
    color: var(--bs-primary);
  }

  .session-attachment-thumb-1 {
    background: rgba(var(--bs-info-rgb), 0.16);
    color: var(--bs-info);
  }

  .session-attachment-thumb-2 {
    background: rgba(var(--bs-success-rgb), 0.16);
    color: var(--bs-success);
  }

  .session-attachment-thumb-3 {
    background: rgba(var(--bs-danger-rgb), 0.13);
    color: var(--bs-danger);
  }

  .session-attachment-thumb-4 {
    background: rgba(var(--bs-warning-rgb), 0.16);
    color: var(--bs-warning);
  }

  .session-attachment-thumb-5 {
    background: color-mix(in srgb, var(--bs-primary) 14%, transparent);
    color: color-mix(in srgb, var(--bs-primary), var(--bs-danger) 35%);
  }

  .session-attachment-semantic-link {
    background: rgba(var(--bs-success-rgb), 0.16);
    color: var(--bs-success);
  }

  .session-attachment-semantic-youtube {
    background: rgba(var(--bs-danger-rgb), 0.14);
    color: var(--bs-danger);
  }

  @media (max-width: 575.98px) {
    .sessions-board-shell .session-card-header,
    .daily-sessions-board-shell .session-card-header,
    .daily-sessions-board-shell .main-daily-card > .accordion-header {
      padding: 0.98rem 0.95rem;
    }

    .sessions-board-shell .session-card-head-grid {
      display: block;
    }

    .sessions-board-shell .session-actions {
      align-self: start;
      padding-top: 0;
    }

    .sessions-board-shell .session-title-text,
    .daily-sessions-board-shell .session-title-text {
      max-width: none;
    }

    .sessions-board-shell .session-title-row {
      align-items: flex-start;
      gap: 0.55rem;
    }

    .sessions-board-shell .session-title-wrap > .session-meta-row {
      flex-direction: row;
      flex-wrap: nowrap;
      gap: 0.38rem;
      overflow: hidden;
    }

    .sessions-board-shell .session-meta-row {
      margin-top: 0.48rem;
    }

    .sessions-board-shell .session-progress-inline {
      border-left: 1px solid color-mix(in sRGB, var(--w14-session-card-accent) 20%, var(--w14-session-soft-border));
      flex: 1 1 auto;
      gap: 0.35rem;
      min-width: 0;
      padding-left: 0.42rem;
    }

    .sessions-board-shell .session-progress-inline .island-progress {
      flex: 1 1 3.4rem;
      min-width: 2.7rem;
      width: clamp(42px, 14vw, 72px);
    }

    .sessions-board-shell .session-date {
      flex: 0 0 auto;
    }

    .sessions-board-shell .session-task-description.mb-0 {
      flex: 0 0 auto;
      font-size: 0.82rem;
    }

    .sessions-board-shell .session-actions {
      gap: 0.3rem;
    }

    .daily-sessions-board-shell .session-actions {
      gap: 0.3rem !important;
    }

    .sessions-board-shell .session-actions .btn,
    .daily-sessions-board-shell .session-actions .btn {
      min-height: 2.15rem;
      padding: 0.38rem 0.55rem;
      font-size: 0.8125rem;
      line-height: 1.1;
    }

    .sessions-board-shell .session-actions .btn > i:not(.session-status-icon):not(.session-journey-icon),
    .daily-sessions-board-shell .session-actions .btn > i:not(.session-status-icon) {
      display: none;
    }

    .sessions-board-shell .session-toggle-btn,
    .daily-sessions-board-shell .session-toggle-btn {
      min-width: 2.15rem;
    }

    .sessions-board-shell .session-toggle-label {
      display: none;
    }

    .sessions-board-shell .session-toggle-icon {
      margin-left: 0;
    }

    .sessions-board-shell .session-status-icon-btn,
    .sessions-board-shell .session-journey-icon-btn,
    .daily-sessions-board-shell .session-status-icon-btn {
      width: 2.15rem;
      min-width: 2.15rem;
      padding: 0;
    }

    .session-attachment-chip {
      justify-content: flex-start;
      max-width: 100%;
      width: min(100%, 15rem);
    }

    .sessions-board-shell .session-task-action-row {
      gap: 0.42rem;
      margin-block: 0.5rem 0.65rem;
    }

    .sessions-board-shell .session-task-topline {
      grid-template-columns: 0.75rem minmax(0, 1fr) auto;
      align-items: center;
      gap: 0.5rem;
    }

    .sessions-board-shell .session-task-top-meta {
      width: auto;
      justify-content: flex-end;
      flex-wrap: nowrap;
      flex: 0 0 auto;
    }

    .sessions-board-shell .session-task-row-ready .session-task-action-row-top .session-task-complete-btn,
    .sessions-board-shell .session-task-row-ready .session-task-action-row-top .session-task-pin-btn {
      display: none;
    }

    .sessions-board-shell .session-task-action-row-phone {
      display: flex;
      justify-content: center;
      margin-block: 1.15rem 0.15rem;
    }

    .sessions-board-shell .session-task-body {
      padding: 0.72rem 0 0.72rem;
    }

    :is(.sessions-board-shell, .daily-sessions-board-shell) .session-task-brief {
      padding: 0.8rem;
      border-radius: 0.65rem;
    }

    :is(.sessions-board-shell, .daily-sessions-board-shell) .session-task-brief-icon {
      inline-size: 1.45rem;
      block-size: 1.45rem;
    }

    :is(.sessions-board-shell, .daily-sessions-board-shell) .session-task-brief-description {
      font-size: 0.8rem;
    }

    .sessions-board-shell .session-task-row-completed .session-task-state-pill,
    .sessions-board-shell .session-task-row-review .session-task-state-pill {
      display: inline-grid;
      gap: 0;
      place-items: center;
      width: 1.45rem;
      min-width: 1.45rem;
      height: 1.45rem;
      min-height: 1.45rem;
      justify-content: center;
      padding: 0;
      border-radius: 999px;
      border-width: 1px;
      font-size: 0;
    }

    .sessions-board-shell .session-task-row-completed .session-task-complete-pill {
      width: 1.3rem;
      min-width: 1.3rem;
      height: 1.3rem;
      min-height: 1.3rem;
      background: transparent;
      border: 0;
      color: var(--bs-success);
    }

    .sessions-board-shell .session-task-row-completed .session-task-state-pill .ti,
    .sessions-board-shell .session-task-row-review .session-task-state-pill .ti {
      display: block;
      line-height: 1;
      margin: 0;
      font-size: 0.86rem;
    }

    .sessions-board-shell .session-task-state-label {
      display: none;
    }

    .sessions-board-shell .session-task-row-completed .session-task-complete-pill .ti {
      font-size: 1.3rem;
    }

    .sessions-board-shell .session-task-row-review .session-task-review-pill .ti {
      transform: translate(0.5px, -0.5px);
    }

    .sessions-board-shell .session-task-complete-btn {
      min-width: 5.4rem;
    }

    .sessions-board-shell .session-task-pin-btn {
      width: 2.1rem;
      min-width: 2.1rem;
      height: 2.1rem;
    }

    .sessions-board-shell .session-review-hint {
      display: none;
    }

    .sessions-board-shell .session-review-hint-label {
      display: none;
    }
  }

  @media (max-width: 360px) {
    .daily-sessions-board-shell .session-card-header > .d-flex {
      gap: 0.5rem !important;
    }

    .sessions-board-shell .session-card-head-grid {
      gap: 0.5rem;
    }

    .daily-sessions-board-shell .session-title-wrap {
      flex-basis: calc(100% - 8.75rem);
    }

    .sessions-board-shell .session-title-text,
    .daily-sessions-board-shell .session-title-text {
      font-size: 0.98rem;
      line-height: 1.2;
    }

    .sessions-board-shell .session-actions .btn,
    .daily-sessions-board-shell .session-actions .btn {
      padding-inline: 0.5rem;
    }
  }
</style>
