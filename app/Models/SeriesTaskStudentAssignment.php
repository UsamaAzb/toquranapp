<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesTaskStudentAssignment extends Model
{
    protected $table = 'series_task_student_assignments';

    protected $fillable = [
        'student_id',
        'series_task_id',
        'version_id',
        'start_sequence_position',
        'effective_from_date',
        'effective_to_date',
        'assigned_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'start_sequence_position' => 'integer',
            'effective_from_date' => 'date',
            'effective_to_date' => 'date',
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

    public function version(): BelongsTo
    {
        return $this->belongsTo(SeriesTaskVersion::class, 'version_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTask($query, int $taskId)
    {
        return $query->where('series_task_id', $taskId);
    }

    public function scopeOpenEnded($query)
    {
        return $query->whereNull('effective_to_date');
    }

    public function scopeEffectiveOn($query, Carbon $date)
    {
        $dateStr = $date->toDateString();

        return $query
            ->whereNotNull('effective_from_date')
            ->whereDate('effective_from_date', '<=', $dateStr)
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('effective_to_date')
                    ->orWhereDate('effective_to_date', '>=', $dateStr);
            });
    }

    public function isOpenEnded(): bool
    {
        return $this->effective_to_date === null;
    }
}
