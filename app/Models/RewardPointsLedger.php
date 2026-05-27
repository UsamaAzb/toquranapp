<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardPointsLedger extends Model
{
    use HasFactory;

    protected $table = 'reward_points_ledger';

    protected $fillable = [
        'student_id',
        'source_type',
        'source_id',
        'points_delta',
        'granted_by',
        'granted_at',
        'academic_year_id',
        'subject_id',
        'comment',
        'sign',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the student that owns the points entry.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who awarded the points.
     */
    public function granted_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Get the academic year that owns the points entry.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the subject that owns the points entry.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Scope to filter by student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to filter by academic year.
     */
    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope to filter by subject.
     */
    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope to filter by source type.
     */
    public function scopeBySourceType($query, $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    /**
     * Scope to filter positive points.
     */
    public function scopePositive($query)
    {
        return $query->where('points', '>', 0);
    }

    /**
     * Scope to filter negative points.
     */
    public function scopeNegative($query)
    {
        return $query->where('points', '<', 0);
    }

    /**
     * Scope to filter PIN verified entries.
     */
    public function scopePinVerified($query)
    {
        return $query->whereNotNull('pin_verified_at');
    }

    /**
     * Scope to order by creation date.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
