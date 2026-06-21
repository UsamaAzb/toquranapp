<?php

namespace App\Services\Library;

use App\Helpers\Helpers;
use App\Models\AttachmentFile;
use App\Models\Background;
use App\Models\Level_up;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentPresenter
{
    public const SURFACE_SESSION = 'session';

    public const SURFACE_JOURNEY = 'journey';

    public const MODE_PROTECTED_FILE = 'protected_file';

    public const MODE_YOUTUBE = 'youtube';

    public const MODE_EXTERNAL_LINK = 'external_link';

    public const MODE_LEGACY_SAME_ORIGIN_LINK = 'legacy_same_origin_link';

    public const MODE_LEGACY_FILE = 'legacy_file';

    public const MODE_UNAVAILABLE = 'unavailable';

    /** @var array<string, bool> */
    private array $legacyTableExists = [];

    public function __construct(private readonly DocumentViewerUrlFactory $documentViewerUrlFactory) {}

    /**
     * @return array{
     *   id:int|null,
     *   title:string,
     *   type:string,
     *   mode:string,
     *   extension:string,
     *   icon:string,
     *   content_url:string|null,
     *   show_url:string|null,
     *   download_url:string|null,
     *   embed_url:string|null,
     *   open_url:string|null,
     *   public_url:string|null,
     *   viewer_provider:string|null,
     *   viewer_url:string|null,
     *   hostname:string|null,
     *   unavailable_reason:string|null
     * }
     */
    public function forLearner(
        AttachmentFile $attachment,
        int $sessionId,
        int $studentId,
        string $surface = self::SURFACE_SESSION,
        ?string $returnTo = null,
    ): array {
        $type = strtolower((string) ($attachment->type ?: 'file'));
        $path = (string) $attachment->path;
        $title = $this->title($attachment, $path);
        $extension = $this->extension($path, $title);
        $id = $attachment->getKey() ? (int) $attachment->getKey() : null;

        return match ($type) {
            'youtube' => $this->youtubeItem($id, $title, $type, $extension, $path),
            'link', 'vocabulary_game' => $this->linkItem($id, $title, $type, $extension, $path, $returnTo),
            default => $this->fileItem($attachment, $id, $title, $type, $extension, $path, $sessionId, $studentId, $surface),
        };
    }

    public function forTeacherSession(
        AttachmentFile $attachment,
        int $sessionId,
        ?string $returnTo = null,
    ): array {
        $type = strtolower((string) ($attachment->type ?: 'file'));
        $path = (string) $attachment->path;
        $title = $this->title($attachment, $path);
        $extension = $this->extension($path, $title);
        $id = $attachment->getKey() ? (int) $attachment->getKey() : null;

        return match ($type) {
            'youtube' => $this->youtubeItem($id, $title, $type, $extension, $path),
            'link', 'vocabulary_game' => $this->linkItem($id, $title, $type, $extension, $path, $returnTo),
            default => $this->teacherFileItem($attachment, $id, $title, $type, $extension, $path, $sessionId),
        };
    }

    private function youtubeItem(?int $id, string $title, string $type, string $extension, string $path): array
    {
        $embedUrl = Helpers::trustedVideoEmbedUrl($path);

        if (! $embedUrl) {
            return $this->unavailableItem($id, $title, $type, $extension, 'This video link is not available.');
        }

        return array_merge($this->baseItem($id, $title, $type, $extension, self::MODE_YOUTUBE), [
            'embed_url' => $embedUrl,
            'open_url' => Helpers::trustedExternalAttachmentUrl($path),
            'hostname' => $this->hostname($path),
        ]);
    }

    private function linkItem(?int $id, string $title, string $type, string $extension, string $path, ?string $returnTo): array
    {
        $url = Helpers::trustedExternalAttachmentUrl($path);

        if (! $url) {
            return $this->unavailableItem($id, $title, $type, $extension, 'This link is not available.');
        }

        if ($this->isSameOriginUrl($url)) {
            $legacyFileUrl = $this->legacyConcreteFileUrl($url);

            if ($legacyFileUrl) {
                return array_merge($this->baseItem(
                    $id,
                    $title,
                    $type,
                    $this->extension($legacyFileUrl, $title),
                    self::MODE_LEGACY_FILE
                ), [
                    'content_url' => $legacyFileUrl,
                    'open_url' => $legacyFileUrl,
                    'hostname' => $this->hostname($legacyFileUrl),
                ]);
            }

            $legacyEmbedUrl = $this->legacyConcreteEmbedUrl($url);

            return array_merge($this->baseItem($id, $title, $type, $extension, self::MODE_LEGACY_SAME_ORIGIN_LINK), [
                'open_url' => $legacyEmbedUrl ?: $this->appendViewerContext($this->appendReturnTarget($url, $returnTo)),
                'hostname' => $this->hostname($legacyEmbedUrl ?: $url),
            ]);
        }

        return array_merge($this->baseItem($id, $title, $type, $extension, self::MODE_EXTERNAL_LINK), [
            'open_url' => $url,
            'hostname' => $this->hostname($url),
        ]);
    }

    private function fileItem(
        AttachmentFile $attachment,
        ?int $id,
        string $title,
        string $type,
        string $extension,
        string $path,
        int $sessionId,
        int $studentId,
        string $surface,
    ): array {
        $storagePath = ltrim($path, '/');

        if ($id === null || $storagePath === '' || ! Storage::disk('public')->exists($storagePath)) {
            return $this->unavailableItem($id, $title, $type, $extension, 'This file is not available.');
        }

        $routeBase = $surface === self::SURFACE_JOURNEY
            ? 'student.journey.attachment'
            : 'student.sessions.attachment';

        $params = [
            'session' => $sessionId,
            'attachment' => $attachment->getKey(),
            'student_id' => $studentId,
        ];

        $contentUrl = route($routeBase.'.file', $params);
        $viewer = $this->documentViewerUrlFactory->forPublicStorageFile($storagePath, $extension, $contentUrl);

        return array_merge($this->baseItem($id, $title, $type, $extension, self::MODE_PROTECTED_FILE), [
            'content_url' => $contentUrl,
            'show_url' => route($routeBase.'.show', $params),
            'download_url' => route($routeBase.'.file', $params + ['download' => 1]),
            'open_url' => $this->openUrlForViewer($viewer, $contentUrl),
            'public_url' => $viewer['public_url'],
            'viewer_provider' => $viewer['provider'],
            'viewer_url' => $viewer['viewer_url'],
        ]);
    }

    private function teacherFileItem(
        AttachmentFile $attachment,
        ?int $id,
        string $title,
        string $type,
        string $extension,
        string $path,
        int $sessionId,
    ): array {
        $storagePath = ltrim($path, '/');

        if ($id === null || $storagePath === '' || ! Storage::disk('public')->exists($storagePath)) {
            return $this->unavailableItem($id, $title, $type, $extension, 'This file is not available.');
        }

        $params = [
            'session' => $sessionId,
            'attachment' => $attachment->getKey(),
        ];

        $contentUrl = route('teacher.sessions.attachment.file', $params);
        $viewer = $this->documentViewerUrlFactory->forPublicStorageFile($storagePath, $extension, $contentUrl);

        return array_merge($this->baseItem($id, $title, $type, $extension, self::MODE_PROTECTED_FILE), [
            'content_url' => $contentUrl,
            'show_url' => route('teacher.sessions.attachment.show', $params),
            'download_url' => route('teacher.sessions.attachment.file', $params + ['download' => 1]),
            'open_url' => $this->openUrlForViewer($viewer, $contentUrl),
            'public_url' => $viewer['public_url'],
            'viewer_provider' => $viewer['provider'],
            'viewer_url' => $viewer['viewer_url'],
        ]);
    }

    /** @param array{provider:string, viewer_url:string|null, public_url:string|null} $viewer */
    private function openUrlForViewer(array $viewer, string $contentUrl): string
    {
        return in_array($viewer['provider'], [
            DocumentViewerUrlFactory::PROVIDER_GOOGLE,
            DocumentViewerUrlFactory::PROVIDER_MICROSOFT,
        ], true)
            ? (string) $viewer['public_url']
            : $contentUrl;
    }

    private function unavailableItem(?int $id, string $title, string $type, string $extension, string $reason): array
    {
        return array_merge($this->baseItem($id, $title, $type, $extension, self::MODE_UNAVAILABLE), [
            'unavailable_reason' => $reason,
        ]);
    }

    private function baseItem(?int $id, string $title, string $type, string $extension, string $mode): array
    {
        return [
            'id' => $id,
            'title' => $title,
            'type' => $type,
            'mode' => $mode,
            'extension' => $extension,
            'icon' => $this->iconFor($type, $title),
            'content_url' => null,
            'show_url' => null,
            'download_url' => null,
            'embed_url' => null,
            'open_url' => null,
            'public_url' => null,
            'viewer_provider' => null,
            'viewer_url' => null,
            'hostname' => null,
            'unavailable_reason' => null,
        ];
    }

    private function extension(string $path, string $title): string
    {
        $extension = strtolower(pathinfo($this->urlPath($path), PATHINFO_EXTENSION));

        if ($extension !== '') {
            return $extension;
        }

        return strtolower(pathinfo($title, PATHINFO_EXTENSION));
    }

    private function title(AttachmentFile $attachment, string $path): string
    {
        $title = trim((string) $attachment->title);

        if ($title !== '') {
            return $title;
        }

        $fallback = basename($this->urlPath($path));

        return $fallback !== '' ? $fallback : 'Attachment';
    }

    private function iconFor(string $type, string $title): string
    {
        if ($type === 'youtube') {
            return 'ti tabler-brand-youtube';
        }

        if ($type === 'link') {
            return 'ti tabler-link';
        }

        if ($type === 'vocabulary_game') {
            return 'ti tabler-balloon';
        }

        $ext = strtolower(pathinfo($title, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'ti tabler-file-type-pdf',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' => 'ti tabler-photo',
            'mp4', 'mov', 'webm' => 'ti tabler-video',
            'mp3', 'wav', 'ogg', 'm4a' => 'ti tabler-music',
            default => 'ti tabler-file-description',
        };
    }

    private function appendReturnTarget(string $url, ?string $returnTo): string
    {
        if (! $returnTo || str_contains($url, 'return_to=')) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query(['return_to' => $returnTo]);
    }

    private function appendViewerContext(string $url): string
    {
        if (str_contains($url, 'w14_viewer=')) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url.$separator.http_build_query(['w14_viewer' => 'attachment']);
    }

    private function legacyConcreteEmbedUrl(string $url): ?string
    {
        $path = trim($this->urlPath($url), '/');

        if (! preg_match('#^tutriols/level-up/([^/]+)$#', $path, $matches)) {
            return null;
        }

        if (! $this->legacyTableAvailable('level_up')) {
            return null;
        }

        $iframeLink = Level_up::query()
            ->where('slug', $matches[1])
            ->value('iframe_link');

        return Helpers::trustedExternalAttachmentUrl((string) $iframeLink);
    }

    private function legacyConcreteFileUrl(string $url): ?string
    {
        $path = trim($this->urlPath($url), '/');

        if (! preg_match('#^background/([^/]+)$#', $path, $matches)) {
            return null;
        }

        if (! $this->legacyTableAvailable('background')) {
            return null;
        }

        $pdfLink = Background::query()
            ->where('slug', 'background/'.$matches[1])
            ->value('pdf_link');

        $assetUrl = Helpers::publicAsset((string) $pdfLink);

        if (! $assetUrl || ! $this->isSameOriginUrl($assetUrl)) {
            return null;
        }

        return str_contains($assetUrl, '#')
            ? $assetUrl
            : $assetUrl.'#toolbar=0';
    }

    private function isSameOriginUrl(string $url): bool
    {
        return str_starts_with($url, url('/'));
    }

    private function hostname(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? $host : null;
    }

    private function urlPath(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) && $path !== '' ? $path : $url;
    }

    private function legacyTableAvailable(string $table): bool
    {
        return $this->legacyTableExists[$table] ??= Schema::hasTable($table);
    }
}
