<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VocabularyGameAssignment extends Model
{
    use HasFactory;

    public const AUDIENCE_STUDENT = 'student';

    public const AUDIENCE_CLASS = 'class';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    public const DIFFICULTY_STUDENT_CHOICE = 'student_choice';

    protected $fillable = [
        'vocabulary_set_id',
        'assigned_by_user_id',
        'audience_type',
        'audience_id',
        'allowed_games',
        'difficulty_policy',
        'status',
        'starts_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'vocabulary_set_id' => 'integer',
            'assigned_by_user_id' => 'integer',
            'audience_id' => 'integer',
            'allowed_games' => 'array',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function vocabularySet(): BelongsTo
    {
        return $this->belongsTo(VocabularySet::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForAudience(Builder $query, string $audienceType, int $audienceId): Builder
    {
        return $query
            ->where('audience_type', $audienceType)
            ->where('audience_id', $audienceId);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getPlayUrlAttribute(): string
    {
        return route('vocabulary.games.assignment', [
            'assignment' => $this->id,
        ]);
    }

    public function playUrl(?string $game = null, ?string $difficulty = null): string
    {
        return route('vocabulary.games.assignment', array_filter([
            'assignment' => $this->id,
            'game' => $game,
            'difficulty' => $difficulty,
        ]));
    }

    public function allowsGame(string $game): bool
    {
        return in_array($game, $this->allowed_games ?? [], true);
    }
}
