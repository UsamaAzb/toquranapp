<?php

namespace Tests\Unit;

use App\Models\BookingChild;
use App\Services\BookingIntakeDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithBookingIntakeTables;
use Tests\TestCase;

class BookingIntakeDetectionServiceTest extends TestCase
{
    use InteractsWithBookingIntakeTables;
    use RefreshDatabase;

    protected BookingIntakeDetectionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createBookingIntakeTables();
        $this->service = app(BookingIntakeDetectionService::class);
    }

    public function test_submission_fingerprint_is_stable_across_child_order_and_phone_formatting(): void
    {
        $first = $this->service->submissionFingerprint([
            'parent_email' => 'Mariam@example.test',
            'parent_phone' => '+20 100 011 1222',
            'children' => [
                ['child_name' => 'Jana'],
                ['child_name' => 'Youssef'],
            ],
        ]);

        $second = $this->service->submissionFingerprint([
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'children' => [
                ['child_name' => 'Youssef'],
                ['child_name' => 'Jana'],
            ],
        ]);

        $this->assertSame($first, $second);
    }

    public function test_write_review_record_rejects_non_review_routes(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->writeReviewRecord([
            'parent_email' => 'amina@example.test',
            'parent_phone' => '201000222333',
            'children' => [['child_name' => 'Sara']],
        ], [
            'route' => 'normal',
            'reason' => null,
            'detail' => null,
            'matched_booking_id' => null,
            'matched_child_id' => null,
            'child_reviews' => [],
        ]);
    }

    public function test_write_review_record_normalizes_service_values_in_child_payload(): void
    {
        $this->createExistingBookingChild();

        $payload = [
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'children' => [[
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['IB Private Classes'],
            ]],
        ];

        $review = $this->service->writeReviewRecord($payload, $this->service->analyze($payload));

        $this->assertSame(['Quran Memorization'], $review->children_payload[0]['service_interests']);
        $this->assertArrayNotHasKey('type', $review->children_payload[0]);
        $this->assertSame(['Quran Memorization'], $review->reviewChildren->first()->service_interests);
    }

    public function test_resolve_existing_family_booking_prefers_most_recent_active_family_container(): void
    {
        $olderActive = $this->createExistingBookingChild([
            'updated_at' => now()->subDays(5),
        ]);

        $latestActive = $this->createExistingBookingChild([
            'booking_reference' => 'BK-INTAKE-2002',
            'updated_at' => now(),
        ], [
            'child_name' => 'Jana',
        ]);

        $historicalOnly = $this->createExistingBookingChild([
            'booking_reference' => 'BK-INTAKE-3003',
            'updated_at' => now()->addMinute(),
        ], [
            'child_name' => 'Mina',
            'transfer_status' => 'transferred',
        ]);

        $resolved = $this->service->resolveExistingFamilyBooking('mariam@example.test', '201000111222');

        $this->assertNotNull($resolved);
        $this->assertSame($latestActive->booking_id, $resolved->id);
        $this->assertNotSame($historicalOnly->booking_id, $resolved->id);
        $this->assertNotSame($olderActive->booking_id, $resolved->id);
    }

    public function test_resolve_existing_family_booking_prefers_newer_active_container_even_if_older_has_more_children(): void
    {
        $olderPrimary = $this->createExistingBookingChild([
            'updated_at' => now()->subDays(5),
        ]);

        BookingChild::create([
            'booking_id' => $olderPrimary->booking_id,
            'child_name' => 'Second Older Child',
            'child_age' => '9',
            'child_grade' => 4,
            'school_system' => 'British',
            'service_interests' => ['Help Me Study'],
            'consultation_status' => 'pending',
            'workflow_status' => 'pending',
            'meeting_disposition' => null,
            'meeting_disposition_reason' => null,
            'evaluation_status' => null,
            'evaluation_outcome' => 'undecided',
            'consultation_type' => 'undecided',
            'meeting_link' => null,
            'meeting_address' => null,
            'transfer_status' => 'not_transferred',
            'followup_date' => null,
            'current_school' => null,
            'student_id' => null,
            'notes' => null,
            'scheduled_date' => null,
            'scheduled_time' => null,
            'sort_order' => 2,
            'updated_by' => null,
        ]);

        $newerActive = $this->createExistingBookingChild([
            'booking_reference' => 'BK-INTAKE-4004',
            'updated_at' => now(),
        ], [
            'child_name' => 'Newest Active Child',
        ]);

        $resolved = $this->service->resolveExistingFamilyBooking('mariam@example.test', '201000111222');

        $this->assertNotNull($resolved);
        $this->assertSame($newerActive->booking_id, $resolved->id);
        $this->assertNotSame($olderPrimary->booking_id, $resolved->id);
    }
}
