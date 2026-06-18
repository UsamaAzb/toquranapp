<?php

namespace App\Services\Library;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GeneralLibraryAttachmentSnapshotter
{
    public function __construct(
        private readonly GeneralLibraryAttachmentAdapter $adapter
    ) {}

    public function snapshotAttributesForSelection(string $resourceId, int $ownerUserId): ?array
    {
        $attributes = $this->adapter->attachmentAttributesFor($resourceId, $ownerUserId);

        if ($attributes === null) {
            return null;
        }

        $type = (string) ($attributes['type'] ?? '');

        if ($type === 'file') {
            $path = $this->copyFileToPublicAttachmentPath($attributes);

            if ($path === null) {
                return null;
            }

            return [
                'type' => 'file',
                'title' => (string) $attributes['title'],
                'description' => $attributes['description'] ?? null,
                'path' => $path,
                'url' => null,
                'file_size' => $attributes['file_size'] ?? null,
            ];
        }

        if ($type === 'text') {
            return [
                'type' => 'text',
                'title' => (string) $attributes['title'],
                'description' => $attributes['description'] ?? null,
                'path' => null,
                'url' => null,
                'file_size' => null,
            ];
        }

        if (! in_array($type, ['link', 'youtube'], true)) {
            return null;
        }

        return [
            'type' => $type,
            'title' => (string) $attributes['title'],
            'description' => $attributes['description'] ?? null,
            'path' => null,
            'url' => $attributes['path'] ?? null,
            'file_size' => null,
        ];
    }

    public function attachmentFileAttributesForSelection(string $resourceId, int $ownerUserId): ?array
    {
        $attributes = $this->snapshotAttributesForSelection($resourceId, $ownerUserId);

        if ($attributes === null) {
            return null;
        }

        return [
            'title' => $attributes['title'],
            'description' => $attributes['description'],
            'type' => $attributes['type'],
            'path' => $attributes['path'] ?? $attributes['url'],
            'file_size' => $attributes['file_size'],
        ];
    }

    private function copyFileToPublicAttachmentPath(array $attributes): ?string
    {
        $sourceDisk = (string) ($attributes['source_disk'] ?? 'local');
        $sourcePath = ltrim((string) ($attributes['path'] ?? ''), '/');

        if (! in_array($sourceDisk, ['local', 'public'], true)
            || $sourcePath === ''
            || ! Storage::disk($sourceDisk)->exists($sourcePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $sourceResourceId = (int) ($attributes['source_resource_id'] ?? 0);
        $targetPath = 'attachments/general-library-resource-'.$sourceResourceId.'/'.Str::uuid()
            .($extension !== '' ? '.'.$extension : '');

        $source = Storage::disk($sourceDisk)->readStream($sourcePath);
        if ($source === false) {
            return null;
        }

        try {
            Storage::disk('public')->put($targetPath, $source);
        } finally {
            if (is_resource($source)) {
                fclose($source);
            }
        }

        return Storage::disk('public')->exists($targetPath) ? $targetPath : null;
    }
}
