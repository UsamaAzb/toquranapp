<style>
.progress-bar-mobile-shell {
    width: min(760px, 100%);
    margin: 0 auto 1rem;
    padding-inline: 0.25rem;
}

@media (max-width: 900px) {
    .progress_bar{
        display: none !important;
    }
    .progress_bar_mobile{
        display:block !important;
    }
}

@media (max-width: 550px) {
    .progress-bar-mobile-shell {
        width: min(100%, calc(100vw - 1rem));
        padding-inline: 0;
    }
}

/* Compact row layout: label + bar side-by-side on mobile (all pages) */
.progress-bar-mobile-shell .w14-bar-layout {
    flex-direction: row !important;
    align-items: center !important;
    gap: 0.75rem !important;
    width: 100% !important;
}
.progress-bar-mobile-shell .point_ratio {
    display: inline-flex !important;
    font-size: 0.85rem !important;
    padding: 0.25rem 0.6rem !important;
    flex-shrink: 0 !important;
}
.progress-bar-mobile-shell .mobile_point_ratio {
    display: none !important;
}
.progress-bar-mobile-shell .w14-bar-wrap {
    flex: 1 !important;
    min-width: 0 !important;
}
.progress-bar-mobile-shell .w14-bar-track {
    height: 14px !important;
}

</style>

@if(isset($show_bar) && ($show_bar=="true"))
<div class="progress-bar-mobile-shell">
  <div class="ms-auto progress_bar_mobile mb-3" style="display:none">
   <livewire:ui.points-progress
      :student-id="$student->id"
      :pending-gift-id="$pendingGift->id ?? null"
      :last-reached-gift-id="$lastReached->id ?? null"
      :allow-reached-click="true"
      :circle-view="false"
      :bar-view="true"
      label="Reward Points"
      />
  </div>
</div>
@endif
