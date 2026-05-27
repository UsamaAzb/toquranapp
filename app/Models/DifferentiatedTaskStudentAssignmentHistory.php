<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DifferentiatedTaskStudentAssignmentHistory extends Model
{
    protected $table = 'differentiated_task_student_assignment_history';

    // Immutable append-only log - no updated_at.
    public const UPDATED_AT = null;

    protected $fillable = [
        'student_id',
        'differentiated_task_id',
        'event_type',
        'from_version_id',
        'from_version_display_name',
        'to_version_id',
        'to_version_display_name',
        'actor_user_id',
    ];

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

    public function fromVersion(): BelongsTo
    {
        return $this->belongsTo(DifferentiatedTaskVersion::class, 'from_version_id');
    }

    public function toVersion(): BelongsTo
    {
        return $this->belongsTo(DifferentiatedTaskVersion::class, 'to_version_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
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
}
