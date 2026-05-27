<?php

namespace App\Livewire\Student;

use App\Models\Student;
use App\Services\Vocabulary\VocabularyAccessService;
use App\Services\Vocabulary\VocabularySourceRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Livewire\Component;

class VocabularyGamesHub extends Component
{
    public ?int $studentId = null;

    public ?int $openSetId = null;

    public function mount(?int $studentId = null): void
    {
        $resolvedStudentId = $studentId ?: request()->integer('student_id');

        $this->studentId = $resolvedStudentId > 0 ? $resolvedStudentId : null;
    }

    public function openSet(int $setId): void
    {
        $this->openSetId = $this->openSetId === $setId ? null : $setId;
    }

    public function render(): View
    {
        $registry = app(VocabularySourceRegistry::class);
        $schemaReady = Schema::hasTable('vocabulary_sets') && Schema::hasTable('vocabulary_source_access');
        $sets = collect();
        $metadata = [];
        $tree = [];
        $currentNode = null;
        $currentNodes = [];
        $breadcrumbs = [];

        if ($schemaReady) {
            $student = $this->studentForCurrentUser();

            if ($student) {
                $sets = app(VocabularyAccessService::class)->visiblePlayableSetsForStudent($student);

                $metadata = $registry->batchMetadata($sets->pluck('id'));
                $tree = $this->buildSourceTree($sets, $metadata);
                $currentNode = $this->openSetId ? $this->findNode($tree, $this->openSetId) : null;
                $currentNodes = $currentNode ? ($currentNode['children'] ?? []) : $tree;
                $breadcrumbs = $currentNode ? $this->breadcrumbTrail($tree, (int) $currentNode['id']) : [];
            }
        }

        return view('livewire.student.vocabulary-games-hub', [
            'schemaReady' => $schemaReady,
            'sets' => $sets,
            'metadata' => $metadata,
            'tree' => $tree,
            'currentNode' => $currentNode,
            'currentNodes' => $currentNodes,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    private function studentForCurrentUser(): ?Student
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if ($user->hasRole('student')) {
            return Student::query()->where('user_id', (int) $user->id)->first();
        }

        if ($user->hasRole('parent')) {
            $query = Student::query()
                ->whereHas('parent', fn ($query) => $query->where('user_id', (int) $user->id));

            if ($this->studentId !== null) {
                $student = $query->whereKey($this->studentId)->first();
                abort_unless($student instanceof Student, 403);

                return $student;
            }

            $students = $query
                ->orderBy('id')
                ->limit(2)
                ->get();

            return $students->count() === 1 ? $students->first() : null;
        }

        return null;
    }

    private function buildSourceTree($sets, array $metadata): array
    {
        $tree = [];

        foreach ($sets as $set) {
            $chain = [];
            $current = $set;

            while ($current) {
                array_unshift($chain, $current);
                $current = $current->parent;
            }

            $cursor = &$tree;

            foreach ($chain as $nodeSet) {
                $key = 'set-'.$nodeSet->id;

                if (! isset($cursor[$key])) {
                    $cursor[$key] = [
                        'id' => (int) $nodeSet->id,
                        'title' => (string) $nodeSet->title,
                        'is_playable' => $nodeSet->isPlayable(),
                        'word_count' => (int) ($metadata[$nodeSet->id]['word_count'] ?? 0),
                        'children' => [],
                    ];
                }

                if ((int) $nodeSet->id === (int) $set->id) {
                    $cursor[$key]['is_playable'] = true;
                    $cursor[$key]['word_count'] = (int) ($metadata[$set->id]['word_count'] ?? 0);
                }

                $cursor = &$cursor[$key]['children'];
            }

            unset($cursor);
        }

        return $this->normalizeTree($tree);
    }

    private function normalizeTree(array $nodes): array
    {
        return collect($nodes)
            ->map(function (array $node): array {
                $node['children'] = $this->normalizeTree($node['children'] ?? []);

                return $node;
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<string, mixed>|null
     */
    private function findNode(array $nodes, int $setId): ?array
    {
        foreach ($nodes as $node) {
            if ((int) ($node['id'] ?? 0) === $setId) {
                return $node;
            }

            $match = $this->findNode($node['children'] ?? [], $setId);

            if ($match !== null) {
                return $match;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    private function breadcrumbTrail(array $nodes, int $setId): array
    {
        foreach ($nodes as $node) {
            if ((int) ($node['id'] ?? 0) === $setId) {
                return [$node];
            }

            $childTrail = $this->breadcrumbTrail($node['children'] ?? [], $setId);

            if ($childTrail !== []) {
                array_unshift($childTrail, $node);

                return $childTrail;
            }
        }

        return [];
    }
}
