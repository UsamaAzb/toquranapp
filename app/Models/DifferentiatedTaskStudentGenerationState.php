<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DifferentiatedTaskStudentGenerationState extends Model
{
    protected $table = 'differentiated_task_student_generation_states';

    protected $fillable = [
        'student_id',
        'differentiated_task_id',
        'is_active',
        'start_date',
        'end_date',
        'last_generated_date',
        'paused_through_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'last_generated_date' => 'date',
            'paused_through_date' => 'date',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(DifferentiatedTask::class, 'differentiated_task_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTask($query, int $taskId)
    {
        return $query->where('differentiated_task_id', $taskId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}
