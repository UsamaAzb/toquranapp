<?php

namespace Tests\Feature\CoreLms;

use App\Livewire\Admin\StarterCatalogInstaller;
use App\Models\MainDailySessionTemplate;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesAutomatedTaskTestingSchema;
use Tests\TestCase;

class StarterCatalogInstallerUiTest extends TestCase
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

        foreach (['admin', 'customer_support', 'teacher'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_admin_can_preview_and_install_starter_catalog_for_one_teacher(): void
    {
        $admin = User::factory()->create(['status' => 'active']);
        $admin->assignRole('admin');

        $teacher = User::factory()->create([
            'name' => 'Osama Qandil',
            'email' => 'teacher@example.test',
            'status' => 'active',
        ]);
        $teacher->assignRole('teacher');

        $wellBeing = $this->createSubject('Well Being');
        $myDeenJourney = $this->createSubject('My Deen Journey');
        $this->createTeacherSubjectContext($teacher, $wellBeing, 'Well Being Class');
        $this->createTeacherSubjectContext($teacher, $myDeenJourney, 'My Deen Journey Class');

        $this->actingAs($admin);

        $component = Livewire::test(StarterCatalogInstaller::class)
            ->set('teacherId', $teacher->id)
            ->call('previewCatalog');

        $preview = $component->get('previewResult');
        $this->assertGreaterThan(0, $preview['created']);
        $this->assertSame(0, $preview['updated']);
        $this->assertSame(0, $preview['skipped']);

        $component
            ->call('installCatalog')
            ->assertHasErrors(['confirmInstall' => 'accepted'])
            ->set('confirmInstall', true)
            ->call('installCatalog')
            ->assertHasNoErrors();

        $install = $component->get('installResult');
        $this->assertGreaterThan(0, $install['created']);
        $this->assertSame(0, $install['skipped']);

        $this->assertDatabaseHas('toquran_automation_catalog_entries', [
            'teacher_user_id' => $teacher->id,
            'catalog_key' => 'mdj-salah',
        ]);
        $this->assertDatabaseHas('toquran_automation_catalog_entries', [
            'teacher_user_id' => $teacher->id,
            'catalog_key' => 'wb-personal-hygiene',
        ]);
        $this->assertDatabaseHas('main_daily_session_templates', [
            'title' => 'Salah',
            'subject_id' => $myDeenJourney,
            'created_by_user_id' => $teacher->id,
        ]);
        $this->assertTrue(MainDailySessionTemplate::query()
            ->where('created_by_user_id', $teacher->id)
            ->where('title', 'Personal Hygiene')
            ->exists());
    }

    public function test_support_user_cannot_access_starter_catalog_installer(): void
    {
        $support = User::factory()->create(['status' => 'active']);
        $support->assignRole('customer_support');

        $this->actingAs($support);

        $this->get(route('admin.starter-catalog-installer.index'))
            ->assertForbidden();
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
            $table->integer('teacher_user_id');
            $table->integer('subject_id');
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
