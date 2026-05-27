<?php

namespace App\Services\Library;

use App\Helpers\Helpers;
use App\Http\Controllers\VocabularyAssignmentController;
use App\Models\AttachmentFile;
use App\Models\ClassSession;
use App\Models\LibraryResource;
use App\Models\SessionTask;
use App\Models\User;
use App\Models\VocabularyGameAssignment;
use App\Models\VocabularySet;
use App\Services\SeriesLibrarySourceResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class LibraryResourceAttachmentWriter
{
    private ?bool $hasSortOrderColumn = null;

    public function writeForTask(SessionTask $task, ClassSession $session, array $resourceIds, int $ownerUserId): int
    {
        $orderedResourceIds = $this->orderedResourceIds($resourceIds);
        $created = 0;
        $sortOrder = $this->nextSortOrder($task);

        foreach ($orderedResourceIds as $resourceId) {
            if ($this->writeOneForTaskAtSortOrder($task, $session, (string) $resourceId, $ownerUserId, $sortOrder)) {
                $created++;
                $sortOrder++;
            }
        }

        return $created;
    }

    public function writeOneForTaskAtSortOrder(
        SessionTask $task,
        ClassSession $session,
        string $resourceId,
        int $ownerUserId,
        int $sortOrder
    ): bool {
        $attributes = $this->attachmentAttributesForResourceId($task, $session, $resourceId, $ownerUserId);

        if ($attributes === null) {
            return false;
        }

        AttachmentFile::create($this->withSortOrder([
            'session_task_id' => $task->id,
            'subject_id' => $session->subject_id,
            'class_id' => $session->class_id,
            'teacher_subject_class_id' => $session->teacher_subject_classes_id,
        ] + $attributes, $sortOrder));

        return true;
    }

    private function attachmentAttributesForResourceId(
        SessionTask $task,
        ClassSession $session,
        string $resourceId,
        int $ownerUserId
    ): ?array {
        if (is_numeric($resourceId)) {
            $resource = $this->eligibleResources([(int) $resourceId], $ownerUserId, (int) $session->subject_id)->first();

            if (! $resource instanceof LibraryResource) {
                return null;
            }

            $attachmentType = $this->attachmentType($resource);

            return [
                'title' => $resource->title,
                'description' => $resource->description ?? $task->description,
                'type' => $attachmentType,
                'path' => $this->attachmentPath($resource, $attachmentType),
                'file_size' => $resource->file_size,
            ];
        }

        if (! str_starts_with($resourceId, 'series__')) {
            return null;
        }

        $owner = User::query()->find($ownerUserId);
        if ($owner === null) {
            return null;
        }

        $resource = collect(app(LegacyLibraryTaskResourceCatalog::class)->findManyForSubject(
            $owner,
            (int) $session->subject_id,
            [$resourceId]
        ))->first();

        if (! is_array($resource)) {
            return null;
        }

        if (($resource['source_type'] ?? '') === SeriesLibrarySourceResolver::SOURCE_VOCABULARY_LIST) {
            return $this->vocabularyGameAttachmentAttributes($task, $session, $resource, $ownerUserId);
        }

        return [
            'title' => $resource['title'],
            'description' => $resource['description'] ?: $task->description,
            'type' => 'link',
            'path' => $resource['url'],
            'file_size' => null,
        ];
    }

    private function vocabularyGameAttachmentAttributes(
        SessionTask $task,
        ClassSession $session,
        array $resource,
        int $ownerUserId
    ): ?array {
        if (! Schema::hasTable('vocabulary_sets') || ! Schema::hasTable('vocabulary_game_assignments')) {
            return null;
        }

        $set = VocabularySet::query()
            ->visibleToTeachers($ownerUserId)
            ->whereKey((int) ($resource['source_id'] ?? 0))
            ->first();

        if (! $set instanceof VocabularySet || ! $set->canBeLaunched()) {
            return null;
        }

        $assignment = VocabularyGameAssignment::query()->create([
            'vocabulary_set_id' => (int) $set->id,
            'assigned_by_user_id' => $ownerUserId,
            'audience_type' => VocabularyGameAssignment::AUDIENCE_CLASS,
            'audience_id' => (int) $session->class_id,
            'allowed_games' => ['hangman', 'missing_letter', 'spelling_choice'],
            'difficulty_policy' => VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE,
            'status' => VocabularyGameAssignment::STATUS_ACTIVE,
        ]);

        return [
            'title' => 'Vocab Game: '.$set->title,
            'description' => $resource['description'] ?: $task->description,
            'type' => 'link',
            'path' => VocabularyAssignmentController::assignmentUrl($assignment),
            'file_size' => null,
        ];
    }

    /**
     * @return Collection<int, LibraryResource>
     */
    private function eligibleResources(array $resourceIds, int $ownerUserId, int $subjectId): Collection
    {
        $ids = collect($resourceIds)
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return collect();
        }

        $activeSectionIds = app(LibraryResourceQuery::class)->activeSectionIdsForOwner($ownerUserId, $subjectId);

        if ($activeSectionIds === []) {
            return collect();
        }

        return LibraryResource::query()
            ->whereIn('id', $ids)
            ->where('owner_user_id', $ownerUserId)
            ->where('subject_id', $subjectId)
            ->whereIn('library_section_id', $activeSectionIds)
            ->where('status', LibraryResource::STATUS_ACTIVE)
            ->get()
            ->sortBy(fn (LibraryResource $resource): int => array_search((int) $resource->id, $ids, true))
            ->values();
    }

    private function orderedResourceIds(array $resourceIds): array
    {
        return collect($resourceIds)
            ->filter(fn ($id): bool => is_numeric($id) || (is_string($id) && str_starts_with($id, 'series__')))
            ->map(fn ($id): string => is_numeric($id) ? (string) (int) $id : (string) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function nextSortOrder(SessionTask $task): int
    {
        if (! $this->hasSortOrderColumn()) {
            return 1;
        }

        return ((int) AttachmentFile::query()
            ->where('session_task_id', $task->id)
            ->max('sort_order')) + 1;
    }

    private function withSortOrder(array $attributes, int $sortOrder): array
    {
        if ($this->hasSortOrderColumn()) {
            $attributes['sort_order'] = $sortOrder;
        }

        return $attributes;
    }

    private function hasSortOrderColumn(): bool
    {
        return $this->hasSortOrderColumn ??= Schema::hasColumn((new AttachmentFile())->getTable(), 'sort_order');
    }

    private function attachmentType(LibraryResource $resource): string
    {
        if ($resource->isFile()) {
            return 'file';
        }

        return Helpers::isYoutubeUrl($resource->external_url) ? 'youtube' : 'link';
    }

    private function attachmentPath(LibraryResource $resource, string $attachmentType): ?string
    {
        if ($resource->isFile()) {
            return $resource->file_path;
        }

        if ($attachmentType === 'youtube') {
            return $this->youtubeEmbedUrl($resource) ?? $resource->external_url;
        }

        return $resource->external_url;
    }

    private function youtubeEmbedUrl(LibraryResource $resource): ?string
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
