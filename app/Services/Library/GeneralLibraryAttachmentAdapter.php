<?php

namespace App\Services\Library;

use App\Helpers\Helpers;
use App\Models\GeneralLibraryResource;
use App\Models\User;

class GeneralLibraryAttachmentAdapter
{
    public const GENERAL_PREFIX = 'general__';

    public function __construct(
        private readonly GeneralLibraryAccessService $accessService
    ) {}

    public function attachmentAttributesFor(string $resourceId, int $ownerUserId): ?array
    {
        if (str_starts_with($resourceId, self::GENERAL_PREFIX)) {
            return $this->generalResourceAttributes((int) substr($resourceId, strlen(self::GENERAL_PREFIX)), $ownerUserId);
        }

        return null;
    }

    public function isGeneralLibrarySelection(string $resourceId): bool
    {
        return str_starts_with($resourceId, self::GENERAL_PREFIX);
    }

    private function generalResourceAttributes(int $resourceId, int $ownerUserId): ?array
    {
        $user = User::query()->find($ownerUserId);
        $resource = GeneralLibraryResource::query()
            ->whereKey($resourceId)
            ->where('status', GeneralLibraryResource::STATUS_ACTIVE)
            ->first();

        if (! $user || ! $resource || ! $this->accessService->canUseResource($user, $resource)) {
            return null;
        }

        if ($resource->isFile()) {
            return [
                'title' => (string) $resource->title,
                'description' => $resource->description,
                'type' => 'file',
                'path' => $resource->file_path,
                'source_disk' => $resource->storage_disk ?: 'local',
                'source_resource_id' => (int) $resource->id,
                'file_size' => $resource->file_size,
            ];
        }

        if ($resource->isYoutube()) {
            $embedUrl = $this->trustedYoutubeEmbed((string) $resource->external_url);
            if ($embedUrl === null) {
                return null;
            }

            return [
                'title' => (string) $resource->title,
                'description' => $resource->description,
                'type' => 'youtube',
                'path' => $embedUrl,
                'file_size' => null,
            ];
        }

        return [
            'title' => (string) $resource->title,
            'description' => $resource->description,
            'type' => 'link',
            'path' => $resource->external_url,
            'file_size' => null,
        ];
    }

    private function trustedYoutubeEmbed(string $url): ?string
    {
        $embedUrl = Helpers::trustedVideoEmbedUrl($url);
        if ($embedUrl === null) {
            return null;
        }

        $host = parse_url($embedUrl, PHP_URL_HOST);

        return is_string($host) && str_contains(strtolower($host), 'youtube')
            ? $embedUrl
            : null;
    }
}
