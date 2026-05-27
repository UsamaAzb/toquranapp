<?php

namespace Tests\Feature\Vocabulary;

use App\Models\User;
use App\Models\VocabularyGameAssignment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ParentVocabularyGameAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createParentAccessTables();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('parent');
    }

    public function test_parent_context_is_explicitly_restricted_for_v1(): void
    {
        $parentUser = User::factory()->create();
        $parentUser->assignRole('parent');

        $parentId = DB::table('parents')->insertGetId([
            'first_name' => 'QA',
            'last_name' => 'Parent',
            'user_id' => $parentUser->id,
            'email' => 'qa.parent@example.test',
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('classes')->insert([
            ['id' => 101, 'title' => 'Parent Class', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 202, 'title' => 'Other Class', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('students')->insert([
            'first_name' => 'Own',
            'last_name' => 'Student',
            'parent_id' => $parentId,
            'current_class_id' => 101,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $assignmentId = DB::table('vocabulary_game_assignments')->insertGetId([
            'vocabulary_set_id' => 999,
            'assigned_by_user_id' => 1,
            'audience_type' => VocabularyGameAssignment::AUDIENCE_CLASS,
            'audience_id' => 202,
            'allowed_games' => json_encode(['hangman', 'missing_letter', 'spelling_choice']),
            'difficulty_policy' => VocabularyGameAssignment::DIFFICULTY_STUDENT_CHOICE,
            'status' => VocabularyGameAssignment::STATUS_ACTIVE,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($parentUser)
            ->get(route('vocabulary.games.assignment', ['assignment' => $assignmentId]))
            ->assertForbidden();
    }

    private function createParentAccessTables(): void
    {
        if (! Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('email')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('classes')) {
            Schema::create('classes', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('current_class_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('vocabulary_game_assignments')) {
            Schema::create('vocabulary_game_assignments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('vocabulary_set_id');
                $table->unsignedBigInteger('assigned_by_user_id');
                $table->string('audience_type');
                $table->unsignedBigInteger('audience_id');
                $table->json('allowed_games');
                $table->string('difficulty_policy');
                $table->string('status');
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }
}
