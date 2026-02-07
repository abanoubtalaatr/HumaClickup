<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds a "Done" status after "In Progress" for all existing projects.
     */
    public function up(): void
    {
        $projects = DB::table('projects')->get();

        foreach ($projects as $project) {
            // Check if "Done" status already exists for this project
            $exists = DB::table('custom_statuses')
                ->where('project_id', $project->id)
                ->where('name', 'Done')
                ->exists();

            if ($exists) {
                continue;
            }

            // Find "In Progress" status to insert "Done" right after it
            $inProgress = DB::table('custom_statuses')
                ->where('project_id', $project->id)
                ->where('name', 'In Progress')
                ->first();

            $insertOrder = $inProgress ? $inProgress->order + 1 : 2;

            // Shift all statuses with order >= insertOrder up by 1
            DB::table('custom_statuses')
                ->where('project_id', $project->id)
                ->where('order', '>=', $insertOrder)
                ->increment('order');

            // Insert "Done" status
            DB::table('custom_statuses')->insert([
                'project_id' => $project->id,
                'name' => 'Done',
                'color' => '#10b981',
                'type' => 'done',
                'order' => $insertOrder,
                'is_default' => false,
                'progress_contribution' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove "Done" statuses that were added by this migration
        DB::table('custom_statuses')
            ->where('name', 'Done')
            ->where('type', 'done')
            ->where('color', '#10b981')
            ->delete();
    }
};
