<?php

namespace App\Console\Commands;

use App\Services\StudentTaskApprovalService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class AutoApproveTrustedChildTasks extends Command
{
    protected $signature = 'tasks:auto-approve-trusted-children {--limit=100}';

    protected $description = 'Auto-approve due in-review tasks snapshotted from trusted-child settings.';

    public function handle(StudentTaskApprovalService $service): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $dueBefore = CarbonImmutable::now(config('app.timezone'));

        $result = $service->autoApproveTrustedChildTasks($dueBefore, $limit);

        $this->info("Trusted child task auto-approval approved {$result['approved']} and skipped {$result['skipped']}.");

        return self::SUCCESS;
    }
}
