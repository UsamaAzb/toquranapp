<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VocabularySourceAccess extends Model
{
    use HasFactory;

    protected $table = 'vocabulary_source_access';

    public const AUDIENCE_STUDENT = 'student';

    public const AUDIENCE_CLASS = 'class';

    public const STATUS_ENABLED = 'enabled';

    public const STATUS_DISABLED = 'disabled';

    protected $fillable = [
        'vocabulary_set_id',
        'audience_type',
        'audience_id',
        'status',
        'enabled_by_user_id',
        'enabled_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'vocabulary_set_id' => 'integer',
            'audience_id' => 'integer',
            'enabled_by_user_id' => 'integer',
            'enabled_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function vocabularySet(): BelongsTo
    {
        return $this->belongsTo(VocabularySet::class);
    }

    public function enabledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enabled_by_user_id');
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    public function scopeForAudience(Builder $query, string $audienceType, int $audienceId): Builder
    {
        return $query
            ->where('audience_type', $audienceType)
            ->where('audience_id', $audienceId);
    }

    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }
}
