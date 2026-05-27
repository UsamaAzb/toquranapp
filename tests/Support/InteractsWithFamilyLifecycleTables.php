<?php

namespace Tests\Support;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait InteractsWithFamilyLifecycleTables
{
    protected function createFamilyLifecycleTables(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->boolean('is_current')->default(false);
                $table->timestamps();
            });
        }

        if (! \Illuminate\Support\Facades\DB::table('academic_years')->where('is_current', 1)->exists()) {
            \Illuminate\Support\Facades\DB::table('academic_years')->insert([
                'id' => 1,
                'title' => 'Current Academic Year',
                'is_current' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('email')->nullable()->unique();
                $table->string('phone')->nullable();
                $table->string('status')->default('active');
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->text('decryp_password')->nullable();
                $table->text('recoverable_password_encrypted')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        $this->ensureColumn('users', 'first_name', fn (Blueprint $table) => $table->string('first_name')->nullable());
        $this->ensureColumn('users', 'last_name', fn (Blueprint $table) => $table->string('last_name')->nullable());
        $this->ensureColumn('users', 'phone', fn (Blueprint $table) => $table->string('phone')->nullable());
        $this->ensureColumn('users', 'status', fn (Blueprint $table) => $table->string('status')->default('active'));
        $this->ensureColumn('users', 'decryp_password', fn (Blueprint $table) => $table->text('decryp_password')->nullable());
        $this->ensureColumn('users', 'recoverable_password_encrypted', fn (Blueprint $table) => $table->text('recoverable_password_encrypted')->nullable());

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

        $this->ensureColumn('parents', 'active', fn (Blueprint $table) => $table->boolean('active')->default(false));
        $this->ensureColumn('parents', 'lifecycle_status', fn (Blueprint $table) => $table->string('lifecycle_status')->nullable());

        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
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

        $this->ensureColumn('students', 'status', fn (Blueprint $table) => $table->string('status')->default('active'));
        $this->ensureColumn('students', 'account_status', fn (Blueprint $table) => $table->string('account_status')->nullable());
        $this->ensureColumn('students', 'student_email', fn (Blueprint $table) => $table->string('student_email')->nullable());
        $this->ensureColumn('students', 'student_phone', fn (Blueprint $table) => $table->string('student_phone')->nullable());
        $this->ensureColumn('students', 'user_id', fn (Blueprint $table) => $table->unsignedBigInteger('user_id')->nullable());
        $this->ensureColumn('students', 'user_name', fn (Blueprint $table) => $table->string('user_name')->nullable());
        $this->ensureColumn('students', 'grade_name', fn (Blueprint $table) => $table->string('grade_name')->nullable());

        if (! Schema::hasTable('account_histories')) {
            Schema::create('account_histories', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('parent_id');
                $table->string('event_type', 80);
                $table->string('reason_code', 80)->nullable();
                $table->unsignedBigInteger('actor_user_id')->nullable();
                $table->string('actor_role', 50)->nullable();
                $table->string('subject_type')->default('family');
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('email_delivery_claims')) {
            Schema::create('email_delivery_claims', function (Blueprint $table): void {
                $table->id();
                $table->string('claim_key')->unique();
                $table->unsignedBigInteger('parent_id');
                $table->string('subject_type', 30);
                $table->unsignedBigInteger('subject_id');
                $table->string('event_type', 80);
                $table->string('status', 20)->default('claimed');
                $table->json('metadata')->nullable();
                $table->timestamp('claimed_at')->useCurrent();
                $table->timestamp('completed_at')->nullable();
            });
        }

        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table): void {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        $this->createPermissionTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function createBookingTransferLifecycleTables(): void
    {
        $this->createFamilyLifecycleTables();

        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table): void {
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
                $table->string('status')->nullable();
                $table->string('booking_reference')->nullable();
                $table->boolean('transfer')->nullable()->default(false);
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->text('meeting_address')->nullable();
                $table->string('meeting_link')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_children')) {
            Schema::create('booking_children', function (Blueprint $table): void {
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

        if (! Schema::hasTable('booking_child_emails')) {
            Schema::create('booking_child_emails', function (Blueprint $table): void {
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

        if (! Schema::hasTable('booking_parent_identity_resolutions')) {
            Schema::create('booking_parent_identity_resolutions', function (Blueprint $table): void {
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

        if (! Schema::hasTable('services_types')) {
            Schema::create('services_types', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->string('value');
                $table->text('info')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grade_levels')) {
            Schema::create('grade_levels', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->unsignedInteger('level_order')->default(0);
                $table->unsignedBigInteger('program_id')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('subjects')) {
            Schema::create('subjects', function (Blueprint $table): void {
                $table->integer('id')->primary();
                $table->string('title');
                $table->string('type')->default('standard');
                $table->unsignedBigInteger('program_id')->default(10);
                $table->string('code')->nullable();
                $table->boolean('active')->default(true);
                $table->string('row_status')->default('current');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grade_level_subjects')) {
            Schema::create('grade_level_subjects', function (Blueprint $table): void {
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

    protected function seedFamilyLifecyclePermissions(?User $user = null): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'families.view_workspace',
            'families.activate',
            'families.suspend',
            'families.reactivate',
            'families.archive',
            'families.restore',
            'families.children.activate',
            'families.children.suspend',
            'families.children.reactivate',
            'families.children.archive',
            'families.children.restore',
            'families.history.view',
            'families.credentials.reveal',
            'families.credentials.send_reset_link',
            'families.credentials.generate_password',
            'families.credentials.resend_activation',
        ];

        $role = Role::findOrCreate('admin', 'web');

        foreach ($permissions as $permissionName) {
            $role->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));
        }

        if ($user) {
            $user->assignRole($role);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function ensureColumn(string $table, string $column, callable $definition): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, $definition);
    }

    private function createPermissionTables(): void
    {
        if (! Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (! Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table): void {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->primary(['role_id', 'model_id', 'model_type']);
            });
        }

        if (! Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->primary(['permission_id', 'model_id', 'model_type']);
            });
        }

        if (! Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table): void {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->primary(['permission_id', 'role_id']);
            });
        }
    }
}
