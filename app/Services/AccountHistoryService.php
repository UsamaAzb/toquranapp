<?php

namespace App\Services;

use App\Models\AccountHistory;
use Illuminate\Support\Facades\Auth;

class AccountHistoryService
{
    /**
     * @param  array{
     *     reason_code?: string|null,
     *     actor_user_id?: int|null,
     *     actor_role?: string|null,
     *     subject_type?: string,
     *     subject_id?: int|null,
     *     old_value?: string|null,
     *     new_value?: string|null,
     *     metadata?: array<string, mixed>|null
     * }  $options
     */
    public function record(int $parentId, string $eventType, array $options = []): AccountHistory
    {
        $subjectType = $options['subject_type'] ?? 'family';

        return AccountHistory::create([
            'parent_id' => $parentId,
            'event_type' => $eventType,
            'reason_code' => $options['reason_code'] ?? null,
            'actor_user_id' => $options['actor_user_id'] ?? Auth::id(),
            'actor_role' => $options['actor_role'] ?? (Auth::user()?->getRoleNames()->first() ?? null),
            'subject_type' => $subjectType,
            'subject_id' => $options['subject_id'] ?? ($subjectType === 'family' ? $parentId : null),
            'old_value' => $options['old_value'] ?? null,
            'new_value' => $options['new_value'] ?? null,
            'metadata' => $options['metadata'] ?? null,
        ]);
    }
}
