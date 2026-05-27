<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingIntakeReviewChild extends Model
{
    protected $table = 'booking_intake_review_children';

    protected $fillable = [
        'booking_intake_review_id',
        'child_index',
        'child_name',
        'child_age',
        'child_grade',
        'school_system',
        'service_interests',
        'review_reason',
        'review_detail',
        'matched_booking_id',
        'matched_child_id',
        'resolution_status',
        'resolution_note',
    ];

    protected function casts(): array
    {
        return [
            'service_interests' => 'array',
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(BookingIntakeReview::class, 'booking_intake_review_id');
    }
}
