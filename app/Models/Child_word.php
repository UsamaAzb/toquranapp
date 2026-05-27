<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Models\Course_translation as Course_translation
class Child_word extends Model
{
    use HasFactory;
    protected $table = 'child_words';
    protected $fillable = [
      'word','child_category_id','image','sound','hangman_words_id'
    ];

    
}
