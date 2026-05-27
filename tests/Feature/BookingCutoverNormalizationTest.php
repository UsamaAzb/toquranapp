<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Verifies the cutover data normalization SQL from data-model.md section 7.
 *
 * Each test runs the exact SQL statement for the relevant step against a
 * controlled in-memory schema and asserts expected column values after execution.
 * The goal is to catch mapping regressions before the owner pastes these
 * statements into phpMyAdmin on the live shared database.
 */
class BookingCutoverNormalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'array']);

        $this->createCutoverTestTables();
    }

    // ─── Step 1: Backfill workflow_status from consultation_status ───────────

    public function test_step1_maps_all_known_consultation_statuses_to_workflow_status(): void
    {
        $mappings = [
            'pending' => 'pending',
            'confirmed' => 'confirmed',
            'questionnaire_sent' => 'questionnaire_sent',
            'followup' => 'followup_required',
            'cancelled' => 'cancelled',
        ];

        foreach ($mappings as $from => $expectedTo) {
            $bookingId = $this->insertBooking();
            DB::table('booking_children')->insert([
                'booking_id' => $bookingId,
                'child_name' => "Child {$from}",
                'child_age' => 10,
                'service_interests' => '["Help Me Study"]',
                'consultation_status' => $from,
                'workflow_status' => 'pending',
                'evaluation_outcome' => 'undecided',
                'consultation_type' => 'undecided',
                'transfer_status' => 'not_transferred',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->runStep1();

        foreach ($mappings as $from => $expectedTo) {
            $this->assertSame(
                $expectedTo,
                DB::table('booking_children')
                    ->where('child_name', "Child {$from}")
                    ->value('workflow_status'),
                "consultation_status '{$from}' should map to workflow_status '{$expectedTo}'"
            );
        }
    }

    public function test_step1_maps_unknown_consultation_status_to_pending(): void
    {
        $bookingId = $this->insertBooking();
        DB::table('booking_children')->insert([
            'booking_id' => $bookingId,
            'child_name' => 'Child Unknown',
            'child_age' => 10,
            'service_interests' => '["Help Me Study"]',
            'consultation_status' => 'legacy_odd_value',
            'workflow_status' => 'pending',
            'evaluation_outcome' => 'undecided',
            'consultation_type' => 'undecided',
            'transfer_status' => 'not_transferred',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runStep1();

        $this->assertSame(
            'pending',
            DB::table('booking_children')->where('child_name', 'Child Unknown')->value('workflow_status')
        );
    }

    public function test_step1_is_idempotent_for_already_migrated_rows(): void
    {
        $bookingId = $this->insertBooking();
        // Simulate a row already migrated (workflow_status != 'pending')
        DB::table('booking_children')->insert([
            'booking_id' => $bookingId,
            'child_name' => 'Already Migrated',
            'child_age' => 10,
            'service_interests' => '["Help Me Study"]',
            'consultation_status' => 'followup',
            'workflow_status' => 'confirmed',   // already explicitly set
            'evaluation_outcome' => 'undecided',
            'consultation_type' => 'undecided',
            'transfer_status' => 'not_transferred',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runStep1();

        // Must not overwrite a non-pending workflow_status
        $this->assertSame(
            'confirmed',
            DB::table('booking_children')->where('child_name', 'Already Migrated')->value('workflow_status')
        );
    }

    // ─── Step 2: Backfill evaluation_outcome from evaluation_status ──────────

    public function test_step2_maps_all_evaluation_statuses_to_evaluation_outcome(): void
    {
        $mappings = [
            'fit' => 'fit',
            'unfit' => 'unfit',
            'PL' => 'PL',
            null => 'undecided',
        ];

        foreach ($mappings as $from => $expectedTo) {
            $bookingId = $this->insertBooking();
            $label = $from ?? 'null';
            DB::table('booking_children')->insert([
                'booking_id' => $bookingId,
                'child_name' => "Child Eval {$label}",
                'child_age' => 10,
                'service_interests' => '["Help Me Study"]',
                'evaluation_status' => $from,
                'evaluation_outcome' => 'undecided',
                'workflow_status' => 'pending',
                'consultation_type' => 'undecided',
                'transfer_status' => 'not_transferred',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->runStep2();

        foreach ($mappings as $from => $expectedTo) {
            $label = $from ?? 'null';
            $this->assertSame(
                $expectedTo,
                DB::table('booking_children')
                    ->where('child_name', "Child Eval {$label}")
                    ->value('evaluation_outcome'),
                "evaluation_status '{$label}' should map to evaluation_outcome '{$expectedTo}'"
            );
        }
    }

    public function test_step2_is_idempotent_for_already_migrated_rows(): void
    {
        $bookingId = $this->insertBooking();
        DB::table('booking_children')->insert([
            'booking_id' => $bookingId,
            'child_name' => 'Already Eval Migrated',
            'child_age' => 10,
            'service_interests' => '["Help Me Study"]',
            'evaluation_status' => 'unfit',
            'evaluation_outcome' => 'fit',  // already explicitly set to fit — must not be overwritten
            'workflow_status' => 'pending',
            'consultation_type' => 'undecided',
            'transfer_status' => 'not_transferred',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runStep2();

        $this->assertSame(
            'fit',
            DB::table('booking_children')->where('child_name', 'Already Eval Migrated')->value('evaluation_outcome')
        );
    }

    // ─── Step 2A: Reconciliation list ────────────────────────────────────────

    public function test_step2a_reconciliation_query_identifies_evaluated_rows_missing_meeting_disposition(): void
    {
        $bookingId = $this->insertBooking();

        // Row that should appear in reconciliation: evaluated outcome set, no meeting_disposition
        DB::table('booking_children')->insert([
            'booking_id' => $bookingId,
            'child_name' => 'Unresolved Eval',
            'child_age' => 10,
            'service_interests' => '["Help Me Study"]',
            'workflow_status' => 'confirmed',
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => null,
            'consultation_type' => 'online',
            'transfer_status' => 'not_transferred',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Row that should NOT appear: evaluation outcome undecided
        DB::table('booking_children')->insert([
            'booking_id' => $bookingId,
            'child_name' => 'Not Yet Evaluated',
            'child_age' => 10,
            'service_interests' => '["Help Me Study"]',
            'workflow_status' => 'pending',
            'evaluation_outcome' => 'undecided',
            'meeting_disposition' => null,
            'consultation_type' => 'undecided',
            'transfer_status' => 'not_transferred',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Row that should NOT appear: has meeting_disposition already
        DB::table('booking_children')->insert([
            'booking_id' => $bookingId,
            'child_name' => 'Already Has Disposition',
            'child_age' => 10,
            'service_interests' => '["Help Me Study"]',
            'workflow_status' => 'confirmed',
            'evaluation_outcome' => 'fit',
            'meeting_disposition' => 'completed',
            'consultation_type' => 'online',
            'transfer_status' => 'not_transferred',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $results = DB::table('booking_children')
            ->whereNull('meeting_disposition')
            ->whereIn('evaluation_outcome', ['fit', 'unfit', 'PL'])
            ->pluck('child_name');

        $this->assertCount(1, $results);
        $this->assertContains('Unresolved Eval', $results->all());
        $this->assertNotContains('Not Yet Evaluated', $results->all());
        $this->assertNotContains('Already Has Disposition', $results->all());
    }

    // ─── Step 3: Migrate consultation details for single-child bookings ──────

    public function test_step3_migrates_consultation_type_for_single_child_booking(): void
    {
        $bookingId = DB::table('bookings')->insertGetId([
            'parent_name' => 'Alia Hassan',
            'parent_email' => 'alia@example.test',
            'parent_phone' => '20100999888',
            'consultation_type' => 'online',
            'meeting_link' => 'https://meet.example.com/alia',
            'meeting_address' => null,
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Single child with undecided consultation_type
        DB::table('booking_children')->insert([
            'booking_id' => $bookingId,
            'child_name' => 'Single Child',
            'child_age' => 10,
            'service_interests' => '["Help Me Study"]',
            'workflow_status' => 'confirmed',
            'evaluation_outcome' => 'undecided',
            'consultation_type' => 'undecided',
            'meeting_link' => null,
            'meeting_address' => null,
            'transfer_status' => 'not_transferred',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runStep3();

        $child = DB::table('booking_children')->where('child_name', 'Single Child')->first();
        $this->assertSame('online', $child->consultation_type);
        $this->assertSame('https://meet.example.com/alia', $child->meeting_link);
    }

    public function test_step3_does_not_migrate_for_multi_child_bookings(): void
    {
        $bookingId = DB::table('bookings')->insertGetId([
            'parent_name' => 'Nour Eldin',
            'parent_email' => 'nour@example.test',
            'parent_phone' => '20100777666',
            'consultation_type' => 'in-person',
            'meeting_link' => null,
            'meeting_address' => '123 School Rd',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Two children — neither should inherit the booking-level consultation_type
        foreach (['Child A', 'Child B'] as $name) {
            DB::table('booking_children')->insert([
                'booking_id' => $bookingId,
                'child_name' => $name,
                'child_age' => 10,
                'service_interests' => '["Help Me Study"]',
                'workflow_status' => 'confirmed',
                'evaluation_outcome' => 'undecided',
                'consultation_type' => 'undecided',
                'meeting_link' => null,
                'meeting_address' => null,
                'transfer_status' => 'not_transferred',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->runStep3();

        foreach (['Child A', 'Child B'] as $name) {
            $this->assertSame(
                'undecided',
                DB::table('booking_children')->where('child_name', $name)->value('consultation_type'),
                "{$name} in a multi-child booking must not inherit booking-level consultation_type"
            );
        }
    }

    public function test_step3_does_not_overwrite_explicit_child_consultation_type(): void
    {
        $bookingId = DB::table('bookings')->insertGetId([
            'parent_name' => 'Sara Mostafa',
            'parent_email' => 'sara@example.test',
            'parent_phone' => '20100333222',
            'consultation_type' => 'in-person',
            'meeting_link' => null,
            'meeting_address' => '456 Center Ave',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('booking_children')->insert([
            'booking_id' => $bookingId,
            'child_name' => 'Explicit Type Child',
            'child_age' => 10,
            'service_interests' => '["Help Me Study"]',
            'workflow_status' => 'confirmed',
            'evaluation_outcome' => 'undecided',
            'consultation_type' => 'online',  // already set — must not be overwritten
            'meeting_link' => 'https://meet.example.com/child',
            'meeting_address' => null,
            'transfer_status' => 'not_transferred',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runStep3();

        $this->assertSame(
            'online',
            DB::table('booking_children')->where('child_name', 'Explicit Type Child')->value('consultation_type')
        );
    }

    // ─── Step 4: Create booking_children for bookings that have none ─────────

    public function test_step4_creates_child_row_for_booking_with_no_existing_children(): void
    {
        $bookingId = DB::table('bookings')->insertGetId([
            'parent_name' => 'Hana Farouk',
            'parent_email' => 'hana@example.test',
            'parent_phone' => '20111222333',
            'child_name' => 'Legacy Only Child',
            'child_age' => 9,
            'child_grade' => 3,
            'school_system' => 'British',
            'service_interest' => 'Help Me Study',
            'current_school' => 'Future School',
            'consultation_type' => 'online',
            'meeting_link' => 'https://meet.example.com/legacy',
            'meeting_address' => null,
            'consultation_date' => '2026-04-20',
            'consultation_time' => '11:00',
            'follow_up_date' => null,
            'status' => 'confirmed',
            'student_id' => null,
            'transfer' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // No children row for this booking
        $this->assertSame(0, DB::table('booking_children')->where('booking_id', $bookingId)->count());

        $this->runStep4();

        $this->assertSame(1, DB::table('booking_children')->where('booking_id', $bookingId)->count());

        $child = DB::table('booking_children')->where('booking_id', $bookingId)->first();
        $this->assertSame('Legacy Only Child', $child->child_name);
        $this->assertSame('confirmed', $child->workflow_status);
        $this->assertSame('undecided', $child->evaluation_outcome);
        $this->assertNull($child->meeting_disposition);
        $this->assertSame('online', $child->consultation_type);
        $this->assertSame('https://meet.example.com/legacy', $child->meeting_link);
    }

    public function test_step4_skips_bookings_that_already_have_children(): void
    {
        $bookingId = $this->insertBooking(['child_name' => 'Parent Booking']);
        DB::table('booking_children')->insert([
            'booking_id' => $bookingId,
            'child_name' => 'Existing Child Row',
            'child_age' => 10,
            'service_interests' => '["Help Me Study"]',
            'workflow_status' => 'pending',
            'evaluation_outcome' => 'undecided',
            'consultation_type' => 'undecided',
            'transfer_status' => 'not_transferred',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runStep4();

        $this->assertSame(1, DB::table('booking_children')->where('booking_id', $bookingId)->count());
    }

    public function test_step4_skips_bookings_with_blank_or_null_child_name(): void
    {
        $bookingId = DB::table('bookings')->insertGetId([
            'parent_name' => 'No Child Name',
            'parent_email' => 'noname@example.test',
            'parent_phone' => '20100000001',
            'child_name' => null,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runStep4();

        $this->assertSame(0, DB::table('booking_children')->where('booking_id', $bookingId)->count());
    }

    public function test_step4_maps_legacy_status_to_correct_workflow_and_evaluation(): void
    {
        $statusMappings = [
            ['status' => 'fit',    'wf' => 'pending', 'eval' => 'fit'],
            ['status' => 'unfit',  'wf' => 'pending', 'eval' => 'unfit'],
            ['status' => 'PL',     'wf' => 'pending', 'eval' => 'PL'],
            ['status' => 'followup', 'wf' => 'followup_required', 'eval' => 'undecided'],
        ];

        foreach ($statusMappings as $map) {
            $bookingId = DB::table('bookings')->insertGetId([
                'parent_name' => 'Mapping Test',
                'parent_email' => "test_{$map['status']}@example.test",
                'parent_phone' => '20100000002',
                'child_name' => "Map Child {$map['status']}",
                'child_age' => 10,
                'service_interest' => 'Help Me Study',
                'status' => $map['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->runStep4();

        foreach ($statusMappings as $map) {
            $child = DB::table('booking_children')
                ->where('child_name', "Map Child {$map['status']}")
                ->first();

            $this->assertNotNull($child, "Expected child row for status '{$map['status']}'");
            $this->assertSame($map['wf'], $child->workflow_status, "workflow_status for status '{$map['status']}'");
            $this->assertSame($map['eval'], $child->evaluation_outcome, "evaluation_outcome for status '{$map['status']}'");
        }
    }

    public function test_step4_never_infers_meeting_disposition_for_synthetic_rows(): void
    {
        $bookingId = DB::table('bookings')->insertGetId([
            'parent_name' => 'Inferred Disp Test',
            'parent_email' => 'inferred@example.test',
            'parent_phone' => '20100000003',
            'child_name' => 'Inferred Child',
            'child_age' => 10,
            'service_interest' => 'Help Me Study',
            'status' => 'fit',  // evaluation-status row
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runStep4();

        $child = DB::table('booking_children')->where('booking_id', $bookingId)->first();
        $this->assertNotNull($child);
        $this->assertNull($child->meeting_disposition, 'Step 4 must not fabricate a meeting_disposition for synthetic rows');
    }

    // ─── SQL helpers ─────────────────────────────────────────────────────────

    /** Run Step 1 normalization SQL verbatim from data-model.md §7 */
    protected function runStep1(): void
    {
        DB::statement("
            UPDATE `booking_children`
            SET `workflow_status` = CASE `consultation_status`
                WHEN 'pending'             THEN 'pending'
                WHEN 'confirmed'           THEN 'confirmed'
                WHEN 'questionnaire_sent'  THEN 'questionnaire_sent'
                WHEN 'followup'            THEN 'followup_required'
                WHEN 'cancelled'           THEN 'cancelled'
                ELSE 'pending'
              END,
              `meeting_disposition` = NULL
            WHERE `workflow_status` = 'pending'
        ");
    }

    /** Run Step 2 normalization SQL verbatim from data-model.md §7 */
    protected function runStep2(): void
    {
        DB::statement("
            UPDATE `booking_children`
            SET `evaluation_outcome` = CASE `evaluation_status`
                WHEN 'fit'   THEN 'fit'
                WHEN 'unfit' THEN 'unfit'
                WHEN 'PL'    THEN 'PL'
                ELSE 'undecided'
              END
            WHERE `evaluation_outcome` = 'undecided'
        ");
    }

    /**
     * Run Step 3 normalization logic from data-model.md §7.
     *
     * On MySQL the verbatim multi-table UPDATE is used.
     * On SQLite (test environment) the same logic is applied via PHP because
     * SQLite does not support MySQL's UPDATE ... JOIN syntax.
     */
    protected function runStep3(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                UPDATE `booking_children` bc
                JOIN `bookings` b ON bc.booking_id = b.id
                JOIN (
                  SELECT booking_id, COUNT(*) AS child_count
                  FROM `booking_children`
                  GROUP BY booking_id
                ) child_counts ON child_counts.booking_id = bc.booking_id
                SET
                  bc.`consultation_type` = CASE
                    WHEN bc.`consultation_type` = 'undecided' AND b.`consultation_type` IS NOT NULL
                      THEN b.`consultation_type`
                    ELSE bc.`consultation_type`
                  END,
                  bc.`meeting_link`    = COALESCE(bc.`meeting_link`, b.`meeting_link`),
                  bc.`meeting_address` = COALESCE(bc.`meeting_address`, b.`meeting_address`)
                WHERE child_counts.`child_count` = 1
                  AND bc.`consultation_type` = 'undecided'
                  AND (b.`consultation_type` IS NOT NULL
                    OR b.`meeting_link` IS NOT NULL
                    OR b.`meeting_address` IS NOT NULL)
            ");

            return;
        }

        // SQLite-compatible equivalent (same logical outcome)
        $singleChildBookingIds = DB::table('booking_children')
            ->selectRaw('booking_id, COUNT(*) as cnt')
            ->groupBy('booking_id')
            ->having('cnt', '=', 1)
            ->pluck('booking_id');

        foreach ($singleChildBookingIds as $bookingId) {
            $booking = DB::table('bookings')->where('id', $bookingId)->first();
            $child = DB::table('booking_children')->where('booking_id', $bookingId)->first();

            if (! $booking || ! $child || $child->consultation_type !== 'undecided') {
                continue;
            }

            if (blank($booking->consultation_type) && blank($booking->meeting_link) && blank($booking->meeting_address)) {
                continue;
            }

            $updates = [];

            if (! blank($booking->consultation_type) && $child->consultation_type === 'undecided') {
                $updates['consultation_type'] = $booking->consultation_type;
            }

            if (blank($child->meeting_link) && ! blank($booking->meeting_link)) {
                $updates['meeting_link'] = $booking->meeting_link;
            }

            if (blank($child->meeting_address) && ! blank($booking->meeting_address)) {
                $updates['meeting_address'] = $booking->meeting_address;
            }

            if (! empty($updates)) {
                DB::table('booking_children')->where('id', $child->id)->update($updates);
            }
        }
    }

    /**
     * Run Step 4 normalization logic from data-model.md §7.
     *
     * On MySQL the verbatim INSERT ... SELECT is used.
     * On SQLite (test environment) the same logic is applied via PHP because
     * SQLite does not support NOW() or JSON_ARRAY().
     */
    protected function runStep4(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                INSERT INTO `booking_children`
                  (booking_id, child_name, child_age, child_grade,
                    service_interests, student_id, transfer_status,
                    workflow_status, meeting_disposition, evaluation_outcome, consultation_type,
                    meeting_link, meeting_address,
                    current_school, school_system, scheduled_date, scheduled_time, followup_date,
                    created_at, updated_at)
                SELECT
                  b.id,
                  b.child_name,
                  b.child_age,
                  b.child_grade,
                  CASE
                    WHEN b.service_interest IS NOT NULL AND b.service_interest != ''
                    THEN JSON_ARRAY(b.service_interest)
                    ELSE JSON_ARRAY()
                  END,
                  b.student_id,
                  CASE WHEN b.transfer = 1 OR b.transfer = '1' THEN 'transferred' ELSE 'not_transferred' END,
                  CASE b.status
                    WHEN 'pending'            THEN 'pending'
                    WHEN 'confirmed'          THEN 'confirmed'
                    WHEN 'questionnaire_sent' THEN 'questionnaire_sent'
                    WHEN 'followup'           THEN 'followup_required'
                    WHEN 'cancelled'          THEN 'cancelled'
                    ELSE 'pending'
                  END,
                  NULL,
                  CASE b.status
                    WHEN 'fit'   THEN 'fit'
                    WHEN 'unfit' THEN 'unfit'
                    WHEN 'PL'    THEN 'PL'
                    ELSE 'undecided'
                  END,
                  COALESCE(b.consultation_type, 'undecided'),
                  b.meeting_link,
                  b.meeting_address,
                  b.current_school,
                  b.school_system,
                  b.consultation_date,
                  b.consultation_time,
                  b.follow_up_date,
                  NOW(),
                  NOW()
                FROM `bookings` b
                WHERE NOT EXISTS (
                  SELECT 1 FROM `booking_children` bc WHERE bc.booking_id = b.id
                )
                  AND b.child_name IS NOT NULL
                  AND b.child_name != ''
            ");

            return;
        }

        // SQLite-compatible equivalent (same logical outcome)
        $bookings = DB::table('bookings')
            ->whereNotExists(function ($q): void {
                $q->select(DB::raw(1))
                    ->from('booking_children')
                    ->whereColumn('booking_children.booking_id', 'bookings.id');
            })
            ->whereNotNull('child_name')
            ->where('child_name', '!=', '')
            ->get();

        foreach ($bookings as $b) {
            $serviceInterest = $b->service_interest ?? null;
            $serviceInterests = filled($serviceInterest)
                ? json_encode([$serviceInterest])
                : json_encode([]);

            $transfer = $b->transfer ?? null;
            $transferStatus = ($transfer == 1 || $transfer === '1') ? 'transferred' : 'not_transferred';

            $workflowStatus = match ($b->status ?? null) {
                'pending' => 'pending',
                'confirmed' => 'confirmed',
                'questionnaire_sent' => 'questionnaire_sent',
                'followup' => 'followup_required',
                'cancelled' => 'cancelled',
                default => 'pending',
            };

            $evaluationOutcome = match ($b->status ?? null) {
                'fit' => 'fit',
                'unfit' => 'unfit',
                'PL' => 'PL',
                default => 'undecided',
            };

            DB::table('booking_children')->insert([
                'booking_id' => $b->id,
                'child_name' => $b->child_name,
                'child_age' => $b->child_age ?? null,
                'child_grade' => $b->child_grade ?? null,
                'service_interests' => $serviceInterests,
                'student_id' => $b->student_id ?? null,
                'transfer_status' => $transferStatus,
                'workflow_status' => $workflowStatus,
                'meeting_disposition' => null,
                'evaluation_outcome' => $evaluationOutcome,
                'consultation_type' => $b->consultation_type ?? 'undecided',
                'meeting_link' => $b->meeting_link ?? null,
                'meeting_address' => $b->meeting_address ?? null,
                'current_school' => $b->current_school ?? null,
                'school_system' => $b->school_system ?? null,
                'scheduled_date' => $b->consultation_date ?? null,
                'scheduled_time' => $b->consultation_time ?? null,
                'followup_date' => $b->follow_up_date ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // ─── Schema helpers ───────────────────────────────────────────────────────

    protected function insertBooking(array $overrides = []): int
    {
        return DB::table('bookings')->insertGetId(array_merge([
            'parent_name' => 'Test Parent',
            'parent_email' => 'parent@example.test',
            'parent_phone' => '201001112233',
            'consultation_type' => 'undecided',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function createCutoverTestTables(): void
    {
        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function ($table) {
                $table->id();
                $table->string('parent_name')->nullable();
                $table->string('parent_email')->nullable();
                $table->string('parent_phone')->nullable();
                $table->string('child_name')->nullable();
                $table->unsignedTinyInteger('child_age')->nullable();
                $table->unsignedInteger('child_grade')->nullable();
                $table->string('current_school')->nullable();
                $table->string('school_system')->nullable();
                $table->string('service_interest')->nullable();
                $table->string('consultation_type')->nullable();
                $table->date('consultation_date')->nullable();
                $table->string('consultation_time')->nullable();
                $table->dateTime('follow_up_date')->nullable();
                $table->text('meeting_address')->nullable();
                $table->string('meeting_link', 500)->nullable();
                $table->string('status')->nullable();
                $table->text('notes')->nullable();
                $table->string('booking_reference')->nullable();
                $table->boolean('transfer')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_children')) {
            Schema::create('booking_children', function ($table) {
                $table->id();
                $table->unsignedBigInteger('booking_id');
                $table->string('child_name');
                $table->unsignedTinyInteger('child_age')->nullable();
                $table->unsignedInteger('child_grade')->nullable();
                $table->string('school_system')->nullable();
                $table->json('service_interests');
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
    }
}
