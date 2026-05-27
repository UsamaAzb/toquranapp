<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentModel extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'first_name',
        'last_name',
        'user_id',
        'phone',
        'email',
        'password',
        'user_name',
        'family_support_id',
        'image',
        'active',
        'lifecycle_status',
    ];

    protected $casts = [
        'active' => 'boolean',
        'lifecycle_status' => 'string',
    ];

    /**
     * Get the user associated with the parent.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the family support user associated with the parent.
     */
    public function familySupport(): BelongsTo
    {
        return $this->belongsTo(User::class, 'family_support_id');
    }

    /**
     * Get all students associated with this parent.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'parent_id', 'id');
    }

    public function accountHistories(): HasMany
    {
        return $this->hasMany(AccountHistory::class, 'parent_id');
    }

    /**
     * Get the parent's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /**
     * Get the parent's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ?: $this->email ?: 'Parent #'.$this->id;
    }

    /**
     * Scope to filter active parents.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to search parents by name or email.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }
}
