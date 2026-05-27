<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainDailySessionTemplate extends Model
{
    protected $table = 'main_daily_session_templates';

    protected $fillable = [
        'title',
        'subject_id',
        'created_by_user_id',
        'recurrence_kind',
        'recurrence_weekdays',
        'recurrence_day_of_month',
        'recurrence_interval',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'recurrence_interval' => 'integer',
            'recurrence_day_of_month' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(MainDailySessionVersion::class, 'main_daily_session_template_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function mainTasks(): HasMany
    {
        return $this->hasMany(MainDailySessionMainTask::class, 'main_daily_session_template_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(MainDailySessionSubscription::class, 'main_daily_session_template_id');
    }

    public function studentAssignments(): HasMany
    {
        return $this->hasMany(MainDailySessionStudentAssignment::class, 'main_daily_session_template_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeForSubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }
}
