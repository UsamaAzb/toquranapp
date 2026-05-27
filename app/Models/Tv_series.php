<?php
namespace  App\Models;
use Illuminate\Database\Eloquent\Model;


class Tv_series extends Model
{
  protected $table = 'tv_series';
  protected $fillable = ['id','type'];
  public function series_seasons()
      {
          return $this->hasMany(Series_season::class, 'type_id');
      }
}
