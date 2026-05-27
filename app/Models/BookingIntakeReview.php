<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingIntakeReview extends Model
{
    protected $table = 'booking_intake_review';

    protected $fillable = [
        'parent_name',
        'parent_email',
        'parent_phone',
        'child_name',
        'child_age',
        'child_grade',
        'school_system',
        'service_interests',
        'children_payload',
        'child_count',
        'open_submission_fingerprint',
        'notes',
        'detection_reason',
        'detection_detail',
        'matched_booking_id',
        'matched_child_id',
        'status',
        'resolved_by',
        'resolution_note',
        'resolved_at',
        'resulting_booking_id',
    ];

    protected function casts(): array
    {
        return [
            'service_interests' => 'array',
            'children_payload' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function reviewChildren(): HasMany
    {
        return $this->hasMany(BookingIntakeReviewChild::class, 'booking_intake_review_id')
            ->orderBy('child_index');
    }
}
