<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student_Session_Discipline extends Model
{
    use HasFactory;

    protected $table = 'student_session_discipline';

    protected $fillable = [
        'discipline_icon_id',
        'discipline_icon_path',
        'class_session_id',
        'student_id',
        'points',
        'description',
        'created_at',
        'updated_at',
        'teacher_subject_classes_id',
        'student_reward_discipline_id',
        'type',
        'title',

    ];

    // public $timestamps = false;

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
