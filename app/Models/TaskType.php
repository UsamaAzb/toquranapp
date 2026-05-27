<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    use HasFactory;

    protected $table = 'task_types';

    protected $fillable = ['title', 'table_name', 'default_points', 'max_points'];

    public $timestamps = false;

    public function sessionTasks()
    {
        return $this->hasMany(SessionTask::class, 'task_type_id');
    }
}
