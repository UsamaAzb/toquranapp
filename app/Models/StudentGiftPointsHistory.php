<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGiftPointsHistory extends Model
{
    use HasFactory;

    protected $table = 'student_gift_points_history';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'points',
        'date',
        'status',
        'sign',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the student that owns this class history.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
