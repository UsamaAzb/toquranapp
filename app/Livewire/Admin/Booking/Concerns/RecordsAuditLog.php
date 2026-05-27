<?php

namespace App\Livewire\Admin\Booking\Concerns;

use App\Models\BookingChild;
use App\Models\BookingChildAuditLog;

trait RecordsAuditLog
{
    protected function logChildChange(BookingChild $child, string $fieldName, mixed $fromValue, mixed $toValue): void
    {
        BookingChildAuditLog::create([
            'booking_child_id' => $child->id,
            'field_name' => $fieldName,
            'from_value' => $this->normalizeAuditValue($fromValue),
            'to_value' => $this->normalizeAuditValue($toValue),
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);
    }

    protected function normalizeAuditValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }
}
