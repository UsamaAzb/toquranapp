<?php

namespace Tests\Feature\CoreLms;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
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

        foreach (['admin', 'super_admin', 'teacher', 'student', 'parent'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_teacher_and_admin_can_open_shared_library_without_legacy_cards(): void
    {
        $teacher = $this->userWithRole('teacher');
        $admin = $this->userWithRole('admin');

        $this->actingAs($teacher)
            ->get(route('teacher.get_library'))
            ->assertOk()
            ->assertSee('To Quran Library')
            ->assertSee('Shared Library')
            ->assertDontSee('href="'.url('tutriols/level-up').'"', false)
            ->assertDontSee('Vocabulary');

        $this->actingAs($admin)
            ->get(route('admin.library.index'))
            ->assertOk()
            ->assertSee('Source')
            ->assertDontSee('Add Surah')
            ->assertDontSee('Private legacy Library source')
            ->assertDontSee('href="'.url('course/radio').'"', false);
    }

    public function test_teacher_cannot_use_admin_library_tab_route(): void
    {
        $teacher = $this->userWithRole('teacher');

        $this->actingAs($teacher)
            ->get(route('admin.library.index'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->get(route('teacher.get_library'))
            ->assertOk()
            ->assertSee('To Quran Library');
    }

    public function test_non_teacher_library_users_cannot_open_teacher_library(): void
    {
        $this->actingAs($this->userWithRole('student'))
            ->get(route('teacher.get_library'))
            ->assertForbidden();

        $this->actingAs($this->userWithRole('parent'))
            ->get(route('teacher.get_library'))
            ->assertForbidden();

        $this->app['auth']->guard()->logout();

        $this->get(route('teacher.get_library'))
            ->assertRedirect(route('login'));
    }

    public function test_teacher_created_folder_is_shared_but_edit_owned_only(): void
    {
        $owner = $this->userWithRole('teacher');
        $otherTeacher = $this->userWithRole('teacher');
        $admin = $this->userWithRole('admin');

        $this->actingAs($owner)
            ->post(route('teacher.general-library.folders.store'), [
                'title' => 'Tajweed Clips',
                'description' => 'Short reusable classroom sources',
            ])
            ->assertRedirect();

        $folderId = (int) DB::table('general_library_folders')->where('title', 'Tajweed Clips')->value('id');

        $this->actingAs($otherTeacher)
            ->get(route('teacher.get_library'))
            ->assertOk()
            ->assertSee('Tajweed Clips');

        $this->actingAs($otherTeacher)
            ->patch(route('teacher.general-library.folders.archive', $folderId))
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('teacher.general-library.folders.archive', $folderId))
            ->assertRedirect();

        $this->assertDatabaseHas('general_library_folders', [
            'id' => $folderId,
            'status' => 'archived',
        ]);
    }

    public function test_source_folder_blocks_subfolders_and_parent_folder_blocks_sources(): void
    {
        $teacher = $this->userWithRole('teacher');

        $this->actingAs($teacher)
            ->post(route('teacher.general-library.folders.store'), [
                'title' => 'Quran Repetition',
            ])
            ->assertRedirect();

        $rootFolderId = (int) DB::table('general_library_folders')->where('title', 'Quran Repetition')->value('id');

        $this->actingAs($teacher)
            ->post(route('teacher.general-library.folders.store'), [
                'parent_id' => $rootFolderId,
                'title' => '001. Al-Faatiha',
                'content_mode' => 'sources_only',
            ])
            ->assertRedirect();

        $surahFolderId = (int) DB::table('general_library_folders')->where('title', '001. Al-Faatiha')->value('id');

        $this->actingAs($teacher)
            ->post(route('teacher.general-library.folders.store'), [
                'parent_id' => $surahFolderId,
                'title' => 'Nested Folder',
            ])
            ->assertSessionHasErrors('library_action');

        $this->actingAs($teacher)
            ->post(route('teacher.general-library.resources.store'), [
                'folder_id' => $rootFolderId,
                'resource_kind' => 'youtube',
                'title' => 'Wrong place',
                'external_url' => 'https://youtu.be/C7GFY46e__g',
            ])
            ->assertSessionHasErrors('library_action');

        $this->actingAs($teacher)
            ->post(route('teacher.general-library.resources.store'), [
                'folder_id' => $surahFolderId,
                'resource_kind' => 'youtube',
                'title' => 'Ayah 1',
                'external_url' => 'https://youtu.be/C7GFY46e__g',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('general_library_folders', [
            'id' => $surahFolderId,
            'content_mode' => 'sources_only',
        ]);
    }

    public function test_teacher_can_create_source_and_open_protected_file_preview(): void
    {
        Storage::fake('local');

        $teacher = $this->userWithRole('teacher');
        $folderId = DB::table('general_library_folders')->insertGetId([
            'title' => 'Quranic Arabic',
            'content_mode' => 'sources_only',
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.general-library.resources.store'), [
                'folder_id' => $folderId,
                'resource_kind' => 'file',
                'title' => 'Makharij worksheet',
                'description' => 'Shared PDF practice',
                'resource_files' => [
                    UploadedFile::fake()->create('makharij.pdf', 24, 'application/pdf'),
                ],
            ])
            ->assertRedirect();

        $resourceId = (int) DB::table('general_library_resources')->where('title', 'Makharij worksheet')->value('id');

        $this->assertDatabaseHas('general_library_resources', [
            'id' => $resourceId,
            'general_library_folder_id' => $folderId,
            'storage_disk' => 'local',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.general-library.resources.open', $resourceId))
            ->assertOk()
            ->assertSee('Makharij worksheet');

        $this->actingAs($teacher)
            ->get(route('teacher.general-library.resources.file', $resourceId))
            ->assertOk();
    }

    public function test_teacher_stages_general_library_files_before_saving_sources(): void
    {
        Storage::fake('local');

        $teacher = $this->userWithRole('teacher');
        $folderId = DB::table('general_library_folders')->insertGetId([
            'title' => 'Quranic Arabic',
            'content_mode' => 'sources_only',
            'created_by_user_id' => $teacher->id,
        ]);

        $mixedUpload = $this->actingAs($teacher)
            ->postJson(route('teacher.general-library.resources.upload-temp'), [
                'resource_files' => [
                    UploadedFile::fake()->create('unsafe.exe', 4, 'application/x-msdownload'),
                    UploadedFile::fake()->create('makharij.pdf', 24, 'application/pdf'),
                ],
            ])
            ->assertOk()
            ->assertJsonCount(1, 'blocked')
            ->assertJsonCount(1, 'files');

        $mixedToken = $mixedUpload->json('files.0.token');
        $this->assertIsString($mixedToken);
        $mixedPayload = json_decode(Crypt::decryptString($mixedToken), true, flags: JSON_THROW_ON_ERROR);
        Storage::disk('local')->assertExists($mixedPayload['path']);

        $this->actingAs($teacher)
            ->deleteJson(route('teacher.general-library.resources.upload-temp.delete'), [
                'uploaded_files' => [$mixedToken],
            ])
            ->assertOk();

        Storage::disk('local')->assertMissing($mixedPayload['path']);

        $upload = $this->actingAs($teacher)
            ->postJson(route('teacher.general-library.resources.upload-temp'), [
                'resource_files' => [
                    UploadedFile::fake()->create('makharij.pdf', 24, 'application/pdf'),
                ],
            ])
            ->assertOk()
            ->assertJsonCount(1, 'files')
            ->json('files.0');

        $this->assertIsString($upload['token']);

        $this->actingAs($teacher)
            ->post(route('teacher.general-library.resources.store'), [
                'folder_id' => $folderId,
                'resource_kind' => 'batch',
                'uploaded_files' => [$upload['token']],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('general_library_resources', [
            'title' => 'makharij',
            'resource_type' => 'file',
            'original_filename' => 'makharij.pdf',
            'storage_disk' => 'local',
        ]);
    }

    public function test_teacher_can_batch_add_general_library_links_and_youtube_sources(): void
    {
        $teacher = $this->userWithRole('teacher');
        $folderId = DB::table('general_library_folders')->insertGetId([
            'title' => 'Quranic Arabic',
            'content_mode' => 'sources_only',
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.general-library.resources.store'), [
                'folder_id' => $folderId,
                'resource_kind' => 'batch',
                'link_titles' => ['Arabic note'],
                'link_urls' => ['https://example.com/arabic-note'],
                'youtube_titles' => ['Short recitation'],
                'youtube_urls' => ['https://youtu.be/C7GFY46e__g'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('general_library_resources', [
            'general_library_folder_id' => $folderId,
            'title' => 'Arabic note',
            'resource_type' => 'link',
            'external_url' => 'https://example.com/arabic-note',
        ]);
        $this->assertDatabaseHas('general_library_resources', [
            'general_library_folder_id' => $folderId,
            'title' => 'Short recitation',
            'resource_type' => 'youtube',
            'external_url' => 'https://youtu.be/C7GFY46e__g',
        ]);
    }

    public function test_teacher_can_add_description_only_general_library_text_source(): void
    {
        $teacher = $this->userWithRole('teacher');
        $folderId = DB::table('general_library_folders')->insertGetId([
            'title' => 'Dua Bank',
            'content_mode' => 'sources_only',
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.general-library.resources.store'), [
                'folder_id' => $folderId,
                'resource_kind' => 'batch',
                'description' => "Before sleeping dua\nArabic and meaning source.",
            ])
            ->assertRedirect();

        $resourceId = (int) DB::table('general_library_resources')
            ->where('general_library_folder_id', $folderId)
            ->where('resource_type', 'text')
            ->value('id');

        $this->assertNotSame(0, $resourceId);
        $this->assertDatabaseHas('general_library_resources', [
            'id' => $resourceId,
            'general_library_folder_id' => $folderId,
            'resource_type' => 'text',
            'title' => 'Before sleeping dua',
            'description' => "Before sleeping dua\nArabic and meaning source.",
            'text_content' => "Before sleeping dua\nArabic and meaning source.",
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.general-library.resources.open', $resourceId))
            ->assertOk()
            ->assertSee('Before sleeping dua')
            ->assertSee('Arabic and meaning source.');
    }

    public function test_general_library_source_delete_archives_assigned_source_but_deletes_unused_source(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('local')->put('general-library-resources/unused.pdf', 'unused');
        Storage::disk('local')->put('general-library-resources/used.pdf', 'used');

        $teacher = $this->userWithRole('teacher');
        $unusedResourceId = DB::table('general_library_resources')->insertGetId([
            'resource_type' => 'file',
            'title' => 'Unused File',
            'storage_disk' => 'local',
            'file_path' => 'general-library-resources/unused.pdf',
            'created_by_user_id' => $teacher->id,
        ]);
        $usedResourceId = DB::table('general_library_resources')->insertGetId([
            'resource_type' => 'file',
            'title' => 'Used File',
            'storage_disk' => 'local',
            'file_path' => 'general-library-resources/used.pdf',
            'created_by_user_id' => $teacher->id,
        ]);
        Storage::disk('public')->put('attachments/general-library-resource-'.$usedResourceId.'/snapshot.pdf', 'used snapshot');
        DB::table('attachment_files')->insert([
            'title' => 'Used File',
            'type' => 'file',
            'path' => 'attachments/general-library-resource-'.$usedResourceId.'/snapshot.pdf',
        ]);

        $this->actingAs($teacher)
            ->delete(route('teacher.general-library.resources.delete', $usedResourceId))
            ->assertRedirect();

        $this->actingAs($teacher)
            ->delete(route('teacher.general-library.resources.delete', $unusedResourceId))
            ->assertRedirect();

        $this->assertDatabaseHas('general_library_resources', [
            'id' => $usedResourceId,
            'status' => 'archived',
        ]);
        $this->assertDatabaseMissing('general_library_resources', [
            'id' => $unusedResourceId,
        ]);
        Storage::disk('local')->assertExists('general-library-resources/used.pdf');
        Storage::disk('local')->assertMissing('general-library-resources/unused.pdf');
        Storage::disk('public')->assertExists('attachments/general-library-resource-'.$usedResourceId.'/snapshot.pdf');
    }

    public function test_general_library_folder_delete_removes_empty_folder_but_archives_folder_with_any_history(): void
    {
        $teacher = $this->userWithRole('teacher');
        $emptyFolderId = DB::table('general_library_folders')->insertGetId([
            'title' => 'Empty Teacher Folder',
            'created_by_user_id' => $teacher->id,
        ]);
        $nonEmptyFolderId = DB::table('general_library_folders')->insertGetId([
            'title' => 'Teacher Folder With Source',
            'created_by_user_id' => $teacher->id,
        ]);
        DB::table('general_library_resources')->insert([
            'general_library_folder_id' => $nonEmptyFolderId,
            'resource_type' => 'link',
            'title' => 'Kept Source',
            'external_url' => 'https://example.com/source',
            'created_by_user_id' => $teacher->id,
        ]);
        $archivedHistoryFolderId = DB::table('general_library_folders')->insertGetId([
            'title' => 'Teacher Folder With Archived History',
            'created_by_user_id' => $teacher->id,
        ]);
        DB::table('general_library_resources')->insert([
            'general_library_folder_id' => $archivedHistoryFolderId,
            'resource_type' => 'link',
            'title' => 'Archived Source',
            'external_url' => 'https://example.com/archived-source',
            'status' => 'archived',
            'created_by_user_id' => $teacher->id,
        ]);
        DB::table('general_library_folders')->insert([
            'parent_id' => $archivedHistoryFolderId,
            'title' => 'Archived Child Folder',
            'status' => 'archived',
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->delete(route('teacher.general-library.folders.delete', $emptyFolderId))
            ->assertRedirect();

        $this->actingAs($teacher)
            ->delete(route('teacher.general-library.folders.delete', $nonEmptyFolderId))
            ->assertRedirect();

        $this->actingAs($teacher)
            ->delete(route('teacher.general-library.folders.delete', $archivedHistoryFolderId))
            ->assertRedirect();

        $this->assertDatabaseMissing('general_library_folders', [
            'id' => $emptyFolderId,
        ]);
        $this->assertDatabaseHas('general_library_folders', [
            'id' => $nonEmptyFolderId,
            'status' => 'archived',
        ]);
        $this->assertDatabaseHas('general_library_folders', [
            'id' => $archivedHistoryFolderId,
            'status' => 'archived',
        ]);
    }

    public function test_general_library_folder_titles_are_unique_inside_same_parent(): void
    {
        $teacher = $this->userWithRole('teacher');

        $this->actingAs($teacher)
            ->post(route('teacher.general-library.folders.store'), [
                'title' => 'Teacher Osama',
            ])
            ->assertRedirect();

        $this->actingAs($teacher)
            ->post(route('teacher.general-library.folders.store'), [
                'title' => 'teacher osama',
            ])
            ->assertSessionHasErrors('library_action');

        $this->assertSame(1, DB::table('general_library_folders')
            ->whereRaw('LOWER(title) = ?', ['teacher osama'])
            ->count());
    }

    public function test_general_library_folder_edit_rejects_duplicate_sibling_title(): void
    {
        $teacher = $this->userWithRole('teacher');
        DB::table('general_library_folders')->insert([
            'title' => 'Quran Repetition',
            'created_by_user_id' => $teacher->id,
        ]);
        $folderId = DB::table('general_library_folders')->insertGetId([
            'title' => 'Quranic Arabic',
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->patch(route('teacher.general-library.folders.update', $folderId), [
                'title' => 'quran repetition',
            ])
            ->assertSessionHasErrors('library_action');

        $this->assertDatabaseHas('general_library_folders', [
            'id' => $folderId,
            'title' => 'Quranic Arabic',
        ]);
    }

    public function test_general_library_source_reorder_persists_shared_sequence(): void
    {
        $teacher = $this->userWithRole('teacher');
        $folderId = DB::table('general_library_folders')->insertGetId([
            'title' => '001. Al-Faatiha',
            'content_mode' => 'sources_only',
            'created_by_user_id' => $teacher->id,
        ]);
        $firstId = DB::table('general_library_resources')->insertGetId([
            'general_library_folder_id' => $folderId,
            'resource_type' => 'youtube',
            'title' => 'Ayahs 1-3',
            'external_url' => 'https://youtu.be/C7GFY46e__g',
            'sort_order' => 10,
            'created_by_user_id' => $teacher->id,
        ]);
        $secondId = DB::table('general_library_resources')->insertGetId([
            'general_library_folder_id' => $folderId,
            'resource_type' => 'youtube',
            'title' => 'Ayahs 4-6',
            'external_url' => 'https://youtu.be/C7GFY46e__g',
            'sort_order' => 20,
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->patchJson(route('teacher.general-library.items.reorder'), [
                'folder_id' => $folderId,
                'items' => [
                    ['type' => 'resource', 'id' => $secondId],
                    ['type' => 'resource', 'id' => $firstId],
                ],
            ])
            ->assertOk();

        $this->assertSame(
            ['Ayahs 4-6', 'Ayahs 1-3'],
            DB::table('general_library_resources')
                ->where('general_library_folder_id', $folderId)
                ->orderBy('sort_order')
                ->pluck('title')
                ->all()
        );
    }

    public function test_general_library_page_reorder_persists_folder_sequence(): void
    {
        $teacher = $this->userWithRole('teacher');
        $firstId = DB::table('general_library_folders')->insertGetId([
            'title' => 'Quran Repetition',
            'sort_order' => 10,
            'created_by_user_id' => $teacher->id,
        ]);
        $secondId = DB::table('general_library_folders')->insertGetId([
            'title' => 'Quranic Arabic',
            'sort_order' => 20,
            'created_by_user_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->patchJson(route('teacher.general-library.items.reorder'), [
                'folder_id' => null,
                'items' => [
                    ['type' => 'folder', 'id' => $secondId],
                    ['type' => 'folder', 'id' => $firstId],
                ],
            ])
            ->assertOk();

        $this->assertSame(
            ['Quranic Arabic', 'Quran Repetition'],
            DB::table('general_library_folders')
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->pluck('title')
                ->all()
        );
    }

    public function test_structured_quran_management_routes_are_not_launch_facing(): void
    {
        $superAdmin = $this->userWithRole('super_admin');

        $this->actingAs($superAdmin)
            ->get(route('teacher.get_library'))
            ->assertOk()
            ->assertSee('To Quran Library')
            ->assertDontSee('Add Surah');

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('teacher.general-library.quran.surahs.store'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('teacher.general-library.quran.videos.store'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('teacher.general-library.quran.videos.update'));
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

        if (! Schema::hasTable('general_library_folders')) {
            Schema::create('general_library_folders', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->string('source_label')->nullable();
                $table->string('content_mode')->default('mixed');
                $table->unsignedInteger('sort_order')->default(0);
                $table->unsignedBigInteger('created_by_user_id');
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('general_library_resources')) {
            Schema::create('general_library_resources', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('general_library_folder_id')->nullable();
                $table->string('resource_type');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->string('source_label')->nullable();
                $table->string('storage_disk')->nullable();
                $table->string('file_path', 2048)->nullable();
                $table->string('original_filename')->nullable();
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('external_url', 2048)->nullable();
                $table->text('text_content')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->unsignedBigInteger('created_by_user_id');
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
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
