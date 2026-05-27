<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peer_coach extends Model
{
    use HasFactory;
        protected $table = 'peer_coach';

    protected $fillable = [
      'title',
      'slug',
      'parent_id',
      'video_link',
      'sort',
      'image',
    ];

    public function peer_desc()
      {
          return $this->hasMany('App\Models\Peer_coach','parent_id','id')->orderby('sort');
      }
}
