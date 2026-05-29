<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingIntakeReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithBookingIntakeTables;
use Tests\TestCase;

class BookingIntakeEntrypointTest extends TestCase
{
    use InteractsWithBookingIntakeTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
        $this->createBookingIntakeTables();
    }

    public function test_flagged_duplicate_submission_routes_to_review_before_any_normal_booking_write(): void
    {
        $existingChild = $this->createExistingBookingChild();

        $response = $this->postJson(route('admin.bookings.intake.store'), [
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'children' => [[
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $response->assertStatus(202)
            ->assertJsonPath('route', 'review');

        $this->assertSame(1, Booking::query()->count());
        $this->assertSame(1, BookingIntakeReview::query()->count());
        $this->assertSame($existingChild->booking_id, BookingIntakeReview::query()->first()->matched_booking_id);
    }

    public function test_phone_only_blocked_submission_routes_to_review_without_email(): void
    {
        \App\Models\BookingParentBlock::query()->create([
            'normalized_email' => null,
            'normalized_phone' => '201000111999',
            'block_reason' => 'Manual phone block',
        ]);

        $response = $this->postJson(route('admin.bookings.intake.store'), [
            'parent_name' => 'Phone Only Parent',
            'parent_email' => null,
            'parent_phone' => '+20 100 011 1999',
            'children' => [[
                'child_name' => 'Phone Only Child',
                'child_age' => '10',
                'child_grade' => '5',
                'school_system' => 'American',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $response->assertStatus(202)
            ->assertJsonPath('route', 'review');

        $review = BookingIntakeReview::query()->firstOrFail();

        $this->assertNull($review->parent_email);
        $this->assertSame('+20 100 011 1999', $review->parent_phone);
        $this->assertSame('blocked_parent', $review->detection_reason);
        $this->assertSame(0, Booking::query()->count());
    }

    public function test_clean_submission_creates_new_booking_and_child_via_intake_endpoint(): void
    {
        $response = $this->postJson(route('admin.bookings.intake.store'), [
            'parent_name' => 'Amina Salem',
            'parent_email' => 'amina@example.test',
            'parent_phone' => '201000222333',
            'notes' => 'New family intake.',
            'children' => [[
                'child_name' => 'Sara',
                'child_age' => '9',
                'child_grade' => '4',
                'school_system' => 'IB',
                'service_interests' => ['IB Private Classes'],
            ]],
        ]);

        $response->assertCreated()
            ->assertJsonPath('route', 'normal');

        $booking = Booking::query()->with('children')->firstOrFail();

        $this->assertSame('Amina Salem', $booking->parent_name);
        $this->assertSame(['Sara'], $booking->children->pluck('child_name')->all());
        $this->assertSame(['Quran Memorization'], $booking->children->first()->service_interests);
        $this->assertSame(1, \Illuminate\Support\Facades\DB::table('booking_intake_submission_locks')->count());
    }

    public function test_flat_intake_payload_requires_complete_child_details(): void
    {
        $response = $this->postJson(route('admin.bookings.intake.store'), [
            'parent_name' => 'Amina Salem',
            'parent_email' => 'amina@example.test',
            'parent_phone' => '201000222333',
            'child_name' => 'Sara',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'child_age',
                'child_grade',
                'school_system',
                'service_interests',
            ]);

        $this->assertSame(0, Booking::query()->count());
        $this->assertSame(0, BookingIntakeReview::query()->count());
    }

    public function test_contact_mismatch_submission_routes_to_review_before_any_normal_booking_write(): void
    {
        $existingChild = $this->createExistingBookingChild([
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
        ]);

        $response = $this->postJson(route('admin.bookings.intake.store'), [
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111999',
            'children' => [[
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $response->assertStatus(202)
            ->assertJsonPath('route', 'review');

        $this->assertSame(1, Booking::query()->count());

        $review = BookingIntakeReview::query()->with('reviewChildren')->firstOrFail();

        $this->assertSame('suspected_contact_mismatch', $review->detection_reason);
        $this->assertSame($existingChild->booking_id, $review->matched_booking_id);
        $this->assertNull($review->matched_child_id);
        $this->assertSame('suspected_contact_mismatch', $review->reviewChildren->first()->review_reason);
        $this->assertSame($existingChild->booking_id, $review->reviewChildren->first()->matched_booking_id);
        $this->assertNull($review->reviewChildren->first()->matched_child_id);
    }

    public function test_existing_family_new_child_appends_to_existing_booking_container_via_intake_endpoint(): void
    {
        $existingChild = $this->createExistingBookingChild();

        $response = $this->postJson(route('admin.bookings.intake.store'), [
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'children' => [[
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $response->assertCreated()
            ->assertJson([
                'route' => 'normal',
                'booking_id' => $existingChild->booking_id,
            ]);

        $booking = Booking::query()->with('children')->findOrFail($existingChild->booking_id);

        $this->assertSame(1, Booking::query()->count());
        $this->assertSame(['Youssef', 'Jana'], $booking->children->pluck('child_name')->all());
    }

    public function test_existing_family_new_child_prefers_most_recent_active_booking_container_when_multiple_match(): void
    {
        $olderActive = $this->createExistingBookingChild([
            'updated_at' => now()->subDays(4),
        ]);

        $latestActive = $this->createExistingBookingChild([
            'booking_reference' => 'BK-INTAKE-1002',
            'updated_at' => now(),
        ], [
            'child_name' => 'Jana',
        ]);

        $this->createExistingBookingChild([
            'booking_reference' => 'BK-INTAKE-1003',
            'updated_at' => now()->addMinute(),
        ], [
            'child_name' => 'Mina',
            'transfer_status' => 'transferred',
        ]);

        $response = $this->postJson(route('admin.bookings.intake.store'), [
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'children' => [[
                'child_name' => 'Omar',
                'child_age' => '7',
                'child_grade' => '2',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $response->assertCreated()
            ->assertJson([
                'route' => 'normal',
                'booking_id' => $latestActive->booking_id,
            ]);

        $this->assertSame(
            ['Jana', 'Omar'],
            Booking::query()->with('children')->findOrFail($latestActive->booking_id)->children->pluck('child_name')->all()
        );
        $this->assertSame(['Youssef'], Booking::query()->with('children')->findOrFail($olderActive->booking_id)->children->pluck('child_name')->all());
    }

    public function test_existing_family_new_child_creates_new_container_when_only_historical_family_booking_exists(): void
    {
        $historicalOnly = $this->createExistingBookingChild([
            'parent_id' => 77,
            'updated_at' => now()->subDay(),
        ], [
            'transfer_status' => 'transferred',
        ]);

        $response = $this->postJson(route('admin.bookings.intake.store'), [
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'children' => [[
                'child_name' => 'Omar',
                'child_age' => '7',
                'child_grade' => '2',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $response->assertCreated()
            ->assertJsonPath('route', 'normal');

        $this->assertSame(2, Booking::query()->count());

        $newBooking = Booking::query()->latest('id')->firstOrFail();

        $this->assertNotSame($historicalOnly->booking_id, $newBooking->id);
        $this->assertSame(77, $newBooking->parent_id);
        $this->assertSame(['Omar'], $newBooking->children->pluck('child_name')->all());
        $this->assertSame(['Youssef'], Booking::query()->with('children')->findOrFail($historicalOnly->booking_id)->children->pluck('child_name')->all());
    }
}
