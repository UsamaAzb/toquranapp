<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\Library\LibraryFileRetentionService;
use App\Services\Library\LibraryResourceAccessService;
use App\Services\Library\LibraryResourceValidator;
use App\Services\Library\LibrarySectionValidator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LibraryResourceFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate('student');

        $this->createLibraryTables();
    }

    public function test_section_validator_rejects_self_parent(): void
    {
        $this->insertSection(id: 1, ownerUserId: 10, subjectId: 20, title: 'Root');

        $this->expectException(ValidationException::class);

        app(LibrarySectionValidator::class)->validateParentForWrite(
            ownerUserId: 10,
            subjectId: 20,
            parentId: 1,
            movingSectionId: 1
        );
    }

    public function test_section_validator_rejects_cross_owner_parent(): void
    {
        $this->insertSection(id: 1, ownerUserId: 99, subjectId: 20, title: 'Other Owner');

        $this->expectException(ValidationException::class);

        app(LibrarySectionValidator::class)->validateParentForWrite(
            ownerUserId: 10,
            subjectId: 20,
            parentId: 1
        );
    }

    public function test_section_validator_rejects_duplicate_active_sibling_title(): void
    {
        $this->insertSection(id: 1, ownerUserId: 10, subjectId: 20, title: 'Practice');

        $this->expectException(ValidationException::class);

        app(LibrarySectionValidator::class)->validateUniqueActiveSiblingTitle(
            ownerUserId: 10,
            subjectId: 20,
            parentId: null,
            title: 'Practice'
        );
    }

    public function test_resource_validator_rejects_unsupported_file_type_and_large_file(): void
    {
        $validator = app(LibraryResourceValidator::class);

        try {
            $validator->validateFileUpload(UploadedFile::fake()->create('unsafe.exe', 10));
            $this->fail('Unsupported file type should be rejected.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $validator->validateFileUpload(UploadedFile::fake()->create('large-but-allowed.pdf', LibraryResourceValidator::MAX_UPLOAD_KB));

        $this->expectException(ValidationException::class);

        $validator->validateFileUpload(UploadedFile::fake()->create('large.pdf', LibraryResourceValidator::MAX_UPLOAD_KB + 1));
    }

    public function test_resource_validator_rejects_non_http_link_schemes(): void
    {
        $validator = app(LibraryResourceValidator::class);

        $validator->validateLinkUrl('https://example.com/resource');
        $validator->validateLinkUrl('http://example.com/resource');

        foreach (['javascript:alert(1)', 'ftp://example.com/file.pdf', 'file:///C:/secret.pdf'] as $url) {
            try {
                $validator->validateLinkUrl($url);
                $this->fail("Unsafe Library URL scheme should be rejected: {$url}");
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('external_url', $exception->errors());
            }
        }
    }

    public function test_file_retention_blocks_delete_when_path_is_referenced_by_library_or_task_snapshot(): void
    {
        DB::table('library_sections')->insert([
            'id' => 1,
            'owner_user_id' => 10,
            'subject_id' => 20,
            'title' => 'Root',
            'created_by_user_id' => 10,
        ]);

        DB::table('library_resources')->insert([
            'id' => 1,
            'owner_user_id' => 10,
            'subject_id' => 20,
            'library_section_id' => 1,
            'resource_type' => 'file',
            'title' => 'PDF',
            'storage_disk' => 'public',
            'file_path' => 'library/example.pdf',
            'original_filename' => 'example.pdf',
            'created_by_user_id' => 10,
        ]);

        DB::table('attachment_files')->insert([
            'id' => 1,
            'type' => 'file',
            'path' => 'library/example.pdf',
        ]);

        $service = app(LibraryFileRetentionService::class);

        $this->assertTrue($service->isReferenced('library/example.pdf'));
        $this->assertFalse($service->canDeletePath('library/example.pdf'));
        $this->assertFalse($service->canDeletePath('library/example.pdf', exceptLibraryResourceId: 1));
    }

    public function test_file_retention_keeps_archived_library_file_when_task_snapshot_references_it(): void
    {
        DB::table('library_sections')->insert([
            'id' => 1,
            'owner_user_id' => 10,
            'subject_id' => 20,
            'title' => 'Root',
            'created_by_user_id' => 10,
        ]);

        DB::table('library_resources')->insert([
            'id' => 1,
            'owner_user_id' => 10,
            'subject_id' => 20,
            'library_section_id' => 1,
            'resource_type' => 'file',
            'title' => 'Archived PDF',
            'status' => 'archived',
            'storage_disk' => 'public',
            'file_path' => 'library/archived.pdf',
            'original_filename' => 'archived.pdf',
            'created_by_user_id' => 10,
        ]);

        DB::table('attachment_files')->insert([
            'id' => 1,
            'type' => 'file',
            'path' => 'library/archived.pdf',
        ]);

        $service = app(LibraryFileRetentionService::class);

        $this->assertTrue($service->isReferenced('library/archived.pdf'));
        $this->assertFalse($service->canDeletePath('library/archived.pdf'));
        $this->assertFalse($service->canDeletePath('library/archived.pdf', exceptLibraryResourceId: 1));
    }

    public function test_learner_attachment_access_requires_specific_task_assignment(): void
    {
        $studentUser = User::factory()->create();
        $studentUser->assignRole('student');

        $otherStudentUser = User::factory()->create();
        $otherStudentUser->assignRole('student');

        DB::table('students')->insert([
            ['id' => 100, 'user_id' => $studentUser->id, 'parent_id' => null],
            ['id' => 101, 'user_id' => $otherStudentUser->id, 'parent_id' => null],
        ]);

        DB::table('class_sessions')->insert([
            'id' => 10,
            'student_id' => null,
            'title' => 'Visible session',
        ]);

        DB::table('session_tasks')->insert([
            'id' => 20,
            'class_session_id' => 10,
            'assign_to_all' => 'custom',
            'title' => 'Assigned resource task',
        ]);

        DB::table('attachment_files')->insert([
            'id' => 30,
            'session_task_id' => 20,
            'type' => 'file',
            'path' => 'library/example.pdf',
        ]);

        $service = app(LibraryResourceAccessService::class);
        $attachment = \App\Models\AttachmentFile::query()->findOrFail(30);

        $this->assertFalse($service->canLearnerAccessAttachment($studentUser, 100, 10, $attachment));
        $this->assertFalse($service->canLearnerAccessAttachment($otherStudentUser, 101, 10, $attachment));

        DB::table('session_task_student')->insert([
            'session_task_id' => 20,
            'student_id' => 100,
            'status' => 'assigned',
            'assign_to_all' => 'custom',
        ]);

        $this->assertTrue($service->canLearnerAccessAttachment(
            $studentUser,
            100,
            10,
            \App\Models\AttachmentFile::query()->findOrFail(30)
        ));
        $this->assertFalse($service->canLearnerAccessAttachment(
            $otherStudentUser,
            101,
            10,
            \App\Models\AttachmentFile::query()->findOrFail(30)
        ));

        DB::table('session_tasks')->where('id', 20)->update(['assign_to_all' => 'all']);

        $this->assertTrue($service->canLearnerAccessAttachment(
            $otherStudentUser,
            101,
            10,
            \App\Models\AttachmentFile::query()->findOrFail(30)
        ));
    }

    private function insertSection(int $id, int $ownerUserId, int $subjectId, string $title, ?int $parentId = null): void
    {
        DB::table('library_sections')->insert([
            'id' => $id,
            'owner_user_id' => $ownerUserId,
            'subject_id' => $subjectId,
            'parent_id' => $parentId,
            'title' => $title,
            'created_by_user_id' => $ownerUserId,
        ]);
    }

    private function createLibraryTables(): void
    {
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

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
        });

        Schema::create('class_sessions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('title')->nullable();
        });

        Schema::create('session_tasks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('class_session_id')->nullable();
            $table->string('assign_to_all')->nullable();
            $table->string('title')->nullable();
        });

        Schema::create('session_task_student', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('session_task_id');
            $table->unsignedBigInteger('student_id');
            $table->string('status')->nullable();
            $table->string('assign_to_all')->nullable();
        });
    }
}
