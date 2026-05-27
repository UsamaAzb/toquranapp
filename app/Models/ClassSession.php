<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSession extends Model
{
    use HasFactory;

    protected $table = 'class_sessions';

    protected $fillable = [
        'teacher_subject_classes_id', 'class_id', 'subject_id', 'grade_id', 'teacher_id',
        'unit_id', 'date', 'session_start_time', 'session_end_time', 'class_subject_id',
        'title', 'daily_session_id', 'generated_for_date',
        // Automated Task identity (per-student generated rows)
        'student_id', 'main_daily_session_template_id', 'differentiated_task_id', 'series_task_id',
    ];

    public $timestamps = false;

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function automatedTaskTemplate(): BelongsTo
    {
        return $this->belongsTo(MainDailySessionTemplate::class, 'main_daily_session_template_id');
    }

    public function differentiatedTask(): BelongsTo
    {
        return $this->belongsTo(DifferentiatedTask::class, 'differentiated_task_id');
    }

    public function seriesTask(): BelongsTo
    {
        return $this->belongsTo(SeriesTask::class, 'series_task_id');
    }

    public function teacherSubjectClass()
    {
        return $this->belongsTo(TeacherSubjectClass::class, 'teacher_subject_classes_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'grade_id');
    }

    public function sessionMaterials()
    {
        return $this->hasOne(SessionMaterial::class, 'session_id');
    }

    public function tasks()
    {
        return $this->hasMany(SessionTask::class, 'class_session_id');
    }

    public function classworks()
    {
        return $this->hasMany(SessionClasswork::class, 'class_session_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeOnDate($q, $date)
    {
        return $q->where('date', $date);
    }

    /** Normal (non-Automated-Task) class sessions: student_id IS NULL. */
    public function scopeNormal($q)
    {
        return $q->whereNull('student_id');
    }

    /** Automated Task per-student generated sessions. */
    public function scopeAutomatedTask($q)
    {
        return $q->whereNotNull('main_daily_session_template_id');
    }

    /** Differentiated Task per-student generated sessions. */
    public function scopeDifferentiatedTask($q)
    {
        return $q->whereNotNull('differentiated_task_id');
    }

    /** Series Task per-student generated sessions. */
    public function scopeSeriesTask($q)
    {
        return $q->whereNotNull('series_task_id');
    }

    /** Automated Task sessions for a specific student. */
    public function scopeForStudent($q, int $studentId)
    {
        return $q->where('student_id', $studentId);
    }

    /** Learner delivery: shared normal sessions plus this student's automated rows. */
    public function scopeVisibleToLearner($q, int $studentId)
    {
        return $q->where(function ($query) use ($studentId): void {
            $query->whereNull('student_id')
                ->orWhere('student_id', $studentId);
        });
    }

    /** Automated Task sessions for a specific template + student on a date. */
    public function scopeGeneratedFor($q, int $templateId, int $studentId, string $date)
    {
        return $q->where('main_daily_session_template_id', $templateId)
            ->where('student_id', $studentId)
            ->where('generated_for_date', $date);
    }

    /** Differentiated Task sessions for a specific task + student on a date. */
    public function scopeGeneratedDifferentiatedTaskFor($q, int $taskId, int $studentId, string $date)
    {
        return $q->where('differentiated_task_id', $taskId)
            ->where('student_id', $studentId)
            ->where('generated_for_date', $date);
    }

    /** Series Task sessions for a specific task + student on a date. */
    public function scopeGeneratedSeriesTaskFor($q, int $taskId, int $studentId, string $date)
    {
        return $q->where('series_task_id', $taskId)
            ->where('student_id', $studentId)
            ->where('generated_for_date', $date);
    }
}
