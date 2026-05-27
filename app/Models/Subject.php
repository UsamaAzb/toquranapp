<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'program_id',
        'code',
        'icon',
        'active',
        'row_status',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the program that owns this subject.
     */
    public function program()
    {
        return $this->belongsTo(SchoolProgram::class, 'program_id');
    }

    /**
     * Get the subject branches for this subject.
     */
    public function subjectBranches()
    {
        return $this->hasMany(SubjectBranch::class);
    }

    /**
     * Get the grade level subjects for this subject.
     */
    public function gradeLevelSubjects()
    {
        return $this->hasMany(GradeLevelSubject::class);
    }

    /**
     * Get the grade levels through the grade level subjects pivot.
     */
    public function gradeLevels()
    {
        return $this->belongsToMany(GradeLevel::class, 'grade_level_subjects')
            ->withPivot('academic_year_id', 'type', 'status', 'created_by_user_id')
            ->withTimestamps();
    }

    /**
     * Get the user subjects for this subject.
     */
    public function studentsSubjects()
    {
        return $this->hasManyThrough(
            StudentsSubject::class,
            GradeLevelSubject::class,
            'subject_id',
            'grade_level_subject_id',
            'id',
            'id'
        );
    }

    /**
     * Get the teachers assigned to this subject via teacher_subject_classes.
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subject_classes', 'subject_id', 'user_teacher_coteacher_id')
            ->withPivot([
                'id',
                'class_subject_id',
                'grade_id',
                'grade_name',
                'class_id',
                'class_name',
                'class_img',
                'teacher_name',
                'subject_name',
                'status',
                'assigned_at',
                'removed_at',
            ])
            ->withTimestamps();
    }

    /**
     * Scope to get active subjects.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get current subjects.
     */
    public function scopeCurrent($query)
    {
        return $query->where('row_status', 'current');
    }
}
