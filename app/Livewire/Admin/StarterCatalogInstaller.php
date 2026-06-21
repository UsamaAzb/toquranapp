<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Support\ToQuranAutomationCatalog\AutomationCatalogInstaller;
use App\Support\ToQuranAutomationCatalog\StarterAutomationCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use RuntimeException;
use Throwable;

class StarterCatalogInstaller extends Component
{
    private const REGISTRY_TABLE = AutomationCatalogInstaller::REGISTRY_TABLE;

    public ?int $teacherId = null;

    public bool $confirmInstall = false;

    /** @var array{dry_run?: bool, created?: int, updated?: int, skipped?: int, messages?: array<int, string>} */
    public array $previewResult = [];

    /** @var array{dry_run?: bool, created?: int, updated?: int, skipped?: int, messages?: array<int, string>} */
    public array $installResult = [];

    public ?string $lastTeacherLabel = null;

    public ?string $installerError = null;

    public function mount(): void
    {
        abort_unless($this->canInstallStarterCatalog(), 403);
    }

    public function updatedTeacherId(): void
    {
        $this->reset(['confirmInstall', 'previewResult', 'installResult', 'lastTeacherLabel', 'installerError']);
        $this->resetValidation();
    }

    public function previewCatalog(): void
    {
        abort_unless($this->canInstallStarterCatalog(), 403);

        $teacher = $this->validatedTeacher();
        $this->installerError = null;
        $this->installResult = [];
        $this->confirmInstall = false;
        $this->lastTeacherLabel = $this->teacherLabel($teacher);

        $this->previewResult = $this->normalizeResult(
            app(AutomationCatalogInstaller::class)->previewForTeacher($teacher)
        );
    }

    public function installCatalog(): void
    {
        abort_unless($this->canInstallStarterCatalog(), 403);

        $teacher = $this->validatedTeacher();

        $this->validate([
            'confirmInstall' => ['accepted'],
        ], [
            'confirmInstall.accepted' => 'Confirm that you want to install or verify the starter catalog for this teacher.',
        ]);

        $this->installerError = null;
        $this->lastTeacherLabel = $this->teacherLabel($teacher);

        try {
            $this->installResult = $this->normalizeResult(
                app(AutomationCatalogInstaller::class)->installForTeacher($teacher, dryRun: false)
            );
            $this->previewResult = $this->normalizeResult(
                app(AutomationCatalogInstaller::class)->previewForTeacher($teacher)
            );
            $this->confirmInstall = false;
            session()->flash('success', 'Starter catalog install completed for '.$this->lastTeacherLabel.'.');
        } catch (RuntimeException $exception) {
            $this->installerError = $exception->getMessage();
        } catch (Throwable $exception) {
            Log::error('Starter catalog installation failed.', [
                'teacher_id' => $teacher->id,
                'exception' => $exception,
            ]);

            $this->installerError = 'Starter catalog installation failed. Please check the logs and try again.';
        }
    }

    public function render(): View
    {
        return view('livewire.admin.starter-catalog-installer', [
            'teachers' => $this->teacherOptions(),
            'catalogSummary' => $this->catalogSummary(),
            'selectedTeacherRegistryCount' => $this->registryCountForSelectedTeacher(),
            'registryTableExists' => Schema::hasTable(self::REGISTRY_TABLE),
            'databaseName' => DB::connection()->getDatabaseName(),
        ])->layout('components.layouts.app', ['title' => 'Starter Catalog Installer']);
    }

    protected function canInstallStarterCatalog(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'super_admin']);
    }

    protected function validatedTeacher(): User
    {
        $data = $this->validate([
            'teacherId' => ['required', 'integer', Rule::exists('users', 'id')],
        ], [], [
            'teacherId' => 'teacher',
        ]);

        return $this->teacherQuery()
            ->whereKey((int) $data['teacherId'])
            ->firstOrFail();
    }

    protected function teacherOptions(): Collection
    {
        return $this->teacherQuery()
            ->get(['id', 'name', 'email', 'status']);
    }

    protected function teacherQuery(): Builder
    {
        return User::role('teacher')
            ->where(function (Builder $query): void {
                $query->where('status', 'active')
                    ->orWhereNull('status');
            })
            ->orderBy('name')
            ->orderBy('email');
    }

    protected function registryCountForSelectedTeacher(): int
    {
        if (! is_int($this->teacherId) || $this->teacherId <= 0 || ! Schema::hasTable(self::REGISTRY_TABLE)) {
            return 0;
        }

        return DB::table(self::REGISTRY_TABLE)
            ->where('teacher_user_id', $this->teacherId)
            ->count();
    }

    protected function catalogSummary(): Collection
    {
        return collect(app(StarterAutomationCatalog::class)->entries())
            ->groupBy('subject_title')
            ->map(fn (Collection $entries, string $subject): array => [
                'subject' => $subject,
                'entries' => $entries->map(fn (array $entry): array => [
                    'key' => (string) $entry['catalog_key'],
                    'title' => (string) $entry['title'],
                    'type' => str_replace('_', ' ', (string) $entry['type']),
                ])->values()->all(),
            ])
            ->values();
    }

    protected function normalizeResult(array $result): array
    {
        return [
            'dry_run' => (bool) ($result['dry_run'] ?? false),
            'created' => (int) ($result['created'] ?? 0),
            'updated' => (int) ($result['updated'] ?? 0),
            'skipped' => (int) ($result['skipped'] ?? 0),
            'messages' => array_values(array_map('strval', $result['messages'] ?? [])),
        ];
    }

    protected function teacherLabel(User $teacher): string
    {
        return trim(($teacher->name ?: 'Teacher').' - '.$teacher->email);
    }
}
