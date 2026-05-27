<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingParentIdentityResolution extends Model
{
    protected $table = 'booking_parent_identity_resolutions';

    public $timestamps = false;

    protected $fillable = [
        'stage',
        'outcome',
        'booking_intake_review_id',
        'booking_intake_review_child_id',
        'booking_id',
        'booking_child_id',
        'matched_booking_id',
        'target_parent_id',
        'conflicting_parent_id',
        'submitted_parent_email',
        'submitted_parent_phone',
        'previous_parent_email',
        'previous_parent_phone',
        'resolved_parent_email',
        'resolved_parent_phone',
        'contact_action',
        'child_identity_summary',
        'conflict_summary',
        'resolution_note',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(BookingIntakeReview::class, 'booking_intake_review_id');
    }

    public function reviewChild(): BelongsTo
    {
        return $this->belongsTo(BookingIntakeReviewChild::class, 'booking_intake_review_child_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function bookingChild(): BelongsTo
    {
        return $this->belongsTo(BookingChild::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function targetParent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'target_parent_id');
    }

    public function conflictingParent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'conflicting_parent_id');
    }
}
