<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grammar extends Model
{
    use HasFactory;
        protected $table = 'grammar';

    protected $fillable = [
      'title',
      'slug',
      'parent_id',
      'video_link',
      'sort',
      'image',
    ];

    public function grammar_desc()
      {
          return $this->hasMany('App\Models\Grammar','parent_id','id')->orderby('sort');
      }
}
