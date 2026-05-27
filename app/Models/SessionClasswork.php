<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionClasswork extends Model
{
    use HasFactory;

    protected $table = 'session_classwork';

    protected $fillable = [
        'title', 'description', 'classwork_type_id', 'table_name', 'library_id', 'class_session_id',
        'datetime', 'subject_id', 'class_id', 'teacher_subject_class_id', 'unit_id', 'session_id_materials',
        'assign_to_all', 'created_by_teacher_id', 'status', 'created_at',
    ];

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null; // لا يوجد updated_at

    /** علاقات */
    public function classSession()
    {
        return $this->belongsTo(ClassSession::class, 'class_session_id');
    }

    public function classworkType()
    {
        return $this->belongsTo(ClassworkType::class, 'classwork_type_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function teacherSubjectClass()
    {
        return $this->belongsTo(TeacherSubjectClass::class, 'teacher_subject_class_id');
    }

    public function creatorTeacher()
    {
        return $this->belongsTo(User::class, 'created_by_teacher_id');
    }

    public function students() // through pivot session_classwork_students
    {
        return $this->belongsToMany(Student::class, 'session_classwork_students', 'classwork_id', 'student_id')
            ->withPivot('status')
            ->using(SessionClassworkStudent::class);
    }

    /** Helpers */
    public function publish(): static
    {
        $this->status = 'published';
        $this->save();

        return $this;
    }

    public function draft(): static
    {
        $this->status = 'draft';
        $this->save();

        return $this;
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }
}
