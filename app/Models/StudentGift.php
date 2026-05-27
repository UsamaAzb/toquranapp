<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StudentGift extends Model
{
    use HasFactory;

    public const DEFAULT_UPCOMING_COUNT = 9;

    public const DEFAULT_POINTS_INTERVAL = 100;

    protected $fillable = [
        'academic_year_id',
        'student_id',
        'gift_id',
        'gift_name',
        'gift_image',
        'points_required',
        'status',
        'approved_by_id',
        'approved_by_name',
        'created_at',
        'reached_at',
        'redeemed_at',
        'gift_order',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'reached_at' => 'datetime',

    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_REACHED = 'reached';

    public const STATUS_WAITING = 'waiting';

    public const STATUS_REDEEMED = 'redeemed';

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_REACHED,
            self::STATUS_WAITING,
            self::STATUS_REDEEMED,
        ];
    }

    public static function defaultGiftName(int $index): string
    {
        return 'Reward'.$index;
    }

    public static function displayGiftName(?string $name, int $index): string
    {
        $name = trim((string) $name);

        if ($name === '' || preg_match('/^gift\s*[0-9]+$/i', $name) === 1) {
            return self::defaultGiftName($index);
        }

        return $name;
    }

    public static function imageUrlFor(?string $path): string
    {
        static $existsCache = [];

        $path = trim((string) $path);

        if ($path === '') {
            return asset('storage/gifts/default_gift.png');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $existsCache[$path] ??= Storage::disk('public')->exists($path);

        if (! $existsCache[$path]) {
            return asset('storage/gifts/default_gift.png');
        }

        return asset('storage/'.$path);
    }

    public function imageUrl(): string
    {
        return self::imageUrlFor($this->gift_image);
    }

    public static function maxCompletedPoints(int $studentId, ?int $academicYearId = null): ?int
    {
        $academicYearId ??= AcademicYear::currentId();

        return self::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->whereIn('status', [self::STATUS_REACHED, self::STATUS_REDEEMED])
            ->max('points_required');
    }

    public static function protectedSchedulingFloorPoints(int $studentId, ?int $academicYearId = null): ?int
    {
        $academicYearId ??= AcademicYear::currentId();

        $completedFloor = self::maxCompletedPoints($studentId, $academicYearId);
        $pendingFloor = self::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', self::STATUS_PENDING)
            ->whereNotNull('points_required')
            ->min('points_required');

        $floors = array_filter(
            [$completedFloor, $pendingFloor],
            fn ($value): bool => $value !== null
        );

        return $floors === [] ? null : max(array_map('intval', $floors));
    }

    public static function createWaitingAfterReached(int $studentId, ?int $academicYearId = null): ?self
    {
        $academicYearId ??= AcademicYear::currentId();
        $maxPoints = (int) self::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->max('points_required');
        $nextPoints = $maxPoints + self::pointsIntervalForStudent($studentId, $academicYearId);

        $nextIndex = self::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->count() + 1;

        return self::firstOrCreate(
            [
                'student_id' => $studentId,
                'academic_year_id' => $academicYearId,
                'points_required' => $nextPoints,
            ],
            [
                'gift_id' => null,
                'gift_name' => self::defaultGiftName($nextIndex),
                'gift_image' => null,
                'status' => self::STATUS_WAITING,
                'approved_by_id' => null,
                'approved_by_name' => null,
                'approval_timestamp' => null,
                'redeemed_at' => null,
            ]
        );
    }

    public static function requeueForStudent(int $studentId, ?int $academicYearId = null): void
    {
        $academicYearId ??= AcademicYear::currentId();

        DB::transaction(function () use ($studentId, $academicYearId) {
            $floor = self::maxCompletedPoints($studentId, $academicYearId);

            self::where('student_id', $studentId)
                ->where('academic_year_id', $academicYearId)
                ->whereNotIn('status', [self::STATUS_REACHED, self::STATUS_REDEEMED])
                ->update(['status' => self::STATUS_WAITING]);

            $pendingQuery = self::where('student_id', $studentId)
                ->where('academic_year_id', $academicYearId)
                ->whereNotIn('status', [self::STATUS_REACHED, self::STATUS_REDEEMED]);

            if ($floor !== null) {
                $pendingQuery->where('points_required', '>', $floor);
            }

            $nextPendingId = $pendingQuery
                ->orderByRaw('(points_required IS NULL), points_required ASC, created_at ASC')
                ->value('id');

            if ($nextPendingId) {
                self::whereKey($nextPendingId)->update(['status' => self::STATUS_PENDING]);
            }
        });
    }

    public static function maintainUpcomingRunway(
        int $studentId,
        ?int $academicYearId = null,
        int $upcomingCount = self::DEFAULT_UPCOMING_COUNT
    ): void {
        $academicYearId ??= AcademicYear::currentId();
        $targetOpenCount = $upcomingCount + 1;

        DB::transaction(function () use ($studentId, $academicYearId, $targetOpenCount): void {
            self::requeueForStudent($studentId, $academicYearId);

            for ($guard = 0; $guard < $targetOpenCount; $guard++) {
                $openCount = self::where('student_id', $studentId)
                    ->where('academic_year_id', $academicYearId)
                    ->whereNotIn('status', [self::STATUS_REACHED, self::STATUS_REDEEMED])
                    ->count();

                if ($openCount >= $targetOpenCount) {
                    return;
                }

                self::createWaitingAfterReached($studentId, $academicYearId);
                self::requeueForStudent($studentId, $academicYearId);
            }
        });
    }

    public static function pointsIntervalForStudent(int $studentId, ?int $academicYearId = null): int
    {
        $academicYearId ??= AcademicYear::currentId();
        $points = self::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->whereNotNull('points_required')
            ->orderBy('points_required', 'desc')
            ->limit(2)
            ->pluck('points_required')
            ->map(fn ($value): int => (int) $value)
            ->values();

        if ($points->count() >= 2) {
            $interval = $points[0] - $points[1];

            if ($interval > 0) {
                return $interval;
            }
        }

        return self::DEFAULT_POINTS_INTERVAL;
    }

    /**
     * Get the student that owns the gift assignment.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    /**
     * Get the gift that owns the assignment.
     */
    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Scope to filter pending gifts.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter redeemed gifts.
     */
    public function scopeRedeemed($query)
    {
        return $query->where('status', 'redeemed');
    }

    /**
     * Scope to filter by student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope to order by points required.
     */
    public function scopeOrderByPoints($query)
    {
        return $query->orderBy('points_required');
    }
}
