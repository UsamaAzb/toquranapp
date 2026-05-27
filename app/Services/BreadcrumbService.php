<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use App\Support\BookingSubjectProvisioning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\View\Factory as ViewFactory;

class BreadcrumbService
{
    public function __construct(
        protected Request $request,
        protected ViewFactory $views
    ) {}

    public function resolve(): array
    {
        $adminLinks = $this->resolveAdmin();

        if ($adminLinks !== []) {
            return $adminLinks;
        }

        return $this->resolveTeacher();
    }

    public function resolveAdmin(): array
    {
        if (! $this->request->is('admin*')) {
            return [];
        }

        $routeName = Route::currentRouteName();
        $adminHomeUrl = Route::has('admin.bookings.livewire') ? route('admin.bookings.livewire') : url('/admin/bookings');
        $studentsUrl = url('/admin/students');
        $giftsUrl = Route::has('gifts.index') ? route('gifts.index') : url('/admin/gifts');

        if (Str::startsWith((string) $routeName, 'admin.bookings.')) {
            $links = [
                'Bookings' => $routeName === 'admin.bookings.livewire' ? null : $adminHomeUrl,
            ];

            $adminBookingLabels = [
                'admin.bookings.transferred' => 'Transferred Families',
                'admin.bookings.intake-review' => 'Intake Review',
                'admin.bookings.children.edit' => 'Child Workflow',
                'admin.bookings.parent.edit' => 'Parent Details',
                'admin.bookings.legacy' => 'Legacy Bookings',
            ];

            if (isset($adminBookingLabels[$routeName])) {
                $links[$adminBookingLabels[$routeName]] = null;
            }

            return $links;
        }

        if ($routeName === 'admin.families.show') {
            return [
                'Families' => Route::has('admin.bookings.transferred') ? route('admin.bookings.transferred') : null,
                'Family Workspace' => null,
            ];
        }

        if ($routeName === 'admin.calendar.view') {
            return [
                'Calendar' => null,
            ];
        }

        if (Str::startsWith((string) $routeName, 'admin.students.')) {
            $studentLabels = [
                'admin.students.account' => 'Student Account',
                'admin.students.security' => 'Security',
                'admin.students.show_reward' => 'Reward System',
            ];

            return [
                'Students' => $studentsUrl,
                $studentLabels[$routeName] ?? 'Student Details' => null,
            ];
        }

        if (Str::startsWith((string) $routeName, 'gifts.')) {
            $links = [
                'Gifts' => $routeName === 'gifts.index' ? null : $giftsUrl,
            ];

            $giftLabels = [
                'gifts.create' => 'Create Gift',
                'gifts.edit' => 'Edit Gift',
                'gifts.show' => 'Gift Details',
            ];

            if (isset($giftLabels[$routeName])) {
                $links[$giftLabels[$routeName]] = null;
            }

            return $links;
        }

        $title = trim($this->views->yieldContent('title'));
        $fallbackLabel = $title !== ''
            ? $title
            : Str::headline(str_replace(['-', '_'], ' ', trim($this->request->path(), '/')));

        return [
            $fallbackLabel => null,
        ];
    }

    public function resolveTeacher(): array
    {
        if (! $this->request->is('teacher*') || ! Auth::user()?->hasRole('teacher')) {
            return [];
        }

        $routeName = Route::currentRouteName();
        $classesUrl = Route::has('teacher.classes') ? route('teacher.classes') : url('/teacher/classes');

        if ($routeName === 'teacher.classes') {
            return [
                'My Classes' => null,
            ];
        }

        if ($routeName === 'teacher.sessions') {
            return [
                'My Classes' => $classesUrl,
                $this->teacherSubjectLabel($this->teacherSubjectClassId()) => null,
            ];
        }

        if ($routeName === 'teacher.task-approvals') {
            return [
                'My Classes' => $classesUrl,
                $this->studentSubjectPageLabel(
                    $this->routeInt('student'),
                    $this->routeInt('subject'),
                    'Task Review'
                ) => null,
            ];
        }

        if ($routeName === 'teacher.reward-discpline') {
            return $this->teacherStudentSurfaceLinks($classesUrl, 'Points Lab');
        }

        if ($routeName === 'teacher.get_agreement') {
            return $this->teacherStudentSurfaceLinks($classesUrl, 'Consequences');
        }

        if ($this->request->is('teacher/journey/board*')) {
            return $this->teacherStudentSurfaceLinks($classesUrl, 'Rewards');
        }

        if ($this->request->is('teacher/journey')) {
            return $this->teacherStudentSurfaceLinks($classesUrl, 'Reward System');
        }

        return [];
    }

    private function teacherStudentSurfaceLinks(string $classesUrl, string $currentLabel): array
    {
        $teacherSubjectClassId = $this->teacherSubjectClassId();
        $studentId = $this->studentId();

        $links = [
            'My Classes' => $classesUrl,
        ];

        if ($teacherSubjectClassId) {
            $links[$this->teacherSubjectLabel($teacherSubjectClassId)] = route('teacher.sessions', [
                'teachersubjectid' => $teacherSubjectClassId,
            ]);
        }

        $links[$this->studentPageLabel($studentId, $currentLabel)] = null;

        return $links;
    }

    private function studentSubjectPageLabel(?int $studentId, ?int $subjectId, string $fallback): string
    {
        $studentName = $this->studentName($studentId);
        $subjectTitle = $this->subjectTitle($subjectId);

        if ($studentName && $subjectTitle) {
            return $studentName.' - '.$subjectTitle.' '.$fallback;
        }

        if ($studentName) {
            return $studentName.' - '.$fallback;
        }

        return $fallback;
    }

    private function studentPageLabel(?int $studentId, string $fallback): string
    {
        $studentName = $this->studentName($studentId);

        return $studentName ? $studentName.' - '.$fallback : $fallback;
    }

    private function teacherSubjectLabel(?int $teacherSubjectClassId): string
    {
        $teacherSubjectClass = $this->teacherSubjectClass($teacherSubjectClassId);

        if (! $teacherSubjectClass) {
            return 'Class Sessions';
        }

        $className = trim((string) ($teacherSubjectClass->class_name ?: $teacherSubjectClass->class?->title));
        $subjectName = trim(BookingSubjectProvisioning::displaySubjectShortName(
            (int) ($teacherSubjectClass->subject_id ?? $teacherSubjectClass->subject?->id ?? 0),
            $teacherSubjectClass->subject_name ?: $teacherSubjectClass->subject?->title
        ));

        if ($className !== '' && $subjectName !== '') {
            return $className.' - '.$subjectName;
        }

        return $className !== '' ? $className : ($subjectName !== '' ? $subjectName : 'Class Sessions');
    }

    private function teacherSubjectClass(?int $teacherSubjectClassId): ?TeacherSubjectClass
    {
        if (! $teacherSubjectClassId) {
            return null;
        }

        return TeacherSubjectClass::query()
            ->with(['class:id,title', 'subject:id,title'])
            ->whereKey($teacherSubjectClassId)
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->first();
    }

    private function studentName(?int $studentId): ?string
    {
        if (! $studentId) {
            return null;
        }

        $student = Student::query()
            ->select(['id', 'first_name', 'last_name'])
            ->find($studentId);

        if (! $student) {
            return null;
        }

        return trim((string) $student->first_name.' '.(string) $student->last_name) ?: null;
    }

    private function subjectTitle(?int $subjectId): ?string
    {
        if (! $subjectId) {
            return null;
        }

        $title = Subject::query()
            ->whereKey($subjectId)
            ->value('title');

        return BookingSubjectProvisioning::displaySubjectShortName($subjectId, filled($title) ? (string) $title : null);
    }

    private function teacherSubjectClassId(): ?int
    {
        return $this->routeInt('teachersubjectid') ?? $this->queryInt('teachersubjectid');
    }

    private function studentId(): ?int
    {
        return $this->routeInt('student_id')
            ?? $this->routeInt('student')
            ?? $this->queryInt('student_id');
    }

    private function routeInt(string $key): ?int
    {
        $value = $this->request->route($key);

        if (is_object($value) && isset($value->id)) {
            $value = $value->id;
        }

        return $this->positiveInt($value);
    }

    private function queryInt(string $key): ?int
    {
        return $this->positiveInt($this->request->query($key));
    }

    private function positiveInt(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $intValue = (int) $value;

        return $intValue > 0 ? $intValue : null;
    }
}
