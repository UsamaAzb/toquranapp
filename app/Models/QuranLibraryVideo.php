<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuranLibraryVideo extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUS_UNAVAILABLE = 'unavailable';

    protected $fillable = [
        'quran_library_surah_id',
        'title',
        'subtitle',
        'ayah_from',
        'ayah_to',
        'youtube_url',
        'youtube_embed_url',
        'description',
        'status',
        'source_label',
        'sort_order',
        'created_by_user_id',
        'updated_by_user_id',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
            'ayah_from' => 'integer',
            'ayah_to' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function surah(): BelongsTo
    {
        return $this->belongsTo(QuranLibrarySurah::class, 'quran_library_surah_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
