<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySessionTask extends Model
{
    use HasFactory;

    protected $table = 'daily_session_tasks';

    protected $fillable = [
        'title',
        'description',
        'daily_session_id',
        'default_points',
        'max_points',
        'sort',
        'task_type_id',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public $timestamps = false;

    public function dailySession()
    {
        return $this->belongsTo(DailySession::class, 'daily_session_id');
    }

    public function attachments()
    {
        return $this->hasMany(DailyAttachmentFile::class, 'daily_session_task_id');
    }
}
