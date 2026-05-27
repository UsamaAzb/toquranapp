<?php

namespace App\Http\Controllers\Front\Student;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttachmentFile;
use App\Models\ClassSession;
use App\Models\ParentModel;
use App\Models\SessionTask;
use App\Models\SessionTaskStudent;
use App\Models\Student;
use App\Models\StudentGift;
use App\Models\StudentGiftPointsHistory;
use App\Models\StudentsSubject;
use App\Models\TeacherSubjectClass;
use App\Services\Library\LibraryResourceAccessService;
use App\Support\JourneyBackgrounds;
use App\Support\LifecycleGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JourneyController extends Controller
{
    private function handleDeniedLearnerLifecycle()
    {
        if (Auth::user()?->hasRole('parent')) {
            return redirect()
                ->route('parent.students')
                ->with('warning', LifecycleGate::NEUTRAL_MESSAGE);
        }

        abort(403, LifecycleGate::NEUTRAL_MESSAGE);
    }

    private function isStudentParentRouteUser(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();

        if ($user->getRoleNames()->diff(['student', 'parent'])->isNotEmpty()) {
            return false;
        }

        return $user->hasRole('student') || $user->hasRole('parent');
    }

    private function handleNonStudentParentRouteUser()
    {
        if (Auth::check()) {
            abort(403);
        }

        return redirect()->route('login');
    }

    protected function canUseEmbeddedOfficePreview(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! $host) {
            return false;
        }

        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return (bool) filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        return ! Str::endsWith($host, ['.test', '.local', '.localhost']);
    }

    protected function buildAttachmentAccessUrl(Request $request, int $sessionId, int $attachmentId, bool $download = false): string
    {
        $params = [
            'session' => $sessionId,
            'attachment' => $attachmentId,
        ];

        if ($request->filled('student_id')) {
            $params['student_id'] = (int) $request->student_id;
        }

        if ($download) {
            $params['download'] = 1;
        }

        return route('student.journey.attachment.file', $params);
    }

    protected function resolveStudentForLearner(Request $request): Student
    {
        $user = Auth::user();

        if ($user->hasRole('student')) {
            $studentId = Student::where('user_id', $user->id)->value('id');

            return Student::findOrFail($studentId);
        }

        $parentModel = ParentModel::where('user_id', $user->id)->firstOrFail();

        return $parentModel->students()->findOrFail((int) $request->student_id);
    }

    protected function resolveEnrollmentForSession(int $studentId, ClassSession $sessionModel): StudentsSubject
    {
        abort_if($sessionModel->student_id !== null && (int) $sessionModel->student_id !== $studentId, 403);

        $enrollment = StudentsSubject::query()
            ->where('student_id', $studentId)
            ->where('class_subject_id', $sessionModel->class_subject_id)
            ->where('status', 'active')
            ->first();

        abort_if(! $enrollment, 403);

        return $enrollment;
    }

    protected function resolveTeacherBoardContext(int $teacherSubjectClassId, int $studentId): TeacherSubjectClass
    {
        $teacherSubjectClass = TeacherSubjectClass::query()
            ->whereKey($teacherSubjectClassId)
            ->where('user_teacher_coteacher_id', Auth::id())
            ->availableForTeacher()
            ->firstOrFail();

        abort_unless(
            StudentsSubject::query()
                ->where('student_id', $studentId)
                ->where('class_subject_id', $teacherSubjectClass->class_subject_id)
                ->where('status', 'active')
                ->exists(),
            403
        );

        return $teacherSubjectClass;
    }

    /**
     * Display a listing of the student's classes.
     */
    public function index()
    {
        if ((Auth::check()) && (Auth::user()->hasRole('student'))) {

            $user = Auth::user();
            $user_id = $user->id;
            $student_id = Student::where('user_id', $user_id)->value('id');
            $lifecycleGate = LifecycleGate::inspect((int) $student_id);
            if ($lifecycleGate->denied()) {
                return $this->handleDeniedLearnerLifecycle();
            }

            $academicYearId = AcademicYear::currentId();
            $reached_gift = StudentGift::where('status', 'reached')->where('student_id', $student_id)->where('academic_year_id', $academicYearId)->get();
            $pendingGift = StudentGift::where('status', 'pending')->where('student_id', $student_id)->where('academic_year_id', $academicYearId)->first();
            $student = Student::findOrFail($student_id);

            return view('student.journey.index', compact('reached_gift', 'pendingGift', 'student'));
        } else {
            return redirect()->route('login');
        }
    }

    public function board(Request $request)
    {
        // مفروض نجيب ال  auth
        // ونجيب  user_id  ومنها نجيب ال  student_id
        //         $student_points=StudentGiftPointsHistory::where('student_id',1)->first();
        // $reached_gift=StudentGift::where('status','reached')->get();
        if ((Auth::check()) && (Auth::user()->hasRole('teacher'))) {
            $student_id = (int) $request->student_id;
            $teacherSubjectClass = $this->resolveTeacherBoardContext((int) $request->teachersubjectid, $student_id);
            $teachersubjectid = $teacherSubjectClass->id;

            $academicYearId = AcademicYear::currentId();
            $pendingGift = StudentGift::where('status', 'pending')->where('student_id', $student_id)->where('academic_year_id', $academicYearId)->first();
            $student = Student::findOrFail($student_id);
            $lastReached = StudentGift::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->where('status', StudentGift::STATUS_REACHED)
                ->orderBy('points_required', 'desc')
                ->first();
            $show_bar = 'true';
            $breadcrumb_links = [
                'Reward System' => null,

            ];

            return view('student.journey.board_gifts', compact('show_bar', 'breadcrumb_links', 'lastReached', 'pendingGift', 'student', 'student_id', 'teachersubjectid'));
        }

        if ((Auth::check()) && ((Auth::user()->hasRole('student')) || (Auth::user()->hasRole('parent')))) {
            $user = Auth::user();
            $user_id = $user->id;

            if (Auth::user()->hasRole('student')) {
                $student_id = Student::where('user_id', $user_id)->value('id');
                $student = Student::findOrFail($student_id);
                $breadcrumb_links = [
                    'Reward System' => null,

                ];
            }

            if (Auth::user()->hasRole('parent')) {
                $student_id = $request->student_id;
                $parentModel = ParentModel::where('user_id', $user_id)->firstOrFail();
                $student = $parentModel->students()->findOrFail($student_id);
                $stu_name = $student->first_name;
                $breadcrumb_links = [
                    $stu_name => url('students'),
                    'Reward System' => null,

                ];

            }

            $student = Student::findOrFail($student_id);
            $lifecycleGate = LifecycleGate::inspect((int) $student_id);
            if ($lifecycleGate->denied()) {
                return $this->handleDeniedLearnerLifecycle();
            }

            $academicYearId = AcademicYear::currentId();
            $pendingGift = StudentGift::where('status', 'pending')->where('student_id', $student_id)->where('academic_year_id', $academicYearId)->first();
            $lastReached = StudentGift::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYearId)
                ->where('status', StudentGift::STATUS_REACHED)
                ->orderBy('points_required', 'desc')
                ->first();
            $show_bar = 'false';

            return view('student.journey.board_gifts', compact('show_bar', 'breadcrumb_links', 'lastReached', 'pendingGift', 'student'));
        }

        return redirect()->route('login');
    }

    public function go_journey(Request $request)
    {
        if ($this->isStudentParentRouteUser()) {

            $sessionId = (int) $request->sessionId;
            $sessionModel = ClassSession::with('subject')->findOrFail($sessionId);
            $subject_title = $sessionModel?->subject?->title ?? 'Subject';
            $session_title = $sessionModel?->title ?? 'Session';

            //  $session_title = Str::limit($session_title, 10, '...');
            $show_bar = 'true';

            $student = $this->resolveStudentForLearner($request);
            $student_id = $student->id;
            $lifecycleGate = LifecycleGate::inspect($student_id);
            if ($lifecycleGate->denied()) {
                return $this->handleDeniedLearnerLifecycle();
            }

            $studentSubject = $this->resolveEnrollmentForSession($student_id, $sessionModel);
            $student_subject_id = $studentSubject->id;

            if (Auth::user()->hasRole('student')) {
                $sessionsUrl = url('student/classes/sessions/'.$student_subject_id);
                $breadcrumb_links = [
                    $subject_title => $sessionsUrl,
                    $session_title => $sessionsUrl,

                ];
            }

            if (Auth::user()->hasRole('parent')) {
                $stu_name = $student->first_name;
                $sessionsUrl = url('student/classes/sessions/'.$student_subject_id.'/'.$student_id);
                $breadcrumb_links = [
                    $stu_name => url('students'),
                    $subject_title => $sessionsUrl,
                    $session_title => $sessionsUrl,

                ];
            }

            $page = 'journey';
            $tasks = SessionTask::query()
                ->where('class_session_id', $sessionId)
                ->orderBy('sort')
                ->get(['id', 'class_session_id', 'title', 'description', 'sort', 'default_points']);

            $totalCount = $tasks->count();
            $completedCount = SessionTaskStudent::query()
                ->whereIn('session_task_id', $tasks->pluck('id'))
                ->where('student_id', $student_id)
                ->where('status', 'completed')
                ->count();

            return view('student.journey.show_journey', compact('totalCount', 'completedCount', 'page', 'sessionId', 'show_bar', 'breadcrumb_links', 'student', 'student_id'));

        } else {
            return $this->handleNonStudentParentRouteUser();
        }
    }

    public function show_attachment(Request $request, int $sessionId, int $attachmentId)
    {
        if ($this->isStudentParentRouteUser()) {
            $student = $this->resolveStudentForLearner($request);
            $student_id = $student->id;
            $lifecycleGate = LifecycleGate::inspect($student_id);
            if ($lifecycleGate->denied()) {
                return $this->handleDeniedLearnerLifecycle();
            }

            $sessionModel = ClassSession::with('subject')->findOrFail($sessionId);
            $studentSubject = $this->resolveEnrollmentForSession($student_id, $sessionModel);
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

            abort_unless(
                app(LibraryResourceAccessService::class)->canLearnerAccessAttachment(
                    Auth::user(),
                    (int) $student_id,
                    $sessionId,
                    $attachment
                ),
                403
            );

            $type = strtolower($attachment->type);
            $path = $attachment->path;

            $isExternal = in_array($type, ['link', 'youtube'], true);
            $externalUrl = $isExternal ? $this->trustedExternalAttachmentUrl((string) $path) : null;

            $fileUrl = $isExternal
                ? $externalUrl
                : $this->buildAttachmentAccessUrl($request, $sessionId, $attachment->id);
            $embedUrl = $type === 'youtube'
                ? Helpers::trustedVideoEmbedUrl((string) $path)
                : null;
            $downloadUrl = $isExternal
                ? null
                : $this->buildAttachmentAccessUrl($request, $sessionId, $attachment->id, true);

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $fileMimeType = $isExternal || blank($path)
                ? null
                : Storage::disk('public')->mimeType(ltrim($path, '/'));
            $canEmbedOfficePreview = in_array($ext, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'], true)
                && $this->canUseEmbeddedOfficePreview($fileUrl);
            $teachersubjectid = $sessionModel->teacher_subject_classes_id;

            $student_subject_id = $studentSubject->id;
            $subject_title = $sessionModel?->subject?->title ?? 'Subject';
            $session_title = $sessionModel?->title ?? 'Session';

            //  $session_title = Str::limit($session_title, 10, '...');
            $show_bar = 'true';

            $taskBreadcrumbLabel = Str::limit($attachment->task->title, 35, '...');

            $taskUrlParams = ['sessionId' => $sessionId];
            if (Auth::user()->hasRole('parent')) {
                $taskUrlParams['student_id'] = $student_id;
            }
            $taskUrl = route('student.tasks.journey', $taskUrlParams + [
                'task' => $attachment->task->id,
            ]);

            if (Auth::user()->hasRole('parent')) {
                $stu_name = $student->first_name;
                $sessionUrl = url('student/tasks/'.$sessionId.'/journey/'.$student_id);
                $breadcrumb_links = [
                    $stu_name => url('students'),
                    $subject_title => url('student/classes/sessions/'.$student_subject_id.'/'.$student_id),
                    $session_title => $sessionUrl,
                    $taskBreadcrumbLabel => $taskUrl,
                ];
            } else {
                $sessionUrl = url('student/tasks/'.$sessionId.'/journey');
                $breadcrumb_links = [
                    $subject_title => url('student/classes/sessions/'.$student_subject_id),
                    $session_title => $sessionUrl,
                    $taskBreadcrumbLabel => $taskUrl,
                ];
            }

            $taskIds = SessionTask::query()
                ->where('class_session_id', $sessionId)
                ->orderBy('sort')
                ->pluck('id');

            $totalCount = $taskIds->count();
            $completedCount = $taskIds->isEmpty()
                ? 0
                : SessionTaskStudent::query()
                    ->whereIn('session_task_id', $taskIds)
                    ->where('student_id', $student_id)
                    ->where('status', 'completed')
                    ->count();

            $academicYearId = AcademicYear::currentId();
            $pendingGift = StudentGift::where('status', 'pending')->where('student_id', $student_id)->where('academic_year_id', $academicYearId)->first();
            $lastReached = StudentGift::query()
                ->where('student_id', $student_id)
                ->where('academic_year_id', $academicYearId)
                ->where('status', StudentGift::STATUS_REACHED)
                ->orderBy('points_required', 'desc')
                ->first();

            $bgUrl = JourneyBackgrounds::currentUrl();

            return view('student.journey.attachment-show', [
                'attachment' => $attachment,
                'fileUrl' => $fileUrl,
                'embedUrl' => $embedUrl,
                'downloadUrl' => $downloadUrl,
                'type' => $type,
                'ext' => $ext,
                'fileMimeType' => $fileMimeType,
                'canEmbedOfficePreview' => $canEmbedOfficePreview,
                'teachersubjectid' => $teachersubjectid,
                'breadcrumb_links' => $breadcrumb_links,
                'show_bar' => $show_bar,
                'bgUrl' => $bgUrl,
                'taskUrl' => $taskUrl,
                'sessionUrl' => $sessionUrl,
                'completedCount' => $completedCount,
                'totalCount' => $totalCount,
                'pendingGift' => $pendingGift,
                'lastReached' => $lastReached,
                'student' => $student,
            ]);
        } else {
            return $this->handleNonStudentParentRouteUser();
        }
    }

    public function stream_attachment(Request $request, int $sessionId, int $attachmentId): BinaryFileResponse|RedirectResponse
    {
        if (! $this->isStudentParentRouteUser()) {
            return $this->handleNonStudentParentRouteUser();
        }

        $student = $this->resolveStudentForLearner($request);
        $lifecycleGate = LifecycleGate::inspect($student->id);
        if ($lifecycleGate->denied()) {
            return $this->handleDeniedLearnerLifecycle();
        }

        $sessionModel = ClassSession::findOrFail($sessionId);
        $this->resolveEnrollmentForSession($student->id, $sessionModel);

        $attachment = AttachmentFile::with('task.classSession')->findOrFail($attachmentId);

        if (
            ! $attachment->task ||
            ! $attachment->task->classSession ||
            $attachment->task->class_session_id != $sessionId
        ) {
            abort(404);
        }

        abort_unless(
            app(LibraryResourceAccessService::class)->canLearnerAccessAttachment(
                Auth::user(),
                (int) $student->id,
                $sessionId,
                $attachment
            ),
            403
        );

        abort_if(in_array(strtolower((string) $attachment->type), ['link', 'youtube'], true), 404);

        $path = ltrim((string) $attachment->path, '/');

        abort_if($path === '' || ! Storage::disk('public')->exists($path), 404);

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
