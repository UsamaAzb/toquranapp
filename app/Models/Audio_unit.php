<?php
namespace  App\Models;
use Illuminate\Database\Eloquent\Model;


class Audio_unit extends Model
{
  protected $table = 'audio_units';
  protected $fillable = [
      'id','title','order','level_id','active'
  ];
  public function audio_lessons()
      {
          return $this->hasMany(Audio_lesson::class, 'unit_id')->orderBy('order');
      }
}
