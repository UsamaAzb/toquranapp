<?php

namespace Tests\Feature;

use App\Livewire\Admin\Booking\TransferredChildren;
use App\Models\Booking;
use App\Models\BookingChild;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TransferredChildrenPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'array']);

        $this->createTransferredChildrenTables();
    }

    public function test_transferred_children_page_groups_rows_by_parent_and_links_to_student_workspace_pages(): void
    {
        $this->actingAs($this->createStaffUser('admin'));

        $transferredChild = $this->createTransferredChild();

        $nonTransferredBooking = Booking::create([
            'parent_name' => 'Ignored Parent',
            'parent_email' => 'ignored@example.test',
            'parent_phone' => '201099900000',
            'booking_reference' => 'BK-IGNORE',
            'consultation_type' => 'online',
            'consultation_date' => '2026-04-15',
            'consultation_time' => '13:00',
            'status' => 'confirmed',
        ]);

        BookingChild::create([
            'booking_id' => $nonTransferredBooking->id,
            'child_name' => 'Should Stay Hidden',
            'child_age' => 12,
            'child_grade' => 1,
            'school_system' => 'British',
            'service_interests' => ['Help Me Study'],
            'workflow_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'evaluation_outcome' => 'fit',
            'consultation_type' => 'online',
            'transfer_status' => 'not_transferred',
            'current_school' => 'Queue School',
            'scheduled_date' => '2026-04-15',
            'scheduled_time' => '13:00',
            'sort_order' => 1,
        ]);

        Livewire::test(TransferredChildren::class)
            ->assertSee('Transferred Families')
            ->assertSee('Parent')
            ->assertSee('Children')
            ->assertSee('Payment')
            ->assertSee('Next Action')
            ->assertSee('Billing not wired yet')
            ->assertSee('Not wired yet')
            ->assertSee('Status')
            ->assertSee('Mariam Parent')
            ->assertSee('Active')
            ->assertSee('Transferred Child')
            ->assertSee('youssef_student')
            ->assertDontSee('Open Workspace')
            ->assertDontSee('Open Family Workspace')
            ->assertDontSee('legacy-password-should-not-render')
            ->assertSee(route('admin.students.account', $transferredChild->student_id))
            ->assertSee(route('admin.students.security', $transferredChild->student_id))
            ->assertSee(route('admin.students.show_reward', $transferredChild->student_id))
            ->assertSee(route('admin.calendar.view', ['student' => $transferredChild->student_id, 'source' => 'transferred-children']))
            ->assertSee(route('admin.families.show', $transferredChild->student->parent_id))
            ->assertDontSeeHtml('tabler-user-check icon-20px')
            ->assertDontSee('table-hover')
            ->assertDontSee('Should Stay Hidden');
    }

    public function test_customer_support_transferred_page_hides_admin_only_student_domain_links(): void
    {
        $this->actingAs($this->createStaffUser('customer_support'));

        $transferredChild = $this->createTransferredChild();

        Livewire::test(TransferredChildren::class)
            ->assertSee(route('admin.families.show', $transferredChild->student->parent_id))
            ->assertSee('Workspace only')
            ->assertDontSee('Active Queue')
            ->assertDontSee('Intake Review')
            ->assertDontSee('Open Family Workspace')
            ->assertDontSee(route('admin.students.account', $transferredChild->student_id))
            ->assertDontSee(route('admin.students.security', $transferredChild->student_id))
            ->assertDontSee(route('admin.students.show_reward', $transferredChild->student_id))
            ->assertDontSee(route('admin.calendar.view', ['student' => $transferredChild->student_id, 'source' => 'transferred-children']));
    }

    public function test_admin_can_assign_customer_support_owner_to_transferred_family(): void
    {
        $this->actingAs($this->createStaffUser('admin'));

        $supportUser = $this->createStaffUser('customer_support', [
            'name' => 'Support One',
            'email' => 'support.one@example.test',
        ]);
        $transferredChild = $this->createTransferredChild();
        $parentId = (int) $transferredChild->student->parent_id;

        Livewire::test(TransferredChildren::class)
            ->assertSee('Support: Unassigned')
            ->call('assignFamilySupport', $parentId, $supportUser->id)
            ->assertHasNoErrors()
            ->assertSee('Family support owner assigned to Support One.')
            ->assertSee('Support: Support One');

        $this->assertSame($supportUser->id, ParentModel::find($parentId)->family_support_id);
    }

    public function test_customer_support_user_cannot_assign_family_support_owner(): void
    {
        $actor = $this->createStaffUser('customer_support');
        $otherSupportUser = $this->createStaffUser('customer_support', [
            'name' => 'Support Two',
            'email' => 'support.two@example.test',
        ]);
        $transferredChild = $this->createTransferredChild();
        $parentId = (int) $transferredChild->student->parent_id;

        Livewire::actingAs($actor)
            ->test(TransferredChildren::class)
            ->call('assignFamilySupport', $parentId, $otherSupportUser->id)
            ->assertHasErrors(['familySupport'])
            ->assertSee('Only admin or superadmin can assign family support ownership.');

        $this->assertNull(ParentModel::find($parentId)->family_support_id);
    }

    public function test_family_support_assignment_rejects_non_support_staff(): void
    {
        $this->actingAs($this->createStaffUser('super_admin'));

        $teacher = $this->createStaffUser('teacher', [
            'name' => 'Teacher One',
            'email' => 'teacher.one@example.test',
        ]);
        $transferredChild = $this->createTransferredChild();
        $parentId = (int) $transferredChild->student->parent_id;

        Livewire::test(TransferredChildren::class)
            ->call('assignFamilySupport', $parentId, $teacher->id)
            ->assertHasErrors(['familySupport'])
            ->assertSee('Choose an active customer support user.');

        $this->assertNull(ParentModel::find($parentId)->family_support_id);
    }

    public function test_family_support_assignment_rejects_parent_without_transferred_child(): void
    {
        $this->actingAs($this->createStaffUser('admin'));

        $this->createTransferredChild();
        $supportUser = $this->createStaffUser('customer_support', [
            'name' => 'Support Three',
            'email' => 'support.three@example.test',
        ]);
        $orphanParent = ParentModel::create([
            'first_name' => 'Non',
            'last_name' => 'Transferred',
            'email' => 'non.transferred.parent@example.test',
            'phone' => '201099999999',
            'active' => true,
            'lifecycle_status' => 'active',
        ]);

        Livewire::test(TransferredChildren::class)
            ->call('assignFamilySupport', $orphanParent->id, $supportUser->id)
            ->assertHasErrors(['familySupport'])
            ->assertSee('Choose a transferred family from this page.');

        $this->assertNull($orphanParent->fresh()->family_support_id);
    }

    public function test_customer_support_layout_includes_transferred_families_sidebar_link(): void
    {
        $this->actingAs($this->createStaffUser('customer_support'));

        $this->createTransferredChild();

        $this->get(route('admin.bookings.transferred'))
            ->assertOk()
            ->assertSee('admin/bookings/transferred', false)
            ->assertSee('Transferred Families');
    }

    public function test_transferred_children_page_search_filters_results(): void
    {
        $this->actingAs(User::factory()->create());

        $matchingChild = $this->createTransferredChild([
            'parent_name' => 'Noor Parent',
            'parent_email' => 'noor.parent@example.test',
            'booking_reference' => 'BK-7002',
        ], [
            'first_name' => 'Noor',
            'last_name' => 'Parent',
            'email' => 'noor.parent@example.test',
            'phone' => '201010101010',
            'user_name' => 'noor_parent',
        ], [
            'first_name' => 'Noor',
            'last_name' => 'Student',
            'user_name' => 'noor_student',
            'student_email' => 'noor.student@example.test',
        ], [
            'child_name' => 'Noor Transfer',
        ]);

        $otherChild = $this->createTransferredChild([
            'parent_name' => 'Karim Parent',
            'parent_email' => 'karim.parent@example.test',
            'booking_reference' => 'BK-7003',
        ], [
            'first_name' => 'Karim',
            'last_name' => 'Parent',
            'email' => 'karim.parent@example.test',
            'phone' => '201011111111',
            'user_name' => 'karim_parent',
        ], [
            'first_name' => 'Karim',
            'last_name' => 'Student',
            'user_name' => 'karim_student',
            'student_email' => 'karim.student@example.test',
        ], [
            'child_name' => 'Karim Transfer',
        ]);

        Livewire::test(TransferredChildren::class)
            ->set('search', 'Noor')
            ->assertSee($matchingChild->child_name)
            ->assertDontSee($otherChild->child_name)
            ->assertSee('Noor Parent')
            ->assertDontSee('Karim Parent')
            ->assertSee('noor_student')
            ->assertDontSee('karim_student');
    }

    public function test_family_rows_render_client_side_collapse_bindings(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createTransferredChild();

        Livewire::test(TransferredChildren::class)
            ->assertSee('Mariam Parent')
            ->assertDontSee('legacy-password-should-not-render')
            ->assertSeeHtml('x-data="{ open: true }"')
            ->assertSeeHtml('x-show="open"');
    }

    public function test_transferred_children_with_same_lms_parent_group_under_one_family_row(): void
    {
        $this->actingAs(User::factory()->create());

        $firstChild = $this->createTransferredChild([], [], [
            'first_name' => 'Aboudi',
            'last_name' => 'Student',
            'user_name' => 'aboudi_student',
            'student_email' => 'aboudi.student@example.test',
        ], [
            'child_name' => 'Aboudi',
        ]);
        $parent = $firstChild->student->parent;

        $secondStudentUser = User::create([
            'name' => 'Mustafa Student',
            'email' => 'mustafa.student@example.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $secondStudent = Student::create([
            'first_name' => 'Mustafa',
            'last_name' => 'Student',
            'parent_id' => $parent->id,
            'student_email' => 'mustafa.student@example.test',
            'current_school' => 'Transferred School',
            'school_system' => 'British',
            'grade_level_id' => 1,
            'user_id' => $secondStudentUser->id,
            'user_name' => 'mustafa_student',
            'password' => 'legacy-password-should-not-render',
        ]);
        $secondBooking = Booking::create([
            'parent_name' => $parent->full_name,
            'parent_email' => $parent->email,
            'parent_phone' => $parent->phone,
            'booking_reference' => 'BK-7004',
            'consultation_type' => 'online',
            'consultation_date' => '2026-04-16',
            'consultation_time' => '11:30',
            'status' => 'confirmed',
            'parent_id' => $parent->id,
        ]);
        $secondChild = BookingChild::create([
            'booking_id' => $secondBooking->id,
            'child_name' => 'Mustafa',
            'child_age' => 10,
            'child_grade' => 1,
            'school_system' => 'British',
            'service_interests' => ['Help Me Study'],
            'consultation_status' => 'confirmed',
            'workflow_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'evaluation_outcome' => 'fit',
            'consultation_type' => 'online',
            'transfer_status' => 'transferred',
            'current_school' => 'Transferred School',
            'student_id' => $secondStudent->id,
            'scheduled_date' => '2026-04-16',
            'scheduled_time' => '11:30',
            'sort_order' => 1,
        ]);

        $html = Livewire::test(TransferredChildren::class)
            ->assertSee('Mariam Parent')
            ->assertSee('Aboudi')
            ->assertSee('Mustafa')
            ->html();

        $this->assertFalse(
            str_contains($html, "booking-{$firstChild->booking_id}-header")
            && str_contains($html, "booking-{$secondChild->booking_id}-header"),
            'Transferred children sharing one LMS parent should not render as separate booking-family headers.'
        );
    }

    public function test_transferred_children_status_prefers_child_account_lifecycle_over_user_status(): void
    {
        $this->actingAs(User::factory()->create());

        $transferredChild = $this->createTransferredChild([], [], [
            'account_status' => 'suspended',
        ]);
        $transferredChild->student->user->forceFill(['status' => 'active'])->save();

        Livewire::test(TransferredChildren::class)
            ->assertSee('Suspended');
    }

    public function test_transferred_children_null_lifecycle_statuses_render_pending_not_active(): void
    {
        $this->actingAs(User::factory()->create());

        $this->createTransferredChild([
            'booking_reference' => 'BK-NULL-LIFE',
        ], [
            'lifecycle_status' => null,
        ], [
            'account_status' => null,
        ]);

        Livewire::test(TransferredChildren::class)
            ->assertSee('Pending')
            ->assertDontSeeHtml('>Active</span>');
    }

    public function test_transferred_children_paginates_family_groups_without_loading_every_family_into_one_page(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (range(1, 10) as $index) {
            $child = $this->createTransferredChild([
                'booking_reference' => sprintf('BK-PAGE1-%02d', $index),
            ], [
                'first_name' => "PageOne{$index}",
                'last_name' => 'Parent',
                'email' => "pageone{$index}.parent@example.test",
            ], [
                'first_name' => "PageOne{$index} Child",
                'student_email' => "pageone{$index}.child@example.test",
            ], [
                'child_name' => "PageOne{$index} Child",
            ]);

            $child->forceFill(['updated_at' => now()->addMinutes(20 - $index)])->save();
        }

        $oldest = $this->createTransferredChild([
            'booking_reference' => 'BK-OLDEST',
        ], [
            'first_name' => 'Oldest',
            'last_name' => 'Parent',
            'email' => 'oldest.parent@example.test',
        ], [
            'first_name' => 'Oldest Child',
            'student_email' => 'oldest.child@example.test',
        ], [
            'child_name' => 'Oldest Child',
        ]);
        $oldest->forceFill(['updated_at' => now()->subMinute()])->save();

        Livewire::test(TransferredChildren::class)
            ->assertDontSee('Oldest Parent')
            ->call('gotoPage', 2)
            ->assertSee('Oldest Parent');
    }

    protected function createTransferredChild(
        array $bookingOverrides = [],
        array $parentOverrides = [],
        array $studentOverrides = [],
        array $childOverrides = []
    ): BookingChild {
        $parentUser = User::create([
            'name' => trim(($parentOverrides['first_name'] ?? 'Mariam').' '.($parentOverrides['last_name'] ?? 'Parent')),
            'email' => $parentOverrides['email'] ?? 'mariam.parent@example.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $parent = ParentModel::create(array_merge([
            'first_name' => 'Mariam',
            'last_name' => 'Parent',
            'user_id' => $parentUser->id,
            'email' => 'mariam.parent@example.test',
            'phone' => '201000111222',
            'user_name' => 'mariam_parent',
            'active' => true,
            'lifecycle_status' => 'active',
        ], $parentOverrides));

        $studentUser = User::create([
            'name' => trim(($studentOverrides['first_name'] ?? 'Youssef').' '.($studentOverrides['last_name'] ?? 'Student')),
            'email' => $studentOverrides['student_email'] ?? 'youssef.student@example.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $student = Student::create(array_merge([
            'first_name' => 'Youssef',
            'last_name' => 'Student',
            'parent_id' => $parent->id,
            'student_email' => 'youssef.student@example.test',
            'current_school' => 'Transferred School',
            'school_system' => 'British',
            'grade_level_id' => 1,
            'user_id' => $studentUser->id,
            'user_name' => 'youssef_student',
            'password' => 'legacy-password-should-not-render',
            'account_status' => 'active',
        ], $studentOverrides));

        $booking = Booking::create(array_merge([
            'parent_name' => $parent->full_name,
            'parent_email' => $parent->email,
            'parent_phone' => $parent->phone,
            'booking_reference' => 'BK-7001',
            'consultation_type' => 'online',
            'consultation_date' => '2026-04-14',
            'consultation_time' => '10:30',
            'status' => 'confirmed',
            'parent_id' => $parent->id,
        ], $bookingOverrides));

        return BookingChild::create(array_merge([
            'booking_id' => $booking->id,
            'child_name' => 'Transferred Child',
            'child_age' => 11,
            'child_grade' => 1,
            'school_system' => 'British',
            'service_interests' => ['Help Me Study'],
            'consultation_status' => 'confirmed',
            'workflow_status' => 'confirmed',
            'meeting_disposition' => 'completed',
            'evaluation_outcome' => 'fit',
            'consultation_type' => 'online',
            'transfer_status' => 'transferred',
            'current_school' => 'Transferred School',
            'student_id' => $student->id,
            'scheduled_date' => '2026-04-14',
            'scheduled_time' => '10:30',
            'sort_order' => 1,
        ], $childOverrides));
    }

    private function createStaffUser(string $roleName, array $attributes = []): User
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::findOrCreate($roleName, 'web');
        $user = User::factory()->create(array_merge(['status' => 'active'], $attributes));
        $user->assignRole($role);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user->fresh();
    }

    protected function createTransferredChildrenTables(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('email')->nullable()->unique();
                $table->string('phone')->nullable();
                $table->string('status')->nullable();
                $table->string('profile_photo_path', 2048)->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->text('decryp_password')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'status')) {
            Schema::table('users', function ($table) {
                $table->string('status')->nullable();
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'profile_photo_path')) {
            Schema::table('users', function ($table) {
                $table->string('profile_photo_path', 2048)->nullable();
            });
        }

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
                $table->boolean('transfer')->nullable()->default(false);
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
                $table->unsignedTinyInteger('child_age')->nullable();
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
                $table->string('lifecycle_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function ($table) {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('student_email')->nullable();
                $table->string('student_phone')->nullable();
                $table->unsignedTinyInteger('age')->nullable();
                $table->unsignedBigInteger('grade_level_id')->nullable();
                $table->unsignedBigInteger('program_id')->nullable();
                $table->string('current_school')->nullable();
                $table->string('school_system')->nullable();
                $table->unsignedBigInteger('service_type_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_name')->nullable();
                $table->string('password')->nullable();
                $table->string('status')->default('active');
                $table->string('account_status')->nullable();
                $table->unsignedBigInteger('current_class_id')->nullable();
                $table->date('birth_date')->nullable();
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

        if (! Schema::hasTable('school_program')) {
            Schema::create('school_program', function ($table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('code')->nullable();
                $table->boolean('active')->default(true);
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

        if (! Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function ($table) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
            });
        }
    }
}
