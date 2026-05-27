<?php

namespace Tests\Feature;

use App\Enums\AccountHistoryEventType;
use App\Enums\ChildAccountStatus;
use App\Enums\FamilyLifecycleStatus;
use App\Enums\LifecycleReason;
use App\Livewire\Admin\Families\FamilyWorkspace;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\Support\InteractsWithFamilyLifecycleTables;
use Tests\TestCase;

class FamilyLifecycleCoreTest extends TestCase
{
    use InteractsWithFamilyLifecycleTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamilyLifecycleTables();
    }

    public function test_workspace_transfer_to_activation_flow_writes_lifecycle_state_and_history(): void
    {
        Queue::fake();

        $actor = User::factory()->create(['status' => 'active']);
        $this->seedFamilyLifecyclePermissions($actor);
        $this->actingAs($actor);

        $parentUser = User::factory()->create(['status' => 'inactive']);
        $parent = ParentModel::create([
            'first_name' => 'Mariam',
            'last_name' => 'Hany',
            'email' => $parentUser->email,
            'user_id' => $parentUser->id,
            'lifecycle_status' => FamilyLifecycleStatus::PendingActivation->value,
        ]);

        $childUser = User::factory()->create(['status' => 'inactive']);
        $child = Student::create([
            'first_name' => 'Youssef',
            'parent_id' => $parent->id,
            'user_id' => $childUser->id,
            'status' => 'active',
            'account_status' => ChildAccountStatus::PendingActivation->value,
        ]);

        Livewire::test(FamilyWorkspace::class, ['parent' => $parent])
            ->assertSee('Mariam Hany')
            ->assertSee('Pending Activation')
            ->call('activateChild', $child->id)
            ->assertSet('showLifecycleModal', true)
            ->set('lifecycleReason', LifecycleReason::SetupComplete->value)
            ->call('confirmLifecycleAction')
            ->assertHasNoErrors()
            ->call('activateFamily')
            ->assertSet('showLifecycleModal', true)
            ->set('lifecycleReason', LifecycleReason::SetupComplete->value)
            ->call('confirmLifecycleAction')
            ->assertHasNoErrors();

        $this->assertSame(FamilyLifecycleStatus::Active->value, $parent->fresh()->lifecycle_status);
        $this->assertSame(ChildAccountStatus::Active->value, $child->fresh()->account_status);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildActivated->value,
            'subject_type' => 'child',
            'subject_id' => $child->id,
        ]);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::FamilyActivated->value,
            'subject_type' => 'family',
            'subject_id' => $parent->id,
        ]);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ParentActivationEmailQueued->value,
        ]);
        $this->assertDatabaseHas('account_histories', [
            'parent_id' => $parent->id,
            'event_type' => AccountHistoryEventType::ChildActivationEmailQueued->value,
            'subject_id' => $child->id,
        ]);
    }
}
