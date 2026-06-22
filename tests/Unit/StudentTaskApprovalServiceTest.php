<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\StudentGiftController;
use App\Livewire\StudentGiftCreate;
use App\Livewire\StudentGiftEdit;
use App\Livewire\Ui\PointsProgress;
use App\Models\ParentModel;
use App\Models\SessionTaskStudent;
use App\Models\Student;
use App\Models\StudentGift;
use App\Models\StudentTaskApprovalEvent;
use App\Models\StudentTaskApprovalSetting;
use App\Models\User;
use App\Services\RewardProgressionService;
use App\Services\StudentTaskApprovalService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentTaskApprovalServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createApprovalWorkflowTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['student', 'parent', 'teacher'] as $role) {
            Role::findOrCreate($role);
        }
    }

    public function test_student_put_to_review_records_review_without_reward_effects(): void
    {
        $context = $this->seedTaskContext();

        app(StudentTaskApprovalService::class)->putToReview(
            $context['student_user'],
            $context['task_id'],
            $context['student']->id
        );

        $this->assertDatabaseHas('session_task_student', [
            'session_task_id' => $context['task_id'],
            'student_id' => $context['student']->id,
            'status' => SessionTaskStudent::STATUS_IN_REVIEW,
            'review_submitted_by_id' => $context['student_user']->id,
            'review_submission_source' => SessionTaskStudent::SOURCE_STUDENT_REVIEW,
            'trusted_auto_approval_snapshot' => 0,
        ]);

        $this->assertDatabaseHas('student_task_approval_events', [
            'event_type' => StudentTaskApprovalEvent::TYPE_SUBMITTED_FOR_REVIEW,
            'actor_user_id' => $context['student_user']->id,
            'source' => SessionTaskStudent::SOURCE_STUDENT_REVIEW,
        ]);

        $this->assertSame(0, DB::table('reward_points_ledger')->count());
        $this->assertSame(0, DB::table('reward_totals')->count());
        $this->assertSame(0, DB::table('student_gift_points_history')->count());
    }

    public function test_student_pin_completion_applies_reward_effects_once(): void
    {
        $context = $this->seedTaskContext(defaultPoints: 5, maxPoints: 10);

        $service = app(StudentTaskApprovalService::class);
        $service->completeWithStudentPin(
            $context['student_user'],
            $context['task_id'],
            $context['student']->id,
            $context['student_user']->id
        );
        $service->completeWithStudentPin(
            $context['student_user'],
            $context['task_id'],
            $context['student']->id,
            $context['student_user']->id
        );

        $this->assertDatabaseHas('session_task_student', [
            'session_task_id' => $context['task_id'],
            'student_id' => $context['student']->id,
            'status' => SessionTaskStudent::STATUS_COMPLETED,
            'student_points' => 5,
            'approval_source' => SessionTaskStudent::SOURCE_STUDENT_PIN,
        ]);

        $this->assertSame(1, DB::table('reward_points_ledger')->count());
        $this->assertSame(1, DB::table('student_gift_points_history')->count());
        $this->assertDatabaseHas('reward_totals', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'subject_id' => $context['subject_id'],
            'total_points' => 5,
        ]);
    }

    public function test_task_reward_reaches_gift_and_unlocks_next_without_redeem(): void
    {
        $context = $this->seedTaskContext(defaultPoints: 50, maxPoints: 50);
        DB::table('student_gifts')->insert([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 100,
            'status' => 'waiting',
        ]);

        app(StudentTaskApprovalService::class)->completeWithStudentPin(
            $context['student_user'],
            $context['task_id'],
            $context['student']->id,
            $context['student_user']->id
        );

        $this->assertDatabaseHas('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => 'reached',
        ]);
        $this->assertNotNull(DB::table('student_gifts')
            ->where('student_id', $context['student']->id)
            ->where('points_required', 50)
            ->value('reached_at'));
        $this->assertDatabaseHas('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 100,
            'status' => 'pending',
        ]);
        $this->assertSame(10, DB::table('student_gifts')
            ->where('student_id', $context['student']->id)
            ->where('academic_year_id', 1)
            ->whereNotIn('status', ['reached', 'redeemed'])
            ->count());
    }

    public function test_reward_progression_allows_multiple_reached_unredeemed_gifts(): void
    {
        $context = $this->seedTaskContext(defaultPoints: 125, maxPoints: 150);
        DB::table('student_gifts')->insert([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 100,
            'status' => 'waiting',
        ]);

        app(StudentTaskApprovalService::class)->completeWithStudentPin(
            $context['student_user'],
            $context['task_id'],
            $context['student']->id,
            $context['student_user']->id
        );

        foreach ([50, 100] as $pointsRequired) {
            $this->assertDatabaseHas('student_gifts', [
                'student_id' => $context['student']->id,
                'academic_year_id' => 1,
                'points_required' => $pointsRequired,
                'status' => 'reached',
            ]);
        }

        $this->assertDatabaseHas('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 150,
            'status' => 'pending',
        ]);
        $this->assertSame(10, DB::table('student_gifts')
            ->where('student_id', $context['student']->id)
            ->where('academic_year_id', 1)
            ->whereNotIn('status', ['reached', 'redeemed'])
            ->count());
    }

    public function test_reward_progression_returns_every_gift_reached_by_one_points_jump(): void
    {
        $context = $this->seedTaskContext();
        DB::table('student_gifts')->insert([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 100,
            'status' => 'waiting',
        ]);

        $reachedGiftIds = app(RewardProgressionService::class)->advanceGiftQueueForTotal(
            $context['student']->id,
            125,
            1
        );

        $this->assertCount(2, $reachedGiftIds);

        $this->assertSame(2, DB::table('student_gifts')
            ->whereIn('id', $reachedGiftIds)
            ->where('status', StudentGift::STATUS_REACHED)
            ->count());
    }

    public function test_deducting_points_does_not_revert_reached_gift(): void
    {
        $context = $this->seedTaskContext(defaultPoints: 50, maxPoints: 50);
        DB::table('student_gifts')->insert([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 100,
            'status' => 'waiting',
        ]);

        app(StudentTaskApprovalService::class)->completeWithStudentPin(
            $context['student_user'],
            $context['task_id'],
            $context['student']->id,
            $context['student_user']->id
        );

        app(RewardProgressionService::class)->applyPointDelta(
            studentId: $context['student']->id,
            pointsDelta: -2,
            sourceType: 'discipline',
            sourceId: 99,
            grantedBy: $context['parent_user']->id,
            academicYearId: 1,
            subjectId: $context['subject_id'],
            comment: 'Deduction'
        );

        $this->assertDatabaseHas('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => 'reached',
        ]);
        $this->assertDatabaseHas('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 100,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('student_gift_points_history', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points' => 48,
            'sign' => 'plus',
        ]);
    }

    public function test_points_progress_fill_matches_current_total_after_deduction_below_reached_floor(): void
    {
        $context = $this->seedTaskContext(defaultPoints: 50, maxPoints: 50);
        DB::table('student_gifts')->insert([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 100,
            'status' => StudentGift::STATUS_WAITING,
        ]);

        app(StudentTaskApprovalService::class)->completeWithStudentPin(
            $context['student_user'],
            $context['task_id'],
            $context['student']->id,
            $context['student_user']->id
        );

        app(RewardProgressionService::class)->applyPointDelta(
            studentId: $context['student']->id,
            pointsDelta: -2,
            sourceType: 'discipline',
            sourceId: 99,
            grantedBy: $context['parent_user']->id,
            academicYearId: 1,
            subjectId: $context['subject_id'],
            comment: 'Deduction'
        );

        Livewire::actingAs($context['student_user'])
            ->test(PointsProgress::class, [
                'studentId' => $context['student']->id,
                'circleView' => true,
                'barView' => false,
            ])
            ->assertSet('current', 48)
            ->assertSet('total', 100)
            ->assertSet('floorPoints', 50)
            ->assertSet('pctNormalized', 48.0);
    }

    public function test_redeeming_reached_gift_records_redeemed_at_without_deducting_points(): void
    {
        $context = $this->seedTaskContext(defaultPoints: 50, maxPoints: 50);
        DB::table('student_gifts')->insert([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 100,
            'status' => 'waiting',
        ]);

        app(StudentTaskApprovalService::class)->completeWithStudentPin(
            $context['student_user'],
            $context['task_id'],
            $context['student']->id,
            $context['student_user']->id
        );

        $giftId = (int) DB::table('student_gifts')
            ->where('student_id', $context['student']->id)
            ->where('points_required', 50)
            ->value('id');

        app(RewardProgressionService::class)->redeemGift($context['student']->id, $giftId);

        $this->assertDatabaseHas('student_gifts', [
            'id' => $giftId,
            'status' => 'redeemed',
        ]);
        $this->assertNotNull(DB::table('student_gifts')->where('id', $giftId)->value('redeemed_at'));
        $this->assertDatabaseHas('student_gift_points_history', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points' => 50,
            'sign' => 'plus',
        ]);
    }

    public function test_admin_gift_create_cannot_move_before_current_pending_target(): void
    {
        $context = $this->seedTaskContext();

        Livewire::test(StudentGiftCreate::class, ['studentId' => $context['student']->id])
            ->set('gift_name', 'Too early')
            ->set('points_required', 40)
            ->set('status', StudentGift::STATUS_WAITING)
            ->call('save')
            ->assertHasErrors(['points_required']);

        $this->assertDatabaseMissing('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'gift_name' => 'Too early',
        ]);
        $this->assertDatabaseHas('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => StudentGift::STATUS_PENDING,
        ]);
    }

    public function test_admin_gift_create_adds_upcoming_without_changing_pending_target(): void
    {
        $context = $this->seedTaskContext();

        Livewire::test(StudentGiftCreate::class, ['studentId' => $context['student']->id])
            ->set('gift_name', 'Future reward')
            ->set('points_required', 75)
            ->set('status', StudentGift::STATUS_WAITING)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => StudentGift::STATUS_PENDING,
        ]);
        $this->assertDatabaseHas('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 75,
            'status' => StudentGift::STATUS_WAITING,
        ]);
    }

    public function test_admin_gift_edit_cannot_move_upcoming_before_current_pending_target(): void
    {
        $context = $this->seedTaskContext();
        $giftId = DB::table('student_gifts')->insertGetId([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'gift_name' => 'Future reward',
            'points_required' => 100,
            'status' => StudentGift::STATUS_WAITING,
        ]);

        Livewire::test(StudentGiftEdit::class, ['studentId' => $context['student']->id])
            ->set('student_id', $context['student']->id)
            ->set('giftId', $giftId)
            ->set('gift_name', 'Moved too early')
            ->set('points_required', 40)
            ->set('status', StudentGift::STATUS_WAITING)
            ->set('academic_year_id', 1)
            ->call('save')
            ->assertHasErrors(['points_required']);

        $this->assertDatabaseHas('student_gifts', [
            'id' => $giftId,
            'points_required' => 100,
            'status' => StudentGift::STATUS_WAITING,
        ]);
        $this->assertDatabaseHas('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => StudentGift::STATUS_PENDING,
        ]);
    }

    public function test_admin_gift_edit_can_move_pending_target_closer(): void
    {
        $context = $this->seedTaskContext();
        $giftId = (int) DB::table('student_gifts')
            ->where('student_id', $context['student']->id)
            ->where('academic_year_id', 1)
            ->where('status', StudentGift::STATUS_PENDING)
            ->value('id');

        Livewire::test(StudentGiftEdit::class, ['studentId' => $context['student']->id])
            ->call('openEditor', $giftId)
            ->set('gift_name', 'Replacement toy')
            ->set('points_required', 40)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('student_gifts', [
            'id' => $giftId,
            'gift_name' => 'Replacement toy',
            'points_required' => 40,
            'status' => StudentGift::STATUS_PENDING,
        ]);
    }

    public function test_admin_gift_edit_marks_pending_reached_when_lowered_to_current_points(): void
    {
        $context = $this->seedTaskContext(defaultPoints: 82, maxPoints: 82);
        DB::table('student_gifts')->insert([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'gift_name' => 'Distant reward',
            'points_required' => 200,
            'status' => StudentGift::STATUS_WAITING,
        ]);

        app(StudentTaskApprovalService::class)->completeWithStudentPin(
            $context['student_user'],
            $context['task_id'],
            $context['student']->id,
            $context['student_user']->id
        );

        $giftId = (int) DB::table('student_gifts')
            ->where('student_id', $context['student']->id)
            ->where('academic_year_id', 1)
            ->where('points_required', 200)
            ->value('id');

        Livewire::test(StudentGiftEdit::class, ['studentId' => $context['student']->id])
            ->call('openEditor', $giftId)
            ->set('points_required', 82)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('student_gifts', [
            'id' => $giftId,
            'points_required' => 82,
            'status' => StudentGift::STATUS_REACHED,
        ]);
        $this->assertDatabaseHas('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'status' => StudentGift::STATUS_PENDING,
        ]);
    }

    public function test_admin_gift_edit_cannot_move_pending_at_or_below_completed_floor(): void
    {
        $context = $this->seedTaskContext(defaultPoints: 50, maxPoints: 50);
        DB::table('student_gifts')->insert([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'gift_name' => 'Next reward',
            'points_required' => 100,
            'status' => StudentGift::STATUS_WAITING,
        ]);

        app(StudentTaskApprovalService::class)->completeWithStudentPin(
            $context['student_user'],
            $context['task_id'],
            $context['student']->id,
            $context['student_user']->id
        );

        $giftId = (int) DB::table('student_gifts')
            ->where('student_id', $context['student']->id)
            ->where('academic_year_id', 1)
            ->where('points_required', 100)
            ->value('id');

        Livewire::test(StudentGiftEdit::class, ['studentId' => $context['student']->id])
            ->call('openEditor', $giftId)
            ->set('points_required', 50)
            ->call('save')
            ->assertHasErrors(['points_required']);

        $this->assertDatabaseHas('student_gifts', [
            'id' => $giftId,
            'points_required' => 100,
            'status' => StudentGift::STATUS_PENDING,
        ]);
    }

    public function test_admin_gift_reorder_rejects_non_current_year_gifts(): void
    {
        $context = $this->seedTaskContext();
        DB::table('academic_years')->insert([
            'id' => 2,
            'title' => '2025',
            'is_current' => 0,
        ]);
        $oldYearGiftId = DB::table('student_gifts')->insertGetId([
            'student_id' => $context['student']->id,
            'academic_year_id' => 2,
            'gift_name' => 'Old year reward',
            'points_required' => 50,
            'status' => StudentGift::STATUS_PENDING,
            'gift_order' => null,
        ]);
        $currentGiftId = DB::table('student_gifts')
            ->where('student_id', $context['student']->id)
            ->where('academic_year_id', 1)
            ->value('id');

        $request = Request::create('/student-gifts/reorder', 'POST', [
            'student_id' => $context['student']->id,
            'order' => [$currentGiftId, $oldYearGiftId],
        ]);

        $response = app(StudentGiftController::class)->reorder($request);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertDatabaseHas('student_gifts', [
            'id' => $oldYearGiftId,
            'gift_order' => null,
        ]);
    }

    public function test_admin_gift_reorder_moves_upcoming_identities_without_changing_points(): void
    {
        $context = $this->seedTaskContext();
        $lowSlotId = DB::table('student_gifts')->insertGetId([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'gift_name' => 'Low reward',
            'gift_image' => 'gifts/low.png',
            'points_required' => 100,
            'status' => StudentGift::STATUS_WAITING,
        ]);
        $highSlotId = DB::table('student_gifts')->insertGetId([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'gift_name' => 'High reward',
            'gift_image' => 'gifts/high.png',
            'points_required' => 150,
            'status' => StudentGift::STATUS_WAITING,
        ]);

        $request = Request::create('/student-gifts/reorder', 'POST', [
            'student_id' => $context['student']->id,
            'order' => [$highSlotId, $lowSlotId],
        ]);

        $response = app(StudentGiftController::class)->reorder($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertDatabaseHas('student_gifts', [
            'id' => $lowSlotId,
            'points_required' => 100,
            'gift_name' => 'High reward',
            'gift_image' => 'gifts/high.png',
        ]);
        $this->assertDatabaseHas('student_gifts', [
            'id' => $highSlotId,
            'points_required' => 150,
            'gift_name' => 'Low reward',
            'gift_image' => 'gifts/low.png',
        ]);
    }

    public function test_admin_bulk_interval_updates_upcoming_points_after_pending_target_only(): void
    {
        $context = $this->seedTaskContext();
        $firstWaitingId = DB::table('student_gifts')->insertGetId([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'gift_name' => 'First waiting',
            'points_required' => 100,
            'status' => StudentGift::STATUS_WAITING,
        ]);
        $secondWaitingId = DB::table('student_gifts')->insertGetId([
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'gift_name' => 'Second waiting',
            'points_required' => 200,
            'status' => StudentGift::STATUS_WAITING,
        ]);

        $request = Request::create('/student-gifts/bulk-interval', 'POST', [
            'student_id' => $context['student']->id,
            'interval' => 25,
        ]);

        $response = app(StudentGiftController::class)->bulkInterval($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertDatabaseHas('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => StudentGift::STATUS_PENDING,
        ]);
        $this->assertDatabaseHas('student_gifts', [
            'id' => $firstWaitingId,
            'points_required' => 75,
            'status' => StudentGift::STATUS_WAITING,
        ]);
        $this->assertDatabaseHas('student_gifts', [
            'id' => $secondWaitingId,
            'points_required' => 100,
            'status' => StudentGift::STATUS_WAITING,
        ]);
    }

    public function test_student_gift_image_url_falls_back_for_missing_files(): void
    {
        $this->assertStringEndsWith(
            '/'.StudentGift::DEFAULT_GIFT_IMAGE_PATH,
            StudentGift::imageUrlFor('gifts/missing-file.png')
        );
    }

    public function test_auto_created_runway_gifts_use_reward_name(): void
    {
        $context = $this->seedTaskContext();

        StudentGift::createWaitingAfterReached($context['student']->id, 1);

        $this->assertDatabaseHas('student_gifts', [
            'student_id' => $context['student']->id,
            'academic_year_id' => 1,
            'gift_name' => 'Reward2',
            'points_required' => 150,
            'status' => StudentGift::STATUS_WAITING,
        ]);
    }

    public function test_legacy_auto_gift_names_display_as_reward_names(): void
    {
        $this->assertSame('Reward11', StudentGift::displayGiftName('gift11', 11));
        $this->assertSame('Reward11', StudentGift::displayGiftName('Gift 11', 11));
    }

    public function test_public_gift_file_route_serves_storage_disk_uploads(): void
    {
        Storage::disk('public')->put(
            'gifts/test-gift-route.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAFgwJ/lq6XXwAAAABJRU5ErkJggg==')
        );

        $this->get('/storage/gifts/test-gift-route.png')
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_advancing_up_next_does_not_downgrade_in_review_task(): void
    {
        $context = $this->seedTaskContext(defaultPoints: 5, maxPoints: 10);

        DB::table('session_tasks')->insert([
            'id' => 51,
            'class_session_id' => 40,
            'title' => 'Second Task',
            'sort' => 2,
            'default_points' => 5,
            'max_points' => 10,
            'created_at' => now(),
        ]);

        SessionTaskStudent::create([
            'session_task_id' => 51,
            'student_id' => $context['student']->id,
            'status' => SessionTaskStudent::STATUS_IN_REVIEW,
            'flag' => 'up-next',
            'review_submitted_at' => now(config('app.timezone')),
            'review_submitted_by_id' => $context['student_user']->id,
            'review_submission_source' => SessionTaskStudent::SOURCE_STUDENT_REVIEW,
        ]);

        app(StudentTaskApprovalService::class)->completeWithStudentPin(
            $context['student_user'],
            $context['task_id'],
            $context['student']->id,
            $context['student_user']->id
        );

        $this->assertDatabaseHas('session_task_student', [
            'session_task_id' => 51,
            'student_id' => $context['student']->id,
            'status' => SessionTaskStudent::STATUS_IN_REVIEW,
            'flag' => 'up-next',
        ]);
    }

    public function test_parent_approval_enforces_point_bounds_and_zero_point_history(): void
    {
        $context = $this->seedTaskContext(defaultPoints: 5, maxPoints: 6);
        $pivot = $this->putPivotInReview($context);

        try {
            app(StudentTaskApprovalService::class)->approveAsParent($context['parent_user'], $pivot->id, 7);
            $this->fail('Out-of-bounds points should be rejected.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $pivot->refresh();
        $this->assertSame(SessionTaskStudent::STATUS_IN_REVIEW, $pivot->status);

        app(StudentTaskApprovalService::class)->approveAsParent($context['parent_user'], $pivot->id, 0);

        $this->assertDatabaseHas('session_task_student', [
            'id' => $pivot->id,
            'status' => SessionTaskStudent::STATUS_COMPLETED,
            'student_points' => 0,
            'approval_source' => SessionTaskStudent::SOURCE_PARENT_APPROVAL,
            'approved_by_id' => $context['parent_user']->id,
        ]);
        $this->assertDatabaseHas('student_task_approval_events', [
            'session_task_student_id' => $pivot->id,
            'event_type' => StudentTaskApprovalEvent::TYPE_APPROVED,
            'points' => 0,
        ]);
        $this->assertSame(0, DB::table('reward_points_ledger')->count());
    }

    public function test_parent_direct_completion_completes_assigned_task_without_pin(): void
    {
        $context = $this->seedTaskContext(defaultPoints: 5, maxPoints: 10);

        app(StudentTaskApprovalService::class)->completeAsParent(
            $context['parent_user'],
            $context['task_id'],
            $context['student']->id,
            6
        );

        $this->assertDatabaseHas('session_task_student', [
            'session_task_id' => $context['task_id'],
            'student_id' => $context['student']->id,
            'status' => SessionTaskStudent::STATUS_COMPLETED,
            'student_points' => 6,
            'approval_source' => SessionTaskStudent::SOURCE_PARENT_DIRECT_COMPLETION,
            'approved_by_id' => $context['parent_user']->id,
        ]);
        $this->assertDatabaseHas('student_task_approval_events', [
            'event_type' => StudentTaskApprovalEvent::TYPE_COMPLETED_BY_PARENT,
            'actor_user_id' => $context['parent_user']->id,
            'source' => SessionTaskStudent::SOURCE_PARENT_DIRECT_COMPLETION,
            'points' => 6,
        ]);
        $this->assertSame(1, DB::table('reward_points_ledger')->count());
    }

    public function test_trusted_child_auto_approval_uses_snapshot_and_captured_parent_granter(): void
    {
        $context = $this->seedTaskContext(defaultPoints: 4, maxPoints: 10);

        StudentTaskApprovalSetting::create([
            'student_id' => $context['student']->id,
            'trusted_auto_approval_enabled' => true,
            'updated_by_user_id' => $context['parent_user']->id,
        ]);

        app(StudentTaskApprovalService::class)->putToReview(
            $context['student_user'],
            $context['task_id'],
            $context['student']->id
        );

        DB::table('session_task_student')
            ->where('session_task_id', $context['task_id'])
            ->update(['trusted_auto_approval_due_at' => now(config('app.timezone'))->subMinute()]);

        $result = app(StudentTaskApprovalService::class)
            ->autoApproveTrustedChildTasks(CarbonImmutable::now(config('app.timezone')));

        $this->assertSame(['approved' => 1, 'skipped' => 0], $result);
        $this->assertDatabaseHas('session_task_student', [
            'session_task_id' => $context['task_id'],
            'student_id' => $context['student']->id,
            'status' => SessionTaskStudent::STATUS_COMPLETED,
            'approval_source' => SessionTaskStudent::SOURCE_TRUSTED_CHILD_AUTO,
            'student_points' => 4,
        ]);
        $this->assertDatabaseHas('reward_points_ledger', [
            'student_id' => $context['student']->id,
            'source_type' => 'task',
            'source_id' => $context['task_id'],
            'points_delta' => 4,
            'granted_by' => $context['parent_user']->id,
        ]);
    }

    public function test_inactive_student_context_is_denied_before_state_change(): void
    {
        $context = $this->seedTaskContext();
        $context['student']->update(['account_status' => 'suspended']);

        try {
            app(StudentTaskApprovalService::class)->putToReview(
                $context['student_user'],
                $context['task_id'],
                $context['student']->id
            );
            $this->fail('Inactive student context should be denied.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException) {
            $this->assertTrue(true);
        }

        $this->assertSame(0, DB::table('session_task_student')->count());
    }

    /**
     * @return array<string, mixed>
     */
    private function seedTaskContext(int $defaultPoints = 5, int $maxPoints = 10): array
    {
        DB::table('academic_years')->insert([
            'id' => 1,
            'title' => '2026',
            'is_current' => 1,
        ]);

        $studentUser = User::factory()->create();
        $studentUser->assignRole('student');
        $parentUser = User::factory()->create();
        $parentUser->assignRole('parent');

        $parent = ParentModel::create([
            'first_name' => 'Parent',
            'user_id' => $parentUser->id,
            'active' => true,
            'lifecycle_status' => 'active',
        ]);

        $student = Student::create([
            'first_name' => 'Student',
            'parent_id' => $parent->id,
            'user_id' => $studentUser->id,
            'status' => 'active',
            'account_status' => 'active',
        ]);

        DB::table('subjects')->insert(['id' => 10, 'title' => 'Language and Literature']);
        DB::table('grade_level_subjects')->insert([
            'id' => 20,
            'subject_id' => 10,
            'academic_year_id' => 1,
            'status' => 'active',
        ]);
        DB::table('class_subjects')->insert([
            'id' => 30,
            'grade_level_subject_id' => 20,
        ]);
        DB::table('students_subjects')->insert([
            'student_id' => $student->id,
            'grade_level_subject_id' => 20,
            'academic_year_id' => 1,
            'class_subject_id' => 30,
            'status' => 'active',
        ]);
        DB::table('class_sessions')->insert([
            'id' => 40,
            'title' => 'Session',
            'subject_id' => 10,
            'class_subject_id' => 30,
            'date' => now()->toDateString(),
        ]);
        DB::table('session_tasks')->insert([
            'id' => 50,
            'class_session_id' => 40,
            'title' => 'Task',
            'sort' => 1,
            'default_points' => $defaultPoints,
            'max_points' => $maxPoints,
            'created_at' => now(),
        ]);

        DB::table('student_gifts')->insert([
            'student_id' => $student->id,
            'academic_year_id' => 1,
            'points_required' => 50,
            'status' => 'pending',
        ]);

        return [
            'student_user' => $studentUser,
            'parent_user' => $parentUser,
            'student' => $student,
            'task_id' => 50,
            'subject_id' => 10,
        ];
    }

    private function putPivotInReview(array $context): SessionTaskStudent
    {
        return SessionTaskStudent::create([
            'session_task_id' => $context['task_id'],
            'student_id' => $context['student']->id,
            'status' => SessionTaskStudent::STATUS_IN_REVIEW,
            'review_submitted_at' => now(config('app.timezone')),
            'review_submitted_by_id' => $context['student_user']->id,
            'review_submission_source' => SessionTaskStudent::SOURCE_STUDENT_REVIEW,
        ]);
    }

    private function createApprovalWorkflowTables(): void
    {
        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->boolean('is_current')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->boolean('active')->default(false);
                $table->string('lifecycle_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('status')->nullable();
                $table->string('account_status')->nullable();
                $table->timestamps();
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
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('class_subjects')) {
            Schema::create('class_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('grade_level_subject_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students_subjects')) {
            Schema::create('students_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('grade_level_subject_id')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->string('status')->nullable();
            });
        }

        if (! Schema::hasTable('class_sessions')) {
            Schema::create('class_sessions', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->date('date')->nullable();
            });
        }

        if (! Schema::hasTable('session_tasks')) {
            Schema::create('session_tasks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('class_session_id')->nullable();
                $table->string('title')->nullable();
                $table->integer('sort')->nullable();
                $table->integer('default_points')->default(0);
                $table->integer('max_points')->default(0);
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('session_task_student')) {
            Schema::create('session_task_student', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_task_id');
                $table->unsignedBigInteger('student_id');
                $table->integer('student_points')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('review_submitted_at')->nullable();
                $table->unsignedBigInteger('review_submitted_by_id')->nullable();
                $table->string('review_submission_source')->nullable();
                $table->string('approval_source')->nullable();
                $table->unsignedBigInteger('approved_by_id')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->boolean('trusted_auto_approval_snapshot')->default(false);
                $table->timestamp('trusted_auto_approval_due_at')->nullable();
                $table->unsignedBigInteger('trusted_auto_approval_granted_by_id')->nullable();
                $table->string('assign_to_all')->nullable();
                $table->string('status')->nullable();
                $table->string('flag')->nullable();
                $table->unique(['session_task_id', 'student_id'], 'uq_session_task_student');
            });
        }

        if (! Schema::hasTable('student_task_approval_events')) {
            Schema::create('student_task_approval_events', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_task_student_id');
                $table->unsignedBigInteger('session_task_id');
                $table->unsignedBigInteger('student_id');
                $table->string('event_type');
                $table->unsignedBigInteger('actor_user_id')->nullable();
                $table->string('actor_role')->nullable();
                $table->string('source');
                $table->integer('points')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('student_task_approval_settings')) {
            Schema::create('student_task_approval_settings', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->boolean('trusted_auto_approval_enabled')->default(false);
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
                $table->timestamps();
                $table->unique('student_id', 'uq_stas_student');
            });
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

        if (! Schema::hasTable('reward_points_ledger')) {
            Schema::create('reward_points_ledger', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->string('source_type');
                $table->unsignedBigInteger('source_id');
                $table->integer('points_delta')->default(0);
                $table->unsignedBigInteger('granted_by')->nullable();
                $table->timestamp('granted_at')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->text('comment')->nullable();
                $table->string('sign')->nullable();
            });
        }

        if (! Schema::hasTable('reward_totals')) {
            Schema::create('reward_totals', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->integer('total_points')->default(0);
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('student_gifts')) {
            Schema::create('student_gifts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('gift_id')->nullable();
                $table->string('gift_name')->nullable();
                $table->string('gift_image')->nullable();
                $table->integer('points_required')->nullable();
                $table->string('status')->nullable();
                $table->unsignedBigInteger('approved_by_id')->nullable();
                $table->string('approved_by_name')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('reached_at')->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->integer('gift_order')->nullable();
            });
        }
    }
}
