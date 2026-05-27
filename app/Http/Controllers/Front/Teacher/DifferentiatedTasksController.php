<?php

namespace App\Http\Controllers\Front\Teacher;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\DifferentiatedTask;
use App\Models\DifferentiatedTaskAttachment;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DifferentiatedTasksController extends Controller
{
    public function get_subjects(): View
    {
        $teacherSubjectIds = TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->pluck('subject_id')
            ->unique();

        $subjects_list = Subject::query()
            ->whereIn('id', $teacherSubjectIds)
            ->where('active', 1)
            ->get();
        $breadcrumb_links = [
            'Automation' => null,
            'Differentiated Tasks' => null,
        ];

        return view('teacher.DifferentiatedTasks.subjects', compact('subjects_list', 'breadcrumb_links'));
    }

    public function get_tasks(string $auth_role, int $subject): View
    {
        abort_unless(Subject::query()->whereKey($subject)->exists(), 404);

        $hasSubject = TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', Auth::id())
            ->where('subject_id', $subject)
            ->availableForTeacher()
            ->exists();

        abort_unless($hasSubject, 403);

        $subjectTitle = Subject::query()->whereKey($subject)->value('title') ?: 'Subject';
        $subjectid = $subject;
        $breadcrumb_links = [
            'Automation' => null,
            'Differentiated Tasks' => route('differentiated-tasks.get_subjects', ['auth_role' => 'teacher']),
            $subjectTitle => null,
        ];

        return view('teacher.DifferentiatedTasks.tasks', compact('subjectid', 'subjectTitle', 'breadcrumb_links'));
    }

    public function show_attachment(DifferentiatedTask $task, DifferentiatedTaskAttachment $attachment): View
    {
        $this->assertOwnedTaskAttachment($task, $attachment);

        $type = strtolower((string) $attachment->type);
        $resource = (string) ($attachment->url ?? $attachment->path ?? '');
        $isExternal = in_array($type, ['link', 'youtube'], true);
        $fileUrl = $isExternal
            ? $resource
            : route('differentiated-tasks.attachment.file', [
                'task' => $task->id,
                'attachment' => $attachment->id,
            ]);
        $embedUrl = $type === 'youtube'
            ? Helpers::trustedVideoEmbedUrl($resource)
            : null;
        $downloadUrl = $isExternal
            ? null
            : route('differentiated-tasks.attachment.file', [
                'task' => $task->id,
                'attachment' => $attachment->id,
                'download' => 1,
            ]);

        $ext = strtolower(pathinfo(parse_url($resource, PHP_URL_PATH) ?: $resource, PATHINFO_EXTENSION));
        $sessionUrl = route('differentiated-tasks.get_tasks', [
            'auth_role' => 'teacher',
            'subject' => $task->subject_id,
        ]);
        $breadcrumb_links = [
            'Differentiated Tasks' => route('differentiated-tasks.get_subjects', ['auth_role' => 'teacher']),
            $task->title ?: 'Task' => $sessionUrl,
        ];

        return view('teacher.DailySession.attachment-show', [
            'attachment' => $attachment,
            'fileUrl' => $fileUrl,
            'embedUrl' => $embedUrl,
            'downloadUrl' => $downloadUrl,
            'type' => $type,
            'ext' => $ext,
            'subjectid' => (int) $task->subject_id,
            'dailySessionId' => null,
            'sessionUrl' => $sessionUrl,
            'backButtonLabel' => 'Back to Differentiated Tasks',
            'breadcrumb_links' => $breadcrumb_links,
        ]);
    }

    public function stream_attachment(
        Request $request,
        DifferentiatedTask $task,
        DifferentiatedTaskAttachment $attachment
    ): BinaryFileResponse {
        $this->assertOwnedTaskAttachment($task, $attachment);

        abort_if(in_array(strtolower((string) $attachment->type), ['link', 'youtube'], true), 404);

        $path = ltrim((string) $attachment->path, '/');
        abort_if($path === '' || ! Storage::disk('public')->exists($path), 404);

        $absolutePath = Storage::disk('public')->path($path);
        $mimeType = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
        $downloadName = $this->safeDownloadFilename($path);

        if ($request->boolean('download')) {
            return $this->attachmentDownloadResponse($absolutePath, $downloadName, [
                'Content-Type' => $mimeType,
            ]);
        }

        return $this->inlineAttachmentResponse($absolutePath, $downloadName, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function assertOwnedTaskAttachment(DifferentiatedTask $task, DifferentiatedTaskAttachment $attachment): void
    {
        abort_unless((int) $task->created_by_user_id === (int) Auth::id(), 403);
        abort_unless((int) $attachment->differentiated_task_id === (int) $task->id, 404);

        $hasSubject = TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', Auth::id())
            ->where('subject_id', $task->subject_id)
            ->availableForTeacher()
            ->exists();

        abort_unless($hasSubject, 403);
    }
}
