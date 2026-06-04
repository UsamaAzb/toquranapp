<?php

namespace Tests\Feature\CoreLms;

use App\Livewire\Student\Journey;
use App\Livewire\Student\SessionsBoard;
use Tests\TestCase;

class StudentTaskApprovalSurfaceTest extends TestCase
{
    public function test_student_surfaces_expose_review_and_pin_entry_points(): void
    {
        $this->assertTrue(method_exists(Journey::class, 'putToReview'));
        $this->assertTrue(method_exists(Journey::class, 'confirmTaskCompletionWithPin'));
        $this->assertTrue(method_exists(SessionsBoard::class, 'putToReview'));

        $journeyView = file_get_contents(resource_path('views/livewire/student/journey.blade.php'));
        $sessionsView = file_get_contents(resource_path('views/livewire/student/sessions-board.blade.php'));
        $taskActionsView = file_get_contents(resource_path('views/components/sessions/task-actions.blade.php'));

        $this->assertIsString($journeyView);
        $this->assertIsString($sessionsView);
        $this->assertIsString($taskActionsView);
        $this->assertStringContainsString('wire:click="putToReview({{ $currentTaskId }})"', $journeyView);
        $this->assertStringContainsString("@include('components.sessions.task-actions'", $sessionsView);
        $this->assertStringContainsString('$putToReviewAction = "putToReview({$taskId})";', $taskActionsView);
        $this->assertStringContainsString('wire:click="{{ $putToReviewAction }}"', $taskActionsView);
        $this->assertStringContainsString('title="{{ __(\'Complete with PIN\') }}"', $journeyView);
        $this->assertStringContainsString('title="Complete with PIN"', $taskActionsView);
        $this->assertStringContainsString('tabler-key', $journeyView);
        $this->assertStringContainsString('tabler-key', $taskActionsView);
        $this->assertStringContainsString("@if(!\$isCompleted && !\$isInReview && auth()->user()?->hasRole('student'))", $journeyView);
        $this->assertStringContainsString('$showStudentReadyActions = !$isCompleted && !$isInReview && $isStudent;', $taskActionsView);
        $this->assertStringContainsString("@if(!\$isCompleted && !\$isInReview && auth()->user()?->hasRole('student'))", $journeyView);
        $this->assertStringContainsString('$showStudentPinAction = !$isCompleted && $isStudent && (!$isPhone || !$isInReview);', $taskActionsView);
        $this->assertStringContainsString('Approved by Parent', $journeyView);
        $this->assertStringContainsString('Approved by Parent', $sessionsView);
        $this->assertStringContainsString('$sourceBorderClass', $journeyView);
        $this->assertStringContainsString('$sourceBorderClass', $sessionsView);
        $this->assertStringNotContainsString('Put to review', $journeyView);
        $this->assertStringNotContainsString('Put to review', $sessionsView);
        $this->assertStringContainsString("openParentTaskPointsModal({{ \$taskId }}, 'complete')", $taskActionsView);
        $this->assertStringContainsString('wire:click="{{ $pinAction }}"', $taskActionsView);
        $this->assertStringContainsString('studentSessionCompletePinModal', $sessionsView);
        $this->assertStringNotContainsString("'complete' => 'pin'", $sessionsView);
    }

    public function test_parent_child_surfaces_can_approve_in_review_tasks_inline(): void
    {
        $journeyClass = file_get_contents(app_path('Livewire/Student/Journey.php'));
        $sessionsClass = file_get_contents(app_path('Livewire/Student/SessionsBoard.php'));
        $journeyView = file_get_contents(resource_path('views/livewire/student/journey.blade.php'));
        $sessionsView = file_get_contents(resource_path('views/livewire/student/sessions-board.blade.php'));
        $taskActionsView = file_get_contents(resource_path('views/components/sessions/task-actions.blade.php'));

        $this->assertIsString($journeyClass);
        $this->assertIsString($sessionsClass);
        $this->assertIsString($journeyView);
        $this->assertIsString($sessionsView);
        $this->assertIsString($taskActionsView);
        $this->assertStringContainsString('openParentReviewApprovalModal', $journeyClass);
        $this->assertStringContainsString('approveAsParent', $journeyClass);
        $this->assertStringContainsString('freshParentTaskPivot', $journeyClass);
        $this->assertStringContainsString('isInReviewLike', $journeyClass);
        $this->assertStringContainsString('openParentTaskPointsModal', $sessionsClass);
        $this->assertStringContainsString('approveAsParent', $sessionsClass);
        $this->assertStringContainsString('freshParentTaskPivot', $sessionsClass);
        $this->assertStringContainsString('isInReviewLike', $sessionsClass);
        $this->assertStringContainsString('wire:click="openParentReviewApprovalModal', $journeyView);
        $this->assertStringContainsString("wire:click=\"openParentTaskPointsModal({{ \$taskId }}, 'approve')\"", $taskActionsView);
        $this->assertStringContainsString('Approve', $journeyView);
        $this->assertStringContainsString('Approve', $taskActionsView);
    }

    public function test_student_task_surfaces_poll_lightweight_task_state_for_cross_account_changes(): void
    {
        $journeyClass = file_get_contents(app_path('Livewire/Student/Journey.php'));
        $sessionsClass = file_get_contents(app_path('Livewire/Student/SessionsBoard.php'));
        $journeyView = file_get_contents(resource_path('views/livewire/student/journey.blade.php'));
        $sessionsView = file_get_contents(resource_path('views/livewire/student/sessions-board.blade.php'));

        $this->assertIsString($journeyClass);
        $this->assertIsString($sessionsClass);
        $this->assertIsString($journeyView);
        $this->assertIsString($sessionsView);
        $this->assertStringContainsString('wire:poll.10s.visible="refreshTaskState"', $journeyView);
        $this->assertStringContainsString('wire:poll.10s.visible="refreshTaskState"', $sessionsView);
        $this->assertStringContainsString('public function refreshTaskState', $journeyClass);
        $this->assertStringContainsString('public function refreshTaskState', $sessionsClass);
        $this->assertStringContainsString('taskStateSignature', $journeyClass);
        $this->assertStringContainsString('taskStateSignature', $sessionsClass);
        $this->assertStringContainsString('task-state-refreshed', $journeyClass);
        $this->assertStringContainsString('task-state-refreshed', $sessionsClass);
        $this->assertStringContainsString('reward-points:updated', $journeyClass);
        $this->assertStringContainsString('reward-points:updated', $sessionsClass);
        $this->assertStringContainsString('x-data="{ openSession:', $sessionsView);
        $this->assertStringContainsString('@click="openSession = openSession ===', $sessionsView);
        $this->assertStringContainsString('x-show="openSession ===', $sessionsView);
        $this->assertStringNotContainsString('wire:click="toggleSessionOpen', $sessionsView);
    }

    public function test_session_task_completion_deep_link_opens_after_livewire_initializes(): void
    {
        $journeyPage = file_get_contents(resource_path('views/student/journey/show_journey.blade.php'));
        $journeyClass = file_get_contents(app_path('Livewire/Student/Journey.php'));
        $sessionsClass = file_get_contents(app_path('Livewire/Student/SessionsBoard.php'));
        $boardScripts = file_get_contents(resource_path('views/components/sessions/board-scripts.blade.php'));
        $boardStyles = file_get_contents(resource_path('views/components/sessions/board-styles.blade.php'));

        $this->assertIsString($journeyPage);
        $this->assertIsString($journeyClass);
        $this->assertIsString($sessionsClass);
        $this->assertIsString($boardScripts);
        $this->assertIsString($boardStyles);
        $this->assertStringContainsString('window.Livewire.dispatch', $journeyPage);
        $this->assertStringContainsString('open-parent-direct-complete-modal-requested', $journeyPage);
        $this->assertStringContainsString("#[On('open-parent-direct-complete-modal-requested')]", $journeyClass);
        $this->assertStringContainsString('openRequestedSession', $sessionsClass);
        $this->assertStringContainsString("request()->integer('open_session')", $sessionsClass);
        $this->assertStringContainsString('$this->open = [$requestedSessionId];', $sessionsClass);
        $this->assertStringContainsString('scrollRequestedTask', $boardScripts);
        $this->assertStringContainsString('window.w14ScrollRequestedTask', $boardScripts);
        $this->assertStringContainsString("hash.startsWith('#task-')", $boardScripts);
        $this->assertStringContainsString('scrollOffset', $boardScripts);
        $this->assertStringContainsString('requestAnimationFrame(restoreBoardScrollTargets);', $boardScripts);
        $this->assertStringContainsString('scroll-margin-top: 7.5rem;', $boardStyles);
        $this->assertStringNotContainsString(
            "if (\$this->autoOpenMode === 'pin' && Auth::user()?->hasRole('student'))",
            $journeyClass
        );
    }

    public function test_student_pin_completion_points_are_not_editable_by_student(): void
    {
        $journeyView = file_get_contents(resource_path('views/livewire/student/journey.blade.php'));

        $this->assertIsString($journeyView);
        $this->assertStringContainsString('value="{{ $currentTaskDefaultPoint ?? 0 }}"', $journeyView);
        $this->assertStringNotContainsString('wire:model.live="currentTaskDefaultPoint"', $journeyView);
        $this->assertStringContainsString('readonly', $journeyView);
    }

    public function test_journey_attachments_open_shared_study_viewer_without_nested_bootstrap_image_modal(): void
    {
        $journeyView = file_get_contents(resource_path('views/livewire/student/journey.blade.php'));

        $this->assertIsString($journeyView);
        $this->assertStringContainsString('openAttachmentStudyViewer', $journeyView);
        $this->assertStringContainsString('<livewire:student.attachment-study-viewer', $journeyView);
        $this->assertStringContainsString('surface="journey"', $journeyView);
        $this->assertStringNotContainsString('data-bs-target="#imageAttachmentModal"', $journeyView);
        $this->assertStringNotContainsString('id="imageAttachmentModal"', $journeyView);
        $this->assertStringNotContainsString('w14ReopenTaskAfterImage', $journeyView);
        $this->assertStringNotContainsString('showImageModal', $journeyView);
        $this->assertStringContainsString("const journeyModalIds = ['taskModal', 'completePinModal', 'parentDirectCompleteModal'];", $journeyView);
    }

    public function test_approval_pages_are_embedded_in_standard_blade_layouts(): void
    {
        $parentPage = file_get_contents(resource_path('views/parent/students/task-approvals.blade.php'));
        $teacherPage = file_get_contents(resource_path('views/teacher/classes/task-approvals.blade.php'));

        $this->assertIsString($parentPage);
        $this->assertIsString($teacherPage);
        $this->assertStringContainsString('@extends(\'layouts/layoutMaster\')', $parentPage);
        $this->assertStringContainsString('<livewire:parent.task-approval-work-view', $parentPage);
        $this->assertStringContainsString('@extends(\'layouts/layoutMaster\')', $teacherPage);
        $this->assertStringContainsString('<livewire:teacher.task-approval-work-view', $teacherPage);
    }

    public function test_navbar_profile_dropdown_prefers_first_name_over_username(): void
    {
        $navbar = file_get_contents(resource_path('views/layouts/sections/navbar/navbar-partial.blade.php'));

        $this->assertIsString($navbar);
        $this->assertStringContainsString('$navbarFirstName', $navbar);
        $this->assertStringContainsString('$navbarUser->student?->first_name', $navbar);
        $this->assertStringContainsString('$navbarUser->parent_user?->first_name', $navbar);
        $this->assertStringContainsString('{{ $navbarFirstName }}', $navbar);
        $this->assertStringNotContainsString('{{ Auth::user()->name }}', $navbar);
    }
}
