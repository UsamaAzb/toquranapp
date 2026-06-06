<?php

namespace App\Livewire\Admin\Students;

use App\Models\PunishmentAgreement;
use App\Models\PunishmentsSuggestion;
use App\Models\PunishmentType;
use App\Models\Student_punishment;
use App\Models\StudentsSubject;
use App\Models\TeacherSubjectClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PunishmentAgreementsTabs extends Component
{
    public int $studentId;

    public ?int $teacherSubjectId = null;

    public ?\App\Models\Student $student = null;

    public ?int $subjectId = null;

    public ?\App\Models\Subject $subject = null;

    public ?string $subjectName = null;

    /** @var array<int, array{title:string, slug:string}> */
    public array $types = []; // [type_id => ['title'=>'Small','slug'=>'small']]

    public string $activeType = 'minor-slip';

    /** @var array<int, array{id:int,title:string,status:string,created_at?:string}> */
    public array $agreements = [];

    public array $form = [
        'punishment_type_id' => null,
        'title' => '',
    ];

    public ?int $editingAgreementId = null;

    public array $editForm = [
        'punishment_type_id' => null,
        'title' => '',
        'status' => 'active',
    ];

    public ?string $successMessage = null;

    public ?string $editSuccessMessage = null;

    // for tracher
    public array $applications = []; // applied punishments list (for the bottom list)

    public array $apply = [
        'punishment_type_id' => null,
        'punishment_agreement_id' => null,
        'description' => '',
    ];

    public array $applyAgreements = [];

    public ?string $applySuccess = null;
    // for tracher

    /** suggestions per slug */
    public array $suggestionsMap = []; // [slug => [text, text...]]

    protected function resolveTeacherSubjectClass(): ?TeacherSubjectClass
    {
        if (! $this->teacherSubjectId) {
            return null;
        }

        if (auth()->check() && auth()->user()->hasRole('teacher')) {
            $teacherSubjectClass = TeacherSubjectClass::query()
                ->whereKey($this->teacherSubjectId)
                ->where('user_teacher_coteacher_id', auth()->id())
                ->firstOrFail();

            abort_unless(
                StudentsSubject::query()
                    ->where('student_id', $this->studentId)
                    ->where('class_subject_id', $teacherSubjectClass->class_subject_id)
                    ->exists(),
                403
            );

            return $teacherSubjectClass;
        }

        return TeacherSubjectClass::find($this->teacherSubjectId);
    }

    public function mount(int $studentId, string $defaultType = 'minor-slip', ?int $teacherSubjectId = null): void
    {
        $this->studentId = $studentId;
        $this->student = \App\Models\Student::find($studentId);

        $this->teacherSubjectId = $teacherSubjectId;

        if ($this->teacherSubjectId) {
            $this->subjectName = $this->resolveTeacherSubjectClass()?->subject_name;
        }

        // $this->activeType = in_array(strtolower($defaultType), ['minor-slip','significant-choice','serious-action']) ? strtolower($defaultType) : 'minor-slip';

        // Load types from DB and map to slug by title
        $this->types = [];
        // foreach (PunishmentType::orderBy('id')->get(['id','title']) as $t) {

        foreach (PunishmentType::where('active', 1)->orderBy('id')->get(['id', 'title']) as $t) {

            $slug = str_replace(' ', '-', strtolower($t->title));  // expects 'minor-slip','significant-choice','serious-action'
            $this->types[$t->id] = ['title' => $t->title, 'slug' => $slug];
        }

        // default form type = active tab
        $this->form['punishment_type_id'] = $this->typeIdBySlug($this->activeType);

        $allowedSlugs = collect($this->types)->pluck('slug')->map(fn ($s) => strtolower($s))->values()->all();
        $requested = strtolower($defaultType);

        $this->activeType = in_array($requested, $allowedSlugs, true)
            ? $requested
            : ($allowedSlugs[0] ?? 'minor-slip');

        $typeIds = array_keys($this->types);

        $rows = PunishmentsSuggestion::query()
            ->whereIn('punishment_type_id', $typeIds)
            ->orderBy('id')
            ->get(['punishment_type_id', 'suggestion_text'])
            ->groupBy('punishment_type_id');

        $this->suggestionsMap = [];
        foreach ($this->types as $typeId => $meta) {
            $slug = $meta['slug'];
            $this->suggestionsMap[$slug] = ($rows->get($typeId, collect()))
                ->pluck('suggestion_text')
                ->filter(fn ($v) => trim((string) $v) !== '')
                ->values()
                ->all();
        }

        $this->loadAgreements();

        // for tracher
        $this->apply['punishment_type_id'] = $this->form['punishment_type_id'];
        $this->loadApplyAgreements();
        $this->loadApplications();
        // for tracher
    }

    public function setActiveType(string $slug): void
    {
        $slug = strtolower($slug);
        // if (! in_array($slug, ['minor-slip','significant-choice','serious-action'])) return;
        $allowedSlugs = collect($this->types)->pluck('slug')->map(fn ($s) => strtolower($s))->all();
        if (! in_array($slug, $allowedSlugs, true)) {
            return;
        }

        $this->activeType = $slug;
        $this->form['punishment_type_id'] = $this->typeIdBySlug($slug);
        $this->successMessage = null;
        $this->editSuccessMessage = null;
        $this->loadAgreements();
    }

    public function addSuggestion(string $text): void
    {
        $this->form['title'] = $text;
    }

    public function save(): void
    {
        if (! auth()->check() || ! auth()->user()->hasAnyRole(['admin', 'super_admin'])) {
            abort(403);
        }

        $this->validate($this->rules());

        $agreement = PunishmentAgreement::create([
            'student_id' => $this->studentId,
            'title' => trim($this->form['title']),
            'punishment_type_id' => (int) $this->form['punishment_type_id'],
            'status' => 'active',
        ]);

        // prepend to current list if matches active type
        if ($this->form['punishment_type_id'] === $this->typeIdBySlug($this->activeType)) {
            array_unshift($this->agreements, [
                'id' => $agreement->id,
                'title' => $agreement->title,
                'status' => $agreement->status,
            ]);
        }

        // clear title only & show success message
        $this->form['title'] = '';
        $this->successMessage = 'Consequence  has been added. You can add another one.';
    }

    public function editAgreement(int $agreementId): void
    {
        $this->authorizeAdminAgreementWrite();

        $agreement = PunishmentAgreement::query()
            ->whereKey($agreementId)
            ->where('student_id', $this->studentId)
            ->firstOrFail();

        $this->resetErrorBag();
        $this->editingAgreementId = (int) $agreement->id;
        $this->editForm = [
            'punishment_type_id' => (int) $agreement->punishment_type_id,
            'title' => (string) $agreement->title,
            'status' => (string) $agreement->status,
        ];
        $this->editSuccessMessage = null;

        $this->dispatch('open-edit-punishment-modal');
    }

    public function cancelEditAgreement(): void
    {
        $this->resetEditAgreementForm();
        $this->dispatch('close-edit-punishment-modal');
    }

    public function updateAgreement(): void
    {
        $this->authorizeAdminAgreementWrite();

        if (! $this->editingAgreementId) {
            $this->addError('editForm.title', 'Choose an agreement to edit first.');

            return;
        }

        $this->validate($this->editRules());

        $agreement = PunishmentAgreement::query()
            ->whereKey($this->editingAgreementId)
            ->where('student_id', $this->studentId)
            ->firstOrFail();

        $agreement->update([
            'punishment_type_id' => (int) $this->editForm['punishment_type_id'],
            'title' => trim((string) $this->editForm['title']),
            'status' => (string) $this->editForm['status'],
        ]);

        $typeId = (int) $this->editForm['punishment_type_id'];
        $newTypeSlug = isset($this->types[$typeId]) ? $this->types[$typeId]['slug'] : $this->activeType;
        $this->activeType = $newTypeSlug;
        $this->form['punishment_type_id'] = $typeId;
        $this->editSuccessMessage = 'Consequence agreement has been updated.';
        $this->loadAgreements();
        $this->loadApplyAgreements();
    }

    private function resetEditAgreementForm(): void
    {
        $this->resetErrorBag();
        $this->editingAgreementId = null;
        $this->editForm = [
            'punishment_type_id' => null,
            'title' => '',
            'status' => 'active',
        ];
        $this->editSuccessMessage = null;
    }

    protected function rules(): array
    {
        return [
            'form.punishment_type_id' => ['required', 'integer', Rule::in(array_keys($this->types))],
            'form.title' => [
                'required', 'string', 'max:255',
                Rule::unique('punishment_agreements', 'title')->where(function ($q) {
                    return $q->where('student_id', $this->studentId)
                        ->where('punishment_type_id', (int) $this->form['punishment_type_id']);
                }),
            ],
        ];
    }

    protected function editRules(): array
    {
        return [
            'editForm.punishment_type_id' => ['required', 'integer', Rule::in(array_keys($this->types))],
            'editForm.title' => [
                'required', 'string', 'max:255',
                Rule::unique('punishment_agreements', 'title')
                    ->ignore($this->editingAgreementId)
                    ->where(function ($q) {
                        return $q->where('student_id', $this->studentId)
                            ->where('punishment_type_id', (int) $this->editForm['punishment_type_id']);
                    }),
            ],
            'editForm.status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    private function authorizeAdminAgreementWrite(): void
    {
        if (! auth()->check() || ! auth()->user()->hasAnyRole(['admin', 'super_admin'])) {
            abort(403);
        }
    }

    protected function typeIdBySlug(string $slug): ?int
    {
        foreach ($this->types as $id => $meta) {
            if (strtolower($meta['slug']) === strtolower($slug)) {
                return (int) $id;
            }
        }

        return null;
    }

    protected function loadAgreements(): void
    {
        $typeId = $this->typeIdBySlug($this->activeType);

        if (! $typeId) {
            $this->agreements = [];

            return;
        }

        $this->agreements = PunishmentAgreement::query()
            ->where('student_id', $this->studentId)
            ->where('punishment_type_id', $typeId)
            ->orderByRaw("CASE WHEN LOWER(TRIM(title)) = 'customized' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get(['id', 'title', 'status'])
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'status' => $a->status,
            ])->toArray();
    }

    // for teacher
    public function loadApplyAgreements(): void
    {
        $this->applySuccess = null;

        $typeId = (int) ($this->apply['punishment_type_id'] ?? 0);
        if (! $typeId) {
            $this->applyAgreements = [];

            return;
        }

        $this->applyAgreements = PunishmentAgreement::query()
            ->where('student_id', $this->studentId)
            ->where('punishment_type_id', $typeId)
            ->where('status', 'active')
            ->orderByRaw("CASE WHEN LOWER(TRIM(title)) = 'customized' THEN 0 ELSE 1 END")
            ->orderBy('id')
            ->get(['id', 'title'])
            ->map(fn ($a) => ['id' => $a->id, 'title' => $a->title])
            ->toArray();

        if (! collect($this->applyAgreements)->pluck('id')->contains($this->apply['punishment_agreement_id'])) {
            $this->apply['punishment_agreement_id'] = null;
        }
    }

    public function selectAgreement(int $id): void
    {
        $this->apply['punishment_agreement_id'] = $id;
        $this->applySuccess = null;
    }

    protected function applicationRules(): array
    {
        return [
            'apply.punishment_type_id' => ['required', 'integer', \Illuminate\Validation\Rule::in(array_keys($this->types))],
            'apply.punishment_agreement_id' => ['required', 'integer', function ($attr, $value, $fail) {
                $ok = PunishmentAgreement::query()
                    ->where('id', $value)
                    ->where('student_id', $this->studentId)
                    ->where('punishment_type_id', (int) $this->apply['punishment_type_id'])
                    ->exists();
                if (! $ok) {
                    $fail('Selected agreement is invalid.');
                }
            }],
            'apply.description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function saveApplication(): void
    {

        if (! auth()->check() || ! auth()->user()->hasAnyRole('teacher')) {
            abort(403);
        }
        $user = Auth::user();
        $user_id = $user->id;

        $teacherSubjectClass = $this->resolveTeacherSubjectClass();
        abort_if(! $teacherSubjectClass, 403);

        $subject_id = $teacherSubjectClass->subject_id;

        $this->subjectId = $subject_id;
        $this->subject = \App\Models\Subject::find($subject_id);

        $this->validate($this->applicationRules());

        Student_punishment::create([
            'student_id' => $this->studentId,
            'description' => trim((string) ($this->apply['description'] ?? '')),
            'punishment_agreement_id' => (int) $this->apply['punishment_agreement_id'],
            'subject_id' => $subject_id,
            'teacher_subject_class' => $teacherSubjectClass->id,
            'created_by_id' => $user_id,
            'created_at' => now()->toDateString(),
        ]);

        $this->applySuccess = 'Punishment has been recorded.';
        $this->apply['description'] = '';
        $this->loadApplications(); // refresh bottom list
    }

    public function loadApplications(): void
    {
        // join to get type title + agreement title
        $rows = DB::table('student_punishments as sp')
            ->join('punishment_agreements as pa', 'pa.id', '=', 'sp.punishment_agreement_id')
            ->join('punishment_types as pt', 'pt.id', '=', 'pa.punishment_type_id')
            ->leftJoin('subjects as s', 's.id', '=', 'sp.subject_id')
            ->where('sp.student_id', $this->studentId)
            ->when($this->teacherSubjectId, function ($q) {
                $q->where('sp.teacher_subject_class', $this->teacherSubjectId);
            })
            ->orderByDesc('sp.created_at')
            ->orderByDesc('sp.id')
            ->select([
                'sp.id',
                'sp.created_at',
                'sp.description',
                'pa.title as agreement_title',
                'pt.title as type_title',
                's.title as subject_title',
            ])
            ->get();

        $this->applications = $rows->map(function ($r) {
            return [
                'id' => (int) $r->id,
                'date' => $r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('d M') : '-',
                'type' => (string) $r->type_title,
                'agreement' => (string) $r->agreement_title,
                'description' => (string) ($r->description ?? ''),
                'subject' => $r->subject_title ?? '-',
            ];
        })->toArray();
    }

    // for teacher

    public function render()
    {
        // compute current suggestions by activeType
        // $suggestions = $this->suggestions[$this->activeType] ?? [];
        $suggestions = $this->suggestionsMap[$this->activeType] ?? [];

        return view('livewire.admin.students.punishment-agreements-tabs', [
            'suggestions' => $suggestions,
        ]);
    }
}
