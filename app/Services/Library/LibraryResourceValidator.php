<?php

namespace App\Services\Library;

use App\Models\LibraryResource;
use App\Models\LibrarySection;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class LibraryResourceValidator
{
    // Keep PHP/web-server upload limits at or above this value on production.
    public const MAX_UPLOAD_KB = 512000;

    public const ALLOWED_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
        'webp',
        'gif',
        'mp4',
        'mp3',
        'm4a',
        'wav',
        'webm',
    ];

    public static function allowedExtensions(): array
    {
        return self::ALLOWED_EXTENSIONS;
    }

    public static function acceptAttribute(): string
    {
        return implode(',', array_map(
            fn (string $extension): string => '.'.$extension,
            self::ALLOWED_EXTENSIONS
        ));
    }

    public function validateSectionForResource(int $ownerUserId, int $subjectId, LibrarySection $section): void
    {
        if ((int) $section->owner_user_id !== $ownerUserId) {
            throw ValidationException::withMessages([
                'library_section_id' => 'Choose a folder from your own Library.',
            ]);
        }

        if ((int) $section->subject_id !== $subjectId) {
            throw ValidationException::withMessages([
                'library_section_id' => 'Choose a folder from the same subject.',
            ]);
        }

        if (! $section->isActive()) {
            throw ValidationException::withMessages([
                'library_section_id' => 'Choose an active Library folder.',
            ]);
        }
    }

    public function validateUniqueActiveTitle(
        int $sectionId,
        string $title,
        ?int $ignoreResourceId = null
    ): void {
        $exists = LibraryResource::query()
            ->where('library_section_id', $sectionId)
            ->where('status', LibraryResource::STATUS_ACTIVE)
            ->where('title', trim($title))
            ->when($ignoreResourceId !== null, fn ($query) => $query->whereKeyNot($ignoreResourceId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'title' => 'This resource already exists in the selected Library folder.',
            ]);
        }
    }

    public function validateFileUpload(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'file' => 'The Library file could not be uploaded. Please choose it again.',
            ]);
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'file' => 'Upload a supported Library file type.',
            ]);
        }

        if ($file->getSize() > self::MAX_UPLOAD_KB * 1024) {
            throw ValidationException::withMessages([
                'file' => sprintf('Library files must be %d MB or smaller.', self::MAX_UPLOAD_KB / 1024),
            ]);
        }
    }

    public function validateLinkUrl(?string $url): void
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw ValidationException::withMessages([
                'external_url' => 'Enter a valid resource link.',
            ]);
        }

        $scheme = strtolower((string) parse_url((string) $url, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw ValidationException::withMessages([
                'external_url' => 'Library links must start with http:// or https://.',
            ]);
        }
    }

    public function validateResourceMatchesSection(LibraryResource $resource, LibrarySection $section): void
    {
        if ((int) $resource->owner_user_id !== (int) $section->owner_user_id) {
            throw ValidationException::withMessages([
                'library_section_id' => 'Resource owner must match the selected folder.',
            ]);
        }

        if ((int) $resource->subject_id !== (int) $section->subject_id) {
            throw ValidationException::withMessages([
                'library_section_id' => 'Resource subject must match the selected folder.',
            ]);
        }
    }
}
