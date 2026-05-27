<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainDailySessionVersion extends Model
{
    protected $table = 'main_daily_session_versions';

    protected $fillable = [
        'main_daily_session_template_id',
        'display_name',
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(MainDailySessionTemplate::class, 'main_daily_session_template_id');
    }

    public function versionTasks(): HasMany
    {
        return $this->hasMany(MainDailySessionVersionTask::class, 'version_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function studentAssignments(): HasMany
    {
        return $this->hasMany(MainDailySessionStudentAssignment::class, 'version_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForTemplate($query, int $templateId)
    {
        return $query->where('main_daily_session_template_id', $templateId);
    }
}
