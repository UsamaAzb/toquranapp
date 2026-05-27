<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gift extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'default_points_required',
        'category',
        'is_active',
    ];

    public $timestamps = false;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the students that have this gift assigned.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_gifts')
            ->withPivot([
                'points_required',
                'status',
                'approved_by_id',
                'approved_by_name',
                'created_at',
                'reached_at',
                'redeemed_at',
                'gift_order',
            ]);
    }

    /**
     * Get the student gift assignments for this gift.
     */
    public function studentGifts(): HasMany
    {
        return $this->hasMany(StudentGift::class);
    }

    /**
     * Scope to filter active gifts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to order by points required.
     */
    public function scopeOrderByPoints($query)
    {
        return $query->orderBy('default_points_required');
    }
}
