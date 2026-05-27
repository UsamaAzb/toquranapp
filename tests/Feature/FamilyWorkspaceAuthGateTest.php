<?php

namespace Tests\Feature;

use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FamilyWorkspaceAuthGateTest extends TestCase
{
    use RefreshDatabase;

    private const GENERIC_LOGIN_MESSAGE = 'We could not sign you in. Please check your details or contact support.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createLifecycleAuthTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['parent', 'student', 'admin', 'teacher', 'super_admin', 'customer_support'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_parent_with_active_family_can_login(): void
    {
        $user = $this->createUserWithRole('parent');
        ParentModel::create([
            'first_name' => 'Mariam',
            'user_id' => $user->id,
            'email' => $user->email,
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_parent_non_active_or_null_family_lifecycle_is_blocked_with_generic_message(): void
    {
        foreach ([
            FamilyLifecycleStatus::PendingActivation->value,
            FamilyLifecycleStatus::Suspended->value,
            FamilyLifecycleStatus::Archived->value,
            null,
        ] as $status) {
            $user = $this->createUserWithRole('parent');
            ParentModel::create([
                'first_name' => 'Parent '.$user->id,
                'user_id' => $user->id,
                'email' => $user->email,
                'lifecycle_status' => $status,
            ]);

            $response = $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

            $this->assertGuest();
            $response->assertSessionHasErrors([
                'email' => self::GENERIC_LOGIN_MESSAGE,
            ]);
        }
    }

    public function test_student_with_active_child_account_and_active_family_can_login(): void
    {
        [$studentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );

        $this->post('/login', [
            'email' => $studentUser->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($studentUser);
    }

    public function test_student_login_requires_active_family_then_active_child_account(): void
    {
        foreach ([
            [FamilyLifecycleStatus::Suspended->value, ChildAccountStatus::Active->value],
            [FamilyLifecycleStatus::Active->value, ChildAccountStatus::PendingActivation->value],
            [FamilyLifecycleStatus::Active->value, ChildAccountStatus::Suspended->value],
            [FamilyLifecycleStatus::Active->value, ChildAccountStatus::Archived->value],
            [null, ChildAccountStatus::Active->value],
            [FamilyLifecycleStatus::Active->value, null],
        ] as [$familyStatus, $childStatus]) {
            [$studentUser] = $this->createStudentFamily($familyStatus, $childStatus);

            $response = $this->post('/login', [
                'email' => $studentUser->email,
                'password' => 'password',
            ]);

            $this->assertGuest();
            $response->assertSessionHasErrors([
                'email' => self::GENERIC_LOGIN_MESSAGE,
            ]);
        }
    }

    public function test_staff_and_internal_roles_keep_users_status_only_login_behavior(): void
    {
        foreach (['admin', 'teacher', 'super_admin', 'customer_support'] as $role) {
            $user = $this->createUserWithRole($role);

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

            $this->assertAuthenticatedAs($user);

            auth()->logout();
            $this->flushSession();
        }
    }

    public function test_super_admin_login_redirects_to_admin_bookings_livewire(): void
    {
        $user = $this->createUserWithRole('super_admin');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.bookings.livewire'));
    }

    public function test_customer_support_login_redirects_to_transferred_families(): void
    {
        $user = $this->createUserWithRole('customer_support');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.bookings.transferred'));
    }

    public function test_parent_refreshing_stale_student_workplace_tab_redirects_to_children_list(): void
    {
        $user = $this->createUserWithRole('parent');
        ParentModel::create([
            'first_name' => 'Stale Tab Parent',
            'user_id' => $user->id,
            'email' => $user->email,
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('student.workplace'));

        $response->assertRedirect(route('parent.students'));
    }

    public function test_parent_refreshing_stale_student_workplace_tab_with_wrong_student_id_redirects_to_children_list(): void
    {
        $user = $this->createUserWithRole('parent');
        $parent = ParentModel::create([
            'first_name' => 'Stale Tab Parent',
            'user_id' => $user->id,
            'email' => $user->email,
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        Student::create([
            'first_name' => 'Owned Student',
            'parent_id' => $parent->id,
        ]);
        $otherStudent = Student::create([
            'first_name' => 'Other Student',
            'parent_id' => ParentModel::create([
                'first_name' => 'Other Parent',
                'email' => 'other.parent@example.test',
                'lifecycle_status' => FamilyLifecycleStatus::Active->value,
            ])->id,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('student.workplace', ['student_id' => $otherStudent->id]));

        $response->assertRedirect(route('parent.students'));
    }

    public function test_non_family_role_takes_precedence_when_user_also_has_parent_role(): void
    {
        Role::findOrCreate('future_internal');

        foreach (['teacher', 'future_internal'] as $role) {
            $user = $this->createUserWithRole('parent');
            $user->assignRole($role);

            ParentModel::create([
                'first_name' => 'Mixed Role '.$role,
                'user_id' => $user->id,
                'email' => $user->email,
                'lifecycle_status' => FamilyLifecycleStatus::Suspended->value,
            ]);

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

            $this->assertAuthenticatedAs($user);

            auth()->logout();
            $this->flushSession();
        }
    }

    public function test_inactive_users_status_is_blocked_before_lifecycle_checks_with_generic_message(): void
    {
        $user = $this->createUserWithRole('parent', ['status' => 'inactive']);
        ParentModel::create([
            'first_name' => 'Inactive',
            'user_id' => $user->id,
            'email' => $user->email,
            'lifecycle_status' => FamilyLifecycleStatus::Active->value,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => self::GENERIC_LOGIN_MESSAGE,
        ]);
    }

    public function test_family_block_does_not_cascade_write_child_user_status(): void
    {
        [$studentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Suspended->value,
            ChildAccountStatus::Active->value,
        );

        $this->post('/login', [
            'email' => $studentUser->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $this->assertSame('active', $studentUser->fresh()->status);
    }

    private function createUserWithRole(string $role, array $overrides = []): User
    {
        $user = User::factory()->create(array_merge([
            'status' => 'active',
        ], $overrides));

        $user->assignRole($role);

        return $user;
    }

    private function createStudentFamily(?string $familyStatus, ?string $childStatus): array
    {
        $parentUser = $this->createUserWithRole('parent');
        $studentUser = $this->createUserWithRole('student');

        $parent = ParentModel::create([
            'first_name' => 'Family',
            'user_id' => $parentUser->id,
            'email' => $parentUser->email,
            'lifecycle_status' => $familyStatus,
        ]);

        $student = Student::create([
            'first_name' => 'Student',
            'parent_id' => $parent->id,
            'user_id' => $studentUser->id,
            'status' => 'active',
            'account_status' => $childStatus,
        ]);

        return [$studentUser, $student, $parent, $parentUser];
    }

    private function createLifecycleAuthTables(): void
    {
        if (! Schema::hasColumn('users', 'status')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('status')->default('active')->after('password');
            });
        }

        if (! Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table): void {
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
                $table->boolean('active')->default(false);
                $table->string('lifecycle_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('status')->default('active');
                $table->string('account_status')->nullable();
                $table->timestamps();
            });
        }
    }
}
