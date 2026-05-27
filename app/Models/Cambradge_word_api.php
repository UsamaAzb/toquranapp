<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cambradge_word_api extends Model
{
    protected $table = 'cambradge_words_api';

    use HasFactory;

    protected $fillable = [
        'word',
        'image',
        'us_sound',
        'uk_sound',
        'difficulty_levels',
        'wrong_spelling',
        'wrong_spelling_rules',
        'wrong_spelling_source',
        'difficulty_reason',
        'difficulty_source',
    ];

    protected function casts(): array
    {
        return [
            'wrong_spelling_rules' => 'array',
        ];
    }
}
