<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VocabularySet extends Model
{
    use HasFactory;

    public const NODE_FOLDER = 'folder';

    public const NODE_PLAYABLE = 'playable';

    public const TYPE_SYSTEM = 'system';

    public const TYPE_TEACHER = 'teacher';

    public const TYPE_LEGACY_IMPORT = 'legacy_import';

    public const SOURCE_CUSTOM = 'custom';

    public const SOURCE_LEGACY_CAMBRIDGE = 'legacy_cambridge';

    public const SOURCE_LEGACY_PHONICS = 'legacy_phonics';

    public const SOURCE_LEGACY_GROUP = 'legacy_group';

    public const SOURCE_LEGACY_DIFFICULTY = 'legacy_difficulty';

    public const SOURCE_LEGACY_HANGMAN = 'legacy_hangman';

    public const VISIBILITY_PRIVATE = 'private';

    public const VISIBILITY_SHARED = 'shared';

    public const VISIBILITY_SYSTEM = 'system';

    public const VISIBILITY_ARCHIVED = 'archived';

    protected $fillable = [
        'parent_id',
        'title',
        'description',
        'node_type',
        'set_type',
        'source_kind',
        'source_key',
        'owner_user_id',
        'visibility',
        'sort_order',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'owner_user_id' => 'integer',
            'sort_order' => 'integer',
            'created_by_user_id' => 'integer',
            'updated_by_user_id' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('title');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(VocabularySetWord::class);
    }

    public function words(): BelongsToMany
    {
        return $this->belongsToMany(
            Cambradge_word_api::class,
            'vocabulary_set_words',
            'vocabulary_set_id',
            'word_id'
        )
            ->withPivot(['position', 'added_by_user_id'])
            ->orderBy('vocabulary_set_words.position')
            ->orderBy('vocabulary_set_words.id');
    }

    public function accessRows(): HasMany
    {
        return $this->hasMany(VocabularySourceAccess::class);
    }

    public function gameAssignments(): HasMany
    {
        return $this->hasMany(VocabularyGameAssignment::class);
    }

    public function scopePlayable(Builder $query): Builder
    {
        return $query->where('node_type', self::NODE_PLAYABLE);
    }

    public function scopeFolders(Builder $query): Builder
    {
        return $query->where('node_type', self::NODE_FOLDER);
    }

    public function scopeVisibleToTeachers(Builder $query, ?int $teacherUserId = null): Builder
    {
        return $query->where(function (Builder $visibilityQuery) use ($teacherUserId): void {
            $visibilityQuery->whereIn('visibility', [self::VISIBILITY_SHARED, self::VISIBILITY_SYSTEM]);

            if ($teacherUserId !== null) {
                $visibilityQuery->orWhere('owner_user_id', $teacherUserId);
            }
        });
    }

    public function isFolder(): bool
    {
        return $this->node_type === self::NODE_FOLDER;
    }

    public function isPlayable(): bool
    {
        return $this->node_type === self::NODE_PLAYABLE;
    }

    public function isArchived(): bool
    {
        return $this->visibility === self::VISIBILITY_ARCHIVED;
    }

    public function canBeLaunched(): bool
    {
        return $this->isPlayable() && ! $this->isArchived();
    }
}
