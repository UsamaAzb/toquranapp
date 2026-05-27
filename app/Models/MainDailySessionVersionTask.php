<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MainDailySessionVersionTask extends Model
{
    protected $table = 'main_daily_session_version_tasks';

    protected $fillable = [
        'version_id',
        'main_task_id',
        'description_override',
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
        return $this->belongsTo(MainDailySessionVersion::class, 'version_id');
    }

    public function mainTask(): BelongsTo
    {
        return $this->belongsTo(MainDailySessionMainTask::class, 'main_task_id');
    }

    // -------------------------------------------------------------------------
    // Helpers - resolve description for snapshot generation
    // -------------------------------------------------------------------------

    /**
     * Returns the resolved description for snapshot: override > main task description.
     */
    public function resolveDescription(): ?string
    {
        if ($this->description_override !== null && trim($this->description_override) !== '') {
            return $this->description_override;
        }

        return $this->mainTask?->description;
    }

    /**
     * Passes the meaningful-content rule: must have description, attachment, or both.
     */
    public function passesMeaningfulContentRule(): bool
    {
        $hasDescription = filled($this->resolveDescription());
        $hasAttachment = ($this->mainTask?->attachments?->isNotEmpty()) === true;

        return $hasDescription || $hasAttachment;
    }
}
