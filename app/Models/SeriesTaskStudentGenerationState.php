<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesTaskStudentGenerationState extends Model
{
    protected $table = 'series_task_student_generation_states';

    protected $fillable = [
        'student_id',
        'series_task_id',
        'current_version_id',
        'is_active',
        'start_date',
        'end_date',
        'next_sequence_position',
        'last_delivered_sequence_position',
        'last_generated_date',
        'paused_through_date',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'current_version_id' => 'integer',
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'next_sequence_position' => 'integer',
            'last_delivered_sequence_position' => 'integer',
            'last_generated_date' => 'date',
            'paused_through_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(SeriesTask::class, 'series_task_id');
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(SeriesTaskVersion::class, 'current_version_id');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTask($query, int $taskId)
    {
        return $query->where('series_task_id', $taskId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}
