<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardDisciplineTransfer extends Model
{
    use HasFactory;

    protected $table = 'reward_discipline_transfer';

    protected $fillable = [
        'title',
        'points',
        'status',
        'description',
        'created_at',
        'updated_at',
        'type',
        'discipline_icon_id',
        'discipline_icon_path',
        'sort',
        'teacher_desc',
        'selected',
    ];

    public $timestamps = false;

    public function icon()
    {
        return $this->belongsTo(DisciplineIcon::class, 'discipline_icon_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
