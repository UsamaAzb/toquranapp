<?php

namespace App\Services;

use App\Helpers\Helpers;
use App\Models\AttachmentFile;
use App\Models\ClassSession;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    public function prepareTeacherSessionViewData(
        AttachmentFile $attachment,
        ClassSession $classSession,
        int $sessionId
    ): array {
        $type = $this->normalizedType($attachment);
        $path = (string) $attachment->path;
        $isExternal = $this->isExternal($attachment);
        $fileUrl = $isExternal
            ? $path
            : route('teacher.sessions.attachment.file', [
                'session' => $sessionId,
                'attachment' => $attachment->id,
            ]);
        $downloadUrl = $isExternal
            ? null
            : route('teacher.sessions.attachment.file', [
                'session' => $sessionId,
                'attachment' => $attachment->id,
                'download' => 1,
            ]);
        $teachersubjectid = $classSession->teacher_subject_classes_id;
        $sessionsUrl = route('teacher.sessions', ['teachersubjectid' => $teachersubjectid]);
        $sessionUrl = $sessionsUrl
            .'?'.http_build_query(['open_session' => $sessionId])
            .'#task-'.$attachment->task->id;

        return [
            'attachment' => $attachment,
            'fileUrl' => $fileUrl,
            'embedUrl' => $type === 'youtube'
                ? Helpers::trustedVideoEmbedUrl($path)
                : null,
            'downloadUrl' => $downloadUrl,
            'type' => $type,
            'ext' => strtolower(pathinfo($path, PATHINFO_EXTENSION)),
            'teachersubjectid' => $teachersubjectid,
            'sessionUrl' => $sessionUrl,
            'breadcrumb_links' => [
                'Sessions' => $sessionsUrl,
                $classSession->title ?? 'Session' => $sessionUrl,
            ],
            'fileAvailable' => $isExternal || $this->fileExists($attachment),
        ];
    }

    public function isExternal(AttachmentFile $attachment): bool
    {
        return in_array($this->normalizedType($attachment), ['link', 'youtube'], true);
    }

    public function normalizedPath(?string $path): string
    {
        return ltrim((string) $path, '/');
    }

    public function fileExists(AttachmentFile $attachment): bool
    {
        $path = $this->normalizedPath($attachment->path);

        return $path !== '' && Storage::disk('public')->exists($path);
    }

    public function normalizedType(AttachmentFile $attachment): string
    {
        return strtolower((string) $attachment->type);
    }
}
