<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTaskApprovalSetting extends Model
{
    protected $table = 'student_task_approval_settings';

    protected $fillable = [
        'student_id',
        'trusted_auto_approval_enabled',
        'updated_by_user_id',
    ];

    protected $casts = [
        'trusted_auto_approval_enabled' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public static function trustedEnabledFor(int $studentId): ?self
    {
        return self::query()
            ->where('student_id', $studentId)
            ->where('trusted_auto_approval_enabled', true)
            ->first();
    }
}
