<?php
namespace  App\Models;
use Illuminate\Database\Eloquent\Model;


class Court_video extends Model
{
  protected $table = 'court_videos';
  protected $fillable = [
      'id','title','video_link','image','sort'
  ];

}
