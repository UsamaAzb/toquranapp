<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PublishDailySessionsMidnight extends Command
{
    protected $signature = 'daily:publish-midnight';

    protected $description = 'Publish Automated Task snapshots for all active subscriptions';

    public function handle(): int
    {
        app(\App\Services\DailyMidnightPublisher::class)->publishForToday();

        $this->info('Daily sessions published.');

        return self::SUCCESS;
    }
}
