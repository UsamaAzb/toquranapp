<style>
  .resource-page {
    --resource-card-shadow: 0 0.75rem 1.65rem rgba(47, 43, 61, 0.08);
    --resource-row-bg: color-mix(in srgb, var(--bs-paper-bg) 92%, var(--bs-primary));
    --resource-row-hover: rgba(var(--bs-primary-rgb), 0.08);
    --resource-row-border: color-mix(in srgb, var(--bs-border-color) 82%, var(--bs-primary));
  }

  [data-bs-theme="dark"] .resource-page {
    --resource-card-shadow: 0 0.75rem 1.65rem rgba(0, 0, 0, 0.22);
    --resource-row-bg: color-mix(in srgb, var(--bs-paper-bg) 86%, var(--bs-primary));
    --resource-row-hover: rgba(var(--bs-primary-rgb), 0.16);
    --resource-row-border: color-mix(in srgb, var(--bs-border-color) 74%, var(--bs-primary));
  }

  .resource-page .box-div {
    padding-block: clamp(1rem, 2vw, 2rem);
  }

  .resource-page .unit_group {
    display: grid;
    gap: 1rem;
  }

  .resource-page .accordion-item {
    overflow: hidden;
    border: 1px solid var(--bs-border-color);
    border-radius: 1rem;
    background: var(--bs-paper-bg);
    box-shadow: var(--resource-card-shadow);
  }

  .resource-page .accordion-button.unit-button {
    width: 100%;
    min-height: 3.75rem;
    padding: 1rem 1.25rem;
    color: var(--bs-heading-color);
    background: linear-gradient(135deg, var(--bs-paper-bg), color-mix(in srgb, var(--bs-paper-bg) 92%, var(--bs-primary)));
    font-weight: 700;
  }

  .resource-page .accordion-button.unit-button:not(.collapsed) {
    color: var(--bs-primary);
    background: color-mix(in srgb, var(--bs-paper-bg) 84%, var(--bs-primary));
    box-shadow: inset 0 -1px 0 var(--resource-row-border);
  }

  .resource-page .accordion-button.unit-button p {
    max-width: calc(100% - 2rem);
    margin: 0 !important;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .resource-page .lessons_body {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 17rem), 1fr));
    gap: 0.75rem;
    padding: 1rem;
    background:
      radial-gradient(circle at top left, rgba(var(--bs-primary-rgb), 0.07), transparent 36%),
      color-mix(in srgb, var(--bs-paper-bg) 96%, var(--bs-primary)) !important;
  }

  .resource-page .accordion-header + .accordion-collapse .accordion-body.lessons_body {
    padding-block-start: 1rem;
  }

  .resource-page .lessons_body a {
    min-width: 0;
    color: inherit;
    text-decoration: none;
  }

  .resource-page .lesson_div {
    position: relative;
    display: flex;
    align-items: center;
    min-height: 3rem;
    padding: 0.75rem 2.35rem 0.75rem 2.85rem;
    border: 1px solid var(--resource-row-border);
    border-radius: 0.85rem;
    background: var(--bs-paper-bg);
    color: var(--bs-body-color);
    font-weight: 600;
    line-height: 1.35;
    box-shadow: 0 0.35rem 0.9rem rgba(47, 43, 61, 0.04);
    transition: transform 0.16s ease, border-color 0.16s ease, background-color 0.16s ease, box-shadow 0.16s ease;
  }

  .resource-page .lesson_div::before {
    content: "";
    position: absolute;
    inset-inline-start: 0.85rem;
    inline-size: 1.15rem;
    block-size: 1.15rem;
    border-radius: 0.38rem;
    background:
      linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.16), rgba(var(--bs-primary-rgb), 0.08)),
      var(--bs-paper-bg);
    border: 1px solid rgba(var(--bs-primary-rgb), 0.28);
    box-shadow: inset 0 0 0 0.22rem rgba(var(--bs-primary-rgb), 0.08);
  }

  .resource-page .lesson_div::after {
    content: ">";
    position: absolute;
    inset-inline-end: 0.95rem;
    color: var(--bs-primary);
    font-weight: 700;
    opacity: 0.68;
  }

  .resource-page .lessons_body a:hover .lesson_div,
  .resource-page .lessons_body a:focus-visible .lesson_div {
    transform: translateY(-2px);
    border-color: rgba(var(--bs-primary-rgb), 0.38);
    background: color-mix(in srgb, var(--bs-paper-bg) 88%, var(--bs-primary));
    box-shadow: 0 0.75rem 1.25rem rgba(47, 43, 61, 0.08);
  }

  .resource-page .video_card {
    overflow: hidden;
    border: 1px solid var(--bs-border-color);
    border-radius: 1rem;
    background: var(--bs-paper-bg);
    box-shadow: var(--resource-card-shadow);
    transition: transform 0.16s ease, border-color 0.16s ease, box-shadow 0.16s ease;
  }

  .resource-page .resource-video-thumb {
    position: relative;
    display: block;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    background:
      radial-gradient(circle at 24% 20%, rgba(var(--bs-primary-rgb), 0.22), transparent 34%),
      linear-gradient(135deg, color-mix(in srgb, var(--bs-paper-bg) 84%, var(--bs-primary)), var(--bs-paper-bg));
  }

  .resource-page .resource-video-thumb img {
    inline-size: 100%;
    block-size: 100%;
    object-fit: cover;
  }

  .resource-page .resource-video-thumb::after {
    content: "";
    position: absolute;
    inset-inline-start: 50%;
    inset-block-start: 50%;
    inline-size: 3rem;
    block-size: 3rem;
    transform: translate(-50%, -50%);
    border-radius: 999px;
    background:
      linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.95), rgba(var(--bs-primary-rgb), 0.72));
    box-shadow: 0 0.75rem 1.5rem rgba(var(--bs-primary-rgb), 0.28);
    opacity: 0;
    transition: opacity 0.16s ease, transform 0.16s ease;
  }

  .resource-page .resource-video-thumb::before {
    content: "";
    position: absolute;
    z-index: 1;
    inset-inline-start: calc(50% + 0.13rem);
    inset-block-start: 50%;
    transform: translate(-50%, -50%);
    border-block: 0.48rem solid transparent;
    border-inline-start: 0.76rem solid #fff;
    opacity: 0;
    transition: opacity 0.16s ease;
  }

  .resource-page .video_card:hover .resource-video-thumb::after,
  .resource-page .video_card:focus-within .resource-video-thumb::after,
  .resource-page .video_card:hover .resource-video-thumb::before,
  .resource-page .video_card:focus-within .resource-video-thumb::before {
    opacity: 1;
  }

  .resource-page .resource-video-fallback {
    position: absolute;
    inset: 0;
    display: none;
    align-items: center;
    justify-content: center;
    color: var(--bs-primary);
    font-weight: 700;
    letter-spacing: 0.02em;
  }

  .resource-page .resource-video-thumb.is-missing .resource-video-fallback {
    display: flex;
  }

  .resource-page .resource-video-thumb.is-missing img {
    display: none;
  }

  .resource-page .video_card:hover,
  .resource-page .video_card:focus-within {
    transform: translateY(-3px);
    border-color: rgba(var(--bs-primary-rgb), 0.34);
  }

  .resource-page .surah_video_block {
    display: block;
    color: inherit;
    text-decoration: none;
  }

  .resource-page .video_body {
    min-height: 5.5rem;
    background: var(--bs-paper-bg);
  }

  .resource-page .video_title {
    color: var(--bs-heading-color);
    line-height: 1.45;
  }

  .resource-page .youtube,
  .resource-page .modal-content,
  .resource-video-modal .modal-content {
    background: var(--bs-paper-bg);
  }

  .resource-page .modal-body,
  .resource-video-modal .modal-body {
    padding: 1rem;
  }

  .resource-page .youtube-iframe,
  .resource-video-modal .youtube-iframe {
    display: block;
    inline-size: 100%;
    aspect-ratio: 16 / 9;
    min-block-size: clamp(16rem, 56vw, 42rem);
    max-block-size: 78vh;
    border: 0;
    border-radius: 0.85rem;
  }

  @media (min-width: 1200px) {
    .resource-video-modal .modal-dialog {
      max-width: min(88vw, 82rem);
    }
  }

  @media (min-width: 992px) and (max-width: 1199.98px) {
    .resource-video-modal .modal-dialog {
      max-width: 86vw;
    }
  }

  @media (max-width: 575.98px) {
    .resource-page .box-div {
      padding-inline: 0.35rem;
    }

    .resource-page .accordion-button.unit-button {
      min-height: 3.25rem;
      padding: 0.85rem 1rem;
      font-size: 0.95rem;
    }

    .resource-page .lessons_body {
      grid-template-columns: 1fr;
      padding: 0.75rem;
    }
  }
</style>
