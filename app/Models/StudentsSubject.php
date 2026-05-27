<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentsSubject extends Model
{
    use HasFactory;

    protected $table = 'students_subjects';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'grade_level_subject_id',
        'academic_year_id',
        'enrolled_at',
        'status',
        'class_subject_id',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
    ];

    /**
     * Get the student that owns this enrollment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the grade level subject for this enrollment.
     */
    public function gradeLevelSubject(): BelongsTo
    {
        return $this->belongsTo(GradeLevelSubject::class, 'grade_level_subject_id');
    }

    public function classSubject(): BelongsTo
    {
        return $this->belongsTo(ClassSubject::class, 'class_subject_id');
    }

    /**
     * Get the academic year for this enrollment.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Scope to get enrollments for current academic year.
     */
    public function scopeCurrentYear($query)
    {
        return $query->where('academic_year_id', AcademicYear::currentId());
    }

    /**
     * Scope to get enrollments for a specific student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
