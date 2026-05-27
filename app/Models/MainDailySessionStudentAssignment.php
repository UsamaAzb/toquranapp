<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MainDailySessionStudentAssignment extends Model
{
    protected $table = 'main_daily_session_student_assignments';

    protected $fillable = [
        'student_id',
        'main_daily_session_template_id',
        'version_id',
        'effective_from_date',
        'effective_to_date',
        'assigned_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'effective_from_date' => 'date',
            'effective_to_date' => 'date',
        ];
    }

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

    public function version(): BelongsTo
    {
        return $this->belongsTo(MainDailySessionVersion::class, 'version_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
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

    public function scopeOpenEnded($query)
    {
        return $query->whereNull('effective_to_date');
    }

    /**
     * Intervals that cover the given date:
     * effective_from_date <= $date AND (effective_to_date IS NULL OR effective_to_date >= $date)
     */
    public function scopeEffectiveOn($query, Carbon $date)
    {
        $dateStr = $date->toDateString();

        return $query
            ->whereDate('effective_from_date', '<=', $dateStr)
            ->where(function ($q) use ($dateStr) {
                $q->whereNull('effective_to_date')
                    ->orWhereDate('effective_to_date', '>=', $dateStr);
            });
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isOpenEnded(): bool
    {
        return $this->effective_to_date === null;
    }
}
