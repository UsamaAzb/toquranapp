<?php

namespace App\Services\Library;

use App\Models\AttachmentFile;
use App\Models\LibraryResource;
use Illuminate\Support\Facades\Storage;

class LibraryFileRetentionService
{
    public function isReferenced(
        ?string $path,
        ?int $exceptLibraryResourceId = null,
        ?int $exceptAttachmentFileId = null
    ): bool {
        if (! filled($path)) {
            return false;
        }

        $libraryReferenceExists = LibraryResource::query()
            ->where('file_path', $path)
            ->when($exceptLibraryResourceId !== null, fn ($query) => $query->whereKeyNot($exceptLibraryResourceId))
            ->exists();

        if ($libraryReferenceExists) {
            return true;
        }

        return AttachmentFile::query()
            ->where('path', $path)
            ->when($exceptAttachmentFileId !== null, fn ($query) => $query->whereKeyNot($exceptAttachmentFileId))
            ->exists();
    }

    public function canDeletePath(
        ?string $path,
        ?int $exceptLibraryResourceId = null,
        ?int $exceptAttachmentFileId = null
    ): bool {
        return filled($path)
            && ! $this->isReferenced($path, $exceptLibraryResourceId, $exceptAttachmentFileId);
    }

    public function deleteIfUnreferenced(
        ?string $path,
        string $disk = 'public',
        ?int $exceptLibraryResourceId = null,
        ?int $exceptAttachmentFileId = null
    ): bool {
        if (! $this->canDeletePath($path, $exceptLibraryResourceId, $exceptAttachmentFileId)) {
            return false;
        }

        try {
            return Storage::disk($disk)->delete($path);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
