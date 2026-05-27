<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PunishmentAgreement extends Model
{
    use HasFactory;

    protected $table = 'punishment_agreements';

    protected $fillable = [
        'student_id',
        'title',
        'punishment_type_id',
        'status',
    ];

    public $timestamps = false;

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PunishmentType::class, 'punishment_type_id');
    }

    // If you keep student_punishments.punishment_id pointing here:
    public function studentPunishments(): HasMany
    {
        return $this->hasMany(StudentPunishment::class, 'punishment_id');
    }
}
