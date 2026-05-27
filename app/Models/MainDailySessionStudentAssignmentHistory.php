<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MainDailySessionStudentAssignmentHistory extends Model
{
    protected $table = 'main_daily_session_student_assignment_history';

    // Immutable append-only log — no updated_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'student_id',
        'main_daily_session_template_id',
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

    public function template(): BelongsTo
    {
        return $this->belongsTo(MainDailySessionTemplate::class, 'main_daily_session_template_id');
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

    public function scopeForTemplate($query, int $templateId)
    {
        return $query->where('main_daily_session_template_id', $templateId);
    }
}
