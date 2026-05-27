<?php

namespace Tests\Feature;

use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithBookingIntakeTables;
use Tests\TestCase;

class BookingExistingFamilyContextQueryTest extends TestCase
{
    use InteractsWithBookingIntakeTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createBookingIntakeTables();
    }

    public function test_existing_family_context_falls_back_to_matching_parent_identity_without_parent_id_or_note(): void
    {
        $this->createExistingBookingChild([
            'parent_phone' => '01000111222',
        ]);

        $booking = Booking::create([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '+201000111222',
            'booking_reference' => 'BK-INTAKE-9009',
            'status' => 'pending',
            'service_interest' => 'Help Me Study',
            'parent_id' => null,
            'notes' => null,
        ]);

        $this->assertTrue($booking->hasExistingFamilyContext());
    }
}
