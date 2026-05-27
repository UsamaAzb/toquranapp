<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notice_note extends Model
{
    use HasFactory;
        protected $table = 'notice_note';

    protected $fillable = [
      'title',
      'slug',
      'parent_id',
      'video_link',
      'sort',
      'image',
    ];

    public function notice_note_desc()
      {
          return $this->hasMany('App\Models\Notice_note','parent_id','id')->orderby('sort');
      }
}
