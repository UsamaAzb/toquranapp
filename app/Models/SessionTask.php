<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionTask extends Model
{
    use HasFactory;

    protected $table = 'session_tasks';

    protected $fillable = [
        'title', 'class_session_id', 'taskable_id', 'task_type_id', 'due_date', 'assign_to_all', 'description',
        'default_points', 'max_points', 'marks', 'session_material_id', 'created_by_teacher_id', 'status', 'created_at', 'sort',
        // Automated Task snapshot fields (immutable once written)
        'version_display_name_snapshot', 'source_version_task_id_snapshot',
        'source_differentiated_task_id_snapshot',
        'source_differentiated_task_version_id_snapshot',
        'source_differentiated_task_assignment_id_snapshot',
        'source_series_task_id_snapshot',
        'source_series_task_version_id_snapshot',
        'source_series_task_version_item_id_snapshot',
        'source_series_task_assignment_id_snapshot',
        'source_series_library_type_snapshot',
        'source_series_library_id_snapshot',
    ];

    const CREATED_AT = 'created_at';

    const UPDATED_AT = null;

    /** علاقات */
    public function taskStudents()
    {
        return $this->hasMany(SessionTaskStudent::class, 'session_task_id');
    }

    public function classSession()
    {
        return $this->belongsTo(ClassSession::class, 'class_session_id');
    }

    public function sessionMaterial()
    {
        return $this->belongsTo(SessionMaterial::class, 'session_material_id');
    }

    public function creatorTeacher()
    {
        return $this->belongsTo(User::class, 'created_by_teacher_id');
    }

    public function taskType()
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    public function students() // pivot session_task_student
    {
        return $this->belongsToMany(User::class, 'session_task_student', 'session_task_id', 'student_user_id')
            ->withPivot(['student_points', 'submitted_at', 'assign_to_all'])
            ->using(SessionTaskStudent::class);
    }

    public function attachments()
    {
        return $this->hasMany(AttachmentFile::class, 'session_task_id')->orderedForDelivery();
    }

    /** Source version-task (traceability only - display must use snapshot fields). */
    public function sourceVersionTask()
    {
        return $this->belongsTo(MainDailySessionVersionTask::class, 'source_version_task_id_snapshot');
    }

    public function sourceDifferentiatedTask()
    {
        return $this->belongsTo(DifferentiatedTask::class, 'source_differentiated_task_id_snapshot');
    }

    public function sourceDifferentiatedTaskVersion()
    {
        return $this->belongsTo(DifferentiatedTaskVersion::class, 'source_differentiated_task_version_id_snapshot');
    }

    public function sourceDifferentiatedTaskAssignment()
    {
        return $this->belongsTo(
            DifferentiatedTaskStudentAssignment::class,
            'source_differentiated_task_assignment_id_snapshot'
        );
    }

    public function sourceSeriesTask()
    {
        return $this->belongsTo(SeriesTask::class, 'source_series_task_id_snapshot');
    }

    public function sourceSeriesTaskVersion()
    {
        return $this->belongsTo(SeriesTaskVersion::class, 'source_series_task_version_id_snapshot');
    }

    public function sourceSeriesTaskVersionItem()
    {
        return $this->belongsTo(SeriesTaskVersionItem::class, 'source_series_task_version_item_id_snapshot');
    }

    public function sourceSeriesTaskAssignment()
    {
        return $this->belongsTo(SeriesTaskStudentAssignment::class, 'source_series_task_assignment_id_snapshot');
    }

    /** ليس Morph: لا يوجد taskable_type في الجدول، لذا نعرّف Accessor حسب task_types.table_name */
    public function getTaskTargetModelAttribute(): ?Model
    {
        if (! $this->taskType?->table_name || ! $this->taskable_id) {
            return null;
        }

        // خريطة table_name => Model::class
        return match ($this->taskType->table_name) {
            'session_materials' => SessionMaterial::find($this->taskable_id),
            'session_classwork' => SessionClasswork::find($this->taskable_id),
            default => null,
        };
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

    public function scopeDueBetween($q, $from, $to)
    {
        return $q->whereBetween('due_date', [$from, $to]);
    }

    /** Automated Task snapshot rows (have a source_version_task_id_snapshot set). */
    public function scopeAutomatedTask($q)
    {
        return $q->whereNotNull('source_version_task_id_snapshot');
    }

    /** Differentiated Task snapshot rows. */
    public function scopeDifferentiatedTask($q)
    {
        return $q->whereNotNull('source_differentiated_task_id_snapshot');
    }

    /** Series Task snapshot rows. */
    public function scopeSeriesTask($q)
    {
        return $q->whereNotNull('source_series_task_id_snapshot');
    }

    public function isAutomatedTaskSnapshot(): bool
    {
        return $this->source_version_task_id_snapshot !== null
            || $this->source_differentiated_task_id_snapshot !== null
            || $this->source_series_task_id_snapshot !== null;
    }
}
