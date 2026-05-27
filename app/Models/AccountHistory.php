<?php

namespace App\Models;

use App\Models\Builders\AccountHistoryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use RuntimeException;

class AccountHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'parent_id',
        'event_type',
        'reason_code',
        'actor_user_id',
        'actor_role',
        'subject_type',
        'subject_id',
        'old_value',
        'new_value',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $history): void {
            $history->created_at ??= now();
        });

        static::updating(function (): void {
            throw new RuntimeException('Account History is append-only.');
        });

        static::deleting(function (): void {
            throw new RuntimeException('Account History is append-only.');
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function newEloquentBuilder($query): AccountHistoryBuilder
    {
        /** @var QueryBuilder $query */
        return new AccountHistoryBuilder($query);
    }

    public function save(array $options = []): bool
    {
        if ($this->exists && $this->isDirty()) {
            throw new RuntimeException('Account History is append-only.');
        }

        return parent::save($options);
    }

    public function delete(): ?bool
    {
        throw new RuntimeException('Account History is append-only.');
    }
}
