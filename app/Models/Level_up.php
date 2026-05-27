<?php
namespace  App\Models;
use Illuminate\Database\Eloquent\Model;


class Level_up extends Model
{
  protected $table = 'level_up';
  protected $fillable = [
      'id','title','slug', 'iframe_link','sort'
  ];
//   public function audio_unit()
//       {
//           return $this->belongsTo(Story::class, 'story_id');
//       }
}
