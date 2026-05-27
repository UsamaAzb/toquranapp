<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\SchoolProgram;
use App\Models\Services_type;
use App\Models\Student;
use App\Models\StudentGift;
use App\Models\User;
use App\Support\RewardGiftVisibility;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class StudentGiftController extends Controller
{
    public function show_reward(int $id)
    {
        $student = Student::findOrFail($id);
        $academicYearId = AcademicYear::currentId();
        $grade_levels = GradeLevel::where('active', 1)->orderBy('level_order', 'asc')->get();
        $programs = SchoolProgram::where('active', 1)->get();
        $services_types = Services_type::where('active', 1)->get();

        $studentgifts = StudentGift::where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->orderByRaw("FIELD(status, 'pending','waiting','reached','redeemed')")
            ->orderBy('points_required', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $rewardPrivacyTeachers = User::role('teacher')
            ->orderBy('name')
            ->orderBy('email')
            ->get(['id', 'name', 'email']);

        $rewardPrivacySelectedTeacherIds = $rewardPrivacyTeachers
            ->filter(fn (User $teacher): bool => RewardGiftVisibility::canViewDetails($teacher))
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();

        return view('admin.Students.student_gifts', compact(
            'studentgifts',
            'student',
            'grade_levels',
            'programs',
            'services_types',
            'rewardPrivacyTeachers',
            'rewardPrivacySelectedTeacherIds'
        ));
    }

    public function updatePrivacy(Request $request)
    {
        // DEF-015 keeps this permission global per teacher for now; student_id
        // stays in the form contract for the later per-student privacy slice.
        $data = $request->validate([
            'teacher_user_ids' => ['nullable', 'array'],
            'teacher_user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
        ]);

        $selectedIds = collect($data['teacher_user_ids'] ?? [])
            ->map(fn ($id): int => (int) $id)
            ->values();

        $permission = Permission::findOrCreate(RewardGiftVisibility::TEACHER_DETAIL_PERMISSION, 'web');

        User::role('teacher')
            ->get()
            ->each(function (User $teacher) use ($selectedIds, $permission): void {
                if ($selectedIds->contains((int) $teacher->id)) {
                    $teacher->givePermissionTo($permission);

                    return;
                }

                if ($teacher->hasPermissionTo($permission)) {
                    $teacher->revokePermissionTo($permission);
                }
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'Reward privacy updated.');
    }

    public function reorder(Request $request)
    {
        $academicYearId = AcademicYear::currentId();
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'distinct', 'exists:student_gifts,id'],
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        $count = StudentGift::where('student_id', $data['student_id'])
            ->where('academic_year_id', $academicYearId)
            ->whereIn('id', $data['order'])
            ->count();

        if ($count !== count($data['order'])) {
            return response()->json(['message' => 'Invalid current-year gift IDs for this student'], 422);
        }

        DB::transaction(function () use ($data, $academicYearId) {
            $targetSlots = StudentGift::where('student_id', $data['student_id'])
                ->where('academic_year_id', $academicYearId)
                ->where('status', StudentGift::STATUS_WAITING)
                ->orderBy('points_required', 'asc')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            if ($targetSlots->pluck('id')->sort()->values()->all() !== collect($data['order'])->sort()->values()->all()) {
                abort(422, 'Reorder payload must include every upcoming gift.');
            }

            $draggedGifts = $targetSlots
                ->mapWithKeys(fn (StudentGift $gift): array => [
                    $gift->id => [
                        'gift_id' => $gift->gift_id,
                        'gift_name' => $gift->gift_name,
                        'gift_image' => $gift->gift_image,
                        'approved_by_id' => $gift->approved_by_id,
                        'approved_by_name' => $gift->approved_by_name,
                    ],
                ]);

            foreach ($targetSlots as $position => $slot) {
                $gift = $draggedGifts[(int) $data['order'][$position]];

                $slot->update([
                    'gift_id' => $gift['gift_id'],
                    'gift_name' => $gift['gift_name'],
                    'gift_image' => $gift['gift_image'],
                    'gift_order' => $position + 1,
                    'approved_by_id' => $gift['approved_by_id'],
                    'approved_by_name' => $gift['approved_by_name'],
                ]);
            }
        });

        return response()->json(['message' => 'Order updated']);
    }

    public function bulkInterval(Request $request)
    {
        $academicYearId = AcademicYear::currentId();
        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'interval' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        try {
            DB::transaction(function () use ($data, $academicYearId) {
                $waitingGifts = StudentGift::where('student_id', $data['student_id'])
                    ->where('academic_year_id', $academicYearId)
                    ->where('status', StudentGift::STATUS_WAITING)
                    ->orderBy('points_required', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->lockForUpdate()
                    ->get();

                if ($waitingGifts->isEmpty()) {
                    return;
                }

                $floor = StudentGift::protectedSchedulingFloorPoints($data['student_id'], $academicYearId) ?? 0;

                StudentGift::whereIn('id', $waitingGifts->pluck('id'))
                    ->update(['points_required' => null]);

                foreach ($waitingGifts as $position => $gift) {
                    $gift->points_required = $floor + ((int) $data['interval'] * ($position + 1));
                    $gift->gift_order = $position + 1;
                    $gift->save();
                }
            });
        } catch (QueryException) {
            return response()->json([
                'message' => 'Could not apply this interval because another gift already uses one of those point values.',
            ], 422);
        }

        return response()->json(['message' => 'Upcoming gift interval updated']);
    }
}
