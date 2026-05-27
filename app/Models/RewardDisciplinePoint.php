<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardDisciplinePoint extends Model
{
    use HasFactory;

    protected $table = 'reward_discipline_points';

    protected $fillable = [
        'student_id',
        'title',
        'points',
        'status',
        'description',
        'created_at',
        'updated_at',
        'type',
        'discipline_icon_id',
        'discipline_icon_path',
        'sort',
        'teacher_desc',
        'selected',
    ];

    public $timestamps = false;

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function icon()
    {
        return $this->belongsTo(DisciplineIcon::class, 'discipline_icon_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
