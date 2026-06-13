<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupGeneralLibraryTempUploads extends Command
{
    protected $signature = 'library:cleanup-temp-uploads {--hours=24 : Delete staged Library uploads older than this many hours}';

    protected $description = 'Delete stale staged files from the shared Library upload temp directory.';

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $cutoff = now()->subHours($hours)->getTimestamp();
        $disk = Storage::disk('local');
        $deleted = 0;

        foreach ($disk->allFiles('general-library-temp') as $path) {
            if ($disk->lastModified($path) > $cutoff) {
                continue;
            }

            if ($disk->delete($path)) {
                $deleted++;
            } else {
                $this->warn("Failed to delete: {$path}");
            }
        }

        foreach (array_reverse($disk->allDirectories('general-library-temp')) as $directory) {
            if ($disk->files($directory) === [] && $disk->directories($directory) === []) {
                $disk->deleteDirectory($directory);
            }
        }

        $this->info("Deleted {$deleted} stale Library upload file(s).");

        return self::SUCCESS;
    }
}
