<div class="row g-6">
  <div class="col-12">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
      <div>
        <h4 class="mb-1">Parent Information Editor</h4>
        <p class="text-muted mb-0">Update only the shared booking container fields. Child milestone, schedule, and consultation state stay untouched.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="{{ $this->cancelUrl() }}" class="btn btn-outline-secondary">
          Back
        </a>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="card h-100">
      <div class="card-body">
        <span class="badge bg-label-primary mb-3">Booking Snapshot</span>
        <h5 class="mb-1">{{ $booking->parent_name ?: 'Unnamed parent' }}</h5>
        <p class="text-muted small mb-3">
          Shared booking container for {{ $booking->children_count }} child row{{ $booking->children_count === 1 ? '' : 's' }}.
        </p>

        <div class="small d-flex flex-column gap-2">
          <span><strong>Booking Ref:</strong> {{ $booking->booking_reference ?: '-' }}</span>
          <span><strong>Email:</strong> {{ $booking->parent_email ?: '-' }}</span>
          <span><strong>Phone:</strong> {{ $booking->parent_phone ?: '-' }}</span>
          <span><strong>Child Rows:</strong> {{ $booking->children_count }}</span>
        </div>

        <div class="alert alert-info mt-4 mb-0">
          <div class="fw-semibold mb-1">Scope of this screen</div>
          <div class="small">
            This page edits only booking-level parent/contact information and shared notes. Child workflow, evaluation, consultation details, and transfer state do not change here.
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-8">
    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="mb-1">Edit Shared Parent Fields</h5>
        <p class="text-muted mb-0">Use this form when parent contact details change without affecting any child record.</p>
      </div>

      <div class="card-body">
        @if (session()->has('success'))
          <div class="alert alert-success">
            {{ session('success') }}
          </div>
        @endif

        <form wire:submit="save" class="row g-4">
          <div class="col-md-6">
            <label for="booking-parent-name" class="form-label">Parent Name</label>
            <input id="booking-parent-name" type="text" class="form-control @error('parentName') is-invalid @enderror" wire:model.blur="parentName">
            @error('parentName')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="booking-reference" class="form-label">Booking Reference</label>
            <input id="booking-reference" type="text" class="form-control @error('bookingReference') is-invalid @enderror" wire:model.blur="bookingReference">
            @error('bookingReference')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="booking-parent-email" class="form-label">Parent Email</label>
            <input id="booking-parent-email" type="email" class="form-control @error('parentEmail') is-invalid @enderror" wire:model.blur="parentEmail">
            @error('parentEmail')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-md-6">
            <label for="booking-parent-phone" class="form-label">Parent Phone</label>
            <input id="booking-parent-phone" type="text" class="form-control @error('parentPhone') is-invalid @enderror" wire:model.blur="parentPhone">
            @error('parentPhone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-12">
            <label for="booking-notes" class="form-label">Shared Booking Notes</label>
            <textarea id="booking-notes" class="form-control @error('notes') is-invalid @enderror" rows="5" wire:model.blur="notes"></textarea>
            @error('notes')
              <div class="invalid-feedback">{{ $message }}</div>
            @else
              <div class="form-text">These are booking-level notes only. Child-specific notes remain on each child record.</div>
            @enderror
          </div>

          <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
            <a href="{{ $this->cancelUrl() }}" class="btn btn-label-secondary">
              Cancel
            </a>
            <button type="submit" class="btn btn-primary">
              Save Parent
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
