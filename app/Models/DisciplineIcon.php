<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplineIcon extends Model
{
    use HasFactory;

    protected $table = 'discipline_icons';
  public $timestamps = false; 
    protected $fillable = [
        
        'path',
       
    ];

    
}
