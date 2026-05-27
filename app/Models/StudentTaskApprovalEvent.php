<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTaskApprovalEvent extends Model
{
    public const TYPE_SUBMITTED_FOR_REVIEW = 'submitted_for_review';

    public const TYPE_APPROVED = 'approved';

    public const TYPE_COMPLETED_WITH_PIN = 'completed_with_pin';

    public const TYPE_COMPLETED_BY_PARENT = 'completed_by_parent';

    public const TYPE_TRUSTED_AUTO_APPROVED = 'trusted_auto_approved';

    public const TYPE_SKIPPED_STALE = 'skipped_stale';

    protected $table = 'student_task_approval_events';

    public $timestamps = false;

    protected $fillable = [
        'session_task_student_id',
        'session_task_id',
        'student_id',
        'event_type',
        'actor_user_id',
        'actor_role',
        'source',
        'points',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function sessionTaskStudent(): BelongsTo
    {
        return $this->belongsTo(SessionTaskStudent::class, 'session_task_student_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(SessionTask::class, 'session_task_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
