<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Camb_category extends Model
{
    use HasFactory;
          protected $table = 'camb_categories';

    protected $fillable = [
      'word','camb_cat_id','camb_sound_id','unit','lesson','active',
    ];

   
}
