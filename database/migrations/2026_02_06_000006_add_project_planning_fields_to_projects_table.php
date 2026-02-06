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
            $table->foreignId('group_id')->nullable()->after('workspace_id')->constrained('groups')->nullOnDelete();
            $table->integer('total_days')->nullable()->after('description')->comment('Total project duration in days');
            $table->integer('working_days')->nullable()->after('total_days')->comment('Working days excluding weekends');
            $table->boolean('exclude_weekends')->default(true)->after('working_days')->comment('Exclude Friday and Saturday from working days');
            $table->integer('required_main_tasks_count')->nullable()->after('exclude_weekends')->comment('Required number of main tasks (group_members × working_days)');
            $table->integer('current_main_tasks_count')->default(0)->after('required_main_tasks_count')->comment('Current number of main tasks created');
            $table->decimal('min_task_hours', 5, 2)->default(6)->after('current_main_tasks_count')->comment('Minimum hours per main task');
            $table->decimal('bug_time_allocation_percentage', 5, 2)->default(20)->after('min_task_hours')->comment('Max percentage of main task time for bugs');
            $table->decimal('weekly_hours_target', 5, 2)->default(30)->after('bug_time_allocation_percentage')->comment('Target hours per week per member');
            $table->boolean('tasks_requirement_met')->default(false)->after('weekly_hours_target')->comment('Whether required tasks count is met');
            // Note: start_date already exists from 2026_01_09_102424_add_due_date_to_projects_table.php
            $table->date('end_date')->nullable()->after('due_date');
            
            $table->index('group_id');
            $table->index('tasks_requirement_met');
        });
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
