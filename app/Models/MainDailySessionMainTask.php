<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainDailySessionMainTask extends Model
{
    protected $table = 'main_daily_session_main_tasks';

    protected $fillable = [
        'main_daily_session_template_id',
        'title',
        'description',
        'task_type_id',
        'default_points',
        'max_points',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_points' => 'integer',
            'max_points' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function template(): BelongsTo
    {
        return $this->belongsTo(MainDailySessionTemplate::class, 'main_daily_session_template_id');
    }

    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MainDailySessionMainTaskAttachment::class, 'main_task_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function versionTasks(): HasMany
    {
        return $this->hasMany(MainDailySessionVersionTask::class, 'main_task_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForTemplate($query, int $templateId)
    {
        return $query->where('main_daily_session_template_id', $templateId);
    }
}
