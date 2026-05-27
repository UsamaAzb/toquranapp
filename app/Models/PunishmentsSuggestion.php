<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PunishmentsSuggestion extends Model
{
    use HasFactory;

    protected $table = 'punishments_suggestions';

    protected $fillable = [
        'punishment_type_id',
        'suggestion_text',
    ];
}
