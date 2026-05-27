<?php

namespace Tests\Feature\Vocabulary;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LegacyHangmanRouteCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::findOrCreate('teacher');
    }

    public function test_legacy_ajax_response_contract_is_gone(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('teacher');

        $this->actingAs($teacher)
            ->get(route('hangman.start'))
            ->assertStatus(410)
            ->assertJson([
                'message' => 'Legacy Floatie AJAX endpoints have been retired. Use Vocabulary Games.',
            ]);
    }
}
