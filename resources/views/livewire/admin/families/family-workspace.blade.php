<div>
  @if (session()->has('success'))
    <div
      class="alert alert-success alert-dismissible"
      role="alert"
      x-data="{ visible: true }"
      x-init="setTimeout(() => visible = false, 5000)"
      x-show="visible"
      x-transition.opacity.duration.250ms
    >
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if (session()->has('info'))
    <div
      class="alert alert-info alert-dismissible"
      role="alert"
      x-data="{ visible: true }"
      x-init="setTimeout(() => visible = false, 5000)"
      x-show="visible"
      x-transition.opacity.duration.250ms
    >
      {{ session('info') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @error('lifecycleAction')
    <div class="alert alert-danger">{{ $message }}</div>
  @enderror

  @php
    $parentInitials = strtoupper(substr($this->parent->first_name ?: 'P', 0, 1)).strtoupper(substr($this->parent->last_name ?: '', 0, 1));
    $parentLoginEmail = $this->parent->user?->email ?: ($this->parent->email ?: '-');
    $parentPhone = $this->parent->phone ?: '-';
    $parentCountry = $this->parentCountry ?: '-';
    $parentJoinedDate = $this->parent->created_at?->format('d M Y') ?: 'Not recorded';
    $parentUpdatedDate = $this->parent->updated_at?->format('d M Y') ?: 'Not recorded';
    $parentLifecycleTone = $this->statusTone($this->parent->lifecycle_status);
    $parentLifecycleLabel = $this->statusLabel($this->parent->lifecycle_status);
    $parentLifecycleIcon = match ($this->parent->lifecycle_status) {
        \App\Enums\FamilyLifecycleStatus::Active->value => 'tabler-circle-check-filled',
        \App\Enums\FamilyLifecycleStatus::PendingActivation->value => 'tabler-clock-hour-4',
        \App\Enums\FamilyLifecycleStatus::Suspended->value => 'tabler-alert-circle-filled',
        \App\Enums\FamilyLifecycleStatus::Archived->value => 'tabler-archive',
        default => 'tabler-help-circle',
    };
    $childrenCount = $this->children->count();
    $familyActions = $this->familyLifecycleActions();
    $canEditParent = $this->canEditParentProfile();
    $canViewStudentDomainLinks = $this->canViewStudentDomainLinks();
  @endphp

  <div class="row g-6 family-workspace-shell">
    <div class="col-12 col-lg-5 col-xl-4">
      <details class="card family-workspace-sidebar family-workspace-mobile-summary d-lg-none">
        <summary class="card-body family-workspace-mobile-summary__trigger">
          <div class="d-flex align-items-start gap-3">
            <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
              <div class="avatar avatar-lg flex-shrink-0">
                <span class="avatar-initial rounded-circle bg-label-primary">
                  {{ $parentInitials }}
                </span>
              </div>
              <div class="min-w-0 flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h5 class="mb-0 text-truncate">{{ $this->parent->display_name }}</h5>
                  <span class="family-workspace-status-icon text-{{ $parentLifecycleTone }}" title="{{ $parentLifecycleLabel }}">
                    <i class="icon-base ti {{ $parentLifecycleIcon }} icon-18px"></i>
                    <span class="visually-hidden">{{ $parentLifecycleLabel }}</span>
                  </span>
                </div>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
              @if ($canEditParent)
                <button
                  type="button"
                  class="btn btn-sm btn-icon btn-text-secondary"
                  wire:click.stop="openParentEditModal"
                  aria-label="Edit parent information for {{ $this->parent->display_name }}"
                >
                  <i class="icon-base ti tabler-edit icon-18px"></i>
                </button>
              @endif
              <i class="icon-base ti tabler-chevron-down family-workspace-mobile-summary__chevron"></i>
            </div>
          </div>
        </summary>
        <div class="border-top">
          <div class="card-body pt-4">
            <div class="family-workspace-sidebar__metric">
              <div class="avatar">
                <div class="avatar-initial rounded bg-label-primary">
                  <i class="icon-base ti tabler-users-group icon-lg"></i>
                </div>
              </div>
              <div>
                <h5 class="mb-0">{{ $childrenCount }}</h5>
                <span>Linked Children</span>
              </div>
            </div>

            <div class="family-workspace-sidebar__contact-actions">
              <button type="button" class="btn btn-sm btn-label-primary" wire:click="setActiveTab('communication')">
                <i class="icon-base ti tabler-message-circle icon-18px me-1"></i>
                Chat
              </button>
              <button type="button" class="btn btn-sm btn-label-secondary" wire:click="setActiveTab('communication')">
                <i class="icon-base ti tabler-mail icon-18px me-1"></i>
                Email
              </button>
            </div>

            <dl class="family-workspace-summary-list family-workspace-summary-list--mobile mb-0">
              <div>
                <dt>Login Email:</dt>
                <dd class="text-break">{{ $parentLoginEmail }}</dd>
              </div>
              <div>
                <dt>Phone:</dt>
                <dd>{{ $parentPhone }}</dd>
              </div>
              <div>
                <dt>Country:</dt>
                <dd>{{ $parentCountry }}</dd>
              </div>
              <div>
                <dt>Joined Date:</dt>
                <dd>{{ $parentJoinedDate }}</dd>
              </div>
            </dl>

            @if ($familyActions !== [])
              <div class="family-workspace-sidebar__actions mt-4">
                @foreach ($familyActions as $action)
                  <button
                    type="button"
                    class="{{ $action['button_class'] }}"
                    wire:key="family-workspace-family-action-mobile-{{ $this->parent->id }}-{{ $action['action'] }}"
                    wire:click="openLifecycleModal('{{ $action['action'] }}', 'family', {{ $this->parent->id }})"
                    wire:loading.attr="disabled"
                  >
                    {{ $action['label'] }} Family
                  </button>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </details>

      <div class="card family-workspace-sidebar family-workspace-sidebar--desktop d-none d-lg-block">
        <div class="card-body p-5 p-xl-6">
          <div class="family-workspace-sidebar__hero">
            <div class="avatar avatar-xl flex-shrink-0">
              <span class="avatar-initial rounded-circle bg-label-primary family-workspace-sidebar__avatar">
                {{ $parentInitials }}
              </span>
            </div>
            <div class="min-w-0 flex-grow-1">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="mb-0 text-truncate">{{ $this->parent->display_name }}</h5>
                <span class="family-workspace-status-icon text-{{ $parentLifecycleTone }}" title="{{ $parentLifecycleLabel }}">
                  <i class="icon-base ti {{ $parentLifecycleIcon }} icon-18px"></i>
                  <span class="visually-hidden">{{ $parentLifecycleLabel }}</span>
                </span>
                @if ($canEditParent)
                  <button
                    type="button"
                    class="btn btn-sm btn-icon btn-text-secondary family-workspace-edit-trigger"
                    wire:click.stop="openParentEditModal"
                    aria-label="Edit parent information for {{ $this->parent->display_name }}"
                  >
                    <i class="icon-base ti tabler-edit icon-18px"></i>
                  </button>
                @endif
              </div>
            </div>
          </div>

          <div class="family-workspace-sidebar__metric">
            <div class="avatar">
              <div class="avatar-initial rounded bg-label-primary">
                <i class="icon-base ti tabler-users-group icon-lg"></i>
              </div>
            </div>
            <div>
              <h5 class="mb-0">{{ $childrenCount }}</h5>
              <span>Linked Children</span>
            </div>
          </div>

          <div class="family-workspace-sidebar__contact-actions">
            <button type="button" class="btn btn-sm btn-label-primary" wire:click="setActiveTab('communication')">
              <i class="icon-base ti tabler-message-circle icon-18px me-1"></i>
              Chat
            </button>
            <button type="button" class="btn btn-sm btn-label-secondary" wire:click="setActiveTab('communication')">
              <i class="icon-base ti tabler-mail icon-18px me-1"></i>
              Email
            </button>
          </div>

          <div class="family-workspace-sidebar__details">
            <h6 class="family-workspace-sidebar__section-title">Details</h6>
            <dl class="family-workspace-summary-list mb-0">
              <div>
                <dt>Login Email:</dt>
                <dd class="text-break">{{ $parentLoginEmail }}</dd>
              </div>
              <div>
                <dt>Phone:</dt>
                <dd>{{ $parentPhone }}</dd>
              </div>
              <div>
                <dt>Country:</dt>
                <dd>{{ $parentCountry }}</dd>
              </div>
              <div>
                <dt>Joined Date:</dt>
                <dd>{{ $parentJoinedDate }}</dd>
              </div>
              <div>
                <dt>Last Update:</dt>
                <dd>{{ $parentUpdatedDate }}</dd>
              </div>
            </dl>
          </div>

          @if ($familyActions !== [])
            <div class="family-workspace-sidebar__actions">
              @foreach ($familyActions as $action)
                <button
                  type="button"
                  class="{{ $action['button_class'] }}"
                  wire:key="family-workspace-family-action-desktop-{{ $this->parent->id }}-{{ $action['action'] }}"
                  wire:click="openLifecycleModal('{{ $action['action'] }}', 'family', {{ $this->parent->id }})"
                  wire:loading.attr="disabled"
                >
                  {{ $action['label'] }} Family
                </button>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-7 col-xl-8">
      <div class="nav-align-top bg-lighter rounded p-2 mb-6 family-workspace-tabs-wrap">
        <ul class="nav nav-pills family-workspace-tabs mb-0" role="tablist">
          @foreach ($this->visibleTabs() as $key => $tab)
            <li class="nav-item" role="presentation" wire:key="family-workspace-tab-{{ $key }}">
              <button
                type="button"
                class="nav-link {{ $this->isActiveTab($key) ? 'active' : '' }}"
                wire:click="setActiveTab('{{ $key }}')"
                role="tab"
                aria-selected="{{ $this->isActiveTab($key) ? 'true' : 'false' }}"
              >
                @if (filled($tab['icon'] ?? null))
                  <i class="icon-base ti {{ $tab['icon'] }} icon-sm me-1_5"></i>
                @endif
                {{ $tab['label'] }}
              </button>
            </li>
          @endforeach
        </ul>
      </div>

      @if ($this->isActiveTab('overview'))
        <div class="card h-100">
          <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <h5 class="card-title mb-0">Children</h5>
              <details class="intake-info intake-info--inline">
                <summary class="intake-info__trigger" aria-label="Open linked children note">
                  <i class="icon-base ti tabler-info-circle icon-18px"></i>
                </summary>
                <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                  Child account status is separate from the family login gate.
                </div>
              </details>
            </div>
            <span class="badge bg-label-secondary">{{ $childrenCount }} child{{ $childrenCount === 1 ? '' : 'ren' }}</span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive d-none d-md-block">
              <table class="table table-hover align-middle mb-0">
                <thead class="text-center">
                  <tr>
                    <th class="text-start">Child</th>
                    <th class="text-center">Class</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Links</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($this->children as $child)
                    <tr wire:key="family-child-{{ $child->id }}">
                      <td class="text-start">
                        <div class="d-flex align-items-center gap-3">
                          <div class="avatar avatar-sm">
                            <span class="avatar-initial rounded-circle bg-label-info">
                              {{ strtoupper(substr($child->first_name ?: 'C', 0, 1)) }}
                            </span>
                          </div>
                          <div class="min-w-0">
                            <div class="text-heading fw-medium text-truncate">{{ $child->display_name }}</div>
                            <div class="small text-body-secondary text-break">{{ $this->childAcademicProfileLabel($child) }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="text-center">
                        <div>{{ $this->currentClassLabel($child) }}</div>
                        @if ($this->hasDuplicateCurrentClassHistory($child))
                          <span class="badge bg-label-warning mt-1">Duplicate class history</span>
                        @endif
                      </td>
                      <td class="text-center">
                        <span class="badge bg-label-{{ $this->statusTone($child->account_status) }}">
                          {{ $this->statusLabel($child->account_status) }}
                        </span>
                        <div class="mt-1">
                          @if(($this->trustedChildSettings[$child->id] ?? false) === true)
                            <span class="badge bg-label-info">Trusted</span>
                          @else
                            <span class="badge bg-label-secondary">Standard review</span>
                          @endif
                        </div>
                      </td>
                      <td class="text-center">
                        @if ($canViewStudentDomainLinks)
                          <div class="d-inline-flex align-items-center justify-content-center gap-2">
                            <a
                              href="{{ route('admin.students.account', $child->id) }}"
                              class="btn btn-sm btn-icon btn-label-secondary rounded-circle family-domain-link"
                              aria-label="Open account page for {{ $child->display_name }}"
                              data-bs-toggle="tooltip"
                              data-bs-placement="top"
                              title="Account"
                            >
                              <i class="icon-base ti tabler-eye icon-18px"></i>
                            </a>
                            <a
                              href="{{ route('admin.students.security', $child->id) }}"
                              class="btn btn-sm btn-icon btn-label-success rounded-circle family-domain-link"
                              aria-label="Open security page for {{ $child->display_name }}"
                              data-bs-toggle="tooltip"
                              data-bs-placement="top"
                              title="Security"
                            >
                              <i class="icon-base ti tabler-shield-lock icon-18px"></i>
                            </a>
                            <a
                              href="{{ route('admin.students.show_reward', $child->id) }}"
                              class="btn btn-sm btn-icon btn-label-primary rounded-circle family-domain-link"
                              aria-label="Open rewards page for {{ $child->display_name }}"
                              data-bs-toggle="tooltip"
                              data-bs-placement="top"
                              title="Rewards"
                            >
                              <i class="icon-base ti tabler-gift icon-18px"></i>
                            </a>
                            <a
                              href="{{ route('admin.calendar.view', ['student' => $child->id, 'source' => 'family-workspace']) }}"
                              class="btn btn-sm btn-icon btn-label-warning rounded-circle family-domain-link"
                              aria-label="Open schedule page for {{ $child->display_name }}"
                              data-bs-toggle="tooltip"
                              data-bs-placement="top"
                              title="Schedule"
                            >
                              <i class="icon-base ti tabler-calendar-week icon-18px"></i>
                            </a>
                          </div>
                        @else
                          <span class="text-body-secondary small">Workspace only</span>
                        @endif
                      </td>
                      <td class="text-center">
                        @php
                          $childActions = $this->childLifecycleActions($child);
                        @endphp

                        @if (count($childActions) > 0)
                          <div class="d-flex flex-wrap justify-content-center gap-2">
                            @foreach ($childActions as $action)
                              <button
                                type="button"
                                class="{{ $action['button_class'] }}"
                                wire:key="family-workspace-child-action-desktop-{{ $child->id }}-{{ $action['action'] }}"
                                wire:click="openLifecycleModal('{{ $action['action'] }}', 'child', {{ $child->id }})"
                                wire:loading.attr="disabled"
                              >
                                {{ $action['label'] }}
                              </button>
                            @endforeach
                          </div>
                        @else
                          <span class="badge bg-label-secondary">All good</span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center text-body-secondary py-5">No linked children yet.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="d-md-none family-workspace-child-stack p-3">
              @forelse ($this->children as $child)
                @php
                  $childActions = $this->childLifecycleActions($child);
                @endphp

                <article class="family-workspace-child-card" wire:key="family-child-mobile-{{ $child->id }}">
                  <div class="family-workspace-child-card__header d-flex align-items-start justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                      <div class="avatar avatar-md flex-shrink-0">
                        <span class="avatar-initial rounded-circle bg-label-info">
                          {{ strtoupper(substr($child->first_name ?: 'C', 0, 1)) }}
                        </span>
                      </div>
                      <div class="min-w-0">
                        <div class="fw-medium text-truncate">{{ $child->display_name }}</div>
                        <div class="small text-body-secondary">{{ $this->childAcademicProfileLabel($child) }}</div>
                      </div>
                    </div>
                    <span class="badge bg-label-{{ $this->statusTone($child->account_status) }}">
                      {{ $this->statusLabel($child->account_status) }}
                    </span>
                  </div>

                  <div class="family-workspace-child-card__meta">
                    @if(($this->trustedChildSettings[$child->id] ?? false) === true)
                      <span class="badge bg-label-info">Trusted</span>
                    @else
                      <span class="badge bg-label-secondary">Standard review</span>
                    @endif
                  </div>

                  @if ($this->hasDuplicateCurrentClassHistory($child))
                    <div class="family-workspace-child-card__meta">
                      <span class="badge bg-label-warning">Duplicate class history</span>
                    </div>
                  @endif

                  <div class="family-workspace-child-card__section">
                    <div class="family-workspace-child-card__label">Links</div>
                    @if ($canViewStudentDomainLinks)
                      <div class="d-inline-flex align-items-center gap-2 flex-wrap">
                        <a
                          href="{{ route('admin.students.account', $child->id) }}"
                          class="btn btn-sm btn-icon btn-label-secondary rounded-circle family-domain-link"
                          aria-label="Open account page for {{ $child->display_name }}"
                          data-bs-toggle="tooltip"
                          data-bs-placement="top"
                          title="Account"
                        >
                          <i class="icon-base ti tabler-eye icon-18px"></i>
                        </a>
                        <a
                          href="{{ route('admin.students.security', $child->id) }}"
                          class="btn btn-sm btn-icon btn-label-success rounded-circle family-domain-link"
                          aria-label="Open security page for {{ $child->display_name }}"
                          data-bs-toggle="tooltip"
                          data-bs-placement="top"
                          title="Security"
                        >
                          <i class="icon-base ti tabler-shield-lock icon-18px"></i>
                        </a>
                        <a
                          href="{{ route('admin.students.show_reward', $child->id) }}"
                          class="btn btn-sm btn-icon btn-label-primary rounded-circle family-domain-link"
                          aria-label="Open rewards page for {{ $child->display_name }}"
                          data-bs-toggle="tooltip"
                          data-bs-placement="top"
                          title="Rewards"
                        >
                          <i class="icon-base ti tabler-gift icon-18px"></i>
                        </a>
                        <a
                          href="{{ route('admin.calendar.view', ['student' => $child->id, 'source' => 'family-workspace']) }}"
                          class="btn btn-sm btn-icon btn-label-warning rounded-circle family-domain-link"
                          aria-label="Open schedule page for {{ $child->display_name }}"
                          data-bs-toggle="tooltip"
                          data-bs-placement="top"
                          title="Schedule"
                        >
                          <i class="icon-base ti tabler-calendar-week icon-18px"></i>
                        </a>
                      </div>
                    @else
                      <span class="text-body-secondary small">Workspace only</span>
                    @endif
                  </div>

                  <div class="family-workspace-child-card__section">
                    <div class="family-workspace-child-card__label">Actions</div>
                    @if (count($childActions) > 0)
                      <div class="d-flex flex-wrap gap-2">
                        @foreach ($childActions as $action)
                          <button
                            type="button"
                            class="{{ $action['button_class'] }}"
                            wire:key="family-workspace-child-action-mobile-{{ $child->id }}-{{ $action['action'] }}"
                            wire:click="openLifecycleModal('{{ $action['action'] }}', 'child', {{ $child->id }})"
                            wire:loading.attr="disabled"
                          >
                            {{ $action['label'] }}
                          </button>
                        @endforeach
                      </div>
                    @else
                      <span class="badge bg-label-secondary">All good</span>
                    @endif
                  </div>
                </article>
              @empty
                <div class="text-center text-body-secondary py-4">No linked children yet.</div>
              @endforelse
            </div>
          </div>
        </div>
      @endif

      @if ($this->isActiveTab('security') && $this->showsSecurityOrLogTab())
        @php
          $canRevealCredentials = $this->canRevealCredentials();
          $canSendResetLinks = $this->canSendResetLinks();
          $canGeneratePasswords = $this->canGeneratePasswords();
          $canResendActivationEmails = $this->canResendActivationEmails();
        @endphp

        @if ($this->canViewAccountSecurity())
          <div class="card">
            <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
              <div class="d-flex flex-wrap align-items-center gap-2">
                <h5 class="card-title mb-0">Security</h5>
                <details class="intake-info intake-info--inline">
                  <summary class="intake-info__trigger" aria-label="Open security note">
                    <i class="icon-base ti tabler-info-circle icon-18px"></i>
                  </summary>
                  <div class="intake-info__panel intake-info__panel--header intake-info__panel--ltr" dir="ltr">
                    Credential actions write Account History entries.
                  </div>
                </details>
              </div>
            </div>
            <div class="card-body p-3 p-md-4">
              <div class="family-security-list">
                <article class="family-security-item" wire:key="family-security-parent-{{ $this->parent->id }}">
                  <div class="family-security-item__head">
                    <div class="family-security-item__identity">
                      <div class="fw-medium">{{ $this->parent->display_name }}</div>
                      <div class="small text-body-secondary text-break">{{ $this->parent->user?->email ?: $this->parent->email ?: '-' }} - Parent</div>
                    </div>
                    <div class="family-security-item__badges">
                      <span class="badge bg-label-{{ $this->statusTone($this->parent->lifecycle_status) }}">
                        {{ $this->statusLabel($this->parent->lifecycle_status) }}
                      </span>
                      @unless ($canRevealCredentials)
                        <span class="badge bg-label-secondary">Restricted</span>
                      @endunless
                    </div>
                  </div>
                  <div class="family-security-item__actions">
                    @if ($canRevealCredentials)
                      @if ($this->parent->user && $this->canRevealCredential($this->parent->user))
                        <button type="button" class="btn btn-sm btn-label-success" wire:click="openRevealModal({{ $this->parent->user->id }}, 'parent')">
                          Reveal
                        </button>
                      @else
                        <span class="d-inline-block" tabindex="0" title="Generate a new password or send a reset link first.">
                          <button type="button" class="btn btn-sm btn-label-secondary w-100" disabled>Reveal</button>
                        </span>
                      @endif
                    @endif
                    @if ($canSendResetLinks)
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        wire:click="sendPasswordResetLink({{ $this->parent->user?->id ?? 0 }}, 'parent')"
                        wire:loading.attr="disabled"
                        wire:target="sendPasswordResetLink({{ $this->parent->user?->id ?? 0 }}, 'parent')"
                        @disabled(! $this->parent->user)
                      >
                        <span wire:loading.remove wire:target="sendPasswordResetLink({{ $this->parent->user?->id ?? 0 }}, 'parent')">Send password reset link</span>
                        <span wire:loading wire:target="sendPasswordResetLink({{ $this->parent->user?->id ?? 0 }}, 'parent')">Sending...</span>
                      </button>
                    @endif
                    @if ($canGeneratePasswords)
                      <button type="button" class="btn btn-sm btn-primary" wire:click="generateNewPassword({{ $this->parent->user?->id ?? 0 }}, 'parent')" @disabled(! $this->parent->user)>
                        Generate new password
                      </button>
                    @endif
                    @if ($canResendActivationEmails)
                      <button
                        type="button"
                        class="btn btn-sm btn-label-secondary"
                        wire:click="resendActivationEmail({{ $this->parent->user?->id ?? 0 }}, 'parent')"
                        wire:loading.attr="disabled"
                        wire:target="resendActivationEmail({{ $this->parent->user?->id ?? 0 }}, 'parent')"
                        @disabled(! $this->parent->user)
                      >
                        <span wire:loading.remove wire:target="resendActivationEmail({{ $this->parent->user?->id ?? 0 }}, 'parent')">Resend activation email</span>
                        <span wire:loading wire:target="resendActivationEmail({{ $this->parent->user?->id ?? 0 }}, 'parent')">Sending activation email...</span>
                      </button>
                    @endif
                  </div>
                </article>

                @foreach ($this->children as $child)
                  <article class="family-security-item" wire:key="family-security-child-{{ $child->id }}">
                    <div class="family-security-item__head">
                      <div class="family-security-item__identity">
                        <div class="fw-medium">{{ $child->display_name }}</div>
                        <div class="small text-body-secondary text-break">{{ $child->user?->email ?: '-' }} - Child</div>
                      </div>
                      <div class="family-security-item__badges">
                        <span class="badge bg-label-{{ $this->statusTone($child->account_status) }}">
                          {{ $this->statusLabel($child->account_status) }}
                        </span>
                        @unless ($canRevealCredentials)
                          <span class="badge bg-label-secondary">Restricted</span>
                        @endunless
                      </div>
                    </div>
                    <div class="family-security-item__actions">
                      @if ($canRevealCredentials)
                        @if ($child->user && $this->canRevealCredential($child->user))
                          <button type="button" class="btn btn-sm btn-label-success" wire:click="openRevealModal({{ $child->user->id }}, 'child')">
                            Reveal
                          </button>
                        @else
                          <span class="d-inline-block" tabindex="0" title="Generate a new password or send a reset link first.">
                            <button type="button" class="btn btn-sm btn-label-secondary w-100" disabled>Reveal</button>
                          </span>
                        @endif
                      @endif
                      @if ($canSendResetLinks)
                        <button
                          type="button"
                          class="btn btn-sm btn-outline-primary"
                          wire:click="sendPasswordResetLink({{ $child->user?->id ?? 0 }}, 'child')"
                          wire:loading.attr="disabled"
                          wire:target="sendPasswordResetLink({{ $child->user?->id ?? 0 }}, 'child')"
                          @disabled(! $child->user)
                        >
                          <span wire:loading.remove wire:target="sendPasswordResetLink({{ $child->user?->id ?? 0 }}, 'child')">Send password reset link</span>
                          <span wire:loading wire:target="sendPasswordResetLink({{ $child->user?->id ?? 0 }}, 'child')">Sending...</span>
                        </button>
                      @endif
                      @if ($canGeneratePasswords)
                        <button type="button" class="btn btn-sm btn-primary" wire:click="generateNewPassword({{ $child->user?->id ?? 0 }}, 'child')" @disabled(! $child->user)>
                          Generate new password
                        </button>
                      @endif
                      @if ($canResendActivationEmails)
                        <button
                          type="button"
                          class="btn btn-sm btn-label-secondary"
                          wire:click="resendActivationEmail({{ $child->user?->id ?? 0 }}, 'child')"
                          wire:loading.attr="disabled"
                          wire:target="resendActivationEmail({{ $child->user?->id ?? 0 }}, 'child')"
                          @disabled(! $child->user)
                        >
                          <span wire:loading.remove wire:target="resendActivationEmail({{ $child->user?->id ?? 0 }}, 'child')">Resend activation email</span>
                          <span wire:loading wire:target="resendActivationEmail({{ $child->user?->id ?? 0 }}, 'child')">Sending activation email...</span>
                        </button>
                      @endif
                    </div>
                  </article>
                @endforeach
              </div>
            </div>
          </div>
        @endif

        @if ($this->canViewHistory())
          <div class="card {{ $this->canViewAccountSecurity() ? 'mt-6' : '' }}">
            <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
              <h5 class="card-title mb-0">Activity Log</h5>
              <span class="badge bg-label-secondary">{{ $this->accountHistory->total() }} entries</span>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive d-none d-md-block">
                <table class="table table-hover mb-0">
                  <thead>
                    <tr>
                      <th>Event</th>
                      <th>Reason</th>
                      <th>Subject</th>
                      <th>Actor</th>
                      <th>When</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($this->accountHistory as $entry)
                      @php
                        $actorName = trim(($entry->actor?->first_name ?? '').' '.($entry->actor?->last_name ?? ''));
                        $actorName = $actorName !== '' ? $actorName : ($entry->actor?->name ?: null);
                        $historyChild = $entry->subject_type === 'child'
                          ? $this->children->firstWhere('id', $entry->subject_id)
                          : null;
                      @endphp

                      <tr wire:key="history-{{ $entry->id }}">
                        <td>{{ \Illuminate\Support\Str::headline($entry->event_type) }}</td>
                        <td>{{ $entry->reason_code ? \Illuminate\Support\Str::headline($entry->reason_code) : '-' }}</td>
                        <td>
                          @if ($entry->subject_type === 'child')
                            {{ $historyChild?->display_name ?: 'Child #'.$entry->subject_id }}
                          @elseif ($entry->subject_type === 'parent')
                            {{ $this->parent->display_name }} (Parent)
                          @else
                            {{ $this->parent->display_name }} (Family)
                          @endif
                        </td>
                        <td>
                          <div>{{ $actorName ?: ($entry->actor_role ?: 'System') }}</div>
                          <div class="small text-body-secondary">{{ $entry->actor_role ?: 'system' }}</div>
                        </td>
                        <td>{{ $entry->created_at?->format('d M Y g:i A') ?: '-' }}</td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="5" class="text-center text-body-secondary py-5">No account history yet.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>

              <div class="d-md-none p-3">
                <div class="family-history-stack">
                  @forelse ($this->accountHistory as $entry)
                    @php
                      $actorName = trim(($entry->actor?->first_name ?? '').' '.($entry->actor?->last_name ?? ''));
                      $actorName = $actorName !== '' ? $actorName : ($entry->actor?->name ?: null);
                      $historyChild = $entry->subject_type === 'child'
                        ? $this->children->firstWhere('id', $entry->subject_id)
                        : null;
                      $historySubject = match ($entry->subject_type) {
                        'child' => $historyChild?->display_name ?: 'Child #'.$entry->subject_id,
                        'parent' => $this->parent->display_name.' (Parent)',
                        default => $this->parent->display_name.' (Family)',
                      };
                    @endphp

                    <article class="family-history-card" wire:key="history-mobile-{{ $entry->id }}">
                      <div class="family-history-card__header">
                        <div class="fw-medium">{{ \Illuminate\Support\Str::headline($entry->event_type) }}</div>
                        <div class="small text-body-secondary">{{ $entry->created_at?->format('d M Y g:i A') ?: '-' }}</div>
                      </div>
                      <dl class="family-history-card__details mb-0">
                        <div>
                          <dt>Reason</dt>
                          <dd>{{ $entry->reason_code ? \Illuminate\Support\Str::headline($entry->reason_code) : '-' }}</dd>
                        </div>
                        <div>
                          <dt>Subject</dt>
                          <dd>{{ $historySubject }}</dd>
                        </div>
                        <div>
                          <dt>Actor</dt>
                          <dd>{{ $actorName ?: ($entry->actor_role ?: 'System') }}</dd>
                        </div>
                      </dl>
                    </article>
                  @empty
                    <div class="text-center text-body-secondary py-4">No account history yet.</div>
                  @endforelse
                </div>
              </div>
            </div>
            @if ($this->accountHistory->hasPages())
              <div class="card-footer">
                {!! $this->accountHistory->links('pagination::bootstrap-5') !!}
              </div>
            @endif
          </div>
        @endif
      @endif

      @if ($this->isActiveTab('consultation'))
        <div class="card">
          <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0">Consultation History</h5>
            <span class="badge bg-label-secondary">{{ $this->consultationHistory->count() }} records</span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th>Child</th>
                    <th>Reference</th>
                    <th>Schedule</th>
                    <th>School</th>
                    <th>Outcome</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($this->consultationHistory as $consultation)
                    <tr wire:key="consultation-{{ $consultation->id }}">
                      <td>{{ $consultation->child_name ?: $consultation->student?->display_name }}</td>
                      <td>{{ $consultation->booking?->booking_reference ?: 'Booking #'.$consultation->booking_id }}</td>
                      <td>
                        {{ $consultation->scheduled_date?->format('d M Y') ?: $consultation->booking?->consultation_date?->format('d M Y') ?: '-' }}
                        @if ($consultation->scheduled_time || $consultation->booking?->consultation_time)
                          <span class="text-body-secondary">at {{ $consultation->scheduled_time ?: $consultation->booking?->consultation_time }}</span>
                        @endif
                      </td>
                      <td>{{ $consultation->current_school ?: $consultation->booking?->current_school ?: '-' }}</td>
                      <td>
                        <span class="badge bg-label-info">{{ \Illuminate\Support\Str::headline($consultation->evaluation_outcome ?: 'undecided') }}</span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center text-body-secondary py-5">No consultation history linked yet.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      @endif

      @if ($this->isActiveTab('notes'))
        <div class="card">
          <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Notes placeholder</h5>
          </div>
          <div class="card-body">
            <div class="bg-lighter rounded p-4 d-flex align-items-start gap-3">
              <span class="avatar-initial rounded bg-label-secondary p-2"><i class="icon-base ti tabler-notes icon-28px"></i></span>
              <div>
                <h6 class="mb-1">Notes are not wired yet.</h6>
                <p class="text-body-secondary mb-0">Use the existing booking and student pages until family notes are scheduled.</p>
              </div>
            </div>
          </div>
        </div>
      @endif

      @if ($this->isActiveTab('payments'))
        <div class="card">
          <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Billing placeholder</h5>
          </div>
          <div class="card-body">
            <div class="bg-lighter rounded p-4 d-flex align-items-start gap-3">
              <span class="avatar-initial rounded bg-label-secondary p-2"><i class="icon-base ti tabler-credit-card icon-28px"></i></span>
              <div>
                <h6 class="mb-1">Payments are out of scope.</h6>
                <p class="text-body-secondary mb-0">Billing operations stay deferred to the billing sprint.</p>
              </div>
            </div>
          </div>
        </div>
      @endif

      @if ($this->isActiveTab('communication'))
        <div class="card">
          <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Communication placeholder</h5>
          </div>
          <div class="card-body">
            <div class="bg-lighter rounded p-4 d-flex align-items-start gap-3">
              <span class="avatar-initial rounded bg-label-secondary p-2"><i class="icon-base ti tabler-message-circle icon-28px"></i></span>
              <div>
                <h6 class="mb-1">Communication tools are not wired yet.</h6>
                <p class="text-body-secondary mb-0">Activation and reset emails remain available from Security.</p>
              </div>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>

  @if ($this->canEditParentProfile() || $this->showParentEditModal)
    <div
      wire:ignore.self
      class="modal fade"
      id="familyWorkspaceParentEditModal"
      tabindex="-1"
      aria-labelledby="familyWorkspaceParentEditModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title" id="familyWorkspaceParentEditModalLabel">Edit Parent Information</h5>
              <p class="text-body-secondary small mb-0">Update the main family contact and login email here.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="parentEditFirstName" class="form-label">First Name</label>
                <input
                  id="parentEditFirstName"
                  type="text"
                  class="form-control @error('parentEditForm.first_name') is-invalid @enderror"
                  wire:model.defer="parentEditForm.first_name"
                >
                @error('parentEditForm.first_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label for="parentEditLastName" class="form-label">Last Name</label>
                <input
                  id="parentEditLastName"
                  type="text"
                  class="form-control @error('parentEditForm.last_name') is-invalid @enderror"
                  wire:model.defer="parentEditForm.last_name"
                >
                @error('parentEditForm.last_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12">
                <label for="parentEditEmail" class="form-label">Login Email</label>
                <input
                  id="parentEditEmail"
                  type="email"
                  class="form-control @error('parentEditForm.email') is-invalid @enderror"
                  wire:model.defer="parentEditForm.email"
                >
                @error('parentEditForm.email')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12">
                <label for="parentEditPhone" class="form-label">Phone</label>
                <input
                  id="parentEditPhone"
                  type="text"
                  class="form-control @error('parentEditForm.phone') is-invalid @enderror"
                  wire:model.defer="parentEditForm.phone"
                >
                @error('parentEditForm.phone')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-12">
                <label for="parentEditCountry" class="form-label">Country</label>
                <input
                  id="parentEditCountry"
                  type="text"
                  class="form-control @error('parentEditForm.country') is-invalid @enderror"
                  wire:model.defer="parentEditForm.country"
                >
                @error('parentEditForm.country')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" wire:click="saveParentEdit" wire:loading.attr="disabled">
              Save Parent
            </button>
          </div>
        </div>
      </div>
    </div>
  @endif

  <div
    wire:ignore.self
    class="modal fade"
    id="familyWorkspaceLifecycleModal"
    tabindex="-1"
    aria-labelledby="familyWorkspaceLifecycleModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title" id="familyWorkspaceLifecycleModalLabel">{{ $this->pendingLifecycleActionLabel() }} {{ $this->pendingLifecycleTargetLabel() }}</h5>
            <p class="text-body-secondary small mb-0">{{ $this->pendingLifecycleTargetName() }}</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="familyLifecycleReason" class="form-label">Reason</label>
            <select id="familyLifecycleReason" class="form-select" wire:model="lifecycleReason">
              <option value="">Select a reason</option>
              @foreach ($this->availableReasons as $reason)
                <option value="{{ $reason }}">{{ \Illuminate\Support\Str::headline($reason) }}</option>
              @endforeach
            </select>
            @error('lifecycleReason')
              <div class="text-danger small mt-2">{{ $message }}</div>
            @enderror
          </div>

          @if ($errors->has('lifecycleAction'))
            <div class="alert alert-danger mb-0">{{ $errors->first('lifecycleAction') }}</div>
          @else
            <div class="alert alert-warning mb-0">
              This change is added to the family audit log with the selected reason and current staff role.
            </div>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" wire:click="confirmLifecycleAction" wire:loading.attr="disabled">
            Confirm {{ $this->pendingLifecycleActionLabel() }}
          </button>
        </div>
      </div>
    </div>
  </div>

  @if ($this->canRevealCredentials() || $this->canGeneratePasswords() || $this->showRevealModal)
    <div
      wire:ignore.self
      class="modal fade"
      id="familyWorkspaceRevealModal"
      tabindex="-1"
      aria-labelledby="familyWorkspaceRevealModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title" id="familyWorkspaceRevealModalLabel">Revealed Credential</h5>
              <p class="text-body-secondary small mb-0">This password is shown only inside the secure workspace modal.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Account</label>
              <div class="text-body-secondary">
                {{ $this->revealSubjectType === 'child' ? 'Child account' : 'Parent account' }}
              </div>
            </div>

            <div class="mb-0">
              <label for="revealedCredential" class="form-label">Password</label>
              <div class="input-group">
                <input
                  id="revealedCredential"
                  type="{{ $this->revealMasked ? 'password' : 'text' }}"
                  class="form-control"
                  value="{{ $this->revealedCredential ?? '' }}"
                  readonly
                >
                <button type="button" class="btn btn-outline-secondary" wire:click="toggleRevealMask">
                  {{ $this->revealMasked ? 'Show' : 'Hide' }}
                </button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  @endif

  <style>
    .family-workspace-shell {
      align-items: start;
    }

    .family-workspace-sidebar {
      overflow: hidden;
    }

    .family-workspace-sidebar--desktop {
      position: sticky;
      top: 5.5rem;
    }

    .family-workspace-sidebar__hero {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .family-workspace-sidebar__avatar {
      width: 4.5rem;
      height: 4.5rem;
      font-size: 1.35rem;
    }

    .family-workspace-status-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .family-workspace-sidebar__metric {
      display: flex;
      align-items: center;
      gap: 0.9rem;
      margin-bottom: 1.5rem;
      padding: 1rem 1.05rem;
      border-radius: 1rem;
      background: color-mix(in srgb, var(--bs-primary-bg-subtle, #eef5ff) 24%, #fff 76%);
    }

    .family-workspace-sidebar__metric h5 {
      font-size: 1.2rem;
      margin-bottom: 0;
    }

    .family-workspace-sidebar__metric span {
      color: var(--bs-secondary-color);
      font-weight: 500;
    }

    .family-workspace-sidebar__details {
      margin-bottom: 1.5rem;
    }

    .family-workspace-sidebar__contact-actions {
      display: grid;
      gap: 0.75rem;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      margin-bottom: 1.5rem;
    }

    .family-workspace-sidebar__contact-actions .btn {
      justify-content: center;
      width: 100%;
    }

    .family-workspace-sidebar__actions {
      display: grid;
      gap: 0.7rem;
    }

    .family-workspace-sidebar__actions .btn {
      justify-content: center;
      width: 100%;
    }

    .family-workspace-sidebar__section-title {
      margin-bottom: 1rem;
      padding-bottom: 0.85rem;
      border-bottom: 1px solid color-mix(in srgb, var(--bs-border-color) 74%, transparent);
      color: var(--bs-heading-color);
      font-size: 1.05rem;
      font-weight: 500;
    }

    .family-workspace-summary-list {
      display: grid;
      gap: 0.85rem;
    }

    .family-workspace-summary-list > div {
      display: flex;
      align-items: baseline;
      gap: 0.45rem;
      min-width: 0;
    }

    .family-workspace-summary-list dt {
      flex: 0 0 auto;
      margin: 0;
      color: var(--bs-heading-color);
      font-size: 0.95rem;
      font-weight: 600;
      letter-spacing: normal;
      text-transform: none;
    }

    .family-workspace-summary-list dd {
      min-width: 0;
      margin: 0;
      color: var(--bs-secondary-color);
      font-weight: 400;
    }

    .family-workspace-tabs-wrap {
      background: color-mix(in srgb, var(--bs-body-bg) 82%, var(--bs-primary-bg-subtle, #eef5ff) 18%);
    }

    .family-workspace-tabs {
      display: grid;
      gap: 0.5rem;
      grid-template-columns: repeat(6, minmax(0, 1fr));
    }

    .family-workspace-tabs .nav-item,
    .family-workspace-tabs .nav-link {
      min-width: 0;
      width: 100%;
    }

    .family-workspace-tabs .nav-link {
      align-items: center;
      display: flex;
      gap: 0.35rem;
      justify-content: center;
      min-height: 2.5rem;
      padding-inline: 0.7rem;
      white-space: nowrap;
    }

    .family-workspace-mobile-summary {
      overflow: hidden;
    }

    .family-workspace-mobile-summary__trigger {
      list-style: none;
      cursor: pointer;
    }

    .family-workspace-mobile-summary__trigger::-webkit-details-marker {
      display: none;
    }

    .family-workspace-mobile-summary__chevron {
      transition: transform 0.2s ease;
    }

    .family-workspace-mobile-summary[open] .family-workspace-mobile-summary__chevron {
      transform: rotate(180deg);
    }

    .family-workspace-summary-list--mobile {
      gap: 0.85rem;
    }

    .family-domain-link {
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .family-domain-link:hover,
    .family-domain-link:focus {
      box-shadow: 0 0.25rem 0.65rem rgba(115, 103, 240, 0.18);
      transform: translateY(-1px);
    }

    .family-workspace-child-stack {
      display: grid;
      gap: 0.9rem;
      background: color-mix(in srgb, var(--bs-body-bg) 82%, var(--bs-primary-bg-subtle, #eef5ff) 18%);
    }

    .family-workspace-child-card {
      padding: 1rem;
      border: 1px solid color-mix(in srgb, var(--bs-border-color) 78%, transparent);
      border-radius: 1rem;
      background: var(--bs-body-bg);
    }

    .family-workspace-child-card__header {
      margin-bottom: 0.85rem;
    }

    .family-workspace-child-card__meta {
      margin-bottom: 0.75rem;
    }

    .family-workspace-child-card__section + .family-workspace-child-card__section {
      margin-top: 0.95rem;
      padding-top: 0.95rem;
      border-top: 1px solid color-mix(in srgb, var(--bs-border-color) 76%, transparent);
    }

    .family-workspace-child-card__label {
      margin-bottom: 0.55rem;
      color: var(--bs-secondary-color);
      font-size: 0.74rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }

    .family-security-list {
      display: grid;
      gap: 1rem;
    }

    .family-security-item {
      padding: 1rem;
      border: 1px solid color-mix(in srgb, var(--bs-border-color) 76%, transparent);
      border-radius: 1rem;
      background: var(--bs-body-bg);
    }

    .family-security-item__head {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 0.9rem;
    }

    .family-security-item__badges {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-end;
      gap: 0.5rem;
    }

    .family-security-item__actions {
      display: grid;
      gap: 0.65rem;
      grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr));
    }

    .family-security-item__actions .btn,
    .family-security-item__actions .d-inline-block {
      width: 100%;
    }

    .family-security-item__actions .btn {
      justify-content: center;
    }

    .family-history-stack {
      display: grid;
      gap: 0.85rem;
    }

    .family-history-card {
      padding: 1rem;
      border: 1px solid color-mix(in srgb, var(--bs-border-color) 76%, transparent);
      border-radius: 1rem;
      background: var(--bs-body-bg);
    }

    .family-history-card__header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 0.85rem;
      margin-bottom: 0.8rem;
      padding-bottom: 0.8rem;
      border-bottom: 1px solid color-mix(in srgb, var(--bs-border-color) 76%, transparent);
    }

    .family-history-card__details {
      display: grid;
      gap: 0.8rem;
    }

    .family-history-card__details dt {
      margin: 0 0 0.2rem;
      color: var(--bs-secondary-color);
      font-size: 0.74rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }

    .family-history-card__details dd {
      margin: 0;
      color: var(--bs-heading-color);
      font-weight: 500;
    }

    @media (max-width: 1199.98px) {
      .family-workspace-sidebar--desktop {
        position: static;
      }

      .family-workspace-sidebar--desktop .card-body {
        padding: 1.35rem !important;
      }

      .family-workspace-sidebar__hero {
        margin-bottom: 1.2rem;
      }

      .family-workspace-sidebar__metric,
      .family-workspace-sidebar__contact-actions,
      .family-workspace-sidebar__details {
        margin-bottom: 1.2rem;
      }

      .family-workspace-tabs {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
    }

    @media (max-width: 767.98px) {
      .family-workspace-tabs .nav-link {
        font-size: 0.95rem;
      }

      .family-workspace-summary-list > div {
        align-items: flex-start;
        flex-direction: column;
        gap: 0.15rem;
      }

      .family-security-item__head {
        flex-direction: column;
      }

      .family-security-item__badges {
        justify-content: flex-start;
      }

      .family-history-card__header {
        flex-direction: column;
      }
    }

    @media (max-width: 575.98px) {
      .family-workspace-mobile-summary__trigger {
        padding: 1rem;
      }

      .family-workspace-tabs {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .family-security-item__actions {
        grid-template-columns: minmax(0, 1fr);
      }

      .family-workspace-tabs .nav-link {
        min-height: 2.4rem;
        padding-inline: 0.85rem;
      }
    }
  </style>

  @include('livewire.admin.booking.partials.shared-page-ui')

  <script>
    (() => {
      const initFamilyWorkspaceModals = () => {
        if (window.w14FamilyWorkspaceModalListenersBound || ! window.Livewire) {
          return;
        }

        window.w14FamilyWorkspaceModalListenersBound = true;

        const bindModal = (id, hiddenEvent, openEvent, closeEvent) => {
          const setupElement = () => {
            const el = document.getElementById(id);

            if (! el || typeof bootstrap === 'undefined') {
              return null;
            }

            if (el.dataset.familyWorkspaceModalBound !== '1') {
              el.dataset.familyWorkspaceModalBound = '1';
              el.addEventListener('hidden.bs.modal', () => {
                if (window.Livewire?.dispatch) {
                  window.Livewire.dispatch(hiddenEvent);
                }
              });
            }

            return el;
          };

          setupElement();

          Livewire.on(openEvent, () => {
            const el = setupElement();

            if (el) {
              bootstrap.Modal.getOrCreateInstance(el).show();
            }
          });

          Livewire.on(closeEvent, () => {
            const el = setupElement();

            if (el) {
              bootstrap.Modal.getOrCreateInstance(el).hide();
            }
          });
        };

        bindModal(
          'familyWorkspaceLifecycleModal',
          'lifecycle-modal-hidden',
          'family-workspace-lifecycle-open',
          'family-workspace-lifecycle-close'
        );
        bindModal(
          'familyWorkspaceParentEditModal',
          'parent-edit-modal-hidden',
          'family-workspace-parent-edit-open',
          'family-workspace-parent-edit-close'
        );
        bindModal(
          'familyWorkspaceRevealModal',
          'reveal-modal-hidden',
          'family-workspace-reveal-open',
          'family-workspace-reveal-close'
        );
      };

      if (window.Livewire) {
        initFamilyWorkspaceModals();
      }

      document.addEventListener('livewire:initialized', initFamilyWorkspaceModals);
    })();
  </script>
</div>
