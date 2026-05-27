<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phonics_level extends Model
{
    use HasFactory;
    protected $fillable = [
      'name','level','order','active','hangman_url'
    ];

   
}
