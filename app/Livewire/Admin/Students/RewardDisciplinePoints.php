<?php

namespace App\Livewire\Admin\Students;

use App\Models\DisciplineIcon;
use App\Models\RewardDisciplinePoint;
use App\Models\Student_Session_Discipline;
use App\Support\MyDeenJourneyLaunchDefaults;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RewardDisciplinePoints extends Component
{
    public int $studentId;

    public string $activeTab = 'Positive';

    public array $positiveBehaviors = [];

    public array $slipBehaviors = [];

    public array $noWayBehaviors = [];

    public array $icons = [];

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $formTitle = '';

    public string $formType = 'Positive';

    public int $formPoints = 1;

    public ?int $formDisciplineIconId = null;

    public ?int $deletingId = null;

    public function mount(int $studentId): void
    {
        $this->studentId = $studentId;
        app(MyDeenJourneyLaunchDefaults::class)->ensureBehaviorTemplates($this->studentId);

        $this->icons = DisciplineIcon::query()
            ->orderBy('id')
            ->get(['id', 'path'])
            ->toArray();

        $this->loadBehaviors();
    }

    protected function rules(): array
    {
        return [
            'formTitle' => ['required', 'string', 'max:255'],
            'formPoints' => ['required', 'integer', 'min:1', 'max:100'],
            'formType' => ['required', 'in:Positive,Slip,No Way'],
            'formDisciplineIconId' => ['nullable', 'exists:discipline_icons,id'],
        ];
    }

    protected function loadBehaviors(): void
    {
        $baseQuery = RewardDisciplinePoint::query()
            ->where(function ($q) {
                $q->where(function ($globalQuery) {
                    $globalQuery->whereNull('student_id')
                        ->where('status', 'active');
                })->orWhere(function ($studentQuery) {
                    $studentQuery->where('student_id', $this->studentId)
                        ->whereIn('status', ['active', 'inactive']);
                });
            });

        $this->positiveBehaviors = (clone $baseQuery)
            ->where('type', 'Positive')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('teacher_desc', 'desc')
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->toArray();

        $this->slipBehaviors = (clone $baseQuery)
            ->where('type', 'Slip')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('teacher_desc', 'desc')
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->toArray();

        $this->noWayBehaviors = (clone $baseQuery)
            ->where('type', 'No Way')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('teacher_desc', 'desc')
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->toArray();

    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['Positive', 'Slip', 'No Way'])) {
            return;
        }

        $this->activeTab = $tab;
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->formTitle = '';
        $this->formPoints = 1;
        $this->formType = 'Positive';
        $this->formDisciplineIconId = null;
    }

    public function closeBehaviorModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function openCreate(string $type = 'Positive'): void
    {
        $this->resetForm();
        $this->formType = $type;

        if (! empty($this->icons)) {
            $firstIcon = $this->icons[0];
            $this->formDisciplineIconId = $firstIcon['id'];
        }

        $this->showModal = true;
        $this->dispatch('open-behavior-modal');
    }

    public function openEdit(int $id): void
    {
        $behavior = RewardDisciplinePoint::where('student_id', $this->studentId)->findOrFail($id);

        $this->editingId = $behavior->id;
        $this->formTitle = $behavior->title;
        $this->formPoints = $behavior->points;
        $this->formType = $behavior->type ?? 'Positive';
        $this->formDisciplineIconId = $behavior->discipline_icon_id;

        $this->showModal = true;
        $this->dispatch('open-behavior-modal');

    }

    public function save(): void
    {
        $this->validate();

        $iconPath = null;
        if ($this->formDisciplineIconId) {
            $iconPath = DisciplineIcon::whereKey($this->formDisciplineIconId)->value('path');
        }

        $data = [
            'title' => $this->formTitle,
            'points' => $this->formPoints,
            'type' => $this->formType,
            'discipline_icon_id' => $this->formDisciplineIconId,
            'discipline_icon_path' => $iconPath,
        ];

        if ($this->editingId) {
            $behavior = RewardDisciplinePoint::where('student_id', $this->studentId)
                ->whereKey($this->editingId)
                ->firstOrFail();

            if ($behavior->type !== $this->formType) {
                $data['sort'] = $this->nextSortForType($this->formType);
            }

            $behavior->update($data);
        } else {
            $data['student_id'] = $this->studentId;
            $data['status'] = 'active';
            $data['sort'] = $this->nextSortForType($this->formType);

            RewardDisciplinePoint::create($data);
        }

        $this->showModal = false;
        $this->loadBehaviors();
        $this->dispatch('close-behavior-modal');
    }

    public function selectIcon(int $iconId): void
    {
        $this->formDisciplineIconId = $iconId;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->dispatch('open-behavior-delete-modal');
    }

    public function cancelDelete(): void
    {
        $this->deletingId = null;
        $this->dispatch('close-behavior-delete-modal');
    }

    public function deleteBehavior(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $behavior = RewardDisciplinePoint::where('student_id', $this->studentId)
            ->whereKey($this->deletingId)
            ->firstOrFail();

        if ($this->behaviorHasHistory($behavior)) {
            $behavior->update([
                'status' => 'inactive',
                'sort' => $this->archivedSortForType($behavior->type ?? 'Positive'),
            ]);
        } else {
            $behavior->delete();
        }

        $this->deletingId = null;

        $this->loadBehaviors();

        $this->dispatch('close-behavior-delete-modal');
    }

    public function restoreBehavior(int $id): void
    {
        $behavior = RewardDisciplinePoint::where('student_id', $this->studentId)
            ->whereKey($id)
            ->where('status', 'inactive')
            ->firstOrFail();

        $behavior->update([
            'status' => 'active',
            'sort' => $this->nextSortForType($behavior->type ?? 'Positive'),
        ]);

        $this->loadBehaviors();
    }

    public function reorderBehaviors(string $type, array $orderedIds): void
    {
        if (! in_array($type, ['Positive', 'Slip', 'No Way'], true)) {
            return;
        }

        $orderedIds = collect($orderedIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($orderedIds->isEmpty()) {
            return;
        }

        $ownedIds = RewardDisciplinePoint::query()
            ->where('student_id', $this->studentId)
            ->where('type', $type)
            ->where('status', 'active')
            ->where('teacher_desc', 0)
            ->whereIn('id', $orderedIds)
            ->pluck('id')
            ->all();

        $ownedIds = array_flip($ownedIds);
        $orderedOwnedIds = $orderedIds
            ->filter(fn (int $id) => isset($ownedIds[$id]))
            ->values();

        if ($orderedOwnedIds->isEmpty()) {
            return;
        }

        $startSort = $this->lastDefaultSortForType($type);

        DB::transaction(function () use ($orderedOwnedIds, $startSort) {
            foreach ($orderedOwnedIds as $index => $id) {
                RewardDisciplinePoint::where('student_id', $this->studentId)
                    ->whereKey($id)
                    ->update(['sort' => $startSort + (($index + 1) * 10)]);
            }
        });

        $this->loadBehaviors();
    }

    protected function behaviorHasHistory(RewardDisciplinePoint $behavior): bool
    {
        return Student_Session_Discipline::query()
            ->where('student_id', $this->studentId)
            ->where('student_reward_discipline_id', $behavior->id)
            ->exists();
    }

    protected function nextSortForType(string $type): int
    {
        return ((int) RewardDisciplinePoint::query()
            ->where('type', $type)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('student_id')
                    ->orWhere('student_id', $this->studentId);
            })
            ->max('sort')) + 10;
    }

    protected function lastDefaultSortForType(string $type): int
    {
        return (int) RewardDisciplinePoint::query()
            ->whereNull('student_id')
            ->where('type', $type)
            ->where('status', 'active')
            ->max('sort');
    }

    protected function archivedSortForType(string $type): int
    {
        return ((int) RewardDisciplinePoint::query()
            ->where('student_id', $this->studentId)
            ->where('type', $type)
            ->max('sort')) + 1000;
    }

    public function render(): View
    {
        return view('livewire.admin.students.reward-discipline-points');
    }
}
