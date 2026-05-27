<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
protected $table='calendar_events';
  protected $fillable = [
    'title','description','url',
    'category','start','end','all_day','guests','created_by_user_id'
  ];

  protected $casts = [
    'start' => 'datetime',
    'end' => 'datetime',
    'all_day' => 'boolean',
  ];

  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by_user_id');
  }
}
?>
