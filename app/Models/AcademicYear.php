<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class AcademicYear extends Model
{
    use HasFactory;

    protected $table = 'academic_years';

    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'is_current',
    ];

    // public $timestamps = false;

    public static function current(): self
    {
        if (! Schema::hasTable('academic_years')) {
            throw new RuntimeException('Academic years table does not exist.');
        }

        $currentAcademicYears = self::query()
            ->where('is_current', 1)
            ->orderBy('id')
            ->limit(2)
            ->get();

        if ($currentAcademicYears->isEmpty()) {
            throw new RuntimeException('No current academic year is configured.');
        }

        if ($currentAcademicYears->count() > 1) {
            throw new RuntimeException('Multiple current academic years are configured.');
        }

        return $currentAcademicYears->first();
    }

    public static function currentId(): int
    {
        return (int) self::current()->id;
    }
}
