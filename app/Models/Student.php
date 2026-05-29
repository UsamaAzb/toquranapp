<?php

namespace App\Models;

use App\Enums\FamilyLifecycleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// replace with your actual class model name

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'parent_id',
        'student_email',
        'student_phone',
        'father_name',
        'father_email',
        'father_phone',
        'father_occupation',
        'father_national_id',
        'mother_name',
        'mother_email',
        'mother_phone',
        'mother_occupation',
        'mother_national_id',
        'student_image',
        'gender',
        'birth_date',
        'grade_level_id',
        'program_id',
        'school_system',
        'is_published',
        'school_fees',
        'current_class_id',
        'religion',
        'user_id',
        'enrollment_date',
        'address',
        'birth_certificate',
        'nationality',
        'health_condition',
        'relatives_school',
        'user_name',
        'password',
        'completion_date',
        'avatar_path',
        'status',
        'account_status',
        'emergency_contact_name',
        'emergency_contact_phone',
        'service_type_id',
        'age',
        'grade_name',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'enrollment_date' => 'date',
        'completion_date' => 'date',
        'is_published' => 'boolean',
        'school_fees' => 'boolean',
        'account_status' => 'string',
    ];

    /**
     * Get the user associated with the student.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services_type(): BelongsTo
    {
        return $this->belongsTo(Services_type::class, 'service_type_id');
    }

    /**
     * Get the parent associated with the student.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    /**
     * Get the grade level associated with the student.
     */
    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    /**
     * Get the current class associated with the student.
     */
    public function currentClass(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'current_class_id');
    }

    /**
     * Get the school program associated with the student.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(SchoolProgram::class, 'program_id');
    }

    /**
     * Get the student's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /**
     * Get the student's display name (full name or email).
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?: $this->student_email ?: 'Student #'.$this->id;
    }

    public function isLifecycleManaged(): bool
    {
        return filled($this->parent_id)
            && in_array(
                (string) $this->account_status,
                array_map(static fn (FamilyLifecycleStatus $status): string => $status->value, FamilyLifecycleStatus::cases()),
                true
            );
    }

    public function lifecycleStatusLabel(): string
    {
        if (! $this->isLifecycleManaged()) {
            return ucfirst((string) $this->status);
        }

        return match ((string) $this->account_status) {
            FamilyLifecycleStatus::PendingActivation->value => 'Pending Activation',
            FamilyLifecycleStatus::Active->value => 'Active',
            FamilyLifecycleStatus::Suspended->value => 'Suspended',
            FamilyLifecycleStatus::Archived->value => 'Archived',
            default => ucfirst((string) $this->status),
        };
    }

    public function lifecycleStatusTone(): ?string
    {
        if (! $this->isLifecycleManaged()) {
            return null;
        }

        return match ((string) $this->account_status) {
            FamilyLifecycleStatus::Active->value => 'success',
            FamilyLifecycleStatus::PendingActivation->value => 'warning',
            FamilyLifecycleStatus::Suspended->value => 'danger',
            FamilyLifecycleStatus::Archived->value => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Scope to filter active students.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVisibleToTeacher($query)
    {
        return $query->where(function ($statusQuery): void {
            $statusQuery->whereNull('account_status')
                ->orWhere('account_status', '')
                ->orWhere('account_status', 'active');
        });
    }

    /**
     * Scope to filter by grade level.
     */
    public function scopeByGradeLevel($query, $gradeLevelId)
    {
        return $query->where('grade_level_id', $gradeLevelId);
    }

    /**
     * Scope to filter by program.
     */
    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    /**
     * All class history records for the student.
     */

    /**
     * Current active class record for the student.
     */
    public function currentClassHistory()
    {
        return $this->hasOne(StudentClassesHistory::class, 'student_id')
            ->where('status', 'current');
    }

    /**
     * The actual Class model for the current active class.
     */
    public function mainClass()
    {
        return $this->belongsToMany(
            ClassModel::class,
            'student_classes_history', // pivot table
            'student_id',              // FK for student
            'class_id'                  // FK for class
        )
            ->wherePivot('status', 'current'); // current active class
    }

    public function subjects()
    {
        // return Subject::query()
        //     ->select('subjects.*')
        //     ->join('grade_level_subjects', 'grade_level_subjects.subject_id', '=', 'subjects.id')
        //     ->join('students_subjects', 'students_subjects.grade_level_subject_id', '=', 'grade_level_subjects.id')
        //     ->whereColumn('students_subjects.student_id', 'students.id');

        return Subject::query()
            ->select('subjects.*')
            ->join('grade_level_subjects', 'grade_level_subjects.subject_id', '=', 'subjects.id')
            ->join('students_subjects', 'students_subjects.grade_level_subject_id', '=', 'grade_level_subjects.id')
            ->where('students_subjects.student_id', $this->id)
            ->distinct();
    }

    public function standardSubjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'students_subjects',         // Pivot table
            'student_id',                // Foreign key on pivot for student
            'grade_level_subject_id'     // Foreign key on pivot for grade_level_subjects
        )
            ->join('grade_level_subjects', 'grade_level_subjects.id', '=', 'students_subjects.grade_level_subject_id')
            ->where('grade_level_subjects.type', 'standard')
            ->select('subjects.*'); // Select only subject columns
    }

    public function optionalSubjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'students_subjects',
            'student_id',
            'grade_level_subject_id'
        )
            ->join('grade_level_subjects', 'grade_level_subjects.id', '=', 'students_subjects.grade_level_subject_id')
            ->where('grade_level_subjects.type', 'optional')
            ->select('subjects.*');
    }

    public function classesHistory()
    {
        return $this->belongsToMany(
            ClassModel::class,
            'student_classes_history', // pivot table
            'student_id',              // FK on pivot for student
            'class_id'                 // FK on pivot for class
        )->withPivot(['status', 'from_date', 'to_date']);
    }

    public function punishmentAgreements()
    {
        return $this->hasMany(PunishmentAgreement::class);
    }

    public function studentPunishments()
    {
        return $this->hasMany(StudentPunishment::class);
    }
}
