<?php

namespace Tests\Feature;

use App\Livewire\Admin\StaffUsers;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StaffUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareStaffUserSchema();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['super_admin', 'admin', 'customer_support', 'teacher'] as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
    }

    public function test_superadmin_can_open_staff_users_page(): void
    {
        $superadmin = $this->staffUser('super_admin');

        $this->actingAs($superadmin)
            ->get(route('admin.staff.index'))
            ->assertOk()
            ->assertSee('Staff Users');
    }

    public function test_admin_cannot_open_staff_users_page(): void
    {
        $admin = $this->staffUser('admin');

        $this->actingAs($admin)
            ->get(route('admin.staff.index'))
            ->assertForbidden();
    }

    public function test_superadmin_can_create_teacher_staff_user(): void
    {
        $superadmin = $this->staffUser('super_admin');

        Livewire::actingAs($superadmin)
            ->test(StaffUsers::class)
            ->set('firstName', 'Aisha')
            ->set('lastName', 'Teacher')
            ->set('email', 'aisha.teacher@example.test')
            ->set('phone', '+201091051913')
            ->set('role', 'teacher')
            ->set('status', 'active')
            ->set('password', 'Teach123')
            ->set('passwordConfirmation', 'Teach123')
            ->call('createStaffUser')
            ->assertHasNoErrors()
            ->assertSee('One-time password')
            ->assertSee('Teach123');

        $teacher = User::where('email', 'aisha.teacher@example.test')->firstOrFail();

        $this->assertSame('Aisha Teacher', $teacher->name);
        $this->assertSame('active', $teacher->status);
        $this->assertTrue($teacher->hasRole('teacher'));
        $this->assertTrue(Hash::check('Teach123', $teacher->password));
        $this->assertSame('Teach123', $teacher->recoverable_password_encrypted);
    }

    public function test_superadmin_can_update_staff_role_status_and_password(): void
    {
        $superadmin = $this->staffUser('super_admin');
        $staff = $this->staffUser('teacher', [
            'name' => 'Old Staff',
            'email' => 'old.staff@example.test',
            'status' => 'active',
        ]);

        Livewire::actingAs($superadmin)
            ->test(StaffUsers::class)
            ->call('editStaffUser', $staff->id)
            ->set('firstName', 'Support')
            ->set('lastName', 'Lead')
            ->set('email', 'support.lead@example.test')
            ->set('role', 'customer_support')
            ->set('status', 'inactive')
            ->set('password', 'Supp1234')
            ->set('passwordConfirmation', 'Supp1234')
            ->call('updateStaffUser')
            ->assertHasNoErrors();

        $staff->refresh();

        $this->assertSame('Support Lead', $staff->name);
        $this->assertSame('inactive', $staff->status);
        $this->assertTrue($staff->hasRole('customer_support'));
        $this->assertFalse($staff->hasRole('teacher'));
        $this->assertTrue(Hash::check('Supp1234', $staff->password));
    }

    public function test_superadmin_cannot_deactivate_own_only_superadmin_access(): void
    {
        $superadmin = $this->staffUser('super_admin');

        Livewire::actingAs($superadmin)
            ->test(StaffUsers::class)
            ->call('toggleStatus', $superadmin->id)
            ->assertHasErrors(['role']);

        $this->assertSame('active', $superadmin->fresh()->status);
    }

    public function test_bootstrap_superadmin_requires_database_confirmation(): void
    {
        $exitCode = Artisan::call('toquran:bootstrap-superadmin', [
            '--confirm-db' => 'wrong_database',
            '--email' => 'owner@example.test',
            '--name' => 'Owner',
            '--password' => 'OwnerPass123',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertDatabaseMissing('users', ['email' => 'owner@example.test']);
    }

    public function test_bootstrap_superadmin_creates_active_superadmin_after_confirmation(): void
    {
        $databaseName = DB::connection()->getDatabaseName();

        $exitCode = Artisan::call('toquran:bootstrap-superadmin', [
            '--confirm-db' => $databaseName,
            '--email' => 'owner@example.test',
            '--name' => 'Owner Admin',
            '--password' => 'OwnerPass123',
            '--phone' => '+201091051913',
        ]);

        $this->assertSame(0, $exitCode);

        $owner = User::where('email', 'owner@example.test')->firstOrFail();

        $this->assertSame('Owner Admin', $owner->name);
        $this->assertSame('active', $owner->status);
        $this->assertSame('+201091051913', $owner->phone);
        $this->assertTrue($owner->hasRole('super_admin'));
        $this->assertTrue(Hash::check('OwnerPass123', $owner->password));
    }

    private function staffUser(string $roleName, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'status' => 'active',
        ], $attributes));

        $user->assignRole($roleName);

        return $user;
    }

    private function prepareStaffUserSchema(): void
    {
        if (! Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('lifecycle_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('account_status')->nullable();
                $table->timestamps();
            });
        }

        foreach ([
            'first_name' => fn (Blueprint $table) => $table->string('first_name')->nullable(),
            'last_name' => fn (Blueprint $table) => $table->string('last_name')->nullable(),
            'phone' => fn (Blueprint $table) => $table->string('phone')->nullable(),
            'status' => fn (Blueprint $table) => $table->string('status')->default('active'),
            'recoverable_password_encrypted' => fn (Blueprint $table) => $table->text('recoverable_password_encrypted')->nullable(),
        ] as $column => $definition) {
            if (! Schema::hasColumn('users', $column)) {
                Schema::table('users', $definition);
            }
        }
    }
}
