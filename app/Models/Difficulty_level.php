<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Difficulty_level extends Model
{
    use HasFactory;
        protected $table = 'difficulty_levels';

    protected $fillable = [
      'name','active'
    ];

   
}
