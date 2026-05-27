<?php

namespace Tests\Feature;

use App\Livewire\Admin\Booking\AdminIntakeForm;
use App\Livewire\Admin\Booking\BookingList;
use App\Models\Booking;
use App\Models\BookingIntakeReview;
use App\Models\BookingParentBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\Support\InteractsWithBookingIntakeTables;
use Tests\TestCase;

class AdminIntakeFormTest extends TestCase
{
    use InteractsWithBookingIntakeTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'array']);

        $this->createBookingIntakeTables();
        $this->createBookingDisplayTables();
        $this->seedBookingReferenceTables();
    }

    public function test_clean_new_family_submission_dispatches_refresh_event(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(AdminIntakeForm::class)
            ->call('openModal')
            ->set('parentName', 'Amina Salem')
            ->set('parentEmail', 'amina@example.test')
            ->set('parentPhone', '201000222333')
            ->set('notes', 'New family intake.')
            ->set('children.0.child_name', 'Sara')
            ->set('children.0.child_age', '9')
            ->set('children.0.child_grade', 1)
            ->set('children.0.school_system', 'IB')
            ->set('children.0.service_interests', ['IB Private Tutoring'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('intake-created');

        $booking = Booking::query()->with('children')->firstOrFail();
        $successMessage = "Intake saved to booking queue (booking #{$booking->id}).";

        $this->assertSame(['Sara'], $booking->children->pluck('child_name')->all());
        $this->assertSame(1, DB::table('booking_intake_submission_locks')->count());

        $this->assertBookingListAlertVisible(['success' => $successMessage], [$successMessage, 'Sara']);
    }

    public function test_existing_family_submission_appends_child_to_existing_booking(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();

        Livewire::test(AdminIntakeForm::class)
            ->call('openModal')
            ->set('intakeMode', 'existing')
            ->call('selectExistingFamily', $existingChild->booking_id)
            ->assertSet('selectedExistingBookingId', $existingChild->booking_id)
            ->set('children.0.child_name', 'Jana')
            ->set('children.0.child_age', '8')
            ->set('children.0.child_grade', 1)
            ->set('children.0.school_system', 'British')
            ->set('children.0.service_interests', ['Help Me Study'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('intake-created');

        $booking = Booking::query()->with('children')->findOrFail($existingChild->booking_id);
        $successMessage = "Child(ren) added to existing booking #{$booking->id}.";

        $this->assertSame(1, Booking::query()->count());
        $this->assertSame(['Youssef', 'Jana'], $booking->children->pluck('child_name')->all());

        $this->assertBookingListAlertVisible(['success' => $successMessage], [$successMessage, 'Jana']);
    }

    public function test_existing_family_mode_allows_search_and_selection_without_retyping_parent_contact(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();

        Livewire::test(AdminIntakeForm::class)
            ->call('openModal')
            ->set('intakeMode', 'existing')
            ->set('existingFamilySearch', 'mariam@example.test')
            ->assertSee('mariam@example.test')
            ->call('selectExistingFamily', $existingChild->booking_id)
            ->assertSet('parentName', 'Mariam Hany')
            ->assertSet('parentEmail', 'mariam@example.test')
            ->assertSet('parentPhone', '201000111222')
            ->set('children.0.child_name', 'Karim')
            ->set('children.0.child_age', '7')
            ->set('children.0.child_grade', 1)
            ->set('children.0.school_system', 'British')
            ->set('children.0.service_interests', ['Help Me Read'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('intake-created');

        $booking = Booking::query()->with('children')->findOrFail($existingChild->booking_id);

        $this->assertSame(['Youssef', 'Karim'], $booking->children->pluck('child_name')->all());
    }

    public function test_existing_family_mode_appends_to_the_selected_booking_when_contacts_have_multiple_matches(): void
    {
        $this->actingAs(User::factory()->create());

        $selectedChild = $this->createExistingBookingChild([
            'parent_name' => 'Mariam Selected',
            'parent_email' => 'shared-family@example.test',
            'parent_phone' => '201000111222',
            'booking_reference' => 'BK-SELECTED',
        ], [
            'child_name' => 'Youssef',
        ]);
        $canonicalChild = $this->createExistingBookingChild([
            'parent_name' => 'Mariam Canonical',
            'parent_email' => 'shared-family@example.test',
            'parent_phone' => '201000111222',
            'booking_reference' => 'BK-CANONICAL',
        ], [
            'child_name' => 'Omar',
        ]);

        Livewire::test(AdminIntakeForm::class)
            ->call('openModal')
            ->set('intakeMode', 'existing')
            ->call('selectExistingFamily', $selectedChild->booking_id)
            ->assertSet('parentName', 'Mariam Selected')
            ->assertSet('parentEmail', 'shared-family@example.test')
            ->assertSet('parentPhone', '201000111222')
            ->set('children.0.child_name', 'Jana')
            ->set('children.0.child_age', '8')
            ->set('children.0.child_grade', 1)
            ->set('children.0.school_system', 'British')
            ->set('children.0.service_interests', ['Help Me Study'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('intake-created');

        $selectedBooking = Booking::query()->with('children')->findOrFail($selectedChild->booking_id);
        $canonicalBooking = Booking::query()->with('children')->findOrFail($canonicalChild->booking_id);

        $this->assertSame(['Youssef', 'Jana'], $selectedBooking->children->pluck('child_name')->all());
        $this->assertSame(['Omar'], $canonicalBooking->children->pluck('child_name')->all());
    }

    public function test_duplicate_child_submission_routes_to_review_and_surfaces_queue_link(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild();

        Livewire::test(AdminIntakeForm::class)
            ->call('openModal')
            ->set('parentName', 'Mariam Hany')
            ->set('parentEmail', 'mariam@example.test')
            ->set('parentPhone', '201000111222')
            ->set('children.0.child_name', 'Youssef')
            ->set('children.0.child_age', '11')
            ->set('children.0.child_grade', 1)
            ->set('children.0.school_system', 'British')
            ->set('children.0.service_interests', ['Help Me Study'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('intake-created');

        $review = BookingIntakeReview::query()->firstOrFail();

        $this->assertSame(1, Booking::query()->count());
        $this->assertSame($existingChild->booking_id, $review->matched_booking_id);

        $this->assertBookingListAlertVisible([
            'warning' => 'Submission routed to Intake Review.',
            'intake_review_id' => $review->id,
        ], [
            'Submission routed to Intake Review.',
            'Open Intake Review',
        ]);
    }

    public function test_contact_mismatch_submission_routes_to_review(): void
    {
        $this->actingAs(User::factory()->create());

        $existingChild = $this->createExistingBookingChild([
            'parent_email' => 'mariam@example.test',
            'parent_phone' => '201000111222',
        ]);

        Livewire::test(AdminIntakeForm::class)
            ->call('openModal')
            ->set('parentName', 'Mariam Hany')
            ->set('parentEmail', 'mariam@example.test')
            ->set('parentPhone', '201000111999')
            ->set('children.0.child_name', 'Jana')
            ->set('children.0.child_age', '8')
            ->set('children.0.child_grade', 1)
            ->set('children.0.school_system', 'British')
            ->set('children.0.service_interests', ['Help Me Study'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('intake-created');

        $review = BookingIntakeReview::query()->firstOrFail();

        $this->assertSame('suspected_contact_mismatch', $review->detection_reason);
        $this->assertSame($existingChild->booking_id, $review->matched_booking_id);
    }

    public function test_blocked_parent_submission_routes_to_review(): void
    {
        $this->actingAs(User::factory()->create());

        BookingParentBlock::query()->create([
            'normalized_email' => 'blocked@example.test',
            'normalized_phone' => null,
            'block_reason' => 'Manual block',
        ]);

        Livewire::test(AdminIntakeForm::class)
            ->call('openModal')
            ->set('parentName', 'Blocked Parent')
            ->set('parentEmail', 'blocked@example.test')
            ->set('children.0.child_name', 'Blocked Child')
            ->set('children.0.child_age', '10')
            ->set('children.0.child_grade', 1)
            ->set('children.0.school_system', 'American')
            ->set('children.0.service_interests', ['Help Me Study'])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('intake-created');

        $review = BookingIntakeReview::query()->firstOrFail();

        $this->assertSame('blocked_parent', $review->detection_reason);
        $this->assertSame(0, Booking::query()->count());
    }

    public function test_missing_parent_email_and_phone_stays_in_validation_and_writes_nothing(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(AdminIntakeForm::class)
            ->call('openModal')
            ->set('parentName', 'Missing Contact Parent')
            ->set('children.0.child_name', 'Contactless Child')
            ->set('children.0.child_age', '10')
            ->set('children.0.child_grade', 1)
            ->set('children.0.school_system', 'British')
            ->set('children.0.service_interests', ['Help Me Study'])
            ->call('save')
            ->assertHasErrors(['parent_email']);

        $this->assertSame(0, Booking::query()->count());
        $this->assertSame(0, BookingIntakeReview::query()->count());
        $this->assertSame(0, DB::table('booking_intake_submission_locks')->count());
    }

    public function test_existing_family_mode_requires_a_selected_family_before_save(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(AdminIntakeForm::class)
            ->call('openModal')
            ->set('intakeMode', 'existing')
            ->set('children.0.child_name', 'Contactless Child')
            ->set('children.0.child_age', '10')
            ->set('children.0.child_grade', 1)
            ->set('children.0.school_system', 'IB')
            ->set('children.0.service_interests', ['Help Me Study'])
            ->call('save')
            ->assertHasErrors(['selectedExistingBookingId']);

        $this->assertSame(0, Booking::query()->count());
        $this->assertSame(0, BookingIntakeReview::query()->count());
        $this->assertSame(0, DB::table('booking_intake_submission_locks')->count());
    }

    public function test_missing_required_child_fields_stay_inline_and_do_not_raise_generic_form_error(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(AdminIntakeForm::class)
            ->call('openModal')
            ->set('parentName', 'Amina Salem')
            ->set('parentEmail', 'amina@example.test')
            ->set('children.0.child_name', 'Sara')
            ->call('save')
            ->assertHasErrors([
                'children.0.child_age',
                'children.0.child_grade',
                'children.0.school_system',
                'children.0.service_interests',
            ])
            ->assertSee('Age is required.')
            ->assertSee('Grade is required.')
            ->assertSee('School system is required.')
            ->assertSee('Select at least one service interest.')
            ->assertDontSee('The children.0.child_age field is required when children is present.')
            ->assertDontSee('Intake could not be saved right now.');

        $this->assertSame(0, Booking::query()->count());
        $this->assertSame(0, BookingIntakeReview::query()->count());
        $this->assertSame(0, DB::table('booking_intake_submission_locks')->count());
    }

    public function test_modal_filters_out_non_child_service_options(): void
    {
        $this->actingAs(User::factory()->create());

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
                'title' => '(Teachers) Course',
                'value' => '(Teachers) Course',
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

        Livewire::test(AdminIntakeForm::class)
            ->call('openModal')
            ->assertDontSee('(Parents) Course')
            ->assertDontSee('(Teachers) Course')
            ->assertSee('IB System')
            ->assertSee('American System')
            ->assertSee('British System')
            ->assertSee('Other')
            ->assertSee('Help Me Study')
            ->assertSee('IB Private Tutoring')
            ->assertSee('SAT / ACT Preparation')
            ->assertSee('Custom Coaching')
            ->assertDontSee('Help Me Read')
            ->assertDontSee('Not Sure');
    }

    public function test_modal_does_not_fallback_when_configured_services_are_not_child_facing(): void
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

        Livewire::test(AdminIntakeForm::class)
            ->call('openModal')
            ->assertSee('No active child-facing services are configured.')
            ->assertDontSee('Help Me Study')
            ->assertDontSee('Help Me Read')
            ->assertDontSee('IB Private Tutoring')
            ->assertDontSee('SAT / ACT Preparation')
            ->assertDontSee('(Parents) Course')
            ->assertDontSee('Not Sure');
    }

    protected function createBookingDisplayTables(): void
    {
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
                    'title' => 'IB Private Tutoring',
                    'value' => 'IB Private Tutoring',
                    'info' => null,
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    protected function assertBookingListAlertVisible(array $session, array $expectedText): void
    {
        foreach ($session as $key => $value) {
            session()->flash($key, $value);
        }

        $component = Livewire::test(BookingList::class);

        foreach ($expectedText as $text) {
            $component->assertSee($text);
        }
    }
}
