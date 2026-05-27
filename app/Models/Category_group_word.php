<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category_group_word extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'category_group_word';

    protected $fillable = [
        'name',
        'order',
        'active',
    ];
}
