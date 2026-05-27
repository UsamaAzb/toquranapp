<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainDailySession extends Model
{
    use HasFactory;

    protected $table = 'main_daily_session';

    protected $fillable = ['title', 'subject_id'];

    public $timestamps = false;

    public function daily_sessions()
    {
        return $this->hasMany(DailySession::class, 'main_daily_session_id');
    }
}
