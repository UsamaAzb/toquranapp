<?php

namespace Tests\Unit\Vocabulary;

use App\Models\Student;
use App\Models\User;
use App\Services\Vocabulary\VocabularyAccessService;
use Tests\TestCase;

class VocabularyAccessServiceTest extends TestCase
{
    public function test_user_without_student_role_cannot_act_for_student_record(): void
    {
        $user = new User(['id' => 10]);
        $student = new Student(['user_id' => 10]);

        $user->setRelation('roles', collect());
        $user->forceFill(['id' => 10]);

        $this->assertFalse(app(VocabularyAccessService::class)->canUserActForStudent($user, $student));
    }
}
