<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingChildAuditLog extends Model
{
    protected $table = 'booking_child_audit_log';

    public $timestamps = false;

    protected $fillable = [
        'booking_child_id',
        'field_name',
        'from_value',
        'to_value',
        'changed_by',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    public function bookingChild(): BelongsTo
    {
        return $this->belongsTo(BookingChild::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
