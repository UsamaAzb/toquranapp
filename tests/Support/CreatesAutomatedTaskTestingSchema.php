<?php

namespace Tests\Support;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesAutomatedTaskTestingSchema
{
    protected function createAutomatedTaskSchema(): void
    {
        if (! Schema::hasTable('academic_years')) {
            Schema::create('academic_years', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->boolean('is_current')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('subjects')) {
            Schema::create('subjects', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('email')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->boolean('active')->default(true);
                $table->string('lifecycle_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('parents', 'user_id')) {
            Schema::table('parents', fn (Blueprint $table) => $table->unsignedBigInteger('user_id')->nullable());
        }

        if (! Schema::hasColumn('parents', 'lifecycle_status')) {
            Schema::table('parents', fn (Blueprint $table) => $table->string('lifecycle_status')->nullable());
        }

        if (! Schema::hasTable('classes')) {
            Schema::create('classes', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students')) {
            Schema::create('students', function (Blueprint $table): void {
                $table->id();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('current_class_id')->nullable();
                $table->string('status')->nullable();
                $table->string('account_status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('students', 'user_id')) {
            Schema::table('students', fn (Blueprint $table) => $table->unsignedBigInteger('user_id')->nullable());
        }

        if (! Schema::hasTable('grade_level_subjects')) {
            Schema::create('grade_level_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->string('type')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('class_subjects')) {
            Schema::create('class_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('grade_level_subject_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('students_subjects')) {
            Schema::create('students_subjects', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('grade_level_subject_id')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->string('status')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
            });
        }

        if (! Schema::hasTable('teacher_subject_classes')) {
            Schema::create('teacher_subject_classes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_teacher_coteacher_id')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('grade_id')->nullable();
                $table->string('class_name')->nullable();
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('task_types')) {
            Schema::create('task_types', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->string('table_name')->nullable();
                $table->integer('default_points')->nullable();
                $table->integer('max_points')->nullable();
            });
        }

        if (! Schema::hasTable('main_daily_session_templates')) {
            Schema::create('main_daily_session_templates', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('created_by_user_id');
                $table->string('recurrence_kind')->default('daily');
                $table->string('recurrence_weekdays')->nullable();
                $table->unsignedTinyInteger('recurrence_day_of_month')->nullable();
                $table->unsignedTinyInteger('recurrence_interval')->default(1);
                $table->string('status')->default('draft');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('main_daily_session_versions')) {
            Schema::create('main_daily_session_versions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('main_daily_session_template_id');
                $table->string('display_name');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('main_daily_session_main_tasks')) {
            Schema::create('main_daily_session_main_tasks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('main_daily_session_template_id');
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('task_type_id')->nullable();
                $table->integer('default_points')->nullable();
                $table->integer('max_points')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('main_daily_session_main_task_attachments')) {
            Schema::create('main_daily_session_main_task_attachments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('main_task_id');
                $table->string('type');
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('path')->nullable();
                $table->string('url')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('library_sections')) {
            Schema::create('library_sections', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('owner_user_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->unsignedBigInteger('created_by_user_id');
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('library_resources')) {
            Schema::create('library_resources', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('owner_user_id');
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('library_section_id');
                $table->string('resource_type');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('status')->default('active');
                $table->string('storage_disk')->nullable();
                $table->string('file_path', 2048)->nullable();
                $table->string('original_filename')->nullable();
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->string('external_url', 2048)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->unsignedBigInteger('created_by_user_id');
                $table->timestamp('archived_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('main_daily_session_version_tasks')) {
            Schema::create('main_daily_session_version_tasks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('version_id');
                $table->unsignedBigInteger('main_task_id');
                $table->text('description_override')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('main_daily_session_subscriptions')) {
            Schema::create('main_daily_session_subscriptions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('main_daily_session_template_id');
                $table->boolean('is_active')->default(true);
                $table->timestamp('paused_at')->nullable();
                $table->timestamp('start_at')->nullable();
                $table->timestamp('end_at')->nullable();
                $table->date('last_generated_date')->nullable();
                $table->date('paused_through_date')->nullable();
            });
        }

        if (! Schema::hasTable('main_daily_session_student_assignments')) {
            Schema::create('main_daily_session_student_assignments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('main_daily_session_template_id');
                $table->unsignedBigInteger('version_id');
                $table->date('effective_from_date');
                $table->date('effective_to_date')->nullable();
                $table->unsignedBigInteger('assigned_by_user_id');
                $table->timestamps();
                $table->unique(
                    ['student_id', 'main_daily_session_template_id', 'effective_from_date'],
                    'uq_mdssa_student_template_from'
                );
            });
        }

        if (! Schema::hasTable('main_daily_session_student_assignment_history')) {
            Schema::create('main_daily_session_student_assignment_history', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('main_daily_session_template_id');
                $table->string('event_type');
                $table->unsignedBigInteger('from_version_id')->nullable();
                $table->string('from_version_display_name')->nullable();
                $table->unsignedBigInteger('to_version_id')->nullable();
                $table->string('to_version_display_name')->nullable();
                $table->unsignedBigInteger('actor_user_id');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        DB::table('academic_years')->updateOrInsert(
            ['id' => 1],
            ['title' => '2026-2027', 'is_current' => 1]
        );
    }

    protected function createAutomatedTaskGenerationRuntimeTables(): void
    {
        if (! Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('teacher_subject_classes_id')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->unsignedBigInteger('grade_level_id')->nullable();
                $table->unsignedBigInteger('unit_type_id')->nullable();
                $table->string('title')->nullable();
                $table->string('status')->nullable();
                $table->boolean('is_interdisciplinary')->default(false);
            });
        }

        if (! Schema::hasTable('class_sessions')) {
            Schema::create('class_sessions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('teacher_subject_classes_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('grade_id')->nullable();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->date('date')->nullable();
                $table->string('session_start_time')->nullable();
                $table->string('session_end_time')->nullable();
                $table->unsignedBigInteger('class_subject_id')->nullable();
                $table->string('title')->nullable();
                $table->unsignedBigInteger('daily_session_id')->nullable();
                $table->date('generated_for_date')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->unsignedBigInteger('main_daily_session_template_id')->nullable();
                $table->unique(
                    ['main_daily_session_template_id', 'student_id', 'generated_for_date'],
                    'uq_class_sessions_automated'
                );
            });
        }

        if (! Schema::hasTable('session_materials')) {
            Schema::create('session_materials', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_id')->nullable();
                $table->unsignedBigInteger('teacher_subject_classes_id')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('grade_id')->nullable();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('status')->nullable();
                $table->string('assign_to_all')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->text('task_desc')->nullable();
                $table->text('class_work_desc')->nullable();
                $table->unique('session_id', 'uq_session_materials_session');
            });
        }

        if (! Schema::hasTable('session_tasks')) {
            Schema::create('session_tasks', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->unsignedBigInteger('class_session_id')->nullable();
                $table->unsignedBigInteger('taskable_id')->nullable();
                $table->unsignedBigInteger('task_type_id')->nullable();
                $table->date('due_date')->nullable();
                $table->string('assign_to_all')->nullable();
                $table->text('description')->nullable();
                $table->integer('default_points')->default(0);
                $table->integer('max_points')->default(0);
                $table->integer('marks')->nullable();
                $table->unsignedBigInteger('session_material_id')->nullable();
                $table->unsignedBigInteger('created_by_teacher_id')->nullable();
                $table->string('status')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->integer('sort')->nullable();
                $table->string('version_display_name_snapshot')->nullable();
                $table->unsignedBigInteger('source_version_task_id_snapshot')->nullable();
                $table->unique(
                    ['class_session_id', 'source_version_task_id_snapshot'],
                    'uq_session_tasks_automated'
                );
            });
        }

        if (! Schema::hasTable('session_task_student')) {
            Schema::create('session_task_student', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('session_task_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->integer('student_points')->nullable();
                $table->timestamp('submitted_at')->nullable();
                $table->string('assign_to_all')->nullable();
                $table->string('status')->nullable();
                $table->string('flag')->nullable();
                $table->unique(['session_task_id', 'student_id'], 'uq_session_task_student');
            });
        }

        if (! Schema::hasTable('attachment_files')) {
            Schema::create('attachment_files', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->string('type')->nullable();
                $table->string('path')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->unsignedBigInteger('class_id')->nullable();
                $table->unsignedBigInteger('teacher_subject_class_id')->nullable();
                $table->unsignedBigInteger('session_task_id')->nullable();
            });
        }

        if (! Schema::hasTable('student_gifts')) {
            Schema::create('student_gifts', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('student_id')->nullable();
                $table->unsignedBigInteger('gift_id')->nullable();
                $table->string('gift_name')->nullable();
                $table->string('gift_image')->nullable();
                $table->integer('points_required')->nullable();
                $table->string('status')->nullable();
                $table->unsignedBigInteger('approved_by_id')->nullable();
                $table->string('approved_by_name')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('reached_at')->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->integer('gift_order')->nullable();
            });
        }

        if (! Schema::hasTable('student_gift_points_history')) {
            Schema::create('student_gift_points_history', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->integer('points')->default(0);
                $table->date('date')->nullable();
                $table->string('status')->nullable();
                $table->string('sign')->nullable();
            });
        }
    }

    protected function createDifferentiatedTaskSchema(): void
    {
        $this->createAutomatedTaskSchema();
        $this->createAutomatedTaskGenerationRuntimeTables();

        if (! Schema::hasTable('differentiated_tasks')) {
            Schema::create('differentiated_tasks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('created_by_user_id');
                $table->unsignedBigInteger('task_type_id')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('recurrence_kind')->default('daily');
                $table->string('recurrence_weekdays')->nullable();
                $table->unsignedTinyInteger('recurrence_day_of_month')->nullable();
                $table->unsignedTinyInteger('recurrence_interval')->default(1);
                $table->integer('default_points')->default(0);
                $table->integer('max_points')->default(0);
                $table->integer('sort_order')->default(0);
                $table->string('status')->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('differentiated_task_versions')) {
            Schema::create('differentiated_task_versions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('differentiated_task_id');
                $table->string('display_name');
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['differentiated_task_id', 'display_name'], 'uq_dtv_task_display_name');
            });
        }

        if (! Schema::hasTable('differentiated_task_attachments')) {
            Schema::create('differentiated_task_attachments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('differentiated_task_id');
                $table->string('type');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('path', 1000)->nullable();
                $table->string('url', 2000)->nullable();
                $table->unsignedInteger('file_size')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('differentiated_task_version_attachments')) {
            Schema::create('differentiated_task_version_attachments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('version_id');
                $table->unsignedBigInteger('attachment_id');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['version_id', 'attachment_id'], 'uq_dtva_version_attachment');
            });
        }

        if (! Schema::hasTable('differentiated_task_student_assignments')) {
            Schema::create('differentiated_task_student_assignments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('differentiated_task_id');
                $table->unsignedBigInteger('version_id');
                $table->date('effective_from_date');
                $table->date('effective_to_date')->nullable();
                $table->unsignedBigInteger('assigned_by_user_id');
                $table->timestamps();
                $table->unique(
                    ['student_id', 'differentiated_task_id', 'effective_from_date'],
                    'uq_dtsa_student_task_from'
                );
            });
        }

        if (! Schema::hasTable('differentiated_task_student_generation_states')) {
            Schema::create('differentiated_task_student_generation_states', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('differentiated_task_id');
                $table->boolean('is_active')->default(true);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->date('last_generated_date')->nullable();
                $table->date('paused_through_date')->nullable();
                $table->timestamps();
                $table->unique(['student_id', 'differentiated_task_id'], 'uq_dtsgs_student_task');
            });
        }

        if (! Schema::hasTable('differentiated_task_student_assignment_history')) {
            Schema::create('differentiated_task_student_assignment_history', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('differentiated_task_id');
                $table->string('event_type');
                $table->unsignedBigInteger('from_version_id')->nullable();
                $table->string('from_version_display_name')->nullable();
                $table->unsignedBigInteger('to_version_id')->nullable();
                $table->string('to_version_display_name')->nullable();
                $table->unsignedBigInteger('actor_user_id');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! Schema::hasColumn('class_sessions', 'differentiated_task_id')) {
            Schema::table('class_sessions', function (Blueprint $table): void {
                $table->unsignedBigInteger('differentiated_task_id')->nullable();
            });
        }

        if (! $this->testingIndexExists('class_sessions', 'uq_class_sessions_dt')) {
            Schema::table('class_sessions', function (Blueprint $table): void {
                $table->unique(
                    ['differentiated_task_id', 'student_id', 'generated_for_date'],
                    'uq_class_sessions_dt'
                );
            });
        }

        foreach ([
            'source_differentiated_task_id_snapshot',
            'source_differentiated_task_version_id_snapshot',
            'source_differentiated_task_assignment_id_snapshot',
        ] as $column) {
            if (! Schema::hasColumn('session_tasks', $column)) {
                Schema::table('session_tasks', function (Blueprint $table) use ($column): void {
                    $table->unsignedBigInteger($column)->nullable();
                });
            }
        }

        if (! $this->testingIndexExists('session_tasks', 'uq_session_tasks_dt')) {
            Schema::table('session_tasks', function (Blueprint $table): void {
                $table->unique(
                    [
                        'class_session_id',
                        'source_differentiated_task_id_snapshot',
                    ],
                    'uq_session_tasks_dt'
                );
            });
        }
    }

    protected function createSeriesTaskSchema(): void
    {
        $this->createAutomatedTaskSchema();
        $this->createAutomatedTaskGenerationRuntimeTables();

        if (! Schema::hasTable('series_tasks')) {
            Schema::create('series_tasks', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('subject_id');
                $table->unsignedBigInteger('created_by_user_id');
                $table->unsignedBigInteger('task_type_id')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('library_collection_type');
                $table->unsignedBigInteger('library_collection_id')->nullable();
                $table->string('recurrence_kind')->default('daily');
                $table->string('recurrence_weekdays')->nullable();
                $table->unsignedTinyInteger('recurrence_day_of_month')->nullable();
                $table->unsignedTinyInteger('recurrence_interval')->default(1);
                $table->string('sequence_behavior')->default('stop_at_end');
                $table->string('release_policy')->default('continuous');
                $table->integer('default_points')->default(0);
                $table->integer('max_points')->default(0);
                $table->integer('sort_order')->default(0);
                $table->string('status')->default('draft');
                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('series_tasks', 'release_policy')) {
            Schema::table('series_tasks', function (Blueprint $table): void {
                $table->string('release_policy')->default('continuous');
            });
        }

        if (! Schema::hasTable('series_task_versions')) {
            Schema::create('series_task_versions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('series_task_id');
                $table->string('display_name');
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['series_task_id', 'display_name'], 'uq_stv_task_display_name');
                $table->unique(['series_task_id', 'id'], 'uq_stv_task_id');
            });
        }

        if (! Schema::hasTable('series_task_version_items')) {
            Schema::create('series_task_version_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('version_id');
                $table->string('library_source_type');
                $table->unsignedBigInteger('library_source_id');
                $table->string('library_title_snapshot');
                $table->string('library_url_snapshot', 2000)->nullable();
                $table->text('library_summary_snapshot')->nullable();
                $table->unsignedSmallInteger('sequence_position');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['version_id', 'sequence_position'], 'uq_stvi_version_position');
                $table->unique(['version_id', 'library_source_type', 'library_source_id'], 'uq_stvi_version_source');
            });
        }

        if (! Schema::hasTable('series_task_student_assignments')) {
            Schema::create('series_task_student_assignments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('series_task_id');
                $table->unsignedBigInteger('version_id');
                $table->unsignedSmallInteger('start_sequence_position')->default(1);
                $table->date('effective_from_date');
                $table->date('effective_to_date')->nullable();
                $table->unsignedBigInteger('assigned_by_user_id');
                $table->timestamps();
                $table->unique(['student_id', 'series_task_id', 'effective_from_date'], 'uq_stsa_student_task_from');
            });
        }

        if (! Schema::hasTable('series_task_student_generation_states')) {
            Schema::create('series_task_student_generation_states', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('series_task_id');
                $table->unsignedBigInteger('current_version_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->unsignedSmallInteger('next_sequence_position')->default(1);
                $table->unsignedSmallInteger('last_delivered_sequence_position')->nullable();
                $table->date('last_generated_date')->nullable();
                $table->date('paused_through_date')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->unique(['student_id', 'series_task_id'], 'uq_stsgs_student_task');
            });
        }

        if (! Schema::hasTable('series_task_student_assignment_history')) {
            Schema::create('series_task_student_assignment_history', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('series_task_id');
                $table->string('event_type');
                $table->unsignedBigInteger('from_version_id')->nullable();
                $table->string('from_version_display_name')->nullable();
                $table->unsignedSmallInteger('from_sequence_position')->nullable();
                $table->unsignedBigInteger('to_version_id')->nullable();
                $table->string('to_version_display_name')->nullable();
                $table->unsignedSmallInteger('to_sequence_position')->nullable();
                $table->unsignedBigInteger('actor_user_id');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (! Schema::hasColumn('class_sessions', 'series_task_id')) {
            Schema::table('class_sessions', function (Blueprint $table): void {
                $table->unsignedBigInteger('series_task_id')->nullable();
            });
        }

        if (! Schema::hasColumn('class_sessions', 'differentiated_task_id')) {
            Schema::table('class_sessions', function (Blueprint $table): void {
                $table->unsignedBigInteger('differentiated_task_id')->nullable();
            });
        }

        if (! $this->testingIndexExists('class_sessions', 'uq_class_sessions_series_task')) {
            Schema::table('class_sessions', function (Blueprint $table): void {
                $table->unique(
                    ['series_task_id', 'student_id', 'generated_for_date'],
                    'uq_class_sessions_series_task'
                );
            });
        }

        foreach ([
            'source_series_task_id_snapshot',
            'source_series_task_version_id_snapshot',
            'source_series_task_version_item_id_snapshot',
            'source_series_task_assignment_id_snapshot',
            'source_series_library_id_snapshot',
        ] as $column) {
            if (! Schema::hasColumn('session_tasks', $column)) {
                Schema::table('session_tasks', function (Blueprint $table) use ($column): void {
                    $table->unsignedBigInteger($column)->nullable();
                });
            }
        }

        if (! Schema::hasColumn('session_tasks', 'source_series_library_type_snapshot')) {
            Schema::table('session_tasks', function (Blueprint $table): void {
                $table->string('source_series_library_type_snapshot')->nullable();
            });
        }

        if (! $this->testingIndexExists('session_tasks', 'uq_session_tasks_series')) {
            Schema::table('session_tasks', function (Blueprint $table): void {
                $table->unique(
                    [
                        'class_session_id',
                        'source_series_task_id_snapshot',
                    ],
                    'uq_session_tasks_series'
                );
            });
        }

        $this->createSeriesLibraryTestingTables();
    }

    protected function createSeriesLibraryTestingTables(): void
    {
        if (! Schema::hasTable('sat')) {
            Schema::create('sat', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('title');
                $table->string('slug')->nullable();
                $table->integer('sort')->default(0);
            });
        }

        if (! Schema::hasTable('stories')) {
            Schema::create('stories', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->integer('sort')->default(0);
                $table->boolean('active')->default(true);
            });
        }

        if (! Schema::hasTable('story_chapters')) {
            Schema::create('story_chapters', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('story_id');
                $table->string('title');
                $table->string('slug')->nullable();
                $table->text('text')->nullable();
                $table->string('audio')->nullable();
                $table->integer('sort')->default(0);
            });
        }

        if (! Schema::hasTable('level_up')) {
            Schema::create('level_up', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->string('slug')->nullable();
                $table->string('iframe_link')->nullable();
                $table->integer('sort')->default(0);
            });
        }

        if (! Schema::hasTable('series_seasons')) {
            Schema::create('series_seasons', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->unsignedTinyInteger('type_id')->default(1);
            });
        }

        if (! Schema::hasTable('series_episodes')) {
            Schema::create('series_episodes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('series_season_id');
                $table->string('title');
                $table->string('slug')->nullable();
                $table->text('subtitles')->nullable();
                $table->integer('sort')->default(0);
                $table->boolean('active')->default(true);
            });
        }

        if (! Schema::hasTable('audio_units')) {
            Schema::create('audio_units', function (Blueprint $table): void {
                $table->id();
                $table->string('title');
                $table->integer('order')->default(0);
                $table->boolean('active')->default(true);
            });
        }

        if (! Schema::hasTable('audio_lessons')) {
            Schema::create('audio_lessons', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('unit_id');
                $table->string('title');
                $table->string('file')->nullable();
                $table->string('type')->nullable();
                $table->integer('order')->default(0);
                $table->boolean('active')->default(true);
            });
        }
    }

    protected function createVocabularyGameTestingTables(): void
    {
        if (! Schema::hasTable('vocabulary_sets')) {
            Schema::create('vocabulary_sets', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('node_type');
                $table->string('set_type');
                $table->string('source_kind');
                $table->string('source_key')->nullable();
                $table->unsignedBigInteger('owner_user_id')->nullable();
                $table->string('visibility');
                $table->unsignedInteger('sort_order')->default(0);
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->unsignedBigInteger('updated_by_user_id')->nullable();
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

    private function testingIndexExists(string $table, string $index): bool
    {
        return Schema::hasIndex($table, $index);
    }

    protected function seedTaskTypes(): void
    {
        DB::table('task_types')->insertOrIgnore([
            ['id' => 1, 'title' => 'Worksheet', 'table_name' => 'attachment_files', 'default_points' => 5, 'max_points' => 10],
            ['id' => 2, 'title' => 'Reading', 'table_name' => 'attachment_files', 'default_points' => 8, 'max_points' => 10],
        ]);
    }

    protected function createTeacherSubjectContext(User $teacher, ?int $subjectId = null, string $classTitle = 'Class A'): array
    {
        if ($subjectId === null) {
            $subjectId = DB::table('subjects')->insertGetId([
                'title' => 'English',
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $gradeLevelSubjectId = DB::table('grade_level_subjects')->insertGetId([
            'subject_id' => $subjectId,
            'academic_year_id' => 1,
            'type' => 'standard',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classId = DB::table('classes')->insertGetId([
            'title' => $classTitle,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classSubjectId = DB::table('class_subjects')->insertGetId([
            'class_id' => $classId,
            'grade_level_subject_id' => $gradeLevelSubjectId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $teacherSubjectClassId = DB::table('teacher_subject_classes')->insertGetId([
            'user_teacher_coteacher_id' => $teacher->id,
            'class_subject_id' => $classSubjectId,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'class_name' => $classTitle,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'subject_id' => $subjectId,
            'grade_level_subject_id' => $gradeLevelSubjectId,
            'class_id' => $classId,
            'class_subject_id' => $classSubjectId,
            'teacher_subject_class_id' => $teacherSubjectClassId,
        ];
    }

    protected function enrollStudent(
        array $context,
        string $firstName,
        string $lastName,
        string $parentFirstName,
        string $parentLastName = 'Family'
    ): array {
        $parentId = DB::table('parents')->insertGetId([
            'first_name' => $parentFirstName,
            'last_name' => $parentLastName,
            'email' => strtolower($parentFirstName).'@example.test',
            'active' => 1,
            'lifecycle_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentId = DB::table('students')->insertGetId([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'parent_id' => $parentId,
            'current_class_id' => $context['class_id'],
            'status' => 'active',
            'account_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('students_subjects')->insert([
            'student_id' => $studentId,
            'grade_level_subject_id' => $context['grade_level_subject_id'],
            'academic_year_id' => 1,
            'status' => 'active',
            'class_subject_id' => $context['class_subject_id'],
        ]);

        return [
            'student_id' => $studentId,
            'parent_id' => $parentId,
        ];
    }
}
