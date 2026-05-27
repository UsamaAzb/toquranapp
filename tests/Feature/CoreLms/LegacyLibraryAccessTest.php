<?php

namespace Tests\Feature\CoreLms;

use App\Http\Middleware\EnsureLegacyLibraryAccess;
use App\Models\User;
use App\Services\Library\LegacyLibraryAccessService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LegacyLibraryAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequiredTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin', 'teacher', 'parent', 'student'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_configured_owner_can_access_legacy_library(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('teacher');

        config(['week14.legacy_library_owner_user_ids' => [$owner->id]]);

        $this->assertTrue(app(LegacyLibraryAccessService::class)->canAccessLegacyLibrary($owner));
    }

    public function test_empty_owner_allowlist_denies_direct_legacy_library_access(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        config(['week14.legacy_library_owner_user_ids' => []]);

        $this->assertFalse(app(LegacyLibraryAccessService::class)->canAccessLegacyLibrary($teacher));

        $this->actingAs($teacher)
            ->get('/course/radio')
            ->assertForbidden();
    }

    public function test_unrelated_teacher_is_denied_legacy_library_access(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('teacher');

        $unrelatedTeacher = User::factory()->create();
        $unrelatedTeacher->assignRole('teacher');

        config(['week14.legacy_library_owner_user_ids' => [$owner->id]]);

        $this->assertFalse(app(LegacyLibraryAccessService::class)->canAccessLegacyLibrary($unrelatedTeacher));
    }

    public function test_unrelated_teacher_cannot_open_legacy_library_route_directly(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('teacher');

        $unrelatedTeacher = User::factory()->create();
        $unrelatedTeacher->assignRole('teacher');

        config(['week14.legacy_library_owner_user_ids' => [$owner->id]]);

        $this->actingAs($unrelatedTeacher)
            ->get('/course/sat')
            ->assertForbidden();
    }

    public function test_unconfigured_admin_cannot_access_private_legacy_library(): void
    {
        $admin = User::factory()->create(['name' => 'Support Admin']);
        $admin->assignRole('admin');

        config(['week14.legacy_library_owner_user_ids' => [999999]]);

        $this->assertFalse(app(LegacyLibraryAccessService::class)->canAccessLegacyLibrary($admin));

        $this->actingAs($admin)
            ->get('/course/radio')
            ->assertForbidden();
    }

    public function test_configured_admin_owner_can_access_available_legacy_library_route(): void
    {
        $admin = User::factory()->create(['name' => 'Configured Admin Owner']);
        $admin->assignRole('admin');

        config(['week14.legacy_library_owner_user_ids' => [$admin->id]]);

        $this->assertTrue(app(LegacyLibraryAccessService::class)->canAccessLegacyLibrary($admin));

        $this->actingAs($admin)
            ->get('/course/radio')
            ->assertOk();
    }

    public function test_parent_teacher_multi_role_is_not_blocked_from_learner_legacy_routes_by_teacher_role(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole('teacher');

        $parentTeacher = User::factory()->create();
        $parentTeacher->assignRole(['parent', 'teacher']);

        config(['week14.legacy_library_owner_user_ids' => [$owner->id]]);

        $request = Request::create('/course/sat');
        $request->setUserResolver(fn (): User => $parentTeacher);

        $response = app(EnsureLegacyLibraryAccess::class)->handle(
            $request,
            fn () => response('ok')
        );

        $this->assertSame('ok', $response->getContent());
    }

    public function test_authenticated_user_without_lms_role_is_denied_legacy_library_route_directly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/course/sat')
            ->assertForbidden();
    }

    private function createRequiredTables(): void
    {
        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('first_name')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('first_name')->nullable();
                $table->timestamps();
            });
        }
    }
}
