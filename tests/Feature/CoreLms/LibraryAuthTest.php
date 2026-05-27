<?php

namespace Tests\Feature\CoreLms;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LibraryAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequiredTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin', 'teacher', 'student', 'parent'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_teacher_can_open_library(): void
    {
        $teacher = $this->userWithRole('teacher');
        config(['week14.legacy_library_owner_user_ids' => [$teacher->id]]);

        $this->actingAs($teacher)
            ->get(route('teacher.get_library'))
            ->assertOk()
            ->assertSee('Teaching library')
            ->assertSee('Add or edit My Library')
            ->assertSee('Daily Sessions')
            ->assertSee('href="'.url('tutriols/level-up').'"', false);
    }

    public function test_admin_can_open_library_without_teacher_only_daily_sessions_link(): void
    {
        $admin = $this->userWithRole('admin');
        config(['week14.legacy_library_owner_user_ids' => [999999]]);

        $this->actingAs($admin)
            ->get(route('teacher.get_library'))
            ->assertOk()
            ->assertSee('Teaching library')
            ->assertDontSee('Daily Sessions')
            ->assertSee('Private legacy Library source')
            ->assertDontSee('href="'.url('course/radio').'"', false)
            ->assertDontSee('href="'.url('course/notice-note').'"', false);
    }

    public function test_allowlisted_admin_card_destination_is_reachable_when_card_is_available(): void
    {
        $admin = $this->userWithRole('admin');
        config(['week14.legacy_library_owner_user_ids' => [$admin->id]]);

        $this->actingAs($admin)
            ->get(route('teacher.get_library'))
            ->assertOk()
            ->assertSee('href="'.url('course/radio').'"', false);

        $this->actingAs($admin)
            ->get('/course/radio')
            ->assertOk();
    }

    public function test_student_cannot_open_teacher_library(): void
    {
        $student = $this->userWithRole('student');

        $this->actingAs($student)
            ->get(route('teacher.get_library'))
            ->assertForbidden();
    }

    public function test_parent_cannot_open_teacher_library(): void
    {
        $parent = $this->userWithRole('parent');

        $this->actingAs($parent)
            ->get(route('teacher.get_library'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('teacher.get_library'))
            ->assertRedirect(route('login'));
    }

    public function test_teacher_owned_library_folder_and_resources_are_browsable_from_library_cards(): void
    {
        $teacher = $this->userWithRole('teacher');
        config(['week14.legacy_library_owner_user_ids' => [999999]]);

        DB::table('subjects')->insert([
            'id' => 20,
            'title' => 'Language and Literature',
        ]);
        DB::table('teacher_subject_classes')->insert([
            'user_teacher_coteacher_id' => $teacher->id,
            'subject_id' => 20,
            'subject_name' => 'Language and Literature',
            'status' => 'current',
        ]);
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'title' => 'Library Trial Sources',
            'created_by_user_id' => $teacher->id,
        ]);
        $resourceId = DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'library_section_id' => $sectionId,
            'resource_type' => 'link',
            'title' => 'Amazon',
            'external_url' => 'https://example.com/resource',
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.get_library'))
            ->assertOk()
            ->assertSee('Library Trial Sources')
            ->assertSee('href="'.url('teacher/library?folder='.$sectionId).'"', false);

        $this->actingAs($teacher)
            ->get(route('teacher.get_library', ['folder' => $sectionId]))
            ->assertOk()
            ->assertSee('Amazon')
            ->assertSee('href="'.route('teacher.library.resources.open', [
                'resource' => $resourceId,
                'return_to' => url('teacher/library?folder='.$sectionId),
            ]).'"', false);
    }

    public function test_teacher_can_create_root_folder_from_library_page_for_owned_subject(): void
    {
        $teacher = $this->userWithRole('teacher');

        DB::table('subjects')->insert([
            'id' => 20,
            'title' => 'Language and Literature',
        ]);
        DB::table('teacher_subject_classes')->insert([
            'user_teacher_coteacher_id' => $teacher->id,
            'subject_id' => 20,
            'subject_name' => 'Language and Literature',
            'status' => 'current',
        ]);
        DB::table('library_sections')->insert([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'title' => 'Existing Root',
            'sort_order' => 6,
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.library.sections.store'), [
                'subject_id' => 20,
                'title' => 'New Root Folder',
                'description' => 'Root description',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('library_sections', [
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'parent_id' => null,
            'title' => 'New Root Folder',
            'description' => 'Root description',
            'sort_order' => 7,
        ]);
    }

    public function test_teacher_cannot_create_root_folder_for_unowned_subject(): void
    {
        $teacher = $this->userWithRole('teacher');

        DB::table('subjects')->insert([
            ['id' => 20, 'title' => 'Language and Literature'],
            ['id' => 21, 'title' => 'Science'],
        ]);
        DB::table('teacher_subject_classes')->insert([
            'user_teacher_coteacher_id' => $teacher->id,
            'subject_id' => 20,
            'subject_name' => 'Language and Literature',
            'status' => 'current',
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.library.sections.store'), [
                'subject_id' => 21,
                'title' => 'Wrong Subject Folder',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('library_sections', [
            'title' => 'Wrong Subject Folder',
        ]);
    }

    public function test_library_folder_page_can_show_restore_and_delete_archived_items(): void
    {
        $teacher = $this->userWithRole('teacher');

        DB::table('subjects')->insert([
            'id' => 20,
            'title' => 'Language and Literature',
        ]);
        DB::table('teacher_subject_classes')->insert([
            'user_teacher_coteacher_id' => $teacher->id,
            'subject_id' => 20,
            'subject_name' => 'Language and Literature',
            'status' => 'current',
        ]);
        $activeSectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'title' => 'Active Folder',
            'created_by_user_id' => $teacher->id,
        ]);
        $archivedSectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'title' => 'Archived Root',
            'status' => 'archived',
            'created_by_user_id' => $teacher->id,
            'archived_at' => now(),
        ]);
        $archivedResourceId = DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'library_section_id' => $activeSectionId,
            'resource_type' => 'link',
            'title' => 'Archived Link',
            'status' => 'archived',
            'external_url' => 'https://example.com/archived',
            'created_by_user_id' => $teacher->id,
            'archived_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.get_library'))
            ->assertOk()
            ->assertSee('Active Folder')
            ->assertDontSee('Archived Root');

        $this->actingAs($teacher)
            ->get(route('teacher.get_library', ['show_archived' => 1]))
            ->assertOk()
            ->assertSee('Archived Root')
            ->assertSee('Archived - restore to open')
            ->assertSee(route('teacher.library.sections.restore', $archivedSectionId), false);

        $this->actingAs($teacher)
            ->get(route('teacher.get_library', ['folder' => $activeSectionId, 'show_archived' => 1]))
            ->assertOk()
            ->assertSee('Archived Link')
            ->assertSee(route('teacher.library.resources.restore', $archivedResourceId), false)
            ->assertDontSee('href="'.route('teacher.library.resources.open', [
                'resource' => $archivedResourceId,
                'return_to' => url('teacher/library?folder='.$activeSectionId),
            ]).'"', false);

        $this->actingAs($teacher)
            ->patch(route('teacher.library.sections.restore', $archivedSectionId))
            ->assertRedirect();
        $this->assertDatabaseHas('library_sections', [
            'id' => $archivedSectionId,
            'status' => 'active',
            'archived_at' => null,
        ]);

        $this->actingAs($teacher)
            ->delete(route('teacher.library.resources.delete', $archivedResourceId))
            ->assertRedirect();
        $this->assertDatabaseMissing('library_resources', ['id' => $archivedResourceId]);
    }

    public function test_controller_resource_creates_append_in_selected_order(): void
    {
        $teacher = $this->userWithRole('teacher');

        DB::table('subjects')->insert([
            'id' => 20,
            'title' => 'Language and Literature',
        ]);
        DB::table('teacher_subject_classes')->insert([
            'user_teacher_coteacher_id' => $teacher->id,
            'subject_id' => 20,
            'subject_name' => 'Language and Literature',
            'status' => 'current',
        ]);
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'title' => 'Links',
            'created_by_user_id' => $teacher->id,
        ]);
        DB::table('library_resources')->insert([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'library_section_id' => $sectionId,
            'resource_type' => 'link',
            'title' => 'Existing',
            'external_url' => 'https://example.com/existing',
            'sort_order' => 12,
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.library.resources.store', $sectionId), [
                'resource_kind' => 'link',
                'title' => 'Next Link',
                'external_url' => 'https://example.com/next',
            ])
            ->assertRedirect();

        $this->assertSame(
            ['Existing', 'Next Link'],
            DB::table('library_resources')
                ->where('library_section_id', $sectionId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('title')
                ->all()
        );
        $this->assertSame(13, (int) DB::table('library_resources')->where('title', 'Next Link')->value('sort_order'));
    }

    public function test_library_root_add_folder_infers_single_teacher_subject(): void
    {
        $teacher = $this->userWithRole('teacher');

        DB::table('subjects')->insert([
            'id' => 20,
            'title' => 'Language and Literature',
        ]);
        DB::table('teacher_subject_classes')->insert([
            'user_teacher_coteacher_id' => $teacher->id,
            'subject_id' => 20,
            'subject_name' => 'Language and Literature',
            'status' => 'current',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.get_library'))
            ->assertOk()
            ->assertSee('Language and Literature Library')
            ->assertSee('value="20"', false)
            ->assertDontSee('Choose subject');
    }

    public function test_multi_subject_teacher_uses_subject_hub_before_adding_root_folder(): void
    {
        $teacher = $this->userWithRole('teacher');

        DB::table('subjects')->insert([
            ['id' => 20, 'title' => 'Language and Literature'],
            ['id' => 21, 'title' => 'Mathematics'],
        ]);
        DB::table('teacher_subject_classes')->insert([
            [
                'user_teacher_coteacher_id' => $teacher->id,
                'subject_id' => 20,
                'subject_name' => 'Language and Literature',
                'status' => 'current',
            ],
            [
                'user_teacher_coteacher_id' => $teacher->id,
                'subject_id' => 21,
                'subject_name' => 'Mathematics',
                'status' => 'current',
            ],
        ]);
        DB::table('library_sections')->insert([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'title' => 'Language Folder',
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.get_library'))
            ->assertOk()
            ->assertSee('Choose a subject to manage reusable resources')
            ->assertSee('Language and Literature')
            ->assertSee('Mathematics')
            ->assertDontSee('Language Folder')
            ->assertDontSee('Add Library folder');

        $this->actingAs($teacher)
            ->get(route('teacher.get_library', ['subject' => 20]))
            ->assertOk()
            ->assertSee('Language and Literature Library')
            ->assertSee('Language Folder')
            ->assertSee('value="20"', false)
            ->assertDontSee('Choose subject');
    }

    public function test_teacher_can_reorder_library_folder_page_items(): void
    {
        $teacher = $this->userWithRole('teacher');

        DB::table('subjects')->insert([
            'id' => 20,
            'title' => 'Language and Literature',
        ]);
        DB::table('teacher_subject_classes')->insert([
            'user_teacher_coteacher_id' => $teacher->id,
            'subject_id' => 20,
            'subject_name' => 'Language and Literature',
            'status' => 'current',
        ]);
        $parentId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'title' => 'Unit Folder',
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
        ]);
        $childId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'parent_id' => $parentId,
            'title' => 'Child Folder',
            'sort_order' => 1,
            'created_by_user_id' => $teacher->id,
        ]);
        $firstResourceId = DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'library_section_id' => $parentId,
            'resource_type' => 'link',
            'title' => 'First Link',
            'external_url' => 'https://example.com/first',
            'sort_order' => 2,
            'created_by_user_id' => $teacher->id,
        ]);
        $secondResourceId = DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'library_section_id' => $parentId,
            'resource_type' => 'link',
            'title' => 'Second Link',
            'external_url' => 'https://example.com/second',
            'sort_order' => 3,
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->patchJson(route('teacher.library.reorder'), [
                'subject_id' => 20,
                'parent_id' => $parentId,
                'items' => [
                    ['type' => 'resource', 'id' => $secondResourceId],
                    ['type' => 'section', 'id' => $childId],
                    ['type' => 'resource', 'id' => $firstResourceId],
                ],
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertSame(1, (int) DB::table('library_resources')->where('id', $secondResourceId)->value('sort_order'));
        $this->assertSame(2, (int) DB::table('library_sections')->where('id', $childId)->value('sort_order'));
        $this->assertSame(3, (int) DB::table('library_resources')->where('id', $firstResourceId)->value('sort_order'));

        $this->actingAs($teacher)
            ->get(route('teacher.get_library', ['folder' => $parentId]))
            ->assertOk()
            ->assertSeeInOrder(['Second Link', 'Child Folder', 'First Link']);
    }

    public function test_library_reorder_rejects_cross_folder_resources(): void
    {
        $teacher = $this->userWithRole('teacher');

        DB::table('subjects')->insert([
            'id' => 20,
            'title' => 'Language and Literature',
        ]);
        DB::table('teacher_subject_classes')->insert([
            'user_teacher_coteacher_id' => $teacher->id,
            'subject_id' => 20,
            'subject_name' => 'Language and Literature',
            'status' => 'current',
        ]);
        $firstSectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'title' => 'First Folder',
            'created_by_user_id' => $teacher->id,
        ]);
        $secondSectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'title' => 'Second Folder',
            'created_by_user_id' => $teacher->id,
        ]);
        $resourceId = DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'library_section_id' => $secondSectionId,
            'resource_type' => 'link',
            'title' => 'Wrong Folder Link',
            'external_url' => 'https://example.com/wrong',
            'sort_order' => 7,
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->patchJson(route('teacher.library.reorder'), [
                'subject_id' => 20,
                'parent_id' => $firstSectionId,
                'items' => [
                    ['type' => 'resource', 'id' => $resourceId],
                ],
            ])
            ->assertStatus(422);

        $this->assertSame(7, (int) DB::table('library_resources')->where('id', $resourceId)->value('sort_order'));
    }

    public function test_teacher_owned_link_resource_opens_normally(): void
    {
        $teacher = $this->userWithRole('teacher');

        DB::table('subjects')->insert([
            'id' => 20,
            'title' => 'Language and Literature',
        ]);
        DB::table('teacher_subject_classes')->insert([
            'user_teacher_coteacher_id' => $teacher->id,
            'subject_id' => 20,
            'subject_name' => 'Language and Literature',
            'status' => 'current',
        ]);
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'title' => 'Links',
            'created_by_user_id' => $teacher->id,
        ]);
        $resourceId = DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'library_section_id' => $sectionId,
            'resource_type' => 'link',
            'title' => 'Framed Link',
            'external_url' => 'https://example.com/resource',
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.library.resources.open', [
                'resource' => $resourceId,
                'return_to' => url('teacher/library?folder='.$sectionId),
            ]))
            ->assertRedirect('https://example.com/resource');
    }

    public function test_teacher_owned_file_resource_opens_in_library_preview_page(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('library-resources/sample.pdf', 'pdf');

        $teacher = $this->userWithRole('teacher');

        DB::table('subjects')->insert([
            'id' => 20,
            'title' => 'Language and Literature',
        ]);
        DB::table('teacher_subject_classes')->insert([
            'user_teacher_coteacher_id' => $teacher->id,
            'subject_id' => 20,
            'subject_name' => 'Language and Literature',
            'status' => 'current',
        ]);
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'title' => 'Files',
            'created_by_user_id' => $teacher->id,
        ]);
        $resourceId = DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => 20,
            'library_section_id' => $sectionId,
            'resource_type' => 'file',
            'title' => 'Sample',
            'storage_disk' => 'public',
            'file_path' => 'library-resources/sample.pdf',
            'original_filename' => 'sample.pdf',
            'mime_type' => 'application/pdf',
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.library.resources.open', $resourceId))
            ->assertOk()
            ->assertSee('Library file')
            ->assertSee('Sample')
            ->assertSee('Files')
            ->assertSee('Back to folder')
            ->assertSee(route('teacher.library.resources.file', $resourceId), false);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
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

        if (! Schema::hasTable('subjects')) {
            Schema::create('subjects', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('teacher_subject_classes')) {
            Schema::create('teacher_subject_classes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_teacher_coteacher_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('subject_name')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('library_sections')) {
            Schema::create('library_sections', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('owner_user_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->smallInteger('sort_order')->default(0);
                $table->unsignedBigInteger('created_by_user_id');
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('library_resources')) {
            Schema::create('library_resources', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('owner_user_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('library_section_id');
                $table->string('resource_type');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->string('storage_disk')->nullable();
                $table->string('file_path', 2048)->nullable();
                $table->string('original_filename')->nullable();
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('external_url', 2048)->nullable();
                $table->smallInteger('sort_order')->default(0);
                $table->unsignedBigInteger('created_by_user_id');
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('attachment_files')) {
            Schema::create('attachment_files', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->string('type')->nullable();
                $table->string('path', 2048)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('session_task_id')->nullable();
            });
        }
    }
}
