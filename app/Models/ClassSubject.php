<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'grade_level_subject_id',
    ];

    /**
     * Get the class that owns this assignment.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the grade level subject that owns this assignment.
     */
    public function gradeLevelSubject()
    {
        return $this->belongsTo(GradeLevelSubject::class);
    }

    public function studentsSubjects()
    {
        return $this->hasMany(StudentsSubject::class, 'class_subject_id');
    }
}
