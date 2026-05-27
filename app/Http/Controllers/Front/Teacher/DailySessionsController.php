<?php

namespace App\Http\Controllers\Front\Teacher;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\DailyAttachmentFile;
use App\Models\DailySession;
use App\Models\MainDailySessionMainTaskAttachment;
use App\Models\MainDailySessionTemplate;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DailySessionsController extends Controller
{
    /**
     * Display a listing of the teacher's classes.
     */
    public function get_subjects()
    {
        $teacherSubjectIds = TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->pluck('subject_id')
            ->unique();

        $subjects_list = Subject::whereIn('id', $teacherSubjectIds)
            ->where('active', 1)
            ->get();
        $breadcrumb_links = [
            'Automation' => null,
            'Versioned Routines' => null,
        ];

        return view('teacher.DailySession.subjects', compact('subjects_list', 'breadcrumb_links'));
    }

    public function get_sessions(string $auth_role, int $subject)
    {
        abort_unless(Subject::query()->whereKey($subject)->exists(), 404);
        $subjectid = $subject;

        $hasSubject = TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', Auth::id())
            ->where('subject_id', $subjectid)
            ->availableForTeacher()
            ->exists();

        if (! $hasSubject) {
            abort(403);
        }

        $subjectTitle = Subject::query()->whereKey($subjectid)->value('title') ?: 'Subject';
        $breadcrumb_links = [
            'Automation' => null,
            'Versioned Routines' => route('daily-sessions.get_subjects', ['auth_role' => 'teacher']),
            $subjectTitle => null,
        ];

        return view('teacher.DailySession.daily_sessions', compact('subjectid', 'subjectTitle', 'breadcrumb_links'));
    }

    private function resolveOwnedTemplateOrFail(int $templateId): MainDailySessionTemplate
    {
        $template = MainDailySessionTemplate::query()
            ->whereKey($templateId)
            ->where('created_by_user_id', Auth::id())
            ->firstOrFail();

        $hasSubject = TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', Auth::id())
            ->where('subject_id', $template->subject_id)
            ->availableForTeacher()
            ->exists();

        abort_unless($hasSubject, 403);

        return $template;
    }

    private function resolveOwnedDailySessionOrFail(int $dailySessionId): DailySession
    {
        $dailySession = DailySession::with('main_daily_session')->findOrFail($dailySessionId);
        $subjectid = (int) optional($dailySession->main_daily_session)->subject_id;
        abort_unless($subjectid, 404);

        $hasSubject = TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', Auth::id())
            ->where('subject_id', $subjectid)
            ->availableForTeacher()
            ->exists();

        abort_unless($hasSubject, 403);

        return $dailySession;
    }

    private function resolveOwnedTemplateAttachmentOrFail(int $templateId, int $attachmentId): MainDailySessionMainTaskAttachment
    {
        $this->resolveOwnedTemplateOrFail($templateId);

        return MainDailySessionMainTaskAttachment::query()
            ->with('mainTask.template')
            ->whereKey($attachmentId)
            ->whereHas('mainTask.template', fn ($query) => $query
                ->whereKey($templateId)
                ->where('created_by_user_id', Auth::id()))
            ->firstOrFail();
    }

    public function show_attachment(int $dailySessionId, int $attachmentId): View
    {
        $dailySession = $this->resolveOwnedDailySessionOrFail($dailySessionId);
        $subjectid = (int) optional($dailySession->main_daily_session)->subject_id;

        $attachment = DailyAttachmentFile::with('daily_session_task.dailySession.main_daily_session')
            ->findOrFail($attachmentId);

        //  Security check
        if (
            ! $attachment->daily_session_task ||
            $attachment->daily_session_task->daily_session_id != $dailySessionId
        ) {
            abort(404);
        }

        $type = strtolower($attachment->type);
        $path = $attachment->path;

        $isExternal = in_array($type, ['link', 'youtube'], true);

        $fileUrl = $isExternal
            ? $path
            : route('daily-sessions.attachment.file', [
                'dailySession' => $dailySessionId,
                'attachment' => $attachment->id,
            ]);
        $embedUrl = $type === 'youtube'
            ? Helpers::trustedVideoEmbedUrl((string) $path)
            : null;
        $downloadUrl = $isExternal
            ? null
            : route('daily-sessions.attachment.file', [
                'dailySession' => $dailySessionId,
                'attachment' => $attachment->id,
                'download' => 1,
            ]);

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $sessionUrl = route('daily-sessions.get_sessions', [
            'auth_role' => 'teacher',
            'subject' => $subjectid,
        ]).'?'.http_build_query(['open_daily' => $dailySessionId]).'#task-'.$attachment->daily_session_task_id;
        $breadcrumb_links = [
            'Versioned Routines' => route('daily-sessions.get_subjects', ['auth_role' => 'teacher']),
            $dailySession->title ?? 'Daily Session' => $sessionUrl,
        ];

        return view('teacher.DailySession.attachment-show', [
            'attachment' => $attachment,
            'fileUrl' => $fileUrl,
            'embedUrl' => $embedUrl,
            'downloadUrl' => $downloadUrl,
            'type' => $type,
            'ext' => $ext,
            'subjectid' => $subjectid,
            'dailySessionId' => $dailySessionId,
            'sessionUrl' => $sessionUrl,
            'backButtonLabel' => 'Back to Daily Session Tasks',
            'breadcrumb_links' => $breadcrumb_links,
        ]

        );
    }

    public function show_template_attachment(int $templateId, int $attachmentId): View
    {
        $template = $this->resolveOwnedTemplateOrFail($templateId);
        $attachment = $this->resolveOwnedTemplateAttachmentOrFail($templateId, $attachmentId);

        $type = strtolower((string) $attachment->type);
        $resource = (string) ($attachment->url ?? $attachment->path ?? '');
        $isExternal = in_array($type, ['link', 'youtube'], true);

        $fileUrl = $isExternal
            ? $resource
            : route('daily-sessions.template-attachment.file', [
                'template' => $templateId,
                'attachment' => $attachment->id,
            ]);
        $embedUrl = $type === 'youtube'
            ? Helpers::trustedVideoEmbedUrl($resource)
            : null;
        $downloadUrl = $isExternal
            ? null
            : route('daily-sessions.template-attachment.file', [
                'template' => $templateId,
                'attachment' => $attachment->id,
                'download' => 1,
            ]);

        $ext = strtolower(pathinfo($resource, PATHINFO_EXTENSION));
        $subjectid = (int) $template->subject_id;
        $sessionUrl = route('daily-sessions.get_sessions', [
            'auth_role' => 'teacher',
            'subject' => $subjectid,
        ]);
        $breadcrumb_links = [
            'Versioned Routines' => route('daily-sessions.get_subjects', ['auth_role' => 'teacher']),
            $template->title ?: 'Template' => $sessionUrl,
        ];

        return view('teacher.DailySession.attachment-show', [
            'attachment' => $attachment,
            'fileUrl' => $fileUrl,
            'embedUrl' => $embedUrl,
            'downloadUrl' => $downloadUrl,
            'type' => $type,
            'ext' => $ext,
            'subjectid' => $subjectid,
            'dailySessionId' => null,
            'sessionUrl' => $sessionUrl,
            'backButtonLabel' => 'Back to Versioned Routines',
            'breadcrumb_links' => $breadcrumb_links,
        ]);
    }

    public function stream_attachment(Request $request, int $dailySessionId, int $attachmentId): BinaryFileResponse
    {
        $this->resolveOwnedDailySessionOrFail($dailySessionId);

        $attachment = DailyAttachmentFile::with('daily_session_task.dailySession')->findOrFail($attachmentId);

        if (
            ! $attachment->daily_session_task ||
            $attachment->daily_session_task->daily_session_id != $dailySessionId
        ) {
            abort(404);
        }

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

    public function stream_template_attachment(Request $request, int $templateId, int $attachmentId): BinaryFileResponse
    {
        $attachment = $this->resolveOwnedTemplateAttachmentOrFail($templateId, $attachmentId);

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
}
