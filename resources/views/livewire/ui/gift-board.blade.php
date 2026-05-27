<div class="w14-reward-board">
  <section class="w14-reward-hero" aria-label="Reward progress">
    <div class="w14-reward-hero-copy">
      <span class="w14-reward-kicker">Reward System</span>
      <h2>Keep climbing.</h2>
      <p>{{ number_format($currentPoints) }} of {{ number_format($targetPoints) }} points toward the active reward.</p>
    </div>

    <div class="w14-reward-hero-progress">
      <div class="w14-reward-hero-topline">
        @if($canToggleRewardDetails)
          <div class="form-check form-switch w14-reward-privacy-toggle" title="{{ $showRewardDetails ? 'Hide reward details' : 'Show reward details' }}">
            <input
              id="reward-details-toggle-{{ $student->id }}"
              class="form-check-input"
              type="checkbox"
              role="switch"
              wire:model.live="showRewardDetails"
              aria-label="Show reward details"
            >
          </div>
        @endif

        <div class="w14-reward-score">
          <span>{{ number_format($currentPoints) }}</span>
          <small>/ {{ number_format($targetPoints) }} pts</small>
        </div>
      </div>
      <div class="w14-reward-trackbar" aria-hidden="true">
        <span style="width: {{ $progressPercent }}%"></span>
      </div>
      <div class="w14-reward-counts" aria-label="Reward states">
        <span class="is-ready">{{ $statusCounts['ready'] }} ready</span>
        <span class="is-upcoming">{{ $statusCounts['upcoming'] }} upcoming</span>
        <span class="is-claimed">{{ $statusCounts['claimed'] }} claimed</span>
      </div>
    </div>
  </section>

  @if($cards->isEmpty())
    <section class="w14-reward-empty">
      <span class="w14-reward-empty-icon" aria-hidden="true">
        <i class="icon-base ti tabler-gift"></i>
      </span>
      <h3>No current-year rewards yet</h3>
      @if($hasLegacyGifts)
        <p>Older rewards still exist in history, but this board is waiting for the current academic-year reward queue.</p>
      @else
        <p>This student needs a reward queue before the board can show milestones.</p>
      @endif
    </section>
  @else
    <section class="w14-reward-grid" aria-label="Reward milestones">
      @foreach($cards as $card)
        <article
          wire:key="reward-gift-{{ $card['id'] }}"
          class="w14-reward-card reward-state-{{ $card['status'] }} {{ $card['is_selected'] ? 'is-selected' : '' }}"
        >
          <button
            type="button"
            class="w14-reward-card-main"
            wire:click="select({{ $card['id'] }})"
            aria-label="View {{ $card['name'] }}"
          >
            <span class="w14-reward-card-number">{{ $card['number'] }}</span>
            <span class="w14-reward-card-state">{{ $card['status_label'] }}</span>

            <span class="w14-reward-card-art">
              @if($card['has_custom_image'])
                <img src="{{ $card['image_url'] }}" alt="{{ $card['name'] }}">
              @else
                <span class="w14-reward-placeholder" aria-hidden="true">
                  <i class="icon-base ti tabler-gift"></i>
                  <b>{{ $card['number'] }}</b>
                </span>
              @endif
            </span>

            <span class="w14-reward-card-title">{{ $card['name'] }}</span>
            <span class="w14-reward-card-points">{{ number_format($card['points']) }} pts</span>
          </button>

          @if($card['is_redeemed'])
            <span class="w14-reward-card-check" aria-hidden="true">
              <i class="icon-base ti tabler-check"></i>
            </span>
          @elseif($canClaimRewards && $card['is_claimable'])
            <button
              type="button"
              class="w14-reward-card-claim"
              wire:click.stop="openRedeemModal({{ $card['id'] }})"
            >
              Claim
            </button>
          @endif
        </article>
      @endforeach
    </section>
  @endif

  <div id="giftDetailModal" class="modal fade w14-reward-detail-modal" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @if($selectedCard)
            <div class="w14-reward-detail-art">
              @if($selectedCard['has_custom_image'])
                <img src="{{ $selectedCard['image_url'] }}" alt="{{ $selectedCard['name'] }}">
              @else
                <span class="w14-reward-placeholder" aria-hidden="true">
                  <i class="icon-base ti tabler-gift"></i>
                  <b>{{ $selectedCard['number'] }}</b>
                </span>
              @endif
            </div>

            <div class="w14-reward-detail-head">
              <span class="w14-reward-status reward-status-{{ $selectedCard['status'] }}">
                {{ $selectedCard['status_label'] }}
              </span>
              <h3>{{ $selectedCard['name'] }}</h3>
              <p>{{ $selectedCard['status_hint'] }}</p>
            </div>

            <div class="w14-reward-detail-meta">
              <div>
                <span><i class="icon-base ti tabler-gift"></i></span>
                <strong>{{ number_format($selectedCard['points']) }}</strong>
                <small>Point target</small>
              </div>
              <div>
                <span><i class="icon-base ti tabler-calendar-check"></i></span>
                <strong>{{ $selectedCard['reached_at'] ?? '-' }}</strong>
                <small>Reached date</small>
              </div>
              <div>
                <span><i class="icon-base ti tabler-check"></i></span>
                <strong>{{ $selectedCard['redeemed_at'] ?? '-' }}</strong>
                <small>Claimed date</small>
              </div>
            </div>

            <div class="w14-reward-detail-actions">
              @if($canClaimRewards && $selectedCard['is_claimable'])
                <button type="button" class="btn btn-primary" wire:click="openRedeemModal({{ $selectedCard['id'] }})">
                  Claim reward
                </button>
              @else
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                  Close
                </button>
              @endif
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div id="giftPinModal" class="modal fade w14-reward-pin-modal" tabindex="-1" aria-hidden="true" wire:ignore.self>
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <form wire:submit.prevent="redeem" autocomplete="off">
          <div class="modal-header">
            <h5 class="modal-title">Enter PIN</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input
              type="text"
              wire:model="pin"
              id="giftpinInput"
              class="form-control w14-pin-input @error('pin') is-invalid @enderror"
              name="w14_reward_pin_{{ $student->id }}_{{ $redeemGiftId ?? 'new' }}"
              autocomplete="off"
              autocapitalize="off"
              spellcheck="false"
              data-lpignore="true"
              data-1p-ignore="true"
              data-form-type="other"
              inputmode="numeric"
              pattern="[0-9]*"
              placeholder="PIN"
            >
            @error('pin')
              <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary w-100">Claim</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  @once
    <style>
      .w14-reward-board {
        --reward-ink: var(--bs-heading-color);
        --reward-muted: var(--bs-secondary-color);
        --reward-surface: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-body-bg)) 92%, white 8%);
        --reward-card: var(--bs-card-bg);
        --reward-line: rgba(var(--bs-primary-rgb), 0.16);
        --reward-blue: var(--bs-primary);
        --reward-blue-soft: rgba(var(--bs-primary-rgb), 0.13);
        --reward-green: #19b66f;
        --reward-green-soft: #dff8eb;
        --reward-gold: #ff9f2f;
        --reward-gold-soft: #fff0d9;
        --reward-slate: #697a8d;
        --reward-slate-soft: #eef4fa;
        max-width: 1460px;
        margin: 0 auto;
        padding: clamp(1rem, 2vw, 1.75rem);
        color: var(--reward-ink);
      }

      [data-bs-theme="dark"] .w14-reward-board {
        --reward-surface: color-mix(in sRGB, var(--bs-paper-bg, var(--bs-body-bg)) 82%, white 6%);
        --reward-card: color-mix(in sRGB, var(--bs-card-bg) 92%, white 8%);
        --reward-line: rgba(var(--bs-primary-rgb), 0.26);
        --reward-green-soft: rgba(25, 182, 111, 0.16);
        --reward-gold-soft: rgba(255, 159, 47, 0.16);
        --reward-slate-soft: rgba(255, 255, 255, 0.08);
      }

      .w14-reward-hero {
        display: grid;
        grid-template-columns: minmax(0, 0.75fr) minmax(280px, 1.25fr);
        gap: clamp(1rem, 2vw, 2rem);
        align-items: end;
        padding: clamp(1rem, 2vw, 1.5rem);
        border: 1px solid var(--reward-line);
        border-radius: 18px;
        background:
          linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.12), transparent 45%),
          var(--reward-surface);
        box-shadow: 0 16px 40px rgba(67, 89, 113, 0.08);
      }

      .w14-reward-kicker {
        display: inline-flex;
        margin-bottom: 0.4rem;
        color: var(--reward-blue);
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
      }

      .w14-reward-hero h2 {
        margin: 0;
        font-size: clamp(1.75rem, 4vw, 3.25rem);
        font-weight: 800;
        letter-spacing: 0;
      }

      .w14-reward-hero p {
        margin: 0.35rem 0 0;
        color: var(--reward-muted);
        font-size: 0.98rem;
      }

      .w14-reward-score {
        display: flex;
        align-items: baseline;
        gap: 0.35rem;
        font-weight: 800;
      }

      .w14-reward-hero-topline {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.85rem;
        margin-bottom: 0.6rem;
      }

      .w14-reward-privacy-toggle {
        min-height: 0;
        margin: 0;
        padding-left: 2.45rem;
      }

      .w14-reward-privacy-toggle .form-check-input {
        width: 2.25rem;
        height: 1.2rem;
        margin-left: -2.45rem;
        cursor: pointer;
        box-shadow: none;
      }

      .w14-reward-privacy-toggle .form-check-input:focus {
        border-color: rgba(var(--bs-primary-rgb), 0.38);
        box-shadow: 0 0 0 0.18rem rgba(var(--bs-primary-rgb), 0.12);
      }

      .w14-reward-score span {
        color: var(--reward-blue);
        font-size: clamp(1.3rem, 3vw, 2.1rem);
      }

      .w14-reward-score small {
        color: var(--reward-muted);
        font-weight: 700;
      }

      .w14-reward-trackbar {
        height: 18px;
        border-radius: 999px;
        overflow: hidden;
        background: color-mix(in sRGB, var(--reward-blue) 16%, white 84%);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.5);
      }

      .w14-reward-trackbar span {
        display: block;
        height: 100%;
        min-width: 0.35rem;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--reward-blue), #33b6ff);
        transition: width 0.45s ease;
      }

      .w14-reward-counts {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: 0.45rem;
        margin-top: 0.75rem;
      }

      .w14-reward-counts span,
      .w14-reward-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 1.65rem;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        line-height: 1;
      }

      .w14-reward-counts .is-ready,
      .reward-status-reached { color: var(--reward-gold); background: var(--reward-gold-soft); }
      .w14-reward-counts .is-claimed,
      .reward-status-redeemed { color: var(--reward-green); background: var(--reward-green-soft); }
      .w14-reward-counts .is-current,
      .reward-status-pending { color: var(--reward-blue); background: var(--reward-blue-soft); }
      .w14-reward-counts .is-upcoming,
      .reward-status-waiting { color: var(--reward-blue); background: var(--reward-blue-soft); }

      .w14-reward-card,
      .w14-reward-detail-art {
        border: 0;
      }

      .w14-reward-card-art img,
      .w14-reward-detail-art img {
        display: block;
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
      }

      .w14-reward-detail-head h3 {
        margin: 0.65rem 0 0.35rem;
        font-size: clamp(1.35rem, 3vw, 2.35rem);
        font-weight: 800;
        letter-spacing: 0;
      }

      .w14-reward-detail-head p {
        max-width: 42rem;
        margin: 0;
        color: var(--reward-muted);
        font-weight: 600;
      }

      .w14-reward-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: clamp(0.85rem, 1.6vw, 1.2rem);
        margin-top: clamp(1.45rem, 3.2vw, 2.35rem);
      }

      .w14-reward-card {
        position: relative;
        min-height: 250px;
        overflow: hidden;
        text-align: left;
        color: var(--reward-ink);
        border: 1px solid var(--reward-line);
        border-radius: 18px;
        background: var(--reward-card);
        box-shadow: 0 14px 32px rgba(67, 89, 113, 0.09);
        transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
      }

      .w14-reward-card:hover,
      .w14-reward-card:focus-within {
        transform: translateY(-2px);
        border-color: rgba(var(--bs-primary-rgb), 0.42);
        box-shadow: 0 18px 38px rgba(67, 89, 113, 0.14);
        outline: none;
      }

      .w14-reward-card-main {
        display: flex;
        flex-direction: column;
        width: 100%;
        min-height: 250px;
        padding: 0.8rem;
        text-align: left;
        color: inherit;
        border: 0;
        background: transparent;
      }

      .w14-reward-card-main:focus-visible {
        outline: 2px solid rgba(var(--bs-primary-rgb), 0.55);
        outline-offset: -0.35rem;
        border-radius: 16px;
      }

      .w14-reward-card.is-selected {
        border-color: var(--reward-blue);
        box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.12), 0 18px 40px rgba(67, 89, 113, 0.14);
      }

      .w14-reward-card-number {
        color: var(--reward-muted);
        font-size: 0.78rem;
        font-weight: 800;
      }

      .w14-reward-card-state {
        position: absolute;
        top: 0.7rem;
        right: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 999px;
        color: var(--reward-muted);
        background: rgba(105, 122, 141, 0.12);
        font-size: 0.68rem;
        font-weight: 800;
      }

      .w14-reward-card-art {
        display: grid;
        flex: 0 0 auto;
        place-items: center;
        width: 100%;
        aspect-ratio: 1 / 0.92;
        margin: 0.45rem 0 0.65rem;
        padding: 0.75rem;
        overflow: hidden;
        border-radius: 15px;
        background: linear-gradient(145deg, color-mix(in sRGB, var(--reward-blue) 9%, white 91%), rgba(255, 255, 255, 0.72));
      }

      .w14-reward-card-art img {
        max-width: 100%;
        max-height: 100%;
      }

      .w14-reward-card-title {
        min-width: 0;
        min-height: 2.35em;
        overflow: hidden;
        color: var(--reward-ink);
        font-size: 0.92rem;
        font-weight: 800;
        line-height: 1.18;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
      }

      .w14-reward-card-points {
        justify-self: start;
        align-self: flex-start;
        margin-top: auto;
        padding: 0.28rem 0.58rem;
        border-radius: 999px;
        color: var(--reward-blue);
        background: var(--reward-blue-soft);
        font-size: 0.78rem;
        font-weight: 800;
      }

      .w14-reward-card-check,
      .w14-reward-card-claim {
        position: absolute;
        right: 0.7rem;
        bottom: 0.7rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        min-height: 2rem;
        border-radius: 999px;
      }

      .w14-reward-card-check {
        color: #fff;
        background: var(--reward-green);
        z-index: 4;
        box-shadow: 0 10px 22px rgba(25, 182, 111, 0.28);
      }

      .w14-reward-card-claim {
        padding: 0 0.65rem;
        color: #fff;
        border: 0;
        background: var(--reward-gold);
        font-size: 0.75rem;
        font-weight: 900;
        z-index: 3;
        box-shadow: 0 10px 20px rgba(255, 159, 47, 0.24);
      }

      .w14-reward-card-claim:focus-visible {
        outline: 2px solid rgba(255, 159, 47, 0.75);
        outline-offset: 2px;
      }

      .reward-state-redeemed {
        position: relative;
        color: color-mix(in sRGB, var(--reward-ink) 64%, transparent);
        background:
          linear-gradient(145deg, rgba(25, 182, 111, 0.13), transparent 60%),
          var(--reward-card);
      }

      .reward-state-redeemed::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 2;
        pointer-events: none;
        background: rgb(149 149 149 / 50%);
        backdrop-filter: saturate(0.72);
      }

      .reward-state-redeemed .w14-reward-card-main {
        filter: saturate(0.68);
      }

      .reward-state-reached {
        border-color: color-mix(in sRGB, var(--reward-gold) 72%, white 28%);
        background:
          linear-gradient(145deg, rgba(255, 159, 47, 0.22), transparent 65%),
          var(--reward-card);
      }

      .reward-state-pending {
        border-color: rgba(var(--bs-primary-rgb), 0.42);
      }

      .reward-state-waiting {
        opacity: 0.86;
      }

      .w14-reward-placeholder {
        position: relative;
        display: grid;
        place-items: center;
        width: min(68%, 8rem);
        aspect-ratio: 1;
        border-radius: 26% 26% 22% 22%;
        color: var(--reward-blue);
        background:
          radial-gradient(circle at 50% 18%, rgba(255, 255, 255, 0.95), transparent 30%),
          linear-gradient(145deg, color-mix(in sRGB, var(--reward-blue) 24%, white 76%), color-mix(in sRGB, var(--reward-blue) 10%, white 90%));
        box-shadow: inset 0 0 0 1px rgba(var(--bs-primary-rgb), 0.14), 0 14px 28px rgba(67, 89, 113, 0.12);
      }

      .w14-reward-placeholder i {
        font-size: clamp(2.3rem, 5vw, 4rem);
      }

      .w14-reward-placeholder b {
        position: absolute;
        right: 0.55rem;
        bottom: 0.45rem;
        font-size: 0.8rem;
        color: rgba(var(--bs-primary-rgb), 0.72);
      }

      .w14-reward-empty {
        display: grid;
        place-items: center;
        margin-top: 1.5rem;
        padding: clamp(2rem, 5vw, 4rem);
        text-align: center;
        border: 1px dashed var(--reward-line);
        border-radius: 20px;
        background: var(--reward-surface);
      }

      .w14-reward-empty-icon {
        display: grid;
        place-items: center;
        width: 4rem;
        height: 4rem;
        margin-bottom: 1rem;
        color: var(--reward-blue);
        border-radius: 18px;
        background: var(--reward-blue-soft);
      }

      .w14-reward-empty-icon i {
        font-size: 2rem;
      }

      .w14-reward-empty h3 {
        margin: 0;
        font-weight: 800;
      }

      .w14-reward-empty p {
        max-width: 34rem;
        margin: 0.5rem 0 0;
        color: var(--reward-muted);
      }

      .w14-reward-detail-modal .modal-content,
      .w14-reward-pin-modal .modal-content {
        border: 0;
        overflow: hidden;
        border-radius: 22px;
        box-shadow: 0 24px 70px rgba(30, 41, 59, 0.22);
      }

      .w14-reward-detail-modal .modal-header {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        z-index: 2;
        padding: 0;
        border: 0;
      }

      .w14-reward-detail-modal .btn-close {
        width: 1.85rem;
        height: 1.85rem;
        padding: 0.52rem;
        border-radius: 999px;
        background-color: var(--bs-card-bg);
        background-size: 0.72rem;
        opacity: 0.72;
        box-shadow: 0 8px 22px rgba(67, 89, 113, 0.16);
      }

      .w14-reward-detail-modal .btn-close:hover,
      .w14-reward-pin-modal .btn-close:hover {
        opacity: 1;
      }

      .w14-reward-detail-modal .modal-body {
        padding: clamp(1rem, 4vw, 1.75rem);
        background:
          linear-gradient(180deg, color-mix(in sRGB, var(--reward-blue) 5%, transparent), transparent 42%),
          var(--reward-card);
      }

      .w14-reward-detail-art {
        display: grid;
        place-items: center;
        min-height: 260px;
        overflow: hidden;
        border-radius: 18px;
        background:
          radial-gradient(circle at 50% 16%, rgba(255, 255, 255, 0.95), transparent 34%),
          linear-gradient(150deg, var(--reward-blue-soft), rgba(25, 182, 111, 0.1));
      }

      .w14-reward-detail-art img {
        max-width: 88%;
        max-height: 235px;
      }

      .w14-reward-detail-head {
        margin-top: 1.25rem;
      }

      .w14-reward-detail-head h3 {
        font-size: 1.45rem;
      }

      .w14-reward-detail-meta {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.7rem;
        margin-top: 1.15rem;
      }

      .w14-reward-detail-meta div {
        min-width: 0;
        padding: 0.9rem;
        border: 1px solid rgba(var(--bs-primary-rgb), 0.08);
        border-radius: 16px;
        background:
          linear-gradient(145deg, rgba(255, 255, 255, 0.62), transparent),
          var(--reward-slate-soft);
      }

      .w14-reward-detail-meta span {
        display: inline-grid;
        place-items: center;
        width: 2.05rem;
        height: 2.05rem;
        margin-bottom: 0.55rem;
        color: var(--reward-blue);
        border-radius: 999px;
        background: rgba(var(--bs-primary-rgb), 0.12);
        box-shadow: inset 0 0 0 1px rgba(var(--bs-primary-rgb), 0.12);
      }

      .w14-reward-pin-modal .modal-header {
        align-items: center;
        padding: 1.15rem 1.25rem 0.4rem;
        border: 0;
      }

      .w14-reward-pin-modal .modal-title {
        font-size: 1.05rem;
        font-weight: 800;
      }

      .w14-reward-pin-modal .btn-close {
        width: 1.85rem;
        height: 1.85rem;
        padding: 0.52rem;
        margin: 0;
        border-radius: 999px;
        background-color: rgba(var(--bs-secondary-rgb), 0.08);
        background-size: 0.72rem;
        opacity: 0.72;
      }

      .w14-reward-pin-modal .modal-body {
        padding: 0.85rem 1.25rem 0.75rem;
      }

      .w14-reward-pin-modal .modal-footer {
        padding: 0 1.25rem 1.25rem;
        border: 0;
      }

      .w14-pin-input {
        -webkit-text-security: disc;
        text-security: disc;
        letter-spacing: 0.28em;
      }

      .w14-pin-input::placeholder {
        letter-spacing: 0;
      }

      .w14-reward-detail-meta strong,
      .w14-reward-detail-meta small {
        display: block;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .w14-reward-detail-meta strong {
        color: var(--reward-ink);
        font-size: 0.95rem;
        font-weight: 800;
      }

      .w14-reward-detail-meta small {
        color: var(--reward-muted);
        font-size: 0.78rem;
        font-weight: 700;
      }

      .w14-reward-detail-actions {
        margin-top: 1.25rem;
      }

      .w14-reward-detail-actions .btn {
        width: 100%;
      }

      @media (max-width: 900px) {
        .w14-reward-hero {
          grid-template-columns: 1fr;
        }

        .w14-reward-score,
        .w14-reward-counts,
        .w14-reward-hero-topline {
          justify-content: flex-start;
        }

        .w14-reward-grid {
          grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
        }

        .w14-reward-card,
        .w14-reward-card-main {
          min-height: 230px;
        }

        .w14-reward-card-art {
          aspect-ratio: 1 / 0.9;
        }
      }

      @media (max-width: 520px) {
        .w14-reward-board {
          padding: 0.8rem;
        }

        .w14-reward-hero {
          border-radius: 16px;
        }

        .w14-reward-hero h2 {
          font-size: 1.75rem;
        }

        .w14-reward-grid {
          grid-template-columns: repeat(2, minmax(132px, 1fr));
          gap: 0.75rem;
        }

        .w14-reward-card,
        .w14-reward-card-main {
          min-height: 214px;
        }

        .w14-reward-card {
          border-radius: 15px;
        }

        .w14-reward-card-main {
          padding: 0.65rem;
        }

        .w14-reward-card-art {
          aspect-ratio: 1 / 0.92;
          padding: 0.55rem;
        }

        .w14-reward-card-title {
          font-size: 0.84rem;
        }

        .w14-reward-detail-meta {
          grid-template-columns: 1fr;
          gap: 0.55rem;
          margin-top: 1rem;
        }

        .w14-reward-detail-modal .modal-dialog {
          margin: 0.75rem auto;
          max-width: calc(100vw - 1.5rem);
        }

        .w14-reward-detail-modal .modal-header {
          top: 0.55rem;
          right: 0.55rem;
        }

        .w14-reward-detail-art {
          min-height: 220px;
          border-radius: 16px;
        }

        .w14-reward-detail-art img {
          max-height: 200px;
        }

        .w14-reward-detail-head {
          margin-top: 0.95rem;
        }

        .w14-reward-detail-meta div {
          display: grid;
          grid-template-columns: 2.25rem minmax(0, 1fr);
          align-items: center;
          column-gap: 0.7rem;
          row-gap: 0.08rem;
          padding: 0.7rem;
        }

        .w14-reward-detail-meta span {
          grid-row: 1 / span 2;
          margin-bottom: 0;
        }

        .w14-reward-detail-meta strong {
          font-size: 0.95rem;
        }

        .w14-reward-detail-actions {
          margin-top: 0.9rem;
        }
      }

      @media (max-width: 360px) {
        .w14-reward-grid {
          grid-template-columns: 1fr;
        }
      }
    </style>
  @endonce

  @once
    <script>
      (function () {
        if (window.w14GiftBoardInitialized) return;
        window.w14GiftBoardInitialized = true;

        document.addEventListener('livewire:initialized', function () {
          const detailId = 'giftDetailModal';
          const pinId = 'giftPinModal';
          const pinInputSelector = '#giftpinInput';

          function modalInstance(id) {
            const el = document.getElementById(id);
            if (!el || !window.bootstrap) return null;
            return bootstrap.Modal.getOrCreateInstance(el);
          }

          function isSmallScreen() {
            return window.matchMedia('(max-width: 768px)').matches;
          }

          window.addEventListener('gift-detail-modal:open', function () {
            modalInstance(detailId)?.show();
          });

          window.addEventListener('gift-pin-modal:open', function () {
            modalInstance(detailId)?.hide();
            const pinModal = modalInstance(pinId);
            pinModal?.show();

            const el = document.getElementById(pinId);
            if (!el || isSmallScreen()) return;

            const focusPin = function () {
              const input = el.querySelector(pinInputSelector);
              input?.focus();
              input?.select?.();
            };

            el.addEventListener('shown.bs.modal', focusPin, { once: true });
            window.setTimeout(focusPin, 80);
          });

          window.addEventListener('gift-pin-modal:close', function () {
            modalInstance(pinId)?.hide();
          });
        });
      })();
    </script>
  @endonce
</div>
