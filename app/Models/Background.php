<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Background extends Model
{
    use HasFactory;
        protected $table = 'background';

    protected $fillable = [
      'title',
      'slug',
      'parent_id',
      'pdf_link',
      'sort',
      'image',
    ];

    public function background_desc()
      {
          return $this->hasMany('App\Models\Background','parent_id','id')->orderby('sort');
      }
}
