<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassworkType extends Model
{
    use HasFactory;

    protected $table = 'classwork_types';

    protected $fillable = ['title'];

    public $timestamps = false;

    public function sessionClassworks()
    {
        return $this->hasMany(SessionClasswork::class, 'classwork_type_id');
    }
}
