<?php

namespace Tests\Feature\CoreLms;

use App\Livewire\Teacher\LibraryManager;
use App\Livewire\Teacher\LibraryPicker;
use App\Models\User;
use App\Models\VocabularySet;
use App\Services\Library\LibraryResourceQuery;
use App\Support\BookingSubjectProvisioning;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LibraryManagerTest extends TestCase
{
    use RefreshDatabase;

    private const LANGUAGE_SUBJECT_ID = BookingSubjectProvisioning::SUBJECT_LANGUAGE_AND_LITERATURE;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRequiredTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('teacher');
    }

    public function test_teacher_creates_section_and_link_resource_in_own_subject(): void
    {
        $teacher = $this->teacherWithSubject();

        Livewire::actingAs($teacher)
            ->test(LibraryManager::class)
            ->set('sectionTitle', 'Reading Pack')
            ->call('createSection')
            ->assertHasNoErrors();

        $sectionId = (int) DB::table('library_sections')->where('title', 'Reading Pack')->value('id');

        Livewire::actingAs($teacher)
            ->test(LibraryManager::class)
            ->set('resourceSectionId', $sectionId)
            ->set('resourceKind', 'link')
            ->set('resourceTitle', 'Close Reading Guide')
            ->set('externalUrl', 'https://example.com/guide')
            ->call('createResource')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('library_sections', [
            'title' => 'Reading Pack',
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('library_resources', [
            'title' => 'Close Reading Guide',
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'library_section_id' => $sectionId,
            'resource_type' => 'link',
            'external_url' => 'https://example.com/guide',
        ]);
    }

    public function test_quick_add_saves_links_inside_current_folder(): void
    {
        $teacher = $this->teacherWithSubject();
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Current Folder',
            'created_by_user_id' => $teacher->id,
        ]);
        $otherSectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Other Folder',
            'created_by_user_id' => $teacher->id,
        ]);

        Livewire::actingAs($teacher)
            ->test(LibraryManager::class, ['initialSectionId' => $sectionId, 'quickAdd' => true])
            ->set('quickLinkTitle', 'Reading Site')
            ->set('quickLinkUrl', 'https://example.com/reading')
            ->call('addQuickLink')
            ->set('quickYoutubeTitle', 'Video Guide')
            ->set('quickYoutubeUrl', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->call('addQuickYoutube')
            ->call('saveQuickAdd')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('library_resources', [
            'title' => 'Reading Site',
            'library_section_id' => $sectionId,
            'resource_type' => 'link',
            'external_url' => 'https://example.com/reading',
        ]);
        $this->assertDatabaseHas('library_resources', [
            'title' => 'Video Guide',
            'library_section_id' => $sectionId,
            'resource_type' => 'link',
            'external_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
        $this->assertSame(0, DB::table('library_resources')->where('library_section_id', $otherSectionId)->count());
    }

    public function test_teacher_uploads_file_resource_with_library_upload_limits(): void
    {
        Storage::fake('public');
        $teacher = $this->teacherWithSubject();
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Files',
            'created_by_user_id' => $teacher->id,
        ]);

        Livewire::actingAs($teacher)
            ->test(LibraryManager::class)
            ->set('resourceSectionId', $sectionId)
            ->set('resourceKind', 'file')
            ->set('resourceTitle', 'Sample PDF')
            ->set('resourceFiles', [UploadedFile::fake()->create('sample.pdf', 10)])
            ->call('createResource')
            ->assertHasNoErrors();

        $path = DB::table('library_resources')->where('title', 'Sample PDF')->value('file_path');

        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseHas('library_resources', [
            'title' => 'Sample PDF',
            'resource_type' => 'file',
            'storage_disk' => 'public',
            'original_filename' => 'sample.pdf',
        ]);
    }

    public function test_teacher_uploads_multiple_file_resources_using_filenames_as_titles(): void
    {
        Storage::fake('public');
        $teacher = $this->teacherWithSubject();
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Files',
            'created_by_user_id' => $teacher->id,
        ]);

        Livewire::actingAs($teacher)
            ->test(LibraryManager::class)
            ->set('resourceSectionId', $sectionId)
            ->set('resourceKind', 'file')
            ->set('resourceTitle', '')
            ->set('resourceFiles', [
                UploadedFile::fake()->create('chapter-one.pdf', 10),
                UploadedFile::fake()->create('lesson-video.mp4', 10),
            ])
            ->call('createResource')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('library_resources', [
            'title' => 'Chapter One',
            'resource_type' => 'file',
            'original_filename' => 'chapter-one.pdf',
        ]);
        $this->assertDatabaseHas('library_resources', [
            'title' => 'Lesson Video',
            'resource_type' => 'file',
            'original_filename' => 'lesson-video.mp4',
        ]);
        $this->assertSame(2, DB::table('library_resources')->where('library_section_id', $sectionId)->count());
    }

    public function test_teacher_upload_rejects_unsupported_file_type_server_side(): void
    {
        Storage::fake('public');
        $teacher = $this->teacherWithSubject();
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Files',
            'created_by_user_id' => $teacher->id,
        ]);

        Livewire::actingAs($teacher)
            ->test(LibraryManager::class)
            ->set('resourceSectionId', $sectionId)
            ->set('resourceKind', 'file')
            ->set('resourceFiles', [UploadedFile::fake()->create('wrong.zip', 10)])
            ->call('createResource')
            ->assertHasErrors(['file']);

        $this->assertSame(0, DB::table('library_resources')->where('library_section_id', $sectionId)->count());
    }

    public function test_uploaded_file_resources_keep_selected_order(): void
    {
        Storage::fake('public');
        $teacher = $this->teacherWithSubject();
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Files',
            'created_by_user_id' => $teacher->id,
        ]);
        DB::table('library_resources')->insert([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'library_section_id' => $sectionId,
            'resource_type' => 'link',
            'title' => 'Existing',
            'external_url' => 'https://example.com/existing',
            'sort_order' => 10,
            'created_by_user_id' => $teacher->id,
        ]);

        Livewire::actingAs($teacher)
            ->test(LibraryManager::class)
            ->set('resourceSectionId', $sectionId)
            ->set('resourceKind', 'file')
            ->set('resourceFiles', [
                UploadedFile::fake()->create('unit-01.mp4', 10),
                UploadedFile::fake()->create('unit-02.mp4', 10),
            ])
            ->call('createResource')
            ->assertHasNoErrors();

        $orderedTitles = DB::table('library_resources')
            ->where('library_section_id', $sectionId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('title')
            ->all();

        $this->assertSame(['Existing', 'Unit 01', 'Unit 02'], $orderedTitles);
        $this->assertSame(
            [10, 11, 12],
            DB::table('library_resources')
                ->where('library_section_id', $sectionId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('sort_order')
                ->all()
        );
    }

    public function test_quick_add_resources_keep_batch_order(): void
    {
        Storage::fake('public');
        $teacher = $this->teacherWithSubject();
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Current Folder',
            'created_by_user_id' => $teacher->id,
        ]);

        Livewire::actingAs($teacher)
            ->test(LibraryManager::class, ['initialSectionId' => $sectionId, 'quickAdd' => true])
            ->set('resourceFiles', [
                UploadedFile::fake()->create('first.pdf', 10),
                UploadedFile::fake()->create('second.pdf', 10),
            ])
            ->set('quickLinkTitle', 'Third Link')
            ->set('quickLinkUrl', 'https://example.com/third')
            ->call('addQuickLink')
            ->set('quickYoutubeTitle', 'Fourth Video')
            ->set('quickYoutubeUrl', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ')
            ->call('addQuickYoutube')
            ->call('saveQuickAdd')
            ->assertHasNoErrors();

        $this->assertSame(
            ['First', 'Second', 'Third Link', 'Fourth Video'],
            DB::table('library_resources')
                ->where('library_section_id', $sectionId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('title')
                ->all()
        );
    }

    public function test_teacher_can_remove_ready_uploaded_file_before_saving(): void
    {
        Storage::fake('public');
        $teacher = $this->teacherWithSubject();
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Files',
            'created_by_user_id' => $teacher->id,
        ]);

        Livewire::actingAs($teacher)
            ->test(LibraryManager::class)
            ->set('resourceSectionId', $sectionId)
            ->set('resourceKind', 'file')
            ->set('resourceFiles', [
                UploadedFile::fake()->create('keep.pdf', 10),
                UploadedFile::fake()->create('remove.pdf', 10),
            ])
            ->call('removeResourceFileAt', 1)
            ->call('createResource')
            ->assertHasNoErrors();

        $this->assertSame(
            ['Keep'],
            DB::table('library_resources')
                ->where('library_section_id', $sectionId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('title')
                ->all()
        );
    }

    public function test_archived_folder_resources_are_hidden_from_active_picker_queries(): void
    {
        $teacher = $this->teacherWithSubject();
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Archived Folder',
            'created_by_user_id' => $teacher->id,
            'status' => 'archived',
        ]);

        DB::table('library_resources')->insert([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'library_section_id' => $sectionId,
            'resource_type' => 'link',
            'title' => 'Hidden Link',
            'status' => 'active',
            'external_url' => 'https://example.com/hidden',
            'created_by_user_id' => $teacher->id,
        ]);

        $resources = app(LibraryResourceQuery::class)
            ->resources($teacher, self::LANGUAGE_SUBJECT_ID, null, 'Hidden Link')
            ->get();

        $this->assertCount(0, $resources);
    }

    public function test_library_picker_shows_general_folder_resources_only_after_entering_folder(): void
    {
        $teacher = $this->teacherWithSubject();
        $folderId = DB::table('general_library_folders')->insertGetId([
            'title' => 'Root Folder',
            'created_by_user_id' => $teacher->id,
        ]);
        DB::table('general_library_resources')->insert([
            'general_library_folder_id' => $folderId,
            'resource_type' => 'file',
            'title' => 'Inside PDF',
            'storage_disk' => 'public',
            'file_path' => 'general-library-resources/inside.pdf',
            'original_filename' => 'inside.pdf',
            'status' => 'active',
            'created_by_user_id' => $teacher->id,
        ]);

        Livewire::actingAs($teacher)
            ->test(LibraryPicker::class)
            ->call('openPicker', self::LANGUAGE_SUBJECT_ID, [])
            ->assertSee('Root Folder')
            ->assertDontSee('Inside PDF')
            ->call('enterGeneralFolder', $folderId)
            ->assertSee('Inside PDF');
    }

    public function test_library_picker_hides_legacy_library_sources_for_launch(): void
    {
        $teacher = $this->teacherWithSubject();

        Livewire::actingAs($teacher)
            ->test(LibraryPicker::class)
            ->call('openPicker', self::LANGUAGE_SUBJECT_ID, [])
            ->assertDontSee('Legacy Library Sources')
            ->assertDontSee('Level Up')
            ->assertDontSee('Peer Coach');
    }

    public function test_library_picker_hides_week14_vocabulary_by_default_even_with_sets(): void
    {
        $this->createVocabularyTables();

        $teacher = $this->teacherWithSubject();
        $otherTeacher = User::factory()->create();

        $systemRootId = DB::table('vocabulary_sets')->insertGetId([
            'title' => 'Cambridge',
            'node_type' => VocabularySet::NODE_FOLDER,
            'set_type' => VocabularySet::TYPE_SYSTEM,
            'source_kind' => VocabularySet::SOURCE_LEGACY_CAMBRIDGE,
            'source_key' => 'cambridge:root',
            'visibility' => VocabularySet::VISIBILITY_SYSTEM,
            'sort_order' => 1,
        ]);
        DB::table('vocabulary_sets')->insert([
            'parent_id' => $systemRootId,
            'title' => 'Starter Lesson',
            'node_type' => VocabularySet::NODE_PLAYABLE,
            'set_type' => VocabularySet::TYPE_SYSTEM,
            'source_kind' => VocabularySet::SOURCE_LEGACY_CAMBRIDGE,
            'source_key' => 'cambridge:starter',
            'visibility' => VocabularySet::VISIBILITY_SYSTEM,
            'sort_order' => 1,
        ]);
        $teacherRootId = DB::table('vocabulary_sets')->insertGetId([
            'title' => 'Cambridge copy',
            'node_type' => VocabularySet::NODE_FOLDER,
            'set_type' => VocabularySet::TYPE_TEACHER,
            'source_kind' => VocabularySet::SOURCE_CUSTOM,
            'owner_user_id' => $teacher->id,
            'visibility' => VocabularySet::VISIBILITY_PRIVATE,
            'sort_order' => 2,
        ]);
        DB::table('vocabulary_sets')->insert([
            'parent_id' => $teacherRootId,
            'title' => 'Edited Lesson',
            'node_type' => VocabularySet::NODE_PLAYABLE,
            'set_type' => VocabularySet::TYPE_TEACHER,
            'source_kind' => VocabularySet::SOURCE_CUSTOM,
            'owner_user_id' => $teacher->id,
            'visibility' => VocabularySet::VISIBILITY_PRIVATE,
            'sort_order' => 1,
        ]);
        DB::table('vocabulary_sets')->insert([
            'title' => 'Other Teacher Copy',
            'node_type' => VocabularySet::NODE_FOLDER,
            'set_type' => VocabularySet::TYPE_TEACHER,
            'source_kind' => VocabularySet::SOURCE_CUSTOM,
            'owner_user_id' => $otherTeacher->id,
            'visibility' => VocabularySet::VISIBILITY_PRIVATE,
            'sort_order' => 3,
        ]);

        Livewire::actingAs($teacher)
            ->test(LibraryPicker::class)
            ->call('openPicker', self::LANGUAGE_SUBJECT_ID, [])
            ->assertDontSee('Vocabulary')
            ->assertDontSee('Cambridge')
            ->assertDontSee('Cambridge copy')
            ->assertDontSee('Other Teacher Copy')
            ->assertDontSee('Edited Lesson');
    }

    public function test_library_picker_does_not_sync_vocabulary_proxies_while_rendering(): void
    {
        $this->createVocabularyTables();

        $teacher = $this->teacherWithSubject();

        $this->assertSame(0, DB::table('vocabulary_sets')->count());

        Livewire::actingAs($teacher)
            ->test(LibraryPicker::class)
            ->call('openPicker', self::LANGUAGE_SUBJECT_ID, [])
            ->assertDontSee('Vocabulary');

        $this->assertSame(0, DB::table('vocabulary_sets')->count());
    }

    public function test_library_picker_legacy_notice_note_shows_child_items_not_parent_folder(): void
    {
        $teacher = $this->teacherWithSubject();

        Livewire::actingAs($teacher)
            ->test(LibraryPicker::class)
            ->call('openPicker', self::LANGUAGE_SUBJECT_ID, [])
            ->call('enterLegacySources')
            ->call('enterLegacyCollectionType', 'notice_note')
            ->assertSee('Notice & Note')
            ->call('enterLegacyCollection', 'notice_note:root')
            ->assertSee('Big Questions')
            ->assertDontSee('Notice & Note');
    }

    public function test_library_picker_shows_selected_resources_from_other_folders(): void
    {
        $teacher = $this->teacherWithSubject();
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Teacher Folder',
            'created_by_user_id' => $teacher->id,
        ]);
        $resourceId = DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'library_section_id' => $sectionId,
            'resource_type' => 'file',
            'title' => 'Teacher PDF',
            'storage_disk' => 'public',
            'file_path' => 'library-resources/teacher.pdf',
            'created_by_user_id' => $teacher->id,
        ]);

        $this->assertSame(1, DB::table('library_resources')
            ->where('id', $resourceId)
            ->where('owner_user_id', $teacher->id)
            ->where('subject_id', self::LANGUAGE_SUBJECT_ID)
            ->count());

        Livewire::actingAs($teacher)
            ->test(LibraryPicker::class)
            ->call('openPicker', self::LANGUAGE_SUBJECT_ID, [(string) $resourceId, 'series__notice_note__22'])
            ->assertSee('Selected resources')
            ->assertSee('Teacher PDF')
            ->assertSee('Big Questions')
            ->call('removeSelection', 'series__notice_note__22')
            ->assertSee('Selected resources')
            ->assertSee('Teacher PDF')
            ->call('removeSelection', (string) $resourceId)
            ->assertDontSee('Selected resources');
    }

    public function test_teacher_edits_link_resource_without_changing_existing_attachment_snapshot(): void
    {
        $teacher = $this->teacherWithSubject();
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Links',
            'created_by_user_id' => $teacher->id,
        ]);
        $resourceId = DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'library_section_id' => $sectionId,
            'resource_type' => 'link',
            'title' => 'Original Link',
            'external_url' => 'https://example.com/original',
            'created_by_user_id' => $teacher->id,
        ]);
        $attachmentId = DB::table('attachment_files')->insertGetId([
            'title' => 'Original Link',
            'type' => 'link',
            'path' => 'https://example.com/original',
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
        ]);

        Livewire::actingAs($teacher)
            ->test(LibraryManager::class)
            ->call('editResource', $resourceId)
            ->set('editingResourceTitle', 'Updated Link')
            ->set('editingExternalUrl', 'https://example.com/updated')
            ->call('saveResource')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('library_resources', [
            'id' => $resourceId,
            'title' => 'Updated Link',
            'external_url' => 'https://example.com/updated',
        ]);
        $this->assertDatabaseHas('attachment_files', [
            'id' => $attachmentId,
            'title' => 'Original Link',
            'path' => 'https://example.com/original',
        ]);
    }

    public function test_replacing_file_resource_keeps_old_file_when_attachment_snapshot_uses_it(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('library-resources/original.pdf', 'original');

        $teacher = $this->teacherWithSubject();
        $sectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Files',
            'created_by_user_id' => $teacher->id,
        ]);
        $resourceId = DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'library_section_id' => $sectionId,
            'resource_type' => 'file',
            'title' => 'Original File',
            'storage_disk' => 'public',
            'file_path' => 'library-resources/original.pdf',
            'original_filename' => 'original.pdf',
            'created_by_user_id' => $teacher->id,
        ]);
        DB::table('attachment_files')->insert([
            'title' => 'Original File',
            'type' => 'file',
            'path' => 'library-resources/original.pdf',
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
        ]);

        Livewire::actingAs($teacher)
            ->test(LibraryManager::class)
            ->call('editResource', $resourceId)
            ->set('editingResourceTitle', 'Updated File')
            ->set('editingResourceFile', UploadedFile::fake()->create('replacement.pdf', 10))
            ->call('saveResource')
            ->assertHasNoErrors();

        $newPath = (string) DB::table('library_resources')->where('id', $resourceId)->value('file_path');

        $this->assertNotSame('library-resources/original.pdf', $newPath);
        Storage::disk('public')->assertExists('library-resources/original.pdf');
        Storage::disk('public')->assertExists($newPath);
        $this->assertDatabaseHas('attachment_files', [
            'type' => 'file',
            'path' => 'library-resources/original.pdf',
        ]);
    }

    public function test_teacher_can_delete_unused_resource_and_empty_folder_only(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('library-resources/unused.pdf', 'unused');
        Storage::disk('public')->put('library-resources/used.pdf', 'used');

        $teacher = $this->teacherWithSubject();
        $emptySectionId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Empty Folder',
            'created_by_user_id' => $teacher->id,
        ]);
        $folderWithResourceId = DB::table('library_sections')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Folder With Resource',
            'created_by_user_id' => $teacher->id,
        ]);
        $unusedResourceId = DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'library_section_id' => $folderWithResourceId,
            'resource_type' => 'file',
            'title' => 'Unused File',
            'storage_disk' => 'public',
            'file_path' => 'library-resources/unused.pdf',
            'created_by_user_id' => $teacher->id,
        ]);
        $usedResourceId = DB::table('library_resources')->insertGetId([
            'owner_user_id' => $teacher->id,
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'library_section_id' => $folderWithResourceId,
            'resource_type' => 'file',
            'title' => 'Used File',
            'storage_disk' => 'public',
            'file_path' => 'library-resources/used.pdf',
            'created_by_user_id' => $teacher->id,
        ]);
        DB::table('attachment_files')->insert([
            'title' => 'Used File',
            'type' => 'file',
            'path' => 'library-resources/used.pdf',
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
        ]);

        Livewire::actingAs($teacher)
            ->test(LibraryManager::class)
            ->call('deleteSection', $folderWithResourceId)
            ->assertHasErrors(['section_delete_'.$folderWithResourceId])
            ->call('deleteSection', $emptySectionId)
            ->assertHasNoErrors(['section_delete_'.$emptySectionId])
            ->call('deleteResource', $usedResourceId)
            ->assertHasErrors(['resource_delete_'.$usedResourceId])
            ->call('deleteResource', $unusedResourceId)
            ->assertHasNoErrors(['resource_delete_'.$unusedResourceId]);

        $this->assertDatabaseMissing('library_sections', ['id' => $emptySectionId]);
        $this->assertDatabaseMissing('library_resources', ['id' => $unusedResourceId]);
        $this->assertDatabaseHas('library_resources', ['id' => $usedResourceId]);
        Storage::disk('public')->assertMissing('library-resources/unused.pdf');
        Storage::disk('public')->assertExists('library-resources/used.pdf');
    }

    private function teacherWithSubject(): User
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        config(['toquran.legacy_library_owner_user_ids' => [$teacher->id]]);

        DB::table('subjects')->insert([
            'id' => self::LANGUAGE_SUBJECT_ID,
            'title' => 'Language and Literature',
        ]);

        DB::table('teacher_subject_classes')->insert([
            'user_teacher_coteacher_id' => $teacher->id,
            'teacher_name' => 'Teacher',
            'class_subject_id' => 30,
            'grade_id' => 1,
            'grade_name' => 'Grade',
            'class_id' => 1,
            'class_name' => 'Class',
            'subject_id' => self::LANGUAGE_SUBJECT_ID,
            'subject_name' => 'Language and Literature',
            'status' => 'current',
        ]);
        DB::table('level_up')->insertOrIgnore([
            'id' => 1,
            'title' => 'Level 1',
            'slug' => 'level-1',
            'iframe_link' => null,
            'sort' => 1,
        ]);
        DB::table('peer_coach')->insertOrIgnore([
            [
                'id' => 11,
                'title' => 'Discussion Pack',
                'slug' => 'discussion-pack',
                'parent_id' => 0,
                'sort' => 1,
            ],
            [
                'id' => 12,
                'title' => 'Partner Prompt',
                'slug' => 'discussion-pack/partner-prompt',
                'parent_id' => 11,
                'sort' => 1,
            ],
        ]);
        DB::table('notice_note')->insertOrIgnore([
            [
                'id' => 21,
                'title' => 'Notice & Note',
                'slug' => 'notice-note',
                'parent_id' => 0,
                'sort' => 1,
            ],
            [
                'id' => 22,
                'title' => 'Big Questions',
                'slug' => 'big-questions',
                'parent_id' => 21,
                'sort' => 1,
            ],
        ]);

        return $teacher;
    }

    private function createRequiredTables(): void
    {
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
                $table->string('teacher_name')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('grade_id')->nullable();
                $table->string('grade_name')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->string('class_name')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('subject_name')->nullable();
                $table->string('status')->default('current');
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
                $table->text('description')->nullable();
                $table->string('type')->nullable();
                $table->string('path', 2048)->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('teacher_subject_class_id')->nullable();
                $table->unsignedBigInteger('session_task_id')->nullable();
            });
        }

        if (! Schema::hasTable('level_up')) {
            Schema::create('level_up', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->string('slug')->nullable();
                $table->string('iframe_link')->nullable();
                $table->integer('sort')->default(0);
            });
        }

        if (! Schema::hasTable('peer_coach')) {
            Schema::create('peer_coach', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->string('slug')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('sort')->default(0);
            });
        }

        if (! Schema::hasTable('notice_note')) {
            Schema::create('notice_note', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->string('slug')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->integer('sort')->default(0);
            });
        }
    }

    private function createVocabularyTables(): void
    {
        if (! Schema::hasTable('vocabulary_sets')) {
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
        }

        if (! Schema::hasTable('vocabulary_set_words')) {
            Schema::create('vocabulary_set_words', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('vocabulary_set_id');
                $table->unsignedBigInteger('word_id');
                $table->unsignedInteger('position')->default(0);
                $table->unsignedBigInteger('added_by_user_id')->nullable();
                $table->timestamps();
            });
        }
    }
}
