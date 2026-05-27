<?php

namespace App\Services\Vocabulary;

use App\Models\TeacherSubjectClass;
use App\Models\User;
use App\Models\VocabularyGameAssignment;
use App\Models\VocabularySet;
use Illuminate\Support\Facades\Schema;

class VocabularyGameAttachmentBuilder
{
    private const SOURCE_PREFIX = 'vocabulary://set/';

    public static function sourcePath(int $setId): string
    {
        return self::SOURCE_PREFIX.$setId;
    }

    public static function setIdFromPath(?string $path): ?int
    {
        $path = trim((string) $path);

        if (! str_starts_with($path, self::SOURCE_PREFIX)) {
            return null;
        }

        $id = (int) substr($path, strlen(self::SOURCE_PREFIX));

        return $id > 0 ? $id : null;
    }

    public function studentAttachmentAttributes(
        int $setId,
        int $assignedByUserId,
        int $studentId,
        ?string $title,
        ?string $description,
        TeacherSubjectClass $teacherSubjectClass,
        int $classId
    ): ?array {
        if (! Schema::hasTable('vocabulary_sets') || ! Schema::hasTable('vocabulary_game_assignments')) {
            return null;
        }

        $setQuery = VocabularySet::query()
            ->playable()
            ->whereKey($setId)
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED);

        $assignedBy = User::query()->find($assignedByUserId);

        if (! $assignedBy?->hasAnyRole(['admin', 'super_admin', 'owner'])) {
            $setQuery->visibleToTeachers($assignedByUserId);
        }

        $set = $setQuery->first();

        if (! $set instanceof VocabularySet || ! $set->canBeLaunched()) {
            return null;
        }

        $assignment = VocabularyGameAssignment::query()->create([
            'vocabulary_set_id' => $set->id,
            'assigned_by_user_id' => $assignedByUserId,
            'audience_type' => VocabularyGameAssignment::AUDIENCE_STUDENT,
            'audience_id' => $studentId,
            'allowed_games' => ['hangman', 'missing_letter', 'spelling_choice'],
            'difficulty_policy' => VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE,
            'status' => VocabularyGameAssignment::STATUS_ACTIVE,
        ]);

        return [
            'title' => $this->attachmentTitle($title, (string) $set->title),
            'description' => $description ?: $set->description,
            'type' => 'link',
            'path' => $assignment->play_url,
            'file_size' => null,
            'subject_id' => $teacherSubjectClass->subject_id,
            'class_id' => $classId,
            'teacher_subject_class_id' => $teacherSubjectClass->id,
        ];
    }

    private function attachmentTitle(?string $title, string $setTitle): string
    {
        $title = trim((string) $title);

        if ($title !== '') {
            return str_starts_with($title, 'Vocab Game: ') ? $title : 'Vocab Game: '.$title;
        }

        return 'Vocab Game: '.$setTitle;
    }
}
