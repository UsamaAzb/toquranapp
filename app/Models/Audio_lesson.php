<?php
namespace  App\Models;
use Illuminate\Database\Eloquent\Model;


class Audio_lesson extends Model
{
  protected $table = 'audio_lessons';
  protected $fillable = [
      'id','title','type', 'file','order','unit_id','active'
  ];
  public function audio_unit()
      {
          return $this->belongsTo(Unit::class, 'unit_id');
      }
}
