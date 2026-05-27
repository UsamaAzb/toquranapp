<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Camb_word extends Model
{
    use HasFactory;
              protected $table = 'camb_words';

    protected $fillable = [
      'word',
      'camb_cat_id',
      'camb_sound_id','unit','lesson'
    ];

   
}
