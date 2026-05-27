<?php
namespace  App\Models;
use Illuminate\Database\Eloquent\Model;


class Ted_video extends Model
{
  protected $table = 'ted_videos';
  protected $fillable = [
      'id','title','video_link','image','sort'
  ];

}
