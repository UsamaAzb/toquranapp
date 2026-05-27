<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DifferentiatedTaskVersionAttachment extends Model
{
    protected $table = 'differentiated_task_version_attachments';

    protected $fillable = [
        'version_id',
        'attachment_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function version(): BelongsTo
    {
        return $this->belongsTo(DifferentiatedTaskVersion::class, 'version_id');
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(DifferentiatedTaskAttachment::class, 'attachment_id');
    }
}
