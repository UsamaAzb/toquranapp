<?php

namespace Tests\Unit;

use App\Models\ClassSession;
use App\Models\SessionTask;
use App\Models\User;
use App\Services\Library\GeneralLibraryAttachmentAdapter;
use App\Services\Library\LibraryResourceAttachmentWriter;
use App\Support\BookingSubjectProvisioning;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LibraryResourceAttachmentWriterTest extends TestCase
{
    use RefreshDatabase;

    private const LANGUAGE_SUBJECT_ID = BookingSubjectProvisioning::SUBJECT_LANGUAGE_AND_LITERATURE;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
    }

    public function test_it_snapshots_active_library_resources_into_attachment_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('library-resources/source.pdf', 'legacy pdf');

        $this->seedFixture();

        $task = SessionTask::query()->findOrFail(51);
        $session = ClassSession::query()->findOrFail(21);

        $created = app(LibraryResourceAttachmentWriter::class)->writeForTask(
            $task,
            $session,
            [602, 601, 999],
            10
        );

        $this->assertSame(2, $created);
        $this->assertDatabaseHas('attachment_files', [
            'session_task_id' => 51,
            'title' => 'Useful Link',
            'type' => 'link',
            'path' => 'https://example.com/resource',
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'class_id' => 12,
            'teacher_subject_class_id' => 41,
        ]);
        $this->assertDatabaseHas('attachment_files', [
            'session_task_id' => 51,
            'title' => 'Source PDF',
            'type' => 'file',
            'path' => 'library-resources/source.pdf',
            'file_size' => 1024,
        ]);
    }

    public function test_it_snapshots_youtube_library_links_as_embedded_youtube_attachments(): void
    {
        $this->seedFixture();

        $task = SessionTask::query()->findOrFail(51);
        $session = ClassSession::query()->findOrFail(21);

        $created = app(LibraryResourceAttachmentWriter::class)->writeForTask(
            $task,
            $session,
            [607],
            10
        );

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('attachment_files', [
            'session_task_id' => 51,
            'title' => 'Video Link',
            'type' => 'youtube',
            'path' => 'https://www.youtube.com/embed/abc123',
        ]);
    }

    public function test_it_snapshots_general_library_resources_into_attachment_files(): void
    {
        $this->seedFixture();
        $teacher = User::factory()->create(['id' => 10]);
        Role::findOrCreate('teacher', 'web');
        $teacher->assignRole('teacher');

        DB::table('general_library_resources')->insert([
            'id' => 910,
            'general_library_folder_id' => null,
            'resource_type' => 'youtube',
            'title' => 'Shared Quran Repetition',
            'description' => 'Shared by the general Library',
            'status' => 'active',
            'external_url' => 'https://youtu.be/C7GFY46e__g',
            'sort_order' => 10,
            'created_by_user_id' => 10,
            'updated_by_user_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $created = app(LibraryResourceAttachmentWriter::class)->writeForTask(
            SessionTask::query()->findOrFail(51),
            ClassSession::query()->findOrFail(21),
            [GeneralLibraryAttachmentAdapter::GENERAL_PREFIX.'910'],
            10
        );

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('attachment_files', [
            'session_task_id' => 51,
            'title' => 'Shared Quran Repetition',
            'type' => 'youtube',
            'path' => 'https://www.youtube.com/embed/C7GFY46e__g',
        ]);
    }

    public function test_it_copies_general_library_files_into_task_attachment_snapshots(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('local')->put('general-library-resources/makharij.pdf', 'private source pdf');

        $this->seedFixture();
        $teacher = User::factory()->create(['id' => 10]);
        Role::findOrCreate('teacher', 'web');
        $teacher->assignRole('teacher');

        DB::table('general_library_resources')->insert([
            'id' => 911,
            'general_library_folder_id' => null,
            'resource_type' => 'file',
            'title' => 'Makharij PDF',
            'description' => 'Private Library source',
            'status' => 'active',
            'storage_disk' => 'local',
            'file_path' => 'general-library-resources/makharij.pdf',
            'original_filename' => 'makharij.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 18,
            'sort_order' => 10,
            'created_by_user_id' => 10,
            'updated_by_user_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $created = app(LibraryResourceAttachmentWriter::class)->writeForTask(
            SessionTask::query()->findOrFail(51),
            ClassSession::query()->findOrFail(21),
            [GeneralLibraryAttachmentAdapter::GENERAL_PREFIX.'911'],
            10
        );

        $this->assertSame(1, $created);
        $snapshotPath = (string) DB::table('attachment_files')
            ->where('session_task_id', 51)
            ->where('title', 'Makharij PDF')
            ->value('path');

        $this->assertStringStartsWith('attachments/general-library-resource-911/', $snapshotPath);
        Storage::disk('public')->assertExists($snapshotPath);
        Storage::disk('local')->assertExists('general-library-resources/makharij.pdf');
    }

    public function test_it_snapshots_legacy_library_sources_as_link_attachments(): void
    {
        config(['toquran.legacy_library_owner_user_ids' => [10]]);
        User::factory()->create(['id' => 10]);
        $this->seedFixture();

        $task = SessionTask::query()->findOrFail(51);
        $session = ClassSession::query()->findOrFail(21);

        $created = app(LibraryResourceAttachmentWriter::class)->writeForTask(
            $task,
            $session,
            ['series__level_up__1'],
            10
        );

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('attachment_files', [
            'session_task_id' => 51,
            'title' => 'Level 1',
            'type' => 'link',
            // Legacy path preserves the original "tutriols" typo from production.
            'path' => url('tutriols/level-up/level-1'),
        ]);
    }

    public function test_it_materializes_vocabulary_games_from_library_picker_selection(): void
    {
        config(['toquran.legacy_library_owner_user_ids' => [10]]);
        User::factory()->create(['id' => 10]);
        $this->seedFixture();

        DB::table('vocabulary_sets')->insert([
            'id' => 701,
            'parent_id' => null,
            'title' => 'Vocabulary Course',
            'description' => null,
            'node_type' => 'folder',
            'set_type' => 'teacher',
            'source_kind' => 'custom',
            'source_key' => null,
            'owner_user_id' => 10,
            'visibility' => 'private',
            'sort_order' => 1,
            'created_by_user_id' => 10,
            'updated_by_user_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('vocabulary_sets')->insert([
            'id' => 702,
            'parent_id' => 701,
            'title' => 'Lesson 1',
            'description' => null,
            'node_type' => 'playable',
            'set_type' => 'teacher',
            'source_kind' => 'custom',
            'source_key' => null,
            'owner_user_id' => 10,
            'visibility' => 'private',
            'sort_order' => 1,
            'created_by_user_id' => 10,
            'updated_by_user_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $task = SessionTask::query()->findOrFail(51);
        $session = ClassSession::query()->findOrFail(21);

        $created = app(LibraryResourceAttachmentWriter::class)->writeForTask(
            $task,
            $session,
            ['series__vocabulary_list__702'],
            10
        );

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('vocabulary_game_assignments', [
            'vocabulary_set_id' => 702,
            'assigned_by_user_id' => 10,
            'audience_type' => 'class',
            'audience_id' => 12,
            'status' => 'active',
        ]);
        $assignmentId = DB::table('vocabulary_game_assignments')->value('id');
        $this->assertDatabaseHas('attachment_files', [
            'session_task_id' => 51,
            'title' => 'Vocab Game: Lesson 1',
            'type' => 'link',
            'path' => route('vocabulary.games.assignment', ['assignment' => $assignmentId]),
        ]);
    }

    public function test_it_preserves_mixed_new_and_legacy_selection_order(): void
    {
        config(['toquran.legacy_library_owner_user_ids' => [10]]);
        User::factory()->create(['id' => 10]);
        $this->seedFixture();

        $task = SessionTask::query()->findOrFail(51);
        $session = ClassSession::query()->findOrFail(21);

        $created = app(LibraryResourceAttachmentWriter::class)->writeForTask(
            $task,
            $session,
            [602, 'series__level_up__1', 601],
            10
        );

        $this->assertSame(3, $created);

        $attachments = DB::table('attachment_files')
            ->where('session_task_id', 51)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['title', 'sort_order']);

        $this->assertSame(['Useful Link', 'Level 1', 'Source PDF'], $attachments->pluck('title')->all());
        $this->assertSame([1, 2, 3], $attachments->pluck('sort_order')->all());
    }

    public function test_it_appends_sort_order_after_existing_task_attachments(): void
    {
        $this->seedFixture();

        DB::table('attachment_files')->insert([
            'id' => 800,
            'session_task_id' => 51,
            'title' => 'Existing Attachment',
            'description' => 'Existing',
            'type' => 'file',
            'path' => 'attachments/existing.pdf',
            'file_size' => 10,
            'sort_order' => 20,
        ]);

        $task = SessionTask::query()->findOrFail(51);
        $session = ClassSession::query()->findOrFail(21);

        $created = app(LibraryResourceAttachmentWriter::class)->writeForTask(
            $task,
            $session,
            [601, 602],
            10
        );

        $this->assertSame(2, $created);

        $attachments = DB::table('attachment_files')
            ->where('session_task_id', 51)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['title', 'sort_order']);

        $this->assertSame(['Existing Attachment', 'Source PDF', 'Useful Link'], $attachments->pluck('title')->all());
        $this->assertSame([20, 21, 22], $attachments->pluck('sort_order')->all());
    }

    public function test_it_ignores_archived_wrong_subject_and_wrong_owner_resources(): void
    {
        $this->seedFixture();

        $task = SessionTask::query()->findOrFail(51);
        $session = ClassSession::query()->findOrFail(21);

        $created = app(LibraryResourceAttachmentWriter::class)->writeForTask(
            $task,
            $session,
            [603, 604, 605, 606],
            10
        );

        $this->assertSame(0, $created);
        $this->assertDatabaseCount('attachment_files', 0);
    }

    public function test_it_rejects_resources_owned_by_another_teacher(): void
    {
        $this->seedFixture();

        $task = SessionTask::query()->findOrFail(51);
        $session = ClassSession::query()->findOrFail(21);

        $created = app(LibraryResourceAttachmentWriter::class)->writeForTask(
            $task,
            $session,
            [605],
            10
        );

        $this->assertSame(0, $created);
        $this->assertDatabaseMissing('attachment_files', [
            'title' => 'Wrong Owner',
            'path' => 'library-resources/wrong-owner.pdf',
        ]);
    }

    private function seedFixture(): void
    {
        DB::table('subjects')->insertOrIgnore([
            'id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Language and Literature',
        ]);
        DB::table('level_up')->insertOrIgnore([
            'id' => 1,
            'title' => 'Level 1',
            'slug' => 'level-1',
            'iframe_link' => null,
            'sort' => 1,
        ]);

        DB::table('teacher_subject_classes')->insert([
            'id' => 41,
            'user_teacher_coteacher_id' => 10,
            'class_id' => 12,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'status' => 'active',
        ]);

        DB::table('class_sessions')->insert([
            'id' => 21,
            'teacher_subject_classes_id' => 41,
            'class_id' => 12,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'student_id' => null,
            'title' => 'Session',
            'date' => now()->toDateString(),
        ]);

        DB::table('session_tasks')->insert([
            'id' => 51,
            'class_session_id' => 21,
            'task_type_id' => 7,
            'title' => 'Task',
            'description' => 'Task instructions',
            'default_points' => 5,
            'max_points' => 10,
            'status' => 'draft',
            'created_at' => now(),
        ]);

        DB::table('library_sections')->insert([
            [
                'id' => 501,
                'owner_user_id' => 10,
                'subject_id' => self::LANGUAGE_SUBJECT_ID,
                'title' => 'Library',
                'status' => 'active',
                'created_by_user_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 502,
                'owner_user_id' => 10,
                'subject_id' => self::LANGUAGE_SUBJECT_ID,
                'title' => 'Archived Folder',
                'status' => 'archived',
                'created_by_user_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('library_resources')->insert([
            [
                'id' => 601,
                'owner_user_id' => 10,
                'subject_id' => self::LANGUAGE_SUBJECT_ID,
                'library_section_id' => 501,
                'resource_type' => 'file',
                'title' => 'Source PDF',
                'description' => 'Library file',
                'status' => 'active',
                'storage_disk' => 'public',
                'file_path' => 'library-resources/source.pdf',
                'original_filename' => 'source.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1024,
                'external_url' => null,
                'created_by_user_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 602,
                'owner_user_id' => 10,
                'subject_id' => self::LANGUAGE_SUBJECT_ID,
                'library_section_id' => 501,
                'resource_type' => 'link',
                'title' => 'Useful Link',
                'description' => 'Library link',
                'status' => 'active',
                'external_url' => 'https://example.com/resource',
                'storage_disk' => null,
                'file_path' => null,
                'original_filename' => null,
                'mime_type' => null,
                'file_size' => null,
                'created_by_user_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 603,
                'owner_user_id' => 10,
                'subject_id' => self::LANGUAGE_SUBJECT_ID,
                'library_section_id' => 501,
                'resource_type' => 'file',
                'title' => 'Archived PDF',
                'description' => null,
                'status' => 'archived',
                'storage_disk' => 'public',
                'file_path' => 'library-resources/archived.pdf',
                'original_filename' => 'archived.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1024,
                'external_url' => null,
                'created_by_user_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 607,
                'owner_user_id' => 10,
                'subject_id' => self::LANGUAGE_SUBJECT_ID,
                'library_section_id' => 501,
                'resource_type' => 'link',
                'title' => 'Video Link',
                'description' => 'Library video',
                'status' => 'active',
                'external_url' => 'https://www.youtube.com/watch?v=abc123',
                'storage_disk' => null,
                'file_path' => null,
                'original_filename' => null,
                'mime_type' => null,
                'file_size' => null,
                'created_by_user_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 604,
                'owner_user_id' => 10,
                'subject_id' => 8,
                'library_section_id' => 501,
                'resource_type' => 'file',
                'title' => 'Wrong Subject',
                'description' => null,
                'status' => 'active',
                'storage_disk' => 'public',
                'file_path' => 'library-resources/wrong-subject.pdf',
                'original_filename' => 'wrong-subject.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1024,
                'external_url' => null,
                'created_by_user_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 605,
                'owner_user_id' => 11,
                'subject_id' => self::LANGUAGE_SUBJECT_ID,
                'library_section_id' => 501,
                'resource_type' => 'file',
                'title' => 'Wrong Owner',
                'description' => null,
                'status' => 'active',
                'storage_disk' => 'public',
                'file_path' => 'library-resources/wrong-owner.pdf',
                'original_filename' => 'wrong-owner.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1024,
                'external_url' => null,
                'created_by_user_id' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 606,
                'owner_user_id' => 10,
                'subject_id' => self::LANGUAGE_SUBJECT_ID,
                'library_section_id' => 502,
                'resource_type' => 'file',
                'title' => 'Archived Folder PDF',
                'description' => null,
                'status' => 'active',
                'storage_disk' => 'public',
                'file_path' => 'library-resources/archived-folder.pdf',
                'original_filename' => 'archived-folder.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1024,
                'external_url' => null,
                'created_by_user_id' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function createTables(): void
    {
        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('teacher_subject_classes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_teacher_coteacher_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('class_sessions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('teacher_subject_classes_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('main_daily_session_template_id')->nullable();
            $table->string('title')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });

        Schema::create('session_tasks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('class_session_id')->nullable();
            $table->unsignedBigInteger('task_type_id')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('default_points')->default(0);
            $table->integer('max_points')->default(0);
            $table->string('status')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('attachment_files', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('session_task_id')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->string('path', 2048)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('teacher_subject_class_id')->nullable();
        });

        Schema::create('library_sections', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('owner_user_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

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
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedBigInteger('created_by_user_id');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

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
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('created_by_user_id');
            $table->unsignedBigInteger('updated_by_user_id')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
        Schema::create('vocabulary_sets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('node_type');
            $table->string('set_type');
            $table->string('source_kind');
            $table->string('source_key')->nullable();
            $table->unsignedBigInteger('owner_user_id')->nullable();
            $table->string('visibility');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->unsignedBigInteger('updated_by_user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('vocabulary_game_assignments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('vocabulary_set_id');
            $table->unsignedBigInteger('assigned_by_user_id');
            $table->string('audience_type');
            $table->unsignedBigInteger('audience_id');
            $table->json('allowed_games');
            $table->string('difficulty_policy');
            $table->string('status');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('level_up', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('iframe_link')->nullable();
            $table->integer('sort')->default(0);
        });
    }
}
