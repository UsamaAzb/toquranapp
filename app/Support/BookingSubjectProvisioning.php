<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\GradeLevelSubject;
use App\Models\StudentsSubject;
use App\Models\TeacherSubjectClass;
use Illuminate\Support\Facades\Schema;

final class BookingSubjectProvisioning
{
    public const SUBJECT_LANGUAGE_AND_LITERATURE = 1;

    /** @deprecated Use SUBJECT_LANGUAGE_AND_LITERATURE. */
    public const SUBJECT_ENGLISH = 1;

    public const SUBJECT_MATH = 2;

    public const SUBJECT_WELL_BEING = 15;

    public static function planForGradeLevel(?int $gradeLevelId): array
    {
        if (! $gradeLevelId || ! Schema::hasTable('grade_level_subjects')) {
            return [];
        }

        $query = GradeLevelSubject::query()
            ->where('grade_level_id', $gradeLevelId)
            ->where('status', 'active')
            ->orderBy('id');

        if (Schema::hasTable('subjects')) {
            $query->whereHas('subject', function ($subjectQuery) {
                $subjectQuery
                    ->where('active', true)
                    ->where('row_status', 'current');
            })->with('subject:id,title');
        }

        return $query->get()
            ->map(fn (GradeLevelSubject $gradeLevelSubject) => [
                'grade_level_subject_id' => $gradeLevelSubject->id,
                'subject_id' => (int) $gradeLevelSubject->subject_id,
                'subject_name' => self::subjectNameFromGradeLevelSubject($gradeLevelSubject),
                'student_status' => self::studentStatus((int) $gradeLevelSubject->subject_id),
                'teacher_status' => self::teacherStatus((int) $gradeLevelSubject->subject_id),
            ])
            ->values()
            ->all();
    }

    public static function missingRequiredActiveGradeLevelSubjects(?int $gradeLevelId): array
    {
        if (! $gradeLevelId) {
            return [];
        }

        if (! Schema::hasTable('grade_level_subjects')) {
            return [];
        }

        $requiredSubjectIds = self::activeByDefaultSubjectIds();

        $existingSubjectIds = GradeLevelSubject::query()
            ->where('grade_level_id', $gradeLevelId)
            ->where('status', 'active')
            ->whereIn('subject_id', $requiredSubjectIds)
            ->when(Schema::hasTable('subjects'), function ($query) {
                $query->whereHas('subject', function ($subjectQuery) {
                    $subjectQuery
                        ->where('active', true)
                        ->where('row_status', 'current');
                });
            })
            ->pluck('subject_id')
            ->map(fn ($subjectId) => (int) $subjectId)
            ->all();

        return collect($requiredSubjectIds)
            ->reject(fn (int $subjectId) => in_array($subjectId, $existingSubjectIds, true))
            ->map(fn (int $subjectId) => self::subjectName($subjectId))
            ->values()
            ->all();
    }

    public static function activeByDefaultSubjectIds(): array
    {
        return [
            self::SUBJECT_LANGUAGE_AND_LITERATURE,
            self::SUBJECT_WELL_BEING,
        ];
    }

    public static function subjectName(int $subjectId): string
    {
        return match ($subjectId) {
            self::SUBJECT_LANGUAGE_AND_LITERATURE => 'Language and Literature',
            self::SUBJECT_MATH => 'Math',
            self::SUBJECT_WELL_BEING => 'Well Being',
            default => 'Subject '.$subjectId,
        };
    }

    public static function displaySubjectName(int $subjectId, ?string $storedTitle = null): string
    {
        if ($subjectId === self::SUBJECT_LANGUAGE_AND_LITERATURE) {
            return self::subjectName($subjectId);
        }

        return filled($storedTitle)
            ? (string) $storedTitle
            : self::subjectName($subjectId);
    }

    public static function displaySubjectShortName(int $subjectId, ?string $storedTitle = null): string
    {
        if ($subjectId === self::SUBJECT_LANGUAGE_AND_LITERATURE) {
            return 'L&L';
        }

        return self::displaySubjectName($subjectId, $storedTitle);
    }

    public static function displayPayloadForStudentSubject(StudentsSubject $studentSubject): array
    {
        $gradeLevelSubject = $studentSubject->gradeLevelSubject;
        $subject = $gradeLevelSubject?->subject;
        $subjectId = (int) ($subject?->id ?? $gradeLevelSubject?->subject_id ?? 0);

        return self::displayPayloadForSubject($subjectId, $subject?->title);
    }

    public static function displayPayloadForSubject(int $subjectId, ?string $storedTitle = null): array
    {
        return [
            'id' => $subjectId,
            'title' => self::displaySubjectName($subjectId, $storedTitle),
            'visual' => self::subjectVisual($subjectId, $storedTitle),
        ];
    }

    public static function syncTeacherSubjectClassStatus(?int $classSubjectId): void
    {
        if (! $classSubjectId) {
            return;
        }

        $hasActiveStudentSubject = StudentsSubject::query()
            ->where('class_subject_id', $classSubjectId)
            ->where('status', 'active')
            ->exists();

        TeacherSubjectClass::query()
            ->where('class_subject_id', $classSubjectId)
            ->update([
                'status' => $hasActiveStudentSubject ? 'active' : 'inactive',
                'assigned_at' => $hasActiveStudentSubject ? now() : null,
                'removed_at' => $hasActiveStudentSubject ? null : now(),
            ]);
    }

    protected static function studentStatus(int $subjectId): string
    {
        return in_array($subjectId, self::activeByDefaultSubjectIds(), true) ? 'active' : 'inactive';
    }

    protected static function teacherStatus(int $subjectId): string
    {
        return in_array($subjectId, self::activeByDefaultSubjectIds(), true) ? 'active' : 'inactive';
    }

    protected static function subjectNameFromGradeLevelSubject(GradeLevelSubject $gradeLevelSubject): string
    {
        $title = $gradeLevelSubject->relationLoaded('subject')
            ? $gradeLevelSubject->subject?->title
            : null;

        return filled($title)
            ? (string) $title
            : self::subjectName((int) $gradeLevelSubject->subject_id);
    }

    private static function subjectVisual(int $subjectId, ?string $storedTitle = null): array
    {
        $title = strtolower((string) $storedTitle);

        if ($subjectId === self::SUBJECT_LANGUAGE_AND_LITERATURE || str_contains($title, 'language') || str_contains($title, 'literature')) {
            return ['icon' => 'ti tabler-books', 'tone' => 'language'];
        }

        if ($subjectId === self::SUBJECT_WELL_BEING || str_contains($title, 'well')) {
            return ['icon' => 'ti tabler-heart-handshake', 'tone' => 'wellbeing'];
        }

        if ($subjectId === self::SUBJECT_MATH || str_contains($title, 'math')) {
            return ['icon' => 'ti tabler-percentage', 'tone' => 'math'];
        }

        if (str_contains($title, 'quran')) {
            return ['icon' => 'ti tabler-book-2', 'tone' => 'default'];
        }

        if (str_contains($title, 'science')) {
            return ['icon' => 'ti tabler-brand-react', 'tone' => 'science'];
        }

        if (str_contains($title, 'art') || str_contains($title, 'drama') || str_contains($title, 'music')) {
            return ['icon' => 'ti tabler-color-swatch', 'tone' => 'arts'];
        }

        if (str_contains($title, 'social') || str_contains($title, 'history') || str_contains($title, 'geography')) {
            return ['icon' => 'ti tabler-globe', 'tone' => 'humanities'];
        }

        return ['icon' => 'ti tabler-school', 'tone' => 'default'];
    }
}
