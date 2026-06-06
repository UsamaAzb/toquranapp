<?php

namespace Tests\Feature;

use App\Livewire\Admin\Students\PunishmentAgreementsTabs;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PunishmentAgreementsTabsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createComponentTables();
    }

    public function test_admin_can_edit_consequence_agreement_title_type_and_status(): void
    {
        $admin = User::factory()->create();
        Role::create(['name' => 'admin']);
        $admin->assignRole('admin');

        $student = Student::create([
            'first_name' => 'Karim',
            'last_name' => 'Learner',
            'status' => 'active',
            'account_status' => 'active',
        ]);

        $minorTypeId = DB::table('punishment_types')->insertGetId([
            'title' => 'Minor Slip',
            'active' => 1,
        ]);
        $seriousTypeId = DB::table('punishment_types')->insertGetId([
            'title' => 'Serious Action',
            'active' => 1,
        ]);

        $agreementId = DB::table('punishment_agreements')->insertGetId([
            'student_id' => $student->id,
            'punishment_type_id' => $minorTypeId,
            'title' => 'Old agreement text',
            'status' => 'active',
        ]);

        Livewire::actingAs($admin)
            ->test(PunishmentAgreementsTabs::class, ['studentId' => $student->id])
            ->call('editAgreement', $agreementId)
            ->set('editForm.punishment_type_id', $seriousTypeId)
            ->set('editForm.title', 'Updated agreement text')
            ->set('editForm.status', 'inactive')
            ->call('updateAgreement')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('punishment_agreements', [
            'id' => $agreementId,
            'student_id' => $student->id,
            'punishment_type_id' => $seriousTypeId,
            'title' => 'Updated agreement text',
            'status' => 'inactive',
        ]);
    }

    public function test_customized_agreement_sorts_first_after_none_button(): void
    {
        $admin = User::factory()->create();
        Role::create(['name' => 'admin']);
        $admin->assignRole('admin');

        $student = Student::create([
            'first_name' => 'Karim',
            'last_name' => 'Learner',
            'status' => 'active',
            'account_status' => 'active',
        ]);

        $typeId = DB::table('punishment_types')->insertGetId([
            'title' => 'Minor Slip',
            'active' => 1,
        ]);

        DB::table('punishment_agreements')->insert([
            [
                'student_id' => $student->id,
                'punishment_type_id' => $typeId,
                'title' => 'Lose 2-3 reward points',
                'status' => 'active',
            ],
            [
                'student_id' => $student->id,
                'punishment_type_id' => $typeId,
                'title' => 'Customized',
                'status' => 'active',
            ],
        ]);

        $component = Livewire::actingAs($admin)
            ->test(PunishmentAgreementsTabs::class, ['studentId' => $student->id]);

        $this->assertSame('Customized', $component->get('agreements')[0]['title']);

        $component->set('apply.punishment_type_id', $typeId)
            ->call('loadApplyAgreements');

        $this->assertSame('Customized', $component->get('applyAgreements')[0]['title']);
    }

    private function createComponentTables(): void
    {
        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('status')->nullable();
                $table->string('account_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('punishment_types')) {
            Schema::create('punishment_types', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->boolean('active')->default(true);
            });
        }

        if (! Schema::hasTable('punishments_suggestions')) {
            Schema::create('punishments_suggestions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('punishment_type_id')->nullable();
                $table->string('suggestion_text');
            });
        }

        if (! Schema::hasTable('punishment_agreements')) {
            Schema::create('punishment_agreements', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('punishment_type_id')->nullable();
                $table->string('title');
                $table->string('status')->default('active');
            });
        }

        if (! Schema::hasTable('student_punishments')) {
            Schema::create('student_punishments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('punishment_agreement_id');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('teacher_subject_class')->nullable();
                $table->unsignedBigInteger('created_by_id')->nullable();
                $table->date('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('subjects')) {
            Schema::create('subjects', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
            });
        }
    }
}
