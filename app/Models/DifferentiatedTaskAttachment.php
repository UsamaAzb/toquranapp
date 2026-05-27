<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DifferentiatedTaskAttachment extends Model
{
    protected $table = 'differentiated_task_attachments';

    protected $fillable = [
        'differentiated_task_id',
        'type',
        'title',
        'description',
        'path',
        'url',
        'file_size',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function task(): BelongsTo
    {
        return $this->belongsTo(DifferentiatedTask::class, 'differentiated_task_id');
    }

    public function versionSelections(): HasMany
    {
        return $this->hasMany(DifferentiatedTaskVersionAttachment::class, 'attachment_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    public function isLink(): bool
    {
        return $this->type === 'link';
    }

    public function isYoutube(): bool
    {
        return $this->type === 'youtube';
    }
}
