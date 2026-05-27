<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
      'title',
      'page_title',
      'subject_id',
      'description',
      'alies',
      'active',
    ];

    public function question_banks()
      {
          return $this->hasMany('App\Models\Question_bank','cat_id')->where('parent_id', 0);
      }
}
