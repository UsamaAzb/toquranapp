<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingChild extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'child_name',
        'child_age',
        'child_grade',
        'school_system',
        'service_interests',
        'consultation_status',
        'workflow_status',
        'meeting_disposition',
        'meeting_disposition_reason',
        'evaluation_status',
        'evaluation_outcome',
        'consultation_type',
        'meeting_link',
        'meeting_address',
        'transfer_status',
        'followup_date',
        'current_school',
        'student_id',
        'notes',
        'scheduled_date',
        'scheduled_time',
        'sort_order',
        'updated_by',
    ];

    protected $casts = [
        'service_interests' => 'array',
        'followup_date' => 'datetime',
        'scheduled_date' => 'date',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(BookingChildEmail::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(BookingChildAuditLog::class);
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeByConsultationStatus($query, string $status)
    {
        return $query->where('consultation_status', $status);
    }

    public function scopeByEvaluationStatus($query, string $status)
    {
        return $query->where('evaluation_status', $status);
    }

    public function scopeByTransferStatus($query, string $status)
    {
        return $query->where('transfer_status', $status);
    }

    public function scopePendingFollowUp($query)
    {
        return $query->where(function ($q) {
            $q->where('consultation_status', 'followup')
                ->orWhere('evaluation_status', 'PL');
        });
    }

    public function scopeTransferred($query)
    {
        return $query->where('transfer_status', 'transferred');
    }

    public function scopeFit($query)
    {
        return $query->where('evaluation_status', 'fit');
    }

    public function scopeUnfit($query)
    {
        return $query->where('evaluation_status', 'unfit');
    }

    public function isFitReadyForTransfer(): bool
    {
        return $this->evaluation_outcome === 'fit'
            && $this->transfer_status !== 'transferred'
            && in_array($this->meeting_disposition, ['completed', 'cancelled', 'no_meeting_required'], true);
    }

    public function displayServiceInterests(): array
    {
        return collect($this->service_interests ?? [])
            ->map(fn ($service) => Booking::displayServiceInterest($service))
            ->all();
    }
}
