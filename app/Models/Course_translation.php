<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course_translation extends Model
{
    use HasFactory;
    protected $table = 'course_translations';
    protected $fillable = [
      'title','slug', 'course_id','lang','sort'
    ];

//     public function course()
// {
//     return $this->belongsTo(Course::class, 'course_id', 'id');
// }
}
