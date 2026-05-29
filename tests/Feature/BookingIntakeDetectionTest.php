<?php

namespace Tests\Feature;

use App\Models\BookingIntakeReview;
use App\Models\BookingParentBlock;
use App\Services\BookingIntakeDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\InteractsWithBookingIntakeTables;
use Tests\TestCase;

class BookingIntakeDetectionTest extends TestCase
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

    public function test_duplicate_child_routes_to_review_queue(): void
    {
        $existingChild = $this->createExistingBookingChild();

        $analysis = $this->service->analyze([
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

        $this->assertSame('review', $analysis['route']);
        $this->assertSame('duplicate_child', $analysis['reason']);
        $this->assertSame($existingChild->booking_id, $analysis['matched_booking_id']);
        $this->assertSame($existingChild->id, $analysis['matched_child_id']);
    }

    public function test_repeat_submission_routes_to_review_queue_for_historical_child(): void
    {
        $existingChild = $this->createExistingBookingChild([], [
            'transfer_status' => 'transferred',
            'meeting_disposition' => 'completed',
            'evaluation_outcome' => 'fit',
        ]);

        $analysis = $this->service->analyze([
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

        $this->assertSame('review', $analysis['route']);
        $this->assertSame('repeat_submission', $analysis['reason']);
        $this->assertSame($existingChild->id, $analysis['matched_child_id']);
    }

    public function test_duplicate_detection_prefers_active_child_over_newer_historical_match(): void
    {
        $historicalChild = $this->createExistingBookingChild([
            'booking_reference' => 'BK-INTAKE-HISTORICAL',
        ], [
            'transfer_status' => 'transferred',
            'meeting_disposition' => 'completed',
            'updated_at' => now()->addMinute(),
        ]);

        $activeChild = $this->createExistingBookingChild([
            'booking_reference' => 'BK-INTAKE-ACTIVE',
        ], [
            'updated_at' => now()->subDay(),
        ]);

        $analysis = $this->service->analyze([
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

        $this->assertSame('review', $analysis['route']);
        $this->assertSame('duplicate_child', $analysis['reason']);
        $this->assertSame($activeChild->id, $analysis['matched_child_id']);
        $this->assertNotSame($historicalChild->id, $analysis['matched_child_id']);
    }

    public function test_egyptian_phone_formats_match_duplicate_child_identity(): void
    {
        $existingChild = $this->createExistingBookingChild([
            'parent_phone' => '01031141431',
        ]);

        $analysis = $this->service->analyze([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '+201031141431',
            'children' => [[
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $this->assertSame('review', $analysis['route']);
        $this->assertSame('duplicate_child', $analysis['reason']);
        $this->assertSame($existingChild->id, $analysis['matched_child_id']);
    }

    public function test_blocked_parent_routes_to_review_queue(): void
    {
        BookingParentBlock::query()->create([
            'normalized_email' => 'blocked@example.test',
            'normalized_phone' => '201000111999',
            'block_reason' => 'Manual block',
        ]);

        $analysis = $this->service->analyze([
            'parent_name' => 'Blocked Parent',
            'parent_email' => 'blocked@example.test',
            'parent_phone' => '+20 100 011 1999',
            'children' => [[
                'child_name' => 'Blocked Child',
                'child_age' => '10',
                'child_grade' => '5',
                'school_system' => 'American',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $this->assertSame('review', $analysis['route']);
        $this->assertSame('blocked_parent', $analysis['reason']);
        $this->assertSame('blocked_parent', $analysis['child_reviews'][0]['review_reason']);
        $this->assertArrayNotHasKey('type', $analysis['child_reviews'][0]);
    }

    public function test_blocked_parent_lookup_canonicalizes_stored_phone_values(): void
    {
        BookingParentBlock::query()->create([
            'normalized_email' => null,
            'normalized_phone' => '01031141431',
            'block_reason' => 'Manual block',
        ]);

        $analysis = $this->service->analyze([
            'parent_name' => 'Blocked Parent',
            'parent_email' => 'different@example.test',
            'parent_phone' => '00201031141431',
            'children' => [[
                'child_name' => 'Blocked Child',
                'child_age' => '10',
                'child_grade' => '5',
                'school_system' => 'American',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $this->assertSame('review', $analysis['route']);
        $this->assertSame('blocked_parent', $analysis['reason']);
    }

    public function test_existing_family_new_child_routes_to_normal_queue(): void
    {
        $this->createExistingBookingChild();

        $analysis = $this->service->analyze([
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

        $this->assertSame('normal', $analysis['route']);
        $this->assertNull($analysis['reason']);
        $this->assertSame('existing_family_new_child', $analysis['child_reviews'][0]['review_reason']);
    }

    public function test_clean_new_customer_routes_to_normal_queue(): void
    {
        $analysis = $this->service->analyze([
            'parent_name' => 'Amina Salem',
            'parent_email' => 'amina@example.test',
            'parent_phone' => '201000222333',
            'children' => [[
                'child_name' => 'Sara',
                'child_age' => '9',
                'child_grade' => '4',
                'school_system' => 'IB',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $this->assertSame('normal', $analysis['route']);
        $this->assertNull($analysis['reason']);
        $this->assertSame('clean_new_customer', $analysis['child_reviews'][0]['review_reason']);
    }

    public function test_multi_child_review_record_preserves_full_children_payload(): void
    {
        $existingChild = $this->createExistingBookingChild();

        $analysis = $this->service->analyze([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'children' => [
                [
                    'child_name' => 'Youssef',
                    'child_age' => '11',
                    'child_grade' => '6',
                    'school_system' => 'British',
                    'service_interests' => ['Help Me Study'],
                ],
                [
                    'child_name' => 'Jana',
                    'child_age' => '8',
                    'child_grade' => '3',
                    'school_system' => 'British',
                    'service_interests' => ['Help Me Study'],
                ],
            ],
        ]);

        $review = $this->service->writeReviewRecord([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'children' => [
                [
                    'child_name' => 'Youssef',
                    'child_age' => '11',
                    'child_grade' => '6',
                    'school_system' => 'British',
                    'service_interests' => ['Help Me Study'],
                ],
                [
                    'child_name' => 'Jana',
                    'child_age' => '8',
                    'child_grade' => '3',
                    'school_system' => 'British',
                    'service_interests' => ['Help Me Study'],
                ],
            ],
        ], $analysis);

        $this->assertSame('review', $analysis['route']);
        $this->assertSame('mixed_children', $analysis['reason']);
        $this->assertCount(2, $review->children_payload);
        $this->assertArrayNotHasKey('type', $review->children_payload[0]);
        $this->assertArrayNotHasKey('type', $review->children_payload[1]);
        $this->assertCount(2, $review->reviewChildren);
        $this->assertSame($existingChild->id, $review->reviewChildren->first()->matched_child_id);
    }

    public function test_repeat_flagged_submission_reuses_existing_pending_review_record(): void
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
                'service_interests' => ['Help Me Study'],
            ]],
        ];

        $firstReview = $this->service->writeReviewRecord($payload, $this->service->analyze($payload));
        $secondReview = $this->service->writeReviewRecord($payload, $this->service->analyze($payload));

        $this->assertSame($firstReview->id, $secondReview->id);
        $this->assertSame(1, BookingIntakeReview::query()->count());
    }

    public function test_repeat_flagged_submission_refreshes_existing_review_snapshot(): void
    {
        $this->createExistingBookingChild();

        $firstPayload = [
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
        ];

        $review = $this->service->writeReviewRecord($firstPayload, $this->service->analyze($firstPayload));

        $updatedPayload = $firstPayload;
        $updatedPayload['children'][0]['child_age'] = '12';
        $updatedPayload['children'][0]['service_interests'] = ['IB Private Classes'];

        $refreshedReview = $this->service->writeReviewRecord($updatedPayload, $this->service->analyze($updatedPayload));

        $this->assertSame($review->id, $refreshedReview->id);
        $this->assertSame('12', $refreshedReview->child_age);
        $this->assertSame(['Quran Memorization'], $refreshedReview->children_payload[0]['service_interests']);
    }

    public function test_repeat_flagged_submission_keeps_children_payload_type_free(): void
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
                'service_interests' => ['Help Me Study'],
            ]],
        ];

        $review = $this->service->writeReviewRecord($payload, $this->service->analyze($payload));

        $refreshedReview = $this->service->writeReviewRecord($payload, $this->service->analyze($payload));

        $this->assertSame($review->id, $refreshedReview->id);
        $this->assertArrayNotHasKey('type', $refreshedReview->children_payload[0]);
    }

    public function test_repeat_flagged_submission_does_not_refresh_after_operator_decision_started(): void
    {
        $this->createExistingBookingChild();

        $firstPayload = [
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
        ];

        $review = $this->service->writeReviewRecord($firstPayload, $this->service->analyze($firstPayload));
        $reviewChild = $review->reviewChildren->first();
        $reviewChild->update([
            'resolution_status' => 'promote_child',
            'resolution_note' => 'Operator has started review.',
        ]);

        $updatedPayload = $firstPayload;
        $updatedPayload['children'][0]['child_age'] = '12';
        $updatedPayload['children'][0]['service_interests'] = ['IB Private Classes'];

        $preservedReview = $this->service->writeReviewRecord($updatedPayload, $this->service->analyze($updatedPayload));

        $this->assertSame($review->id, $preservedReview->id);
        $this->assertSame('11', $preservedReview->child_age);
        $this->assertSame(['My Deen Journey'], $preservedReview->children_payload[0]['service_interests']);
        $this->assertSame($reviewChild->id, $preservedReview->reviewChildren->first()->id);
        $this->assertSame('promote_child', $preservedReview->reviewChildren->first()->resolution_status);
        $this->assertSame('Operator has started review.', $preservedReview->reviewChildren->first()->resolution_note);
    }

    public function test_concurrent_identical_flagged_submissions_converge_on_one_pending_review_record(): void
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
                'service_interests' => ['Help Me Study'],
            ]],
        ];

        $fingerprint = $this->service->submissionFingerprint($payload);
        BookingIntakeReview::query()->create([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'child_name' => 'Youssef',
            'child_age' => '11',
            'child_grade' => '6',
            'school_system' => 'British',
            'service_interests' => ['Help Me Study'],
            'children_payload' => $payload['children'],
            'child_count' => 1,
            'open_submission_fingerprint' => $fingerprint,
            'notes' => null,
            'detection_reason' => 'duplicate_child',
            'detection_detail' => 'Existing pending review.',
            'matched_booking_id' => null,
            'matched_child_id' => null,
            'status' => 'pending_review',
        ]);

        $review = $this->service->writeReviewRecord($payload, $this->service->analyze($payload));

        $this->assertSame(1, BookingIntakeReview::query()->where('open_submission_fingerprint', $fingerprint)->count());
        $this->assertSame($fingerprint, $review->open_submission_fingerprint);
    }

    public function test_resolved_review_with_stale_open_fingerprint_does_not_block_new_review_record(): void
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
                'service_interests' => ['Help Me Study'],
            ]],
        ];
        $fingerprint = $this->service->submissionFingerprint($payload);
        $staleReview = BookingIntakeReview::query()->create([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'child_name' => 'Youssef',
            'child_age' => '11',
            'child_grade' => '6',
            'school_system' => 'British',
            'service_interests' => ['Help Me Study'],
            'children_payload' => $payload['children'],
            'child_count' => 1,
            'open_submission_fingerprint' => $fingerprint,
            'notes' => null,
            'detection_reason' => 'duplicate_child',
            'detection_detail' => 'Resolved row should no longer own the open fingerprint.',
            'matched_booking_id' => null,
            'matched_child_id' => null,
            'status' => 'dismissed',
        ]);

        $newReview = $this->service->writeReviewRecord($payload, $this->service->analyze($payload));

        $this->assertNotSame($staleReview->id, $newReview->id);
        $this->assertNull($staleReview->fresh()->open_submission_fingerprint);
        $this->assertSame('pending_review', $newReview->status);
        $this->assertSame($fingerprint, $newReview->open_submission_fingerprint);
        $this->assertSame(1, BookingIntakeReview::query()->where('open_submission_fingerprint', $fingerprint)->count());
    }

    public function test_submission_fingerprint_uses_canonical_egyptian_phone(): void
    {
        $payload = [
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '+201031141431',
            'children' => [[
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
            ]],
        ];

        $localPayload = $payload;
        $localPayload['parent_phone'] = '01031141431';

        $countryCodePayload = $payload;
        $countryCodePayload['parent_phone'] = '201031141431';

        $this->assertSame(
            $this->service->submissionFingerprint($localPayload),
            $this->service->submissionFingerprint($payload)
        );
        $this->assertSame(
            $this->service->submissionFingerprint($countryCodePayload),
            $this->service->submissionFingerprint($payload)
        );
    }

    public function test_same_email_with_different_non_empty_phone_routes_to_contact_mismatch_review(): void
    {
        $existingChild = $this->createExistingBookingChild([], [
            'child_name' => 'Youssef',
        ]);

        $analysis = $this->service->analyze([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111999',
            'children' => [[
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $this->assertSame('review', $analysis['route']);
        $this->assertSame('suspected_contact_mismatch', $analysis['reason']);
        $this->assertSame($existingChild->booking_id, $analysis['matched_booking_id']);
        $this->assertNull($analysis['matched_child_id']);
        $this->assertSame('suspected_contact_mismatch', $analysis['child_reviews'][0]['review_reason']);
        $this->assertSame($existingChild->booking_id, $analysis['child_reviews'][0]['matched_booking_id']);
        $this->assertNull($analysis['child_reviews'][0]['matched_child_id']);
        $this->assertSame(
            "Contact mismatch: submitted email matches existing booking #{$existingChild->booking_id} but phone differs. Admin verification required before child intake proceeds.",
            $analysis['detail']
        );
    }

    public function test_same_phone_with_different_non_empty_email_routes_to_contact_mismatch_review(): void
    {
        $existingChild = $this->createExistingBookingChild();

        $analysis = $this->service->analyze([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'different-parent@example.test',
            'parent_phone' => '201000111222',
            'children' => [[
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $this->assertSame('review', $analysis['route']);
        $this->assertSame('suspected_contact_mismatch', $analysis['reason']);
        $this->assertSame($existingChild->booking_id, $analysis['matched_booking_id']);
        $this->assertNull($analysis['matched_child_id']);
        $this->assertSame('suspected_contact_mismatch', $analysis['child_reviews'][0]['review_reason']);
        $this->assertSame(
            "Contact mismatch: submitted phone matches existing booking #{$existingChild->booking_id} but email differs. Admin verification required before child intake proceeds.",
            $analysis['child_reviews'][0]['review_detail']
        );
    }

    public function test_contact_mismatch_review_applies_to_every_submitted_child_without_child_match(): void
    {
        $existingChild = $this->createExistingBookingChild();

        $analysis = $this->service->analyze([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111999',
            'children' => [
                [
                    'child_name' => 'Youssef',
                    'child_age' => '11',
                    'child_grade' => '6',
                    'school_system' => 'British',
                    'service_interests' => ['Help Me Study'],
                ],
                [
                    'child_name' => 'Jana',
                    'child_age' => '8',
                    'child_grade' => '3',
                    'school_system' => 'British',
                    'service_interests' => ['Help Me Study'],
                ],
            ],
        ]);

        $this->assertSame('review', $analysis['route']);
        $this->assertSame('suspected_contact_mismatch', $analysis['reason']);
        $this->assertSame(
            ['suspected_contact_mismatch', 'suspected_contact_mismatch'],
            collect($analysis['child_reviews'])->pluck('review_reason')->all()
        );
        $this->assertSame(
            [$existingChild->booking_id, $existingChild->booking_id],
            collect($analysis['child_reviews'])->pluck('matched_booking_id')->all()
        );
        $this->assertSame(
            [null, null],
            collect($analysis['child_reviews'])->pluck('matched_child_id')->all()
        );
    }

    public function test_existing_family_detection_falls_back_to_single_shared_identifier_only_when_the_other_is_absent(): void
    {
        $this->createExistingBookingChild([
            'parent_phone' => null,
        ]);

        $analysis = $this->service->analyze([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000222333',
            'children' => [[
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
            ]],
        ]);

        $this->assertSame('normal', $analysis['route']);
        $this->assertSame('existing_family_new_child', $analysis['child_reviews'][0]['review_reason']);
    }
}
