<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sat extends Model
{
    use HasFactory;
        protected $table = 'sat';

    protected $fillable = [
      'title',
      'slug',
      'parent_id',
      'video_link',
      'sort',
      'image',
    ];

    public function sat_desc()
      {
          return $this->hasMany('App\Models\Sat','parent_id','id');
      }
}
