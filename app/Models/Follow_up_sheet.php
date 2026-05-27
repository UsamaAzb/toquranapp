<?php
namespace  App\Models;
use Illuminate\Database\Eloquent\Model;


class Follow_up_sheet extends Model
{
  protected $table = 'follow_up_sheet';
  protected $fillable = [
      'id','student_id','google_sheet'
  ];
    public function student()
      {
          return $this->belongsTo(Student::class);
      }
}
