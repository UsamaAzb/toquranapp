<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySessionStudent extends Model
{
    use HasFactory;

    protected $table = 'daily_session_students';

    protected $fillable = ['student_id', 'daily_session_id', 'is_active', 'paused_at', 'start_at', 'end_at', 'last_generated_date'];

    public $timestamps = false;

    public function daily_session()
    {
        return $this->belongsTo(DailySession::class, 'daily_session_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
