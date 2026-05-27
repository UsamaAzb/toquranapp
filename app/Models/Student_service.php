<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student_service extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'service_id',
        'service_name',
        'student_id',
        'parent_id',
        'subscription_type',
        'class_nums',
        'price_month',
        'started_at',

    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
