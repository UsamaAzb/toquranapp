<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionMaterial extends Model
{
    use HasFactory;

    protected $table = 'session_materials';

    protected $fillable = [
        'teacher_subject_classes_id', 'subject_id', 'grade_id', 'teacher_id', 'session_id',
        'unit_id', 'status', 'assign_to_all', 'task_desc', 'class_work_desc',
    ];

    public $timestamps = false;

    /** علاقات */
    public function classSession()
    {
        return $this->belongsTo(ClassSession::class, 'session_id');
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

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /** سكوبات */
    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public function scopeDraft($q)
    {
        return $q->where('status', 'draft');
    }

    // Note: uq_session_materials_session DB unique key (session_id) backs the
    // ClassSession::sessionMaterials() hasOne relationship with idempotency at
    // the DB level. This model requires no additional changes for Automated Tasks
    // because generated rows reuse the same one-per-session contract.
}
