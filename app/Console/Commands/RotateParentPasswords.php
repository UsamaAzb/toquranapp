<?php

namespace App\Console\Commands;

use App\Enums\AccountHistoryEventType;
use App\Models\User;
use App\Services\AccountHistoryService;
use App\Services\CredentialService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RotateParentPasswords extends Command
{
    protected $signature = 'parents:rotate-passwords {--dry-run : Show how many parent accounts would be rotated without changing them}';

    protected $description = 'Rotate all parent account passwords to the ToQuran{Name} format.';

    public function handle(CredentialService $credentials, AccountHistoryService $history): int
    {
        $query = User::query()
            ->role('parent');

        $total = (clone $query)->count();

        if ($total === 0) {
            $this->info('No linked parent accounts were found.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info("{$total} parent account(s) would be rotated.");

            return self::SUCCESS;
        }

        $rotated = 0;
        $historySkipped = 0;

        $this->info("Rotating {$total} parent account(s) to the ToQuran{Name} format...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query
            ->with('parent_user')
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($bar, &$historySkipped, &$rotated, $credentials, $history): void {
                foreach ($users as $user) {
                    $parent = $user->parent_user;
                    $plain = $credentials->generateParentPasswordForName(
                        $parent?->first_name ?: $user->first_name ?: $user->name
                    );

                    DB::transaction(function () use ($credentials, $history, $parent, $plain, $user, &$historySkipped): void {
                        $credentials->generateAndStore($user, $plain);

                        if (! $parent) {
                            $historySkipped++;

                            return;
                        }

                        $history->record($parent->id, AccountHistoryEventType::ParentPasswordResetByAdmin->value, [
                            'subject_type' => 'parent',
                            'subject_id' => $parent->id,
                            'actor_user_id' => null,
                            'actor_role' => 'system',
                            'metadata' => [
                                'subject_user_id' => $user->id,
                                'source' => 'bulk_parent_password_rotation',
                                'password_format' => 'ToQuran{Name}',
                            ],
                        ]);
                    });

                    $rotated++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);
        $this->info("Rotated {$rotated} parent account(s).");

        if ($historySkipped > 0) {
            $this->warn("Skipped Account History writes for {$historySkipped} parent user(s) with no linked parent record.");
        }

        return self::SUCCESS;
    }
}
