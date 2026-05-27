<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentClassesHistory extends Model
{
    use HasFactory;

    protected $table = 'student_classes_history';

    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'class_id',
        'from_date',
        'to_date',
        'status',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    /**
     * Get the student that owns this class history.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the class for this history record.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Scope to get current enrollments.
     */
    public function scopeCurrent($query)
    {
        return $query->where('status', 'current');
    }

    /**
     * Scope to get past enrollments.
     */
    public function scopePast($query)
    {
        return $query->where('status', 'past');
    }

    /**
     * Scope to get active enrollments (current and not archived).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['current', 'inactive']);
    }

    /**
     * Scope to get enrollments for a specific student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to get enrollments for a specific class.
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Check if the enrollment is currently active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['current', 'inactive']);
    }

    /**
     * Check if the enrollment is current.
     */
    public function isCurrent(): bool
    {
        return $this->status === 'current';
    }
}
