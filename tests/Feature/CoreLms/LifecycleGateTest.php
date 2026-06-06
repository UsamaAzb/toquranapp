<?php

namespace Tests\Feature\CoreLms;

use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Http\Controllers\Admin\StudentGiftController;
use App\Http\Controllers\Front\Student\JourneyController;
use App\Http\Controllers\Front\Student\WorkplaceController;
use App\Livewire\Student\AttachmentStudyViewer;
use App\Livewire\Student\Journey;
use App\Livewire\Student\SessionsBoard;
use App\Livewire\Teacher\AddBehaviorButton;
use App\Livewire\Ui\GiftBoard;
use App\Livewire\Ui\PointsProgress;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\StudentGift;
use App\Models\User;
use App\Services\DailySessionPublisher;
use App\Support\LifecycleGate;
use App\Support\RewardGiftVisibility;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Livewire;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LifecycleGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createLifecycleGateTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin', 'student', 'parent', 'teacher'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_suspended_student_cannot_open_workplace_page(): void
    {
        [$studentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Suspended->value,
        );

        $publisher = Mockery::mock(DailySessionPublisher::class);
        $publisher->shouldNotReceive('needsGenerationForStudent');
        $publisher->shouldNotReceive('generateForStudent');
        $this->app->instance(DailySessionPublisher::class, $publisher);

        $this->actingAs($studentUser);

        $this->get(route('student.workplace'))
            ->assertForbidden();
    }

    public function test_archived_student_cannot_open_journey_board(): void
    {
        [$studentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Archived->value,
        );

        $this->actingAs($studentUser);

        $this->get('/student/journey/board')
            ->assertForbidden();
    }

    public function test_archived_student_cannot_open_journey_index(): void
    {
        [$studentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Archived->value,
        );

        $this->actingAs($studentUser);

        $this->get('/student/journey')
            ->assertForbidden();
    }

    public function test_active_student_with_suspended_parent_cannot_open_workplace_or_journey(): void
    {
        [$studentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Suspended->value,
            ChildAccountStatus::Active->value,
        );

        $publisher = Mockery::mock(DailySessionPublisher::class);
        $publisher->shouldNotReceive('needsGenerationForStudent');
        $publisher->shouldNotReceive('generateForStudent');
        $this->app->instance(DailySessionPublisher::class, $publisher);

        $this->actingAs($studentUser);

        $this->get(route('student.workplace'))
            ->assertForbidden();

        $this->get('/student/journey/board')
            ->assertForbidden();
    }

    public function test_active_student_and_active_parent_workplace_context_still_resolves(): void
    {
        [, $student, , $parentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );

        $publisher = Mockery::mock(DailySessionPublisher::class);
        $publisher->shouldReceive('needsGenerationForStudent')
            ->once()
            ->withArgs(fn (int $studentId): bool => $studentId === $student->id)
            ->andReturn(false);
        $publisher->shouldNotReceive('generateForStudent');
        $this->app->instance(DailySessionPublisher::class, $publisher);

        $this->actingAs($parentUser);

        $request = Request::create('/student/workplace/'.$student->id, 'GET');
        $route = app('router')->getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);

        $response = app(WorkplaceController::class)->index($request);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame($student->id, $response->getData()['student']->id);
    }

    public function test_journey_attachment_deep_link_is_blocked_for_suspended_student(): void
    {
        [$studentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Suspended->value,
        );

        $this->actingAs($studentUser);

        $this->get(route('student.journey.attachment.show', [
            'session' => 999,
            'attachment' => 999,
        ]))->assertForbidden();
    }

    public function test_parent_context_lifecycle_denial_redirects_with_neutral_message(): void
    {
        [, $student, , $parentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Suspended->value,
            ChildAccountStatus::Active->value,
        );

        $this->actingAs($parentUser);

        $this->get(route('student.workplace', ['student_id' => $student->id]))
            ->assertRedirect(route('parent.students'))
            ->assertSessionHas('warning', LifecycleGate::NEUTRAL_MESSAGE);
    }

    public function test_parent_context_route_param_still_requires_own_linked_student(): void
    {
        [, , , $parentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        [, $otherStudent] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );

        $this->actingAs($parentUser);

        $this->get(route('student.workplace', ['student_id' => $otherStudent->id]))
            ->assertRedirect(route('parent.students'));
    }

    public function test_admin_parent_mixed_role_can_open_owned_child_journey_as_parent(): void
    {
        [, $student, , $parentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $parentUser->assignRole('admin');
        $this->createClassSessionAttachmentFixture($student);

        $this->actingAs($parentUser);

        $this->get(route('student.tasks.journey', [
            'sessionId' => 1,
            'student_id' => $student->id,
        ]))->assertOk();
    }

    public function test_journey_livewire_mount_aborts_for_suspended_student(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Suspended->value,
        );

        $this->actingAs($studentUser);

        Livewire::test(Journey::class, [
            'sessionId' => 1,
            'studentId' => $student->id,
        ])->assertForbidden();
    }

    public function test_teacher_journey_board_access_remains_exempt_from_learner_lifecycle_gate(): void
    {
        $teacherUser = User::factory()->create();
        $teacherUser->assignRole('teacher');

        [, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Suspended->value,
            ChildAccountStatus::Suspended->value,
        );
        $this->grantTeacherJourneyAccess($teacherUser, $student);

        $this->actingAs($teacherUser);

        $request = Request::create('/student/journey/board/'.$student->id.'/12', 'GET', [
            'student_id' => $student->id,
            'teachersubjectid' => 12,
        ]);

        $response = app(JourneyController::class)->board($request);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame($student->id, $response->getData()['student']->id);
    }

    public function test_teacher_parent_mixed_role_journey_board_access_uses_teacher_exemption(): void
    {
        $mixedRoleUser = User::factory()->create();
        $mixedRoleUser->assignRole(['teacher', 'parent']);

        ParentModel::create([
            'first_name' => 'Mixed Role Parent',
            'user_id' => $mixedRoleUser->id,
            'email' => $mixedRoleUser->email,
            'lifecycle_status' => FamilyLifecycleStatus::Suspended->value,
        ]);

        [, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Suspended->value,
            ChildAccountStatus::Suspended->value,
        );
        $this->grantTeacherJourneyAccess($mixedRoleUser, $student);

        $this->actingAs($mixedRoleUser);

        $request = Request::create('/student/journey/board/'.$student->id.'/12', 'GET', [
            'student_id' => $student->id,
            'teachersubjectid' => 12,
        ]);

        $response = app(JourneyController::class)->board($request);

        $this->assertInstanceOf(View::class, $response);
        $this->assertSame($student->id, $response->getData()['student']->id);
        $this->assertSame('true', $response->getData()['show_bar']);
    }

    public function test_teacher_journey_board_requires_an_owned_teacher_subject_assignment(): void
    {
        $teacherUser = User::factory()->create();
        $teacherUser->assignRole('teacher');
        $otherTeacher = User::factory()->create();
        $otherTeacher->assignRole('teacher');

        [, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->grantTeacherJourneyAccess($otherTeacher, $student);

        $this->actingAs($teacherUser);

        $this->get('/student/journey/board/'.$student->id.'/12?student_id='.$student->id.'&teachersubjectid=12')
            ->assertNotFound();
    }

    public function test_teacher_parent_mixed_role_cannot_use_student_task_journey_route_as_parent(): void
    {
        $mixedRoleUser = User::factory()->create();
        $mixedRoleUser->assignRole(['teacher', 'parent']);

        $this->actingAs($mixedRoleUser);

        $this->get(route('student.tasks.journey', [
            'sessionId' => 1,
            'student_id' => 123,
        ]))->assertForbidden();
    }

    public function test_teacher_parent_mixed_role_cannot_use_student_journey_attachment_route_as_parent(): void
    {
        $mixedRoleUser = User::factory()->create();
        $mixedRoleUser->assignRole(['teacher', 'parent']);

        $this->actingAs($mixedRoleUser);

        $this->get(route('student.journey.attachment.show', [
            'session' => 1,
            'attachment' => 1,
            'student_id' => 123,
        ]))->assertForbidden();
    }

    public function test_teacher_parent_mixed_role_cannot_stream_student_journey_attachment_as_parent(): void
    {
        $mixedRoleUser = User::factory()->create();
        $mixedRoleUser->assignRole(['teacher', 'parent']);

        $this->actingAs($mixedRoleUser);

        $this->get(route('student.journey.attachment.file', [
            'session' => 1,
            'attachment' => 1,
            'student_id' => 123,
        ]))->assertForbidden();
    }

    public function test_parent_add_behavior_button_is_disabled_when_lifecycle_gate_denies(): void
    {
        [, $student, , $parentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Suspended->value,
            ChildAccountStatus::Active->value,
        );

        $this->actingAs($parentUser);

        Livewire::test(AddBehaviorButton::class, [
            'studentId' => $student->id,
            'teacherSubjectClassesId' => 12,
        ])
            ->assertSet('disabled', true)
            ->assertSee(LifecycleGate::NEUTRAL_MESSAGE);
    }

    public function test_parent_reward_discipline_page_redirects_when_lifecycle_gate_denies(): void
    {
        [, $student, , $parentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Suspended->value,
            ChildAccountStatus::Active->value,
        );

        $this->actingAs($parentUser);

        $this->get('/parent/reward-discpline/'.$student->id)
            ->assertRedirect(route('parent.students'))
            ->assertSessionHas('warning', LifecycleGate::NEUTRAL_MESSAGE);
    }

    public function test_suspended_student_cannot_open_subject_or_session_pages(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Suspended->value,
        );

        $this->actingAs($studentUser);

        $this->get(route('student.classes'))
            ->assertForbidden();

        $this->get('/student/classes/sessions/1/'.$student->id)
            ->assertForbidden();
    }

    public function test_suspended_student_cannot_open_class_session_attachment_route(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Suspended->value,
        );

        $this->actingAs($studentUser);

        $this->get(route('student.sessions.attachment.show', [
            'session' => 1,
            'attachment' => 1,
            'student_id' => $student->id,
        ]))->assertForbidden();
    }

    public function test_active_student_class_session_attachment_uses_authenticated_stream_url(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createClassSessionAttachmentFixture($student);

        $this->actingAs($studentUser);

        $streamUrl = route('student.sessions.attachment.file', [
            'session' => 1,
            'attachment' => 86,
        ]);

        $this->get(route('student.sessions.attachment.show', [
            'session' => 1,
            'attachment' => 86,
        ]))
            ->assertOk()
            ->assertSee($streamUrl);

        $this->get($streamUrl)
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename=test-image.png');
    }

    public function test_parent_class_session_attachment_breadcrumb_preserves_child_context(): void
    {
        [, $student, , $parentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createClassSessionAttachmentFixture($student);

        $this->actingAs($parentUser);

        $response = $this->get(route('student.sessions.attachment.show', [
            'session' => 1,
            'attachment' => 85,
            'student_id' => $student->id,
        ]));

        $response->assertOk();

        $breadcrumbLinks = $response->viewData('breadcrumb_links');
        $sessionUrl = url('student/classes/sessions/42/'.$student->id).'?open_session=1#task-77';

        $this->assertSame(
            url('student/classes/'.$student->id),
            $breadcrumbLinks['Language and Literature']
        );
        $this->assertSame($sessionUrl, $breadcrumbLinks['Session Title']);
        $this->assertArrayNotHasKey('Test PDF', $breadcrumbLinks);
        $this->assertSame($sessionUrl, $response->viewData('sessionUrl'));
        $response->assertSee('Back to Session Tasks');
    }

    public function test_invalid_external_link_is_not_rendered_on_student_class_attachment_page(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createClassSessionAttachmentFixture($student);

        DB::table('attachment_files')->where('id', 85)->update([
            'type' => 'link',
            'path' => 'javascript:alert(1)',
            'title' => 'Unsafe Link',
        ]);

        $this->actingAs($studentUser);

        $this->get(route('student.sessions.attachment.show', [
            'session' => 1,
            'attachment' => 85,
        ]))
            ->assertOk()
            ->assertSee('This attachment link is unavailable.')
            ->assertDontSee('javascript:alert(1)', false);
    }

    public function test_sessions_board_opens_requested_session_and_dispatches_attachment_viewer(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createClassSessionAttachmentFixture($student);

        $this->actingAs($studentUser);

        Livewire::withQueryParams(['open_session' => 1])
            ->test(SessionsBoard::class, [
                'studentSubjectId' => 42,
                'studentId' => $student->id,
            ])
            ->assertSet('open', [1])
            ->assertSee('collapse-1')
            ->assertSee('openAttachmentStudyViewer(1, 77, 86)', false)
            ->assertDontSee('/storage/attachments/test-image.png', false)
            ->call('openAttachmentStudyViewer', 1, 77, 86)
            ->assertDispatched('open-attachment-study-viewer');
    }

    public function test_attachment_study_viewer_opens_authorized_session_attachment(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createClassSessionAttachmentFixture($student);

        $this->actingAs($studentUser);

        Livewire::test(AttachmentStudyViewer::class, [
            'studentId' => $student->id,
            'surface' => 'session',
        ])
            ->call('openViewer', 1, 77, 86)
            ->assertSet('open', true)
            ->assertSet('taskTitle', 'Session Task')
            ->assertSet('currentItem.title', 'Test Image')
            ->assertSee('Test Image')
            ->assertSee('wire:key="viewer-item-86-1-protected_file-', false)
            ->assertSee('Image zoom controls')
            ->assertSee('100%')
            ->assertSee(route('student.sessions.attachment.file', [
                'session' => 1,
                'attachment' => 86,
                'student_id' => $student->id,
            ]), false);
    }

    public function test_attachment_study_viewer_keeps_same_origin_legacy_link_in_task_context(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createClassSessionAttachmentFixture($student);

        DB::table('attachment_files')->where('id', 85)->update([
            'type' => 'link',
            'path' => url('/reading/listen-read?d=7'),
            'title' => 'Listen Read',
        ]);

        $this->actingAs($studentUser);

        Livewire::test(AttachmentStudyViewer::class, [
            'studentId' => $student->id,
            'surface' => 'session',
        ])
            ->call('openViewer', 1, 77, 85)
            ->assertSet('open', true)
            ->assertSet('currentItem.mode', 'legacy_same_origin_link')
            ->assertSee('Listen Read')
            ->assertSee('Loading...')
            ->assertSee('Still loading...')
            ->assertSee('sandbox="allow-same-origin allow-forms allow-scripts allow-popups allow-downloads"', false)
            ->assertSee('/reading/listen-read?d=7&amp;return_to=', false)
            ->assertSee('w14_viewer=attachment', false)
            ->assertDontSee('Open link')
            ->assertDontSee('target="_blank"', false);
    }

    public function test_attachment_study_viewer_keeps_ordinary_external_link_as_new_tab_card(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createClassSessionAttachmentFixture($student);

        DB::table('attachment_files')->where('id', 85)->update([
            'type' => 'link',
            'path' => 'https://example.com/resource',
            'title' => 'External Resource',
        ]);

        $this->actingAs($studentUser);

        Livewire::test(AttachmentStudyViewer::class, [
            'studentId' => $student->id,
            'surface' => 'session',
        ])
            ->call('openViewer', 1, 77, 85)
            ->assertSet('open', true)
            ->assertSet('currentItem.mode', 'external_link')
            ->assertSee('External Resource')
            ->assertSee('Open link')
            ->assertSee('target="_blank"', false)
            ->assertDontSee('<iframe', false);
    }

    public function test_attachment_study_viewer_keys_media_bodies_when_navigating_between_modes(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createClassSessionAttachmentFixture($student);

        Storage::disk('public')->put('test-lifecycle-gate-attachments/unit-one.mp4', 'video body');
        Storage::disk('public')->put('test-lifecycle-gate-attachments/reading.pdf', 'pdf body');

        DB::table('attachment_files')->where('id', 85)->update([
            'type' => 'file',
            'path' => 'test-lifecycle-gate-attachments/unit-one.mp4',
            'title' => 'Unit One Video',
        ]);

        DB::table('attachment_files')->where('id', 86)->update([
            'type' => 'file',
            'path' => 'test-lifecycle-gate-attachments/reading.pdf',
            'title' => 'Reading PDF',
        ]);

        DB::table('attachment_files')->insert([
            [
                'id' => 87,
                'session_task_id' => 77,
                'type' => 'link',
                'path' => url('/reading/listen-read?d=7'),
                'title' => 'Embedded Library Link',
            ],
            [
                'id' => 88,
                'session_task_id' => 77,
                'type' => 'link',
                'path' => 'https://example.com/resource',
                'title' => 'External Resource',
            ],
        ]);

        $this->actingAs($studentUser);

        Livewire::test(AttachmentStudyViewer::class, [
            'studentId' => $student->id,
            'surface' => 'session',
        ])
            ->call('openViewer', 1, 77, 85)
            ->assertSet('currentItem.id', 85)
            ->assertSet('currentItem.extension', 'mp4')
            ->assertSee('<video', false)
            ->assertSee('preload="metadata"', false)
            ->assertSee('wire:key="viewer-item-85-0-protected_file-', false)
            ->call('nextAttachment')
            ->assertSet('currentItem.id', 86)
            ->assertSet('currentItem.extension', 'pdf')
            ->assertSee('Reading PDF')
            ->assertSee('wire:key="viewer-item-86-1-protected_file-', false)
            ->assertDontSee('wire:key="viewer-item-85-0-protected_file-', false)
            ->call('nextAttachment')
            ->assertSet('currentItem.id', 87)
            ->assertSet('currentItem.mode', 'legacy_same_origin_link')
            ->assertSee('wire:key="viewer-item-87-2-legacy_same_origin_link-', false)
            ->assertSee('sandbox="allow-same-origin allow-forms allow-scripts allow-popups allow-downloads"', false)
            ->call('nextAttachment')
            ->assertSet('currentItem.id', 88)
            ->assertSet('currentItem.mode', 'external_link')
            ->assertSee('wire:key="viewer-item-88-3-external_link-', false)
            ->assertSee('Open link')
            ->call('closeViewer')
            ->assertDispatched('attachment-study-viewer-closed');
    }

    public function test_attachment_study_viewer_uses_concrete_background_pdf_instead_of_nested_wrapper(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createClassSessionAttachmentFixture($student);
        $this->ensureBackgroundTableExists();

        DB::table('background')->insert([
            'id' => 901,
            'title' => 'Historical Background - Apartheid',
            'slug' => 'background/apartheid',
            'parent_id' => 0,
            'pdf_link' => 'legacy/background/apartheid.pdf',
            'sort' => 1,
        ]);

        DB::table('attachment_files')->where('id', 85)->update([
            'type' => 'link',
            'path' => url('/background/apartheid'),
            'title' => 'Historical Background - Apartheid',
        ]);

        $this->actingAs($studentUser);

        Livewire::test(AttachmentStudyViewer::class, [
            'studentId' => $student->id,
            'surface' => 'session',
        ])
            ->call('openViewer', 1, 77, 85)
            ->assertSet('currentItem.mode', 'legacy_file')
            ->assertSet('currentItem.extension', 'pdf')
            ->assertSet('currentItem.content_url', asset('legacy/background/apartheid.pdf').'#toolbar=0')
            ->assertSee('wire:key="viewer-item-85-0-legacy_file-', false)
            ->assertSee(asset('legacy/background/apartheid.pdf').'#toolbar=0', false)
            ->assertDontSee('/background/apartheid?return_to=', false)
            ->assertDontSee('sandbox="allow-same-origin allow-forms allow-scripts allow-popups allow-downloads"', false);
    }

    public function test_attachment_study_viewer_orders_items_by_attachment_sort_order(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createClassSessionAttachmentFixture($student);

        DB::table('attachment_files')->where('id', 85)->update(['sort_order' => 20]);
        DB::table('attachment_files')->where('id', 86)->update(['sort_order' => 10]);

        $this->actingAs($studentUser);

        Livewire::test(AttachmentStudyViewer::class, [
            'studentId' => $student->id,
            'surface' => 'session',
        ])
            ->call('openViewer', 1, 77, 86)
            ->assertSet('currentIndex', 0)
            ->assertSet('currentItem.id', 86)
            ->call('nextAttachment')
            ->assertSet('currentIndex', 1)
            ->assertSet('currentItem.id', 85);
    }

    public function test_suspended_student_cannot_open_own_automated_task_session_or_attachment(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Suspended->value,
        );
        $this->createAutomatedClassSessionAttachmentFixture($student);

        $this->actingAs($studentUser);

        $this->get(route('student.tasks.journey', [
            'sessionId' => 1,
        ]))->assertForbidden();

        $this->get(route('student.sessions.attachment.show', [
            'session' => 1,
            'attachment' => 85,
        ]))->assertForbidden();

        $this->get(route('student.sessions.attachment.file', [
            'session' => 1,
            'attachment' => 85,
        ]))->assertForbidden();
    }

    public function test_automated_task_journey_completion_still_requires_a_valid_pin(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createAutomatedClassSessionAttachmentFixture($student);

        $this->actingAs($studentUser);

        Livewire::test(Journey::class, [
            'sessionId' => 1,
            'studentId' => $student->id,
        ])
            ->call('openCompleteModal', 77)
            ->set('pinInput', '0000')
            ->call('confirmTaskCompletionWithPin')
            ->assertSet('pinErrorMessage', 'Invalid PIN.');

        $this->assertDatabaseMissing('session_task_student', [
            'session_task_id' => 77,
            'student_id' => $student->id,
            'status' => 'completed',
        ]);
    }

    public function test_suspended_student_cannot_mount_sessions_board_or_points_progress(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Suspended->value,
        );

        $this->actingAs($studentUser);

        Livewire::test(SessionsBoard::class, [
            'studentSubjectId' => 1,
            'studentId' => $student->id,
        ])->assertForbidden();

        Livewire::test(PointsProgress::class, [
            'studentId' => $student->id,
        ])->assertForbidden();
    }

    public function test_teacher_and_admin_points_and_gift_components_still_load_for_suspended_student(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        [, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Suspended->value,
            ChildAccountStatus::Suspended->value,
        );

        StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => StudentGift::STATUS_PENDING,
        ]);

        $this->actingAs($teacher);

        Livewire::test(PointsProgress::class, [
            'studentId' => $student->id,
        ])->assertOk();

        Livewire::test(GiftBoard::class, [
            'student' => $student,
        ])->assertOk();

        $this->actingAs($admin);

        Livewire::test(PointsProgress::class, [
            'studentId' => $student->id,
        ])->assertOk();

        Livewire::test(GiftBoard::class, [
            'student' => $student,
        ])->assertOk();
    }

    public function test_gift_board_redeem_accepts_student_reward_pin(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createRewardPin($studentUser);

        $reachedGift = StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => StudentGift::STATUS_REACHED,
        ]);
        StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points_required' => 100,
            'status' => StudentGift::STATUS_PENDING,
        ]);

        Livewire::actingAs($studentUser)
            ->test(GiftBoard::class, [
                'student' => $student,
            ])
            ->call('openRedeemModal', $reachedGift->id)
            ->set('pin', '1234')
            ->call('redeem')
            ->assertHasNoErrors('pin');

        $this->assertSame(StudentGift::STATUS_REDEEMED, $reachedGift->fresh()->status);
    }

    public function test_parent_gift_board_redeem_accepts_child_reward_pin(): void
    {
        [$studentUser, $student, , $parentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createRewardPin($studentUser);

        $reachedGift = StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => StudentGift::STATUS_REACHED,
        ]);
        StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points_required' => 100,
            'status' => StudentGift::STATUS_PENDING,
        ]);

        Livewire::actingAs($parentUser)
            ->test(GiftBoard::class, [
                'student' => $student,
            ])
            ->call('openRedeemModal', $reachedGift->id)
            ->set('pin', '1234')
            ->call('redeem')
            ->assertHasNoErrors('pin');

        $this->assertSame(StudentGift::STATUS_REDEEMED, $reachedGift->fresh()->status);
    }

    public function test_gift_board_displays_claimed_rewards_after_active_queue(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );

        StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'gift_name' => 'Claimed Gift',
            'points_required' => 50,
            'status' => StudentGift::STATUS_REDEEMED,
        ]);
        StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'gift_name' => 'Current Gift',
            'points_required' => 100,
            'status' => StudentGift::STATUS_PENDING,
        ]);
        StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'gift_name' => 'Upcoming Gift',
            'points_required' => 150,
            'status' => StudentGift::STATUS_WAITING,
        ]);

        Livewire::actingAs($studentUser)
            ->test(GiftBoard::class, [
                'student' => $student,
            ])
            ->assertSee('Reward1')
            ->assertSee('Reward2')
            ->assertSee('Reward3')
            ->assertDontSee('Current Gift')
            ->set('showRewardDetails', true)
            ->assertSeeInOrder(['Current Gift', 'Upcoming Gift', 'Claimed Gift']);
    }

    public function test_teacher_gift_board_masks_reward_details_by_default(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');
        [, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );

        StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'gift_name' => 'Private Bike',
            'gift_image' => 'https://example.test/private-bike.png',
            'points_required' => 100,
            'status' => StudentGift::STATUS_PENDING,
        ]);

        Livewire::actingAs($teacher)
            ->test(GiftBoard::class, [
                'student' => $student,
            ])
            ->assertSee('Reward1')
            ->assertDontSee('Private Bike')
            ->assertDontSee('private-bike.png')
            ->set('showRewardDetails', true)
            ->assertSet('showRewardDetails', false)
            ->assertDontSee('Private Bike')
            ->assertDontSee('private-bike.png');
    }

    public function test_teacher_with_reward_detail_permission_can_reveal_reward_details(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');
        $teacher->givePermissionTo(Permission::findOrCreate('view student reward gift details', 'web'));
        [, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );

        StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'gift_name' => 'Private Bike',
            'gift_image' => 'https://example.test/private-bike.png',
            'points_required' => 100,
            'status' => StudentGift::STATUS_PENDING,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Livewire::actingAs($teacher)
            ->test(GiftBoard::class, [
                'student' => $student,
            ])
            ->assertSee('Reward1')
            ->assertDontSee('Private Bike')
            ->assertDontSee('private-bike.png')
            ->set('showRewardDetails', true)
            ->assertSee('Private Bike')
            ->assertSee('private-bike.png');
    }

    public function test_points_progress_masks_gift_images_until_allowed_viewer_reveals_details(): void
    {
        [$studentUser, $student, , $parentUser] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );

        StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'gift_name' => 'Private Bike',
            'gift_image' => 'gifts/private-bike.png',
            'points_required' => 100,
            'status' => StudentGift::STATUS_PENDING,
        ]);

        Livewire::actingAs($parentUser)
            ->test(PointsProgress::class, [
                'studentId' => $student->id,
                'showRewardDetailsToggle' => true,
            ])
            ->assertSet('icon', 'gifts/default_gift.png')
            ->set('showRewardDetails', true)
            ->assertSet('icon', 'gifts/private-bike.png');

        Livewire::actingAs($studentUser)
            ->test(PointsProgress::class, [
                'studentId' => $student->id,
                'showRewardDetailsToggle' => true,
            ])
            ->assertSet('icon', 'gifts/default_gift.png')
            ->set('showRewardDetails', true)
            ->assertSet('icon', 'gifts/private-bike.png');
    }

    public function test_teacher_without_reward_detail_permission_cannot_force_points_progress_reveal(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');
        [, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );

        StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'gift_image' => 'gifts/private-bike.png',
            'points_required' => 100,
            'status' => StudentGift::STATUS_PENDING,
        ]);

        Livewire::actingAs($teacher)
            ->test(PointsProgress::class, [
                'studentId' => $student->id,
                'showRewardDetailsToggle' => true,
            ])
            ->assertSet('icon', 'gifts/default_gift.png')
            ->set('showRewardDetails', true)
            ->assertSet('showRewardDetails', false)
            ->assertSet('icon', 'gifts/default_gift.png');
    }

    public function test_admin_reward_privacy_syncs_teacher_detail_permission(): void
    {
        $allowedTeacher = User::factory()->create(['name' => 'Allowed Teacher']);
        $allowedTeacher->assignRole('teacher');
        $hiddenTeacher = User::factory()->create(['name' => 'Hidden Teacher']);
        $hiddenTeacher->assignRole('teacher');
        [, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );

        $request = Request::create('/student-gifts/reward-privacy', 'POST', [
            'student_id' => $student->id,
            'teacher_user_ids' => [$allowedTeacher->id],
        ]);

        app(StudentGiftController::class)->updatePrivacy($request);

        $this->assertTrue($allowedTeacher->fresh()->hasPermissionTo(RewardGiftVisibility::TEACHER_DETAIL_PERMISSION));
        $this->assertFalse($hiddenTeacher->fresh()->hasPermissionTo(RewardGiftVisibility::TEACHER_DETAIL_PERMISSION));

        $request = Request::create('/student-gifts/reward-privacy', 'POST', [
            'student_id' => $student->id,
            'teacher_user_ids' => [],
        ]);

        app(StudentGiftController::class)->updatePrivacy($request);

        $this->assertFalse($allowedTeacher->fresh()->hasPermissionTo(RewardGiftVisibility::TEACHER_DETAIL_PERMISSION));
    }

    public function test_gift_board_redeem_rejects_when_student_is_suspended_mid_flow(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createRewardPin($studentUser);

        $reachedGift = StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => StudentGift::STATUS_REACHED,
        ]);
        StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points_required' => 100,
            'status' => StudentGift::STATUS_PENDING,
        ]);

        $this->actingAs($studentUser);

        $component = Livewire::test(GiftBoard::class, [
            'student' => $student,
        ])
            ->call('openRedeemModal', $reachedGift->id)
            ->set('pin', '1234');

        $student->forceFill(['account_status' => ChildAccountStatus::Suspended->value])->save();

        $component
            ->call('redeem')
            ->assertHasErrors('pin');

        $this->assertSame(StudentGift::STATUS_REACHED, $reachedGift->fresh()->status);
    }

    public function test_points_progress_redeem_rejects_when_student_is_suspended_mid_flow(): void
    {
        [$studentUser, $student] = $this->createStudentFamily(
            FamilyLifecycleStatus::Active->value,
            ChildAccountStatus::Active->value,
        );
        $this->createRewardPin($studentUser);

        $reachedGift = StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => StudentGift::STATUS_REACHED,
        ]);
        StudentGift::create([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points_required' => 100,
            'status' => StudentGift::STATUS_PENDING,
        ]);

        $this->actingAs($studentUser);

        $component = Livewire::test(PointsProgress::class, [
            'studentId' => $student->id,
        ])
            ->call('openRedeemModal', $reachedGift->id)
            ->set('pin', '1234');

        $student->forceFill(['account_status' => ChildAccountStatus::Suspended->value])->save();

        $component
            ->call('redeem')
            ->assertHasErrors('pin');

        $this->assertSame(StudentGift::STATUS_REACHED, $reachedGift->fresh()->status);
    }

    private function createStudentFamily(?string $familyStatus, ?string $childStatus): array
    {
        $parentUser = User::factory()->create();
        $parentUser->assignRole('parent');

        $studentUser = User::factory()->create();
        $studentUser->assignRole('student');

        $parent = ParentModel::create([
            'first_name' => 'Family',
            'user_id' => $parentUser->id,
            'email' => $parentUser->email,
            'lifecycle_status' => $familyStatus,
        ]);

        $student = Student::create([
            'first_name' => 'Student',
            'parent_id' => $parent->id,
            'user_id' => $studentUser->id,
            'status' => 'active',
            'account_status' => $childStatus,
        ]);

        return [$studentUser, $student, $parent, $parentUser];
    }

    private function createRewardPin(User $user): void
    {
        DB::table('reward_pin_hashes')->insert([
            'user_id' => $user->id,
            'pin_hash' => Hash::make('1234'),
            'pin_unhash' => '1234',
            'updated_at' => now(),
        ]);
    }

    private function createClassSessionAttachmentFixture(Student $student): void
    {
        Storage::persistentFake('public');
        Storage::disk('public')->deleteDirectory('test-lifecycle-gate-attachments');
        Storage::disk('public')->put('test-lifecycle-gate-attachments/test.pdf', '%PDF-1.4 test');

        DB::table('subjects')->updateOrInsert(
            ['id' => 10],
            ['title' => 'Language and Literature']
        );

        DB::table('grade_level_subjects')->updateOrInsert(
            ['id' => 20],
            [
                'subject_id' => 10,
                'grade_level_id' => 1,
                'academic_year_id' => 1,
                'type' => 'standard',
                'status' => 'active',
            ]
        );

        DB::table('students_subjects')->updateOrInsert(
            ['id' => 42],
            [
                'student_id' => $student->id,
                'grade_level_subject_id' => 20,
                'academic_year_id' => 1,
                'status' => 'active',
                'class_subject_id' => 1,
            ]
        );

        DB::table('class_sessions')->where('id', 1)->update([
            'title' => 'Session Title',
            'subject_id' => 10,
            'class_subject_id' => 1,
        ]);

        DB::table('session_materials')->updateOrInsert(
            ['session_id' => 1],
            ['status' => 'published']
        );

        DB::table('session_tasks')->updateOrInsert(
            ['id' => 77],
            [
                'class_session_id' => 1,
                'title' => 'Session Task',
                'description' => 'Task description',
                'sort' => 1,
                'default_points' => 5,
                'max_points' => 10,
            ]
        );

        DB::table('attachment_files')->updateOrInsert(
            ['id' => 85],
            [
                'session_task_id' => 77,
                'type' => 'pdf',
                'path' => 'test-lifecycle-gate-attachments/test.pdf',
                'title' => 'Test PDF',
            ]
        );

        Storage::disk('public')->put('test-lifecycle-gate-attachments/test-image.png', 'test image');

        DB::table('attachment_files')->updateOrInsert(
            ['id' => 86],
            [
                'session_task_id' => 77,
                'type' => 'file',
                'path' => 'test-lifecycle-gate-attachments/test-image.png',
                'title' => 'Test Image',
            ]
        );
    }

    private function createAutomatedClassSessionAttachmentFixture(Student $student): void
    {
        $this->createClassSessionAttachmentFixture($student);

        DB::table('class_sessions')->where('id', 1)->update([
            'student_id' => $student->id,
            'main_daily_session_template_id' => 700,
            'generated_for_date' => now()->toDateString(),
            'title' => 'Automated Task Session',
        ]);

        DB::table('session_task_student')->updateOrInsert(
            [
                'session_task_id' => 77,
                'student_id' => $student->id,
            ],
            [
                'student_points' => 5,
                'status' => 'assigned',
                'flag' => null,
            ]
        );
    }

    private function ensureBackgroundTableExists(): void
    {
        if (Schema::hasTable('background')) {
            return;
        }

        Schema::create('background', function (Blueprint $table): void {
            $table->id();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->longText('pdf_link')->nullable();
            $table->integer('sort')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    private function createLifecycleGateTables(): void
    {
        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->boolean('is_current')->default(false);
                $table->timestamps();
            });
        }

        if (! DB::table('academic_years')->where('is_current', 1)->exists()) {
            DB::table('academic_years')->insert([
                'id' => 1,
                'title' => 'Current Academic Year',
                'is_current' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('email')->nullable();
                $table->boolean('active')->default(false);
                $table->string('lifecycle_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('parents', 'active')) {
            Schema::table('parents', fn (Blueprint $table) => $table->boolean('active')->default(false));
        }

        if (! Schema::hasColumn('parents', 'lifecycle_status')) {
            Schema::table('parents', fn (Blueprint $table) => $table->string('lifecycle_status')->nullable());
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('status')->default('active');
                $table->string('account_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('students', 'parent_id')) {
            Schema::table('students', fn (Blueprint $table) => $table->unsignedBigInteger('parent_id')->nullable());
        }

        if (! Schema::hasColumn('students', 'account_status')) {
            Schema::table('students', fn (Blueprint $table) => $table->string('account_status')->nullable());
        }

        if (! Schema::hasTable('student_gift_points_history')) {
            Schema::create('student_gift_points_history', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->integer('points')->default(0);
                $table->date('date')->nullable();
                $table->string('status')->nullable();
                $table->string('sign')->nullable();
            });
        }

        if (! Schema::hasTable('student_gifts')) {
            Schema::create('student_gifts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('gift_id')->nullable();
                $table->string('gift_name')->nullable();
                $table->string('gift_image')->nullable();
                $table->integer('points_required')->nullable();
                $table->string('status')->nullable();
                $table->unsignedBigInteger('approved_by_id')->nullable();
                $table->string('approved_by_name')->nullable();
                $table->timestamp('approval_timestamp')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('reached_at')->nullable();
                $table->timestamp('redeemed_at')->nullable();
            });
        }

        if (! Schema::hasTable('session_task_student')) {
            Schema::create('session_task_student', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_task_id')->nullable();
                $table->unsignedBigInteger('student_id');
                $table->integer('student_points')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->boolean('assign_to_all')->nullable();
                $table->string('status')->nullable();
                $table->string('flag')->nullable();
            });
        }

        if (! Schema::hasTable('students_subjects')) {
            Schema::create('students_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('grade_level_subject_id')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->date('enrolled_at')->nullable();
                $table->string('status')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
            });
        }

        if (! Schema::hasTable('subjects')) {
            Schema::create('subjects', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('grade_level_subjects')) {
            Schema::create('grade_level_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('grade_level_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->string('type')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('teacher_subject_classes')) {
            Schema::create('teacher_subject_classes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_teacher_coteacher_id')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('grade_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('punishment_agreements')) {
            Schema::create('punishment_agreements', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->string('title')->nullable();
                $table->unsignedBigInteger('punishment_type_id')->nullable();
                $table->string('status')->nullable();
            });
        }

        if (! Schema::hasTable('student_session_discipline')) {
            Schema::create('student_session_discipline', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->integer('points')->default(0);
                $table->string('type')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('class_sessions')) {
            Schema::create('class_sessions', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('teacher_subject_classes_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->unsignedBigInteger('main_daily_session_template_id')->nullable();
                $table->date('generated_for_date')->nullable();
                $table->date('date')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('class_sessions', 'student_id')) {
            Schema::table('class_sessions', fn (Blueprint $table) => $table->unsignedBigInteger('student_id')->nullable());
        }

        if (! Schema::hasColumn('class_sessions', 'main_daily_session_template_id')) {
            Schema::table('class_sessions', fn (Blueprint $table) => $table->unsignedBigInteger('main_daily_session_template_id')->nullable());
        }

        if (! Schema::hasColumn('class_sessions', 'generated_for_date')) {
            Schema::table('class_sessions', fn (Blueprint $table) => $table->date('generated_for_date')->nullable());
        }

        if (! Schema::hasTable('session_materials')) {
            Schema::create('session_materials', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_id')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('session_tasks')) {
            Schema::create('session_tasks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('class_session_id');
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->integer('sort')->nullable();
                $table->integer('default_points')->default(0);
                $table->integer('max_points')->default(10);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('attachment_files')) {
            Schema::create('attachment_files', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_task_id')->nullable();
                $table->string('type')->nullable();
                  $table->string('path')->nullable();
                  $table->string('title')->nullable();
                  $table->unsignedInteger('sort_order')->default(0);
                  $table->timestamps();
              });
          }

        if (! Schema::hasTable('reward_pin_hashes')) {
            Schema::create('reward_pin_hashes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('pin_hash');
                $table->string('pin_unhash')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! Schema::hasTable('task_pin_hashes')) {
            Schema::create('task_pin_hashes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('pin_hash');
                $table->string('pin_unhash')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (DB::table('class_sessions')->count() === 0) {
            DB::table('class_sessions')->insert([
                'id' => 1,
                'title' => 'Journey Session',
                'subject_id' => 1,
                'class_subject_id' => 1,
                'teacher_subject_classes_id' => 12,
                'date' => now()->toDateString(),
            ]);
        }
    }

    private function grantTeacherJourneyAccess(User $teacher, Student $student, int $teacherSubjectClassId = 12, int $classSubjectId = 1): void
    {
        DB::table('students_subjects')->updateOrInsert(
            [
                'student_id' => $student->id,
                'class_subject_id' => $classSubjectId,
            ],
            [
                'grade_level_subject_id' => 20,
                'academic_year_id' => 1,
                'status' => 'active',
            ]
        );

        DB::table('teacher_subject_classes')->updateOrInsert(
            ['id' => $teacherSubjectClassId],
            [
                'user_teacher_coteacher_id' => $teacher->id,
                'class_subject_id' => $classSubjectId,
                'grade_id' => 1,
                'class_id' => 1,
                'subject_id' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
