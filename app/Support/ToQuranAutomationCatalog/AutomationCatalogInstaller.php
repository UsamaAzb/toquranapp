<?php

namespace App\Support\ToQuranAutomationCatalog;

use App\Models\GeneralLibraryFolder;
use App\Models\MainDailySessionMainTask;
use App\Models\MainDailySessionTemplate;
use App\Models\MainDailySessionVersion;
use App\Models\MainDailySessionVersionTask;
use App\Models\SeriesTask;
use App\Models\SeriesTaskVersion;
use App\Models\SeriesTaskVersionItem;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use App\Models\User;
use App\Services\SeriesLibrarySourceResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class AutomationCatalogInstaller
{
    private const REGISTRY_TABLE = 'toquran_automation_catalog_entries';

    public function __construct(
        private readonly StarterAutomationCatalog $catalog,
        private readonly SeriesLibrarySourceResolver $seriesResolver,
    ) {}

    public function installForTeacher(User $teacher, bool $dryRun = true, ?array $onlyKeys = null): array
    {
        $entries = collect($this->catalog->entries())
            ->when($onlyKeys !== null && $onlyKeys !== [], fn ($items) => $items->whereIn('catalog_key', $onlyKeys))
            ->values();

        $result = $this->emptyResult($dryRun);

        if (! $dryRun && ! Schema::hasTable(self::REGISTRY_TABLE)) {
            throw new RuntimeException('Catalog registry table is missing. Run the reviewed manual SQL before installing starter automation data.');
        }

        foreach ($entries as $entry) {
            $outcome = $this->installEntry($teacher, $entry, $dryRun);
            $result['created'] += $outcome['created'];
            $result['updated'] += $outcome['updated'];
            $result['skipped'] += $outcome['skipped'];
            array_push($result['messages'], ...$outcome['messages']);
        }

        return $result;
    }

    public function previewForTeacher(User $teacher, ?array $onlyKeys = null): array
    {
        return $this->installForTeacher($teacher, true, $onlyKeys);
    }

    private function installEntry(User $teacher, array $entry, bool $dryRun): array
    {
        $result = $this->emptyResult($dryRun);
        $catalogKey = (string) $entry['catalog_key'];
        $type = (string) $entry['type'];
        $subject = $this->resolveSubject((string) $entry['subject_title']);

        if (! $subject instanceof Subject) {
            return $this->skip($result, $catalogKey, "Subject '{$entry['subject_title']}' is missing or inactive.");
        }

        if (! $this->teacherCanInstallForSubject($teacher, (int) $subject->id)) {
            return $this->skip($result, $catalogKey, "Teacher {$teacher->email} is not eligible for {$subject->title}.");
        }

        $taskTypeId = $this->resolveTaskTypeId((string) ($entry['task_type'] ?? 'Assignment'));

        if ($taskTypeId === null) {
            return $this->skip($result, $catalogKey, "Task type '{$entry['task_type']}' is missing.");
        }

        return match ($type) {
            'versioned_routine' => $this->installVersionedRoutine($teacher, $subject, $taskTypeId, $entry, $dryRun),
            'series_task' => $this->installSeriesTask($teacher, $subject, $taskTypeId, $entry, $dryRun),
            default => $this->skip($result, $catalogKey, "Unknown catalog entry type '{$type}'."),
        };
    }

    private function installVersionedRoutine(User $teacher, Subject $subject, int $taskTypeId, array $entry, bool $dryRun): array
    {
        $result = $this->emptyResult($dryRun);
        $catalogKey = (string) $entry['catalog_key'];
        $hash = $this->manifestHash($entry);

        if ($dryRun) {
            $exists = $this->registryTargetExists('versioned_routine', $catalogKey, 'root', 'root', (int) $teacher->id, (int) $subject->id);
            $result[$exists ? 'updated' : 'created']++;
            $result['messages'][] = ($exists ? 'Would verify/update' : 'Would create').' versioned routine '.$catalogKey.'.';

            return $result;
        }

        return DB::transaction(function () use ($teacher, $subject, $taskTypeId, $entry, $catalogKey, $hash): array {
            $result = $this->emptyResult(false);
            $root = $this->registryTarget('versioned_routine', $catalogKey, 'root', 'root', (int) $teacher->id, (int) $subject->id);

            if ($root instanceof MainDailySessionTemplate) {
                $this->assertVersionedRootMatches($root, $teacher, $subject, $catalogKey);
                $result['updated']++;
            } else {
                $recurrence = $entry['recurrence'] ?? [];
                $root = MainDailySessionTemplate::create([
                    'title' => $entry['title'],
                    'subject_id' => $subject->id,
                    'created_by_user_id' => $teacher->id,
                    'recurrence_kind' => $recurrence['kind'] ?? 'daily',
                    'recurrence_interval' => (int) ($recurrence['interval'] ?? 1),
                    'recurrence_weekdays' => null,
                    'recurrence_day_of_month' => null,
                    'status' => $entry['status'] ?? 'draft',
                ]);
                $this->recordRegistry('versioned_routine', $catalogKey, 'root', 'root', $root->getTable(), (int) $root->id, $teacher, $subject, $hash);
                $result['created']++;
            }

            foreach (array_values($entry['versions'] ?? []) as $index => $versionSpec) {
                $version = $this->upsertVersionedRoutineVersion($root, $teacher, $subject, $catalogKey, $versionSpec, $index + 1, $hash, $result);

                foreach (array_values($entry['tasks'] ?? []) as $taskIndex => $taskSpec) {
                    $task = $this->upsertVersionedRoutineTask($root, $teacher, $subject, $taskTypeId, $catalogKey, $taskSpec, $taskIndex + 1, $hash, $result);
                    $this->upsertVersionedRoutineTaskLink($version, $task, $teacher, $subject, $catalogKey, $versionSpec, $taskSpec, $taskIndex + 1, $hash, $result);
                }
            }

            $result['messages'][] = 'Installed versioned routine '.$catalogKey.'.';

            return $result;
        });
    }

    private function installSeriesTask(User $teacher, Subject $subject, int $taskTypeId, array $entry, bool $dryRun): array
    {
        $result = $this->emptyResult($dryRun);
        $catalogKey = (string) $entry['catalog_key'];
        $folder = $this->resolveGeneralLibraryFolderPath($entry['library_folder_path'] ?? []);

        if (! $folder instanceof GeneralLibraryFolder) {
            return $this->skip($result, $catalogKey, 'Shared Library folder path was not found: '.implode(' / ', $entry['library_folder_path'] ?? []));
        }

        if (! $this->seriesResolver->sourceIsSelectableForSeriesLaunch(
            SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER,
            (int) $folder->id,
            (int) $teacher->id,
            (int) $subject->id
        )) {
            return $this->skip($result, $catalogKey, 'Shared Library folder is not selectable for Series launch: '.implode(' / ', $entry['library_folder_path'] ?? []));
        }

        $items = collect($this->seriesResolver->orderedItems(
            SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER,
            (int) $folder->id,
            (int) $teacher->id,
            (int) $subject->id,
        ));

        if ($items->isEmpty()) {
            return $this->skip($result, $catalogKey, 'Shared Library folder has no active resources: '.implode(' / ', $entry['library_folder_path'] ?? []));
        }

        if ($dryRun) {
            $exists = $this->registryTargetExists('series_task', $catalogKey, 'root', 'root', (int) $teacher->id, (int) $subject->id);
            $result[$exists ? 'updated' : 'created']++;
            $result['messages'][] = ($exists ? 'Would verify/update' : 'Would create').' series task '.$catalogKey.' with '.$items->count().' source items.';

            return $result;
        }

        return DB::transaction(function () use ($teacher, $subject, $taskTypeId, $entry, $catalogKey, $folder, $items): array {
            $result = $this->emptyResult(false);
            $hash = $this->manifestHash($entry);
            $root = $this->registryTarget('series_task', $catalogKey, 'root', 'root', (int) $teacher->id, (int) $subject->id);

            if ($root instanceof SeriesTask) {
                $this->assertSeriesRootMatches($root, $teacher, $subject, $catalogKey);
                $result['updated']++;
            } else {
                $recurrence = $entry['recurrence'] ?? [];
                $root = SeriesTask::create([
                    'subject_id' => $subject->id,
                    'created_by_user_id' => $teacher->id,
                    'task_type_id' => $taskTypeId,
                    'title' => $entry['title'],
                    'description' => $entry['description'] ?? null,
                    'library_collection_type' => SeriesLibrarySourceResolver::TYPE_GENERAL_LIBRARY_FOLDER,
                    'library_collection_id' => $folder->id,
                    'recurrence_kind' => $recurrence['kind'] ?? 'daily',
                    'recurrence_interval' => (int) ($recurrence['interval'] ?? 1),
                    'sequence_behavior' => $entry['sequence_behavior'] ?? 'stop_at_end',
                    'release_policy' => $entry['release_policy'] ?? 'continuous',
                    'default_points' => (int) ($entry['default_points'] ?? 5),
                    'max_points' => (int) ($entry['max_points'] ?? 10),
                    'sort_order' => 0,
                    'status' => $entry['status'] ?? 'draft',
                    'published_at' => null,
                ]);
                $this->recordRegistry('series_task', $catalogKey, 'root', 'root', $root->getTable(), (int) $root->id, $teacher, $subject, $hash);
                $result['created']++;
            }

            foreach (array_values($entry['versions'] ?? []) as $index => $versionSpec) {
                $version = $this->upsertSeriesVersion($root, $teacher, $subject, $catalogKey, $versionSpec, $index + 1, $hash, $result);

                foreach ($items->values() as $itemIndex => $item) {
                    $this->upsertSeriesItem($version, $teacher, $subject, $catalogKey, $versionSpec, $item, $itemIndex + 1, $hash, $result);
                }
            }

            $result['messages'][] = 'Installed series task '.$catalogKey.'.';

            return $result;
        });
    }

    private function upsertVersionedRoutineVersion(
        MainDailySessionTemplate $root,
        User $teacher,
        Subject $subject,
        string $catalogKey,
        array $versionSpec,
        int $sortOrder,
        string $hash,
        array &$result
    ): MainDailySessionVersion {
        $entryKey = (string) $versionSpec['key'];
        $version = $this->registryTarget('versioned_routine', $catalogKey, 'version', $entryKey, (int) $teacher->id, (int) $subject->id);

        if ($version instanceof MainDailySessionVersion) {
            if ((int) $version->main_daily_session_template_id !== (int) $root->id) {
                throw new RuntimeException("Registry mismatch for {$catalogKey} version {$entryKey}.");
            }
            $version->update(['sort_order' => $sortOrder]);
            $result['updated']++;

            return $version;
        }

        $version = MainDailySessionVersion::create([
            'main_daily_session_template_id' => $root->id,
            'display_name' => $versionSpec['display_name'],
            'sort_order' => $sortOrder,
        ]);
        $this->recordRegistry('versioned_routine', $catalogKey, 'version', $entryKey, $version->getTable(), (int) $version->id, $teacher, $subject, $hash);
        $result['created']++;

        return $version;
    }

    private function upsertVersionedRoutineTask(
        MainDailySessionTemplate $root,
        User $teacher,
        Subject $subject,
        int $taskTypeId,
        string $catalogKey,
        array $taskSpec,
        int $sortOrder,
        string $hash,
        array &$result
    ): MainDailySessionMainTask {
        $entryKey = (string) $taskSpec['key'];
        $task = $this->registryTarget('versioned_routine', $catalogKey, 'task', $entryKey, (int) $teacher->id, (int) $subject->id);

        if ($task instanceof MainDailySessionMainTask) {
            if ((int) $task->main_daily_session_template_id !== (int) $root->id) {
                throw new RuntimeException("Registry mismatch for {$catalogKey} task {$entryKey}.");
            }
            $task->update(['sort_order' => $sortOrder]);
            $result['updated']++;

            return $task;
        }

        $task = MainDailySessionMainTask::create([
            'main_daily_session_template_id' => $root->id,
            'title' => $taskSpec['title'],
            'description' => $taskSpec['description'] ?? null,
            'task_type_id' => $taskTypeId,
            'default_points' => (int) ($taskSpec['default_points'] ?? 5),
            'max_points' => (int) ($taskSpec['max_points'] ?? 10),
            'sort_order' => $sortOrder,
        ]);
        $this->recordRegistry('versioned_routine', $catalogKey, 'task', $entryKey, $task->getTable(), (int) $task->id, $teacher, $subject, $hash);
        $result['created']++;

        return $task;
    }

    private function upsertVersionedRoutineTaskLink(
        MainDailySessionVersion $version,
        MainDailySessionMainTask $task,
        User $teacher,
        Subject $subject,
        string $catalogKey,
        array $versionSpec,
        array $taskSpec,
        int $sortOrder,
        string $hash,
        array &$result
    ): MainDailySessionVersionTask {
        $entryKey = $versionSpec['key'].':'.$taskSpec['key'];
        $link = $this->registryTarget('versioned_routine', $catalogKey, 'version_task', $entryKey, (int) $teacher->id, (int) $subject->id);

        if ($link instanceof MainDailySessionVersionTask) {
            if ((int) $link->version_id !== (int) $version->id || (int) $link->main_task_id !== (int) $task->id) {
                throw new RuntimeException("Registry mismatch for {$catalogKey} version task {$entryKey}.");
            }
            $link->update(['sort_order' => $sortOrder]);
            $result['updated']++;

            return $link;
        }

        $versionDescriptions = $taskSpec['version_descriptions'] ?? [];
        $link = MainDailySessionVersionTask::create([
            'version_id' => $version->id,
            'main_task_id' => $task->id,
            'description_override' => $versionDescriptions[$versionSpec['key']] ?? ($taskSpec['description'] ?? null),
            'sort_order' => $sortOrder,
        ]);
        $this->recordRegistry('versioned_routine', $catalogKey, 'version_task', $entryKey, $link->getTable(), (int) $link->id, $teacher, $subject, $hash);
        $result['created']++;

        return $link;
    }

    private function upsertSeriesVersion(
        SeriesTask $root,
        User $teacher,
        Subject $subject,
        string $catalogKey,
        array $versionSpec,
        int $sortOrder,
        string $hash,
        array &$result
    ): SeriesTaskVersion {
        $entryKey = (string) $versionSpec['key'];
        $version = $this->registryTarget('series_task', $catalogKey, 'version', $entryKey, (int) $teacher->id, (int) $subject->id);

        if ($version instanceof SeriesTaskVersion) {
            if ((int) $version->series_task_id !== (int) $root->id) {
                throw new RuntimeException("Registry mismatch for {$catalogKey} series version {$entryKey}.");
            }
            $version->update(['sort_order' => $sortOrder]);
            $result['updated']++;

            return $version;
        }

        $version = SeriesTaskVersion::create([
            'series_task_id' => $root->id,
            'display_name' => $versionSpec['display_name'],
            'description' => $versionSpec['description'] ?? null,
            'sort_order' => $sortOrder,
        ]);
        $this->recordRegistry('series_task', $catalogKey, 'version', $entryKey, $version->getTable(), (int) $version->id, $teacher, $subject, $hash);
        $result['created']++;

        return $version;
    }

    private function upsertSeriesItem(
        SeriesTaskVersion $version,
        User $teacher,
        Subject $subject,
        string $catalogKey,
        array $versionSpec,
        object $item,
        int $position,
        string $hash,
        array &$result
    ): SeriesTaskVersionItem {
        $entryKey = $versionSpec['key'].':'.$item->sourceType.':'.$item->sourceId;
        $versionItem = $this->registryTarget('series_task', $catalogKey, 'item', $entryKey, (int) $teacher->id, (int) $subject->id);

        if ($versionItem instanceof SeriesTaskVersionItem) {
            if ((int) $versionItem->version_id !== (int) $version->id) {
                throw new RuntimeException("Registry mismatch for {$catalogKey} series item {$entryKey}.");
            }
            $versionItem->update(['sequence_position' => $position]);
            $result['updated']++;

            return $versionItem;
        }

        $versionItem = SeriesTaskVersionItem::create([
            'version_id' => $version->id,
            'library_source_type' => $item->sourceType,
            'library_source_id' => $item->sourceId,
            'library_title_snapshot' => $item->title,
            'library_url_snapshot' => $item->url,
            'library_summary_snapshot' => $item->summary,
            'sequence_position' => $position,
            'is_active' => true,
        ]);
        $this->recordRegistry('series_task', $catalogKey, 'item', $entryKey, $versionItem->getTable(), (int) $versionItem->id, $teacher, $subject, $hash);
        $result['created']++;

        return $versionItem;
    }

    private function registryTarget(string $type, string $catalogKey, string $scope, string $entryKey, int $teacherId, int $subjectId): object|null
    {
        if (! Schema::hasTable(self::REGISTRY_TABLE)) {
            return null;
        }

        $row = DB::table(self::REGISTRY_TABLE)
            ->where([
                'automation_type' => $type,
                'catalog_key' => $catalogKey,
                'entry_scope' => $scope,
                'entry_key' => $entryKey,
                'teacher_user_id' => $teacherId,
                'subject_id' => $subjectId,
            ])
            ->first();

        if (! $row) {
            return null;
        }

        return match ($row->target_table) {
            'main_daily_session_templates' => MainDailySessionTemplate::find($row->target_id),
            'main_daily_session_versions' => MainDailySessionVersion::find($row->target_id),
            'main_daily_session_main_tasks' => MainDailySessionMainTask::find($row->target_id),
            'main_daily_session_version_tasks' => MainDailySessionVersionTask::find($row->target_id),
            'series_tasks' => SeriesTask::find($row->target_id),
            'series_task_versions' => SeriesTaskVersion::find($row->target_id),
            'series_task_version_items' => SeriesTaskVersionItem::find($row->target_id),
            default => throw new RuntimeException("Unsupported catalog registry target table {$row->target_table}."),
        };
    }

    private function registryTargetExists(string $type, string $catalogKey, string $scope, string $entryKey, int $teacherId, int $subjectId): bool
    {
        return $this->registryTarget($type, $catalogKey, $scope, $entryKey, $teacherId, $subjectId) !== null;
    }

    private function recordRegistry(
        string $type,
        string $catalogKey,
        string $scope,
        string $entryKey,
        string $targetTable,
        int $targetId,
        User $teacher,
        Subject $subject,
        string $hash
    ): void {
        $identity = [
            'automation_type' => $type,
            'catalog_key' => $catalogKey,
            'entry_scope' => $scope,
            'entry_key' => $entryKey,
            'teacher_user_id' => $teacher->id,
            'subject_id' => $subject->id,
        ];
        $values = [
            'target_table' => $targetTable,
            'target_id' => $targetId,
            'installed_version' => StarterAutomationCatalog::VERSION,
            'manifest_hash' => $hash,
            'updated_at' => now(),
        ];
        $query = DB::table(self::REGISTRY_TABLE)->where($identity);

        if ($query->exists()) {
            $query->update($values);

            return;
        }

        DB::table(self::REGISTRY_TABLE)->insert(array_merge($identity, $values, [
            'created_at' => now(),
        ]));
    }

    private function resolveSubject(string $title): ?Subject
    {
        return Subject::query()
            ->where('title', $title)
            ->where(function ($query): void {
                $query->where('active', 1)
                    ->orWhereNull('active');
            })
            ->first();
    }

    private function resolveTaskTypeId(string $title): ?int
    {
        $id = DB::table('task_types')->where('title', $title)->value('id');

        return $id === null ? null : (int) $id;
    }

    private function teacherCanInstallForSubject(User $teacher, int $subjectId): bool
    {
        if (! $teacher->hasRole('teacher')) {
            return false;
        }

        if (filled($teacher->status) && $teacher->status !== 'active') {
            return false;
        }

        return TeacherSubjectClass::query()
            ->where('user_teacher_coteacher_id', $teacher->id)
            ->where('subject_id', $subjectId)
            ->availableForTeacher()
            ->exists();
    }

    private function resolveGeneralLibraryFolderPath(array $path): ?GeneralLibraryFolder
    {
        $parentId = null;
        $folder = null;

        foreach ($path as $title) {
            $matches = GeneralLibraryFolder::query()
                ->when(
                    $parentId === null,
                    fn ($query) => $query->whereNull('parent_id'),
                    fn ($query) => $query->where('parent_id', $parentId)
                )
                ->where('title', $title)
                ->where('status', GeneralLibraryFolder::STATUS_ACTIVE)
                ->get();

            if ($matches->count() !== 1) {
                return null;
            }

            $folder = $matches->first();
            $parentId = (int) $folder->id;
        }

        return $folder;
    }

    private function assertVersionedRootMatches(MainDailySessionTemplate $root, User $teacher, Subject $subject, string $catalogKey): void
    {
        if ((int) $root->created_by_user_id !== (int) $teacher->id || (int) $root->subject_id !== (int) $subject->id) {
            throw new RuntimeException("Registry mismatch for {$catalogKey} versioned routine root.");
        }
    }

    private function assertSeriesRootMatches(SeriesTask $root, User $teacher, Subject $subject, string $catalogKey): void
    {
        if ((int) $root->created_by_user_id !== (int) $teacher->id || (int) $root->subject_id !== (int) $subject->id) {
            throw new RuntimeException("Registry mismatch for {$catalogKey} series task root.");
        }
    }

    private function manifestHash(array $entry): string
    {
        return hash('sha256', json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    private function emptyResult(bool $dryRun): array
    {
        return [
            'dry_run' => $dryRun,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'messages' => [],
        ];
    }

    private function skip(array $result, string $catalogKey, string $reason): array
    {
        $result['skipped']++;
        $result['messages'][] = "Skipped {$catalogKey}: {$reason}";

        return $result;
    }
}
