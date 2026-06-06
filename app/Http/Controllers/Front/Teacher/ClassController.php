<?php

namespace App\Http\Controllers\Front\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttachmentFile;
use App\Models\ClassSession;
use App\Models\DifferentiatedTaskStudentAssignment;
use App\Models\MainDailySessionStudentAssignment;
use App\Models\SessionTaskStudent;
use App\Models\SeriesTaskStudentAssignment;
use App\Models\Student;
use App\Models\StudentsSubject;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use App\Services\AttachmentService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClassController extends Controller
{
    public function __construct(
        private readonly AttachmentService $attachmentService
    ) {}

    protected function requireTeacherId(): int
    {
        abort_unless(Auth::check() && Auth::user()->hasRole('teacher'), 403);

        return (int) Auth::id();
    }

    protected function findOwnedTeacherSubjectClass(int $teacherSubjectClassId): TeacherSubjectClass
    {
        return TeacherSubjectClass::query()
            ->whereKey($teacherSubjectClassId)
            ->where('user_teacher_coteacher_id', $this->requireTeacherId())
            ->availableForTeacher()
            ->withActiveStudentSubject()
            ->firstOrFail();
    }

    protected function findOwnedClassSession(int $sessionId): ClassSession
    {
        return ClassSession::query()
            ->whereKey($sessionId)
            ->whereHas('teacherSubjectClass', function ($query) {
                $query->where('user_teacher_coteacher_id', $this->requireTeacherId())
                    ->availableForTeacher()
                    ->withActiveStudentSubject();
            })
            ->normal()
            ->firstOrFail();
    }

    /**
     * Display a listing of the teacher's classes.
     */
    public function get_classes()
    {
        // user_teacher_coteacher_id must be user->auth

        if ((Auth::check()) && (Auth::user()->hasRole('teacher'))) {

            $user = Auth::user();
            $user_id = $user->id;

            $TeacherSubjectClass = TeacherSubjectClass::query()
                ->where('user_teacher_coteacher_id', $user_id)
                ->availableForTeacher()
                ->withActiveStudentSubject()
                ->with(['classSubject.studentsSubjects' => function ($query): void {
                    $query->where('status', 'active')
                        ->whereHas('student', function ($studentQuery): void {
                            $studentQuery->visibleToTeacher();
                        })
                        ->with('student:id,first_name,last_name,student_email,account_status');
                }])
                ->get();

            return view('teacher.classes.subject_classes', compact('TeacherSubjectClass'));
        }

    }

    public function change_status($id)
    {
        return response()->json([
            'status' => false,
            'message' => 'Subject access is managed by the admin dashboard.',
        ], 403);

    }

    public function get_sessions(Request $request)
    {
        $teacherSubjectClass = $this->findOwnedTeacherSubjectClass((int) $request->teachersubjectid);

        //   get teacher session for this class
        $teacher_class_sessions = ClassSession::query()
            ->where('teacher_subject_classes_id', $teacherSubjectClass->id)
            ->normal()
            ->get();

        $class_subject_id = $teacherSubjectClass->class_subject_id;

        $teacher_students = StudentsSubject::query()
            ->with('student:id,first_name,last_name,student_email')
            ->where('class_subject_id', $class_subject_id)
            ->where('status', 'active')
            ->whereHas('student', function ($studentQuery): void {
                $studentQuery->visibleToTeacher();
            })
            ->get()
            ->pluck('student')
            ->filter()
            ->unique('id')
            ->values();

        $student_id = $teacher_students->first()?->id;

        $teachersubjectid = $teacherSubjectClass->id;
        // Raw COUNT aggregate keeps the in-review task summary grouped by student
        // for the class-session quick-action badges.
        $reviewCounts = SessionTaskStudent::query()
            ->select('session_task_student.student_id', DB::raw('COUNT(*) as review_count'))
            ->join('session_tasks', 'session_tasks.id', '=', 'session_task_student.session_task_id')
            ->join('class_sessions', 'class_sessions.id', '=', 'session_tasks.class_session_id')
            ->whereIn('session_task_student.student_id', $teacher_students->pluck('id'))
            ->where('class_sessions.subject_id', $teacherSubjectClass->subject_id)
            ->where('class_sessions.class_subject_id', $teacherSubjectClass->class_subject_id)
            ->whereIn('session_task_student.status', [
                SessionTaskStudent::STATUS_IN_REVIEW,
                SessionTaskStudent::STATUS_LEGACY_PENDING,
            ])
            ->groupBy('session_task_student.student_id')
            ->pluck('review_count', 'session_task_student.student_id')
            ->map(fn ($count): int => (int) $count);

        $automationSummaries = $this->currentAutomationSummaries($teacher_students, $teacherSubjectClass);

        return view('teacher.classes.class_sessions', compact(
            'teacher_class_sessions',
            'teacherSubjectClass',
            'teacher_students',
            'teachersubjectid',
            'student_id',
            'reviewCounts',
            'automationSummaries'
        ));
    }

    private function currentAutomationSummaries(Collection $students, TeacherSubjectClass $teacherSubjectClass): Collection
    {
        $studentIds = $students
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();

        $summaries = $studentIds->mapWithKeys(fn (int $studentId): array => [$studentId => collect()]);

        if ($studentIds->isEmpty()) {
            return $summaries;
        }

        $today = Carbon::today(config('app.timezone', 'Africa/Cairo'));

        if (
            Schema::hasTable('main_daily_session_student_assignments')
            && Schema::hasTable('main_daily_session_templates')
        ) {
            MainDailySessionStudentAssignment::query()
                ->with(['template:id,title,subject_id,created_by_user_id,status', 'version:id,display_name'])
                ->whereIn('student_id', $studentIds)
                ->effectiveOn($today)
                ->whereHas('template', fn ($query) => $query
                    ->where('subject_id', $teacherSubjectClass->subject_id)
                    ->where('created_by_user_id', Auth::id())
                    ->where('status', 'active'))
                ->get()
                ->each(function (MainDailySessionStudentAssignment $assignment) use ($summaries): void {
                    $summaries[(int) $assignment->student_id]->push([
                        'type' => 'Routine',
                        'title' => (string) ($assignment->template?->title ?? 'Untitled routine'),
                        'meta' => (string) ($assignment->version?->display_name ?? 'Current version'),
                    ]);
                });
        }

        if (
            Schema::hasTable('differentiated_task_student_assignments')
            && Schema::hasTable('differentiated_tasks')
        ) {
            DifferentiatedTaskStudentAssignment::query()
                ->with(['task:id,title,subject_id,created_by_user_id,status', 'version:id,display_name'])
                ->whereIn('student_id', $studentIds)
                ->effectiveOn($today)
                ->whereHas('task', fn ($query) => $query
                    ->where('subject_id', $teacherSubjectClass->subject_id)
                    ->where('created_by_user_id', Auth::id())
                    ->where('status', 'active'))
                ->get()
                ->each(function (DifferentiatedTaskStudentAssignment $assignment) use ($summaries): void {
                    $summaries[(int) $assignment->student_id]->push([
                        'type' => 'Custom task',
                        'title' => (string) ($assignment->task?->title ?? 'Untitled custom task'),
                        'meta' => (string) ($assignment->version?->display_name ?? 'Current version'),
                    ]);
                });
        }

        if (
            Schema::hasTable('series_task_student_assignments')
            && Schema::hasTable('series_tasks')
        ) {
            SeriesTaskStudentAssignment::query()
                ->with(['task:id,title,subject_id,created_by_user_id,status', 'version:id,display_name'])
                ->whereIn('student_id', $studentIds)
                ->effectiveOn($today)
                ->whereHas('task', fn ($query) => $query
                    ->where('subject_id', $teacherSubjectClass->subject_id)
                    ->where('created_by_user_id', Auth::id())
                    ->where('status', 'active'))
                ->get()
                ->each(function (SeriesTaskStudentAssignment $assignment) use ($summaries): void {
                    $summaries[(int) $assignment->student_id]->push([
                        'type' => 'Series',
                        'title' => (string) ($assignment->task?->title ?? 'Untitled series'),
                        'meta' => (string) ($assignment->version?->display_name ?? 'Current version'),
                    ]);
                });
        }

        return $summaries->map(fn (Collection $items): Collection => $items
            ->sortBy([['type', 'asc'], ['title', 'asc']])
            ->values());
    }

    public function taskApprovals(int $student, int $subject)
    {
        $this->requireTeacherId();

        Student::query()->findOrFail($student);
        Subject::query()->findOrFail($subject);

        abort_unless(
            TeacherSubjectClass::query()
                ->where('user_teacher_coteacher_id', Auth::id())
                ->where('subject_id', $subject)
                ->availableForTeacher()
                ->whereHas('classSubject.studentsSubjects', function ($query) use ($student): void {
                    $query->where('student_id', $student)
                        ->where('status', 'active')
                        ->whereHas('student', function ($studentQuery): void {
                            $studentQuery->visibleToTeacher();
                        });
                })
                ->exists(),
            403
        );

        return view('teacher.classes.task-approvals', [
            'studentId' => $student,
            'subjectId' => $subject,
        ]);
    }

    public function index()
    {
        // Classes are loaded via Auth::user()->teacherClasses in the view
        return view('teacher.classes.index');
    }

    public function show_attachment(int $sessionId, int $attachmentId)
    {
        $class_session = $this->findOwnedClassSession($sessionId);
        $attachment = AttachmentFile::with('task.classSession')
            ->findOrFail($attachmentId);

        // 🔐 Security check
        if (
            ! $attachment->task ||
            ! $attachment->task->classSession ||
            $attachment->task->class_session_id != $sessionId
        ) {
            abort(404);
        }

        return view(
            'teacher.classes.attachment-show',
            $this->attachmentService->prepareTeacherSessionViewData($attachment, $class_session, $sessionId)
        );
    }

    public function stream_attachment(Request $request, int $sessionId, int $attachmentId): BinaryFileResponse
    {
        $this->findOwnedClassSession($sessionId);

        $attachment = AttachmentFile::with('task.classSession')->findOrFail($attachmentId);

        if (
            ! $attachment->task ||
            ! $attachment->task->classSession ||
            $attachment->task->class_session_id != $sessionId
        ) {
            abort(404);
        }

        abort_if($this->attachmentService->isExternal($attachment), 404);

        $path = $this->attachmentService->normalizedPath($attachment->path);
        abort_if(! $this->attachmentService->fileExists($attachment), 404);

        $absolutePath = Storage::disk('public')->path($path);
        $mimeType = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
        $downloadName = $this->safeDownloadFilename($path);

        if ($request->boolean('download')) {
            return $this->attachmentDownloadResponse($absolutePath, $downloadName, [
                'Content-Type' => $mimeType,
            ]);
        }

        return $this->inlineAttachmentResponse($absolutePath, $downloadName, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
