<?php

namespace App\Http\Controllers\Front\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSession;
use App\Models\ParentModel;
use App\Models\PunishmentAgreement;
use App\Models\SessionTask;
use App\Models\SessionTaskStudent;
use App\Models\Student;
use App\Models\Student_Session_Discipline;
use App\Models\StudentGift;
use App\Models\StudentGiftPointsHistory;
use App\Models\StudentsSubject;
use App\Services\DailySessionPublisher;
use App\Services\DifferentiatedTaskAssignmentService;
use App\Services\DifferentiatedTaskPublisher;
use App\Services\DifferentiatedTaskSnapshotWriter;
use App\Services\SeriesTaskPublisher;
use App\Support\BookingSubjectProvisioning;
use App\Support\LifecycleGate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkplaceController extends Controller
{
    private const EMPTY_DT_SESSION_REPAIR_LIMIT = 10;

    /**
     * Display a listing of the student's classes.
     */
    public function index(Request $request)
    {

        if ((Auth::check()) && ((Auth::user()->hasRole('student')) || (Auth::user()->hasRole('parent')))) {

            $show_bar = 'false';

            $user = Auth::user();
            $user_id = $user->id;
            if (Auth::user()->hasRole('student')) {
                $student = Student::where('user_id', $user_id)->first();
                if (! $student) {
                    abort(404, 'Student record not found');
                }
                $student_id = $student->id;
                $breadcrumb_links = [
                    'My Workplace' => null,

                ];

            } elseif (Auth::user()->hasRole('parent')) {
                $student_id = $request->route('student_id');

                if (blank($student_id)) {
                    return redirect()->route('parent.students');
                }

                $parentModel = ParentModel::where('user_id', $user_id)->firstOrFail();
                $student = $parentModel->students()->find($student_id);

                if (! $student) {
                    return redirect()->route('parent.students');
                }

                $stu_name = $student->first_name;
                $breadcrumb_links = [
                    $stu_name => url('students'),
                    'My Workplace' => null,

                ];

            }

            $lifecycleGate = LifecycleGate::inspect((int) $student_id);
            if ($lifecycleGate->denied()) {
                if (Auth::user()->hasRole('parent')) {
                    return redirect()
                        ->route('parent.students')
                        ->with('warning', LifecycleGate::NEUTRAL_MESSAGE);
                }

                abort(403, LifecycleGate::NEUTRAL_MESSAGE);
            }

            // create daily sessions and tasks if there are daily sessions for this student
            $today = now(config('app.timezone', 'Africa/Cairo'))->startOfDay();
            $dailySessionPublisher = app(DailySessionPublisher::class);

            if ($dailySessionPublisher->needsGenerationForStudent($student_id, $today)) {
                $dailySessionPublisher->generateForStudent($student_id, $today);
            }

            $differentiatedTaskPublisher = app(DifferentiatedTaskPublisher::class);

            if ($differentiatedTaskPublisher->needsGenerationForStudent($student_id, $today)) {
                $differentiatedTaskPublisher->generateForStudent($student_id, $today);
            }

            $seriesTaskPublisher = app(SeriesTaskPublisher::class);

            if ($seriesTaskPublisher->needsGenerationForStudent($student_id, $today)) {
                $seriesTaskPublisher->generateForStudent($student_id, $today);
            }

            $this->repairEmptyDifferentiatedTaskSessions((int) $student_id);
            $this->ensureVisibleTaskPivots((int) $student_id);

            $total_post_point = 0;
            $total_negative_point = 0;
            $academicYearId = AcademicYear::currentId();
            $studentGiftPoints = StudentGiftPointsHistory::where('student_id', $student_id)
                ->where('academic_year_id', $academicYearId)
                ->first();
            $current_point = $studentGiftPoints?->points ?? 0;
            $target = null;

            $pending = StudentGift::query()
                ->where('student_id', $student_id)
                ->where('academic_year_id', $academicYearId)
                ->where('status', StudentGift::STATUS_PENDING)
                ->orderBy('points_required', 'asc')
                ->first();
            if ($pending) {
                $target = $pending->points_required;
            }

            $visibleTaskCounts = $this->visibleStudentSessionQuery((int) $student_id)
                ->withCount([
                    'tasks as visible_tasks_count',
                    'tasks as completed_tasks_count' => fn (Builder $query) => $query
                        ->whereHas('taskStudents', fn (Builder $taskStudentQuery) => $taskStudentQuery
                            ->where('student_id', $student_id)
                            ->where('status', 'completed')),
                ])
                ->get(['id']);

            $CompletedTaskStudentCount = (int) $visibleTaskCounts->sum('completed_tasks_count');
            $AssignedTaskStudentCount = (int) $visibleTaskCounts
                ->sum(fn (ClassSession $session): int => max(
                    0,
                    (int) $session->visible_tasks_count - (int) $session->completed_tasks_count
                ));

            $ReachedGift = StudentGift::where('student_id', $student_id)->where('academic_year_id', $academicYearId)->where('status', 'reached')->count();
            $RedeemedGift = StudentGift::where('student_id', $student_id)->where('academic_year_id', $academicYearId)->where('status', 'redeemed')->count();
            $student_subjects = StudentsSubject::where('student_id', $student_id)->where('status', 'active')->with('gradeLevelSubject.subject')
                ->get()
                ->each(function (StudentsSubject $studentSubject): void {
                    $studentSubject->setAttribute(
                        'subject_display',
                        BookingSubjectProvisioning::displayPayloadForStudentSubject($studentSubject)
                    );
                });

            $classSubjectIds = $student_subjects
                ->pluck('class_subject_id')
                ->map(fn ($id): int => (int) $id)
                ->filter()
                ->unique()
                ->values();

            $openTaskCountsByClassSubject = $classSubjectIds->isEmpty()
                ? collect()
                : SessionTask::query()
                    ->select('class_sessions.class_subject_id', DB::raw('COUNT(DISTINCT session_tasks.id) as open_task_count'))
                    ->join('class_sessions', 'class_sessions.id', '=', 'session_tasks.class_session_id')
                    ->join('session_task_student', 'session_task_student.session_task_id', '=', 'session_tasks.id')
                    ->whereIn('session_tasks.class_session_id', $this->visibleStudentSessionQuery((int) $student_id)->select('id'))
                    ->whereIn('class_sessions.class_subject_id', $classSubjectIds->all())
                    ->where('session_task_student.student_id', $student_id)
                    ->where(fn (Builder $query) => $query
                        ->whereNull('session_task_student.status')
                        ->orWhere('session_task_student.status', '!=', SessionTaskStudent::STATUS_COMPLETED))
                    ->groupBy('class_sessions.class_subject_id')
                    ->pluck('open_task_count', 'class_sessions.class_subject_id')
                    ->map(fn ($count): int => (int) $count);

            $student_subjects->each(function (StudentsSubject $studentSubject) use ($openTaskCountsByClassSubject): void {
                $studentSubject->setAttribute(
                    'open_task_count',
                    (int) $openTaskCountsByClassSubject->get((int) $studentSubject->class_subject_id, 0)
                );
            });

            $PunishmentCount = PunishmentAgreement::where('student_id', $student_id)->where('status', 'active')->count();

            $total_post_point = (int) Student_Session_Discipline::query()
                ->where('student_id', $student_id)
                ->where('type', 'Positive')
                ->sum('points');
            $total_negative_point = (int) Student_Session_Discipline::query()
                ->where('student_id', $student_id)
                ->whereIn('type', ['Slip', 'No Way'])
                ->sum('points');

            return view('student.workplace', compact('show_bar', 'breadcrumb_links', 'studentGiftPoints', 'CompletedTaskStudentCount', 'AssignedTaskStudentCount', 'ReachedGift', 'RedeemedGift', 'student_subjects', 'student', 'PunishmentCount', 'total_post_point', 'total_negative_point', 'target', 'current_point'));
        }

        abort(403);
    }

    private function visibleStudentSessionQuery(int $studentId): Builder
    {
        return ClassSession::query()
            ->where(fn (Builder $query) => $this->visibleStudentSessionConstraints($query, $studentId));
    }

    private function ensureVisibleTaskPivots(int $studentId): void
    {
        $taskIds = SessionTask::query()
            ->whereHas('classSession', fn (Builder $query) => $this->visibleStudentSessionConstraints($query, $studentId))
            ->pluck('id')
            ->map(fn (mixed $taskId): int => (int) $taskId)
            ->values();

        if ($taskIds->isEmpty()) {
            return;
        }

        $existingTaskIds = SessionTaskStudent::query()
            ->where('student_id', $studentId)
            ->whereIn('session_task_id', $taskIds->all())
            ->pluck('session_task_id')
            ->map(fn (mixed $taskId): int => (int) $taskId)
            ->all();

        $missingTaskIds = $taskIds->diff($existingTaskIds)->values();

        if ($missingTaskIds->isNotEmpty()) {
            $rows = $missingTaskIds->map(fn (int $taskId): array => [
                'session_task_id' => $taskId,
                'student_id' => $studentId,
                'student_points' => null,
                'submitted_at' => null,
                'assign_to_all' => 'custom',
                'status' => 'assigned',
                'flag' => null,
            ])->all();

            SessionTaskStudent::query()->insertOrIgnore($rows);
        }

        SessionTaskStudent::query()
            ->where('student_id', $studentId)
            ->whereIn('session_task_id', $taskIds->all())
            ->where(fn (Builder $query) => $query
                ->whereNull('status')
                ->orWhere('status', ''))
            ->update(['status' => 'assigned']);
    }

    private function repairEmptyDifferentiatedTaskSessions(int $studentId): void
    {
        $sessions = ClassSession::query()
            ->where(fn (Builder $query) => $this->visibleStudentSessionConstraints($query, $studentId))
            ->whereNotNull('differentiated_task_id')
            ->whereDoesntHave('tasks')
            ->orderBy('id')
            ->limit(self::EMPTY_DT_SESSION_REPAIR_LIMIT)
            ->get(['id', 'student_id', 'differentiated_task_id', 'generated_for_date', 'date']);

        if ($sessions->isEmpty()) {
            return;
        }

        $assignmentService = app(DifferentiatedTaskAssignmentService::class);
        $snapshotWriter = app(DifferentiatedTaskSnapshotWriter::class);

        $sessions->each(function (ClassSession $session) use ($assignmentService, $snapshotWriter, $studentId): void {
            $taskId = (int) $session->differentiated_task_id;
            $generationDate = Carbon::parse($session->generated_for_date ?? $session->date)->startOfDay();

            if ($taskId <= 0) {
                return;
            }

            $assignment = $assignmentService->resolveEffectiveAssignment(
                $studentId,
                $taskId,
                $generationDate
            );

            if (! $assignment || ! $assignment->version || ! $assignment->version->hasMeaningfulContent()) {
                return;
            }

            $snapshotWriter->generateForStudent(
                $studentId,
                $taskId,
                (int) $assignment->version_id,
                (int) $assignment->id,
                $generationDate
            );
        });
    }

    private function visibleStudentSessionConstraints(Builder $query, int $studentId): void
    {
        $query->visibleToLearner($studentId)
            ->whereIn('class_subject_id', StudentsSubject::query()
                ->select('class_subject_id')
                ->where('student_id', $studentId)
                ->where('status', 'active'))
            ->whereHas('sessionMaterials', fn (Builder $materialsQuery) => $materialsQuery
                ->where('status', 'published'));
    }
}
