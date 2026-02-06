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
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'bug_time_used')) {
                $table->decimal('bug_time_used', 5, 2)->default(0)->after('estimated_time')->comment('Total time used by bugs for this main task');
            }
            if (!Schema::hasColumn('tasks', 'bug_time_limit')) {
                $table->decimal('bug_time_limit', 5, 2)->nullable()->after('bug_time_used')->comment('Maximum time allowed for bugs (20% of main task)');
            }
            if (!Schema::hasColumn('tasks', 'bugs_count')) {
                $table->integer('bugs_count')->default(0)->after('bug_time_limit')->comment('Number of bugs created for this main task');
            }
            if (!Schema::hasColumn('tasks', 'is_main_task')) {
                $table->enum('is_main_task', ['yes', 'no'])->default('no')->after('bugs_count')->comment('Is this a main task (minimum 6 hours)');
            }
            if (!Schema::hasColumn('tasks', 'assigned_date')) {
                $table->date('assigned_date')->nullable()->after('is_main_task')->comment('Date when task was assigned');
            }
            if (!Schema::hasColumn('tasks', 'completion_date')) {
                $table->date('completion_date')->nullable()->after('assigned_date')->comment('Date when task was completed');
            }
        });
        
        // Add indexes separately
        Schema::table('tasks', function (Blueprint $table) {
            if (!$this->indexExists('tasks', 'tasks_is_main_task_index')) {
                $table->index('is_main_task');
            }
            if (!$this->indexExists('tasks', 'tasks_assigned_date_index')) {
                $table->index('assigned_date');
            }
            if (!$this->indexExists('tasks', 'tasks_completion_date_index')) {
                $table->index('completion_date');
            }
        });
    }

    private function indexExists($table, $name): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$name]);
        return !empty($indexes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['is_main_task']);
            $table->dropIndex(['assigned_date']);
            $table->dropIndex(['completion_date']);
            $table->dropColumn([
                'bug_time_used',
                'bug_time_limit',
                'bugs_count',
                'is_main_task',
                'assigned_date',
                'completion_date'
            ]);
        });
    }
};
