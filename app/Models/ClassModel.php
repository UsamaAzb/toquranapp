<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'title',
        'grade_level_id',
        'grade_name',
        'class_img',
        'status',
        'type',
        'academic_year_id',
    ];

    /**
     * Get the grade level that owns this class.
     */
    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    /**
     * Get the academic year that owns this class.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the class students for this class.
     */
    public function classStudents()
    {
        return $this->hasMany(StudentClassesHistory::class, 'class_id');
    }

    /**
     * Get the currently enrolled students in this class.
     * Uses student_classes_history with wherePivot status=current.
     * For full history, use studentClassesHistory() instead.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_classes_history', 'class_id', 'student_id')
            ->withPivot('from_date', 'to_date', 'status')
            ->wherePivot('status', 'current');
    }

    /**
     * Get the class subjects for this class.
     */
    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class, 'class_id');
    }

    public function studentClassesHistory()
    {
        return $this->hasMany(StudentClassesHistory::class, 'class_id');
    }

    /**
     * Get the subjects assigned to this class.
     */
    public function subjects()
    {
        return $this->belongsToMany(GradeLevelSubject::class, 'class_subjects', 'class_id', 'grade_level_subject_id')
            ->withTimestamps();
    }

    public function teacherSubjectClasses()
    {
        return $this->hasMany(TeacherSubjectClass::class, 'class_id');
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class, 'class_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'class_id');
    }

    /**
     * Scope to get active classes.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get main classes.
     */
    public function scopeMain($query)
    {
        return $query->where('type', 'main');
    }

    /**
     * Scope to get secondary classes.
     */
    public function scopeSecondary($query)
    {
        return $query->where('type', 'secondary');
    }

    /**
     * Scope to filter by academic year.
     */
    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }
}
