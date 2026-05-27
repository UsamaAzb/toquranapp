<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'active',
        'level_order',
        'program_id',
        'code',
    ];

    protected $casts = [
        'active' => 'boolean',
        'level_order' => 'integer',
    ];

    /**
     * Get the program that owns this grade level.
     */
    public function program()
    {
        return $this->belongsTo(SchoolProgram::class, 'program_id');
    }

    /**
     * Get the classes for this grade level.
     */
    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    /**
     * Get the grade level subjects for this grade level.
     */
    public function gradeLevelSubjects()
    {
        return $this->hasMany(GradeLevelSubject::class);
    }

    /**
     * Get the subjects through the grade level subjects pivot.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'grade_level_subjects')
            ->withPivot('academic_year_id', 'type', 'status', 'created_by_user_id')
            ->withTimestamps();
    }

    /**
     * Scope to get active grade levels.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to order by level order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('level_order');
    }

    public function studentClassesHistory()
    {
        return $this->hasManyThrough(
            StudentClassesHistory::class,
            ClassModel::class,
            'grade_level_id',
            'class_id',
            'id',
            'id'
        );
    }
}
