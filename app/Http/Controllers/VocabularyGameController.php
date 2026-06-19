<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\TeacherSubjectClass;
use App\Models\VocabularyGameAssignment;
use App\Models\VocabularySet;
use App\Models\VocabularySourceAccess;
use App\Services\Library\ResourceReturnTargetResolver;
use App\Services\Vocabulary\VocabularyAccessService;
use App\Services\Vocabulary\VocabularySourceRegistry;
use App\Services\Vocabulary\VocabularyWordProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VocabularyGameController extends Controller
{
    public function __construct(
        private readonly VocabularyAccessService $accessService,
        private readonly VocabularyWordProvider $wordProvider,
        private readonly VocabularySourceRegistry $sourceRegistry,
    ) {}

    public function hub(): View|RedirectResponse
    {
        if (! $this->gamesEnabled()) {
            return view('vocabulary.games.coming-soon');
        }

        if (Auth::user()?->hasAnyRole(['teacher', 'admin', 'super_admin', 'owner'])) {
            return redirect()->route('teacher.vocabulary.games.launch');
        }

        return view('vocabulary.games.hub');
    }

    public function teacherLauncher(Request $request): View
    {
        if (! $this->gamesEnabled()) {
            return view('vocabulary.games.coming-soon');
        }

        $teacherSubjectClass = $this->resolveTeacherContext($request);
        $contexts = $this->teacherContexts();
        $this->sourceRegistry->ensureLegacySourceProxies();
        $sourceId = $request->integer('source_id');
        $selectedSource = null;
        $sourceRows = collect();
        $sourceBreadcrumbs = collect();
        $sourceMetadata = [];

        if (Schema::hasTable('vocabulary_sets')) {
            $baseQuery = VocabularySet::query()
                ->visibleToTeachers(Auth::id())
                ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED);

            if ($sourceId > 0) {
                $selectedSource = (clone $baseQuery)->whereKey($sourceId)->first();

                if ($selectedSource instanceof VocabularySet && ! $this->isTeacherLauncherSource($selectedSource)) {
                    $selectedSource = null;
                }

                if ($selectedSource instanceof VocabularySet && $selectedSource->isPlayable()) {
                    $selectedSource = $selectedSource->parent;
                }
            }

            $sourceRows = (clone $baseQuery)
                ->where('parent_id', $selectedSource instanceof VocabularySet ? (int) $selectedSource->id : null)
                ->where(function ($query) use ($selectedSource): void {
                    if ($selectedSource instanceof VocabularySet) {
                        return;
                    }

                    $query
                        ->where('set_type', VocabularySet::TYPE_TEACHER)
                        ->orWhere('source_kind', VocabularySet::SOURCE_LEGACY_DIFFICULTY);
                })
                ->withCount(['children', 'memberships'])
                ->orderBy('sort_order')
                ->orderBy('title')
                ->limit(80)
                ->get();

            if ($teacherSubjectClass instanceof TeacherSubjectClass) {
                $sourceRows = $this->filterSourcesForClassAccess($sourceRows, (int) $teacherSubjectClass->class_id);
            }

            $sourceMetadata = $this->sourceRegistry->batchMetadata($sourceRows->pluck('id'));
            $sourceBreadcrumbs = $selectedSource instanceof VocabularySet
                ? $this->sourceBreadcrumbs($selectedSource)
                : collect();
        }

        return view('vocabulary.games.custom-launch', [
            'teacherSubjectClass' => $teacherSubjectClass,
            'contexts' => $contexts,
            'sources' => $sourceRows,
            'selectedSource' => $selectedSource,
            'sourceBreadcrumbs' => $sourceBreadcrumbs,
            'sourceMetadata' => $sourceMetadata,
            'breadcrumb_links' => $this->teacherLauncherBreadcrumbLinks($sourceBreadcrumbs),
        ]);
    }

    public function playSource(Request $request, VocabularySet $source): View
    {
        abort_unless($source->canBeLaunched(), 404);
        $this->authorizeSourceLaunch($source);

        if (! $this->gamesEnabled()) {
            return view('vocabulary.games.coming-soon');
        }

        $game = $this->validateGame($request->string('game')->toString() ?: 'hangman');
        $difficulty = $this->validateDifficulty($request->string('difficulty')->toString() ?: 'sprout');
        $words = $this->wordProvider->playableWordsForSet($source, $game);
        $backToGames = Auth::user()?->hasAnyRole(['teacher', 'admin', 'super_admin', 'owner'])
            ? route('teacher.library.vocabulary', ['mode' => 'source', 'set' => (int) $source->id])
            : route('vocabulary.games.hub');
        $backLabel = Auth::user()?->hasAnyRole(['teacher', 'admin', 'super_admin', 'owner'])
            ? 'Vocabulary'
            : 'Vocab Games';

        abort_if($words === [], 404);

        return $this->gameView($game, [
            'payload' => $this->payload($source->title, $game, $difficulty, $words, [
                'viewer' => [
                    'embedded' => $this->isAttachmentViewer($request),
                ],
                'initialIndex' => $this->initialWordIndex($request, $words),
                'routes' => [
                    'backToGames' => $backToGames,
                    'backLabel' => $backLabel,
                    'navbarBreadcrumbs' => $this->sourceGameBreadcrumbLinks($source, $game, $backToGames, $backLabel),
                    'surfaceBreadcrumbs' => $this->sourceSurfaceBreadcrumbLinks($source, $backToGames, $backLabel),
                    'gameTabs' => $this->sourceGameTabs($request, $source, $difficulty),
                    'difficultyTabs' => $this->sourceDifficultyTabs($request, $source, $game),
                ],
            ]),
        ]);
    }

    public function playAssignment(Request $request, VocabularyGameAssignment $assignment): View
    {
        abort_unless($assignment->isActive(), 404);
        $this->authorizeAssignmentLaunch($assignment);

        if (! $this->gamesEnabled()) {
            return view('vocabulary.games.coming-soon');
        }

        $availableGames = $this->allGameKeys();
        $game = $this->validateGame($request->string('game')->toString() ?: 'hangman');

        $difficulty = $this->validateDifficulty($request->string('difficulty')->toString() ?: 'sprout');

        $assignment->loadMissing('vocabularySet');
        $words = $this->wordProvider->playableWordsForSet($assignment->vocabularySet, $game);

        abort_if($words === [], 404);

        $fallbackBackToGames = Auth::user()?->hasAnyRole(['teacher', 'admin', 'super_admin', 'owner'])
            ? route('teacher.vocabulary.games.launch')
            : route('vocabulary.games.hub');
        $backToGames = app(ResourceReturnTargetResolver::class)
            ->resolveFromRequest($request, $fallbackBackToGames);
        $backLabel = $backToGames === $fallbackBackToGames ? 'Vocab Games' : 'Task';

        return $this->gameView($game, [
            'payload' => $this->payload($assignment->vocabularySet->title, $game, $difficulty, $words, [
                'assignmentId' => $assignment->id,
                'viewer' => [
                    'embedded' => $this->isAttachmentViewer($request),
                ],
                'initialIndex' => $this->initialWordIndex($request, $words),
                'routes' => [
                    'backToGames' => $backToGames,
                    'backLabel' => $backLabel,
                    'navbarBreadcrumbs' => $this->assignmentGameBreadcrumbLinks($assignment, $game, $backToGames, $backLabel),
                    'surfaceBreadcrumbs' => $this->assignmentSurfaceBreadcrumbLinks($assignment, $backToGames, $backLabel),
                    'gameTabs' => $this->assignmentGameTabs($request, $assignment, $difficulty, $availableGames),
                    'difficultyTabs' => $this->assignmentDifficultyTabs($request, $assignment, $game),
                ],
            ]),
        ]);
    }

    public function playCustom(Request $request): View|RedirectResponse
    {
        abort_unless(Auth::user()?->hasAnyRole(['teacher', 'admin', 'super_admin', 'owner']), 403);

        if (! $this->gamesEnabled()) {
            return view('vocabulary.games.coming-soon');
        }

        $data = $request->validate([
            'game' => ['required', 'in:hangman,missing_letter,spelling_choice'],
            'difficulty' => ['required', 'in:sprout,climber,champion'],
            'words' => ['required'],
            'teacher_subject_class_id' => ['nullable', 'integer'],
        ]);

        $this->authorizeTeacherLaunchContext((int) ($data['teacher_subject_class_id'] ?? 0));

        $words = $this->normalizeCustomWords($data['words']);
        abort_if($words === [], 422, __('vocabulary.games.launcher.word_required'));

        $payloadWords = $this->wordProvider->customPayload($words, $data['game']);

        if ($payloadWords === []) {
            return back()->withErrors([
                'words' => __('vocabulary.games.no_safe_options'),
            ])->withInput();
        }

        $token = (string) Str::uuid();
        session()->put($this->customSessionKey($token), [
            'words' => $words,
            'teacher_subject_class_id' => (int) ($data['teacher_subject_class_id'] ?? 0),
        ]);

        return redirect()->route('teacher.vocabulary.games.custom.play', [
            'token' => $token,
            'game' => $this->publicGameQueryValue($data['game']),
            'difficulty' => $data['difficulty'],
        ]);
    }

    public function playCustomSession(Request $request, string $token): View|RedirectResponse
    {
        abort_unless(Auth::user()?->hasAnyRole(['teacher', 'admin', 'super_admin', 'owner']), 403);

        if (! $this->gamesEnabled()) {
            return view('vocabulary.games.coming-soon');
        }

        $sessionData = session($this->customSessionKey($token));

        if (! is_array($sessionData) || ! isset($sessionData['words']) || ! is_array($sessionData['words'])) {
            return redirect()
                ->route('teacher.vocabulary.games.launch')
                ->withErrors(['words' => __('vocabulary.games.launcher.word_required')]);
        }

        $teacherSubjectClassId = (int) ($sessionData['teacher_subject_class_id'] ?? 0);
        $this->authorizeTeacherLaunchContext($teacherSubjectClassId);

        $game = $this->validateGame($request->string('game')->toString() ?: 'hangman');
        $difficulty = $this->validateDifficulty($request->string('difficulty')->toString() ?: 'sprout');
        $payloadWords = $this->wordProvider->customPayload($sessionData['words'], $game);

        if ($payloadWords === []) {
            return redirect()
                ->route('teacher.vocabulary.games.launch', array_filter([
                    'teacher_subject_class_id' => $teacherSubjectClassId ?: null,
                ]))
                ->withErrors(['words' => __('vocabulary.games.no_safe_options')]);
        }

        return $this->gameView($game, [
            'payload' => $this->payload(__('vocabulary.games.launcher.custom_words'), $game, $difficulty, $payloadWords, [
                'custom' => true,
                'initialIndex' => $this->initialWordIndex($request, $payloadWords),
                'routes' => [
                    'backToGames' => route('teacher.vocabulary.games.launch', array_filter([
                        'teacher_subject_class_id' => $teacherSubjectClassId ?: null,
                    ])),
                    'backLabel' => 'Teacher Play',
                    'navbarBreadcrumbs' => $this->customGameBreadcrumbLinks($game),
                    'surfaceBreadcrumbs' => $this->customSurfaceBreadcrumbLinks(),
                    'gameTabs' => $this->customGameTabs($request, $token, $difficulty),
                ],
            ]),
        ]);
    }

    public function resolveTeacherContext(Request $request): ?TeacherSubjectClass
    {
        $teacherSubjectClassId = $request->integer('teacher_subject_class_id');

        if ($teacherSubjectClassId <= 0) {
            return null;
        }

        $context = TeacherSubjectClass::query()->find($teacherSubjectClassId);

        if (! $context) {
            return null;
        }

        abort_unless(
            $this->accessService->teacherCanUseClassContext(Auth::user(), (int) $context->class_id, (int) $context->id),
            403
        );

        return $context;
    }

    /**
     * @param  array<int, array<string, mixed>>  $words
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function payload(string $sourceTitle, string $game, string $difficulty, array $words, array $extra = []): array
    {
        $base = [
            'source' => [
                'title' => $sourceTitle,
            ],
            'game' => $game,
            'difficulty' => $difficulty,
            'words' => $words,
            'routes' => [
                'backToGames' => route('vocabulary.games.hub'),
            ],
            'viewer' => [
                'embedded' => false,
            ],
            'assets' => $this->hangmanAssets(),
            'initialIndex' => 0,
        ];

        return array_replace_recursive($base, $extra);
    }

    /**
     * @param  array<int, array<string, mixed>>  $words
     */
    private function initialWordIndex(Request $request, array $words): int
    {
        if ($words === []) {
            return 0;
        }

        return min(max($request->integer('word_index'), 0), count($words) - 1);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function gameView(string $game, array $data): View
    {
        return match ($game) {
            'missing_letter' => view('vocabulary.games.missing-letter', $data),
            'spelling_choice' => view('vocabulary.games.spelling-choice', $data),
            default => view('vocabulary.games.hangman', $data),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function hangmanAssets(): array
    {
        return [
            'cheerSound' => '/uploads/vocabgames/correct/storegraphic-crowd-cheers.mp3',
            'popSound' => '/uploads/vocabgames/balloon-pop.mp3',
            'popSounds' => [
                '/uploads/vocabgames/balloon-pop.mp3',
            ],
            'completeSound' => '/uploads/vocabgames/correct/storegraphic-crowd-cheers.mp3',
            'winSounds' => [
                '/uploads/vocabgames/correct/alex_jauk-fireworks.mp3',
                '/uploads/vocabgames/correct/dragon-studio-wow.mp3',
                '/uploads/vocabgames/correct/freesound_community-fireworks.mp3',
                '/uploads/vocabgames/correct/freesound_community-party-horn.mp3',
                '/uploads/vocabgames/correct/freesound_community-yeah.mp3',
                '/uploads/vocabgames/correct/storegraphic-crowd-cheers.mp3',
            ],
            'lossSounds' => [
                '/uploads/vocabgames/wrong/losing-game-1.mp3',
                '/uploads/vocabgames/wrong/losing-game-2.mp3',
                '/uploads/vocabgames/wrong/losing-game-3.mp3',
                '/uploads/vocabgames/wrong/losing-game-4.mp3',
            ],
            'winGif' => '/uploads/hangman/videos/1-unscreen.gif',
            'lossGif' => '/uploads/hangman/videos/2-unscreen.gif',
        ];
    }

    private function validateGame(string $game): string
    {
        $game = $game === 'floatie' ? 'hangman' : $game;

        abort_unless(in_array($game, ['hangman', 'missing_letter', 'spelling_choice'], true), 404);

        return $game;
    }

    /**
     * @return list<string>
     */
    private function allGameKeys(): array
    {
        return ['hangman', 'missing_letter', 'spelling_choice'];
    }

    /**
     * @return list<string>
     */
    private function allDifficultyKeys(): array
    {
        return ['sprout', 'climber', 'champion'];
    }

    private function isAttachmentViewer(Request $request): bool
    {
        return $request->query('w14_viewer') === 'attachment';
    }

    /**
     * @return array<string, mixed>
     */
    private function sourceGameTabs(Request $request, VocabularySet $source, string $difficulty): array
    {
        $baseQuery = $this->preservedGameQuery($request, $difficulty);

        return collect($this->allGameKeys())
            ->mapWithKeys(fn (string $game): array => [
                $game => route('vocabulary.games.source', array_merge($baseQuery, [
                    'source' => (int) $source->id,
                    'game' => $this->publicGameQueryValue($game),
                ])),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function sourceDifficultyTabs(Request $request, VocabularySet $source, string $game): array
    {
        $baseQuery = $this->preservedDifficultyQuery($request, $game);

        return collect($this->allDifficultyKeys())
            ->mapWithKeys(fn (string $difficulty): array => [
                $difficulty => route('vocabulary.games.source', array_merge($baseQuery, [
                    'source' => (int) $source->id,
                    'difficulty' => $difficulty,
                ])),
            ])
            ->all();
    }

    /**
     * @param  list<string>  $availableGames
     * @return array<string, string>
     */
    private function assignmentGameTabs(
        Request $request,
        VocabularyGameAssignment $assignment,
        string $difficulty,
        array $availableGames
    ): array {
        $baseQuery = $this->preservedGameQuery($request, $difficulty);

        return collect($availableGames)
            ->mapWithKeys(fn (string $game): array => [
                $game => route('vocabulary.games.assignment', array_merge($baseQuery, [
                    'assignment' => (int) $assignment->id,
                    'game' => $this->publicGameQueryValue($game),
                ])),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function assignmentDifficultyTabs(
        Request $request,
        VocabularyGameAssignment $assignment,
        string $game
    ): array {
        $baseQuery = $this->preservedDifficultyQuery($request, $game);

        return collect($this->allDifficultyKeys())
            ->mapWithKeys(fn (string $difficulty): array => [
                $difficulty => route('vocabulary.games.assignment', array_merge($baseQuery, [
                    'assignment' => (int) $assignment->id,
                    'difficulty' => $difficulty,
                ])),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function customGameTabs(Request $request, string $token, string $difficulty): array
    {
        $baseQuery = $this->preservedGameQuery($request, $difficulty);

        return collect($this->allGameKeys())
            ->mapWithKeys(fn (string $game): array => [
                $game => route('teacher.vocabulary.games.custom.play', array_merge($baseQuery, [
                    'token' => $token,
                    'game' => $this->publicGameQueryValue($game),
                ])),
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function preservedGameQuery(Request $request, string $difficulty): array
    {
        $query = collect($request->query())
            ->except(['game', 'word_index'])
            ->all();

        $query['difficulty'] = $difficulty;

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function preservedDifficultyQuery(Request $request, string $game): array
    {
        $query = collect($request->query())
            ->except(['difficulty', 'word_index'])
            ->all();

        $query['game'] = $this->publicGameQueryValue($game);

        return $query;
    }

    private function publicGameQueryValue(string $game): string
    {
        return $game === 'hangman' ? 'floatie' : $game;
    }

    private function customSessionKey(string $token): string
    {
        return 'vocabulary_games.custom.'.$token;
    }

    private function authorizeSourceLaunch(VocabularySet $source): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->hasAnyRole(['admin', 'super_admin', 'owner'])) {
            return;
        }

        if ($user->hasRole('teacher')) {
            $visible = VocabularySet::query()
                ->visibleToTeachers((int) $user->id)
                ->whereKey($source->id)
                ->exists();

            abort_unless($visible, 403);

            return;
        }

        if ($user->hasRole('student')) {
            $student = Student::query()->where('user_id', $user->id)->first();
            abort_unless($student && $this->accessService->hasEnabledAccess($student, $source), 403);

            return;
        }

        if ($user->hasRole('parent')) {
            $student = $this->studentForParent((int) $user->id);
            abort_unless($student && $this->accessService->hasEnabledAccess($student, $source), 403);

            return;
        }

        abort(403);
    }

    private function authorizeAssignmentLaunch(VocabularyGameAssignment $assignment): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->hasAnyRole(['admin', 'super_admin', 'owner'])) {
            return;
        }

        if ($user->hasRole('teacher')) {
            abort_unless((int) $assignment->assigned_by_user_id === (int) $user->id, 403);

            return;
        }

        if ($user->hasRole('student')) {
            $student = Student::query()->where('user_id', $user->id)->first();
            abort_unless($student, 403);
            $student->loadMissing('currentClass');

            $matchesStudent = $assignment->audience_type === VocabularyGameAssignment::AUDIENCE_STUDENT
                && (int) $assignment->audience_id === (int) $student->id;
            $matchesClass = $assignment->audience_type === VocabularyGameAssignment::AUDIENCE_CLASS
                && $student->currentClass
                && (int) $assignment->audience_id === (int) $student->currentClass->id;

            abort_unless($matchesStudent || $matchesClass, 403);

            return;
        }

        if ($user->hasRole('parent')) {
            $student = $this->studentForParent((int) $user->id);
            abort_unless($student, 403);
            $student->loadMissing('currentClass');

            $matchesStudent = $assignment->audience_type === VocabularyGameAssignment::AUDIENCE_STUDENT
                && (int) $assignment->audience_id === (int) $student->id;
            $matchesClass = $assignment->audience_type === VocabularyGameAssignment::AUDIENCE_CLASS
                && $student->currentClass
                && (int) $assignment->audience_id === (int) $student->currentClass->id;

            abort_unless($matchesStudent || $matchesClass, 403);

            return;
        }

        abort(403);
    }

    private function validateDifficulty(string $difficulty): string
    {
        abort_unless(in_array($difficulty, ['sprout', 'climber', 'champion'], true), 404);

        return $difficulty;
    }

    private function teacherContexts(): \Illuminate\Support\Collection
    {
        $user = Auth::user();

        if (! $user || ! Schema::hasTable('teacher_subject_classes')) {
            return collect();
        }

        $query = TeacherSubjectClass::query()
            ->availableForTeacher()
            ->with('class:id,title');

        if ($user->hasRole('teacher')) {
            $query->where('user_teacher_coteacher_id', $user->id);
        }

        return $query
            ->orderBy('class_name')
            ->orderBy('subject_name')
            ->limit(80)
            ->get();
    }

    private function sourceBreadcrumbs(VocabularySet $source): \Illuminate\Support\Collection
    {
        $trail = collect();
        $current = $source;

        while ($current instanceof VocabularySet) {
            $trail->prepend($current);
            $current = $current->parent;
        }

        return $trail->values();
    }

    private function isTeacherLauncherSource(VocabularySet $source): bool
    {
        return $source->set_type === VocabularySet::TYPE_TEACHER
            || $source->source_kind === VocabularySet::SOURCE_LEGACY_DIFFICULTY;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, VocabularySet>  $sourceRows
     * @return \Illuminate\Support\Collection<int, VocabularySet>
     */
    private function filterSourcesForClassAccess(\Illuminate\Support\Collection $sourceRows, int $classId): \Illuminate\Support\Collection
    {
        if ($classId <= 0 || $sourceRows->isEmpty()) {
            return $sourceRows;
        }

        $accessRows = VocabularySourceAccess::query()
            ->where('audience_type', VocabularySourceAccess::AUDIENCE_CLASS)
            ->where('audience_id', $classId)
            ->get()
            ->keyBy('vocabulary_set_id');

        $enabledSetIds = $accessRows
            ->filter(fn (VocabularySourceAccess $access): bool => $access->status === VocabularySourceAccess::STATUS_ENABLED)
            ->pluck('vocabulary_set_id')
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();

        if ($enabledSetIds->isEmpty()) {
            return collect();
        }

        $visibleIds = VocabularySet::query()
            ->whereIn('id', $enabledSetIds)
            ->with('parent.parent.parent.parent')
            ->get()
            ->flatMap(function (VocabularySet $set): array {
                $ids = [];
                $current = $set;

                while ($current instanceof VocabularySet) {
                    $ids[] = (int) $current->id;
                    $current = $current->parent;
                }

                return $ids;
            })
            ->unique()
            ->values();

        return $sourceRows
            ->filter(function (VocabularySet $set) use ($accessRows, $visibleIds): bool {
                $current = $set;

                while ($current instanceof VocabularySet) {
                    $access = $accessRows->get((int) $current->id);

                    if ($access instanceof VocabularySourceAccess) {
                        return $access->status === VocabularySourceAccess::STATUS_ENABLED;
                    }

                    $current = $current->parent;
                }

                return $visibleIds->contains((int) $set->id);
            })
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, VocabularySet>  $sourceBreadcrumbs
     * @return array<string, string|null>
     */
    private function teacherLauncherBreadcrumbLinks(\Illuminate\Support\Collection $sourceBreadcrumbs): array
    {
        $links = [
            'Vocabulary' => route('teacher.library.vocabulary'),
            'Teacher Play' => $sourceBreadcrumbs->isEmpty() ? null : route('teacher.vocabulary.games.launch'),
        ];

        foreach ($sourceBreadcrumbs as $crumb) {
            $links[(string) $crumb->title] = $sourceBreadcrumbs->last()?->is($crumb)
                ? null
                : route('teacher.vocabulary.games.launch', ['source_id' => (int) $crumb->id]);
        }

        return $links;
    }

    /**
     * @return array<string, string|null>
     */
    private function sourceGameBreadcrumbLinks(VocabularySet $source, string $game, string $backUrl, string $backLabel): array
    {
        $links = [
            $backLabel => $backLabel === 'Vocabulary' ? route('teacher.library.vocabulary') : $backUrl,
        ];

        if ($backLabel === 'Vocabulary') {
            foreach ($this->sourceBreadcrumbs($source) as $crumb) {
                $links[(string) $crumb->title] = route('teacher.library.vocabulary', [
                    'mode' => 'source',
                    'set' => (int) $crumb->id,
                ]);
            }
        } else {
            $links[(string) $source->title] = null;
        }

        $links[$this->gameTitle($game)] = null;

        return $links;
    }

    /**
     * @return array<string, string|null>
     */
    private function assignmentGameBreadcrumbLinks(
        VocabularyGameAssignment $assignment,
        string $game,
        string $backUrl,
        string $backLabel
    ): array {
        return [
            $backLabel => $backUrl,
            (string) ($assignment->vocabularySet?->title ?? 'Vocabulary') => null,
            $this->gameTitle($game) => null,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function sourceSurfaceBreadcrumbLinks(VocabularySet $source, string $backUrl, string $backLabel): array
    {
        $links = [
            $backLabel => $backLabel === 'Vocabulary' ? route('teacher.library.vocabulary') : $backUrl,
        ];

        if ($backLabel === 'Vocabulary') {
            foreach ($this->sourceBreadcrumbs($source) as $crumb) {
                $links[(string) $crumb->title] = $source->is($crumb)
                    ? null
                    : route('teacher.library.vocabulary', [
                        'mode' => 'source',
                        'set' => (int) $crumb->id,
                    ]);
            }

            return $links;
        }

        $links[(string) $source->title] = null;

        return $links;
    }

    /**
     * @return array<string, string|null>
     */
    private function assignmentSurfaceBreadcrumbLinks(
        VocabularyGameAssignment $assignment,
        string $backUrl,
        string $backLabel
    ): array {
        return [
            $backLabel => $backUrl,
            (string) ($assignment->vocabularySet?->title ?? 'Vocabulary') => null,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function customGameBreadcrumbLinks(string $game): array
    {
        return [
            'Vocabulary' => route('teacher.library.vocabulary'),
            'Teacher Play' => route('teacher.vocabulary.games.launch'),
            'Custom Words' => null,
            $this->gameTitle($game) => null,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function customSurfaceBreadcrumbLinks(): array
    {
        return [
            'Teacher Play' => route('teacher.vocabulary.games.launch'),
            'Custom Words' => null,
        ];
    }

    private function gameTitle(string $game): string
    {
        return match ($game) {
            'missing_letter' => 'Missing Letter',
            'spelling_choice' => 'Correct Spelling',
            default => 'Floatie',
        };
    }

    private function gamesEnabled(): bool
    {
        return (bool) config('vocabulary.games.enabled', false);
    }

    private function studentForParent(int $parentUserId): ?Student
    {
        return Student::query()
            ->whereHas('parent', fn ($query) => $query->where('user_id', $parentUserId))
            ->orderBy('id')
            ->first();
    }

    private function authorizeTeacherLaunchContext(int $teacherSubjectClassId): void
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole('teacher')) {
            return;
        }

        if ($teacherSubjectClassId <= 0) {
            return;
        }

        $context = TeacherSubjectClass::query()->findOrFail($teacherSubjectClassId);

        abort_unless(
            $this->accessService->teacherCanUseClassContext($user, (int) $context->class_id, (int) $context->id),
            403
        );
    }

    /**
     * @return list<string>
     */
    private function normalizeCustomWords(mixed $value): array
    {
        $items = is_array($value)
            ? $value
            : preg_split('/[\r\n,;]+/', (string) $value);

        return collect($items ?: [])
            ->map(fn (mixed $word): string => trim((string) $word))
            ->filter()
            ->filter(fn (string $word): bool => mb_strlen($word) <= (int) config('vocabulary.free_play.max_length', 40))
            ->filter(fn (string $word): bool => preg_match((string) config('vocabulary.free_play.allowed_pattern'), $word) === 1)
            ->unique(fn (string $word): string => mb_strtolower($word))
            ->take((int) config('vocabulary.games.custom_source_word_limit', 30))
            ->values()
            ->all();
    }
}
