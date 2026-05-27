<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PunishmentType extends Model
{
    use HasFactory;

    protected $table = 'punishment_types';

    protected $fillable = [
        'title', 'decrease_point',

    ];

    public $timestamps = false;

    public function agreements()
    {
        return $this->hasMany(PunishmentAgreement::class, 'punishment_type_id');
    }
}
