<?php

namespace App\Services;

use App\Models\BookingChild;

class BookingChildMetricsService
{
    public function activeFitCount(): int
    {
        return BookingChild::query()
            ->where('evaluation_outcome', 'fit')
            ->where('transfer_status', '!=', 'transferred')
            ->count();
    }
}
