<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySession extends Model
{
    use HasFactory;

    protected $table = 'daily_sessions';

    protected $fillable = ['title', 'subject_id', 'main_daily_session_id'];

    public $timestamps = false;

    public function main_daily_session()
    {
        return $this->belongsTo(MainDailySession::class, 'main_daily_session_id');
    }

    public function daily_session_tasks()
    {
        return $this->hasMany(DailySessionTask::class, 'daily_session_id');
    }
}
