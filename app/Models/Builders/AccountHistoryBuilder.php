<?php

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

class AccountHistoryBuilder extends Builder
{
    public function update(array $values)
    {
        throw new RuntimeException('Account History is append-only.');
    }

    public function delete()
    {
        throw new RuntimeException('Account History is append-only.');
    }

    public function forceDelete()
    {
        throw new RuntimeException('Account History is append-only.');
    }

    public function touch($column = null)
    {
        throw new RuntimeException('Account History is append-only.');
    }

    public function increment($column, $amount = 1, array $extra = [])
    {
        throw new RuntimeException('Account History is append-only.');
    }

    public function decrement($column, $amount = 1, array $extra = [])
    {
        throw new RuntimeException('Account History is append-only.');
    }

    public function upsert(array $values, $uniqueBy, $update = null)
    {
        throw new RuntimeException('Account History is append-only.');
    }

    public function truncate()
    {
        throw new RuntimeException('Account History is append-only.');
    }
}
