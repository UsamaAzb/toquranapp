<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibraryResource extends Model
{
    use HasFactory;

    public const TYPE_FILE = 'file';

    public const TYPE_LINK = 'link';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUS_UNAVAILABLE = 'unavailable';

    protected $fillable = [
        'owner_user_id',
        'subject_id',
        'library_section_id',
        'resource_type',
        'title',
        'description',
        'status',
        'storage_disk',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'external_url',
        'sort_order',
        'created_by_user_id',
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

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(LibrarySection::class, 'library_section_id');
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('owner_user_id', $userId);
    }

    public function scopeForSubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function scopeFiles(Builder $query): Builder
    {
        return $query->where('resource_type', self::TYPE_FILE);
    }

    public function scopeLinks(Builder $query): Builder
    {
        return $query->where('resource_type', self::TYPE_LINK);
    }

    public function isFile(): bool
    {
        return $this->resource_type === self::TYPE_FILE;
    }

    public function isLink(): bool
    {
        return $this->resource_type === self::TYPE_LINK;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function isUnavailable(): bool
    {
        return $this->status === self::STATUS_UNAVAILABLE;
    }
}
