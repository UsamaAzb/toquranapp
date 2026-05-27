<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SessionTaskStudent extends Pivot
{
    public const STATUS_ASSIGNED = 'assigned';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_LEGACY_PENDING = 'pending';

    public const SOURCE_STUDENT_REVIEW = 'student_review';

    public const SOURCE_PARENT_APPROVAL = 'parent_approval';

    public const SOURCE_PARENT_DIRECT_COMPLETION = 'parent_direct_completion';

    public const SOURCE_TEACHER_APPROVAL = 'teacher_approval';

    public const SOURCE_STUDENT_PIN = 'student_pin';

    public const SOURCE_TRUSTED_CHILD_AUTO = 'trusted_child_auto';

    protected $table = 'session_task_student';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'session_task_id',
        'student_id',
        'student_points',
        'submitted_at',
        'review_submitted_at',
        'review_submitted_by_id',
        'review_submission_source',
        'approval_source',
        'approved_by_id',
        'approved_at',
        'trusted_auto_approval_snapshot',
        'trusted_auto_approval_due_at',
        'trusted_auto_approval_granted_by_id',
        'assign_to_all',
        'status',
        'flag',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'review_submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'trusted_auto_approval_snapshot' => 'boolean',
        'trusted_auto_approval_due_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(SessionTask::class, 'session_task_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function reviewSubmitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'review_submitted_by_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function trustedAutoApprovalGranter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trusted_auto_approval_granted_by_id');
    }

    public function approvalEvents(): HasMany
    {
        return $this->hasMany(StudentTaskApprovalEvent::class, 'session_task_student_id');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeInReview(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_IN_REVIEW, self::STATUS_LEGACY_PENDING]);
    }

    public function scopeAssignedLike(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereNull('status')
                ->orWhere('status', '')
                ->orWhere('status', self::STATUS_ASSIGNED);
        });
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isInReviewLike(): bool
    {
        return in_array($this->status, [self::STATUS_IN_REVIEW, self::STATUS_LEGACY_PENDING], true);
    }

    public function displayStatus(): string
    {
        return $this->status === self::STATUS_LEGACY_PENDING
            ? self::STATUS_IN_REVIEW
            : (string) $this->status;
    }
}
