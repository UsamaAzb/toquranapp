<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectBranch extends Model
{
    use HasFactory;

    protected $table = 'subject_branchs';

    protected $fillable = [
        'title',
        'subject_id',
        'parent_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the subject that owns this branch.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the parent branch.
     */
    public function parent()
    {
        return $this->belongsTo(SubjectBranch::class, 'parent_id');
    }

    /**
     * Get the child branches.
     */
    public function children()
    {
        return $this->hasMany(SubjectBranch::class, 'parent_id');
    }

    /**
     * Get all descendants recursively.
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors recursively.
     */
    public function ancestors()
    {
        return $this->parent()->with('ancestors');
    }

    /**
     * Scope to get root branches (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Check if this branch is a root branch.
     */
    public function isRoot()
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this branch has children.
     */
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }
}
