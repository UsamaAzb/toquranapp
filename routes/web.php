<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CalendarEventController;
use App\Http\Controllers\Admin\ClassesController;
use App\Http\Controllers\Admin\GiftController;
use App\Http\Controllers\Admin\GradeController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentGiftController;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\authentications\ForgotPasswordCover;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\LoginCover;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\RegisterCover;
use App\Http\Controllers\authentications\RegisterMultiSteps;
use App\Http\Controllers\authentications\ResetPasswordBasic;
use App\Http\Controllers\authentications\ResetPasswordCover;
use App\Http\Controllers\authentications\TwoStepsBasic;
use App\Http\Controllers\authentications\TwoStepsCover;
use App\Http\Controllers\authentications\VerifyEmailBasic;
use App\Http\Controllers\authentications\VerifyEmailCover;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\Front\BackgroundController;
use App\Http\Controllers\Front\CourseController;
use App\Http\Controllers\Front\GrammarController;
use App\Http\Controllers\Front\NoticeController;
use App\Http\Controllers\Front\Parent\DisciplinePointsController;
use App\Http\Controllers\Front\Parent\ParentStudentController;
use App\Http\Controllers\Front\Peer_coachController;
use App\Http\Controllers\Front\SatController;
use App\Http\Controllers\Front\SeriesController;
use App\Http\Controllers\Front\StoryController;
use App\Http\Controllers\Front\Student\JourneyController;
use App\Http\Controllers\Front\Student\StudentConsequenceAgreementController;
use App\Http\Controllers\Front\Student\StudentDisciplinePointsController;
use App\Http\Controllers\Front\Student\StudentsSubjectController;
use App\Http\Controllers\Front\Student\WorkplaceController;
use App\Http\Controllers\Front\Teacher\ClassController;
use App\Http\Controllers\Front\Teacher\ConsequenceAgreementController;
use App\Http\Controllers\Front\Teacher\DailySessionsController;
use App\Http\Controllers\Front\Teacher\DifferentiatedTasksController;
use App\Http\Controllers\Front\Teacher\GiveDisciplinePointsController;
use App\Http\Controllers\Front\Teacher\GeneralLibraryController;
use App\Http\Controllers\Front\Teacher\SeriesTasksController;
use App\Http\Controllers\Front\Teacher\TeacherJourneyController;
// use App\Http\Controllers\Hang_manController;

use App\Http\Controllers\Front\VideoController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\VocabularyAssignmentController;
use App\Http\Controllers\VocabularyGameController;
use App\Livewire\Student\Journey;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// routes/web.php

Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])
    ->withoutMiddleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
    ])
    ->name('pwa.manifest');
Route::get('/storage/gifts/{filename}', function (string $filename) {
    $path = 'gifts/'.$filename;

    abort_unless(Storage::disk('public')->exists($path), 404);

    return response()->file(Storage::disk('public')->path($path));
})->where('filename', '[A-Za-z0-9._-]+')->name('public.gifts.file');

// Route::get('/home', function () {
//     return view('student.app-academy-course');
// });
Route::get('/homebar1', function () {
    return view('student.app-logistics-dashboardold');
});
Route::get('/homebar2', function () {
    return view('student.app-logistics-dashboard');
});
Route::get('/home2', function () {
    return view('student.app-ecommerce-dashboard');
});

Route::get('/full-clear', function () {
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('clear-compiled');

    // Artisan::call('optimize');
    return 'All caches cleared and optimized.';
});
Route::get('/', function () {
    return redirect('/login');
});
// Main Page Route
// Route::get('/', [HomePage::class, 'index'])->name('pages-home');
Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');

// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

// authentication
// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/login-cover', [LoginCover::class, 'index'])->name('auth-login-cover');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/register-cover', [RegisterCover::class, 'index'])->name('auth-register-cover');
Route::get('/auth/register-multisteps', [RegisterMultiSteps::class, 'index'])->name('auth-register-multisteps');
Route::get('/auth/verify-email-basic', [VerifyEmailBasic::class, 'index'])->name('auth-verify-email-basic');
Route::get('/auth/verify-email-cover', [VerifyEmailCover::class, 'index'])->name('auth-verify-email-cover');
Route::get('/auth/reset-password-basic', [ResetPasswordBasic::class, 'index'])->name('auth-reset-password-basic');
Route::get('/auth/reset-password-cover', [ResetPasswordCover::class, 'index'])->name('auth-reset-password-cover');
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-forgot-password-basic');
Route::get('/auth/forgot-password-cover', [ForgotPasswordCover::class, 'index'])->name('auth-forgot-password-cover');
Route::get('/auth/two-steps-basic', [TwoStepsBasic::class, 'index'])->name('auth-two-steps-basic');
Route::get('/auth/two-steps-cover', [TwoStepsCover::class, 'index'])->name('auth-two-steps-cover');

// Route::get('game/hangman', 'App\Http\Controllers\GameController::class,index')->middleware(['auth', 'role:teacher|student']);

// Route::post('hangman/begin', 'App\Http\Controllers\GameController::class,begin')->name('hangman.begin')->middleware(['auth', 'role:teacher|student']);
// Route::get('hangman/begin/{ct?}/{cid?}/{u?}/{les?}/{l?}', 'App\Http\Controllers\GameController::class,begin')->name('hangman.begin')->middleware(['auth', 'role:teacher|student']);

// Route::get('/hangman/start', 'App\Http\Controllers\GameController::class,start')->middleware(['auth', 'role:teacher|student']);
// Route::get('/hangman/guess', 'App\Http\Controllers\GameController::class,guess')->middleware(['auth', 'role:teacher|student']);
// Route::get('/hangman/hint', 'App\Http\Controllers\GameController::class,hint')->middleware(['auth', 'role:teacher|student']);

// Route::get('hangman/get-word','App\Http\Controllers\Hang_manController::class,getWord')->middleware(['auth', 'role:teacher']);

Route::middleware(['auth'])->group(function () {
    Route::prefix('vocabulary/games')->name('vocabulary.games.')->group(function (): void {
        Route::get('/', [VocabularyGameController::class, 'hub'])->name('hub')->middleware('role:teacher|student|parent|admin|super_admin|owner');
        Route::get('/source/{source}', [VocabularyGameController::class, 'playSource'])->name('source')->middleware('role:teacher|student|parent|admin|super_admin|owner');
        Route::get('/assignment/{assignment}', [VocabularyGameController::class, 'playAssignment'])->name('assignment')->middleware('role:teacher|student|parent|admin|super_admin|owner');
    });

    Route::middleware('role:teacher|admin|super_admin|owner')
        ->prefix('teacher/vocabulary/games')
        ->name('teacher.vocabulary.games.')
        ->group(function (): void {
            Route::get('/launch', [VocabularyGameController::class, 'teacherLauncher'])->name('launch');
            Route::post('/custom', [VocabularyGameController::class, 'playCustom'])->name('custom');
            Route::get('/custom/{token}', [VocabularyGameController::class, 'playCustomSession'])->name('custom.play');
            Route::post('/assignments', [VocabularyAssignmentController::class, 'store'])->name('assignments.store');
        });
});

Route::middleware(['auth', 'role:teacher|student|parent|admin|super_admin|owner'])->group(function () {
    // Legacy Floatie compatibility routes
    Route::get('game/hangman', [GameController::class, 'index'])
        ->name('hangman.index');

    // Begin Game
    Route::post('hangman/begin', [GameController::class, 'begin'])
        ->name('hangman.begin');

    Route::get('hangman/begin/{ct?}/{cid?}/{u?}/{les?}/{l?}', [GameController::class, 'begin']);

    // Game Actions
    Route::get('hangman/start', [GameController::class, 'start'])
        ->name('hangman.start');

    Route::get('hangman/guess', [GameController::class, 'guess'])
        ->name('hangman.guess');

    Route::get('hangman/hint', [GameController::class, 'hint'])
        ->name('hangman.hint');
});

// get word (Teachers only)
// Route::get('hangman/get-word', [Hang_manController::class, 'getWord'])
//     ->middleware(['auth', 'role:teacher'])
//     ->name('hangman.word');

Route::prefix('admin/families')
    ->middleware(['auth', 'role:admin|super_admin|customer_support'])
    ->name('admin.')
    ->group(function (): void {
        Route::get('{parent}', '\\'.\App\Livewire\Admin\Families\FamilyWorkspace::class)
            ->name('families.show');
    });

Route::prefix('admin/bookings')
    ->middleware(['auth', 'role:admin|super_admin|customer_support'])
    ->name('admin.bookings.')
    ->group(function (): void {
        Route::get('transferred', '\\'.\App\Livewire\Admin\Booking\TransferredChildren::class)
            ->name('transferred');
    });

Route::namespace('App\Http\Controllers\Admin')->middleware(['auth', 'role:admin|super_admin'])->prefix('admin')->group(function () {
    $retiredBookingEndpointResponse = static function (): \Illuminate\Http\JsonResponse {
        return response()->json([
            'message' => 'Legacy booking DataTables endpoints have been retired. Use the Livewire booking admin.',
        ], 410);
    };

    Route::prefix('bookings')->name('admin.bookings.')->group(function () {
        Route::get('/', '\\'.\App\Livewire\Admin\Booking\BookingList::class)
            ->name('livewire');

        Route::get('children/{bookingChild}', '\\'.\App\Livewire\Admin\Booking\BookingChildEdit::class)
            ->name('children.edit');

        Route::get('{booking}/parent', '\\'.\App\Livewire\Admin\Booking\BookingParentEdit::class)
            ->name('parent.edit');

        Route::get('intake-review', '\\'.\App\Livewire\Admin\Booking\IntakeReviewQueue::class)
            ->name('intake-review');

        Route::post('intake', [BookingController::class, 'storeIntake'])
            ->name('intake.store');

        Route::get('legacy', fn () => redirect()->route('admin.bookings.livewire'))
            ->name('legacy');
    });

    Route::get('booking', fn () => redirect()->route('admin.bookings.livewire'))
        ->name('admin.booking');

    Route::get('staff', '\\'.\App\Livewire\Admin\StaffUsers::class)
        ->middleware('role:super_admin')
        ->name('admin.staff.index');

    Route::get('teacher-class-assignments', '\\'.\App\Livewire\Admin\TeacherClassAssignments::class)
        ->middleware('role:super_admin|admin')
        ->name('admin.teacher-class-assignments.index');

    Route::prefix('booking')->name('admin.booking.')->group(function () use ($retiredBookingEndpointResponse) {

        Route::get('data', $retiredBookingEndpointResponse)
            ->name('data');

        Route::get('show/{id}', $retiredBookingEndpointResponse)
            ->name('show');

        Route::delete('{booking}', $retiredBookingEndpointResponse)
            ->name('destroy');

        Route::get('{booking}/json', $retiredBookingEndpointResponse)
            ->name('showJson');

        Route::put('update/{booking}', $retiredBookingEndpointResponse)
            ->name('update');

        Route::put('{booking}/children/{bookingChild}', $retiredBookingEndpointResponse)
            ->name('children.update');

        Route::post('{booking}/transfer', $retiredBookingEndpointResponse)
            ->name('transfer');

        Route::post('{booking}/children/{bookingChild}/transfer', $retiredBookingEndpointResponse)
            ->name('children.transfer');
    });

    Route::get('/calendar', [CalendarEventController::class, 'view'])
        ->name('admin.calendar.view');

    // JSON Endpoints لواجهة FullCalendar
    Route::get('/calendar/events', [CalendarEventController::class, 'index']);
    Route::post('/calendar/events', [CalendarEventController::class, 'store']);
    Route::put('/calendar/events/{event}', [CalendarEventController::class, 'update']);
    Route::delete('/calendar/events/{event}', [CalendarEventController::class, 'destroy']);

    Route::get('/calendar/guests', [CalendarEventController::class, 'guests'])
        ->name('calendar.guests');
    Route::resource('gifts', GiftController::class);
    Route::get('gifts/{gift}/check-before-delete', [GiftController::class, 'checkBeforeDelete'])
        ->name('gifts.checkBeforeDelete');
    // Route::patch('gifts/{gift}/toggle', [GiftController::class, 'toggle'])
    // ->name('gifts.toggle');

    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/account/{id}', [StudentController::class, 'get_account'])->name('admin.students.account');
    Route::put(
        '/students/{student}/modal-update',
        [StudentController::class, 'updateFromModal']
    )->name('admin.students.modal-update');

    Route::get('/students/security/{id}', [StudentController::class, 'show_security'])->name('admin.students.security');
    Route::get('/students/reward-system/{id}', [StudentGiftController::class, 'show_reward'])->name('admin.students.show_reward');
    Route::post('/student-gifts/reorder', [StudentGiftController::class, 'reorder'])
        ->name('admin.student-gifts.reorder');
    Route::post('/student-gifts/bulk-interval', [StudentGiftController::class, 'bulkInterval'])
        ->name('admin.student-gifts.bulk-interval');
    Route::post('/student-gifts/reward-privacy', [StudentGiftController::class, 'updatePrivacy'])
        ->name('admin.student-gifts.reward-privacy');
});

Route::prefix('student')->group(function () {

    //       Route::get('/workspace', function () {
    //     return view('student.workspace');
    // });
    Route::get('/workplace/{student_id?}', [WorkplaceController::class, 'index'])->middleware(['auth', 'role:student|parent'])->name('student.workplace');

    Route::get('/journey', [JourneyController::class, 'index'])->middleware(['auth', 'role:student|teacher|parent']);
    Route::get('/journey/board/{student_id?}/{teachersubjectid?}', [JourneyController::class, 'board'])
        ->middleware(['auth', 'role:student|teacher|parent'])
        ->name('student.journey.board');

    Route::get('/classes/{student_id?}', [StudentsSubjectController::class, 'student_subjects'])->name('student.classes')->middleware(['auth', 'role:student|parent']);
    Route::get('/classes/sessions/{student_subject_id}/{student_id?}', [StudentsSubjectController::class, 'get_sessions'])->name('student.sessions')->middleware(['auth', 'role:student|parent']);
    Route::get('/consequence-agreement/{student_id}/{teachersubjectid?}', [StudentConsequenceAgreementController::class, 'get_agreement'])->name('student.get_agreement');
    Route::get('/reward-discpline/{student_id}/{teachersubjectid?}', [StudentDisciplinePointsController::class, 'get_discipline_points'])->name('student.discipline-points');

    //  App\Livewire\Student\Journey
    //   Route::get('/tasks/{sessionId}/journey', Journey::class)
    //       ->name('student.tasks.journey');

    Route::get('/tasks/{sessionId}/journey/{student_id?}', [JourneyController::class, 'go_journey'])
        ->name('student.tasks.journey');

    Route::get('/tasks/{session}/journey/file/{attachment}/content', [JourneyController::class, 'stream_attachment'])
        ->name('student.journey.attachment.file');

    Route::get('/tasks/{session}/journey/file/{attachment}', [JourneyController::class, 'show_attachment'])->name('student.journey.attachment.show');

    Route::get('/classes/sessions/{session}/file/{attachment}/content', [StudentsSubjectController::class, 'stream_attachment'])->name('student.sessions.attachment.file');
    Route::get('/classes/sessions/{session}/file/{attachment}', [StudentsSubjectController::class, 'show_attachment'])->name('student.sessions.attachment.show');
});

// teacher auth
Route::prefix('teacher')->group(function () {

    Route::get('/classes', [ClassController::class, 'get_classes'])->name('teacher.classes')->middleware(['auth', 'role:teacher']);
    Route::get('/students/{student}/subjects/{subject}/task-approvals', [ClassController::class, 'taskApprovals'])
        ->middleware(['auth', 'role:teacher'])
        ->name('teacher.task-approvals');
    Route::post('/classes/{id}/change-status', [ClassController::class, 'change_status'])->name('teacher.change-status')->middleware(['auth', 'role:teacher']);

    Route::get('/classes/sessions/{teachersubjectid}', [ClassController::class, 'get_sessions'])->name('teacher.sessions')->middleware(['auth', 'role:teacher|parent']);
    Route::get('/classes/sessions/{session}/file/{attachment}/content', [ClassController::class, 'stream_attachment'])->name('teacher.sessions.attachment.file')->middleware(['auth', 'role:teacher|parent']);
    Route::get('/classes/sessions/{session}/file/{attachment}', [ClassController::class, 'show_attachment'])->name('teacher.sessions.attachment.show')->middleware(['auth', 'role:teacher|parent']);

    Route::get('/consequence-agreement/{student_id}/{teachersubjectid?}', [ConsequenceAgreementController::class, 'get_agreement'])->name('teacher.get_agreement')->middleware(['auth', 'role:teacher']);

    Route::get('/reward-discpline/{student_id}/{teachersubjectid?}', [GiveDisciplinePointsController::class, 'index'])->name('teacher.reward-discpline')->middleware(['auth', 'role:teacher']);
    Route::get('/journey', [TeacherJourneyController::class, 'index'])->middleware(['auth', 'role:teacher']);
    Route::get('/journey/board/{student_id?}/{teachersubjectid?}', [TeacherJourneyController::class, 'board'])->middleware(['auth', 'role:teacher']);
});

//   for parent account
Route::get('/students', [ParentStudentController::class, 'index'])->middleware(['auth', 'role:parent'])->name('parent.students');
Route::get('/students/{student}/task-approvals', [ParentStudentController::class, 'taskApprovals'])
    ->middleware(['auth', 'role:parent'])
    ->name('parent.task-approvals');
Route::get('parent/reward-discpline/{student_id}/', [DisciplinePointsController::class, 'index'])
    ->middleware(['auth', 'role:parent'])
    ->name('parent.reward-discpline');

// Academic manager auth
// Route::prefix('academic-manager')->middleware(['auth', 'role:academic -manager'])->group(function(){
//       Route::get('/allteachers',[ClassController::class, 'get_teachers'])->name('academic-manager.allteachers');
// });
// teacherusama library
Route::middleware(['auth'])->group(function () {
    // in teacher controller

    Route::get('{auth_role}/daily-sessions/subjects', [DailySessionsController::class, 'get_subjects'])->name('daily-sessions.get_subjects')->middleware(['role:teacher']);
    Route::get('{auth_role}/daily-sessions/{subject}/sessions', [DailySessionsController::class, 'get_sessions'])->name('daily-sessions.get_sessions')->middleware(['role:teacher']);
    Route::get('daily-sessions/{dailySession}/file/{attachment}/content', [DailySessionsController::class, 'stream_attachment'])->name('daily-sessions.attachment.file')->middleware(['role:teacher']);
    Route::get('daily-sessions/{dailySession}/file/{attachment}', [DailySessionsController::class, 'show_attachment'])->name('daily-sessions.attachment.show')->middleware(['role:teacher']);
    Route::get('daily-sessions/templates/{template}/file/{attachment}/content', [DailySessionsController::class, 'stream_template_attachment'])->name('daily-sessions.template-attachment.file')->middleware(['role:teacher']);
    Route::get('daily-sessions/templates/{template}/file/{attachment}', [DailySessionsController::class, 'show_template_attachment'])->name('daily-sessions.template-attachment.show')->middleware(['role:teacher']);
    Route::get('{auth_role}/differentiated-tasks/subjects', [DifferentiatedTasksController::class, 'get_subjects'])->name('differentiated-tasks.get_subjects')->middleware(['role:teacher']);
    Route::get('{auth_role}/differentiated-tasks/{subject}/tasks', [DifferentiatedTasksController::class, 'get_tasks'])->name('differentiated-tasks.get_tasks')->middleware(['role:teacher']);
    Route::get('differentiated-tasks/{task}/file/{attachment}/content', [DifferentiatedTasksController::class, 'stream_attachment'])->name('differentiated-tasks.attachment.file')->middleware(['role:teacher']);
    Route::get('differentiated-tasks/{task}/file/{attachment}', [DifferentiatedTasksController::class, 'show_attachment'])->name('differentiated-tasks.attachment.show')->middleware(['role:teacher']);
    Route::get('{auth_role}/series-tasks/subjects', [SeriesTasksController::class, 'subjects'])->name('series-tasks.subjects')->middleware(['role:teacher']);
    Route::get('{auth_role}/series-tasks/{subject}/tasks', [SeriesTasksController::class, 'board'])->name('series-tasks.board')->middleware(['role:teacher']);

    Route::get('admin/library', [GeneralLibraryController::class, 'index'])->name('admin.library.index')->middleware(['role:admin|super_admin']);
    Route::get('teacher/library', [GeneralLibraryController::class, 'index'])->name('teacher.get_library')->middleware(['role:admin|super_admin|teacher']);
    Route::get('teacher/library/manage', [GeneralLibraryController::class, 'index'])->name('teacher.library.manage')->middleware(['role:admin|super_admin|teacher']);
    Route::post('teacher/library/folders', [GeneralLibraryController::class, 'storeFolder'])->name('teacher.general-library.folders.store')->middleware(['role:admin|super_admin|teacher']);
    Route::patch('teacher/library/reorder', [GeneralLibraryController::class, 'reorderPageItems'])->name('teacher.general-library.items.reorder')->middleware(['role:admin|super_admin|teacher']);
    Route::patch('teacher/library/folders/{folder}', [GeneralLibraryController::class, 'updateFolder'])->name('teacher.general-library.folders.update')->middleware(['role:admin|super_admin|teacher']);
    Route::patch('teacher/library/folders/{folder}/archive', [GeneralLibraryController::class, 'archiveFolder'])->name('teacher.general-library.folders.archive')->middleware(['role:admin|super_admin|teacher']);
    Route::delete('teacher/library/folders/{folder}', [GeneralLibraryController::class, 'deleteFolder'])->name('teacher.general-library.folders.delete')->middleware(['role:admin|super_admin|teacher']);
    Route::post('teacher/library/resources/upload-temp', [GeneralLibraryController::class, 'uploadTemporaryResources'])->name('teacher.general-library.resources.upload-temp')->middleware(['role:admin|super_admin|teacher']);
    Route::delete('teacher/library/resources/upload-temp', [GeneralLibraryController::class, 'deleteTemporaryResources'])->name('teacher.general-library.resources.upload-temp.delete')->middleware(['role:admin|super_admin|teacher']);
    Route::post('teacher/library/resources', [GeneralLibraryController::class, 'storeResource'])->name('teacher.general-library.resources.store')->middleware(['role:admin|super_admin|teacher']);
    Route::patch('teacher/library/resources/{resource}', [GeneralLibraryController::class, 'updateResource'])->name('teacher.general-library.resources.update')->middleware(['role:admin|super_admin|teacher']);
    Route::patch('teacher/library/resources/{resource}/archive', [GeneralLibraryController::class, 'archiveResource'])->name('teacher.general-library.resources.archive')->middleware(['role:admin|super_admin|teacher']);
    Route::delete('teacher/library/resources/{resource}', [GeneralLibraryController::class, 'deleteResource'])->name('teacher.general-library.resources.delete')->middleware(['role:admin|super_admin|teacher']);
    Route::get('teacher/library/resources/{resource}/open', [GeneralLibraryController::class, 'openResource'])->name('teacher.general-library.resources.open')->middleware(['role:admin|super_admin|teacher']);
    Route::get('teacher/library/resources/{resource}/file', [GeneralLibraryController::class, 'streamResourceFile'])->name('teacher.general-library.resources.file')->middleware(['role:admin|super_admin|teacher']);
    Route::get('course/sat', [SatController::class, 'index'])->name('front.sat.index')->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);
    Route::get('course/sat/{slug1}/{slug2}', [SatController::class, 'get_desc'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);
    Route::get('course/grammar', [GrammarController::class, 'index'])->name('front.grammar.index')->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);

    Route::get('course/grammar/{slug1}/{slug2}', [GrammarController::class, 'get_desc'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);

    Route::get('course/notice-note', [NoticeController::class, 'index'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);
    Route::get('notice-note/{slug}', [NoticeController::class, 'get_desc'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);

    Route::get('course/background', [BackgroundController::class, 'index'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);
    Route::get('background/{slug}', [BackgroundController::class, 'get_desc'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);

    Route::get('course/peer-coach', [Peer_coachController::class, 'index'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);
    Route::get('peer-coach/{slug1}/{slug2}', [Peer_coachController::class, 'get_desc'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);

    Route::get('course/levels', [CourseController::class, 'get_levels'])->name('front.course')->middleware(['auth', 'role:student']);
    Route::get('course/level/{id?}', [CourseController::class, 'get_units'])->name('front.course.level')->middleware(['auth', 'role:student']);
    Route::get('course/radio', function () {
        $breadcrumb_links = [];

        if (auth()->user()?->hasAnyRole(['admin', 'teacher'])) {
            $breadcrumb_links['Library'] = route('teacher.get_library');
        }

        $breadcrumb_links['Radio'] = null;

        return view('front.radio', compact('breadcrumb_links'));
    })->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);

    Route::get('videos/ted', [VideoController::class, 'show_ted'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);
    Route::get('videos/court', [VideoController::class, 'show_court'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);
    Route::get('reading/listen-read', [StoryController::class, 'index'])->name('legacy-library.listen-read.index')->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);
    Route::get('reading/listen-read/{slug1}/{slug2}', [StoryController::class, 'get_chapters'])->name('legacy-library.listen-read.chapter')->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);

    Route::get('tutriols/level-up', [StoryController::class, 'get_tutriols_level'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);
    Route::get('tutriols/level-up/{slug}', [StoryController::class, 'get_tutriols_lesson'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);

    Route::get('tv_series/avatar', [SeriesController::class, 'tv_series_avatar'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);
    Route::get('tv_series/avatar/{slug1}/{slug2}', [SeriesController::class, 'get_tv_series_avatar'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);

    Route::get('tv_series/friends', [SeriesController::class, 'tv_series_friends'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);
    Route::get('tv_series/friends/{slug1}/{slug2}', [SeriesController::class, 'get_tv_series_friends'])->middleware(['auth', 'role:teacher|admin|super_admin', 'legacy_library_access']);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard');
    Route::get('/grades/index', [GradeController::class, 'index'])->name('grades.index');
    Route::get('/grades/show/{grade}', [GradeController::class, 'show'])->name('grades.show');

    Route::get('/grades/create', [GradeController::class, 'create'])->name('grades.create');
    Route::get('/grades/edit/{grade}', [GradeController::class, 'edit'])->name('grades.edit');

    Route::post('/grades/store', [GradeController::class, 'store'])->name('grades.store');
    Route::put('/grades/update/{grade}', [GradeController::class, 'update'])->name('grades.update');
    Route::delete('/grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');
});
// Route::middleware(['auth','role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
//     // Grades
//     Route::resource('grades', GradeController::class);
//
//     // Classes
//     Route::resource('classes', ClassesController::class);
// });
