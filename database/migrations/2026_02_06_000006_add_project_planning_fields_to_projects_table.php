<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Check and add only missing columns
            if (!Schema::hasColumn('projects', 'group_id')) {
                $table->foreignId('group_id')->nullable()->after('workspace_id')->constrained('groups')->nullOnDelete();
            }
            if (!Schema::hasColumn('projects', 'total_days')) {
                $table->integer('total_days')->nullable()->after('description')->comment('Total project duration in days');
            }
            if (!Schema::hasColumn('projects', 'working_days')) {
                $table->integer('working_days')->nullable()->after('total_days')->comment('Working days excluding weekends');
            }
            if (!Schema::hasColumn('projects', 'exclude_weekends')) {
                $table->boolean('exclude_weekends')->default(true)->after('working_days')->comment('Exclude Friday and Saturday from working days');
            }
            if (!Schema::hasColumn('projects', 'required_main_tasks_count')) {
                $table->integer('required_main_tasks_count')->nullable()->after('exclude_weekends')->comment('Required number of main tasks (group_members × working_days)');
            }
            if (!Schema::hasColumn('projects', 'current_main_tasks_count')) {
                $table->integer('current_main_tasks_count')->default(0)->after('required_main_tasks_count')->comment('Current number of main tasks created');
            }
            if (!Schema::hasColumn('projects', 'min_task_hours')) {
                $table->decimal('min_task_hours', 5, 2)->default(6)->after('current_main_tasks_count')->comment('Minimum hours per main task');
            }
            if (!Schema::hasColumn('projects', 'bug_time_allocation_percentage')) {
                $table->decimal('bug_time_allocation_percentage', 5, 2)->default(20)->after('min_task_hours')->comment('Max percentage of main task time for bugs');
            }
            if (!Schema::hasColumn('projects', 'weekly_hours_target')) {
                $table->decimal('weekly_hours_target', 5, 2)->default(30)->after('bug_time_allocation_percentage')->comment('Target hours per week per member');
            }
            if (!Schema::hasColumn('projects', 'tasks_requirement_met')) {
                $table->boolean('tasks_requirement_met')->default(false)->after('weekly_hours_target')->comment('Whether required tasks count is met');
            }
            // Note: start_date already exists from 2026_01_09_102424_add_due_date_to_projects_table.php
            if (!Schema::hasColumn('projects', 'end_date')) {
                $table->date('end_date')->nullable()->after('due_date');
            }
        });
        
        // Add indexes separately to avoid duplicates
        Schema::table('projects', function (Blueprint $table) {
            if (!$this->indexExists('projects', 'projects_group_id_index')) {
                $table->index('group_id');
            }
            if (!$this->indexExists('projects', 'projects_tasks_requirement_met_index')) {
                $table->index('tasks_requirement_met');
            }
        });
    }

    private function indexExists($table, $name): bool
    {
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        $doctrineTable = $dbSchemaManager->introspectTable($table);
        return $doctrineTable->hasIndex($name);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropIndex(['group_id']);
            $table->dropIndex(['tasks_requirement_met']);
            $table->dropColumn([
                'group_id', 
                'total_days', 
                'working_days', 
                'exclude_weekends',
                'required_main_tasks_count',
                'current_main_tasks_count',
                'min_task_hours',
                'bug_time_allocation_percentage',
                'weekly_hours_target',
                'tasks_requirement_met',
                'end_date'
            ]);
        });
    }
};
