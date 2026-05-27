<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeriesTask extends Model
{
    protected $table = 'series_tasks';

    protected $fillable = [
        'subject_id',
        'created_by_user_id',
        'task_type_id',
        'title',
        'description',
        'library_collection_type',
        'library_collection_id',
        'vocabulary_allowed_games',
        'vocabulary_difficulty_policy',
        'recurrence_kind',
        'recurrence_weekdays',
        'recurrence_day_of_month',
        'recurrence_interval',
        'sequence_behavior',
        'release_policy',
        'default_points',
        'max_points',
        'sort_order',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'library_collection_id' => 'integer',
            'vocabulary_allowed_games' => 'array',
            'recurrence_day_of_month' => 'integer',
            'recurrence_interval' => 'integer',
            'default_points' => 'integer',
            'max_points' => 'integer',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SeriesTaskVersion::class, 'series_task_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function studentAssignments(): HasMany
    {
        return $this->hasMany(SeriesTaskStudentAssignment::class, 'series_task_id');
    }

    public function generationStates(): HasMany
    {
        return $this->hasMany(SeriesTaskStudentGenerationState::class, 'series_task_id');
    }

    public function assignmentHistory(): HasMany
    {
        return $this->hasMany(SeriesTaskStudentAssignmentHistory::class, 'series_task_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeForSubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

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

    public function activeVersionsWithItemsCount(): int
    {
        if ($this->relationLoaded('versions')) {
            return $this->versions
                ->filter(fn (SeriesTaskVersion $version): bool => $version->activeItemsCount() > 0)
                ->count();
        }

        return $this->versions()
            ->whereHas('items', fn ($query) => $query->active())
            ->count();
    }

    public function assignedStudentCount(): int
    {
        $today = today(config('app.timezone', 'Africa/Cairo'));
        $todayString = $today->toDateString();

        if ($this->relationLoaded('studentAssignments')) {
            return $this->studentAssignments
                ->filter(fn (SeriesTaskStudentAssignment $assignment): bool => $assignment->effective_from_date !== null
                    && $assignment->effective_from_date->toDateString() <= $todayString
                    && (
                        $assignment->effective_to_date === null
                        || $assignment->effective_to_date->toDateString() >= $todayString
                    ))
                ->unique('student_id')
                ->count();
        }

        return $this->studentAssignments()
            ->effectiveOn($today)
            ->distinct('student_id')
            ->count('student_id');
    }
}
