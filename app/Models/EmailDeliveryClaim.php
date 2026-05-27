<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailDeliveryClaim extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'claim_key',
        'parent_id',
        'subject_type',
        'subject_id',
        'event_type',
        'status',
        'metadata',
        'claimed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'claimed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
