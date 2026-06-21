<?php

namespace Tests\Unit;

use App\Models\AttachmentFile;
use App\Services\Library\TaskAttachmentPresenter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskAttachmentPresenterTest extends TestCase
{
    public function test_it_presents_protected_file_with_student_session_routes(): void
    {
        config(['filesystems.disks.public.url' => 'https://app.toquran.org/storage']);

        Storage::persistentFake('public');
        Storage::disk('public')->deleteDirectory('test-task-attachment-presenter');
        Storage::disk('public')->put('test-task-attachment-presenter/file.pdf', '%PDF-1.4');

        $item = app(TaskAttachmentPresenter::class)->forLearner(
            $this->attachment([
                'id' => 90,
                'type' => 'file',
                'path' => 'test-task-attachment-presenter/file.pdf',
                'title' => 'Protected PDF',
            ]),
            60,
            12,
        );

        $this->assertSame(TaskAttachmentPresenter::MODE_PROTECTED_FILE, $item['mode']);
        $this->assertSame('Protected PDF', $item['title']);
        $this->assertSame(route('student.sessions.attachment.file', [
            'session' => 60,
            'attachment' => 90,
            'student_id' => 12,
        ]), $item['content_url']);
        $this->assertStringContainsString('download=1', $item['download_url']);
        $this->assertSame('https://app.toquran.org/storage/test-task-attachment-presenter/file.pdf', $item['public_url']);
        $this->assertSame($item['public_url'], $item['open_url']);
        $this->assertSame('google', $item['viewer_provider']);
        $this->assertStringStartsWith('https://docs.google.com/gview?embedded=true&url=', (string) $item['viewer_url']);

        parse_str((string) parse_url((string) $item['viewer_url'], PHP_URL_QUERY), $query);

        $this->assertSame($item['public_url'], $query['url'] ?? null);
    }

    public function test_it_percent_encodes_public_file_urls_by_path_segment(): void
    {
        config(['filesystems.disks.public.url' => 'https://app.toquran.org/storage']);

        Storage::persistentFake('public');
        Storage::disk('public')->deleteDirectory('test-task-attachment-presenter');
        Storage::disk('public')->put('test-task-attachment-presenter/Arabic File اسم.pdf', '%PDF-1.4');

        $item = app(TaskAttachmentPresenter::class)->forLearner(
            $this->attachment([
                'id' => 98,
                'type' => 'file',
                'path' => 'test-task-attachment-presenter/Arabic File اسم.pdf',
                'title' => 'Arabic File اسم.pdf',
            ]),
            60,
            12,
        );

        $this->assertSame('https://app.toquran.org/storage/test-task-attachment-presenter/Arabic%20File%20%D8%A7%D8%B3%D9%85.pdf', $item['public_url']);

        parse_str((string) parse_url((string) $item['viewer_url'], PHP_URL_QUERY), $query);

        $this->assertSame($item['public_url'], $query['url'] ?? null);
    }

    public function test_it_can_roll_pdf_preview_back_to_native_provider(): void
    {
        config([
            'document-viewer.pdf_provider' => 'native',
            'filesystems.disks.public.url' => 'https://app.toquran.org/storage',
        ]);

        Storage::persistentFake('public');
        Storage::disk('public')->deleteDirectory('test-task-attachment-presenter');
        Storage::disk('public')->put('test-task-attachment-presenter/file.pdf', '%PDF-1.4');

        $item = app(TaskAttachmentPresenter::class)->forLearner(
            $this->attachment([
                'id' => 99,
                'type' => 'file',
                'path' => 'test-task-attachment-presenter/file.pdf',
                'title' => 'Protected PDF',
            ]),
            60,
            12,
        );

        $this->assertSame('native', $item['viewer_provider']);
        $this->assertSame($item['content_url'], $item['viewer_url']);
        $this->assertSame($item['content_url'], $item['open_url']);
        $this->assertSame('https://app.toquran.org/storage/test-task-attachment-presenter/file.pdf', $item['public_url']);
    }

    public function test_it_uses_native_pdf_preview_for_loopback_public_urls(): void
    {
        config([
            'document-viewer.pdf_provider' => 'google',
            'filesystems.disks.public.url' => 'http://127.0.0.1:8014/storage',
        ]);

        Storage::persistentFake('public');
        Storage::disk('public')->deleteDirectory('test-task-attachment-presenter');
        Storage::disk('public')->put('test-task-attachment-presenter/file.pdf', '%PDF-1.4');

        $item = app(TaskAttachmentPresenter::class)->forLearner(
            $this->attachment([
                'id' => 101,
                'type' => 'file',
                'path' => 'test-task-attachment-presenter/file.pdf',
                'title' => 'Protected PDF',
            ]),
            60,
            12,
        );

        $this->assertSame('native', $item['viewer_provider']);
        $this->assertSame($item['content_url'], $item['viewer_url']);
        $this->assertSame($item['content_url'], $item['open_url']);
        $this->assertSame('http://127.0.0.1:8014/storage/test-task-attachment-presenter/file.pdf', $item['public_url']);
    }

    public function test_it_presents_office_files_with_microsoft_viewer(): void
    {
        config(['filesystems.disks.public.url' => 'https://app.toquran.org/storage']);

        Storage::persistentFake('public');
        Storage::disk('public')->deleteDirectory('test-task-attachment-presenter');
        Storage::disk('public')->put('test-task-attachment-presenter/lesson plan.docx', 'office body');

        $item = app(TaskAttachmentPresenter::class)->forLearner(
            $this->attachment([
                'id' => 100,
                'type' => 'file',
                'path' => 'test-task-attachment-presenter/lesson plan.docx',
                'title' => 'Lesson Plan.docx',
            ]),
            60,
            12,
        );

        $this->assertSame('microsoft', $item['viewer_provider']);
        $this->assertSame('https://app.toquran.org/storage/test-task-attachment-presenter/lesson%20plan.docx', $item['public_url']);
        $this->assertSame($item['public_url'], $item['open_url']);
        $this->assertStringStartsWith('https://view.officeapps.live.com/op/embed.aspx?src=', (string) $item['viewer_url']);

        parse_str((string) parse_url((string) $item['viewer_url'], PHP_URL_QUERY), $query);

        $this->assertSame($item['public_url'], $query['src'] ?? null);
    }

    public function test_it_presents_journey_file_with_journey_routes(): void
    {
        Storage::persistentFake('public');
        Storage::disk('public')->deleteDirectory('test-task-attachment-presenter-journey');
        Storage::disk('public')->put('test-task-attachment-presenter-journey/file.pdf', '%PDF-1.4');

        $item = app(TaskAttachmentPresenter::class)->forLearner(
            $this->attachment([
                'id' => 91,
                'type' => 'file',
                'path' => 'test-task-attachment-presenter-journey/file.pdf',
            ]),
            61,
            13,
            TaskAttachmentPresenter::SURFACE_JOURNEY,
        );

        $this->assertSame(TaskAttachmentPresenter::MODE_PROTECTED_FILE, $item['mode']);
        $this->assertSame(route('student.journey.attachment.file', [
            'session' => 61,
            'attachment' => 91,
            'student_id' => 13,
        ]), $item['content_url']);
    }

    public function test_it_presents_same_origin_legacy_link_with_return_target(): void
    {
        $returnTo = url('/student/tasks/179/journey/52?task=300');

        $item = app(TaskAttachmentPresenter::class)->forLearner(
            $this->attachment([
                'id' => 92,
                'type' => 'link',
                'path' => url('/reading/listen-read?d=7'),
                'title' => 'Listen Read',
            ]),
            179,
            52,
            TaskAttachmentPresenter::SURFACE_JOURNEY,
            $returnTo,
        );

        $this->assertSame(TaskAttachmentPresenter::MODE_LEGACY_SAME_ORIGIN_LINK, $item['mode']);
        $this->assertStringContainsString('/reading/listen-read?d=7&return_to=', $item['open_url']);
        $this->assertStringContainsString('/student/tasks/179/journey/52?task=300', urldecode((string) $item['open_url']));
        $this->assertStringContainsString('w14_viewer=attachment', (string) $item['open_url']);
    }

    public function test_it_presents_vocabulary_game_link_inside_attachment_viewer_context(): void
    {
        $returnTo = url('/student/classes?open_session=60#task-300');

        $item = app(TaskAttachmentPresenter::class)->forLearner(
            $this->attachment([
                'id' => 97,
                'type' => 'vocabulary_game',
                'path' => route('vocabulary.games.assignment', ['assignment' => 123]),
                'title' => 'Vocab Game: Lesson 1',
            ]),
            60,
            12,
            TaskAttachmentPresenter::SURFACE_SESSION,
            $returnTo,
        );

        $this->assertSame(TaskAttachmentPresenter::MODE_LEGACY_SAME_ORIGIN_LINK, $item['mode']);
        $this->assertSame('ti tabler-balloon', $item['icon']);
        $this->assertStringContainsString('/vocabulary/games/assignment/123?return_to=', (string) $item['open_url']);
        $this->assertStringContainsString('/student/classes?open_session=60#task-300', urldecode((string) $item['open_url']));
        $this->assertStringContainsString('w14_viewer=attachment', (string) $item['open_url']);
    }

    public function test_it_presents_external_link_as_open_link_card_data(): void
    {
        $item = app(TaskAttachmentPresenter::class)->forLearner(
            $this->attachment([
                'id' => 93,
                'type' => 'link',
                'path' => 'https://example.com/resource',
                'title' => 'External Resource',
            ]),
            60,
            12,
        );

        $this->assertSame(TaskAttachmentPresenter::MODE_EXTERNAL_LINK, $item['mode']);
        $this->assertSame('https://example.com/resource', $item['open_url']);
        $this->assertSame('example.com', $item['hostname']);
    }

    public function test_it_presents_youtube_as_trusted_embed(): void
    {
        $item = app(TaskAttachmentPresenter::class)->forLearner(
            $this->attachment([
                'id' => 94,
                'type' => 'youtube',
                'path' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'title' => 'Video',
            ]),
            60,
            12,
        );

        $this->assertSame(TaskAttachmentPresenter::MODE_YOUTUBE, $item['mode']);
        $this->assertSame('https://www.youtube.com/embed/dQw4w9WgXcQ', $item['embed_url']);
    }

    public function test_it_marks_unsafe_or_missing_targets_unavailable(): void
    {
        $unsafe = app(TaskAttachmentPresenter::class)->forLearner(
            $this->attachment([
                'id' => 95,
                'type' => 'link',
                'path' => 'javascript:alert(1)',
                'title' => 'Unsafe Link',
            ]),
            60,
            12,
        );

        $missing = app(TaskAttachmentPresenter::class)->forLearner(
            $this->attachment([
                'id' => 96,
                'type' => 'file',
                'path' => 'missing.pdf',
                'title' => 'Missing PDF',
            ]),
            60,
            12,
        );

        $this->assertSame(TaskAttachmentPresenter::MODE_UNAVAILABLE, $unsafe['mode']);
        $this->assertSame(TaskAttachmentPresenter::MODE_UNAVAILABLE, $missing['mode']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function attachment(array $attributes): AttachmentFile
    {
        $attachment = new AttachmentFile;
        $attachment->forceFill($attributes);

        return $attachment;
    }
}
