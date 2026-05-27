<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MainDailySessionMainTaskAttachment extends Model
{
    protected $table = 'main_daily_session_main_task_attachments';

    protected $fillable = [
        'main_task_id',
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

    public function mainTask(): BelongsTo
    {
        return $this->belongsTo(MainDailySessionMainTask::class, 'main_task_id');
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
