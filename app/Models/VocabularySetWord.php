<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VocabularySetWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'vocabulary_set_id',
        'word_id',
        'position',
        'added_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'vocabulary_set_id' => 'integer',
            'word_id' => 'integer',
            'position' => 'integer',
            'added_by_user_id' => 'integer',
        ];
    }

    public function vocabularySet(): BelongsTo
    {
        return $this->belongsTo(VocabularySet::class);
    }

    public function word(): BelongsTo
    {
        return $this->belongsTo(Cambradge_word_api::class, 'word_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }
}
