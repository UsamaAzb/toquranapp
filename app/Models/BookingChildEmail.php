<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingChildEmail extends Model
{
    protected $table = 'booking_child_emails';

    protected $fillable = [
        'booking_child_id',
        'email_type',
        'status',
        'last_attempt_at',
        'last_sent_at',
        'last_error_message',
        'triggered_by',
    ];

    protected function casts(): array
    {
        return [
            'last_attempt_at' => 'datetime',
            'last_sent_at' => 'datetime',
        ];
    }

    public function bookingChild(): BelongsTo
    {
        return $this->belongsTo(BookingChild::class);
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
