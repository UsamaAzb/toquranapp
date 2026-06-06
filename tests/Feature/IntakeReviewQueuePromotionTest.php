<?php

namespace Tests\Feature;

use App\Livewire\Admin\Booking\IntakeReviewQueue;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\Support\InteractsWithBookingIntakeTables;
use Tests\TestCase;

class IntakeReviewQueuePromotionTest extends TestCase
{
    use InteractsWithBookingIntakeTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'array']);

        $this->createBookingIntakeTables();
    }

    public function test_review_item_promotes_once_only(): void
    {
        $this->actingAs(User::factory()->create());

        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'clean_new_customer',
                'review_detail' => 'Ready for clean promotion.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Operator approved this child.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'Promote approved child into normal queue.')
            ->call('finalizePromotion', $review->id)
            ->assertHasNoErrors();

        $review->refresh();

        $this->assertSame('promoted_to_queue', $review->status);
        $this->assertNotNull($review->resulting_booking_id);

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'Second attempt should fail.')
            ->call('finalizePromotion', $review->id)
            ->assertHasErrors(["reviewActions.{$review->id}"]);
    }

    public function test_review_item_promotion_rolls_back_on_failure(): void
    {
        $this->actingAs(User::factory()->create());

        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'clean_new_customer',
                'review_detail' => 'Ready for clean promotion.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Approve child.',
            ],
        ]);

        Schema::drop('booking_children');

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'Attempting promotion.')
            ->call('finalizePromotion', $review->id)
            ->assertHasErrors(["reviewActions.{$review->id}"]);

        $this->assertSame(0, Booking::query()->count());
        $this->assertSame('pending_review', $review->fresh()->status);
    }

    public function test_review_item_promotion_creates_only_approved_child_rows(): void
    {
        $this->actingAs(User::factory()->create());

        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Duplicate Child',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Dismiss duplicate.',
                'matched_booking_id' => 99,
                'matched_child_id' => 11,
                'resolution_status' => 'dismiss_child',
                'resolution_note' => 'Duplicate child already exists.',
            ],
            [
                'child_index' => 1,
                'child_name' => 'Approved Child',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'clean_new_customer',
                'review_detail' => 'Promote this sibling.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Approved for queue.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'Promote only approved child rows.')
            ->call('finalizePromotion', $review->id)
            ->assertHasNoErrors();

        $booking = Booking::query()->firstOrFail();

        $this->assertSame(['Approved Child'], $booking->children()->pluck('child_name')->all());
    }

    public function test_mixed_submission_allows_duplicate_child_dismiss_and_new_sibling_promotion(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();

        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Duplicate Child',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Dismiss duplicate.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => $existingChild->id,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
            [
                'child_index' => 1,
                'child_name' => 'New Sibling',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'existing_family_new_child',
                'review_detail' => 'Promote sibling.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);

        $duplicateChild = $review->reviewChildren->firstWhere('child_name', 'Duplicate Child');
        $newSibling = $review->reviewChildren->firstWhere('child_name', 'New Sibling');

        Livewire::test(IntakeReviewQueue::class)
            ->set("childResolutionNotes.{$duplicateChild->id}", 'Duplicate child already exists.')
            ->call('setChildResolution', $duplicateChild->id, 'dismiss_child')
            ->call('setChildResolution', $newSibling->id, 'promote_child')
            ->set("submissionNotes.{$review->id}", 'Only the genuinely new sibling should enter the queue.')
            ->call('finalizePromotion', $review->id)
            ->assertHasNoErrors();

        $booking = Booking::query()->with('children')->findOrFail($existingChild->booking_id);

        $this->assertSame(['Youssef', 'New Sibling'], $booking->children->pluck('child_name')->all());
    }

    public function test_mixed_submission_promotion_handles_students_schema_without_current_school(): void
    {
        $this->actingAs(User::factory()->create());

        Schema::drop('students');
        Schema::create('students', function ($table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->unsignedBigInteger('grade_level_id')->nullable();
            $table->string('school_system')->nullable();
            $table->timestamps();
        });

        $parentId = DB::table('parents')->insertGetId([
            'first_name' => 'Salem',
            'last_name' => 'Parent',
            'email' => 'salem.parent@example.test',
            'phone' => '201000999888',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $existingChild = $this->createExistingBookingChild([
            'parent_name' => 'Salem',
            'parent_email' => 'salem.parent@example.test',
            'parent_phone' => '201000999888',
            'parent_id' => $parentId,
        ], [
            'child_name' => 'Dodo',
            'child_age' => '6',
            'child_grade' => 6,
            'school_system' => 'IB',
        ]);

        $review = $this->createReview([
            'parent_name' => 'Salem',
            'parent_email' => 'salem.parent@example.test',
            'parent_phone' => '201000999888',
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Dodo',
                'child_age' => '6',
                'child_grade' => '6',
                'school_system' => 'IB',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Dismiss duplicate.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => $existingChild->id,
                'resolution_status' => 'dismiss_child',
                'resolution_note' => 'Duplicate child already exists.',
            ],
            [
                'child_index' => 1,
                'child_name' => 'Omar',
                'child_age' => '11',
                'child_grade' => '11',
                'school_system' => 'IB',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'existing_family_new_child',
                'review_detail' => 'Promote sibling.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Same family, new child.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'Promote only the new sibling.')
            ->call('finalizePromotion', $review->id)
            ->assertHasNoErrors();

        $booking = Booking::query()->with('children')->findOrFail($existingChild->booking_id);

        $this->assertSame(['Dodo', 'Omar'], $booking->children->pluck('child_name')->all());
    }

    public function test_child_dismiss_requires_a_child_note(): void
    {
        $this->actingAs(User::factory()->create());

        $review = $this->createReview();
        $reviewChild = $review->reviewChildren->first();

        Livewire::test(IntakeReviewQueue::class)
            ->call('setChildResolution', $reviewChild->id, 'dismiss_child')
            ->assertHasErrors(["childResolutionNotes.{$reviewChild->id}"])
            ->assertSee('Enter a child note before marking this child dismissed.');
    }

    public function test_duplicate_like_child_cannot_be_promoted_as_is(): void
    {
        $this->actingAs(User::factory()->create());

        $review = $this->createReview();
        $reviewChild = $review->reviewChildren->first();

        Livewire::test(IntakeReviewQueue::class)
            ->call('setChildResolution', $reviewChild->id, 'promote_child')
            ->assertHasErrors(["reviewActions.{$review->id}"])
            ->assertSee('This child cannot be approved as-is.');

        $this->assertSame('pending_decision', $reviewChild->fresh()->resolution_status);
    }

    public function test_duplicate_child_correction_modal_can_turn_row_into_approved_sibling(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Matches an existing child.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => $existingChild->id,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);
        $reviewChild = $review->reviewChildren->first();

        Livewire::test(IntakeReviewQueue::class)
            ->call('openCorrectionModal', $reviewChild->id)
            ->assertSet('correctionReviewChildId', $reviewChild->id)
            ->set('correctionForm.child_name', 'Jana')
            ->set('correctionForm.child_age', '8')
            ->set('correctionForm.child_grade', '3')
            ->set('correctionForm.school_system', '')
            ->call('saveCorrection', true)
            ->assertHasNoErrors()
            ->assertSee('Correction saved and child approved for promotion.');

        $reviewChild->refresh();
        $review->refresh();

        $this->assertSame('Jana', $reviewChild->child_name);
        $this->assertSame('existing_family_new_child', $reviewChild->review_reason);
        $this->assertSame($existingChild->booking_id, $reviewChild->matched_booking_id);
        $this->assertNull($reviewChild->matched_child_id);
        $this->assertSame('promote_child', $reviewChild->resolution_status);
        $this->assertSame('Other', $reviewChild->school_system);
        $this->assertSame('existing_family_new_child', $review->detection_reason);
        $this->assertSame('Jana', $review->child_name);
        $this->assertSame('Jana', $review->children_payload[0]['child_name']);
        $this->assertSame('Other', $review->children_payload[0]['school_system']);
    }

    public function test_duplicate_child_correction_stays_blocked_when_data_still_matches_existing_child(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        Booking::query()->findOrFail($existingChild->booking_id)
            ->children()
            ->create([
                'child_name' => 'Dodo',
                'child_age' => '6',
                'child_grade' => 1,
                'school_system' => 'IB',
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
        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Matches an existing child.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => $existingChild->id,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);
        $reviewChild = $review->reviewChildren->first();

        Livewire::test(IntakeReviewQueue::class)
            ->call('openCorrectionModal', $reviewChild->id)
            ->set('correctionForm.child_name', 'Dodo')
            ->call('saveCorrection', true)
            ->assertHasErrors(['correctionForm.child_name'])
            ->assertSee('Duplicate child: exact parent identity + child name matches an active consultation')
            ->assertSet('correctionForm.child_name', 'Youssef');

        $reviewChild->refresh();
        $review->refresh();

        $this->assertSame('Youssef', $reviewChild->child_name);
        $this->assertSame('duplicate_child', $reviewChild->review_reason);
        $this->assertSame('pending_decision', $reviewChild->resolution_status);
        $this->assertSame('Youssef', $review->child_name);
        $this->assertSame('Youssef', $review->children_payload[0]['child_name']);
    }

    public function test_duplicate_child_correction_can_become_verified_contact_update_candidate(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Matches an existing child.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => $existingChild->id,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);
        $reviewChild = $review->reviewChildren->first();

        Livewire::test(IntakeReviewQueue::class)
            ->call('openContactCorrectionModal', $review->id)
            ->set('contactCorrectionForm.parent_phone', '201555666777')
            ->call('saveContactCorrection')
            ->assertHasNoErrors()
            ->assertSee('Contact Action', false)
            ->assertDontSee('Finalize Approved Children');

        $reviewChild->refresh();
        $review->refresh();

        $this->assertSame('suspected_contact_mismatch', $reviewChild->review_reason);
        $this->assertSame($existingChild->booking_id, $reviewChild->matched_booking_id);
        $this->assertNull($reviewChild->matched_child_id);
        $this->assertSame('pending_decision', $reviewChild->resolution_status);
        $this->assertSame('suspected_contact_mismatch', $review->detection_reason);
        $this->assertSame('201555666777', $review->parent_phone);
        $this->assertSame('Youssef', $review->child_name);
    }

    public function test_save_correction_true_does_not_force_approve_a_verified_contact_update_candidate(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $review = $this->createReview([
            'parent_phone' => '201555666777',
            'detection_reason' => 'duplicate_child',
            'detection_detail' => 'Needs correction before review.',
            'matched_booking_id' => $existingChild->booking_id,
            'matched_child_id' => $existingChild->id,
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Matches an existing child.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => $existingChild->id,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);
        $reviewChild = $review->reviewChildren->first();

        Livewire::test(IntakeReviewQueue::class)
            ->call('openCorrectionModal', $reviewChild->id)
            ->set('correctionForm.child_name', 'Jana')
            ->set('correctionForm.child_age', '8')
            ->set('correctionForm.child_grade', '3')
            ->call('saveCorrection', true)
            ->assertHasNoErrors()
            ->assertSee('Correction saved. Contact verification is still required before promotion.')
            ->assertSee('Contact Action', false)
            ->assertDontSee('Finalize Approved Children');

        $reviewChild->refresh();
        $review->refresh();

        $this->assertSame('Jana', $reviewChild->child_name);
        $this->assertSame('suspected_contact_mismatch', $reviewChild->review_reason);
        $this->assertSame($existingChild->booking_id, $reviewChild->matched_booking_id);
        $this->assertNull($reviewChild->matched_child_id);
        $this->assertSame('pending_decision', $reviewChild->resolution_status);
        $this->assertSame('suspected_contact_mismatch', $review->detection_reason);
    }

    public function test_parent_contact_correction_child_collision_uses_modal_level_error(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $review = $this->createReview([
            'parent_email' => 'new-family@example.test',
            'parent_phone' => '201777888999',
            'detection_reason' => 'clean_new_customer',
            'matched_booking_id' => null,
            'matched_child_id' => null,
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'clean_new_customer',
                'review_detail' => 'No match.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->call('openContactCorrectionModal', $review->id)
            ->set('contactCorrectionForm.parent_email', $existingChild->booking->parent_email)
            ->set('contactCorrectionForm.parent_phone', $existingChild->booking->parent_phone)
            ->call('saveContactCorrection')
            ->assertHasErrors(['contactCorrectionForm.general'])
            ->assertHasNoErrors(['contactCorrectionForm.parent_phone'])
            ->assertSee('Duplicate child');

        $this->assertSame('pending_review', $review->fresh()->status);
    }

    public function test_verified_contact_update_blocks_when_corrected_phone_belongs_to_another_family(): void
    {
        $this->actingAs(User::factory()->create());

        $targetChild = $this->createExistingBookingChild();
        $otherChild = $this->createExistingBookingChild([
            'parent_name' => 'Other Family',
            'parent_email' => 'other@example.test',
            'parent_phone' => '201555666777',
            'booking_reference' => 'BK-OTHER-1001',
        ], [
            'child_name' => 'Other Child',
        ]);

        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Matches an existing child.',
                'matched_booking_id' => $targetChild->booking_id,
                'matched_child_id' => $targetChild->id,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);
        $reviewChild = $review->reviewChildren->first();

        Livewire::test(IntakeReviewQueue::class)
            ->call('openContactCorrectionModal', $review->id)
            ->set('contactCorrectionForm.parent_phone', $otherChild->booking->parent_phone)
            ->call('saveContactCorrection')
            ->assertSee('Contact Mismatch')
            ->assertSee('Split Contact')
            ->assertSee("Booking #{$targetChild->booking_id}")
            ->assertSee("Booking #{$otherChild->booking_id}")
            ->assertSee(route('admin.bookings.livewire', ['search' => 'BK-INTAKE-1001']), false)
            ->assertSee(route('admin.bookings.livewire', ['search' => 'BK-OTHER-1001']), false)
            ->assertSee('Replace saved contact & promote', false)
            ->assertSeeHtml('dropdown-item disabled" disabled aria-disabled="true" title="Email and phone match different booking families."')
            ->call('setChildResolution', $reviewChild->id, 'promote_child')
            ->assertSee('Replace saved contact & promote', false)
            ->assertSeeHtml('dropdown-item disabled" disabled aria-disabled="true" title="Email and phone match different booking families."')
            ->set("submissionNotes.{$review->id}", 'Verified new phone by mistake.')
            ->call('finalizeVerifiedContactUpdate', $review->id)
            ->assertHasErrors(["reviewActions.{$review->id}"])
            ->assertSee('different booking families');

        $this->assertSame('pending_review', $review->fresh()->status);
        $this->assertSame('201000111222', $targetChild->booking->fresh()->parent_phone);
    }

    public function test_mixed_review_with_mismatch_child_shows_verified_update_footer_action(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $review = $this->createReview([
            'parent_phone' => '201555666777',
            'detection_reason' => 'mixed_children',
            'detection_detail' => 'Submission contains mixed child outcomes.',
            'matched_booking_id' => $existingChild->booking_id,
            'matched_child_id' => null,
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => 'Contact changed and requires verification.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => null,
            ],
            [
                'child_index' => 1,
                'child_name' => 'Omar',
                'child_age' => '7',
                'child_grade' => '2',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'existing_family_new_child',
                'review_detail' => 'Sibling row is already clean.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => null,
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->assertSee('Contact Action', false)
            ->assertSee('Replace saved contact & promote', false)
            ->assertSee('Add phone number', false)
            ->assertSeeHtml('disabled aria-disabled="true" title="Parent account phone slots are not wired yet"')
            ->assertDontSee('Finalize Approved Children');

        $this->assertSame('mixed_children', $review->fresh()->detection_reason);
    }

    public function test_dismissed_mismatch_row_no_longer_forces_contact_action_footer(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $review = $this->createReview([
            'parent_phone' => '201555666777',
            'detection_reason' => 'mixed_children',
            'detection_detail' => 'Submission contains mixed child outcomes.',
            'matched_booking_id' => $existingChild->booking_id,
            'matched_child_id' => null,
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Mismatch Child',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => 'Contact changed and requires verification.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'dismiss_child',
                'resolution_note' => 'Dismiss the mismatch row.',
            ],
            [
                'child_index' => 1,
                'child_name' => 'Clean Child',
                'child_age' => '7',
                'child_grade' => '2',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'clean_new_customer',
                'review_detail' => 'This child is ready for normal promotion.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Approve the clean row.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->assertSee('Finalize Approved Children')
            ->assertDontSee('Contact Action', false);

        $this->assertSame('mixed_children', $review->fresh()->detection_reason);
    }

    public function test_contact_action_is_disabled_when_mismatch_review_has_no_approved_children(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $review = $this->createReview([
            'parent_phone' => '201555666777',
            'detection_reason' => 'suspected_contact_mismatch',
            'matched_booking_id' => $existingChild->booking_id,
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => 'Contact changed and requires verification.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'dismiss_child',
                'resolution_note' => 'Not needed.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->assertDontSee('Contact Action', false)
            ->assertSee('Finalize Approved Children')
            ->assertSeeHtml('btn btn-primary disabled" disabled aria-disabled="true" title="Approve at least one child before finalizing this submission."')
            ->set("submissionNotes.{$review->id}", 'Trying with no approved children.')
            ->call('finalizePromotion', $review->id)
            ->assertHasErrors(["reviewActions.{$review->id}"])
            ->assertSee('At least one child must be approved before a booking can be created.');
    }

    public function test_final_submission_buttons_are_disabled_until_all_child_rows_are_decided(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $review = $this->createReview([
            'parent_phone' => '201555666777',
            'detection_reason' => 'suspected_contact_mismatch',
            'matched_booking_id' => $existingChild->booking_id,
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => 'Contact changed and requires verification.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->assertSee('Contact Action', false)
            ->assertSee('Dismiss Entire Submission')
            ->assertSeeHtml('btn btn-label-danger disabled" disabled aria-disabled="true" title="Decide every child row before using the final submission actions."')
            ->assertSeeHtml('btn btn-primary dropdown-toggle"')
            ->assertSeeHtml('disabled aria-disabled="true" title="Decide every child row before using the final submission actions."');

        $this->assertSame('pending_review', $review->fresh()->status);
    }

    public function test_parent_contact_correction_rechecks_all_active_children_and_resets_changed_approvals(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $review = $this->createReview([
            'parent_email' => 'new-family@example.test',
            'parent_phone' => '201777888999',
            'detection_reason' => 'mixed_children',
            'detection_detail' => 'Admin-created two-child review.',
            'matched_booking_id' => null,
            'matched_child_id' => null,
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'clean_new_customer',
                'review_detail' => 'No match.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Approved before contact correction.',
            ],
            [
                'child_index' => 1,
                'child_name' => 'Omar',
                'child_age' => '7',
                'child_grade' => '2',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'clean_new_customer',
                'review_detail' => 'No match.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Approved before contact correction.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->call('openContactCorrectionModal', $review->id)
            ->set('contactCorrectionForm.parent_email', $existingChild->booking->parent_email)
            ->set('contactCorrectionForm.parent_phone', $existingChild->booking->parent_phone)
            ->call('saveContactCorrection')
            ->assertHasNoErrors()
            ->assertSee('Parent contact correction saved and review rows re-checked.');

        $review->refresh();
        $children = $review->reviewChildren()->orderBy('child_index')->get();

        $this->assertSame('existing_family_new_child', $review->detection_reason);
        $this->assertSame($existingChild->booking_id, $review->matched_booking_id);
        $this->assertSame(
            ['existing_family_new_child', 'existing_family_new_child'],
            $children->pluck('review_reason')->all()
        );
        $this->assertSame(
            [$existingChild->booking_id, $existingChild->booking_id],
            $children->pluck('matched_booking_id')->all()
        );
        $this->assertSame(
            ['pending_decision', 'pending_decision'],
            $children->pluck('resolution_status')->all()
        );
    }

    public function test_child_resolution_state_shows_saved_note_and_reset_copy(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Duplicate Child',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Dismiss duplicate.',
                'matched_booking_id' => 99,
                'matched_child_id' => 11,
                'resolution_status' => 'dismiss_child',
                'resolution_note' => 'Duplicate child already exists.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->assertSee('Dismissed')
            ->assertSee('Saved child note:')
            ->assertSee('Duplicate child already exists.')
            ->assertSee('Reset');
    }

    public function test_existing_family_review_promotion_appends_child_to_existing_booking_container(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $existingBookingId = $existingChild->booking_id;

        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'existing_family_new_child',
                'review_detail' => 'Append this sibling to the existing family booking.',
                'matched_booking_id' => $existingBookingId,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Approved for the existing family container.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'Promote this sibling into the existing family container.')
            ->call('finalizePromotion', $review->id)
            ->assertHasNoErrors();

        $review->refresh();
        $booking = Booking::query()->with('children')->findOrFail($existingBookingId);

        $this->assertSame(1, Booking::query()->count());
        $this->assertSame($existingBookingId, $review->resulting_booking_id);
        $this->assertSame(['Youssef', 'Jana'], $booking->children->pluck('child_name')->all());
    }

    public function test_existing_family_review_promotion_re_resolves_canonical_family_container_from_parent_identity(): void
    {
        $this->actingAs(User::factory()->create());

        $olderActive = $this->createExistingBookingChild([
            'updated_at' => now()->subDays(5),
        ]);

        $latestActive = $this->createExistingBookingChild([
            'booking_reference' => 'BK-INTAKE-2002',
            'updated_at' => now(),
        ], [
            'child_name' => 'Jana',
        ]);

        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Omar',
                'child_age' => '7',
                'child_grade' => '2',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'existing_family_new_child',
                'review_detail' => 'Append to the current family container.',
                'matched_booking_id' => $olderActive->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Approved for promotion.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'Promote into the canonical existing-family container.')
            ->call('finalizePromotion', $review->id)
            ->assertHasNoErrors();

        $review->refresh();

        $this->assertSame($latestActive->booking_id, $review->resulting_booking_id);
        $this->assertSame(
            ['Jana', 'Omar'],
            Booking::query()->with('children')->findOrFail($latestActive->booking_id)->children->pluck('child_name')->all()
        );
        $this->assertSame(
            ['Youssef'],
            Booking::query()->with('children')->findOrFail($olderActive->booking_id)->children->pluck('child_name')->all()
        );
    }

    public function test_existing_family_review_promotion_creates_new_container_when_only_historical_booking_exists(): void
    {
        $this->actingAs(User::factory()->create());

        $historicalOnly = $this->createExistingBookingChild([
            'parent_id' => 88,
            'updated_at' => now()->subDay(),
        ], [
            'transfer_status' => 'transferred',
        ]);

        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Omar',
                'child_age' => '7',
                'child_grade' => '2',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'existing_family_new_child',
                'review_detail' => 'Create a fresh family container because only historical rows exist.',
                'matched_booking_id' => $historicalOnly->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Approved for promotion.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'Promote into a fresh family container.')
            ->call('finalizePromotion', $review->id)
            ->assertHasNoErrors();

        $review->refresh();
        $newBooking = Booking::query()->latest('id')->firstOrFail();

        $this->assertSame(2, Booking::query()->count());
        $this->assertNotSame($historicalOnly->booking_id, $newBooking->id);
        $this->assertSame($newBooking->id, $review->resulting_booking_id);
        $this->assertSame(88, $newBooking->parent_id);
        $this->assertSame(['Omar'], $newBooking->children->pluck('child_name')->all());
        $this->assertSame(['Youssef'], Booking::query()->with('children')->findOrFail($historicalOnly->booking_id)->children->pluck('child_name')->all());
    }

    public function test_finalize_promotion_requires_all_child_rows_to_be_resolved(): void
    {
        $this->actingAs(User::factory()->create());

        $review = $this->createReview();

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'Attempting premature promotion.')
            ->call('finalizePromotion', $review->id)
            ->assertHasErrors(["reviewActions.{$review->id}"]);

        $this->assertSame(0, Booking::query()->count());
    }

    public function test_finalize_promotion_requires_at_least_one_approved_child(): void
    {
        $this->actingAs(User::factory()->create());

        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Dismissed Child',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Dismiss duplicate.',
                'matched_booking_id' => 11,
                'matched_child_id' => 22,
                'resolution_status' => 'dismiss_child',
                'resolution_note' => 'Duplicate child.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'No child approved.')
            ->call('finalizePromotion', $review->id)
            ->assertHasErrors(["reviewActions.{$review->id}"]);

        $this->assertSame(0, Booking::query()->count());
    }

    public function test_dismissing_submission_clears_open_submission_fingerprint(): void
    {
        $this->actingAs(User::factory()->create());

        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Previously Approved Child',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'clean_new_customer',
                'review_detail' => 'Was approved before the whole submission was dismissed.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Earlier operator decision.',
            ],
            [
                'child_index' => 1,
                'child_name' => 'Pending Child',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Still pending.',
                'matched_booking_id' => 11,
                'matched_child_id' => 22,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);
        $fingerprint = $review->open_submission_fingerprint;

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'Dismiss the whole submission.')
            ->call('dismissSubmission', $review->id)
            ->assertHasNoErrors();

        $review->refresh();

        $this->assertSame('dismissed', $review->status);
        $this->assertNull($review->open_submission_fingerprint);
        $this->assertSame($fingerprint !== null, true);
        $this->assertSame(
            ['dismiss_child'],
            $review->reviewChildren()->pluck('resolution_status')->unique()->values()->all()
        );
        $this->assertSame(
            ['Dismiss the whole submission.'],
            $review->reviewChildren()->pluck('resolution_note')->unique()->values()->all()
        );
    }

    public function test_review_child_renders_active_booking_context_link(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $review = $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Matches active child.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => $existingChild->id,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);

        $childEditUrl = route('admin.bookings.children.edit', [
            'bookingChild' => $existingChild->id,
            'return' => route('admin.bookings.intake-review'),
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->assertSee('Dismiss')
            ->assertSee('Finalize Approved Children')
            ->assertSee('Dismiss Entire Submission')
            ->assertSee("Open Account #{$existingChild->id}")
            ->assertSee("Open Booking #{$existingChild->booking_id}")
            ->assertSee(route('admin.bookings.livewire', ['search' => 'BK-INTAKE-1001']), false)
            ->assertSee($childEditUrl, false)
            ->assertSee('#'.$existingChild->booking_id)
            ->assertSee('#'.$existingChild->id)
            ->assertDontSee('Edit before approving')
            ->assertDontSee('Matched booking:')
            ->assertDontSee('Matched child:')
            ->assertDontSee('Matches active child.');

        $this->assertSame('pending_review', $review->fresh()->status);
    }

    public function test_review_child_renders_transferred_family_context_link(): void
    {
        $this->actingAs(User::factory()->create());

        $transferredChild = $this->createExistingBookingChild([], [
            'transfer_status' => 'transferred',
            'meeting_disposition' => 'completed',
        ]);

        $this->createReview([], [
            [
                'child_index' => 0,
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'repeat_submission',
                'review_detail' => 'Matches transferred child.',
                'matched_booking_id' => $transferredChild->booking_id,
                'matched_child_id' => $transferredChild->id,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);

        $childEditUrl = route('admin.bookings.children.edit', [
            'bookingChild' => $transferredChild->id,
            'return' => route('admin.bookings.intake-review'),
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->assertSee("Open Account #{$transferredChild->id}")
            ->assertSee('Open Transferred Family')
            ->assertSee(route('admin.bookings.transferred', ['search' => 'Youssef']), false)
            ->assertSee($childEditUrl, false)
            ->assertSee('#'.$transferredChild->booking_id)
            ->assertSee('#'.$transferredChild->id);
    }

    public function test_contact_mismatch_reason_renders_badge_stat_and_filter(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();

        $this->createReview([
            'detection_reason' => 'suspected_contact_mismatch',
            'detection_detail' => "Contact mismatch: submitted email matches existing booking #{$existingChild->booking_id} but phone differs. Admin verification required before child intake proceeds.",
            'matched_booking_id' => $existingChild->booking_id,
            'matched_child_id' => null,
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => "Contact mismatch: submitted email matches existing booking #{$existingChild->booking_id} but phone differs. Admin verification required before child intake proceeds.",
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->assertSee('Contact Mismatches')
            ->assertSee('Contact Mismatch')
            ->assertSee("Open Booking #{$existingChild->booking_id}")
            ->call('applyQuickFilter', 'suspected_contact_mismatch')
            ->assertSee('Jana')
            ->assertSee('Contact Mismatch')
            ->assertSet('reasonFilter', 'suspected_contact_mismatch');
    }

    public function test_quick_filter_card_can_switch_to_duplicate_repeat_bucket(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createReview([
            'parent_name' => 'Repeat Parent',
            'detection_reason' => 'repeat_submission',
            'detection_detail' => 'Matches transferred child.',
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Repeat Child',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'repeat_submission',
                'review_detail' => 'Matches transferred child.',
                'matched_booking_id' => 10,
                'matched_child_id' => 20,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);

        $this->createReview([
            'parent_name' => 'Mismatch Parent',
            'detection_reason' => 'suspected_contact_mismatch',
            'detection_detail' => 'Contact changed and requires verification.',
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Mismatch Child',
                'child_age' => '9',
                'child_grade' => '4',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => 'Contact changed and requires verification.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->call('applyQuickFilter', 'duplicate_repeat')
            ->assertSet('reasonFilter', 'duplicate_repeat')
            ->assertSee('Repeat Parent')
            ->assertDontSee('Mismatch Parent');
    }

    public function test_contact_mismatch_generic_promotion_is_blocked_until_verified_update(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();

        $review = $this->createReview([
            'parent_phone' => '201555666777',
            'detection_reason' => 'suspected_contact_mismatch',
            'matched_booking_id' => $existingChild->booking_id,
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => 'Contact changed and requires verification.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Verified by phone call.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'Tried normal promotion.')
            ->call('finalizePromotion', $review->id)
            ->assertHasErrors(["reviewActions.{$review->id}"])
            ->assertSee('Contact mismatch requires the explicit verified contact update action before promotion.');

        $this->assertSame('pending_review', $review->fresh()->status);
        $this->assertSame(['Youssef'], $existingChild->booking()->firstOrFail()->children()->pluck('child_name')->all());
    }

    public function test_pending_contact_mismatch_child_can_be_approved_for_verified_contact_update(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $newPhone = '201555666777';

        $review = $this->createReview([
            'parent_phone' => $newPhone,
            'detection_reason' => 'suspected_contact_mismatch',
            'matched_booking_id' => $existingChild->booking_id,
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => 'Contact changed and requires verification.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);
        $reviewChild = $review->reviewChildren->first();

        Livewire::test(IntakeReviewQueue::class)
            ->assertSee('Approve for contact update')
            ->call('setChildResolution', $reviewChild->id, 'promote_child')
            ->assertHasNoErrors()
            ->set("submissionNotes.{$review->id}", 'Verified same parent by phone call.')
            ->call('finalizeVerifiedContactUpdate', $review->id)
            ->assertHasNoErrors();

        $booking = Booking::query()->with('children')->findOrFail($existingChild->booking_id);

        $this->assertSame('promote_child', $reviewChild->fresh()->resolution_status);
        $this->assertSame('promoted_to_queue', $review->fresh()->status);
        $this->assertSame($newPhone, $booking->parent_phone);
        $this->assertSame(['Youssef', 'Jana'], $booking->children->pluck('child_name')->all());
    }

    public function test_verified_contact_update_replaces_target_booking_contact_and_promotes_child(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();
        $newPhone = '201555666777';

        $review = $this->createReview([
            'parent_phone' => $newPhone,
            'detection_reason' => 'suspected_contact_mismatch',
            'matched_booking_id' => $existingChild->booking_id,
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => 'Contact changed and requires verification.',
                'matched_booking_id' => $existingChild->booking_id,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Verified by phone call.',
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->set("submissionNotes.{$review->id}", 'Verified same parent by phone call.')
            ->call('finalizeVerifiedContactUpdate', $review->id)
            ->assertHasNoErrors();

        $booking = Booking::query()->with('children')->findOrFail($existingChild->booking_id);

        $this->assertSame('promoted_to_queue', $review->fresh()->status);
        $this->assertSame($newPhone, $booking->parent_phone);
        $this->assertSame(['Youssef', 'Jana'], $booking->children->pluck('child_name')->all());
        $this->assertDatabaseHas('booking_parent_identity_resolutions', [
            'stage' => 'intake_review_promotion',
            'outcome' => 'verified_contact_update',
            'booking_intake_review_id' => $review->id,
            'booking_id' => $existingChild->booking_id,
            'contact_action' => 'replace_phone',
            'previous_parent_phone' => '201000111222',
            'resolved_parent_phone' => $newPhone,
            'resolution_note' => 'Verified same parent by phone call.',
        ]);
        $this->assertSame(1, DB::table('booking_parent_identity_resolutions')->count());
    }

    public function test_contact_mismatch_filter_includes_mixed_children_reviews_with_pending_mismatch_rows(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createReview([
            'parent_name' => 'Mixed Mismatch Parent',
            'detection_reason' => 'mixed_children',
            'detection_detail' => 'Submission contains mixed outcomes.',
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => 'Contact changed and requires verification.',
                'matched_booking_id' => 10,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
            [
                'child_index' => 1,
                'child_name' => 'Omar',
                'child_age' => '7',
                'child_grade' => '2',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'clean_new_customer',
                'review_detail' => 'Clean child.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->call('applyQuickFilter', 'suspected_contact_mismatch')
            ->assertSee('Mixed Mismatch Parent')
            ->assertSee('Jana')
            ->assertSet('reasonFilter', 'suspected_contact_mismatch');
    }

    public function test_contact_mismatch_filter_excludes_mixed_children_reviews_once_mismatch_rows_are_resolved(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createReview([
            'parent_name' => 'Resolved Mismatch Parent',
            'detection_reason' => 'mixed_children',
            'detection_detail' => 'Submission contains mixed outcomes.',
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => 'Contact changed and requires verification.',
                'matched_booking_id' => 10,
                'matched_child_id' => null,
                'resolution_status' => 'promote_child',
                'resolution_note' => 'Verified',
            ],
            [
                'child_index' => 1,
                'child_name' => 'Omar',
                'child_age' => '7',
                'child_grade' => '2',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'clean_new_customer',
                'review_detail' => 'Clean child.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);

        Livewire::test(IntakeReviewQueue::class)
            ->call('applyQuickFilter', 'suspected_contact_mismatch')
            ->assertDontSee('Resolved Mismatch Parent')
            ->assertSet('reasonFilter', 'suspected_contact_mismatch');
    }

    public function test_contact_mismatch_stat_count_matches_contact_mismatch_filter_population(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createReview([
            'parent_name' => 'Top Level Mismatch Parent',
            'detection_reason' => 'suspected_contact_mismatch',
            'detection_detail' => 'Contact changed and requires verification.',
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Jana',
                'child_age' => '8',
                'child_grade' => '3',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => 'Contact changed and requires verification.',
                'matched_booking_id' => 10,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);

        $this->createReview([
            'parent_name' => 'Mixed Mismatch Parent',
            'detection_reason' => 'mixed_children',
            'detection_detail' => 'Submission contains mixed outcomes.',
        ], [
            [
                'child_index' => 0,
                'child_name' => 'Omar',
                'child_age' => '7',
                'child_grade' => '2',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'suspected_contact_mismatch',
                'review_detail' => 'Contact changed and requires verification.',
                'matched_booking_id' => 10,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
            [
                'child_index' => 1,
                'child_name' => 'Lina',
                'child_age' => '9',
                'child_grade' => '4',
                'school_system' => 'British',
                'service_interests' => ['Help Me Study'],
                'review_reason' => 'clean_new_customer',
                'review_detail' => 'Clean child.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ]);

        $component = Livewire::test(IntakeReviewQueue::class)
            ->call('applyQuickFilter', 'suspected_contact_mismatch')
            ->assertSee('Top Level Mismatch Parent')
            ->assertSee('Mixed Mismatch Parent')
            ->assertSet('reasonFilter', 'suspected_contact_mismatch')
            ->instance();

        $baseQueryMethod = new \ReflectionMethod($component, 'baseQuery');
        $baseQueryMethod->setAccessible(true);
        $filterCount = $baseQueryMethod->invoke($component)->count();

        $statsMethod = new \ReflectionMethod($component, 'stats');
        $statsMethod->setAccessible(true);
        $stats = collect($statsMethod->invoke($component));
        $contactMismatchStat = $stats->firstWhere('filter', 'suspected_contact_mismatch');

        $this->assertSame(2, $filterCount);
        $this->assertSame($filterCount, $contactMismatchStat['value']);
    }
}
