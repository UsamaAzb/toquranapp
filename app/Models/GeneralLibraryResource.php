<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeneralLibraryResource extends Model
{
    public const TYPE_FILE = 'file';

    public const TYPE_LINK = 'link';

    public const TYPE_YOUTUBE = 'youtube';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUS_UNAVAILABLE = 'unavailable';

    protected $fillable = [
        'general_library_folder_id',
        'resource_type',
        'title',
        'description',
        'status',
        'source_label',
        'storage_disk',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'external_url',
        'sort_order',
        'created_by_user_id',
        'updated_by_user_id',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'archived_at' => 'datetime',
            'file_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(GeneralLibraryFolder::class, 'general_library_folder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function isFile(): bool
    {
        return $this->resource_type === self::TYPE_FILE;
    }

    public function isLink(): bool
    {
        return $this->resource_type === self::TYPE_LINK;
    }

    public function isYoutube(): bool
    {
        return $this->resource_type === self::TYPE_YOUTUBE;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }
}
