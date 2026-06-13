<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuranLibrarySurah extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'surah_number',
        'title',
        'title_ar',
        'ayah_count',
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
            'ayah_count' => 'integer',
            'sort_order' => 'integer',
            'surah_number' => 'integer',
        ];
    }

    public function videos(): HasMany
    {
        return $this->hasMany(QuranLibraryVideo::class);
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
