<?php
namespace  App\Models;
use Illuminate\Database\Eloquent\Model;


class Story_chapter extends Model
{
  protected $table = 'story_chapters';
  protected $fillable = [
      'id','title','slug', 'iframe_link','text','audio','sort','story_id'
  ];
  public function audio_unit()
      {
          return $this->belongsTo(Story::class, 'story_id');
      }
}
