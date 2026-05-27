<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Models\Course_translation as Course_translation
class Listening_chapter extends Model
{
    use HasFactory;
    protected $table = 'listening_chapters';
    protected $fillable = [
      'title','listen_book_id', 'iframe_link','aduio','text'
    ];

    public function book()
      {
          return $this->belongsTo('App\Models\Listening_book','listen_book_id');
      }
     
}
