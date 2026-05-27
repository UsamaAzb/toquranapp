<?php

namespace Tests\Feature;

use App\Livewire\Admin\Booking\BookingChildEdit;
use App\Livewire\Admin\Booking\BookingList;
use App\Livewire\Admin\Booking\BookingParentEdit;
use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\User;
use App\Services\BookingChildEmailService;
use App\Services\BookingConfirmationService;
use Closure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Locked;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class BookingMilestoneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'array']);

        $this->createBookingTestTables();
        $this->seedBookingReferenceTables();
        Mail::fake();
    }

    public function test_server_owned_livewire_navigation_and_stale_guard_fields_are_locked(): void
    {
        $this->assertNotEmpty((new \ReflectionProperty(BookingChildEdit::class, 'expectedUpdatedAt'))->getAttributes(Locked::class));
        $this->assertNotEmpty((new \ReflectionProperty(BookingChildEdit::class, 'returnUrl'))->getAttributes(Locked::class));
        $this->assertNotEmpty((new \ReflectionProperty(BookingParentEdit::class, 'returnUrl'))->getAttributes(Locked::class));
    }

    public function test_workflow_status_saves_correctly(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'pending')
            ->call('save')
            ->assertHasNoErrors()
            ->set('workflowStatus', 'confirmed')
            ->set('consultationType', 'online')
            ->set('meetingLink', 'https://meet.example.com/booking-child')
            ->set('scheduledDate', '2026-04-08')
            ->set('scheduledTime', '10:30')
            ->call('save')
            ->assertHasNoErrors()
            ->set('workflowStatus', 'followup_required')
            ->set('followupDate', '2026-04-15')
            ->call('save')
            ->assertHasNoErrors();

        $child->refresh();

        $this->assertSame('followup_required', $child->workflow_status);
        $this->assertSame('followup', $child->consultation_status);
        $this->assertNotNull($child->followup_date);
        $this->assertDatabaseHas('booking_child_audit_log', [
            'booking_child_id' => $child->id,
            'field_name' => 'workflow_status',
            'changed_by' => $admin->id,
        ]);
    }

    public function test_cancelled_workflow_status_saves_to_legacy_consultation_status(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'cancelled')
            ->call('save')
            ->assertHasNoErrors();

        $child->refresh();

        $this->assertSame('cancelled', $child->workflow_status);
        $this->assertSame('cancelled', $child->consultation_status);
    }

    public function test_cancelled_closed_workflow_rejects_meeting_disposition_and_evaluation(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'cancelled')
            ->set('meetingDisposition', 'completed')
            ->set('evaluationOutcome', 'fit')
            ->call('save')
            ->assertHasErrors(['meetingDisposition', 'evaluationOutcome']);
    }

    public function test_followup_date_required_when_followup_required(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'followup_required')
            ->set('followupDate', null)
            ->call('save')
            ->assertHasErrors(['followupDate']);
    }

    public function test_pl_requires_future_followup_date(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('evaluationOutcome', 'PL')
            ->set('followupDate', null)
            ->call('save')
            ->assertHasErrors(['followupDate']);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('evaluationOutcome', 'PL')
            ->set('followupDate', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['followupDate']);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('evaluationOutcome', 'PL')
            ->set('meetingDisposition', 'no_meeting_required')
            ->set('followupDate', now()->addWeek()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $child->refresh();

        $this->assertSame('PL', $child->evaluation_outcome);
        $this->assertNotNull($child->followup_date);
    }

    public function test_scheduled_date_required_when_confirmed(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'confirmed')
            ->set('consultationType', 'undecided')
            ->set('scheduledDate', null)
            ->set('scheduledTime', '11:15')
            ->call('save')
            ->assertHasErrors(['scheduledDate']);
    }

    public function test_consultation_mode_must_be_chosen_before_confirmed_save(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild([
            'consultation_type' => 'undecided',
        ], [
            'consultation_type' => 'undecided',
            'meeting_link' => null,
            'meeting_address' => null,
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'confirmed')
            ->set('consultationType', 'undecided')
            ->set('scheduledDate', '2026-04-09')
            ->set('scheduledTime', '11:15')
            ->call('save')
            ->assertHasErrors(['consultationType']);

        $child->refresh();

        $this->assertSame('pending', $child->workflow_status);
    }

    public function test_cancelled_workflow_hydrates_as_cancelled_and_queue_badge_matches(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild([], [
            'workflow_status' => 'cancelled',
            'consultation_status' => 'cancelled',
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->assertSet('workflowStatus', 'cancelled');

        $badge = app(BookingList::class)->workflowBadge('cancelled');

        $this->assertSame([
            'label' => 'Cancelled / Closed',
            'class' => 'bg-label-danger',
        ], $badge);
    }

    public function test_booking_list_shows_only_children_matching_active_queue_filter(): void
    {
        $this->actingAs(User::factory()->create());

        $booking = Booking::create([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'booking_reference' => 'BK-FILTER-1001',
            'consultation_type' => 'undecided',
            'current_school' => 'Legacy School',
            'school_system' => 'British',
            'service_interest' => 'Help Me Study',
            'status' => 'pending',
            'notes' => 'Booking note',
        ]);

        BookingChild::create([
            'booking_id' => $booking->id,
            'child_name' => 'Confirmed Child',
            'child_age' => 11,
            'child_grade' => 1,
            'school_system' => 'British',
            'service_interests' => ['Help Me Study'],
            'consultation_status' => 'confirmed',
            'workflow_status' => 'confirmed',
            'meeting_disposition' => null,
            'evaluation_status' => null,
            'evaluation_outcome' => 'undecided',
            'consultation_type' => 'undecided',
            'transfer_status' => 'not_transferred',
            'current_school' => 'Current School',
            'notes' => 'Child note',
            'sort_order' => 1,
        ]);

        BookingChild::create([
            'booking_id' => $booking->id,
            'child_name' => 'Pending Child',
            'child_age' => 9,
            'child_grade' => 1,
            'school_system' => 'British',
            'service_interests' => ['Help Me Study'],
            'consultation_status' => 'pending',
            'workflow_status' => 'pending',
            'meeting_disposition' => null,
            'evaluation_status' => null,
            'evaluation_outcome' => 'undecided',
            'consultation_type' => 'undecided',
            'transfer_status' => 'not_transferred',
            'current_school' => 'Current School',
            'notes' => 'Child note',
            'sort_order' => 2,
        ]);

        Livewire::test(BookingList::class)
            ->set('filterQueueState', 'confirmed_upcoming')
            ->assertSee('Confirmed Child')
            ->assertDontSee('Pending Child');
    }

    public function test_booking_list_default_sort_matches_operational_urgency(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createBookingChild([
            'parent_name' => 'Pending Parent',
            'parent_email' => 'pending-parent@example.test',
            'booking_reference' => 'BK-PENDING-1001',
        ], [
            'child_name' => 'Pending Child',
            'workflow_status' => 'pending',
            'consultation_status' => 'pending',
            'meeting_disposition' => null,
        ]);

        $this->createBookingChild([
            'parent_name' => 'Cancelled Parent',
            'parent_email' => 'cancelled-parent@example.test',
            'booking_reference' => 'BK-CANCELLED-1002',
        ], [
            'child_name' => 'Cancelled Child',
            'workflow_status' => 'confirmed',
            'consultation_status' => 'confirmed',
            'meeting_disposition' => 'cancelled',
            'scheduled_date' => '2026-04-22',
            'scheduled_time' => '14:00',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Completed Parent',
            'parent_email' => 'completed-parent@example.test',
            'booking_reference' => 'BK-COMPLETED-1003',
        ], [
            'child_name' => 'Completed Child',
            'workflow_status' => 'confirmed',
            'consultation_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'scheduled_date' => '2026-04-21',
            'scheduled_time' => '13:00',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Questionnaire Parent',
            'parent_email' => 'questionnaire-parent@example.test',
            'booking_reference' => 'BK-QUESTIONNAIRE-1004',
        ], [
            'child_name' => 'Questionnaire Child',
            'workflow_status' => 'questionnaire_sent',
            'consultation_status' => 'questionnaire_sent',
            'meeting_disposition' => null,
        ]);

        $this->createBookingChild([
            'parent_name' => 'Confirmed Parent',
            'parent_email' => 'confirmed-parent@example.test',
            'booking_reference' => 'BK-CONFIRMED-1005',
        ], [
            'child_name' => 'Confirmed Child',
            'workflow_status' => 'confirmed',
            'consultation_status' => 'confirmed',
            'meeting_disposition' => null,
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '09:30',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Followup Parent',
            'parent_email' => 'followup-parent@example.test',
            'booking_reference' => 'BK-FOLLOWUP-1006',
        ], [
            'child_name' => 'Followup Due Child',
            'workflow_status' => 'followup_required',
            'consultation_status' => 'followup',
            'meeting_disposition' => null,
            'followup_date' => now()->subDay()->format('Y-m-d H:i:s'),
        ]);

        $this->createBookingChild([
            'parent_name' => 'Future Followup Parent',
            'parent_email' => 'future-followup-parent@example.test',
            'booking_reference' => 'BK-FOLLOWUP-1007',
        ], [
            'child_name' => 'Future Followup Child',
            'workflow_status' => 'followup_required',
            'consultation_status' => 'followup',
            'meeting_disposition' => null,
            'followup_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ]);

        Livewire::test(BookingList::class)
            ->assertSeeInOrder([
                'Followup Due Child',
                'Confirmed Child',
                'Questionnaire Child',
                'Pending Child',
                'Future Followup Child',
                'Completed Child',
                'Cancelled Child',
            ]);
    }

    public function test_booking_list_stats_count_terminal_records_separately_from_active_backlog(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createBookingChild([], [
            'child_name' => 'Pending Count Child',
            'workflow_status' => 'pending',
            'consultation_status' => 'pending',
            'meeting_disposition' => null,
            'evaluation_outcome' => 'undecided',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Confirmed Count Parent',
            'parent_email' => 'confirmed-count@example.test',
        ], [
            'child_name' => 'Confirmed Count Child',
            'workflow_status' => 'confirmed',
            'consultation_status' => 'confirmed',
            'meeting_disposition' => null,
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '10:00',
            'evaluation_outcome' => 'undecided',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Followup Count Parent',
            'parent_email' => 'followup-count@example.test',
        ], [
            'child_name' => 'Followup Count Child',
            'workflow_status' => 'followup_required',
            'consultation_status' => 'followup',
            'meeting_disposition' => null,
            'followup_date' => now()->subDay()->format('Y-m-d H:i:s'),
            'evaluation_outcome' => 'undecided',
        ]);

        $this->createBookingChild([
            'parent_name' => 'PL Count Parent',
            'parent_email' => 'pl-count@example.test',
        ], [
            'child_name' => 'PL Count Child',
            'workflow_status' => 'pending',
            'consultation_status' => 'pending',
            'meeting_disposition' => 'no_meeting_required',
            'followup_date' => now()->addWeek()->format('Y-m-d H:i:s'),
            'evaluation_outcome' => 'PL',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Transfer Count Parent',
            'parent_email' => 'transfer-count@example.test',
        ], [
            'child_name' => 'Transfer Count Child',
            'workflow_status' => 'confirmed',
            'consultation_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'evaluation_outcome' => 'fit',
            'transfer_status' => 'not_transferred',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '11:00',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Cancelled Count Parent',
            'parent_email' => 'cancelled-count@example.test',
        ], [
            'child_name' => 'Cancelled Count Child',
            'workflow_status' => 'confirmed',
            'consultation_status' => 'confirmed',
            'meeting_disposition' => 'cancelled',
            'evaluation_outcome' => 'unfit',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '12:00',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Questionnaire Count Parent',
            'parent_email' => 'questionnaire-count@example.test',
        ], [
            'child_name' => 'Questionnaire Count Child',
            'workflow_status' => 'questionnaire_answer_received',
            'consultation_status' => 'questionnaire_answer_received',
            'meeting_disposition' => null,
        ]);

        $stats = Livewire::test(BookingList::class)->instance()->stats();
        $statsByLabel = collect($stats)->keyBy('label');

        $this->assertSame(1, $statsByLabel['Pending']['value']);
        $this->assertSame(1, $statsByLabel['Confirmed']['value']);
        $this->assertSame(1, $statsByLabel['Follow-Up Due']['value']);
        $this->assertSame(1, $statsByLabel['Potential Later']['value']);
        $this->assertSame(1, $statsByLabel['Questionnaire']['value']);
        $this->assertSame(1, $statsByLabel['Fit / Ready']['value']);
        $this->assertSame(1, $statsByLabel['Completed']['value']);
        $this->assertSame(1, $statsByLabel['Cancelled']['value']);
    }

    public function test_potential_later_children_remain_visible_in_default_queue_and_support_dedicated_filter(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createBookingChild([
            'parent_name' => 'PL Lane Parent',
            'parent_email' => 'pl-lane@example.test',
        ], [
            'child_name' => 'PL Lane Child',
            'workflow_status' => 'pending',
            'consultation_status' => 'pending',
            'meeting_disposition' => 'no_meeting_required',
            'evaluation_outcome' => 'PL',
            'followup_date' => now()->addWeek()->format('Y-m-d H:i:s'),
        ]);

        $this->createBookingChild([
            'parent_name' => 'Visible Pending Parent',
            'parent_email' => 'visible-pending@example.test',
        ], [
            'child_name' => 'Visible Pending Child',
            'workflow_status' => 'pending',
            'consultation_status' => 'pending',
            'meeting_disposition' => null,
            'evaluation_outcome' => 'undecided',
        ]);

        Livewire::test(BookingList::class)
            ->assertSee('PL Lane Child')
            ->assertSee('Visible Pending Child')
            ->call('filterBy', 'queue_state', 'potential_later')
            ->assertSet('filterQueueState', 'potential_later')
            ->assertSee('PL Lane Child')
            ->assertDontSee('Visible Pending Child');
    }

    public function test_booking_list_shows_intake_review_badge_when_pending_reviews_exist(): void
    {
        $this->actingAs(User::factory()->create());

        DB::table('booking_intake_review')->insert([
            'parent_name' => 'Review Parent',
            'parent_email' => 'review-parent@example.test',
            'parent_phone' => '201000333444',
            'detection_reason' => 'mixed_children',
            'status' => 'pending_review',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Livewire::test(BookingList::class)
            ->assertSee('Intake Review')
            ->assertSeeHtml('queue-page-link__badge')
            ->assertSee('1');
    }

    public function test_filter_by_switches_between_active_and_terminal_queue_views(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createBookingChild([
            'parent_name' => 'Pending Filter Parent',
            'parent_email' => 'pending-filter@example.test',
        ], [
            'child_name' => 'Pending Filter Child',
            'workflow_status' => 'pending',
            'consultation_status' => 'pending',
            'meeting_disposition' => null,
        ]);

        $this->createBookingChild([
            'parent_name' => 'Completed Filter Parent',
            'parent_email' => 'completed-filter@example.test',
        ], [
            'child_name' => 'Completed Filter Child',
            'workflow_status' => 'confirmed',
            'consultation_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '11:30',
        ]);

        Livewire::test(BookingList::class)
            ->call('filterBy', 'queue_state', 'completed')
            ->assertSet('filterQueueState', 'completed')
            ->assertSee('Completed Filter Child')
            ->assertDontSee('Pending Filter Child')
            ->call('filterBy', 'queue_state', 'completed')
            ->assertSet('filterQueueState', 'all')
            ->assertSee('Completed Filter Child')
            ->assertSee('Pending Filter Child');
    }

    public function test_booking_list_combines_queue_and_evaluation_filters_on_the_same_child(): void
    {
        $this->actingAs(User::factory()->create());

        $firstChild = $this->createBookingChild([
            'parent_name' => 'Mixed Filter Parent',
            'parent_email' => 'mixed-filter@example.test',
            'booking_reference' => 'BK-MIXED-2001',
        ], [
            'child_name' => 'Fit Completed Child',
            'workflow_status' => 'confirmed',
            'consultation_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'evaluation_outcome' => 'fit',
            'evaluation_status' => 'fit',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '10:00',
        ]);

        BookingChild::create([
            'booking_id' => $firstChild->booking_id,
            'child_name' => 'Pending Undecided Child',
            'child_age' => 9,
            'child_grade' => 1,
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
            'current_school' => 'Current School',
            'student_id' => null,
            'notes' => 'Sibling child note',
            'scheduled_date' => null,
            'scheduled_time' => null,
            'sort_order' => 2,
            'updated_by' => null,
        ]);

        $this->createBookingChild([
            'parent_name' => 'Matching Filter Parent',
            'parent_email' => 'matching-filter@example.test',
            'booking_reference' => 'BK-MATCH-2002',
        ], [
            'child_name' => 'Fit Pending Child',
            'workflow_status' => 'pending',
            'consultation_status' => 'pending',
            'meeting_disposition' => null,
            'evaluation_outcome' => 'fit',
            'evaluation_status' => 'fit',
        ]);

        Livewire::test(BookingList::class)
            ->set('filterEvaluation', 'fit')
            ->set('filterQueueState', 'pending_active')
            ->assertSee('Matching Filter Parent')
            ->assertSee('Fit Pending Child')
            ->assertDontSee('Mixed Filter Parent')
            ->assertDontSee('Fit Completed Child')
            ->assertDontSee('Pending Undecided Child');
    }

    public function test_transfer_ready_stats_and_filter_only_include_children_that_can_actually_transfer(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createBookingChild([
            'parent_name' => 'Ready Transfer Parent',
            'parent_email' => 'ready-transfer@example.test',
            'booking_reference' => 'BK-TRANSFER-3001',
        ], [
            'child_name' => 'Ready Transfer Child',
            'workflow_status' => 'confirmed',
            'consultation_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'evaluation_outcome' => 'fit',
            'evaluation_status' => 'fit',
            'service_interests' => ['Help Me Study'],
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:00',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Fallback Transfer Parent',
            'parent_email' => 'fallback-transfer@example.test',
            'booking_reference' => 'BK-TRANSFER-3001-FALLBACK',
            'child_grade' => 1,
            'school_system' => 'British',
            'service_interest' => 'Help Me Study',
        ], [
            'child_name' => 'Fallback Transfer Child',
            'workflow_status' => 'confirmed',
            'consultation_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'evaluation_outcome' => 'fit',
            'evaluation_status' => 'fit',
            'child_grade' => null,
            'school_system' => null,
            'service_interests' => [],
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '09:30',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Blocked Transfer Parent',
            'parent_email' => 'blocked-transfer@example.test',
            'booking_reference' => 'BK-TRANSFER-3002',
        ], [
            'child_name' => 'Blocked Transfer Child',
            'workflow_status' => 'confirmed',
            'consultation_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'evaluation_outcome' => 'fit',
            'evaluation_status' => 'fit',
            'service_interests' => ['Unknown Service'],
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '10:00',
        ]);

        $stats = Livewire::test(BookingList::class)->instance()->stats();
        $statsByLabel = collect($stats)->keyBy('label');

        $this->assertSame(2, $statsByLabel['Fit / Ready']['value']);

        Livewire::test(BookingList::class)
            ->call('filterBy', 'queue_state', 'transfer_ready')
            ->assertSee('Ready Transfer Parent')
            ->assertSee('Ready Transfer Child')
            ->assertSee('Fallback Transfer Parent')
            ->assertSee('Fallback Transfer Child')
            ->assertDontSee('Blocked Transfer Parent')
            ->assertDontSee('Blocked Transfer Child');
    }

    public function test_booking_list_search_matches_parent_phone(): void
    {
        $this->actingAs(User::factory()->create());

        $matchingChild = $this->createBookingChild([
            'parent_name' => 'Phone Match Parent',
            'parent_email' => 'phone-match@example.test',
            'parent_phone' => '201000111222',
        ], [
            'child_name' => 'Phone Match Child',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Other Parent',
            'parent_email' => 'other@example.test',
            'parent_phone' => '201999888777',
        ], [
            'child_name' => 'Other Child',
        ]);

        Livewire::test(BookingList::class)
            ->set('search', '111222')
            ->assertSee('Phone Match Child')
            ->assertDontSee('Other Child');

        $matchingChild->refresh();
        $this->assertSame('201000111222', $matchingChild->booking->parent_phone);
    }

    public function test_booking_list_search_matches_booking_reference(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createBookingChild([
            'parent_name' => 'Reference Match Parent',
            'parent_email' => 'reference-match@example.test',
            'parent_phone' => '201000111222',
            'booking_reference' => 'BK-REFERENCE-9001',
        ], [
            'child_name' => 'Reference Match Child',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Other Reference Parent',
            'parent_email' => 'other-reference@example.test',
            'parent_phone' => '201999888777',
            'booking_reference' => 'BK-REFERENCE-9002',
        ], [
            'child_name' => 'Other Reference Child',
        ]);

        Livewire::test(BookingList::class)
            ->set('search', 'BK-REFERENCE-9001')
            ->assertSee('Reference Match Child')
            ->assertDontSee('Other Reference Child');
    }

    public function test_booking_list_search_matches_child_name(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createBookingChild([
            'parent_name' => 'Child Search Parent',
            'parent_email' => 'child-search-parent@example.test',
            'booking_reference' => 'BK-CHILD-9001',
        ], [
            'child_name' => 'Unique Child Search Name',
        ]);

        $this->createBookingChild([
            'parent_name' => 'Unrelated Child Search Parent',
            'parent_email' => 'unrelated-child-search@example.test',
            'booking_reference' => 'BK-CHILD-9002',
        ], [
            'child_name' => 'Other Child Search Name',
        ]);

        Livewire::test(BookingList::class)
            ->set('search', 'Unique Child Search')
            ->assertSee('Child Search Parent')
            ->assertSee('Unique Child Search Name')
            ->assertDontSee('Unrelated Child Search Parent')
            ->assertDontSee('Other Child Search Name');
    }

    public function test_booking_list_hides_existing_family_badge_when_context_exists_but_no_navigation_seed_is_available(): void
    {
        $this->actingAs(User::factory()->create());

        $booking = Booking::create([
            'parent_name' => null,
            'parent_email' => null,
            'parent_phone' => null,
            'booking_reference' => 'BK-BLANK-EXISTING',
            'status' => 'pending',
            'service_interest' => 'Help Me Study',
            'parent_id' => 99,
        ]);

        BookingChild::create([
            'booking_id' => $booking->id,
            'child_name' => 'Hidden Context Child',
            'child_age' => 9,
            'child_grade' => 1,
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
            'sort_order' => 1,
            'updated_by' => null,
        ]);

        Livewire::test(BookingList::class)
            ->assertDontSee('Existing family');
    }

    public function test_sibling_intake_badge_links_to_active_booking_list_for_non_transferred_family_context(): void
    {
        $this->actingAs(User::factory()->create());

        $firstChild = $this->createBookingChild([
            'parent_name' => 'Sibling Active Parent',
            'parent_email' => 'siblings-active@example.test',
            'parent_phone' => '201000111222',
            'booking_reference' => 'BK-SIBLING-ACTIVE',
        ], [
            'child_name' => 'First Sibling',
        ]);

        BookingChild::create([
            'booking_id' => $firstChild->booking_id,
            'child_name' => 'Second Sibling',
            'child_age' => 7,
            'child_grade' => 1,
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

        Livewire::test(BookingList::class)
            ->assertSee('Sibling intake')
            ->assertSee(route('admin.bookings.livewire', ['search' => 'siblings-active@example.test']), false);
    }

    public function test_sibling_intake_badge_links_to_transferred_family_page_when_parent_id_exists(): void
    {
        $this->actingAs(User::factory()->create());

        $firstChild = $this->createBookingChild([
            'parent_name' => 'Sibling Transferred Parent',
            'parent_email' => 'siblings-transferred@example.test',
            'parent_phone' => '201000111223',
            'booking_reference' => 'BK-SIBLING-TRANSFERRED',
            'parent_id' => 44,
        ], [
            'child_name' => 'Transferred First Sibling',
        ]);

        BookingChild::create([
            'booking_id' => $firstChild->booking_id,
            'child_name' => 'Transferred Second Sibling',
            'child_age' => 8,
            'child_grade' => 1,
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

        Livewire::test(BookingList::class)
            ->assertSee('Sibling intake')
            ->assertSee(route('admin.bookings.transferred', ['search' => 'siblings-transferred@example.test']), false);
    }

    public function test_sibling_intake_badge_stays_non_linked_when_no_navigation_seed_is_available(): void
    {
        $this->actingAs(User::factory()->create());

        $booking = Booking::create([
            'parent_name' => null,
            'parent_email' => null,
            'parent_phone' => null,
            'booking_reference' => 'BK-SIBLING-BLANK',
            'status' => 'pending',
            'service_interest' => 'Help Me Study',
        ]);

        BookingChild::create([
            'booking_id' => $booking->id,
            'child_name' => 'First Blank Sibling',
            'child_age' => 9,
            'child_grade' => 1,
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
            'sort_order' => 1,
            'updated_by' => null,
        ]);

        BookingChild::create([
            'booking_id' => $booking->id,
            'child_name' => 'Second Blank Sibling',
            'child_age' => 8,
            'child_grade' => 1,
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

        Livewire::test(BookingList::class)
            ->assertSee('Sibling intake')
            ->assertDontSee('View Siblings', false)
            ->assertDontSee('admin/bookings?search=', false)
            ->assertDontSee('admin/bookings/transferred?search=', false);
    }

    public function test_parent_edit_keeps_dual_child_milestones_and_consultation_details_unchanged(): void
    {
        $this->actingAs(User::factory()->create());

        $booking = Booking::create([
            'parent_name' => 'Original Parent',
            'parent_email' => 'original@example.test',
            'parent_phone' => '201000111222',
            'booking_reference' => 'BK-PARENT-2001',
            'consultation_type' => 'online',
            'current_school' => 'Legacy School',
            'school_system' => 'British',
            'service_interest' => 'Help Me Study',
            'status' => 'pending',
            'notes' => 'Original booking note',
        ]);

        $firstChild = BookingChild::create([
            'booking_id' => $booking->id,
            'child_name' => 'First Child',
            'child_age' => 11,
            'child_grade' => 1,
            'school_system' => 'British',
            'service_interests' => ['Help Me Study'],
            'consultation_status' => 'confirmed',
            'workflow_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'meeting_disposition_reason' => null,
            'evaluation_status' => 'fit',
            'evaluation_outcome' => 'fit',
            'consultation_type' => 'online',
            'meeting_link' => 'https://meet.example.com/first-child',
            'meeting_address' => null,
            'transfer_status' => 'not_transferred',
            'followup_date' => null,
            'current_school' => 'First School',
            'student_id' => null,
            'notes' => 'First child note',
            'scheduled_date' => '2026-04-15',
            'scheduled_time' => '10:30',
            'sort_order' => 1,
            'updated_by' => null,
        ]);

        $secondChild = BookingChild::create([
            'booking_id' => $booking->id,
            'child_name' => 'Second Child',
            'child_age' => 9,
            'child_grade' => 2,
            'school_system' => 'British',
            'service_interests' => ['SAT / ACT Preparation'],
            'consultation_status' => 'followup',
            'workflow_status' => 'followup_required',
            'meeting_disposition' => 'cancelled',
            'meeting_disposition_reason' => null,
            'evaluation_status' => 'unfit',
            'evaluation_outcome' => 'unfit',
            'consultation_type' => 'in-person',
            'meeting_link' => null,
            'meeting_address' => 'Second Child Address',
            'transfer_status' => 'not_transferred',
            'followup_date' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'current_school' => 'Second School',
            'student_id' => null,
            'notes' => 'Second child note',
            'scheduled_date' => '2026-04-16',
            'scheduled_time' => '14:00',
            'sort_order' => 2,
            'updated_by' => null,
        ]);

        Livewire::test(BookingParentEdit::class, ['booking' => $booking])
            ->set('parentName', 'Updated Parent')
            ->set('parentEmail', 'updated@example.test')
            ->set('parentPhone', '201123456789')
            ->set('bookingReference', 'BK-PARENT-UPDATED')
            ->set('notes', 'Updated booking note')
            ->call('save')
            ->assertHasNoErrors();

        $booking->refresh();
        $firstChild->refresh();
        $secondChild->refresh();

        $this->assertSame('Updated Parent', $booking->parent_name);
        $this->assertSame('updated@example.test', $booking->parent_email);
        $this->assertSame('201123456789', $booking->parent_phone);
        $this->assertSame('BK-PARENT-UPDATED', $booking->booking_reference);
        $this->assertSame('Updated booking note', $booking->notes);

        $this->assertSame('confirmed', $firstChild->workflow_status);
        $this->assertSame('completed', $firstChild->meeting_disposition);
        $this->assertSame('fit', $firstChild->evaluation_outcome);
        $this->assertSame('online', $firstChild->consultation_type);
        $this->assertSame('https://meet.example.com/first-child', $firstChild->meeting_link);
        $this->assertSame('2026-04-15', optional($firstChild->scheduled_date)->format('Y-m-d') ?? $firstChild->scheduled_date);
        $this->assertSame('10:30', $firstChild->scheduled_time);

        $this->assertSame('followup_required', $secondChild->workflow_status);
        $this->assertSame('cancelled', $secondChild->meeting_disposition);
        $this->assertSame('unfit', $secondChild->evaluation_outcome);
        $this->assertSame('in-person', $secondChild->consultation_type);
        $this->assertSame('Second Child Address', $secondChild->meeting_address);
        $this->assertSame('2026-04-16', optional($secondChild->scheduled_date)->format('Y-m-d') ?? $secondChild->scheduled_date);
        $this->assertSame('14:00', $secondChild->scheduled_time);
    }

    public function test_meeting_link_required_when_online(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'confirmed')
            ->set('scheduledDate', '2026-04-09')
            ->set('scheduledTime', '13:00')
            ->set('consultationInPerson', false)
            ->set('meetingLink', null)
            ->call('save')
            ->assertHasErrors(['meetingLink']);
    }

    public function test_current_school_is_required_when_saving_child_workflow(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('currentSchool', '')
            ->call('save')
            ->assertHasErrors(['currentSchool']);
    }

    public function test_scheduled_time_must_use_15_minute_intervals(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'confirmed')
            ->set('consultationType', 'online')
            ->set('meetingLink', 'https://meet.example.com/quarter-hours')
            ->set('scheduledDate', '2026-04-09')
            ->set('scheduledTime', '13:01')
            ->call('save')
            ->assertHasErrors(['scheduledTime']);
    }

    public function test_legacy_non_url_booking_meeting_link_does_not_block_confirmed_save(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild([
            'consultation_type' => 'online',
            'meeting_link' => 'Zoom link will be provided upon confirmation',
        ], [
            'consultation_type' => 'undecided',
            'meeting_link' => null,
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->assertSet('consultationType', 'online')
            ->assertSet('meetingLink', null)
            ->set('workflowStatus', 'confirmed')
            ->set('meetingLink', 'https://meet.example.com/confirmed')
            ->set('scheduledDate', '2026-04-19')
            ->set('scheduledTime', '16:00')
            ->call('save')
            ->assertHasNoErrors();

        $child->refresh();

        $this->assertSame('confirmed', $child->workflow_status);
        $this->assertSame('https://meet.example.com/confirmed', $child->meeting_link);
    }

    public function test_confirmation_email_booking_uses_child_in_person_address_without_booking_zoom_link(): void
    {
        $child = $this->createBookingChild([
            'consultation_type' => 'online',
            'meeting_link' => 'https://meet.example.com/legacy-booking',
            'meeting_address' => null,
        ], [
            'consultation_type' => 'in-person',
            'meeting_link' => null,
            'meeting_address' => '5th Settlement',
            'scheduled_date' => '2026-04-19',
            'scheduled_time' => '18:00',
        ]);

        $service = app(BookingConfirmationService::class);

        $emailBooking = Closure::bind(function () use ($service, $child) {
            return $service->buildEmailBooking($child->booking, $child);
        }, null, BookingConfirmationService::class)();

        $this->assertSame('in-person', $emailBooking->consultation_type);
        $this->assertNull($emailBooking->meeting_link);
        $this->assertSame('5th Settlement', $emailBooking->meeting_address);
    }

    public function test_confirmation_email_uses_sort_order_when_children_relation_is_loaded(): void
    {
        $firstChild = $this->createBookingChild([], [
            'child_name' => 'First Sorted Child',
            'scheduled_date' => '2026-04-19',
            'scheduled_time' => '18:00',
            'sort_order' => 1,
        ]);

        BookingChild::create([
            'booking_id' => $firstChild->booking_id,
            'child_name' => 'Loaded First Child',
            'child_age' => 10,
            'child_grade' => 2,
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
            'current_school' => 'Current School',
            'student_id' => null,
            'notes' => 'Child note',
            'scheduled_date' => '2026-04-20',
            'scheduled_time' => '19:00',
            'sort_order' => 2,
            'updated_by' => null,
        ]);

        $booking = $firstChild->booking;
        $booking->setRelation(
            'children',
            BookingChild::query()
                ->where('booking_id', $booking->id)
                ->orderByDesc('sort_order')
                ->get()
        );

        $service = app(BookingConfirmationService::class);

        $emailBooking = Closure::bind(function () use ($service, $booking) {
            return $service->buildEmailBooking($booking);
        }, null, BookingConfirmationService::class)();

        $this->assertSame('First Sorted Child', $emailBooking->child_name);
        $this->assertSame('2026-04-19', $emailBooking->consultation_date->toDateString());
        $this->assertSame('18:00', $emailBooking->consultation_time);
    }

    public function test_booking_list_shows_latest_email_status_badges_per_child(): void
    {
        $this->actingAs(User::factory()->create());

        $child = $this->createBookingChild();

        DB::table('booking_child_emails')->insert([
            [
                'booking_child_id' => $child->id,
                'email_type' => 'confirmation_parent',
                'status' => 'queued',
                'last_attempt_at' => now()->subMinutes(10),
                'last_sent_at' => null,
                'last_error_message' => null,
                'triggered_by' => null,
                'created_at' => now()->subMinutes(10),
                'updated_at' => now()->subMinutes(10),
            ],
            [
                'booking_child_id' => $child->id,
                'email_type' => 'confirmation_parent',
                'status' => 'failed',
                'last_attempt_at' => now()->subMinutes(2),
                'last_sent_at' => null,
                'last_error_message' => 'SMTP down',
                'triggered_by' => null,
                'created_at' => now()->subMinutes(2),
                'updated_at' => now()->subMinutes(2),
            ],
            [
                'booking_child_id' => $child->id,
                'email_type' => 'confirmation_admin',
                'status' => 'sent',
                'last_attempt_at' => now()->subMinute(),
                'last_sent_at' => now()->subMinute(),
                'last_error_message' => null,
                'triggered_by' => null,
                'created_at' => now()->subMinute(),
                'updated_at' => now()->subMinute(),
            ],
            [
                'booking_child_id' => $child->id,
                'email_type' => 'transfer_welcome',
                'status' => 'failed',
                'last_attempt_at' => now()->subSeconds(30),
                'last_sent_at' => null,
                'last_error_message' => 'Retired transfer path',
                'triggered_by' => null,
                'created_at' => now()->subSeconds(30),
                'updated_at' => now()->subSeconds(30),
            ],
        ]);

        Livewire::test(BookingList::class)
            ->assertSee('Parent Confirm')
            ->assertSee('Failed')
            ->assertSee('Admin Confirm')
            ->assertSee('Sent')
            ->assertSee('Transfer Welcome (Retired): Retired');
    }

    public function test_email_status_service_preserves_not_sent_placeholders_for_missing_types(): void
    {
        $this->actingAs(User::factory()->create());

        $child = $this->createBookingChild();

        DB::table('booking_child_emails')->insert([
            'booking_child_id' => $child->id,
            'email_type' => 'confirmation_parent',
            'status' => 'sent',
            'last_attempt_at' => now()->subMinute(),
            'last_sent_at' => now()->subMinute(),
            'last_error_message' => null,
            'triggered_by' => null,
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);

        $statuses = app(BookingChildEmailService::class)->latestStatusesForChild($child);

        $this->assertSame(BookingChildEmailService::TRACKED_EMAIL_TYPES, $statuses->keys()->all());
        $this->assertSame('sent', $statuses->get('confirmation_parent')?->status);
        $this->assertSame('not_sent', $statuses->get('confirmation_admin')?->status);
        $this->assertSame('not_sent', $statuses->get('questionnaire_parent')?->status);
        $this->assertSame($child->id, $statuses->get('transfer_welcome')?->booking_child_id);
    }

    public function test_booking_child_edit_shows_failed_email_slots_with_resend_action(): void
    {
        $this->actingAs(User::factory()->create());

        $child = $this->createBookingChild();

        DB::table('booking_child_emails')->insert([
            [
                'booking_child_id' => $child->id,
                'email_type' => 'confirmation_parent',
                'status' => 'failed',
                'last_attempt_at' => now()->subMinutes(3),
                'last_sent_at' => null,
                'last_error_message' => 'Mailbox unavailable',
                'triggered_by' => null,
                'created_at' => now()->subMinutes(3),
                'updated_at' => now()->subMinutes(3),
            ],
            [
                'booking_child_id' => $child->id,
                'email_type' => 'confirmation_admin',
                'status' => 'sent',
                'last_attempt_at' => now()->subMinutes(2),
                'last_sent_at' => now()->subMinutes(2),
                'last_error_message' => null,
                'triggered_by' => null,
                'created_at' => now()->subMinutes(2),
                'updated_at' => now()->subMinutes(2),
            ],
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->assertSee('Email Delivery Status')
            ->assertSee('Parent Confirmation')
            ->assertSee('Failed')
            ->assertSee('Admin Confirmation')
            ->assertSee('Sent')
            ->assertSee('Resend')
            ->assertSee('Questionnaire Parent')
            ->assertSee('Not Sent');
    }

    public function test_booking_child_edit_marks_retired_transfer_email_slots_without_resend_action(): void
    {
        $this->actingAs(User::factory()->create());

        $child = $this->createBookingChild([], [
            'transfer_status' => 'transferred',
            'student_id' => 123,
        ]);

        DB::table('booking_child_emails')->insert([
            'booking_child_id' => $child->id,
            'email_type' => 'transfer_welcome',
            'status' => 'failed',
            'last_attempt_at' => now()->subMinutes(3),
            'last_sent_at' => null,
            'last_error_message' => 'Legacy transfer delivery failed',
            'triggered_by' => null,
            'created_at' => now()->subMinutes(3),
            'updated_at' => now()->subMinutes(3),
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->assertSee('Transfer Welcome (Retired)')
            ->assertSee('Retired')
            ->assertDontSee('Resend');
    }

    public function test_resending_failed_parent_confirmation_creates_a_new_parent_only_attempt(): void
    {
        $this->actingAs(User::factory()->create());

        $child = $this->createBookingChild([], [
            'workflow_status' => 'confirmed',
            'consultation_status' => 'confirmed',
            'consultation_type' => 'online',
            'meeting_link' => 'https://meet.example.com/resend-parent',
            'scheduled_date' => '2026-04-20',
            'scheduled_time' => '15:00',
        ]);

        DB::table('booking_child_emails')->insert([
            'booking_child_id' => $child->id,
            'email_type' => 'confirmation_parent',
            'status' => 'failed',
            'last_attempt_at' => now()->subMinutes(5),
            'last_sent_at' => null,
            'last_error_message' => 'SMTP timeout',
            'triggered_by' => null,
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);

        $emailService = Mockery::mock(BookingChildEmailService::class)->makePartial();
        $emailService->shouldReceive('resend')
            ->once()
            ->withArgs(fn (BookingChild $resendChild, string $emailType): bool => $resendChild->is($child) && $emailType === 'confirmation_parent')
            ->passthru();
        $this->app->instance(BookingChildEmailService::class, $emailService);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->call('resendEmail', $child->id, 'confirmation_parent')
            ->assertHasNoErrors();

        $this->assertSame(2, DB::table('booking_child_emails')
            ->where('booking_child_id', $child->id)
            ->where('email_type', 'confirmation_parent')
            ->count());

        $this->assertSame(0, DB::table('booking_child_emails')
            ->where('booking_child_id', $child->id)
            ->where('email_type', 'confirmation_admin')
            ->count());

        $this->assertDatabaseHas('booking_child_emails', [
            'booking_child_id' => $child->id,
            'email_type' => 'confirmation_parent',
            'status' => 'resent',
        ]);
    }

    public function test_service_interests_initially_load_from_persisted_child_values_only(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild([
            'service_interest' => 'Help Me Study,SAT / ACT Preparation',
        ], [
            'service_interests' => ['IB Private Tutoring'],
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->assertSet('serviceInterests', ['IB Private Tutoring'])
            ->assertDontSee('checked value="Help Me Study"', false);
    }

    public function test_child_edit_service_options_filter_internal_values_consistently(): void
    {
        $this->actingAs(User::factory()->create());

        DB::table('services_types')->insert([
            [
                'title' => 'Custom Coaching',
                'value' => 'Custom Coaching',
                'info' => null,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Not Sure',
                'value' => 'Not Sure',
                'info' => null,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $child = $this->createBookingChild([], [
            'service_interests' => ['Help Me Study'],
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->assertSee('Custom Coaching')
            ->assertDontSee('Not Sure');
    }

    public function test_child_edit_does_not_fallback_when_configured_services_are_not_child_facing(): void
    {
        $this->actingAs(User::factory()->create());

        DB::table('services_types')->delete();

        DB::table('services_types')->insert([
            [
                'title' => '(Parents) Course',
                'value' => '(Parents) Course',
                'info' => null,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Not Sure',
                'value' => 'Not Sure',
                'info' => null,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $child = $this->createBookingChild([], [
            'service_interests' => [],
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->assertSee('No active child-facing services are configured.')
            ->assertDontSee('value="Help Me Study"', false)
            ->assertDontSee('value="Help Me Read"', false)
            ->assertDontSee('value="IB Private Tutoring"', false)
            ->assertDontSee('value="SAT / ACT Preparation"', false)
            ->assertDontSee('(Parents) Course')
            ->assertDontSee('Not Sure');
    }

    public function test_service_interests_cannot_be_saved_empty(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild([], [
            'service_interests' => ['Help Me Study'],
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('serviceInterests', [])
            ->call('save')
            ->assertHasErrors(['serviceInterests']);

        $child->refresh();

        $this->assertSame(['Help Me Study'], $child->service_interests);
    }

    public function test_meeting_disposition_no_meeting_required_with_reason(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('meetingDisposition', 'no_meeting_required')
            ->set('meetingDispositionReason', 'Parent already completed the intake call externally.')
            ->call('save')
            ->assertHasNoErrors();

        $child->refresh();

        $this->assertSame('no_meeting_required', $child->meeting_disposition);
        $this->assertSame('Parent already completed the intake call externally.', $child->meeting_disposition_reason);
        $this->assertDatabaseHas('booking_child_audit_log', [
            'booking_child_id' => $child->id,
            'field_name' => 'meeting_disposition_reason',
            'changed_by' => $admin->id,
        ]);
    }

    public function test_meeting_disposition_cancelled_requires_confirmed_or_followup_workflow(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'pending')
            ->set('meetingDisposition', 'cancelled')
            ->set('scheduledDate', '2026-04-20')
            ->set('scheduledTime', '14:00')
            ->call('save')
            ->assertHasErrors(['meetingDisposition']);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'followup_required')
            ->set('followupDate', now()->addDays(10)->toDateString())
            ->set('consultationType', 'online')
            ->set('meetingLink', 'https://meet.example.com/followup-cancelled')
            ->set('meetingDisposition', 'cancelled')
            ->set('scheduledDate', '2026-04-20')
            ->set('scheduledTime', '14:00')
            ->call('save')
            ->assertHasNoErrors();

        $child->refresh();

        $this->assertSame('followup_required', $child->workflow_status);
        $this->assertSame('cancelled', $child->meeting_disposition);
        $this->assertSame('cancelled', $child->consultation_status);
    }

    public function test_meeting_disposition_completed_requires_confirmed_workflow(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'followup_required')
            ->set('followupDate', now()->addDays(14)->toDateString())
            ->set('meetingDisposition', 'completed')
            ->set('scheduledDate', '2026-04-21')
            ->set('scheduledTime', '15:30')
            ->call('save')
            ->assertHasErrors(['meetingDisposition']);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'confirmed')
            ->set('consultationType', 'online')
            ->set('meetingLink', 'https://meet.example.com/completed')
            ->set('meetingDisposition', 'completed')
            ->set('scheduledDate', '2026-04-21')
            ->set('scheduledTime', '15:30')
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_cancelled_or_completed_meetings_require_scheduled_date_and_time(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'confirmed')
            ->set('meetingDisposition', 'completed')
            ->set('scheduledDate', null)
            ->set('scheduledTime', null)
            ->call('save')
            ->assertHasErrors(['scheduledDate', 'scheduledTime']);
    }

    public function test_followup_required_and_pl_are_mutually_exclusive_in_editor_state(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'followup_required')
            ->set('evaluationOutcome', 'PL')
            ->assertSet('workflowStatus', 'pending')
            ->set('workflowStatus', 'followup_required')
            ->assertSet('evaluationOutcome', 'undecided');
    }

    public function test_followup_required_shows_scheduled_and_followup_fields(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'followup_required')
            ->assertSee('Scheduled Date')
            ->assertSee('Scheduled Time')
            ->assertSee('Follow-Up Date & Time', false);
    }

    public function test_confirmed_keeps_meeting_disposition_not_set_until_schedule_exists(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'confirmed')
            ->assertDontSee('Meeting Completed')
            ->assertDontSee('Meeting Cancelled')
            ->set('scheduledDate', '2026-04-22')
            ->set('scheduledTime', '11:00')
            ->assertSee('Meeting Completed')
            ->assertSee('Meeting Cancelled');
    }

    public function test_followup_required_keeps_meeting_disposition_not_set_until_schedule_exists(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'followup_required')
            ->assertDontSee('Meeting Cancelled')
            ->set('scheduledDate', '2026-04-22')
            ->set('scheduledTime', '11:00')
            ->assertSee('Meeting Cancelled');
    }

    public function test_consultation_type_stays_undecided_until_admin_chooses_a_mode(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild([], [
            'consultation_type' => 'undecided',
            'meeting_link' => null,
            'meeting_address' => null,
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->assertSet('consultationType', 'undecided')
            ->assertDontSeeHtml('>Meeting Link<')
            ->assertDontSeeHtml('>Meeting Address<');
    }

    public function test_consultation_mode_toggle_updates_the_meeting_details_box(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild([], [
            'consultation_type' => 'online',
            'meeting_link' => 'https://meet.example.com/toggle',
            'meeting_address' => null,
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->assertSeeHtml('>Meeting Link<')
            ->assertDontSeeHtml('>Meeting Address<')
            ->call('selectConsultationType', 'in-person')
            ->assertSet('consultationType', 'in-person')
            ->assertSeeHtml('>Meeting Address<')
            ->assertDontSeeHtml('>Meeting Link<')
            ->call('selectConsultationType', 'online')
            ->assertSet('consultationType', 'online')
            ->assertSeeHtml('>Meeting Link<')
            ->assertDontSeeHtml('>Meeting Address<');
    }

    public function test_clearing_meeting_disposition_does_not_clear_existing_schedule(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild([], [
            'workflow_status' => 'confirmed',
            'consultation_type' => 'online',
            'meeting_link' => 'https://meet.example.com/scheduled',
            'scheduled_date' => '2026-04-22',
            'scheduled_time' => '11:00',
            'meeting_disposition' => 'completed',
            'evaluation_outcome' => 'fit',
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('meetingDisposition', null)
            ->assertSet('scheduledDate', '2026-04-22')
            ->assertSet('scheduledTime', '11:00')
            ->assertSet('evaluationOutcome', 'undecided');
    }

    public function test_clearing_meeting_disposition_back_to_not_set_does_not_create_a_blank_option(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild([], [
            'workflow_status' => 'confirmed',
            'consultation_type' => 'online',
            'meeting_link' => 'https://meet.example.com/disposition-reset',
            'scheduled_date' => '2026-04-22',
            'scheduled_time' => '11:00',
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('meetingDisposition', 'completed')
            ->set('evaluationOutcome', 'fit')
            ->set('meetingDisposition', '')
            ->assertSet('meetingDisposition', null)
            ->assertSet('evaluationOutcome', 'undecided')
            ->assertDontSeeHtml('<option value=""></option>');
    }

    public function test_existing_followup_datetime_stays_visible_on_reopen(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild([], [
            'workflow_status' => 'pending',
            'followup_date' => now()->addWeek()->format('Y-m-d H:i:s'),
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->assertSee('Follow-Up Date & Time', false);
    }

    public function test_evaluation_outcome_requires_meeting_disposition_first(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'pending')
            ->set('meetingDisposition', null)
            ->set('evaluationOutcome', 'fit')
            ->call('save')
            ->assertHasErrors(['evaluationOutcome']);
    }

    public function test_legacy_questionnaire_state_can_still_save_non_questionnaire_fields(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild([], [
            'workflow_status' => 'questionnaire_sent',
            'consultation_status' => 'questionnaire_sent',
            'notes' => 'Before save',
        ]);

        Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('notes', 'Updated while preserving reserved questionnaire status')
            ->call('save')
            ->assertHasNoErrors();

        $child->refresh();

        $this->assertSame('questionnaire_sent', $child->consultation_status);
        $this->assertSame('questionnaire_sent', $child->workflow_status);
        $this->assertSame('Updated while preserving reserved questionnaire status', $child->notes);
    }

    public function test_stale_child_save_is_rejected_when_updated_at_mismatch_occurs(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        $component = Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'confirmed')
            ->set('consultationType', 'online')
            ->set('meetingLink', 'https://meet.example.com/stale')
            ->set('scheduledDate', '2026-04-10')
            ->set('scheduledTime', '12:00');

        DB::table('booking_children')
            ->where('id', $child->id)
            ->update([
                'notes' => 'Updated elsewhere',
                'updated_at' => now()->addMinute(),
            ]);

        $component
            ->call('save')
            ->assertHasErrors(['stale']);

        $child->refresh();

        $this->assertSame('pending', $child->workflow_status);
        $this->assertNull($child->meeting_link);
    }

    public function test_stale_child_save_does_not_trigger_confirmation_email(): void
    {
        $this->actingAs(User::factory()->create());
        $child = $this->createBookingChild();

        $confirmationService = Mockery::mock(BookingConfirmationService::class);
        $confirmationService->shouldNotReceive('sendConfirmationEmails');
        $this->app->instance(BookingConfirmationService::class, $confirmationService);

        $component = Livewire::test(BookingChildEdit::class, ['bookingChild' => $child])
            ->set('workflowStatus', 'confirmed')
            ->set('consultationType', 'online')
            ->set('meetingLink', 'https://meet.example.com/no-send')
            ->set('scheduledDate', '2026-04-10')
            ->set('scheduledTime', '09:30');

        DB::table('booking_children')
            ->where('id', $child->id)
            ->update([
                'notes' => 'Changed by another admin',
                'updated_at' => now()->addMinutes(2),
            ]);

        $component
            ->call('save')
            ->assertHasErrors(['stale']);
    }

    public function test_child_editor_skips_audit_queries_until_the_audit_trail_is_explicitly_loaded(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $child = $this->createBookingChild();

        DB::table('booking_child_audit_log')->insert([
            'booking_child_id' => $child->id,
            'field_name' => 'workflow_status',
            'from_value' => 'pending',
            'to_value' => 'confirmed',
            'changed_by' => $admin->id,
            'changed_at' => now(),
        ]);

        $auditQueries = [];
        DB::listen(function ($query) use (&$auditQueries): void {
            if (str_contains(strtolower($query->sql), 'booking_child_audit_log')) {
                $auditQueries[] = $query->sql;
            }
        });

        $component = Livewire::test(BookingChildEdit::class, ['bookingChild' => $child]);

        $this->assertCount(0, $auditQueries);

        $component
            ->set('notes', 'Draft update without saving');

        $this->assertCount(0, $auditQueries);

        $component
            ->call('loadAuditTrail')
            ->assertSet('auditTrailLoaded', true)
            ->assertSet('auditTrailTotal', 1)
            ->assertSee('workflow status');

        $this->assertNotEmpty($auditQueries);
    }

    protected function createBookingChild(array $bookingOverrides = [], array $childOverrides = []): BookingChild
    {
        $booking = Booking::create(array_merge([
            'parent_name' => 'Mariam Hany',
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
            'booking_reference' => 'BK-1001',
            'consultation_type' => 'undecided',
            'consultation_date' => null,
            'consultation_time' => null,
            'follow_up_date' => null,
            'current_school' => 'Legacy School',
            'school_system' => 'British',
            'service_interest' => 'Help Me Study',
            'status' => 'pending',
            'notes' => 'Booking note',
        ], $bookingOverrides));

        return BookingChild::create(array_merge([
            'booking_id' => $booking->id,
            'child_name' => 'Youssef',
            'child_age' => 11,
            'child_grade' => 1,
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
            'current_school' => 'Current School',
            'student_id' => null,
            'notes' => 'Child note',
            'scheduled_date' => null,
            'scheduled_time' => null,
            'sort_order' => 1,
            'updated_by' => null,
        ], $childOverrides));
    }

    protected function createBookingTestTables(): void
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
                $table->unsignedTinyInteger('child_age');
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

        if (! Schema::hasTable('booking_child_emails')) {
            Schema::create('booking_child_emails', function ($table) {
                $table->id();
                $table->unsignedBigInteger('booking_child_id');
                $table->string('email_type');
                $table->string('status')->default('not_sent');
                $table->dateTime('last_attempt_at')->nullable();
                $table->dateTime('last_sent_at')->nullable();
                $table->text('last_error_message')->nullable();
                $table->unsignedBigInteger('triggered_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_intake_review')) {
            Schema::create('booking_intake_review', function ($table) {
                $table->id();
                $table->string('parent_name')->nullable();
                $table->string('parent_email')->nullable();
                $table->string('parent_phone')->nullable();
                $table->string('detection_reason')->nullable();
                $table->string('status')->default('pending_review');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_child_audit_log')) {
            Schema::create('booking_child_audit_log', function ($table) {
                $table->id();
                $table->unsignedBigInteger('booking_child_id');
                $table->string('field_name');
                $table->text('from_value')->nullable();
                $table->text('to_value')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable();
                $table->dateTime('changed_at');
            });
        }

        if (! Schema::hasTable('grade_levels')) {
            Schema::create('grade_levels', function ($table) {
                $table->id();
                $table->string('title');
                $table->boolean('active')->default(true);
                $table->integer('level_order')->default(0);
                $table->unsignedBigInteger('program_id')->nullable();
                $table->string('code')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('services_types')) {
            Schema::create('services_types', function ($table) {
                $table->id();
                $table->string('title');
                $table->string('value');
                $table->text('info')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('subjects')) {
            Schema::create('subjects', function ($table) {
                $table->integer('id')->primary();
                $table->string('title');
                $table->string('type')->default('standard');
                $table->unsignedBigInteger('program_id')->nullable();
                $table->string('code')->nullable();
                $table->boolean('active')->default(true);
                $table->string('row_status')->default('current');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grade_level_subjects')) {
            Schema::create('grade_level_subjects', function ($table) {
                $table->id();
                $table->unsignedBigInteger('grade_level_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->string('type')->default('standard');
                $table->string('status')->default('active');
                $table->unsignedBigInteger('created_by_user_id')->default(1);
                $table->timestamps();
            });
        }
    }

    protected function seedBookingReferenceTables(): void
    {
        if (DB::table('grade_levels')->count() === 0) {
            DB::table('grade_levels')->insert([
                'title' => 'Year 6',
                'active' => 1,
                'level_order' => 1,
                'program_id' => null,
                'code' => 'Y6',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('services_types')->count() === 0) {
            DB::table('services_types')->insert([
                [
                    'title' => 'Help Me Study',
                    'value' => 'Help Me Study',
                    'info' => null,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'SAT / ACT Preparation',
                    'value' => 'SAT / ACT Preparation',
                    'info' => null,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        foreach ([
            1 => ['title' => 'Language and Literature', 'code' => 'lang'],
            15 => ['title' => 'Well Being', 'code' => 'well-being'],
        ] as $subjectId => $subject) {
            DB::table('subjects')->updateOrInsert(
                ['id' => $subjectId],
                [
                    'title' => $subject['title'],
                    'type' => 'standard',
                    'program_id' => null,
                    'code' => $subject['code'],
                    'active' => 1,
                    'row_status' => 'current',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            DB::table('grade_level_subjects')->updateOrInsert(
                [
                    'grade_level_id' => 1,
                    'subject_id' => $subjectId,
                ],
                [
                    'academic_year_id' => 1,
                    'type' => 'standard',
                    'status' => 'active',
                    'created_by_user_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
