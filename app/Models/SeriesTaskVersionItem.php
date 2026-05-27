<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeriesTaskVersionItem extends Model
{
    protected $table = 'series_task_version_items';

    protected $fillable = [
        'version_id',
        'library_source_type',
        'library_source_id',
        'library_title_snapshot',
        'library_url_snapshot',
        'library_summary_snapshot',
        'sequence_position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'library_source_id' => 'integer',
            'sequence_position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(SeriesTaskVersion::class, 'version_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
