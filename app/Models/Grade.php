<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $table = 'grade_levels';

    protected $fillable = ['title', 'code', 'level_order', 'active', 'school_program_id'];

    /**
     * Get the classes for this grade.
     */
    public function classes()
    {
        return $this->hasMany(Classe_group::class, 'grade_level_id');
    }

    /**
     * Get the subjects associated with this grade.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subjects_grades', 'grade_id', 'subject_id');
    }
}
