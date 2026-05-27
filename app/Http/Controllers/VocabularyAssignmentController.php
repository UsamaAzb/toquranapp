<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\VocabularyGameAssignment;
use App\Models\VocabularySet;
use App\Services\Vocabulary\VocabularyAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VocabularyAssignmentController extends Controller
{
    public function __construct(
        private readonly VocabularyAccessService $accessService,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user?->hasAnyRole(['teacher', 'admin', 'super_admin', 'owner']), 403);

        $data = $request->validate([
            'vocabulary_set_id' => ['required', 'integer'],
            'audience_type' => ['required', 'in:student,class'],
            'audience_id' => ['required', 'integer', 'min:1'],
        ]);

        $setQuery = VocabularySet::query()
            ->where('visibility', '!=', VocabularySet::VISIBILITY_ARCHIVED);

        if (! $user->hasAnyRole(['admin', 'super_admin', 'owner'])) {
            $setQuery->visibleToTeachers((int) $user->id);
        }

        $set = $setQuery->findOrFail($data['vocabulary_set_id']);
        abort_unless($set->canBeLaunched(), 422);

        if (! $user->hasAnyRole(['admin', 'super_admin', 'owner'])) {
            $this->authorizeTeacherAudience($user, (string) $data['audience_type'], (int) $data['audience_id']);
        }

        VocabularyGameAssignment::query()->create([
            'vocabulary_set_id' => $set->id,
            'assigned_by_user_id' => $user->id,
            'audience_type' => $data['audience_type'],
            'audience_id' => $data['audience_id'],
            'allowed_games' => ['hangman', 'missing_letter', 'spelling_choice'],
            'difficulty_policy' => VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE,
            'status' => VocabularyGameAssignment::STATUS_ACTIVE,
        ]);

        return back()->with('status', 'Vocabulary game assignment created.');
    }

    private function authorizeTeacherAudience(User $user, string $audienceType, int $audienceId): void
    {
        if ($audienceType === VocabularyGameAssignment::AUDIENCE_CLASS) {
            abort_unless($this->accessService->teacherCanUseClassContext($user, $audienceId), 403);

            return;
        }

        $student = Student::query()->findOrFail($audienceId);
        $classId = (int) $student->current_class_id;

        abort_unless(
            $classId > 0 && $this->accessService->teacherCanUseClassContext($user, $classId),
            403
        );
    }

    public static function assignmentUrl(VocabularyGameAssignment $assignment, ?string $game = null, ?string $difficulty = null): string
    {
        return $assignment->playUrl($game, $difficulty);
    }
}
