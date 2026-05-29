<?php

namespace Tests\Support;

use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\BookingIntakeReview;
use Illuminate\Support\Facades\Schema;

trait InteractsWithBookingIntakeTables
{
    protected function createBookingIntakeTables(): void
    {
        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function ($table) {
                $table->id();
                $table->string('parent_name')->nullable();
                $table->string('type')->nullable();
                $table->string('parent_email')->nullable();
                $table->string('parent_phone')->nullable();
                $table->string('child_name')->nullable();
                $table->string('child_age')->nullable();
                $table->unsignedInteger('child_grade')->nullable();
                $table->string('current_school')->nullable();
                $table->string('school_system')->nullable();
                $table->text('primary_challenges')->nullable();
                $table->string('service_interest')->nullable();
                $table->date('preferred_date')->nullable();
                $table->string('preferred_time')->nullable();
                $table->string('consultation_type')->nullable();
                $table->date('consultation_date')->nullable();
                $table->text('main_concerns')->nullable();
                $table->string('how_heard')->nullable();
                $table->string('status')->nullable();
                $table->text('notes')->nullable();
                $table->string('contact_method')->nullable();
                $table->string('booking_reference')->nullable();
                $table->boolean('terms')->nullable();
                $table->text('teacher_notes')->nullable();
                $table->string('consultation_time')->nullable();
                $table->boolean('transfer')->nullable();
                $table->dateTime('follow_up_date')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->text('meeting_address')->nullable();
                $table->string('meeting_link')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_children')) {
            Schema::create('booking_children', function ($table) {
                $table->id();
                $table->unsignedBigInteger('booking_id');
                $table->string('child_name');
                $table->string('child_age')->nullable();
                $table->unsignedInteger('child_grade')->nullable();
                $table->string('school_system')->nullable();
                $table->json('service_interests')->nullable();
                $table->string('consultation_status')->nullable();
                $table->string('workflow_status')->default('pending');
                $table->string('meeting_disposition')->nullable();
                $table->string('meeting_disposition_reason', 500)->nullable();
                $table->string('evaluation_status')->nullable();
                $table->string('evaluation_outcome')->default('undecided');
                $table->string('consultation_type')->default('undecided');
                $table->string('meeting_link', 500)->nullable();
                $table->text('meeting_address')->nullable();
                $table->string('transfer_status')->default('not_transferred');
                $table->dateTime('followup_date')->nullable();
                $table->string('type')->nullable();
                $table->string('current_school')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->text('notes')->nullable();
                $table->date('scheduled_date')->nullable();
                $table->string('scheduled_time')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_intake_review')) {
            Schema::create('booking_intake_review', function ($table) {
                $table->id();
                $table->string('parent_name');
                $table->string('parent_email')->nullable();
                $table->string('parent_phone')->nullable();
                $table->string('child_name');
                $table->string('child_age')->nullable();
                $table->string('child_grade')->nullable();
                $table->string('school_system')->nullable();
                $table->string('type')->nullable();
                $table->json('service_interests')->nullable();
                $table->json('children_payload');
                $table->unsignedInteger('child_count')->default(1);
                $table->string('open_submission_fingerprint')->nullable()->unique();
                $table->text('notes')->nullable();
                $table->string('detection_reason');
                $table->text('detection_detail')->nullable();
                $table->unsignedBigInteger('matched_booking_id')->nullable();
                $table->unsignedBigInteger('matched_child_id')->nullable();
                $table->string('status')->default('pending_review');
                $table->unsignedBigInteger('resolved_by')->nullable();
                $table->text('resolution_note')->nullable();
                $table->dateTime('resolved_at')->nullable();
                $table->unsignedBigInteger('resulting_booking_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_intake_submission_locks')) {
            Schema::create('booking_intake_submission_locks', function ($table) {
                $table->id();
                $table->string('submission_fingerprint', 64)->unique();
                $table->string('parent_email')->nullable();
                $table->string('parent_phone', 100)->nullable();
                $table->string('child_names_hash', 64);
                $table->dateTime('first_seen_at')->nullable();
                $table->dateTime('last_seen_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_intake_review_children')) {
            Schema::create('booking_intake_review_children', function ($table) {
                $table->id();
                $table->unsignedBigInteger('booking_intake_review_id');
                $table->unsignedInteger('child_index');
                $table->string('child_name');
                $table->string('child_age')->nullable();
                $table->string('child_grade')->nullable();
                $table->string('school_system')->nullable();
                $table->string('type')->nullable();
                $table->json('service_interests')->nullable();
                $table->string('review_reason');
                $table->text('review_detail')->nullable();
                $table->unsignedBigInteger('matched_booking_id')->nullable();
                $table->unsignedBigInteger('matched_child_id')->nullable();
                $table->string('resolution_status')->default('pending_decision');
                $table->text('resolution_note')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_parent_blocks')) {
            Schema::create('booking_parent_blocks', function ($table) {
                $table->id();
                $table->string('normalized_email')->nullable()->unique();
                $table->string('normalized_phone')->nullable()->unique();
                $table->string('block_reason')->nullable();
                $table->text('internal_notes')->nullable();
                $table->unsignedBigInteger('blocked_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('parents')) {
            Schema::create('parents', function ($table) {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->string('user_name')->nullable();
                $table->unsignedBigInteger('family_support_id')->nullable();
                $table->string('image')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function ($table) {
                $table->id();
                $table->string('first_name')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedTinyInteger('age')->nullable();
                $table->unsignedBigInteger('grade_level_id')->nullable();
                $table->string('current_school')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grade_levels')) {
            Schema::create('grade_levels', function ($table) {
                $table->id();
                $table->string('title')->nullable();
                $table->unsignedInteger('level_order')->default(0);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (\Illuminate\Support\Facades\DB::table('grade_levels')->count() === 0) {
            foreach (range(1, 12) as $gradeId) {
                \Illuminate\Support\Facades\DB::table('grade_levels')->insert([
                    'id' => $gradeId,
                    'title' => 'Grade '.$gradeId,
                    'level_order' => $gradeId,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (! Schema::hasTable('booking_parent_identity_resolutions')) {
            Schema::create('booking_parent_identity_resolutions', function ($table) {
                $table->id();
                $table->string('stage');
                $table->string('outcome');
                $table->unsignedBigInteger('booking_intake_review_id')->nullable();
                $table->unsignedBigInteger('booking_intake_review_child_id')->nullable();
                $table->unsignedBigInteger('booking_id')->nullable();
                $table->unsignedBigInteger('booking_child_id')->nullable();
                $table->unsignedBigInteger('matched_booking_id')->nullable();
                $table->unsignedBigInteger('target_parent_id')->nullable();
                $table->unsignedBigInteger('conflicting_parent_id')->nullable();
                $table->string('submitted_parent_email')->nullable();
                $table->string('submitted_parent_phone')->nullable();
                $table->string('previous_parent_email')->nullable();
                $table->string('previous_parent_phone')->nullable();
                $table->string('resolved_parent_email')->nullable();
                $table->string('resolved_parent_phone')->nullable();
                $table->string('contact_action')->default('none');
                $table->text('child_identity_summary')->nullable();
                $table->text('conflict_summary')->nullable();
                $table->text('resolution_note')->nullable();
                $table->unsignedBigInteger('resolved_by')->nullable();
                $table->dateTime('resolved_at');
            });
        }
    }

    protected function createExistingBookingChild(array $bookingOverrides = [], array $childOverrides = []): BookingChild
    {
        $booking = Booking::create(array_merge([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'booking_reference' => 'BK-INTAKE-1001',
            'status' => 'pending',
            'service_interest' => 'My Deen Journey',
        ], $bookingOverrides));

        return BookingChild::create(array_merge([
            'booking_id' => $booking->id,
            'child_name' => 'Youssef',
            'child_age' => '11',
            'child_grade' => 6,
            'school_system' => 'British',
            'service_interests' => ['My Deen Journey'],
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
            'sort_order' => 1,
            'updated_by' => null,
        ], $childOverrides));
    }

    protected function createReview(array $reviewOverrides = [], ?array $children = null): BookingIntakeReview
    {
        $children ??= [
            [
                'child_index' => 0,
                'child_name' => 'Youssef',
                'child_age' => '11',
                'child_grade' => '6',
                'school_system' => 'British',
                'service_interests' => ['My Deen Journey'],
                'review_reason' => 'duplicate_child',
                'review_detail' => 'Matches an existing child.',
                'matched_booking_id' => null,
                'matched_child_id' => null,
                'resolution_status' => 'pending_decision',
                'resolution_note' => null,
            ],
        ];

        $primaryChild = $children[0];

        $review = BookingIntakeReview::create(array_merge([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'child_name' => $primaryChild['child_name'],
            'child_age' => $primaryChild['child_age'],
            'child_grade' => $primaryChild['child_grade'],
            'school_system' => $primaryChild['school_system'],
            'service_interests' => $primaryChild['service_interests'],
            'children_payload' => collect($children)->map(function (array $child) {
                return [
                    'child_name' => $child['child_name'],
                    'child_age' => $child['child_age'],
                    'child_grade' => $child['child_grade'],
                    'school_system' => $child['school_system'],
                    'service_interests' => $child['service_interests'],
                ];
            })->all(),
            'child_count' => count($children),
            'open_submission_fingerprint' => 'fingerprint-'.uniqid(),
            'notes' => 'Needs careful review.',
            'detection_reason' => 'mixed_children',
            'detection_detail' => 'Submission contains mixed child outcomes.',
            'matched_booking_id' => $primaryChild['matched_booking_id'] ?? null,
            'matched_child_id' => $primaryChild['matched_child_id'] ?? null,
            'status' => 'pending_review',
            'resolved_by' => null,
            'resolution_note' => null,
            'resolved_at' => null,
            'resulting_booking_id' => null,
        ], $reviewOverrides));

        foreach ($children as $child) {
            $review->reviewChildren()->create($child);
        }

        return $review->fresh('reviewChildren');
    }
}
