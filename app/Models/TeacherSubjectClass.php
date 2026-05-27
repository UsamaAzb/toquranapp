<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSubjectClass extends Model
{
    use HasFactory;

    // اسم الجدول (لو الاسم مش نفس صيغة Laravel الافتراضية)
    protected $table = 'teacher_subject_classes';

    // الأعمدة اللي مسموح بالـ Mass Assignment
    protected $fillable = [
        'user_teacher_coteacher_id',
        'teacher_name',
        'class_subject_id',
        'grade_id',
        'grade_name',
        'class_id',
        'class_name',
        'class_img',
        'subject_id',
        'subject_name',
        'status',
        'assigned_at',
        'removed_at',
    ];

    // كاست التواريخ
    protected $dates = [
        'assigned_at',
        'removed_at',
        'created_at',
        'updated_at',
    ];

    /**
     * العلاقات (Relationships)
     */

    // علاقة مع المدرس أو المساعد (ممكن يربط بـ users table)
    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_teacher_coteacher_id');
    }

    // علاقة مع الصف
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    // علاقة مع المرحلة (grade)
    public function grade()
    {
        return $this->belongsTo(GradeLevel::class, 'grade_id');
    }

    // علاقة مع المادة
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class, 'class_subject_id');
    }

    public function units()
    {
        return $this->hasMany(Unit::class, 'teacher_subject_classes_id');
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class, 'teacher_subject_classes_id');
    }

    public function sessionMaterials()
    {
        return $this->hasMany(SessionMaterial::class, 'teacher_subject_classes_id');
    }

    /** سكوبات */
    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    public function scopeCurrent($q)
    {
        return $q->where('status', 'current');
    }

    public function scopeAvailableForTeacher($q)
    {
        return $q->whereIn('status', ['active', 'current']);
    }

    public function scopeWithActiveStudentSubject($q, ?int $studentId = null)
    {
        return $q->whereHas('classSubject.studentsSubjects', function ($query) use ($studentId) {
            $query->where('status', 'active');

            if ($studentId) {
                $query->where('student_id', $studentId);
            }
        });
    }

    public function scopeArchived($q)
    {
        return $q->where('status', 'archived');
    }
}
