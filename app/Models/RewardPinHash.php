<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class RewardPinHash extends Model
{
    use HasFactory;

    protected $table = 'reward_pin_hashes';

    protected $fillable = [
        'user_id',
        'pin_hash',
        'pin_unhash',
        'updated_at',
    ];

    const CREATED_AT = null;

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the PIN hash.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Verify a PIN against the stored hash.
     */
    public function verifyPin($pin): bool
    {
        return Hash::check($pin, $this->pin_hash);
    }

    /**
     * Create a new PIN hash for a user.
     */
    public static function createForUser($userId, $pin): self
    {

        // Create new active PIN
        return self::create([
            'user_id' => $userId,
            'pin_unhash' => $pin,
            'pin_hash' => Hash::make($pin),
            'updated_at' => now(),
        ]);
    }

    /**
     * Verify a PIN for a user.
     */
    public function verifyUserPin(int $userId, string $input): bool
    {
        $row = UserPinHash::where('user_id', $userId)->first();
        if (! $row) {
            return false;
        }

        try {
            return hash_equals(Crypt::decryptString($row->pin_hash), $input);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
