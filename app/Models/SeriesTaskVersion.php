<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeriesTaskVersion extends Model
{
    protected $table = 'series_task_versions';

    protected $fillable = [
        'series_task_id',
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

    public function task(): BelongsTo
    {
        return $this->belongsTo(SeriesTask::class, 'series_task_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SeriesTaskVersionItem::class, 'version_id')
            ->orderBy('sequence_position')
            ->orderBy('id');
    }

    public function activeItems(): HasMany
    {
        return $this->items()->active();
    }

    public function studentAssignments(): HasMany
    {
        return $this->hasMany(SeriesTaskStudentAssignment::class, 'version_id');
    }

    public function activeItemsCount(): int
    {
        if ($this->relationLoaded('items')) {
            return $this->items->where('is_active', true)->count();
        }

        return $this->items()->active()->count();
    }

    public function assignedStudentCount(): int
    {
        $today = today(config('app.timezone', 'Africa/Cairo'));
        $todayString = $today->toDateString();

        if ($this->relationLoaded('studentAssignments')) {
            return $this->studentAssignments
                ->filter(fn (SeriesTaskStudentAssignment $assignment): bool => $assignment->effective_from_date !== null
                    && $assignment->effective_from_date->toDateString() <= $todayString
                    && (
                        $assignment->effective_to_date === null
                        || $assignment->effective_to_date->toDateString() >= $todayString
                    ))
                ->count();
        }

        return $this->studentAssignments()->effectiveOn($today)->count();
    }
}
