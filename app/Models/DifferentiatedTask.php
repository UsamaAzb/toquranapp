<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DifferentiatedTask extends Model
{
    protected $table = 'differentiated_tasks';

    protected $fillable = [
        'subject_id',
        'created_by_user_id',
        'task_type_id',
        'title',
        'description',
        'recurrence_kind',
        'recurrence_weekdays',
        'recurrence_day_of_month',
        'recurrence_interval',
        'default_points',
        'max_points',
        'sort_order',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'recurrence_day_of_month' => 'integer',
            'recurrence_interval' => 'integer',
            'default_points' => 'integer',
            'max_points' => 'integer',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
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

    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DifferentiatedTaskVersion::class, 'differentiated_task_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DifferentiatedTaskAttachment::class, 'differentiated_task_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function studentAssignments(): HasMany
    {
        return $this->hasMany(DifferentiatedTaskStudentAssignment::class, 'differentiated_task_id');
    }

    public function generationStates(): HasMany
    {
        return $this->hasMany(DifferentiatedTaskStudentGenerationState::class, 'differentiated_task_id');
    }

    public function assignmentHistory(): HasMany
    {
        return $this->hasMany(DifferentiatedTaskStudentAssignmentHistory::class, 'differentiated_task_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

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

    public function validVersionsCount(): int
    {
        if ($this->relationLoaded('versions')) {
            return $this->versions
                ->filter(fn (DifferentiatedTaskVersion $version): bool => $version->hasMeaningfulContent())
                ->count();
        }

        return $this->versions()
            ->get()
            ->filter(fn (DifferentiatedTaskVersion $version): bool => $version->hasMeaningfulContent())
            ->count();
    }
}
