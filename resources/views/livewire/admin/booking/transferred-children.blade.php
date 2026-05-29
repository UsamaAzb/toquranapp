<div class="row g-6">
  @php
    $canViewStudentDomainLinks = $this->canViewStudentDomainLinks();
    $canManageBookingAdmin = auth()->check() && auth()->user()?->hasAnyRole(['admin', 'super_admin']);
  @endphp

  <div class="col-12">
    <div
      class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-6">
      <div>
        <h4 class="mb-1">Transferred Families</h4>
        <p class="text-body-secondary mb-0">Find a transferred family and open the linked student workspace.</p>
      </div>
      @if ($canManageBookingAdmin)
        <div class="queue-page-actions queue-page-actions--with-create">
          <button
            type="button"
            class="btn btn-primary rounded-pill btn-icon"
            wire:click="$dispatch('admin-intake-form:open')"
            title="New Intake"
            aria-label="New Intake"
          >
            <i class="icon-base ti tabler-plus icon-20px"></i>
          </button>
          <a href="{{ route('admin.bookings.livewire') }}" class="btn btn-label-secondary">
            Active Queue
          </a>
          <a href="{{ route('admin.bookings.intake-review') }}" class="btn btn-label-secondary queue-page-link">
            Intake Review
            @if ($pendingIntakeReviewCount > 0)
              <span class="queue-page-link__badge">{{ $pendingIntakeReviewCount > 99 ? '99+' : $pendingIntakeReviewCount }}</span>
            @endif
          </a>
        </div>
      @endif
    </div>
  </div>

  @if ($canManageBookingAdmin)
    <livewire:admin.booking.admin-intake-form />
  @endif

  @if (session()->has('success'))
    <div class="col-12">
      <div class="alert alert-success alert-dismissible mb-0" role="alert">
        {{ session('success') }}
        @if (session()->has('family_workspace_url'))
          <div class="mt-2">
            <a href="{{ session('family_workspace_url') }}" class="btn btn-sm btn-success">
              Open Family Workspace
            </a>
          </div>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  @endif

  @if (session()->has('warning'))
    <div class="col-12">
      <div class="alert alert-warning alert-dismissible mb-0 d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3" role="alert">
        <div>{{ session('warning') }}</div>
        @if (session()->has('intake_review_id'))
          <a href="{{ route('admin.bookings.intake-review') }}" class="btn btn-sm btn-outline-warning">
            Open Intake Review
          </a>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    </div>
  @endif

  @error('familySupport')
    <div class="col-12">
      <div class="alert alert-danger mb-0" role="alert">{{ $message }}</div>
    </div>
  @enderror

  <div class="col-12">
    <div class="card">
      <div class="card-header border-bottom">
        <div class="row m-3 my-0 justify-content-between g-3">
          <div class="col-12 col-lg">
            <h5 class="card-title mb-1">Transferred Archive</h5>
            <p class="card-subtitle mb-0">Transferred accounts only. Search by parent, child, workspace, or ref.</p>
          </div>
          <div class="col-12 col-lg-auto">
            <div class="transferred-toolbar d-flex flex-column flex-sm-row align-items-stretch gap-2">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="icon-base ti tabler-search icon-18px"></i></span>
                <input type="search" class="form-control" placeholder="Search parent, child, student account, or ref"
                  wire:model.live.debounce.300ms="search">
              </div>

              <select class="form-select w-auto" wire:model.live="perPage">
                <option value="10">10 families</option>
                <option value="25">25 families</option>
                <option value="50">50 families</option>
              </select>

              @if ($search !== '' || $perPage !== 10)
                <button type="button" class="btn btn-label-secondary" wire:click="resetListFilters">
                  Reset
                </button>
              @endif
            </div>
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        @if ($bookings->isEmpty())
          <div class="p-5 text-center">
            <div
              class="mx-auto mb-3 d-inline-flex align-items-center justify-content-center rounded-circle bg-label-secondary"
              style="width:64px;height:64px;">
              <i class="icon-base ti tabler-user-check icon-32px"></i>
            </div>
            <h5 class="mb-2">No transferred families found</h5>
            <p class="text-body-secondary mb-3">No transferred family matches the current search yet.</p>
            @if ($search !== '')
              <button type="button" class="btn btn-label-secondary" wire:click="resetListFilters">
                Clear Search
              </button>
            @endif
          </div>
        @else
          <div class="table-responsive d-none d-xl-block">
            <table class="table align-middle mb-0">
              <thead class="table-dark">
                <tr>
                  <th style="width: 48px;"></th>
                  <th style="min-width: 300px;">Parent</th>
                  <th style="min-width: 150px;" class="text-center">Children</th>
                  <th style="min-width: 180px;" class="text-center">Payment</th>
                  <th style="min-width: 180px;" class="text-center">Next Action</th>
                  <th style="min-width: 120px;" class="text-center">Status</th>
                  <th style="min-width: 140px;" class="text-center">Actions</th>
                </tr>
              </thead>
              @foreach ($bookings as $booking)
                <tbody x-data="{ open: true }" wire:key="booking-{{ $booking->id }}-group">
                  @php
                    $visibleChildren = $booking->displayTransferredChildren;
                    $familyWorkspaceUrl = $this->familyWorkspaceUrl($booking);
                    $familyWorkspaceTargetId = $this->familyWorkspaceTargetId($booking);
                    $parentInitials = $this->initials($this->parentDisplayName($booking), 'P');
                    $payment = $this->paymentSummary($booking);
                    $nextAction = $this->nextActionMeta($booking);
                    $statusMeta = $this->parentStatusMeta($booking);
                    $menuActions = $this->accountMenuActions($booking);
                    $familySupportId = $this->familySupportId($booking);
                    $familySupportName = $this->familySupportName($booking);
                  @endphp
                  <tr class="bg-lighter" wire:key="booking-{{ $booking->id }}-header">
                    <td class="py-3 text-center">
                      <button type="button" class="btn btn-sm btn-icon btn-text-secondary"
                        @click="open = ! open"
                        :aria-label="(open ? 'Collapse family ' : 'Expand family ') + @js($this->parentDisplayName($booking))">
                        <i class="icon-base ti tabler-chevron-down icon-18px"
                          :class="open ? 'tabler-chevron-down' : 'tabler-chevron-right'"></i>
                      </button>
                    </td>
                    <td class="py-3">
                      <div class="d-flex align-items-center gap-3">
                        <div class="avatar avatar-sm">
                          <span class="avatar-initial rounded-circle bg-label-primary">{{ $parentInitials }}</span>
                        </div>
                        <div class="d-flex flex-column gap-1">
                          <div class="d-flex flex-wrap align-items-center gap-2">
                            @if ($familyWorkspaceUrl)
                              <a href="{{ $familyWorkspaceUrl }}"
                                class="text-heading fw-medium text-decoration-none">
                                {{ $this->parentDisplayName($booking) }}
                              </a>
                            @else
                              <span class="text-heading fw-medium">{{ $this->parentDisplayName($booking) }}</span>
                            @endif
                          </div>
                          <div class="small text-body-secondary">
                            {{ $this->parentContactEmail($booking) }}
                          </div>
                          <div class="small text-body-secondary">
                            {{ $this->parentContactPhone($booking) }}
                          </div>
                          <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge bg-label-info">Support: {{ $familySupportName }}</span>
                            @if ($canManageBookingAdmin && $familyWorkspaceTargetId)
                              <select
                                class="form-select form-select-sm w-auto"
                                wire:change="assignFamilySupport({{ $familyWorkspaceTargetId }}, $event.target.value)"
                                aria-label="Assign support owner for {{ $this->parentDisplayName($booking) }}"
                              >
                                <option value="">Unassigned</option>
                                @foreach ($supportUsers as $supportUser)
                                  <option value="{{ $supportUser->id }}" @selected($familySupportId === $supportUser->id)>
                                    {{ $supportUser->name }}
                                  </option>
                                @endforeach
                              </select>
                            @endif
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 text-center">
                      <div class="d-flex align-items-center justify-content-center">
                        <ul class="list-unstyled users-list d-flex align-items-center avatar-group m-0">
                          @foreach ($visibleChildren->take(4) as $child)
                            @php
                              $childAvatar = $this->childAvatarUrl($child);
                              $childInitials = $this->initials(
                                  $child->child_name ?: $child->student?->display_name,
                                  'C',
                              );
                            @endphp
                            <li class="avatar avatar-sm pull-up" title="{{ $child->child_name ?: 'Child' }}">
                              @if ($childAvatar)
                                <img src="{{ $childAvatar }}" alt="{{ $child->child_name ?: 'Child avatar' }}"
                                  class="rounded-circle">
                              @else
                                <span
                                  class="avatar-initial rounded-circle bg-label-primary">{{ $childInitials }}</span>
                              @endif
                            </li>
                          @endforeach
                          @if ($visibleChildren->count() > 4)
                            <li class="avatar avatar-sm">
                              <span
                                class="avatar-initial rounded-circle bg-label-secondary">+{{ $visibleChildren->count() - 4 }}</span>
                            </li>
                          @endif
                        </ul>
                      </div>
                    </td>
                    <td class="py-3 text-center">
                      <span class="fw-medium {{ $payment['textClass'] }}">{{ $payment['label'] }}</span>
                    </td>
                    <td class="py-3 text-center">
                      <span class="badge bg-label-{{ $nextAction['tone'] }}">{{ $nextAction['label'] }}</span>
                    </td>
                    <td class="py-3 text-center">
                      <span class="badge bg-label-{{ $statusMeta['tone'] }}">{{ $statusMeta['label'] }}</span>
                    </td>
                    <td class="py-3 text-end">
                      <div class="d-inline-flex align-items-center gap-1">
                        @if ($familyWorkspaceUrl)
                          <a href="{{ $familyWorkspaceUrl }}"
                            class="btn btn-sm btn-icon btn-text-secondary"
                            aria-label="View Family Workspace for {{ $this->parentDisplayName($booking) }}">
                            <i class="icon-base ti tabler-eye icon-20px"></i>
                          </a>
                          @if ($canViewStudentDomainLinks && $familyWorkspaceTargetId && $menuActions !== [])
                            <div class="dropdown">
                              <button type="button" class="btn btn-sm btn-icon btn-text-secondary"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="icon-base ti tabler-dots-vertical icon-20px"></i>
                              </button>
                              <ul class="dropdown-menu dropdown-menu-end">
                                @foreach ($menuActions as $menuAction)
                                  <li>
                                    <button
                                      type="button"
                                      class="dropdown-item"
                                      wire:click="openLifecycleModal('{{ $menuAction['action'] }}', 'family', {{ $familyWorkspaceTargetId }})"
                                    >
                                      {{ $menuAction['label'] }}
                                    </button>
                                  </li>
                                @endforeach
                              </ul>
                            </div>
                          @endif
                        @else
                          <button type="button" class="btn btn-sm btn-label-secondary" disabled>
                            Unavailable
                          </button>
                        @endif
                      </div>
                    </td>
                  </tr>

                  @foreach ($visibleChildren as $child)
                      @php
                        $student = $child->student;
                        $serviceLabels = collect(explode(',', $this->serviceSummary($child)))
                            ->map(fn($service) => trim($service))
                            ->filter()
                            ->values();
                        $childAvatar = $this->childAvatarUrl($child);
                        $childInitials = $this->initials($child->child_name ?: $student?->display_name, 'C');
                        $childStatusMeta = $this->childStatusMeta($child);
                        $childMenuActions = $this->childMenuActions($child);
                        $accountAccess = $this->childAccountAccess($child);
                      @endphp
                      <tr wire:key="booking-{{ $booking->id }}-child-{{ $child->id }}" x-show="open">
                        <td class="py-3"></td>
                        <td class="py-3" style="min-width: 300px;">
                          <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-sm">
                              @if ($childAvatar)
                                <img src="{{ $childAvatar }}" alt="{{ $child->child_name ?: 'Child avatar' }}"
                                  class="rounded-circle">
                              @else
                                <span
                                  class="avatar-initial rounded-circle bg-label-primary">{{ $childInitials }}</span>
                              @endif
                            </div>
                            <div class="d-flex flex-column gap-1">
                              <span class="text-heading fw-medium">{{ $child->child_name ?: 'Unnamed child' }}</span>
                              <span
                                class="small text-body-secondary">{{ $child->school_system ?: ($student?->school_system ?: '-') }}
                                | {{ $this->childGradeDisplay($child) }}</span>
                            </div>
                          </div>
                        </td>
                        <td class="py-3 text-center" style="min-width: 150px;">
                          <div class="d-flex flex-column align-items-center gap-1">
                            <span class="small text-body-secondary">{{ $accountAccess['username'] }}</span>
                          </div>
                        </td>
                        <td class="py-3 text-center" style="min-width: 180px;">
                          <div class="d-flex flex-wrap justify-content-center gap-1">
                            @foreach ($serviceLabels as $serviceLabel)
                              <span class="badge bg-label-info">{{ $serviceLabel }}</span>
                            @endforeach
                          </div>
                        </td>
                        <td class="py-3 text-center" style="min-width: 180px;">
                          @if ($canViewStudentDomainLinks)
                            <div class="d-inline-flex align-items-center justify-content-center gap-2">
                              <a href="{{ route('admin.students.show_reward', $student->id) }}"
                                class="btn btn-sm btn-icon btn-label-primary rounded-circle text-primary child-quick-action"
                                aria-label="Open reward agreement for {{ $child->child_name ?: 'this child' }}"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Reward System">
                                <i class="icon-base ti tabler-gift icon-18px"></i>
                              </a>
                              <a href="{{ route('admin.students.security', $student->id) }}"
                                class="btn btn-sm btn-icon btn-label-success rounded-circle text-success child-quick-action"
                                aria-label="Open security page for {{ $child->child_name ?: 'this child' }}"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Security">
                                <i class="icon-base ti tabler-shield-lock icon-18px"></i>
                              </a>
                              <a href="{{ route('admin.calendar.view', ['student' => $student->id, 'source' => 'transferred-children']) }}"
                                class="btn btn-sm btn-icon btn-label-warning rounded-circle text-warning child-quick-action"
                                aria-label="Open schedule page for {{ $child->child_name ?: 'this child' }}"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Schedule">
                                <i class="icon-base ti tabler-calendar-week icon-18px"></i>
                              </a>
                            </div>
                          @else
                            <span class="small text-body-secondary">Workspace only</span>
                          @endif
                        </td>
                        <td class="py-3 text-center" style="min-width: 120px;">
                          <span
                            class="badge bg-label-{{ $childStatusMeta['tone'] }}">{{ $childStatusMeta['label'] }}</span>
                        </td>
                        <td class="py-3 text-center" style="min-width: 140px;">
                          @if ($canViewStudentDomainLinks)
                            <div class="d-inline-flex align-items-center justify-content-center gap-1">
                              <a href="{{ route('admin.students.account', $student->id) }}"
                                class="btn btn-sm btn-icon btn-text-secondary"
                                aria-label="Edit student account for {{ $child->child_name ?: 'this child' }}">
                                <i class="icon-base ti tabler-edit icon-20px"></i>
                              </a>
                              <a href="{{ route('admin.students.account', $student->id) }}"
                                class="btn btn-sm btn-icon btn-text-secondary"
                                aria-label="Open student account for {{ $child->child_name ?: 'this child' }}">
                                <i class="icon-base ti tabler-eye icon-20px"></i>
                              </a>
                              @if ($student && $childMenuActions !== [])
                                <div class="dropdown">
                                  <button type="button" class="btn btn-sm btn-icon btn-text-secondary"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="icon-base ti tabler-dots-vertical icon-20px"></i>
                                  </button>
                                  <ul class="dropdown-menu dropdown-menu-end">
                                    @foreach ($childMenuActions as $menuAction)
                                      <li>
                                        <button
                                          type="button"
                                          class="dropdown-item"
                                          wire:click="openLifecycleModal('{{ $menuAction['action'] }}', 'child', {{ $student->id }})"
                                        >
                                          {{ $menuAction['label'] }}
                                        </button>
                                      </li>
                                    @endforeach
                                  </ul>
                                </div>
                              @endif
                            </div>
                          @else
                            <span class="small text-body-secondary">Workspace only</span>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                </tbody>
              @endforeach
            </table>
          </div>

          <div class="d-xl-none p-3 p-md-4">
            <div class="d-flex flex-column gap-3">
              @foreach ($bookings as $booking)
                @php
                    $visibleChildren = $booking->displayTransferredChildren;
                    $familyWorkspaceUrl = $this->familyWorkspaceUrl($booking);
                    $familyWorkspaceTargetId = $this->familyWorkspaceTargetId($booking);
                    $parentInitials = $this->initials($this->parentDisplayName($booking), 'P');
                    $payment = $this->paymentSummary($booking);
                    $nextAction = $this->nextActionMeta($booking);
                  $statusMeta = $this->parentStatusMeta($booking);
                  $menuActions = $this->accountMenuActions($booking);
                  $familySupportId = $this->familySupportId($booking);
                  $familySupportName = $this->familySupportName($booking);
                @endphp

                <div class="border rounded-3 overflow-hidden mobile-family-card" :class="{ 'mobile-family-card--open': open }" wire:key="mobile-booking-{{ $booking->id }}" x-data="{ open: true }">
                  <div class="mobile-family-card__header p-3">
                    <div class="mobile-family-card__top d-flex flex-column flex-sm-row align-items-start justify-content-between gap-3">
                      <div class="mobile-family-card__identity d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                        <button
                          type="button"
                          class="btn btn-sm btn-icon btn-text-secondary flex-shrink-0 mobile-family-card__toggle"
                          @click="open = ! open"
                          :aria-label="(open ? 'Collapse family ' : 'Expand family ') + @js($this->parentDisplayName($booking))"
                        >
                          <i class="icon-base ti tabler-chevron-down icon-18px" :class="open ? 'tabler-chevron-down' : 'tabler-chevron-right'"></i>
                        </button>

                        <div class="avatar avatar-sm flex-shrink-0">
                          <span class="avatar-initial rounded-circle bg-label-primary">{{ $parentInitials }}</span>
                        </div>

                        <div class="min-w-0 flex-grow-1">
                          @if ($familyWorkspaceUrl)
                            <a href="{{ $familyWorkspaceUrl }}" class="text-heading fw-medium text-decoration-none d-block">
                              {{ $this->parentDisplayName($booking) }}
                            </a>
                          @else
                            <span class="text-heading fw-medium d-block">{{ $this->parentDisplayName($booking) }}</span>
                          @endif
                          <div class="small text-body-secondary text-break">{{ $this->parentContactEmail($booking) }}</div>
                          <div class="small text-body-secondary">{{ $this->parentContactPhone($booking) }}</div>
                          <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                            <span class="badge bg-label-info">Support: {{ $familySupportName }}</span>
                            @if ($canManageBookingAdmin && $familyWorkspaceTargetId)
                              <select
                                class="form-select form-select-sm w-auto"
                                wire:change="assignFamilySupport({{ $familyWorkspaceTargetId }}, $event.target.value)"
                                aria-label="Assign support owner for {{ $this->parentDisplayName($booking) }}"
                              >
                                <option value="">Unassigned</option>
                                @foreach ($supportUsers as $supportUser)
                                  <option value="{{ $supportUser->id }}" @selected($familySupportId === $supportUser->id)>
                                    {{ $supportUser->name }}
                                  </option>
                                @endforeach
                              </select>
                            @endif
                          </div>
                        </div>
                      </div>

                      @if ($familyWorkspaceUrl)
                        <div class="mobile-family-card__actions d-none d-sm-inline-flex align-items-center gap-1 flex-shrink-0">
                          <a
                            href="{{ $familyWorkspaceUrl }}"
                            class="btn btn-sm btn-icon btn-text-secondary"
                            aria-label="View Family Workspace for {{ $this->parentDisplayName($booking) }}"
                          >
                            <i class="icon-base ti tabler-eye icon-20px"></i>
                          </a>
                          @if ($canViewStudentDomainLinks && $familyWorkspaceTargetId && $menuActions !== [])
                            <div class="dropdown">
                              <button type="button" class="btn btn-sm btn-icon btn-text-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="icon-base ti tabler-dots-vertical icon-20px"></i>
                              </button>
                              <ul class="dropdown-menu dropdown-menu-end">
                                @foreach ($menuActions as $menuAction)
                                  <li>
                                    <button
                                      type="button"
                                      class="dropdown-item"
                                      wire:click="openLifecycleModal('{{ $menuAction['action'] }}', 'family', {{ $familyWorkspaceTargetId }})"
                                    >
                                      {{ $menuAction['label'] }}
                                    </button>
                                  </li>
                                @endforeach
                              </ul>
                            </div>
                          @endif
                        </div>
                      @endif
                    </div>

                    <div class="table-responsive mt-3">
                      <table class="table mobile-details-table mb-0">
                        <tbody>
                          <tr>
                            <td>Children:</td>
                            <td>
                              <ul class="list-unstyled users-list d-flex align-items-center avatar-group m-0">
                                @foreach ($visibleChildren->take(4) as $child)
                                  @php
                                    $childAvatar = $this->childAvatarUrl($child);
                                    $childInitials = $this->initials($child->child_name ?: $child->student?->display_name, 'C');
                                  @endphp
                                  <li class="avatar avatar-sm pull-up" title="{{ $child->child_name ?: 'Child' }}">
                                    @if ($childAvatar)
                                      <img src="{{ $childAvatar }}" alt="{{ $child->child_name ?: 'Child avatar' }}" class="rounded-circle">
                                    @else
                                      <span class="avatar-initial rounded-circle bg-label-primary">{{ $childInitials }}</span>
                                    @endif
                                  </li>
                                @endforeach
                                @if ($visibleChildren->count() > 4)
                                  <li class="avatar avatar-sm">
                                    <span class="avatar-initial rounded-circle bg-label-secondary">+{{ $visibleChildren->count() - 4 }}</span>
                                  </li>
                                @endif
                              </ul>
                            </td>
                          </tr>
                          <tr>
                            <td>Payment:</td>
                            <td class="fw-medium {{ $payment['textClass'] }}">{{ $payment['label'] }}</td>
                          </tr>
                          <tr>
                            <td>Next Action:</td>
                            <td><span class="badge bg-label-{{ $nextAction['tone'] }}">{{ $nextAction['label'] }}</span></td>
                          </tr>
                          <tr>
                            <td>Status:</td>
                            <td><span class="badge bg-label-{{ $statusMeta['tone'] }}">{{ $statusMeta['label'] }}</span></td>
                          </tr>
                          @if ($familyWorkspaceUrl)
                            <tr class="d-sm-none">
                              <td>Actions:</td>
                              <td>
                                <div class="d-inline-flex align-items-center gap-1 flex-wrap">
                                  <a
                                    href="{{ $familyWorkspaceUrl }}"
                                    class="btn btn-sm btn-icon btn-text-secondary"
                                    aria-label="View Family Workspace for {{ $this->parentDisplayName($booking) }}"
                                  >
                                    <i class="icon-base ti tabler-eye icon-20px"></i>
                                  </a>
                                  @if ($canViewStudentDomainLinks && $familyWorkspaceTargetId && $menuActions !== [])
                                    <div class="dropdown">
                                      <button type="button" class="btn btn-sm btn-icon btn-text-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="icon-base ti tabler-dots-vertical icon-20px"></i>
                                      </button>
                                      <ul class="dropdown-menu dropdown-menu-end">
                                        @foreach ($menuActions as $menuAction)
                                          <li>
                                            <button
                                              type="button"
                                              class="dropdown-item"
                                              wire:click="openLifecycleModal('{{ $menuAction['action'] }}', 'family', {{ $familyWorkspaceTargetId }})"
                                            >
                                              {{ $menuAction['label'] }}
                                            </button>
                                          </li>
                                        @endforeach
                                      </ul>
                                    </div>
                                  @endif
                                </div>
                              </td>
                            </tr>
                          @endif
                        </tbody>
                      </table>
                    </div>
                  </div>

                    <div class="mobile-family-card__children" x-show="open">
                      @foreach ($visibleChildren as $child)
                        @php
                          $student = $child->student;
                          $serviceLabels = collect(explode(',', $this->serviceSummary($child)))
                              ->map(fn ($service) => trim($service))
                              ->filter()
                              ->values();
                          $childAvatar = $this->childAvatarUrl($child);
                          $childInitials = $this->initials($child->child_name ?: $student?->display_name, 'C');
                          $childStatusMeta = $this->childStatusMeta($child);
                          $childMenuActions = $this->childMenuActions($child);
                          $accountAccess = $this->childAccountAccess($child);
                        @endphp

                        <div class="mobile-child-card p-3" wire:key="mobile-booking-{{ $booking->id }}-child-{{ $child->id }}">
                          <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                            <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                              <div class="avatar avatar-sm flex-shrink-0">
                                @if ($childAvatar)
                                  <img src="{{ $childAvatar }}" alt="{{ $child->child_name ?: 'Child avatar' }}" class="rounded-circle">
                                @else
                                  <span class="avatar-initial rounded-circle bg-label-primary">{{ $childInitials }}</span>
                                @endif
                              </div>
                              <div class="min-w-0">
                                <div class="text-heading fw-medium">{{ $child->child_name ?: 'Unnamed child' }}</div>
                                <div class="small text-body-secondary">{{ $child->school_system ?: ($student?->school_system ?: '-') }} | {{ $this->childGradeDisplay($child) }}</div>
                              </div>
                            </div>
                            <span class="badge bg-label-{{ $childStatusMeta['tone'] }} flex-shrink-0">{{ $childStatusMeta['label'] }}</span>
                          </div>

                          <div class="table-responsive mt-2">
                            <table class="table mobile-details-table mobile-details-table--child mb-0">
                              <tbody>
                                <tr>
                                  <td>Account:</td>
                                  <td>
                                    <div class="small text-body-secondary">{{ $accountAccess['username'] }}</div>
                                  </td>
                                </tr>
                                <tr>
                                  <td>Services:</td>
                                  <td>
                                    <div class="d-inline-flex flex-wrap justify-content-center gap-1">
                                      @foreach ($serviceLabels as $serviceLabel)
                                        <span class="badge bg-label-info">{{ $serviceLabel }}</span>
                                      @endforeach
                                    </div>
                                  </td>
                                </tr>
                                <tr>
                                  <td>Shortcuts:</td>
                                  <td>
                                    @if ($canViewStudentDomainLinks)
                                      <div class="d-inline-flex align-items-center gap-2 flex-wrap">
                                        <a
                                          href="{{ route('admin.students.show_reward', $student->id) }}"
                                          class="btn btn-sm btn-icon btn-label-primary rounded-circle text-primary child-quick-action"
                                          aria-label="Open reward agreement for {{ $child->child_name ?: 'this child' }}"
                                          data-bs-toggle="tooltip"
                                          data-bs-placement="top"
                                          title="Reward System"
                                        >
                                          <i class="icon-base ti tabler-gift icon-18px"></i>
                                        </a>
                                        <a
                                          href="{{ route('admin.students.security', $student->id) }}"
                                          class="btn btn-sm btn-icon btn-label-success rounded-circle text-success child-quick-action"
                                          aria-label="Open security page for {{ $child->child_name ?: 'this child' }}"
                                          data-bs-toggle="tooltip"
                                          data-bs-placement="top"
                                          title="Security"
                                        >
                                          <i class="icon-base ti tabler-shield-lock icon-18px"></i>
                                        </a>
                                        <a
                                          href="{{ route('admin.calendar.view', ['student' => $student->id, 'source' => 'transferred-children']) }}"
                                          class="btn btn-sm btn-icon btn-label-warning rounded-circle text-warning child-quick-action"
                                          aria-label="Open schedule page for {{ $child->child_name ?: 'this child' }}"
                                          data-bs-toggle="tooltip"
                                          data-bs-placement="top"
                                          title="Schedule"
                                        >
                                          <i class="icon-base ti tabler-calendar-week icon-18px"></i>
                                        </a>
                                      </div>
                                    @else
                                      <span class="small text-body-secondary">Workspace only</span>
                                    @endif
                                  </td>
                                </tr>
                                <tr>
                                  <td>Status:</td>
                                  <td><span class="badge bg-label-{{ $childStatusMeta['tone'] }}">{{ $childStatusMeta['label'] }}</span></td>
                                </tr>
                                <tr>
                                  <td>Actions:</td>
                                  <td>
                                    @if ($canViewStudentDomainLinks)
                                      <div class="d-inline-flex align-items-center gap-1 flex-wrap">
                                        <a
                                          href="{{ route('admin.students.account', $student->id) }}"
                                          class="btn btn-sm btn-icon btn-text-secondary"
                                          aria-label="Edit student account for {{ $child->child_name ?: 'this child' }}"
                                        >
                                          <i class="icon-base ti tabler-edit icon-20px"></i>
                                        </a>
                                        <a
                                          href="{{ route('admin.students.account', $student->id) }}"
                                          class="btn btn-sm btn-icon btn-text-secondary"
                                          aria-label="Open student account for {{ $child->child_name ?: 'this child' }}"
                                        >
                                          <i class="icon-base ti tabler-eye icon-20px"></i>
                                        </a>
                                        @if ($student && $childMenuActions !== [])
                                          <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-icon btn-text-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                                              <i class="icon-base ti tabler-dots-vertical icon-20px"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                              @foreach ($childMenuActions as $menuAction)
                                                <li>
                                                  <button
                                                    type="button"
                                                    class="dropdown-item"
                                                    wire:click="openLifecycleModal('{{ $menuAction['action'] }}', 'child', {{ $student->id }})"
                                                  >
                                                    {{ $menuAction['label'] }}
                                                  </button>
                                                </li>
                                              @endforeach
                                            </ul>
                                          </div>
                                        @endif
                                      </div>
                                    @else
                                      <span class="small text-body-secondary">Workspace only</span>
                                    @endif
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      @endforeach
                    </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>

      @if (!$bookings->isEmpty())
        <div
          class="card-footer d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3">
          <div class="small text-body-secondary">
            Showing {{ $bookings->firstItem() }}-{{ $bookings->lastItem() }} of {{ $bookings->total() }} transferred
            famil{{ $bookings->total() === 1 ? 'y' : 'ies' }}.
          </div>
          {{ $bookings->links() }}
        </div>
      @endif
    </div>
  </div>

  <div
    wire:ignore.self
    class="modal fade"
    id="transferredChildrenLifecycleModal"
    tabindex="-1"
    aria-labelledby="transferredChildrenLifecycleModalLabel"
    aria-hidden="true"
  >
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title" id="transferredChildrenLifecycleModalLabel">{{ $this->pendingLifecycleActionLabel() }} {{ $this->pendingLifecycleTargetLabel() }}</h5>
            <p class="text-body-secondary small mb-0">{{ $this->pendingLifecycleTargetName() }}</p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="transferredLifecycleReason" class="form-label">Reason</label>
            <select id="transferredLifecycleReason" class="form-select" wire:model="lifecycleReason">
              <option value="">Select a reason</option>
              @foreach ($this->availableLifecycleReasons() as $reason)
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
              This change is added to the family audit log with the selected reason.
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
  @include('livewire.admin.booking.partials.shared-page-ui')
  <style>
    .child-quick-action {
      transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }

    .child-quick-action.btn-label-primary:hover,
    .child-quick-action.btn-label-primary:focus {
      background-color: color-mix(in srgb, var(--bs-primary) 88%, #000 12%);
      border-color: color-mix(in srgb, var(--bs-primary) 88%, #000 12%);
      color: #fff !important;
      box-shadow: 0 0.25rem 0.65rem color-mix(in srgb, var(--bs-primary) 30%, transparent);
      transform: translateY(-1px);
    }

    .child-quick-action.btn-label-success:hover,
    .child-quick-action.btn-label-success:focus {
      background-color: color-mix(in srgb, var(--bs-success) 88%, #000 12%);
      border-color: color-mix(in srgb, var(--bs-success) 88%, #000 12%);
      color: #fff !important;
      box-shadow: 0 0.25rem 0.65rem color-mix(in srgb, var(--bs-success) 30%, transparent);
      transform: translateY(-1px);
    }

    .child-quick-action.btn-label-warning:hover,
    .child-quick-action.btn-label-warning:focus {
      background-color: color-mix(in srgb, var(--bs-warning) 82%, #000 18%);
      border-color: color-mix(in srgb, var(--bs-warning) 82%, #000 18%);
      color: #fff !important;
      box-shadow: 0 0.25rem 0.65rem color-mix(in srgb, var(--bs-warning) 28%, transparent);
      transform: translateY(-1px);
    }

    .mobile-family-card {
      background: #f3f5f8;
      border-color: #d8dee8 !important;
      border-left-width: 3px !important;
      transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .mobile-family-card--open {
      border-left-color: var(--bs-primary) !important;
      box-shadow: 0 0.35rem 1rem color-mix(in srgb, var(--bs-primary) 7%, transparent);
    }

    .mobile-family-card__header {
      background: #eceff4;
      border-bottom: 1px solid #d8dee8;
      position: relative;
    }

    .mobile-family-card__children {
      display: grid;
      gap: 0.75rem;
      padding: 0.75rem;
      background: #f8fafc;
    }

    .mobile-child-card {
      background: #fff;
      border: 1px solid #e1e6ef;
      border-radius: 0.9rem;
    }

    .mobile-family-card__actions {
      align-self: flex-end;
    }

    .transferred-toolbar .input-group {
      min-width: min(30rem, 48vw);
    }

    .transferred-toolbar .form-select {
      min-width: 8.75rem;
    }

    .mobile-details-table {
      --bs-table-bg: transparent;
      --bs-table-striped-bg: transparent;
      --bs-table-border-color: color-mix(in srgb, var(--bs-border-color) 72%, transparent);
      margin-bottom: 0;
    }

    .mobile-details-table > :not(caption) > * > * {
      padding: 0.85rem 0.9rem;
      vertical-align: middle;
      background: transparent;
    }

    .mobile-details-table td:first-child {
      width: 96px;
      color: var(--bs-secondary-color);
      font-weight: 500;
      white-space: nowrap;
    }

    .mobile-details-table td:last-child {
      text-align: center;
    }

    .mobile-details-table td:last-child .d-inline-flex,
    .mobile-details-table td:last-child .d-flex,
    .mobile-details-table td:last-child .users-list {
      width: 100%;
      justify-content: center;
    }

    .mobile-details-table--child td:first-child {
      width: 88px;
    }

    @media (min-width: 768px) and (max-width: 1199.98px) {
      .mobile-family-card__top {
        align-items: center !important;
      }

      .mobile-family-card__actions {
        align-self: center;
      }
    }

    @media (max-width: 767.98px) {
      .mobile-family-card__header .btn.btn-sm.btn-icon,
      .mobile-family-card__children .btn.btn-sm.btn-icon {
        width: 2.5rem;
        height: 2.5rem;
      }

      .mobile-family-card__toggle {
        position: absolute;
        inset-block-start: 0.65rem;
        inset-inline-end: 0.65rem;
        width: 1.75rem !important;
        height: 1.75rem !important;
        min-width: 1.75rem !important;
        border-radius: 999px;
        background: color-mix(in srgb, var(--bs-primary) 8%, transparent);
        color: var(--bs-primary) !important;
        z-index: 1;
      }

      .mobile-family-card__toggle:hover,
      .mobile-family-card__toggle:focus {
        background: color-mix(in srgb, var(--bs-primary) 14%, transparent);
      }

      .mobile-family-card__toggle i {
        font-size: 0.9rem !important;
      }

      .mobile-family-card__header .mobile-family-card__top {
        padding-inline-end: 2rem;
      }

      .mobile-details-table--child td:last-child {
        text-align: center;
      }

      .mobile-details-table--child td:last-child .d-inline-flex,
      .mobile-details-table--child td:last-child .d-flex,
      .mobile-details-table--child td:last-child .users-list {
        justify-content: center;
      }

      .mobile-child-card .badge {
        white-space: normal;
        line-height: 1.35;
      }
    }

    @media (max-width: 575.98px) {
      .queue-page-actions {
        display: grid;
        gap: 0.6rem;
        grid-template-columns: 2.75rem minmax(0, 1fr);
        width: 100%;
      }

      .queue-page-actions--with-create > .btn:first-child {
        grid-row: 1 / span 2;
      }

      .queue-page-actions > .btn:not(:first-child),
      .queue-page-actions > a {
        justify-content: center;
        justify-self: stretch;
        width: 100%;
      }

      .transferred-toolbar {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr);
        align-items: start;
      }

      .transferred-toolbar .input-group,
      .transferred-toolbar .form-select {
        width: 100% !important;
        min-width: 0;
        font-size: 0.875rem;
      }

      .mobile-family-card__actions {
        align-self: start;
        justify-content: flex-end;
      }

      .mobile-family-card__top {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: start;
      }

      .mobile-details-table > :not(caption) > * > * {
        padding: 0.7rem 0.85rem;
      }
    }

    @media (max-width: 374.98px) {
      .mobile-details-table td:first-child,
      .mobile-details-table--child td:first-child {
        width: 72px;
      }

      .mobile-details-table > :not(caption) > * > * {
        padding: 0.6rem 0.55rem;
      }

      .mobile-details-table--child .badge {
        font-size: 0.66rem;
        padding: 0.28rem 0.42rem;
      }

      .mobile-details-table--child .child-quick-action,
      .mobile-details-table--child .btn.btn-sm.btn-icon {
        width: 1.9rem !important;
        height: 1.9rem !important;
        min-width: 1.9rem !important;
      }

      .mobile-details-table--child .btn.btn-sm.btn-icon i {
        font-size: 0.95rem !important;
      }

      .mobile-details-table--child td:last-child .d-inline-flex,
      .mobile-details-table--child td:last-child .d-flex {
        gap: 0.3rem !important;
      }

      .mobile-details-table--child tbody tr:last-child td:last-child .d-inline-flex {
        flex-wrap: nowrap !important;
      }
    }
  </style>

  <script>
    (() => {
      const initTransferredChildrenModals = () => {
        if (! window.Livewire || ! window.bootstrap) {
          return;
        }

        const bindModal = (id, closeDispatchEvent, openEvent, closeEvent) => {
          const setupElement = () => {
            const el = document.getElementById(id);

            if (! el || el.dataset.lifecycleModalBound === 'true') {
              return el;
            }

            el.dataset.lifecycleModalBound = 'true';
            el.addEventListener('hidden.bs.modal', () => {
              window.Livewire.dispatch(closeDispatchEvent);
            });

            return el;
          };

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
          'transferredChildrenLifecycleModal',
          'transferred-children-lifecycle-modal-hidden',
          'transferred-children-lifecycle-open',
          'transferred-children-lifecycle-close'
        );
      };

      if (window.Livewire) {
        initTransferredChildrenModals();
      }

      document.addEventListener('livewire:initialized', initTransferredChildrenModals);
    })();
  </script>
</div>
