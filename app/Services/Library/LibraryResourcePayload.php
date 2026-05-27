<?php

namespace App\Services\Library;

use App\Helpers\Helpers;
use App\Models\LibraryResource;

class LibraryResourcePayload
{
    public static function forSnapshot(LibraryResource $resource): array
    {
        $type = self::attachmentType($resource);

        return [
            'type' => $type,
            'title' => (string) $resource->title,
            'description' => $resource->description,
            'path' => $resource->isFile() ? $resource->file_path : null,
            'url' => $resource->isFile() ? null : self::externalUrl($resource, $type),
            'file_size' => $resource->file_size,
        ];
    }

    private static function attachmentType(LibraryResource $resource): string
    {
        if ($resource->isFile()) {
            return 'file';
        }

        return self::youtubeEmbedUrl($resource) === null ? 'link' : 'youtube';
    }

    private static function externalUrl(LibraryResource $resource, string $type): ?string
    {
        if ($type === 'youtube') {
            return self::youtubeEmbedUrl($resource) ?? $resource->external_url;
        }

        return $resource->external_url;
    }

    private static function youtubeEmbedUrl(LibraryResource $resource): ?string
    {
        if ($resource->external_url === null) {
            return null;
        }

        $embedUrl = Helpers::trustedVideoEmbedUrl((string) $resource->external_url);
        if ($embedUrl === null) {
            return null;
        }

        $host = parse_url($embedUrl, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return null;
        }

        return str_contains(strtolower($host), 'youtube') ? $embedUrl : null;
    }
}
