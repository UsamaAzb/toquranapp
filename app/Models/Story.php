<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;
    protected $fillable = [
      'title',
      'active','sort','description'
    ];
    public function chapters()
      {
          return $this->hasMany('App\Models\Story_chapter','story_id');
      }
}
