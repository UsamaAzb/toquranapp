<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AttachmentFile extends Model
{
    use HasFactory;

    protected $table = 'attachment_files';

    protected $fillable = [
        'title',
        'description',
        'type',
        'path',
        'file_size',
        'sort_order',
        'subject_id',
        'class_id',
        'teacher_subject_class_id',
        'session_task_id',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public $timestamps = false;

    public function task()
    {
        return $this->belongsTo(SessionTask::class, 'session_task_id');
    }

    public function scopeOrderedForDelivery($query)
    {
        if (Schema::hasColumn($this->getTable(), 'sort_order')) {
            return $query->orderBy('sort_order')->orderBy('id');
        }

        return $query->orderBy('id');
    }

    public function hasStoredFilePath(): bool
    {
        return $this->type === 'file' && filled($this->path);
    }

    // Note: Automated Task snapshot generation creates new AttachmentFile rows
    // per generated session_task (session_task_id points to the generated task).
    // No additional columns needed — the existing path/type/title fields carry
    // the copied snapshot. Protected attachment streaming routes remain unchanged.
}
