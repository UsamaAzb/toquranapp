<?php

namespace Tests\Feature\CoreLms;

use Tests\TestCase;

class ParentTeacherTaskApprovalWorkflowTest extends TestCase
{
    public function test_manual_sql_artifacts_define_approval_metadata_and_unique_task_student_contract(): void
    {
        $patch = file_get_contents(base_path('database/manual/patches/2026-05-04-parent-teacher-task-approval-workflow.sql'));
        $verify = file_get_contents(base_path('database/manual/patches/2026-05-04-parent-teacher-task-approval-workflow-verify.sql'));
        $punishmentAgreementPatch = file_get_contents(base_path('database/manual/patches/2026-05-05-punishment-agreements-unique-student-type-title.sql'));

        $this->assertIsString($patch);
        $this->assertIsString($verify);
        $this->assertIsString($punishmentAgreementPatch);

        foreach ([
            'review_submitted_at',
            'approval_source',
            'approved_at',
            'trusted_auto_approval_snapshot',
            'trusted_auto_approval_due_at',
            'trusted_auto_approval_granted_by_id',
            'student_task_approval_events',
            'student_task_approval_settings',
            'uq_session_task_student',
        ] as $needle) {
            $this->assertStringContainsString($needle, $patch);
            $this->assertStringContainsString($needle, $verify);
        }

        $this->assertStringContainsString('`u504065335_vuexy_week14`.`session_task_student`', $verify);
        $this->assertStringContainsString("TABLE_SCHEMA = 'u504065335_vuexy_week14'", $verify);

        foreach ([
            'punishment_agreements',
            'student_id',
            'punishment_type_id',
            'title',
            'uq_pa_student_type_title',
            'HAVING COUNT(*) > 1',
            'student_punishments',
            'merge_then_delete',
            'WHEN 17 THEN 128',
            'WHEN 14 THEN 70',
            'DELETE FROM `u504065335_vuexy_week14`.`punishment_agreements`',
            'ADD UNIQUE KEY `uq_pa_student_type_title`',
        ] as $needle) {
            $this->assertStringContainsString($needle, $punishmentAgreementPatch);
        }
    }

    public function test_teacher_review_entry_is_student_specific_not_first_student_guess(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/front/Teacher/ClassController.php'));
        $view = file_get_contents(resource_path('views/teacher/classes/class_sessions.blade.php'));

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertStringContainsString('$teacher_students', $controller);
        $this->assertStringContainsString('@foreach(($teacher_students ?? collect()) as $teacherStudent)', $view);
        $this->assertStringContainsString("'student' => \$teacherStudent->id", $view);
        $this->assertStringNotContainsString("->value('student_id')", $controller);
    }

    public function test_approval_views_rebuild_selection_from_visible_rows_only(): void
    {
        $parentComponent = file_get_contents(app_path('Livewire/Parent/TaskApprovalWorkView.php'));
        $teacherComponent = file_get_contents(app_path('Livewire/Teacher/TaskApprovalWorkView.php'));

        $this->assertIsString($parentComponent);
        $this->assertIsString($teacherComponent);
        $this->assertStringContainsString('$this->selected = [];', $parentComponent);
        $this->assertStringContainsString('$this->points = [];', $parentComponent);
        $this->assertStringContainsString('$this->selected = [];', $teacherComponent);
        $this->assertStringContainsString('$this->points = [];', $teacherComponent);
    }

    public function test_normal_session_assignment_does_not_prefill_final_student_points(): void
    {
        $teacherSessionsBoard = file_get_contents(app_path('Livewire/Teacher/SessionsBoard.php'));

        $this->assertIsString($teacherSessionsBoard);
        $this->assertStringContainsString("'student_points' => null", $teacherSessionsBoard);
        $this->assertStringNotContainsString("'student_points' => \$task->default_points", $teacherSessionsBoard);
        $this->assertStringContainsString('sessionsQuery', $teacherSessionsBoard);
        $this->assertStringContainsString('$requestedOpenSessionId', $teacherSessionsBoard);
        $this->assertStringContainsString('whereKey($requestedOpenSessionId)', $teacherSessionsBoard);
    }

    public function test_approval_point_inputs_open_only_from_row_edit_action(): void
    {
        $parentComponent = file_get_contents(app_path('Livewire/Parent/TaskApprovalWorkView.php'));
        $teacherComponent = file_get_contents(app_path('Livewire/Teacher/TaskApprovalWorkView.php'));
        $parentView = file_get_contents(resource_path('views/livewire/parent/task-approval-work-view.blade.php'));
        $teacherView = file_get_contents(resource_path('views/livewire/teacher/task-approval-work-view.blade.php'));

        $this->assertIsString($parentComponent);
        $this->assertIsString($teacherComponent);
        $this->assertIsString($parentView);
        $this->assertIsString($teacherView);
        $this->assertStringContainsString('togglePointEditor', $parentComponent);
        $this->assertStringContainsString('togglePointEditor', $teacherComponent);
        $this->assertStringContainsString('maxPointsForPivot', $parentComponent);
        $this->assertStringContainsString('maxPointsForPivot', $teacherComponent);
        $this->assertStringContainsString('pointValidationError', $parentComponent);
        $this->assertStringContainsString('pointValidationError', $teacherComponent);
        $this->assertStringContainsString('pointErrors', $parentComponent);
        $this->assertStringContainsString('pointErrors', $teacherComponent);
        $this->assertStringNotContainsString('$this->points[$pivotId] = max(0, min((int) $value, $max));', $parentComponent);
        $this->assertStringNotContainsString('$this->points[$pivotId] = max(0, min((int) $value, $max));', $teacherComponent);
        $this->assertStringContainsString('tabler-pencil', $parentView);
        $this->assertStringContainsString('tabler-pencil', $teacherView);
        $this->assertStringContainsString('is-invalid', $parentView);
        $this->assertStringContainsString('is-invalid', $teacherView);
        $this->assertStringContainsString('invalid-feedback', $parentView);
        $this->assertStringContainsString('invalid-feedback', $teacherView);
        $this->assertStringContainsString('@if($editingPoints[$task[\'pivot_id\']] ?? false)', $parentView);
        $this->assertStringContainsString('@if($editingPoints[$task[\'pivot_id\']] ?? false)', $teacherView);
    }

    public function test_parent_bulk_review_task_title_links_to_session_task_details(): void
    {
        $parentComponent = file_get_contents(app_path('Livewire/Parent/TaskApprovalWorkView.php'));
        $teacherComponent = file_get_contents(app_path('Livewire/Teacher/TaskApprovalWorkView.php'));
        $parentView = file_get_contents(resource_path('views/livewire/parent/task-approval-work-view.blade.php'));
        $teacherView = file_get_contents(resource_path('views/livewire/teacher/task-approval-work-view.blade.php'));

        $this->assertIsString($parentComponent);
        $this->assertIsString($teacherComponent);
        $this->assertIsString($parentView);
        $this->assertIsString($teacherView);
        $this->assertStringContainsString('details_url', $parentComponent);
        $this->assertStringContainsString('details_url', $teacherComponent);
        $this->assertStringContainsString('taskDetailsUrl', $parentComponent);
        $this->assertStringContainsString('taskDetailsUrl', $teacherComponent);
        $this->assertStringContainsString("'open_session' => \$session->id", $parentComponent);
        $this->assertStringContainsString("'open_session' => \$session->id", $teacherComponent);
        $this->assertStringContainsString("'#task-'.\$task->id", $parentComponent);
        $this->assertStringContainsString("'#task-'.\$task->id", $teacherComponent);
        $this->assertStringContainsString('href="{{ $task[\'details_url\'] }}"', $parentView);
        $this->assertStringContainsString('href="{{ $task[\'details_url\'] }}"', $teacherView);
        $this->assertStringContainsString('Open task details', $parentView);
        $this->assertStringContainsString('Open task details', $teacherView);
        $this->assertStringContainsString('aria-label="Select {{ $task[\'title\'] }}"', $parentView);
        $this->assertStringContainsString('aria-label="Select {{ $task[\'title\'] }}"', $teacherView);
        $this->assertStringContainsString("route('teacher.sessions'", $teacherComponent);
    }

    public function test_parent_child_card_keeps_behavior_visible_and_moves_trusted_setting_to_topbar(): void
    {
        $parentChildrenView = file_get_contents(resource_path('views/parent/students/my-children.blade.php'));
        $addBehaviorView = file_get_contents(resource_path('views/livewire/parent/add-behavior-button.blade.php'));
        $verticalMenu = file_get_contents(resource_path('views/layouts/sections/menu/verticalMenu.blade.php'));

        $this->assertIsString($parentChildrenView);
        $this->assertIsString($addBehaviorView);
        $this->assertIsString($verticalMenu);
        $this->assertStringContainsString("@extends('layouts/layoutMaster')", $parentChildrenView);
        $this->assertStringContainsString('<livewire:parent.behavior-modal />', $parentChildrenView);
        $this->assertStringContainsString('tabler-settings', $parentChildrenView);
        $this->assertStringContainsString("'showLabel' => true", $parentChildrenView);
        $this->assertStringContainsString("'label' => 'Add points'", $parentChildrenView);
        $this->assertStringContainsString("url('/parent/reward-discpline/'.\$stu->id)", $parentChildrenView);
        $this->assertStringContainsString("url('/student/journey/board/'.\$stu->id)", $parentChildrenView);
        $this->assertStringContainsString('Points Lab', $parentChildrenView);
        $this->assertStringContainsString('tabler-chart-bar', $parentChildrenView);
        $this->assertStringNotContainsString('tabler-flask', $parentChildrenView);
        $this->assertStringContainsString('Rewards', $parentChildrenView);
        $this->assertStringContainsString('Review', $parentChildrenView);
        $this->assertStringContainsString('aria-label="Review tasks for {{ $stu->first_name }}"', $parentChildrenView);
        $this->assertStringContainsString('aria-label="{{ $this->ariaLabel }}"', $addBehaviorView);
        $this->assertStringContainsString('Open workspace', $parentChildrenView);
        $this->assertStringContainsString('@livewire(\'parent.add-behavior-button\'', $parentChildrenView);
        $this->assertStringContainsString('trusted-child-approval-setting', $parentChildrenView);
        $this->assertStringNotContainsString('w14-child-card__trusted', $parentChildrenView);
        $this->assertStringNotContainsString("@extends('layouts/blankLayout')", $parentChildrenView);
        $this->assertStringNotContainsString('@livewire(\'teacher.add-behavior-button\'', $parentChildrenView);
        $this->assertStringNotContainsString('app-zone.css', $parentChildrenView);
        $this->assertStringContainsString('visually-hidden', $addBehaviorView);
        $this->assertStringContainsString('$parentMenuStudents', $verticalMenu);
        $this->assertStringContainsString('tabler-user', $verticalMenu);
        $this->assertStringNotContainsString('tabler-user-circle', $verticalMenu);
        $this->assertStringContainsString("route('student.workplace', \$menuStudentId)", $verticalMenu);
        $this->assertStringContainsString("url('/parent/reward-discpline/'.\$menuStudentId)", $verticalMenu);
        $this->assertStringContainsString("url('/student/journey/board/'.\$menuStudentId)", $verticalMenu);
        $this->assertStringContainsString("route('parent.task-approvals', \$menuStudentId)", $verticalMenu);
    }

    public function test_review_task_pages_use_shared_vuexy_shell_and_responsive_approval_workspace(): void
    {
        $parentWrapper = file_get_contents(resource_path('views/parent/students/task-approvals.blade.php'));
        $parentComponent = file_get_contents(app_path('Livewire/Parent/TaskApprovalWorkView.php'));
        $teacherComponent = file_get_contents(app_path('Livewire/Teacher/TaskApprovalWorkView.php'));
        $parentView = file_get_contents(resource_path('views/livewire/parent/task-approval-work-view.blade.php'));
        $teacherView = file_get_contents(resource_path('views/livewire/teacher/task-approval-work-view.blade.php'));
        $sharedStyles = file_get_contents(resource_path('views/components/task-approval-work-styles.blade.php'));

        $this->assertIsString($parentWrapper);
        $this->assertIsString($parentComponent);
        $this->assertIsString($teacherComponent);
        $this->assertIsString($parentView);
        $this->assertIsString($teacherView);
        $this->assertIsString($sharedStyles);

        $this->assertStringContainsString("@extends('layouts/layoutMaster')", $parentWrapper);
        $this->assertStringContainsString("#[Layout('layouts.layoutMaster')]", $parentComponent);
        $this->assertStringNotContainsString("@extends('layouts/blankLayout')", $parentWrapper);
        $this->assertStringNotContainsString('app-zone.css', $parentView);

        foreach ([
            'w14-approval-work',
            'w14-approval-hero',
            'Approve selected',
            'w14-approval-submit',
        ] as $needle) {
            $this->assertStringContainsString($needle, $parentView);
            $this->assertStringContainsString($needle, $teacherView);
        }

        foreach ([
            'session_title_short',
            '$currentPoints',
            'No points',
            'w14-approval-points-badge',
            'w14-approval-task__editor',
            'Default {{ $task[\'default_points\'] }}. Max {{ $task[\'max_points\'] }}.',
        ] as $needle) {
            $this->assertStringContainsString($needle, $parentView);
            $this->assertStringContainsString($needle, $teacherView);
        }

        $this->assertStringContainsString('$this->selected[$pivot->id] = false;', $parentComponent);
        $this->assertStringContainsString('$this->selected[$pivot->id] = false;', $teacherComponent);
        $this->assertStringNotContainsString('toggleSubjectCollapse', $parentComponent);
        $this->assertStringContainsString('x-data="{ open: false }"', $parentView);
        $this->assertStringContainsString('@click="open = !open"', $parentView);
        $this->assertStringContainsString('x-data="{ open: false }"', $teacherView);
        $this->assertStringContainsString('@click="open = !open"', $teacherView);
        $this->assertStringContainsString('toggleAll(true)', $parentView);
        $this->assertStringContainsString('approveSubject', $parentView);
        $this->assertStringContainsString('toggleAllTasks(true)', $teacherView);

        $this->assertStringContainsString('grid-template-columns: auto minmax(0, 1fr) auto', $sharedStyles);
        $this->assertStringContainsString('w14-approval-subject-title', $sharedStyles);
        $this->assertStringContainsString('w14-approval-task__editor', $sharedStyles);
        $this->assertStringContainsString('w14-approval-points-badge', $sharedStyles);
        $this->assertStringContainsString('w14-approval-selected-badge', $sharedStyles);
        $this->assertStringContainsString('--w14-approval-mint-bg', $sharedStyles);
        $this->assertStringContainsString('w14-approval-meta-badge__text', $sharedStyles);
        $this->assertStringContainsString('text-overflow: ellipsis', $sharedStyles);
        $this->assertStringContainsString('white-space: nowrap', $sharedStyles);
        $this->assertStringContainsString('max-width: 100%;', $sharedStyles);
        $this->assertStringContainsString('grid-template-columns: 1fr', $sharedStyles);
        $this->assertStringContainsString('@media (max-width: 575.98px)', $sharedStyles);
        $this->assertStringContainsString('wire:loading.attr="disabled"', $parentView);
        $this->assertStringContainsString('wire:loading.attr="disabled"', $teacherView);
    }

    public function test_teacher_session_surface_exposes_student_specific_quick_actions(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/front/Teacher/ClassController.php'));
        $view = file_get_contents(resource_path('views/teacher/classes/class_sessions.blade.php'));

        $this->assertIsString($controller);
        $this->assertIsString($view);
        $this->assertStringContainsString('$reviewCounts', $controller);
        $this->assertStringContainsString('Student quick actions', $view);
        $this->assertStringContainsString('w14-teacher-quick-actions', $view);
        $this->assertStringContainsString('w14-teacher-student-action-row', $view);
        $this->assertStringContainsString('w14-teacher-student-action-buttons', $view);
        $this->assertStringContainsString('@media (max-width: 430px)', $view);
        $this->assertStringContainsString('teacher.task-approvals', $view);
        $this->assertStringContainsString('@livewire(\'teacher.add-behavior-button\'', $view);
        $this->assertStringContainsString('$reviewCount', $view);
    }

    public function test_points_lab_subject_scope_parent_access_and_task_history_contracts_are_explicit(): void
    {
        $pointsLab = file_get_contents(app_path('Livewire/Teacher/RewardDisciplinePoints.php'));
        $pointsLabView = file_get_contents(resource_path('views/livewire/teacher/reward-discipline-points.blade.php'));
        $historyPartial = file_get_contents(resource_path('views/livewire/teacher/partials/behavior-history-list.blade.php'));
        $parentPointsPage = file_get_contents(resource_path('views/parent/students/RewardDisciplinePoints/index.blade.php'));
        $parentModal = file_get_contents(app_path('Livewire/Parent/BehaviorModal.php'));
        $subjectResolver = file_get_contents(app_path('Support/ParentBehaviorSubjectResolver.php'));
        $giftBoard = file_get_contents(app_path('Livewire/Ui/GiftBoard.php'));
        $pointsProgress = file_get_contents(app_path('Livewire/Ui/PointsProgress.php'));

        foreach ([$pointsLab, $pointsLabView, $historyPartial, $parentPointsPage, $parentModal, $subjectResolver, $giftBoard, $pointsProgress] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString('public ?int $selectedSubjectId = null;', $pointsLab);
        $this->assertStringContainsString('protected function loadSubjectFilters(): void', $pointsLab);
        $this->assertStringContainsString("->from('students_subjects as ss')", $pointsLab);
        $this->assertStringContainsString("->join('grade_level_subjects as gls'", $pointsLab);
        $this->assertStringContainsString("->join('subjects as subjects'", $pointsLab);
        $this->assertStringContainsString('protected function applyPointsLabDisciplineScope', $pointsLab);
        $this->assertStringContainsString('$tscAlias.\'.id\'', $pointsLab);
        $this->assertStringContainsString('$tscAlias.\'.subject_id\'', $pointsLab);
        $this->assertStringContainsString('protected function taskHistoryBaseQuery()', $pointsLab);
        $this->assertStringContainsString('protected function taskHistoryStateSignature(): string', $pointsLab);
        $this->assertStringContainsString('$this->historyTab === \'tasks\' ? $this->taskHistoryStateSignature() : \'\'', $pointsLab);
        $this->assertStringContainsString('SessionTaskStudent::STATUS_COMPLETED', $pointsLab);
        $this->assertStringContainsString('StudentTaskApprovalEvent::TYPE_COMPLETED_BY_PARENT', $pointsLab);
        $this->assertStringNotContainsString('TYPE_SUBMITTED_FOR_REVIEW,', $pointsLab);

        $this->assertStringContainsString('setSubjectFilter', $pointsLabView);
        $this->assertStringContainsString('All Subjects', $pointsLabView);
        $this->assertStringContainsString("\$userRole !== 'teacher' && count(\$subjectFilters ?? []) > 0", $pointsLabView);
        $this->assertStringNotContainsString('Readonly subject scope', $pointsLabView);
        $this->assertStringContainsString('\\App\\Livewire\\Parent\\BehaviorModal::class', $pointsLab);
        $this->assertStringContainsString('<livewire:parent.behavior-modal />', $parentPointsPage);
        $this->assertStringContainsString('Student History', $historyPartial);
        $this->assertStringContainsString("setHistoryTab('behavior')", $historyPartial);
        $this->assertStringContainsString("setHistoryTab('tasks')", $historyPartial);
        $this->assertStringContainsString('Completed tasks', $historyPartial);
        $this->assertStringContainsString('No completed tasks match these filters.', $historyPartial);

        $this->assertStringContainsString('ParentBehaviorSubjectResolver::ERROR_MESSAGE', $parentModal);
        $this->assertStringContainsString("reward-discipline-points:refresh", $parentModal);
        $this->assertStringContainsString("Config::get('toquran.parent_behavior_subject_id'", $subjectResolver);
        $this->assertStringContainsString('wellBeingSubjectIdsForStudent', $subjectResolver);
        $this->assertStringContainsString("->from('students_subjects as ss')", $subjectResolver);
        $this->assertStringContainsString('titleLooksLikeWellBeing', $subjectResolver);
        $this->assertStringContainsString("preg_replace('/[^a-z]+/', '', strtolower(\$title))", $subjectResolver);
        $this->assertStringContainsString('withActiveStudentSubject($studentId)', $subjectResolver);
        $this->assertStringContainsString('wellbeing', $subjectResolver);

        $this->assertStringContainsString('protected function rewardPinOwnerUserId(): int', $giftBoard);
        $this->assertStringContainsString('protected function rewardPinOwnerUserId(): int', $pointsProgress);
        $this->assertStringContainsString('hasRole(\'parent\')', $giftBoard);
        $this->assertStringContainsString('hasRole(\'parent\')', $pointsProgress);
        $this->assertStringContainsString('$this->student->user_id', $giftBoard);
        $this->assertStringContainsString('whereKey($this->studentId)->value(\'user_id\')', $pointsProgress);
    }

    public function test_behavior_point_modals_enforce_server_side_point_ranges(): void
    {
        $parentModal = file_get_contents(app_path('Livewire/Parent/BehaviorModal.php'));
        $teacherModal = file_get_contents(app_path('Livewire/Teacher/BehaviorModal.php'));

        $this->assertIsString($parentModal);
        $this->assertIsString($teacherModal);

        foreach ([$parentModal, $teacherModal] as $modal) {
            $this->assertStringContainsString('use Livewire\Attributes\Computed;', $modal);
            $this->assertStringContainsString('use Livewire\Attributes\On;', $modal);
            $this->assertStringContainsString("#[On('openAddBehaviorModal')]", $modal);
            $this->assertStringContainsString('#[Computed]', $modal);
            $this->assertStringContainsString('public function selectedBehaviorType(): ?string', $modal);
            $this->assertStringContainsString("'created_at' => now()->toDateString(),", $modal);
            $this->assertStringContainsString('behaviorPointBounds', $modal);
            $this->assertStringContainsString('behaviorPointRules', $modal);
            $this->assertStringContainsString("'Slip' => [1, 5]", $modal);
            $this->assertStringContainsString("'No Way' => [5, 10]", $modal);
            $this->assertStringContainsString('\'pointsInput\' => [\'required\', \'integer\', \'min:\'.$min, \'max:\'.$max]', $modal);
            $this->assertStringNotContainsString("'pointsInput' => ['required', 'integer', 'min:1']", $modal);
            $this->assertStringNotContainsString('protected $listeners', $modal);
            $this->assertStringNotContainsString('getSelectedBehaviorTypeProperty', $modal);
            $this->assertStringNotContainsString("'created_at' => now(),", $modal);
        }
    }
}
