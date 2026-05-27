<?php
namespace  App\Models;
use Illuminate\Database\Eloquent\Model;


class Foundation_detail extends Model
{
  protected $table = 'foundation_details';
  protected $fillable = [
      'id','title','type', 'file','order','foundation_id','active'
  ];
  public function foundation()
      {
          return $this->belongsTo(Foundation::class, 'foundation_id');
      }
}
