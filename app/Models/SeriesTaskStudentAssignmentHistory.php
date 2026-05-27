<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesTaskStudentAssignmentHistory extends Model
{
    protected $table = 'series_task_student_assignment_history';

    protected $fillable = [
        'student_id',
        'series_task_id',
        'event_type',
        'from_version_id',
        'from_version_display_name',
        'to_version_id',
        'to_version_display_name',
        'from_sequence_position',
        'to_sequence_position',
        'actor_user_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(SeriesTask::class, 'series_task_id');
    }

    public function fromVersion(): BelongsTo
    {
        return $this->belongsTo(SeriesTaskVersion::class, 'from_version_id');
    }

    public function toVersion(): BelongsTo
    {
        return $this->belongsTo(SeriesTaskVersion::class, 'to_version_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
