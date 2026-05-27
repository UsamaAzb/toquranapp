<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services_type extends Model
{
    use HasFactory;
protected $table = "services_types";
    protected $fillable = [
        'title',
        'value',
        'info',
        'active',

    ];


}
