<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use App\Models\Course_translation as Course_translation
class Course extends Model
{
    use HasFactory;
    protected $table = 'courses';
    protected $fillable = [
      'title','slug', 'parent_id','active','code','category_id','sort'
    ];

    public function children()
      {
          return $this->hasMany('App\Models\Course','parent_id');
      }
      public function translations()
        {
          // dd('sd');
            return $this->hasMany(Course_translation::class);
        }
}
