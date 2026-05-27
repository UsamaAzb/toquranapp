<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phonics_word extends Model
{
    use HasFactory;
    protected $fillable = [
      'word',
      'category_id',
      'camb_sound_id'
    ];

   
}
