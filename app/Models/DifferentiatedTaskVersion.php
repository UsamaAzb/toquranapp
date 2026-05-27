<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DifferentiatedTaskVersion extends Model
{
    protected $table = 'differentiated_task_versions';

    protected $fillable = [
        'differentiated_task_id',
        'display_name',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function task(): BelongsTo
    {
        return $this->belongsTo(DifferentiatedTask::class, 'differentiated_task_id');
    }

    public function attachmentSelections(): HasMany
    {
        return $this->hasMany(DifferentiatedTaskVersionAttachment::class, 'version_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function selectedAttachments(): BelongsToMany
    {
        return $this->belongsToMany(
            DifferentiatedTaskAttachment::class,
            'differentiated_task_version_attachments',
            'version_id',
            'attachment_id'
        )
            ->withPivot(['sort_order'])
            ->withTimestamps()
            ->orderBy('differentiated_task_version_attachments.sort_order')
            ->orderBy('differentiated_task_attachments.id');
    }

    public function studentAssignments(): HasMany
    {
        return $this->hasMany(DifferentiatedTaskStudentAssignment::class, 'version_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function hasMeaningfulContent(): bool
    {
        $hasDescription = filled($this->description);
        $hasAttachment = $this->relationLoaded('selectedAttachments')
            ? $this->selectedAttachments->isNotEmpty()
            : $this->selectedAttachments()->exists();

        return $hasDescription || $hasAttachment;
    }

    public function assignedStudentCount(): int
    {
        if ($this->relationLoaded('studentAssignments')) {
            return $this->studentAssignments
                ->whereNull('effective_to_date')
                ->count();
        }

        return $this->studentAssignments()->openEnded()->count();
    }
}
