<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student_punishment extends Model
{
    use HasFactory;

    protected $table = 'student_punishments';

    protected $fillable = [
        'student_id',
        'description',
        'punishment_agreement_id',        // references punishment_agreements.id
        'subject_id',
        'student_session_discipline_id',
        'teacher_subject_class',
        'created_by_id',
        'created_at',
    ];

    public $timestamps = false;

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(PunishmentAgreement::class, 'punishment_agreement_id');
    }

    public function type(): ?BelongsTo
    {
        return $this->agreement()->getRelated()->type();
    }
}
