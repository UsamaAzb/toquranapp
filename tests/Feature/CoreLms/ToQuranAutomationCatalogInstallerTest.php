<?php

namespace Tests\Feature\CoreLms;

use App\Models\GeneralLibraryFolder;
use App\Models\GeneralLibraryResource;
use App\Models\MainDailySessionTemplate;
use App\Models\SeriesTask;
use App\Models\User;
use App\Support\ToQuranAutomationCatalog\AutomationCatalogInstaller;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

class ToQuranAutomationCatalogInstallerTest extends TestCase
{
    use CreatesAutomatedTaskTestingSchema;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createSeriesTaskSchema();
        if (! Schema::hasColumn('users', 'status')) {
            Schema::table('users', fn (Blueprint $table) => $table->string('status')->nullable());
        }
        $this->createCatalogRegistryTable();
        $this->seedTaskTypes();
        DB::table('task_types')->insertOrIgnore([
            'id' => 3,
            'title' => 'Assignment',
            'table_name' => 'attachment_files',
            'default_points' => 5,
            'max_points' => 10,
        ]);

        Role::findOrCreate('teacher');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_catalog_installs_versioned_wellbeing_and_deen_routines_idempotently_without_overwriting_teacher_edits(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 10:00:00'));

        $teacher = User::factory()->create(['status' => 'active']);
        $teacher->assignRole('teacher');
        $wellBeing = $this->createSubject('Well Being');
        $mdj = $this->createSubject('My Deen Journey');
        $this->createTeacherSubjectContext($teacher, $wellBeing);
        $this->createTeacherSubjectContext($teacher, $mdj);

        $result = app(AutomationCatalogInstaller::class)->installForTeacher($teacher, dryRun: false);

        $this->assertSame(1, $result['skipped']);
        $this->assertDatabaseHas('main_daily_session_templates', [
            'title' => 'Salah',
            'subject_id' => $mdj,
            'created_by_user_id' => $teacher->id,
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('main_daily_session_main_tasks', ['title' => 'Fajr']);
        $this->assertDatabaseHas('main_daily_session_main_tasks', ['title' => 'Dhuhr']);
        $this->assertDatabaseHas('main_daily_session_main_tasks', ['title' => 'Asr']);
        $this->assertDatabaseHas('main_daily_session_main_tasks', ['title' => 'Maghrib']);
        $this->assertDatabaseHas('main_daily_session_main_tasks', ['title' => 'Isha']);

        $salah = MainDailySessionTemplate::query()
            ->where('title', 'Salah')
            ->where('subject_id', $mdj)
            ->with(['versions', 'mainTasks'])
            ->firstOrFail();

        $this->assertCount(5, $salah->versions);
        $this->assertCount(5, $salah->mainTasks);
        $this->assertSame(25, DB::table('main_daily_session_version_tasks')
            ->whereIn('version_id', $salah->versions->pluck('id'))
            ->count());
        $this->assertDatabaseHas('toquran_automation_catalog_entries', [
            'automation_type' => 'versioned_routine',
            'catalog_key' => 'mdj-salah',
            'entry_scope' => 'root',
            'entry_key' => 'root',
            'target_table' => 'main_daily_session_templates',
            'target_id' => $salah->id,
        ]);
        $rootRegistryBeforeRerun = DB::table('toquran_automation_catalog_entries')
            ->where('automation_type', 'versioned_routine')
            ->where('catalog_key', 'mdj-salah')
            ->where('entry_scope', 'root')
            ->where('entry_key', 'root')
            ->first(['created_at', 'updated_at']);
        $this->assertNotNull($rootRegistryBeforeRerun);

        Carbon::setTestNow(Carbon::parse('2026-06-15 11:00:00'));

        $recordRegistry = new \ReflectionMethod(AutomationCatalogInstaller::class, 'recordRegistry');
        $recordRegistry->setAccessible(true);
        $recordRegistry->invoke(
            app(AutomationCatalogInstaller::class),
            'versioned_routine',
            'mdj-salah',
            'root',
            'root',
            $salah->getTable(),
            (int) $salah->id,
            $teacher,
            \App\Models\Subject::findOrFail($mdj),
            str_repeat('a', 64)
        );
        $rootRegistryAfterExplicitUpdate = DB::table('toquran_automation_catalog_entries')
            ->where('automation_type', 'versioned_routine')
            ->where('catalog_key', 'mdj-salah')
            ->where('entry_scope', 'root')
            ->where('entry_key', 'root')
            ->first(['created_at', 'updated_at']);
        $this->assertSame((string) $rootRegistryBeforeRerun->created_at, (string) $rootRegistryAfterExplicitUpdate->created_at);
        $this->assertNotSame((string) $rootRegistryBeforeRerun->updated_at, (string) $rootRegistryAfterExplicitUpdate->updated_at);

        $salah->update(['title' => 'Salah - Teacher Edited']);

        Carbon::setTestNow(Carbon::parse('2026-06-16 10:00:00'));

        app(AutomationCatalogInstaller::class)->installForTeacher($teacher, dryRun: false);

        $this->assertDatabaseHas('main_daily_session_templates', [
            'id' => $salah->id,
            'title' => 'Salah - Teacher Edited',
        ]);
        $this->assertSame(1, DB::table('toquran_automation_catalog_entries')
            ->where('automation_type', 'versioned_routine')
            ->where('catalog_key', 'mdj-salah')
            ->where('entry_scope', 'root')
            ->where('entry_key', 'root')
            ->count());
        $rootRegistryAfterRerun = DB::table('toquran_automation_catalog_entries')
            ->where('automation_type', 'versioned_routine')
            ->where('catalog_key', 'mdj-salah')
            ->where('entry_scope', 'root')
            ->where('entry_key', 'root')
            ->first(['created_at', 'updated_at']);
        $this->assertSame((string) $rootRegistryBeforeRerun->created_at, (string) $rootRegistryAfterRerun->created_at);
    }

    public function test_dua_series_task_is_skipped_until_reviewed_shared_library_folder_exists(): void
    {
        $teacher = User::factory()->create(['status' => 'active']);
        $teacher->assignRole('teacher');
        $mdj = $this->createSubject('My Deen Journey');
        $this->createTeacherSubjectContext($teacher, $mdj);

        $result = app(AutomationCatalogInstaller::class)->installForTeacher(
            $teacher,
            dryRun: false,
            onlyKeys: ['mdj-dua-bank-series']
        );

        $this->assertSame(1, $result['skipped']);
        $this->assertDatabaseMissing('series_tasks', [
            'title' => 'Dua Bank',
            'subject_id' => $mdj,
        ]);
    }

    public function test_dua_series_task_skips_mixed_shared_library_folder_even_when_resources_exist(): void
    {
        $teacher = User::factory()->create(['status' => 'active']);
        $teacher->assignRole('teacher');
        $mdj = $this->createSubject('My Deen Journey');
        $this->createTeacherSubjectContext($teacher, $mdj);
        $root = GeneralLibraryFolder::create([
            'title' => 'My Deen Journey',
            'status' => GeneralLibraryFolder::STATUS_ACTIVE,
            'content_mode' => GeneralLibraryFolder::CONTENT_MODE_MIXED,
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
        ]);
        $duaBank = GeneralLibraryFolder::create([
            'parent_id' => $root->id,
            'title' => 'Dua Bank',
            'status' => GeneralLibraryFolder::STATUS_ACTIVE,
            'content_mode' => GeneralLibraryFolder::CONTENT_MODE_MIXED,
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
        ]);
        GeneralLibraryResource::create([
            'general_library_folder_id' => $duaBank->id,
            'resource_type' => GeneralLibraryResource::TYPE_LINK,
            'title' => 'Before Eating Dua',
            'status' => GeneralLibraryResource::STATUS_ACTIVE,
            'external_url' => 'https://example.test/dua-before-eating',
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
        ]);

        $result = app(AutomationCatalogInstaller::class)->installForTeacher(
            $teacher,
            dryRun: false,
            onlyKeys: ['mdj-dua-bank-series']
        );

        $this->assertSame(1, $result['skipped']);
        $this->assertStringContainsString('not selectable for Series launch', implode("\n", $result['messages']));
        $this->assertDatabaseMissing('series_tasks', [
            'title' => 'Dua Bank',
            'subject_id' => $mdj,
        ]);
    }

    public function test_dua_series_task_installs_from_reviewed_shared_library_folder(): void
    {
        $teacher = User::factory()->create(['status' => 'active']);
        $teacher->assignRole('teacher');
        $mdj = $this->createSubject('My Deen Journey');
        $this->createTeacherSubjectContext($teacher, $mdj);
        $root = GeneralLibraryFolder::create([
            'title' => 'My Deen Journey',
            'status' => GeneralLibraryFolder::STATUS_ACTIVE,
            'content_mode' => GeneralLibraryFolder::CONTENT_MODE_MIXED,
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
        ]);
        $duaBank = GeneralLibraryFolder::create([
            'parent_id' => $root->id,
            'title' => 'Dua Bank',
            'status' => GeneralLibraryFolder::STATUS_ACTIVE,
            'content_mode' => GeneralLibraryFolder::CONTENT_MODE_SOURCES_ONLY,
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
        ]);
        GeneralLibraryResource::create([
            'general_library_folder_id' => $duaBank->id,
            'resource_type' => GeneralLibraryResource::TYPE_LINK,
            'title' => 'Before Eating Dua',
            'description' => 'Reviewed starter dua source.',
            'status' => GeneralLibraryResource::STATUS_ACTIVE,
            'external_url' => 'https://example.test/dua-before-eating',
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
        ]);

        $result = app(AutomationCatalogInstaller::class)->installForTeacher(
            $teacher,
            dryRun: false,
            onlyKeys: ['mdj-dua-bank-series']
        );

        $this->assertSame(0, $result['skipped']);
        $series = SeriesTask::query()
            ->where('title', 'Dua Bank')
            ->where('subject_id', $mdj)
            ->with('versions.items')
            ->firstOrFail();

        $this->assertSame('wait_for_completion', $series->release_policy);
        $this->assertSame('stop_at_end', $series->sequence_behavior);
        $this->assertCount(1, $series->versions);
        $this->assertCount(1, $series->versions->first()->items);
        $this->assertDatabaseHas('series_task_version_items', [
            'library_source_type' => 'general_library_resource',
            'library_title_snapshot' => 'Before Eating Dua',
            'sequence_position' => 1,
            'is_active' => 1,
        ]);
    }

    public function test_catalog_skips_ineligible_teacher_targets(): void
    {
        $mdj = $this->createSubject('My Deen Journey');
        $nonTeacher = User::factory()->create(['status' => 'active']);
        $this->createTeacherSubjectContext($nonTeacher, $mdj);

        $nonTeacherResult = app(AutomationCatalogInstaller::class)->installForTeacher(
            $nonTeacher,
            dryRun: false,
            onlyKeys: ['mdj-salah']
        );

        $this->assertSame(1, $nonTeacherResult['skipped']);

        $inactiveTeacher = User::factory()->create(['status' => 'inactive']);
        $inactiveTeacher->assignRole('teacher');
        $this->createTeacherSubjectContext($inactiveTeacher, $mdj);

        $inactiveResult = app(AutomationCatalogInstaller::class)->installForTeacher(
            $inactiveTeacher,
            dryRun: false,
            onlyKeys: ['mdj-salah']
        );

        $this->assertSame(1, $inactiveResult['skipped']);

        $teacherWithoutSubject = User::factory()->create(['status' => 'active']);
        $teacherWithoutSubject->assignRole('teacher');

        $missingSubjectResult = app(AutomationCatalogInstaller::class)->installForTeacher(
            $teacherWithoutSubject,
            dryRun: false,
            onlyKeys: ['mdj-salah']
        );

        $this->assertSame(1, $missingSubjectResult['skipped']);
        $this->assertDatabaseMissing('main_daily_session_templates', [
            'title' => 'Salah',
            'subject_id' => $mdj,
        ]);
    }

    public function test_catalog_command_refuses_unsafe_write_and_target_options(): void
    {
        $this->artisan('toquran:install-automation-catalog', [
            '--teacher-email' => 'teacher@example.test',
        ])->assertExitCode(1);

        $this->artisan('toquran:install-automation-catalog', [
            '--all-active-teachers' => true,
            '--confirm-db' => DB::connection()->getDatabaseName(),
        ])->assertExitCode(1);

        $this->artisan('toquran:install-automation-catalog', [
            '--dry-run' => true,
        ])->assertExitCode(1);

        $this->artisan('toquran:install-automation-catalog', [
            '--teacher-email' => 'teacher@example.test',
            '--all-active-teachers' => true,
            '--dry-run' => true,
        ])->assertExitCode(1);

        DB::shouldReceive('connection')
            ->once()
            ->andReturn(new class {
                public function getDatabaseName(): ?string
                {
                    return null;
                }
            });

        $this->artisan('toquran:install-automation-catalog', [
            '--teacher-email' => 'teacher@example.test',
            '--confirm-db' => '',
        ])
            ->expectsOutput('Could not determine the active database name. Aborting to prevent unsafe writes.')
            ->assertExitCode(1);
    }

    public function test_catalog_command_dry_run_prevents_database_writes(): void
    {
        $teacher = User::factory()->create([
            'email' => 'teacher@example.test',
            'status' => 'active',
        ]);
        $teacher->assignRole('teacher');
        $mdj = $this->createSubject('My Deen Journey');
        $this->createTeacherSubjectContext($teacher, $mdj);

        $this->artisan('toquran:install-automation-catalog', [
            '--teacher-email' => 'teacher@example.test',
            '--dry-run' => true,
            '--confirm-db' => DB::connection()->getDatabaseName(),
            '--only' => ['mdj-salah'],
        ])->assertExitCode(0);

        $this->assertDatabaseMissing('main_daily_session_templates', [
            'created_by_user_id' => $teacher->id,
        ]);
        $this->assertDatabaseMissing('toquran_automation_catalog_entries', [
            'teacher_user_id' => $teacher->id,
        ]);
    }

    private function createSubject(string $title): int
    {
        return DB::table('subjects')->insertGetId([
            'title' => $title,
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createCatalogRegistryTable(): void
    {
        Schema::create('toquran_automation_catalog_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('automation_type', 64);
            $table->string('catalog_key', 160);
            $table->string('entry_scope', 64);
            $table->string('entry_key', 191);
            $table->string('target_table', 128);
            $table->unsignedBigInteger('target_id');
            $table->unsignedBigInteger('teacher_user_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('installed_version', 80);
            $table->char('manifest_hash', 64);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique([
                'automation_type',
                'catalog_key',
                'teacher_user_id',
                'subject_id',
                'entry_scope',
                'entry_key',
            ], 'tq_auto_catalog_identity_uq');
        });
    }
}
