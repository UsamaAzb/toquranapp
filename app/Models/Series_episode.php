<?php
namespace  App\Models;
use Illuminate\Database\Eloquent\Model;


class Series_episode extends Model
{
  protected $table = 'series_episodes';
  protected $fillable = [
      'id','title','slug', 'link','active','sort','type_id','series_season_id','subtitles'
  ];
  public function series_season()
      {
          return $this->belongsTo(Series_season::class, 'series_season_id');
      }
}
