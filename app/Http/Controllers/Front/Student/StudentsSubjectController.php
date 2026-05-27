<?php

namespace App\Http\Controllers\Front\Student;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttachmentFile;
use App\Models\ClassModel;
use App\Models\ClassSession;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\StudentGift;
use App\Models\StudentsSubject;
use App\Services\DailySessionPublisher;
use App\Services\DifferentiatedTaskPublisher;
use App\Services\Library\LibraryResourceAccessService;
use App\Services\SeriesTaskPublisher;
use App\Support\BookingSubjectProvisioning;
use App\Support\LifecycleGate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class StudentsSubjectController extends Controller
{
    /**
     * Display a listing of the student's classes.
     */
    public function student_subjects(Request $request)
    {
        // Auth->user();
        // Classes are loaded via Auth::user()->studentClasses in the view
        if ((Auth::check()) && ((Auth::user()->hasRole('student')) || (Auth::user()->hasRole('parent')))) {
            [$student_id, $student, $isParentContext] = $this->resolveLearnerContext($request);

            $denial = $this->inactiveLifecycleResponse($student_id, $isParentContext);
            if ($denial) {
                return $denial;
            }

            $this->generateDueAutomationForStudent((int) $student_id);

            if (! $isParentContext) {
                $breadcrumb_links = [
                    'My Subjects' => null,

                ];
            }

            if ($isParentContext) {
                $stu_name = $student->first_name;
                $breadcrumb_links = [
                    $stu_name => route('parent.students'),
                    'Subjects' => null,
                ];
            }

            $student_subjects = StudentsSubject::where('student_id', $student_id)->where('status', 'active')->with('gradeLevelSubject.subject')
                ->get()
                ->each(function (StudentsSubject $studentSubject): void {
                    $studentSubject->setAttribute(
                        'subject_display',
                        BookingSubjectProvisioning::displayPayloadForStudentSubject($studentSubject)
                    );
                });
            $show_bar = 'true';

            $page = 'subject';

            return view('student.classes.index', compact('page', 'show_bar', 'breadcrumb_links', 'student_subjects', 'student_id', 'student'));
        }

        abort(403);
    }

    public function get_sessions(Request $request)
    {
        if ((Auth::check()) && ((Auth::user()->hasRole('student')) || (Auth::user()->hasRole('parent')))) {
            [$student_id, $student, $isParentContext] = $this->resolveLearnerContext($request);

            $denial = $this->inactiveLifecycleResponse($student_id, $isParentContext);
            if ($denial) {
                return $denial;
            }

            $this->generateDueAutomationForStudent((int) $student_id);

            $student_subject_id = $request->route('student_subject_id');
            $student_subject = StudentsSubject::query()
                ->whereKey($student_subject_id)
                ->where('student_id', $student_id)
                ->where('status', 'active')
                ->firstOrFail();

            if (! $isParentContext) {
                $breadcrumb_links = [
                    BookingSubjectProvisioning::displaySubjectShortName(
                        (int) $student_subject->gradeLevelSubject->subject_id,
                        $student_subject->gradeLevelSubject->subject?->title
                    ) => route('student.classes', ['student_id' => $student_id]),
                    'Tasks' => null,
                ];
            }

            if ($isParentContext) {
                $stu_name = $student->first_name;
                $breadcrumb_links = [
                    $stu_name => route('parent.students'),
                    BookingSubjectProvisioning::displaySubjectShortName(
                        (int) $student_subject->gradeLevelSubject->subject_id,
                        $student_subject->gradeLevelSubject->subject?->title
                    ) => route('student.classes', ['student_id' => $student_id]),
                    'Tasks' => null,
                ];
            }

            $class_subject_id = $student_subject->class_subject_id;
            $student_class_sessions = ClassSession::query()
                ->where('class_subject_id', $class_subject_id)
                ->visibleToLearner((int) $student_id)
                ->get();

            $academicYearId = AcademicYear::currentId();
            $pendingGift = StudentGift::where('status', 'pending')->where('student_id', $student_id)->where('academic_year_id', $academicYearId)->first();
            $student = Student::find($student_id);
            $lastReached = StudentGift::query()
                ->where('student_id', $student_id)
                ->where('academic_year_id', $academicYearId)
                ->where('status', StudentGift::STATUS_REACHED)
                ->orderBy('points_required', 'desc')
                ->first();

            // $breadcrumb_links=[
            //     "My Subjects" => url('student/classes'),
            //     $student_subject->gradeLevelSubject->subject->title =>null,
            //     "Tasks"=>null,
            //     ];

            $show_bar = 'true';

            return view('student.classes.class_sessions', compact('student_class_sessions', 'student_subject_id', 'student_id', 'lastReached', 'pendingGift', 'student', 'show_bar', 'student', 'breadcrumb_links'));
        }

        abort(403);
    }

    public function show_attachment(Request $request, int $sessionId, int $attachmentId)
    {
        if ((Auth::check()) && ((Auth::user()->hasRole('student')) || (Auth::user()->hasRole('parent')))) {
            [$student_id, $student, $isParentContext] = $this->resolveLearnerContext($request);

            $denial = $this->inactiveLifecycleResponse($student_id, $isParentContext);
            if ($denial) {
                return $denial;
            }

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

            $student_class_session = $this->findLearnerVisibleSession($sessionId, (int) $student_id);
            $teachersubjectid = $student_class_session->teacher_subject_classes_id;

            $sessionModel = $student_class_session->loadMissing('subject');
            $class_subject_id = $student_class_session->class_subject_id;

            $student_subject_id = StudentsSubject::where('class_subject_id', $class_subject_id)
                ->where('student_id', $student_id)
                ->where('status', 'active')
                ->value('id');
            if (! $student_subject_id) {
                abort(403);
            }
            if (! $sessionModel || ! $sessionModel->subject) {
                abort(404);
            }

            $type = strtolower($attachment->type);
            $path = $attachment->path;

            $isExternal = in_array($type, ['link', 'youtube'], true)
                || Str::startsWith((string) $path, ['http://', 'https://']);
            $externalUrl = $isExternal ? $this->trustedExternalAttachmentUrl((string) $path) : null;
            $fileAvailable = $isExternal || ($path && Storage::disk('public')->exists(ltrim((string) $path, '/')));

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
            $subject_title = BookingSubjectProvisioning::displaySubjectShortName(
                (int) $sessionModel->subject->id,
                $sessionModel->subject->title
            );
            $session_title = $sessionModel->title;

            //  $session_title = Str::limit($session_title, 10, '...');
            $show_bar = 'true';
            $subjectUrl = route('student.classes', ['student_id' => $student_id]);
            $sessionUrl = $this->buildSessionTasksUrl(
                (int) $student_subject_id,
                (int) $student_id,
                $isParentContext,
                $sessionId,
                (int) $attachment->task->id
            );
            $breadcrumb_links = [
                $subject_title => $subjectUrl,
                $session_title => $sessionUrl,

            ];

            return view('student.classes.attachment-show', [
                'attachment' => $attachment,
                'fileUrl' => $fileUrl,
                'embedUrl' => $embedUrl,
                'downloadUrl' => $downloadUrl,
                'type' => $type,
                'ext' => $ext,
                'teachersubjectid' => $teachersubjectid,
                'breadcrumb_links' => $breadcrumb_links,
                'show_bar' => $show_bar,
                'student' => $student,
                'sessionUrl' => $sessionUrl,
                'fileAvailable' => $fileAvailable,
            ]

            );
        }

        abort(403);
    }

    private function generateDueAutomationForStudent(int $studentId): void
    {
        $today = now(config('app.timezone'))->startOfDay();

        try {
            $dailySessionPublisher = app(DailySessionPublisher::class);

            if ($dailySessionPublisher->needsGenerationForStudent($studentId, $today)) {
                $dailySessionPublisher->generateForStudent($studentId, $today);
            }
        } catch (Throwable $exception) {
            Log::warning('Skipped Versioned Routine lazy generation during student page load.', [
                'student_id' => $studentId,
                'date' => $today->toDateString(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        try {
            $differentiatedTaskPublisher = app(DifferentiatedTaskPublisher::class);

            if ($differentiatedTaskPublisher->needsGenerationForStudent($studentId, $today)) {
                $differentiatedTaskPublisher->generateForStudent($studentId, $today);
            }
        } catch (Throwable $exception) {
            Log::warning('Skipped Differentiated Task lazy generation during student page load.', [
                'student_id' => $studentId,
                'date' => $today->toDateString(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }

        try {
            $seriesTaskPublisher = app(SeriesTaskPublisher::class);

            if ($seriesTaskPublisher->needsGenerationForStudent($studentId, $today)) {
                $seriesTaskPublisher->generateForStudent($studentId, $today);
            }
        } catch (Throwable $exception) {
            Log::warning('Skipped Series Task lazy generation during student page load.', [
                'student_id' => $studentId,
                'date' => $today->toDateString(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function stream_attachment(Request $request, int $sessionId, int $attachmentId): BinaryFileResponse|StreamedResponse|RedirectResponse
    {
        if (! ((Auth::check()) && ((Auth::user()->hasRole('student')) || (Auth::user()->hasRole('parent'))))) {
            abort(403);
        }

        [$student_id, , $isParentContext] = $this->resolveLearnerContext($request);

        $denial = $this->inactiveLifecycleResponse($student_id, $isParentContext);
        if ($denial) {
            return $denial;
        }

        $sessionModel = $this->findLearnerVisibleSession($sessionId, (int) $student_id);
        $student_subject_id = StudentsSubject::where('class_subject_id', $sessionModel->class_subject_id)
            ->where('student_id', $student_id)
            ->where('status', 'active')
            ->value('id');
        abort_unless($student_subject_id, 403);

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
                (int) $student_id,
                $sessionId,
                $attachment
            ),
            403
        );

        abort_if(in_array(strtolower((string) $attachment->type), ['link', 'youtube'], true), 404);

        $path = ltrim((string) $attachment->path, '/');
        abort_if($path === '' || ! Storage::disk('public')->exists($path), 404);

        $mimeType = Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';
        $downloadName = $this->safeDownloadFilename($path);

        if ($request->boolean('download')) {
            return $this->storageAttachmentDownloadResponse('public', $path, $downloadName, [
                'Content-Type' => $mimeType,
            ]);
        }

        return $this->storageInlineAttachmentResponse('public', $path, $downloadName, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function buildAttachmentAccessUrl(Request $request, int $sessionId, int $attachmentId, bool $download = false): string
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

        return route('student.sessions.attachment.file', $params);
    }

    private function buildSessionTasksUrl(
        int $studentSubjectId,
        int $studentId,
        bool $isParentContext,
        int $sessionId,
        ?int $taskId = null
    ): string {
        $url = $isParentContext
            ? route('student.sessions', ['student_subject_id' => $studentSubjectId, 'student_id' => $studentId])
            : route('student.sessions', ['student_subject_id' => $studentSubjectId]);

        $anchor = $taskId ? 'task-'.$taskId : 'session-'.$sessionId;

        return $url.'?'.http_build_query(['open_session' => $sessionId]).'#'.$anchor;
    }

    /**
     * @return array{0:int,1:Student,2:bool}
     */
    private function resolveLearnerContext(Request $request): array
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($user->hasRole('student')) {
            $studentId = Student::where('user_id', $user->id)->value('id');
            abort_unless($studentId, 403);

            return [(int) $studentId, Student::findOrFail($studentId), false];
        }

        if ($user->hasRole('parent')) {
            $studentId = $request->route('student_id')
                ?? $request->query('student_id')
                ?? $request->input('student_id');
            abort_unless($studentId, 403);

            $parentModel = ParentModel::where('user_id', $user->id)->firstOrFail();
            $student = $parentModel->students()->findOrFail((int) $studentId);

            return [(int) $student->id, $student, true];
        }

        abort(403);
    }

    private function findLearnerVisibleSession(int $sessionId, int $studentId): ClassSession
    {
        $session = ClassSession::query()
            ->whereKey($sessionId)
            ->visibleToLearner($studentId)
            ->first();

        abort_unless($session, 403);

        return $session;
    }

    private function inactiveLifecycleResponse(int $studentId, bool $isParentContext): ?RedirectResponse
    {
        $gate = LifecycleGate::inspect($studentId);

        if (! $gate->denied()) {
            return null;
        }

        if ($isParentContext) {
            return redirect()
                ->route('parent.students')
                ->with('warning', LifecycleGate::NEUTRAL_MESSAGE);
        }

        abort(403, LifecycleGate::NEUTRAL_MESSAGE);
    }

    /**
     * Display the specified class.
     */
    public function show(ClassModel $class)
    {
        // Check if the student is enrolled in this class
        if (! Auth::user()->studentClasses->contains($class)) {
            abort(403, 'Unauthorized action.');
        }

        $class->load(['grade', 'subjects', 'teachers']);

        return view('student.classes.show', compact('class'));
    }
}
