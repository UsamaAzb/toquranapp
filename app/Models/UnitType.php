<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitType extends Model
{
    protected $table = 'units_types';

    protected $fillable = ['type', 'status', 'table_name', 'program_id'];
}
