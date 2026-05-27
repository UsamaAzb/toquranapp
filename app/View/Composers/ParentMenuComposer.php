<?php

namespace App\View\Composers;

use Illuminate\View\View;

class ParentMenuComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();
        $parentMenuStudents = collect();
        $parentActiveStudentId = 0;

        if (auth()->check() && $user?->hasRole('parent') && $user?->parent_user) {
            $parentMenuStudents = $user->parent_user
                ->students()
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->orderBy('students.id')
                ->get(['students.id', 'students.first_name', 'students.last_name']);

            $parentActiveStudentId = $this->activeStudentId();
        }

        $view->with([
            'parentMenuStudents' => $parentMenuStudents,
            'parentActiveStudentId' => $parentActiveStudentId,
        ]);
    }

    private function activeStudentId(): int
    {
        $routeStudent = request()->route('student');

        if (is_object($routeStudent) && isset($routeStudent->id)) {
            return (int) $routeStudent->id;
        }

        if (is_numeric($routeStudent)) {
            return (int) $routeStudent;
        }

        return (int) (request()->route('student_id') ?? 0);
    }
}
