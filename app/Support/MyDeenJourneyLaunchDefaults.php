<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\DisciplineIcon;
use App\Models\PunishmentAgreement;
use App\Models\PunishmentsSuggestion;
use App\Models\RewardDisciplinePoint;
use App\Models\RewardDisciplineTransfer;
use App\Models\Student;
use App\Models\StudentGift;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MyDeenJourneyLaunchDefaults
{
    private const DEFAULT_DISCIPLINE_ICON_PATH = 'images/discipline/respect.png';

    private const DISCIPLINE_ICON_PATHS_BY_TITLE = [
        'Good Job' => 'images/discipline/34-ud0vRyQq.png',
        'Good Effort' => 'images/discipline/74-Dizvjp7n.png',
        'Focused' => 'images/discipline/20-BPaZ4Ete.png',
        'Good Adab' => 'images/discipline/respect.png',
        'Honesty' => 'images/discipline/35-CxgcOsNl.png',
        'Responsibility' => 'images/discipline/61-DWTOj_T6.png',
        'Self-Control' => 'images/discipline/71-Ey5tyt2G.png',
        'Helping Others' => 'images/discipline/shakehands.png',
        'Good Deed' => 'images/discipline/67-DlneWycG.png',
        'Good Question' => 'images/discipline/59-DctAzBtq.png',
        'On Time' => 'images/discipline/clock.png',
        'Oops!' => 'images/discipline/42-CcVNxBRq.png',
        'Not Ready' => 'images/discipline/26-coCa5JE0.png',
        'Distracted' => 'images/discipline/51-CZkeNwpv.png',
        'Time Wasted' => 'images/discipline/clock.png',
        'Task Not Done' => 'images/discipline/61-DWTOj_T6.png',
        'Low Practice' => 'images/discipline/leafpng.png',
        'Adab Slip' => 'images/discipline/73-tSz4ujTS.png',
        'Device Slip' => 'images/discipline/63-C0dY3Flz.png',
        'Small Excuse' => 'images/discipline/59-DctAzBtq.png',
        'No Response' => 'images/discipline/micopng.png',
        'Rule Reminder' => 'images/discipline/40-CVyPO1Sf.png',
        'Serious Matter' => 'images/discipline/41-D3kTTAuf.png',
        'Hurtful Words' => 'images/discipline/43-D4EMnrNR.png',
        'Dishonesty' => 'images/discipline/35-CxgcOsNl.png',
        'Cheating' => 'images/discipline/21-DWkgiIWq.png',
        'Bullying' => 'images/discipline/50-DbUUee_w.png',
        'Aggression' => 'images/discipline/55-DT6MykPZ.png',
        'Major Disrespect' => 'images/discipline/50-DbUUee_w.png',
        'Device Misuse' => 'images/discipline/63-C0dY3Flz.png',
        'Rule Broken' => 'images/discipline/40-CVyPO1Sf.png',
    ];

    private const POPUP_BEHAVIOR_CATEGORIES = [
        'Positive' => ['Good Job'],
        'Slip' => ['Oops!'],
        'No Way' => ['Serious Matter'],
    ];

    public function ensureRewardQueue(int $studentId, ?int $academicYearId = null): void
    {
        if (! Schema::hasTable('students') || ! Schema::hasTable('student_gifts')) {
            return;
        }

        if ($academicYearId === null && ! Schema::hasTable('academic_years')) {
            return;
        }

        $academicYearId ??= AcademicYear::currentId();

        if (! Student::query()->whereKey($studentId)->exists()) {
            return;
        }

        if ($this->hasCurrentYearGifts($studentId, $academicYearId)) {
            return;
        }

        try {
            DB::transaction(function () use ($studentId, $academicYearId): void {
                Student::query()
                    ->whereKey($studentId)
                    ->lockForUpdate()
                    ->first();

                if (! $this->hasCurrentYearGifts($studentId, $academicYearId)) {
                    StudentGift::maintainUpcomingRunway($studentId, $academicYearId);
                }
            });
        } catch (QueryException $exception) {
            if (! $this->isUniqueConstraintViolation($exception)) {
                throw $exception;
            }

            StudentGift::requeueForStudent($studentId, $academicYearId);
        }
    }

    private function hasCurrentYearGifts(int $studentId, int $academicYearId): bool
    {
        return StudentGift::query()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->exists();
    }

    public function ensureBehaviorTemplates(int $studentId): void
    {
        if (! Schema::hasTable('students') || ! Schema::hasTable('reward_discipline_points')) {
            return;
        }

        if (! Student::query()->whereKey($studentId)->exists()) {
            return;
        }

        if (
            ! $this->needsBehaviorTemplates($studentId)
            && ! $this->needsBehaviorIconHeal($studentId)
            && ! $this->needsLaunchBehaviorIconMapping($studentId)
            && ! $this->needsLaunchBehaviorPopupFlagHeal($studentId)
            && ! $this->needsAgreementTemplates($studentId)
        ) {
            return;
        }

        try {
            DB::transaction(function () use ($studentId): void {
                Student::query()
                    ->whereKey($studentId)
                    ->lockForUpdate()
                    ->first();

                $defaultIcon = $this->defaultDisciplineIcon();

                $this->seedBehaviorTemplates($studentId, $defaultIcon);
                $this->healBehaviorIcons($studentId, $defaultIcon);
                $this->healLaunchBehaviorIconMappings($studentId, $defaultIcon);
                $this->healLaunchBehaviorPopupFlags($studentId);
                $this->seedAgreementTemplates($studentId);
            });
        } catch (QueryException $exception) {
            if (! $this->isUniqueConstraintViolation($exception)) {
                throw $exception;
            }
        }
    }

    private function seedBehaviorTemplates(int $studentId, ?DisciplineIcon $defaultIcon): void
    {
        if (
            Schema::hasTable('reward_discipline_transfer')
            && ! RewardDisciplinePoint::query()->where('student_id', $studentId)->exists()
        ) {
            RewardDisciplineTransfer::active()
                ->orderByRaw('COALESCE(sort, 999999) asc')
                ->orderBy('id')
                ->get()
                ->each(function (RewardDisciplineTransfer $reward) use ($studentId, $defaultIcon): void {
                    $mappedIcon = $this->disciplineIconForBehavior($reward->title) ?? $defaultIcon;

                    RewardDisciplinePoint::firstOrCreate(
                        [
                            'student_id' => $studentId,
                            'title' => $reward->title,
                            'status' => 'active',
                        ],
                        [
                            'description' => $reward->description,
                            'type' => $reward->type,
                            'points' => $reward->points,
                            'discipline_icon_id' => $reward->discipline_icon_id ?: $mappedIcon?->id,
                            'discipline_icon_path' => $reward->discipline_icon_path ?: $mappedIcon?->path,
                            'sort' => $reward->sort,
                            'teacher_desc' => $this->isPopupBehaviorCategory($reward->title, $reward->type)
                                ? 1
                                : $reward->teacher_desc,
                            'selected' => $reward->selected,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                });
        }
    }

    private function healLaunchBehaviorPopupFlags(int $studentId): void
    {
        foreach (self::POPUP_BEHAVIOR_CATEGORIES as $type => $titles) {
            RewardDisciplinePoint::query()
                ->where('student_id', $studentId)
                ->where('type', $type)
                ->whereIn('title', $titles)
                ->where('teacher_desc', '<>', 1)
                ->update([
                    'teacher_desc' => 1,
                    'updated_at' => now(),
                ]);
        }
    }

    private function healLaunchBehaviorIconMappings(int $studentId, ?DisciplineIcon $defaultIcon): void
    {
        foreach (self::DISCIPLINE_ICON_PATHS_BY_TITLE as $title => $path) {
            $icon = $this->disciplineIconForBehavior($title) ?? $defaultIcon;

            if (! $icon) {
                continue;
            }

            RewardDisciplinePoint::query()
                ->where('student_id', $studentId)
                ->where('title', $title)
                ->where(function ($query) use ($path): void {
                    $query->whereNull('discipline_icon_id')
                        ->orWhereNull('discipline_icon_path')
                        ->orWhere('discipline_icon_path', '')
                        ->orWhere('discipline_icon_path', '<>', $path);
                })
                ->update([
                    'discipline_icon_id' => $icon->id,
                    'discipline_icon_path' => $icon->path,
                    'updated_at' => now(),
                ]);
        }
    }

    private function healBehaviorIcons(int $studentId, ?DisciplineIcon $defaultIcon): void
    {
        if ($defaultIcon) {
            RewardDisciplinePoint::query()
                ->where('student_id', $studentId)
                ->where(function ($query): void {
                    $query->whereNull('discipline_icon_id')
                        ->orWhereNull('discipline_icon_path')
                        ->orWhere('discipline_icon_path', '');
                })
                ->update([
                    'discipline_icon_id' => $defaultIcon->id,
                    'discipline_icon_path' => $defaultIcon->path,
                    'updated_at' => now(),
                ]);
        }
    }

    private function seedAgreementTemplates(int $studentId): void
    {
        if (
            Schema::hasTable('punishment_agreements')
            && Schema::hasTable('punishments_suggestions')
            && ! PunishmentAgreement::query()->where('student_id', $studentId)->exists()
        ) {
            PunishmentsSuggestion::query()
                ->orderBy('id')
                ->get()
                ->each(function (PunishmentsSuggestion $punishment) use ($studentId): void {
                    PunishmentAgreement::firstOrCreate(
                        [
                            'student_id' => $studentId,
                            'punishment_type_id' => $punishment->punishment_type_id,
                            'title' => $punishment->suggestion_text,
                        ],
                        [
                            'status' => 'active',
                        ]
                    );
                });
        }
    }

    private function needsBehaviorTemplates(int $studentId): bool
    {
        return Schema::hasTable('reward_discipline_transfer')
            && RewardDisciplineTransfer::active()->exists()
            && ! RewardDisciplinePoint::query()->where('student_id', $studentId)->exists();
    }

    private function needsBehaviorIconHeal(int $studentId): bool
    {
        return RewardDisciplinePoint::query()
            ->where('student_id', $studentId)
            ->where(function ($query): void {
                $query->whereNull('discipline_icon_id')
                    ->orWhereNull('discipline_icon_path')
                    ->orWhere('discipline_icon_path', '');
            })
            ->exists();
    }

    private function needsLaunchBehaviorIconMapping(int $studentId): bool
    {
        $rows = RewardDisciplinePoint::query()
            ->where('student_id', $studentId)
            ->whereIn('title', array_keys(self::DISCIPLINE_ICON_PATHS_BY_TITLE))
            ->get(['title', 'discipline_icon_id', 'discipline_icon_path']);

        foreach ($rows as $row) {
            $expectedPath = self::DISCIPLINE_ICON_PATHS_BY_TITLE[$row->title] ?? null;

            if (
                $expectedPath
                && (
                    ! $row->discipline_icon_id
                    || ! $row->discipline_icon_path
                    || $row->discipline_icon_path !== $expectedPath
                )
            ) {
                return true;
            }
        }

        return false;
    }

    private function needsLaunchBehaviorPopupFlagHeal(int $studentId): bool
    {
        return RewardDisciplinePoint::query()
            ->where('student_id', $studentId)
            ->where('teacher_desc', '<>', 1)
            ->where(function ($query): void {
                foreach (self::POPUP_BEHAVIOR_CATEGORIES as $type => $titles) {
                    $query->orWhere(function ($typeQuery) use ($type, $titles): void {
                        $typeQuery->where('type', $type)
                            ->whereIn('title', $titles);
                    });
                }
            })
            ->exists();
    }

    private function needsAgreementTemplates(int $studentId): bool
    {
        return Schema::hasTable('punishment_agreements')
            && Schema::hasTable('punishments_suggestions')
            && PunishmentsSuggestion::query()->exists()
            && ! PunishmentAgreement::query()->where('student_id', $studentId)->exists();
    }

    public function defaultDisciplineIcon(): ?DisciplineIcon
    {
        if (! Schema::hasTable('discipline_icons')) {
            return null;
        }

        return DisciplineIcon::query()->firstOrCreate([
            'path' => self::DEFAULT_DISCIPLINE_ICON_PATH,
        ]);
    }

    private function disciplineIconForBehavior(string $title): ?DisciplineIcon
    {
        if (! Schema::hasTable('discipline_icons')) {
            return null;
        }

        $path = self::DISCIPLINE_ICON_PATHS_BY_TITLE[$title] ?? self::DEFAULT_DISCIPLINE_ICON_PATH;

        return DisciplineIcon::query()->firstOrCreate([
            'path' => $path,
        ]);
    }

    private function isPopupBehaviorCategory(string $title, string $type): bool
    {
        return in_array($title, self::POPUP_BEHAVIOR_CATEGORIES[$type] ?? [], true);
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        return ($exception->errorInfo[0] ?? null) === '23000'
            && (int) ($exception->errorInfo[1] ?? 0) === 1062;
    }
}
