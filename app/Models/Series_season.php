<?php
namespace  App\Models;
use Illuminate\Database\Eloquent\Model;


class Series_season extends Model
{
  protected $table = 'series_seasons';
  protected $fillable = [
      'id','title','type_id'];
  public function series_episodes()
      {
          return $this->hasMany(Series_episode::class, 'series_season_id');
      }
}
