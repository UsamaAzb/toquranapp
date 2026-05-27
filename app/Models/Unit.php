<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'title', 'teacher_subject_classes_id', 'academic_year_id', 'subject_id', 'class_id',
        'teacher_id', 'grade_level_id', 'unit_type_id', 'status', 'is_interdisciplinary',
    ];

    public $timestamps = false;

    /** علاقات */
    public function teacherSubjectClass()
    {
        return $this->belongsTo(TeacherSubjectClass::class, 'teacher_subject_classes_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }

    public function unitType()
    {
        return $this->belongsTo(UnitType::class, 'unit_type_id');
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class, 'unit_id');
    }

    public function sessionMaterials()
    {
        return $this->hasMany(SessionMaterial::class, 'unit_id');
    }

    public function sessionClassworks()
    {
        return $this->hasMany(SessionClasswork::class, 'unit_id');
    }

    /** سكوبات/هلبـرز */
    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public function scopeDraft($q)
    {
        return $q->where('status', 'draft');
    }

    public function isInterdisciplinary(): bool
    {
        return (bool) $this->is_interdisciplinary;
    }
}
