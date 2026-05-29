<?php

namespace Tests\Feature\CoreLms;

use App\Livewire\Teacher\AddDailySession;
use App\Livewire\Teacher\SessionsBoard as TeacherSessionsBoard;
use App\Livewire\Teacher\ShowDailySessionTask;
use App\Livewire\Teacher\ShowSessionTask;
use App\Models\AttachmentFile;
use App\Models\ClassSession;
use App\Models\User;
use App\Services\AttachmentService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherAttachmentStateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->createTeacherAttachmentTables();
    }

    public function test_show_session_task_edit_replaces_deleted_file_without_leaving_orphans(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowSessionTask::class)
            ->call('openEdit', 51)
            ->set('files', [
                UploadedFile::fake()->create('staged.pdf', 10, 'application/pdf'),
                UploadedFile::fake()->create('replacement.pdf', 12, 'application/pdf'),
            ])
            ->call('removeFile', 0)
            ->call('markAttachmentForDeletion', 101)
            ->call('updateTask');

        $this->assertSame(1, DB::table('attachment_files')->where('session_task_id', 51)->count());
        $this->assertDatabaseMissing('attachment_files', ['id' => 101]);
        $this->assertDatabaseHas('attachment_files', [
            'session_task_id' => 51,
            'title' => 'replacement.pdf',
            'type' => 'file',
        ]);
    }

    public function test_show_session_task_edit_requires_meaningful_content_after_deleting_all_existing_files(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowSessionTask::class)
            ->call('openEdit', 51)
            ->set('description', '   ')
            ->call('markAttachmentForDeletion', 101)
            ->call('updateTask')
            ->assertHasErrors(['content']);

        $this->assertDatabaseHas('attachment_files', ['id' => 101, 'session_task_id' => 51]);
        $this->assertSame(1, DB::table('attachment_files')->where('session_task_id', 51)->count());
    }

    public function test_show_session_task_edit_allows_description_only_after_deleting_existing_files(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowSessionTask::class)
            ->call('openEdit', 51)
            ->set('description', 'Teacher note only')
            ->call('markAttachmentForDeletion', 101)
            ->call('updateTask')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('attachment_files', ['id' => 101]);
        $this->assertSame(0, DB::table('attachment_files')->where('session_task_id', 51)->count());
        $this->assertDatabaseHas('session_tasks', [
            'id' => 51,
            'description' => 'Teacher note only',
        ]);
    }

    public function test_show_session_task_deleting_library_snapshot_keeps_shared_library_file(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);
        $this->seedLibraryFixture($teacher->id, 'attachments/existing.pdf');
        Storage::disk('public')->put('attachments/existing.pdf', 'PDF');

        $this->actingAs($teacher);

        Livewire::test(ShowSessionTask::class)
            ->call('openEdit', 51)
            ->set('description', 'Keep the task valid')
            ->call('markAttachmentForDeletion', 101)
            ->call('updateTask')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('attachment_files', ['id' => 101]);
        Storage::disk('public')->assertExists('attachments/existing.pdf');
    }

    public function test_show_session_task_edit_uses_protected_route_for_existing_file_links(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);

        $this->actingAs($teacher);

        $component = Livewire::test(ShowSessionTask::class)
            ->call('openEdit', 51);

        $existingFiles = $component->get('existingFiles');

        $this->assertSame(
            route('teacher.sessions.attachment.show', ['session' => 21, 'attachment' => 101]),
            $existingFiles[0]['url'] ?? null
        );
        $this->assertStringNotContainsString('/storage/', $existingFiles[0]['url'] ?? '');
    }

    public function test_show_session_task_reorders_existing_attachments(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);

        DB::table('attachment_files')->where('id', 101)->update(['sort_order' => 1]);
        DB::table('attachment_files')->insert([
            [
                'id' => 102,
                'session_task_id' => 51,
                'title' => 'reading link',
                'description' => 'Reading link',
                'type' => 'link',
                'path' => 'https://example.com/reading',
                'file_size' => null,
                'subject_id' => 9,
                'class_id' => 12,
                'teacher_subject_class_id' => 41,
                'sort_order' => 2,
            ],
            [
                'id' => 103,
                'session_task_id' => 51,
                'title' => 'lesson video',
                'description' => 'Lesson video',
                'type' => 'youtube',
                'path' => 'https://www.youtube.com/watch?v=abc123',
                'file_size' => null,
                'subject_id' => 9,
                'class_id' => 12,
                'teacher_subject_class_id' => 41,
                'sort_order' => 3,
            ],
        ]);

        $this->actingAs($teacher);

        $component = Livewire::test(ShowSessionTask::class)
            ->call('openEdit', 51)
            ->call('reorderExistingAttachments', 51, [103, 101, 102]);

        $this->assertSame([103, 101, 102], collect($component->get('existingAttachments'))->pluck('id')->all());
        $this->assertSame(
            [103, 101, 102],
            DB::table('attachment_files')
                ->where('session_task_id', 51)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('id')
                ->all()
        );
    }

    public function test_show_session_task_reorders_selected_library_resources_before_save(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);
        $this->seedLibraryFixture($teacher->id);
        DB::table('library_resources')->insert([
            'id' => 602,
            'owner_user_id' => $teacher->id,
            'subject_id' => 9,
            'library_section_id' => 501,
            'resource_type' => 'file',
            'title' => 'Second Source PDF',
            'description' => 'Second Library source',
            'status' => 'active',
            'storage_disk' => 'public',
            'file_path' => 'library-resources/second.pdf',
            'original_filename' => 'second.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 4,
            'created_by_user_id' => $teacher->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($teacher);

        $component = Livewire::test(ShowSessionTask::class)
            ->call('open', 21)
            ->call('useLibraryResources', ['601', '602'])
            ->call('reorderSelectedLibraryResources', ['602', '601']);

        $this->assertSame(['602', '601'], $component->get('selectedLibraryResourceIds'));
        $this->assertSame(
            ['Second Source PDF', 'Source PDF'],
            collect($component->get('selectedLibraryResources'))->pluck('title')->all()
        );
    }

    public function test_teacher_attachment_service_prepares_protected_file_urls(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);
        Storage::disk('public')->put('attachments/existing.pdf', 'PDF');

        $attachment = AttachmentFile::with('task.classSession')->findOrFail(101);
        $session = ClassSession::query()->findOrFail(21);
        $protectedUrl = route('teacher.sessions.attachment.file', ['session' => 21, 'attachment' => 101]);
        $downloadUrl = route('teacher.sessions.attachment.file', [
            'session' => 21,
            'attachment' => 101,
            'download' => 1,
        ]);

        $data = app(AttachmentService::class)->prepareTeacherSessionViewData($attachment, $session, 21);

        $this->assertSame($protectedUrl, $data['fileUrl']);
        $this->assertSame($downloadUrl, $data['downloadUrl']);
        $this->assertSame('file', $data['type']);
        $this->assertSame('pdf', $data['ext']);
        $this->assertTrue($data['fileAvailable']);
        $this->assertStringNotContainsString('/storage/attachments/existing.pdf', $data['fileUrl']);
    }

    public function test_show_session_task_edit_rejects_mismatched_session_context(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);

        DB::table('class_sessions')->insert([
            'id' => 22,
            'teacher_subject_classes_id' => 41,
            'class_id' => 12,
            'subject_id' => 10,
            'student_id' => null,
            'main_daily_session_template_id' => null,
            'title' => 'Different Session',
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($teacher);

        Livewire::test(ShowSessionTask::class)
            ->call('openEdit', 51)
            ->set('sessionId', 22)
            ->call('updateTask')
            ->assertStatus(404);
    }

    public function test_show_daily_session_task_edit_replaces_deleted_file_without_leaving_orphans(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedDailySessionFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowDailySessionTask::class)
            ->call('openEdit', 61)
            ->set('files', [
                UploadedFile::fake()->create('staged.pdf', 10, 'application/pdf'),
                UploadedFile::fake()->create('replacement.pdf', 12, 'application/pdf'),
            ])
            ->call('removeFile', 0)
            ->call('markAttachmentForDeletion', 201)
            ->call('updateTask');

        $this->assertSame(1, DB::table('daily_attachment_files')->where('daily_session_task_id', 61)->count());
        $this->assertDatabaseMissing('daily_attachment_files', ['id' => 201]);
        $this->assertDatabaseHas('daily_attachment_files', [
            'daily_session_task_id' => 61,
            'title' => 'replacement.pdf',
            'type' => 'file',
        ]);
    }

    public function test_show_daily_session_task_edit_requires_meaningful_content_after_deleting_all_existing_files(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedDailySessionFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowDailySessionTask::class)
            ->call('openEdit', 61)
            ->set('description', '   ')
            ->call('markAttachmentForDeletion', 201)
            ->call('updateTask')
            ->assertHasErrors(['content']);

        $this->assertDatabaseHas('daily_attachment_files', ['id' => 201, 'daily_session_task_id' => 61]);
        $this->assertSame(1, DB::table('daily_attachment_files')->where('daily_session_task_id', 61)->count());
    }

    public function test_show_daily_session_task_edit_allows_description_only_after_deleting_existing_files(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedDailySessionFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowDailySessionTask::class)
            ->call('openEdit', 61)
            ->set('description', 'Daily note only')
            ->call('markAttachmentForDeletion', 201)
            ->call('updateTask')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('daily_attachment_files', ['id' => 201]);
        $this->assertSame(0, DB::table('daily_attachment_files')->where('daily_session_task_id', 61)->count());
        $this->assertDatabaseHas('daily_session_tasks', [
            'id' => 61,
            'description' => 'Daily note only',
        ]);
    }

    public function test_show_session_task_save_is_blocked_while_uploads_are_in_progress(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowSessionTask::class)
            ->call('open', 21)
            ->set('uploadsInProgress', true)
            ->call('save')
            ->assertHasErrors(['files']);

        $this->assertSame(1, DB::table('session_tasks')->where('class_session_id', 21)->count());
    }

    public function test_show_session_task_renders_direct_upload_progress_controls(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowSessionTask::class)
            ->call('open', 21)
            ->assertSee('Uploading selected files...', false)
            ->assertSee('Clear selection', false)
            ->assertSee('window.w14TaskUploadState', false);
    }

    public function test_show_session_task_direct_uploads_can_be_removed_and_saved_in_order(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowSessionTask::class)
            ->call('open', 21)
            ->set('title', 'Direct upload task')
            ->set('description', 'Direct upload instructions')
            ->set('files', [
                UploadedFile::fake()->create('first.pdf', 10, 'application/pdf'),
                UploadedFile::fake()->create('remove-me.pdf', 10, 'application/pdf'),
                UploadedFile::fake()->create('second.pdf', 10, 'application/pdf'),
            ])
            ->call('reorderPendingFiles', ['second.pdf', 'first.pdf', 'remove-me.pdf'])
            ->call('removeFile', 2)
            ->call('save')
            ->assertHasNoErrors();

        $taskId = DB::table('session_tasks')
            ->where('title', 'Direct upload task')
            ->value('id');

        $this->assertNotNull($taskId);
        $this->assertSame(
            ['second.pdf', 'first.pdf'],
            DB::table('attachment_files')
                ->where('session_task_id', $taskId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('title')
                ->all()
        );
    }

    public function test_show_session_task_unified_draft_tray_saves_mixed_order_without_duplicates(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);
        $this->seedLibraryFixture($teacher->id);

        $this->actingAs($teacher);

        $component = Livewire::test(ShowSessionTask::class)
            ->call('openEdit', 51)
            ->set('files', [
                UploadedFile::fake()->create('uploaded.pdf', 10, 'application/pdf'),
            ])
            ->call('useLibraryResources', ['601'])
            ->set('links', [
                ['key' => 'manual-link', 'title' => 'Docs', 'url' => 'https://example.com/docs'],
            ])
            ->set('youtubes', [
                ['key' => 'manual-youtube', 'title' => 'Clip', 'url' => 'https://www.youtube.com/watch?v=abc123'],
            ]);

        $draftOrder = $component->get('attachmentDraftOrder');
        $pendingFileKey = collect($draftOrder)
            ->first(fn (string $key): bool => str_starts_with($key, 'pending_file:'));

        $this->assertNotNull($pendingFileKey);

        $component
            ->call('reorderAttachmentDraftItems', [
                'library:601',
                $pendingFileKey,
                'link:manual-link',
                'existing:101',
                'youtube:manual-youtube',
            ])
            ->call('updateTask')
            ->assertHasNoErrors();

        $this->assertSame(
            ['Source PDF', 'uploaded.pdf', 'Docs', 'existing.pdf', 'Clip'],
            DB::table('attachment_files')
                ->where('session_task_id', 51)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('title')
                ->all()
        );

        $this->assertSame(5, DB::table('attachment_files')->where('session_task_id', 51)->count());
        $this->assertSame(1, DB::table('attachment_files')->where('id', 101)->count());
    }

    public function test_show_session_task_can_save_with_selected_library_resource_only(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);
        $this->seedLibraryFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowSessionTask::class)
            ->call('open', 21)
            ->set('title', 'Library-only task')
            ->set('description', ' ')
            ->call('useLibraryResources', [601])
            ->call('save')
            ->assertHasNoErrors();

        $taskId = DB::table('session_tasks')
            ->where('title', 'Library-only task')
            ->value('id');

        $this->assertNotNull($taskId);
        $this->assertDatabaseHas('attachment_files', [
            'session_task_id' => $taskId,
            'title' => 'Source PDF',
            'type' => 'file',
            'path' => 'library-resources/source.pdf',
            'subject_id' => 9,
            'class_id' => 12,
            'teacher_subject_class_id' => 41,
        ]);

        DB::table('library_resources')->where('id', 601)->update(['status' => 'archived']);
        $this->assertDatabaseHas('attachment_files', [
            'session_task_id' => $taskId,
            'path' => 'library-resources/source.pdf',
        ]);
    }

    public function test_show_session_task_rejects_library_only_content_from_archived_folder(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);
        $this->seedLibraryFixture($teacher->id);

        DB::table('library_sections')->where('id', 501)->update(['status' => 'archived']);

        $this->actingAs($teacher);

        Livewire::test(ShowSessionTask::class)
            ->call('open', 21)
            ->set('title', 'Archived-folder Library task')
            ->set('description', ' ')
            ->call('useLibraryResources', [601])
            ->call('save')
            ->assertHasErrors(['content']);

        $this->assertDatabaseMissing('session_tasks', [
            'title' => 'Archived-folder Library task',
        ]);
    }

    public function test_show_daily_session_task_save_is_blocked_while_uploads_are_in_progress(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedDailySessionFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowDailySessionTask::class)
            ->call('open', 31)
            ->set('uploadsInProgress', true)
            ->call('save')
            ->assertHasErrors(['files']);

        $this->assertSame(1, DB::table('daily_session_tasks')->where('daily_session_id', 31)->count());
    }

    public function test_show_daily_session_task_save_works_without_task_type_column(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedDailySessionFixture($teacher->id);
        $this->dropDailyTaskTypeColumn();

        $this->actingAs($teacher);

        Livewire::test(ShowDailySessionTask::class)
            ->call('open', 31)
            ->set('title', 'Brand new daily task')
            ->set('description', 'Daily instructions')
            ->set('files', [
                UploadedFile::fake()->create('daily-new.pdf', 10, 'application/pdf'),
            ])
            ->call('save')
            ->assertHasNoErrors();

        $taskId = DB::table('daily_session_tasks')
            ->where('daily_session_id', 31)
            ->where('title', 'Brand new daily task')
            ->value('id');

        $this->assertNotNull($taskId);
        $this->assertDatabaseHas('daily_attachment_files', [
            'daily_session_task_id' => $taskId,
            'title' => 'daily-new.pdf',
            'type' => 'file',
        ]);
    }

    public function test_show_daily_session_task_edit_can_add_content_without_task_type_column(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedDailySessionFixture($teacher->id);

        DB::table('daily_attachment_files')->where('daily_session_task_id', 61)->delete();
        DB::table('daily_session_tasks')->where('id', 61)->update(['description' => null]);
        $this->dropDailyTaskTypeColumn();

        $this->actingAs($teacher);

        Livewire::test(ShowDailySessionTask::class)
            ->call('openEdit', 61)
            ->set('description', 'Now this old task has content')
            ->set('files', [
                UploadedFile::fake()->create('old-task-added.pdf', 10, 'application/pdf'),
            ])
            ->call('updateTask')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('daily_session_tasks', [
            'id' => 61,
            'description' => 'Now this old task has content',
        ]);
        $this->assertDatabaseHas('daily_attachment_files', [
            'daily_session_task_id' => 61,
            'title' => 'old-task-added.pdf',
            'type' => 'file',
        ]);
    }

    public function test_show_session_task_switching_type_keeps_pending_attachments(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowSessionTask::class)
            ->call('open', 21)
            ->set('finalFiles', [
                UploadedFile::fake()->create('queued.pdf', 10, 'application/pdf'),
            ])
            ->set('youtubes', [
                ['title' => 'Clip', 'url' => 'https://www.youtube.com/watch?v=abc123'],
            ])
            ->set('task_type_id', 3)
            ->assertSet('finalFiles', fn (array $files): bool => count($files) === 1)
            ->assertSet('youtubes', [
                ['title' => 'Clip', 'url' => 'https://www.youtube.com/watch?v=abc123'],
            ]);
    }

    public function test_show_daily_session_task_switching_type_keeps_pending_attachments(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedDailySessionFixture($teacher->id);

        $this->actingAs($teacher);

        Livewire::test(ShowDailySessionTask::class)
            ->call('open', 31)
            ->set('finalFiles', [
                UploadedFile::fake()->create('queued.pdf', 10, 'application/pdf'),
            ])
            ->set('links', [
                ['title' => 'Docs', 'url' => 'https://example.com'],
            ])
            ->set('link_title_input', 'Pending title')
            ->set('link_url_input', 'https://pending.example.com')
            ->set('task_type_id', 7)
            ->assertSet('finalFiles', fn (array $files): bool => count($files) === 1)
            ->assertSet('links', [
                ['title' => 'Docs', 'url' => 'https://example.com'],
            ])
            ->assertSet('link_title_input', 'Pending title')
            ->assertSet('link_url_input', 'https://pending.example.com');
    }

    public function test_teacher_can_delete_unpublished_session_task(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);
        $this->seedSessionMaterial(21, 'draft');
        Storage::disk('public')->put('attachments/existing.pdf', 'pdf');

        $this->actingAs($teacher);

        Livewire::test(TeacherSessionsBoard::class, ['teacherSubjectClassId' => 41])
            ->call('deleteDraftTask', 51)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('session_tasks', ['id' => 51]);
        $this->assertDatabaseMissing('attachment_files', ['id' => 101]);
        Storage::disk('public')->assertMissing('attachments/existing.pdf');
    }

    public function test_teacher_cannot_delete_published_session_task(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);
        $this->seedSessionMaterial(21, 'published');
        DB::table('session_tasks')->where('id', 51)->update(['status' => 'published']);

        $this->actingAs($teacher);

        Livewire::test(TeacherSessionsBoard::class, ['teacherSubjectClassId' => 41])
            ->call('deleteDraftTask', 51)
            ->assertHasErrors(['task']);

        $this->assertDatabaseHas('session_tasks', ['id' => 51]);
    }

    public function test_teacher_can_delete_unpublished_session_with_tasks(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);
        $this->seedSessionMaterial(21, 'draft');

        $this->actingAs($teacher);

        Livewire::test(TeacherSessionsBoard::class, ['teacherSubjectClassId' => 41])
            ->call('deleteDraftSession', 21)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('class_sessions', ['id' => 21]);
        $this->assertDatabaseMissing('session_tasks', ['id' => 51]);
        $this->assertDatabaseMissing('session_materials', ['session_id' => 21]);
    }

    public function test_publishing_past_dated_draft_requires_confirmation_and_moves_date_to_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-18 09:00:00', config('app.timezone')));

        try {
            $teacher = $this->createTeacher();

            $this->seedTaskTypes();
            $this->seedSessionFixture($teacher->id);
            $this->seedSessionMaterial(21, 'draft');
            DB::table('class_sessions')->where('id', 21)->update(['date' => '2026-05-10']);

            $this->actingAs($teacher);

            Livewire::test(TeacherSessionsBoard::class, ['teacherSubjectClassId' => 41])
                ->call('publishSession', 21)
                ->assertHasNoErrors();

            $this->assertDatabaseHas('session_materials', [
                'session_id' => 21,
                'status' => 'draft',
            ]);
            $this->assertSame('2026-05-10', (string) DB::table('class_sessions')->where('id', 21)->value('date'));

            Livewire::test(TeacherSessionsBoard::class, ['teacherSubjectClassId' => 41])
                ->call('publishSession', 21, true)
                ->assertHasNoErrors();

            $this->assertDatabaseHas('session_materials', [
                'session_id' => 21,
                'status' => 'published',
            ]);
            $this->assertSame('2026-05-18', (string) DB::table('class_sessions')->where('id', 21)->value('date'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_publishing_future_dated_draft_keeps_future_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-18 09:00:00', config('app.timezone')));

        try {
            $teacher = $this->createTeacher();

            $this->seedTaskTypes();
            $this->seedSessionFixture($teacher->id);
            $this->seedSessionMaterial(21, 'draft');
            DB::table('class_sessions')->where('id', 21)->update(['date' => '2026-05-20']);

            $this->actingAs($teacher);

            Livewire::test(TeacherSessionsBoard::class, ['teacherSubjectClassId' => 41])
                ->call('publishSession', 21)
                ->assertHasNoErrors();

            $this->assertDatabaseHas('session_materials', [
                'session_id' => 21,
                'status' => 'published',
            ]);
            $this->assertSame('2026-05-20', (string) DB::table('class_sessions')->where('id', 21)->value('date'));
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_teacher_sessions_order_by_session_date_then_id(): void
    {
        $teacher = $this->createTeacher();

        $this->seedTaskTypes();
        $this->seedSessionFixture($teacher->id);
        $this->seedSessionMaterial(21, 'published');
        DB::table('class_sessions')->insert([
            'id' => 22,
            'teacher_subject_classes_id' => 41,
            'class_id' => 12,
            'subject_id' => 9,
            'student_id' => null,
            'main_daily_session_template_id' => null,
            'title' => 'Newer Calendar Session',
            'date' => '2026-05-18',
        ]);
        $this->seedSessionMaterial(22, 'published');
        DB::table('class_sessions')->where('id', 21)->update(['date' => '2026-05-10']);

        $this->actingAs($teacher);

        $sessions = Livewire::test(TeacherSessionsBoard::class, ['teacherSubjectClassId' => 41])
            ->get('sessions');

        $this->assertSame([22, 21], array_column($sessions, 'id'));
    }

    public function test_add_daily_session_requires_owned_subject_and_main_session(): void
    {
        $teacher = $this->createTeacher();
        $otherTeacher = $this->createTeacher();

        DB::table('teacher_subject_classes')->insert([
            'id' => 90,
            'user_teacher_coteacher_id' => $teacher->id,
            'class_subject_id' => 22,
            'class_id' => 12,
            'subject_id' => 9,
            'status' => 'active',
        ]);

        DB::table('main_daily_session')->insert([
            'id' => 301,
            'subject_id' => 9,
            'title' => 'Owned Main Session',
        ]);

        $this->actingAs($otherTeacher);

        Livewire::test(AddDailySession::class, [
            'subjectId' => 9,
            'mainDailySessionId' => 301,
        ])->call('addDailySession');

        $this->assertSame(0, DB::table('daily_sessions')->where('main_daily_session_id', 301)->count());
    }

    public function test_add_daily_session_creates_record_for_owner(): void
    {
        $teacher = $this->createTeacher();

        DB::table('teacher_subject_classes')->insert([
            'id' => 91,
            'user_teacher_coteacher_id' => $teacher->id,
            'class_subject_id' => 22,
            'class_id' => 12,
            'subject_id' => 9,
            'status' => 'active',
        ]);

        DB::table('main_daily_session')->insert([
            'id' => 302,
            'subject_id' => 9,
            'title' => 'Owned Main Session',
        ]);

        $this->actingAs($teacher);

        Livewire::test(AddDailySession::class, [
            'subjectId' => 9,
            'mainDailySessionId' => 302,
        ])->call('addDailySession');

        $this->assertDatabaseHas('daily_sessions', [
            'main_daily_session_id' => 302,
            'subject_id' => 9,
            'title' => 'New Automated Task Set',
        ]);
    }

    private function createTeacher(): User
    {
        return User::factory()->create();
    }

    private function seedTaskTypes(): void
    {
        DB::table('task_types')->insert([
            [
                'id' => 2,
                'title' => 'Quiz',
                'table_name' => 'teacher_classes_quizzes_and_exams',
                'default_points' => 5,
                'max_points' => 10,
            ],
            [
                'id' => 3,
                'title' => 'Lesson',
                'table_name' => 'teacher_classes_lessons',
                'default_points' => 5,
                'max_points' => 10,
            ],
            [
                'id' => 4,
                'title' => 'Project',
                'table_name' => 'teacher_classes_projects',
                'default_points' => 5,
                'max_points' => 10,
            ],
            [
                'id' => 7,
                'title' => 'Assignment',
                'table_name' => 'teacher_classes_assignments',
                'default_points' => 5,
                'max_points' => 10,
            ],
        ]);
    }

    private function dropDailyTaskTypeColumn(): void
    {
        if (Schema::hasColumn('daily_session_tasks', 'task_type_id')) {
            Schema::table('daily_session_tasks', function (Blueprint $table): void {
                $table->dropColumn('task_type_id');
            });
        }
    }

    private function seedSessionFixture(int $teacherId): void
    {
        DB::table('class_subjects')->insert([
            'id' => 22,
            'class_id' => 12,
        ]);

        DB::table('students_subjects')->insert([
            'student_id' => 501,
            'class_subject_id' => 22,
            'status' => 'active',
        ]);

        DB::table('teacher_subject_classes')->insert([
            'id' => 41,
            'user_teacher_coteacher_id' => $teacherId,
            'class_subject_id' => 22,
            'class_id' => 12,
            'subject_id' => 9,
            'status' => 'active',
        ]);

        DB::table('class_sessions')->insert([
            'id' => 21,
            'teacher_subject_classes_id' => 41,
            'class_id' => 12,
            'subject_id' => 9,
            'student_id' => null,
            'main_daily_session_template_id' => null,
            'title' => 'Generated Session',
            'date' => now()->toDateString(),
        ]);

        DB::table('session_tasks')->insert([
            'id' => 51,
            'class_session_id' => 21,
            'task_type_id' => 7,
            'title' => 'Read this PDF',
            'description' => 'Original attachment',
            'default_points' => 5,
            'max_points' => 10,
            'status' => 'draft',
            'created_at' => now(),
        ]);

        DB::table('attachment_files')->insert([
            'id' => 101,
            'session_task_id' => 51,
            'title' => 'existing.pdf',
            'description' => 'Existing file',
            'type' => 'file',
            'path' => 'attachments/existing.pdf',
            'file_size' => 1234,
            'subject_id' => 9,
            'class_id' => 12,
            'teacher_subject_class_id' => 41,
        ]);
    }

    private function seedSessionMaterial(int $sessionId, string $status): void
    {
        DB::table('session_materials')->updateOrInsert(
            ['session_id' => $sessionId],
            [
                'teacher_subject_classes_id' => 41,
                'subject_id' => 9,
                'grade_id' => null,
                'teacher_id' => null,
                'unit_id' => null,
                'status' => $status,
                'assign_to_all' => 'all',
            ]
        );
    }

    private function seedLibraryFixture(int $teacherId, string $filePath = 'library-resources/source.pdf'): void
    {
        DB::table('library_sections')->insert([
            'id' => 501,
            'owner_user_id' => $teacherId,
            'subject_id' => 9,
            'parent_id' => null,
            'title' => 'Teacher Library',
            'status' => 'active',
            'created_by_user_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('library_resources')->insert([
            'id' => 601,
            'owner_user_id' => $teacherId,
            'subject_id' => 9,
            'library_section_id' => 501,
            'resource_type' => 'file',
            'title' => 'Source PDF',
            'description' => 'Library source',
            'status' => 'active',
            'storage_disk' => 'public',
            'file_path' => $filePath,
            'original_filename' => 'source.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 3,
            'created_by_user_id' => $teacherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedDailySessionFixture(int $teacherId): void
    {
        DB::table('teacher_subject_classes')->insert([
            'id' => 42,
            'user_teacher_coteacher_id' => $teacherId,
            'class_subject_id' => 23,
            'class_id' => 12,
            'subject_id' => 9,
            'status' => 'active',
        ]);

        DB::table('daily_sessions')->insert([
            'id' => 31,
            'subject_id' => 9,
            'main_daily_session_id' => 1,
            'title' => 'Automated Task Set',
        ]);

        DB::table('daily_session_tasks')->insert([
            'id' => 61,
            'daily_session_id' => 31,
            'task_type_id' => 7,
            'title' => 'Daily PDF',
            'description' => 'Original daily attachment',
            'default_points' => 5,
            'max_points' => 10,
            'status' => 'draft',
            'sort' => 1,
        ]);

        DB::table('daily_attachment_files')->insert([
            'id' => 201,
            'daily_session_task_id' => 61,
            'title' => 'existing-daily.pdf',
            'description' => 'Existing file',
            'type' => 'file',
            'path' => 'attachments/existing-daily.pdf',
            'file_size' => 1234,
            'subject_id' => 9,
        ]);
    }

    private function createTeacherAttachmentTables(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->unique()->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('task_types')) {
            Schema::create('task_types', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->string('table_name')->nullable();
                $table->integer('default_points')->default(0);
                $table->integer('max_points')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('teacher_subject_classes')) {
            Schema::create('teacher_subject_classes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_teacher_coteacher_id')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('class_subjects')) {
            Schema::create('class_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students_subjects')) {
            Schema::create('students_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->string('status')->nullable();
            });
        }

        if (! Schema::hasTable('class_sessions')) {
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
        }

        if (! Schema::hasTable('session_tasks')) {
            Schema::create('session_tasks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('class_session_id')->nullable();
                $table->unsignedBigInteger('task_type_id')->nullable();
                $table->unsignedBigInteger('session_material_id')->nullable();
                $table->unsignedBigInteger('created_by_teacher_id')->nullable();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->integer('default_points')->default(0);
                $table->integer('max_points')->default(0);
                $table->string('status')->nullable();
                $table->integer('sort')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('session_materials')) {
            Schema::create('session_materials', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('teacher_subject_classes_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('grade_id')->nullable();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->unsignedBigInteger('session_id')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('status')->nullable();
                $table->string('assign_to_all')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('attachment_files')) {
            Schema::create('attachment_files', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_task_id')->nullable();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('type')->nullable();
                $table->string('path')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('teacher_subject_class_id')->nullable();
            });
        }

        if (! Schema::hasTable('session_task_student')) {
            Schema::create('session_task_student', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_task_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->unsignedBigInteger('student_user_id')->nullable();
                $table->integer('student_points')->nullable();
                $table->string('status')->nullable();
                $table->string('flag')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->string('assign_to_all')->nullable();
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
                $table->unsignedSmallInteger('sort_order')->default(0);
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
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->unsignedBigInteger('created_by_user_id');
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('daily_sessions')) {
            Schema::create('daily_sessions', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('main_daily_session_id')->nullable();
            });
        }

        if (! Schema::hasTable('main_daily_session')) {
            Schema::create('main_daily_session', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
            });
        }

        if (! Schema::hasTable('daily_session_tasks')) {
            Schema::create('daily_session_tasks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('daily_session_id')->nullable();
                $table->unsignedBigInteger('task_type_id')->nullable();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->integer('default_points')->default(0);
                $table->integer('max_points')->default(0);
                $table->string('status')->nullable();
                $table->integer('sort')->nullable();
            });
        }

        if (! Schema::hasTable('daily_attachment_files')) {
            Schema::create('daily_attachment_files', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('daily_session_task_id')->nullable();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('type')->nullable();
                $table->string('path')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
            });
        }
    }
}
