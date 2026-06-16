<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\ToQuranAutomationCatalog\AutomationCatalogInstaller;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class InstallToQuranAutomationCatalog extends Command
{
    protected $signature = 'toquran:install-automation-catalog
        {--teacher-email= : Install for one teacher user}
        {--all-active-teachers : Install for every active teacher}
        {--confirm-all-active-teachers : Required for writes with --all-active-teachers}
        {--only=* : Optional catalog key filter}
        {--dry-run : Preview without writing rows}
        {--confirm-db= : Required for writes; must match the active database name}';

    protected $description = 'Install the To Quran starter automation catalog for eligible teachers.';

    public function handle(AutomationCatalogInstaller $installer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $database = DB::connection()->getDatabaseName();
        $confirmDb = (string) $this->option('confirm-db');

        if (! $dryRun) {
            if (! is_string($database) || trim($database) === '') {
                $this->error('Could not determine the active database name. Aborting to prevent unsafe writes.');

                return self::FAILURE;
            }

            $database = trim($database);

            if ($confirmDb !== $database) {
                $this->error("Refusing to write starter automation data. Pass --confirm-db={$database} after backup/target checks.");

                return self::FAILURE;
            }
        }

        if (! $dryRun && (bool) $this->option('all-active-teachers') && ! (bool) $this->option('confirm-all-active-teachers')) {
            $this->error('Refusing all-teacher starter automation write without --confirm-all-active-teachers.');

            return self::FAILURE;
        }

        $teachers = $this->resolveTeachers();

        if ($teachers->isEmpty()) {
            $this->error('No teacher users matched the requested installer target.');

            return self::FAILURE;
        }

        $only = array_values(array_filter((array) $this->option('only')));
        $aggregate = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        foreach ($teachers as $teacher) {
            $this->line(($dryRun ? '[dry-run] ' : '').'Installing catalog for '.$teacher->email);
            $result = $installer->installForTeacher($teacher, $dryRun, $only === [] ? null : $only);

            foreach ($result['messages'] as $message) {
                $this->line('  - '.$message);
            }

            $aggregate['created'] += $result['created'];
            $aggregate['updated'] += $result['updated'];
            $aggregate['skipped'] += $result['skipped'];
        }

        $this->info(sprintf(
            'Catalog %s complete: %d created, %d updated, %d skipped.',
            $dryRun ? 'dry-run' : 'install',
            $aggregate['created'],
            $aggregate['updated'],
            $aggregate['skipped']
        ));

        return self::SUCCESS;
    }

    private function resolveTeachers(): Collection
    {
        $teacherEmail = $this->option('teacher-email');
        $allActiveTeachers = (bool) $this->option('all-active-teachers');

        if (filled($teacherEmail) === $allActiveTeachers) {
            $this->error('Choose exactly one installer target: --teacher-email=... or --all-active-teachers.');

            return collect();
        }

        $query = User::role('teacher')
            ->where(function (Builder $query): void {
                $query->where('status', 'active')
                    ->orWhereNull('status');
            })
            ->orderBy('email');

        if (filled($teacherEmail)) {
            return $query->where('email', $teacherEmail)->get();
        }

        return $query->get();
    }
}
