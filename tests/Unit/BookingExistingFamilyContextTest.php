<?php

namespace Tests\Unit;

use App\Models\Booking;
use Tests\TestCase;

class BookingExistingFamilyContextTest extends TestCase
{
    public function test_existing_family_context_is_detected_from_parent_id(): void
    {
        $booking = new Booking([
            'parent_id' => 44,
            'notes' => null,
        ]);

        $this->assertTrue($booking->hasExistingFamilyContext());
    }

    public function test_existing_family_context_is_detected_from_website_operational_note(): void
    {
        $booking = new Booking([
            'parent_id' => null,
            'notes' => '[Website intake] Existing family exact parent match detected via booking 12, 18; submission appears to be a genuinely new child intake.',
        ]);

        $this->assertTrue($booking->hasExistingFamilyContext());
        $this->assertSame([12, 18], $booking->existingFamilyContextBookingIds()->all());
    }

    public function test_existing_family_context_is_false_without_parent_link_or_marker_note(): void
    {
        $booking = new Booking([
            'parent_id' => null,
            'notes' => 'General booking notes only.',
        ]);

        $this->assertFalse($booking->hasExistingFamilyContext());
        $this->assertSame([], $booking->existingFamilyContextBookingIds()->all());
    }
}
