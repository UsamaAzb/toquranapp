<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Models\Course_translation as Course_translation
class Listening_book extends Model
{
    use HasFactory;
    protected $table = 'listening_books';
    protected $fillable = [
      'title','author', 'active'
    ];

    public function chapters()
      {
          return $this->hasMany('App\Models\Listening_chapter','listen_book_id');
      }
     
}
