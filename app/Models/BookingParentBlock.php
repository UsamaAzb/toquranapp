<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingParentBlock extends Model
{
    protected $table = 'booking_parent_blocks';

    protected $fillable = [
        'normalized_email',
        'normalized_phone',
        'block_reason',
        'internal_notes',
        'blocked_by',
    ];
}
