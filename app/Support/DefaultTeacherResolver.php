<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

final class DefaultTeacherResolver
{
    public function resolve(): ?User
    {
        $configuredTeacher = $this->resolveConfiguredTeacher();

        if ($configuredTeacher) {
            return $configuredTeacher;
        }

        return $this->resolveFirstActiveTeacher();
    }

    public function assignmentPayload(): array
    {
        $teacher = $this->resolve();

        if (! $teacher) {
            throw new RuntimeException('No active default teacher is configured for To Quran transfers.');
        }

        return [
            'user_teacher_coteacher_id' => $teacher->id,
            'teacher_name' => $teacher->name,
        ];
    }

    private function resolveConfiguredTeacher(): ?User
    {
        $email = trim((string) config('toquran.default_teacher_email', ''));

        if ($email === '') {
            return null;
        }

        $teacher = $this->activeUserQuery()
            ->where('email', $email)
            ->first();

        if (! $teacher) {
            return null;
        }

        return $this->userHasTeacherRole($teacher) ? $teacher : null;
    }

    private function resolveFirstActiveTeacher(): ?User
    {
        if (! $this->permissionTablesExist()) {
            return null;
        }

        try {
            return $this->activeUserQuery()
                ->role('teacher')
                ->orderBy('id')
                ->first();
        } catch (Throwable $exception) {
            Log::warning('Failed to resolve first active teacher via role query.', [
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function activeUserQuery(): Builder
    {
        return User::query()
            ->when(Schema::hasColumn('users', 'status'), fn (Builder $query) => $query->where('status', 'active'));
    }

    private function userHasTeacherRole(User $teacher): bool
    {
        if (! $this->permissionTablesExist()) {
            return true;
        }

        try {
            return $teacher->hasRole('teacher');
        } catch (Throwable $exception) {
            Log::warning('Failed to check teacher role for configured default teacher.', [
                'user_id' => $teacher->id,
                'email' => $teacher->email,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function permissionTablesExist(): bool
    {
        return Schema::hasTable('roles')
            && Schema::hasTable('model_has_roles');
    }
}
