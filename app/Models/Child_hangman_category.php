<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Models\Course_translation as Course_translation
class Child_hangman_category extends Model
{
    use HasFactory;
    protected $table = 'child_hangman_category';
    protected $fillable = [
      'name','order','active','student_id','password'
    ];

    
}
