<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyAttachmentFile extends Model
{
    use HasFactory;

    protected $table = 'daily_attachment_files';

    protected $fillable = [
        'title',
        'description',
        'type',
        'path',
        'file_size',
        'subject_id',
        'daily_session_task_id',

    ];

    public $timestamps = false;

    public function daily_session_task()
    {
        return $this->belongsTo(DailySessionTask::class, 'daily_session_task_id');
    }

    public function hasStoredFilePath(): bool
    {
        return $this->type === 'file' && filled($this->path);
    }
}
