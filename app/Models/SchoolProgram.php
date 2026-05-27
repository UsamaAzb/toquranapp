<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolProgram extends Model
{
    use HasFactory;

    protected $table = 'school_program';

    protected $fillable = [
        'title', 'code',
    ];

    /**
     * Get the grade levels for this program.
     */
    public function gradeLevels()
    {
        return $this->hasMany(GradeLevel::class, 'program_id');
    }

    /**
     * Get the subjects for this program.
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'program_id');
    }

    public function classes()
    {
        return $this->hasManyThrough(
            ClassModel::class,
            GradeLevel::class,
            'program_id',
            'grade_level_id',
            'id',
            'id'
        );
    }

    /**
     * Get the title of the grade level with the smallest ID.
     */
    public function getMinGradeLevelTitleAttribute()
    {
        return $this->gradeLevels()
            ->orderBy('id', 'asc')
            ->value('title');
    }

    /**
     * Get the title of the grade level with the largest ID.
     */
    public function getMaxGradeLevelTitleAttribute()
    {
        return $this->gradeLevels()
            ->orderBy('id', 'desc')
            ->value('title');
    }
}
