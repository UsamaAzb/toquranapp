<?php
namespace  App\Models;
use Illuminate\Database\Eloquent\Model;


class Foundation extends Model
{
  protected $table = 'foundations';
  protected $fillable = [
      'id','title','order','active'
  ];
  public function foundation_details()
      {
          return $this->hasMany(Foundation_detail::class, 'Foundation_id')->orderBy('order');
      }
}
