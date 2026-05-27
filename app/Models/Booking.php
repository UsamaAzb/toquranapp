<?php

namespace App\Models;

use App\Support\BookingServiceInterest;
use App\Support\PhoneNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'parent_name',
        'parent_email',
        'parent_phone',
        'child_name',
        'child_age',
        'child_grade',
        'current_school',
        'school_system',
        'primary_challenges',
        'service_interest',
        'preferred_date',
        'preferred_time',
        'consultation_type',
        'consultation_date',
        'main_concerns',
        'how_heard',
        'status',
        'notes',
        'contact_method',
        'booking_reference',
        'terms',
        'teacher_notes',
        'consultation_time',
        'transfer',
        'follow_up_date',
        'parent_id',
        'student_id',
        'meeting_address',
        'meeting_link',
    ];

    protected $casts = [
        // If the column is DATE use 'date'; for DATETIME use 'datetime:Y-m-d'.
        'consultation_date' => 'date', // or 'datetime:Y-m-d'
        'preferred_date' => 'date',
        'follow_up_date' => 'datetime',
    ];

    public function children()
    {
        return $this->hasMany(BookingChild::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public static function normalizeServiceInterestValue(?string $value): ?string
    {
        return BookingServiceInterest::normalize($value);
    }

    public static function displayServiceInterest(?string $value): string
    {
        return BookingServiceInterest::display($value);
    }

    public function preferredTimeLabel(): string
    {
        return match ($this->preferred_time) {
            'morning' => 'Morning',
            'afternoon' => 'Afternoon',
            'evening' => 'Evening',
            default => (string) $this->preferred_time,
        };
    }

    public function consultationTypeLabel(): string
    {
        return $this->consultation_type === 'in-person' ? 'In-Person Consultation' : 'Online Consultation';
    }

    public function intakeChildren(): Collection
    {
        $children = $this->relationLoaded('children')
            ? $this->children
            : $this->children()->get();

        if ($children->isNotEmpty()) {
            return $children;
        }

        return collect([(object) [
            'child_name' => $this->child_name,
            'child_age' => $this->child_age,
            'child_grade' => $this->child_grade,
            'school_system' => $this->school_system,
            'current_school' => $this->current_school,
            'consultation_status' => match ($this->status) {
                'pending', 'confirmed', 'followup', 'cancelled' => $this->status,
                default => null,
            },
            'evaluation_status' => match ($this->status) {
                'fit' => 'fit',
                'unfit' => 'unfit',
                default => null,
            },
            'service_interests' => $this->service_interest
                ? collect(explode(',', $this->service_interest))
                    ->map(fn ($service) => trim($service))
                    ->filter()
                    ->map(fn ($service) => self::normalizeServiceInterestValue($service))
                    ->values()
                    ->all()
                : [],
        ]]);
    }

    public function hasExistingFamilyContext(): bool
    {
        return filled($this->parent_id)
            || $this->existingFamilyContextBookingIds()->isNotEmpty()
            || $this->hasMatchingParentIdentityBooking();
    }

    public function existingFamilyContextBookingIds(): Collection
    {
        $notes = trim((string) $this->notes);

        if ($notes === '' || ! Str::startsWith($notes, '[Website intake] Existing family exact parent match detected via booking ')) {
            return collect();
        }

        if (! preg_match('/via booking ([0-9,\s]+);/i', $notes, $matches)) {
            return collect();
        }

        return collect(explode(',', $matches[1]))
            ->map(fn (string $value) => (int) trim($value))
            ->filter()
            ->values();
    }

    protected function hasMatchingParentIdentityBooking(): bool
    {
        $normalizedEmail = strtolower(trim((string) $this->parent_email));
        $normalizedPhone = PhoneNormalizer::normalize($this->parent_phone);

        if ($normalizedEmail === '' && $normalizedPhone === '') {
            return false;
        }

        return static::query()
            ->when($this->exists, fn (Builder $query) => $query->whereKeyNot($this->getKey()))
            ->where(function (Builder $query) use ($normalizedEmail, $normalizedPhone) {
                $this->applyExactParentIdentityMatch($query, $normalizedEmail, $normalizedPhone);
            })
            ->exists();
    }

    protected function applyExactParentIdentityMatch(Builder $query, string $normalizedEmail, string $normalizedPhone): void
    {
        $hasEmail = $normalizedEmail !== '';
        $hasPhone = $normalizedPhone !== '';
        $parentPhoneExpression = $this->normalizedPhoneExpression('parent_phone');
        $parentEmailExpression = $this->normalizedEmailExpression('parent_email');
        $blankPhoneExpression = $this->blankNormalizedPhoneExpression('parent_phone');
        $blankEmailExpression = $this->blankNormalizedEmailExpression('parent_email');

        if ($hasEmail && $hasPhone) {
            $query->where(function (Builder $query) use (
                $normalizedEmail,
                $normalizedPhone,
                $parentEmailExpression,
                $parentPhoneExpression,
                $blankPhoneExpression,
                $blankEmailExpression
            ) {
                $query->where(function (Builder $query) use ($normalizedEmail, $normalizedPhone, $parentEmailExpression, $parentPhoneExpression) {
                    $query->whereRaw("{$parentEmailExpression} = ?", [$normalizedEmail])
                        ->whereRaw("{$parentPhoneExpression} = ?", [$normalizedPhone]);
                })->orWhere(function (Builder $query) use ($normalizedEmail, $parentEmailExpression, $blankPhoneExpression) {
                    $query->whereRaw("{$parentEmailExpression} = ?", [$normalizedEmail])
                        ->where(function (Builder $query) use ($blankPhoneExpression) {
                            $query->whereNull('parent_phone')
                                ->orWhereRaw($blankPhoneExpression);
                        });
                })->orWhere(function (Builder $query) use ($normalizedPhone, $parentPhoneExpression, $blankEmailExpression) {
                    $query->whereRaw("{$parentPhoneExpression} = ?", [$normalizedPhone])
                        ->where(function (Builder $query) use ($blankEmailExpression) {
                            $query->whereNull('parent_email')
                                ->orWhereRaw($blankEmailExpression);
                        });
                });
            });

            return;
        }

        if ($hasEmail) {
            $query->whereRaw("{$parentEmailExpression} = ?", [$normalizedEmail]);

            return;
        }

        if ($hasPhone) {
            $query->whereRaw("{$parentPhoneExpression} = ?", [$normalizedPhone]);

            return;
        }

        $query->whereRaw('1 = 0');
    }

    protected function normalizedEmailExpression(string $column): string
    {
        return "LOWER(TRIM(COALESCE({$column}, '')))";
    }

    protected function blankNormalizedEmailExpression(string $column): string
    {
        return "TRIM(COALESCE({$column}, '')) = ''";
    }

    protected function normalizedPhoneExpression(string $column): string
    {
        return PhoneNormalizer::sqlExpression($column, DB::connection()->getDriverName());
    }

    protected function blankNormalizedPhoneExpression(string $column): string
    {
        return $this->normalizedPhoneExpression($column)." = ''";
    }
}
