<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SessionClassworkStudent extends Pivot
{
    protected $table = 'session_classwork_students';

    public $timestamps = false;

    protected $fillable = ['student_id', 'classwork_id', 'status'];
}
