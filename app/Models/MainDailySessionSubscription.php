<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MainDailySessionSubscription extends Model
{
    protected $table = 'main_daily_session_subscriptions';

    // The schema table has no created_at / updated_at columns.
    public $timestamps = false;

    protected $fillable = [
        'student_id',
        'main_daily_session_template_id',
        'is_active',
        'paused_at',
        'start_at',
        'end_at',
        'last_generated_date',
        'paused_through_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'paused_at' => 'datetime',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'last_generated_date' => 'date',
            'paused_through_date' => 'date',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MainDailySessionTemplate::class, 'main_daily_session_template_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', 1)->whereNull('paused_at');
    }

    public function scopePaused($query)
    {
        return $query->whereNotNull('paused_at');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTemplate($query, int $templateId)
    {
        return $query->where('main_daily_session_template_id', $templateId);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    public function isActive(): bool
    {
        return (bool) $this->is_active && $this->paused_at === null;
    }

    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }
}
