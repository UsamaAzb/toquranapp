<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use App\Models\Course_translation as Course_translation
class Hangman_word extends Model
{
    use HasFactory;

    protected $table = 'hangman_words';

    protected $fillable = [
        'word', 'category_id', 'image', 'sound', 'camb_sound_id',
    ];

    public function Cambradge_word_api()
    {
        return $this->hasOne(Cambradge_word_api::class, 'camb_sound_id', 'id');
    }
}
