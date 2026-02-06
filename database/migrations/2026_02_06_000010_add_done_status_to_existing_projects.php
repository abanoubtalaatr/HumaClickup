<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all projects
        $projects = DB::table('projects')->get();

        foreach ($projects as $project) {
            // Check if this project already has a "Done" status
            $hasDoneStatus = DB::table('custom_statuses')
                ->where('project_id', $project->id)
                ->where('name', 'Done')
                ->exists();

            if (!$hasDoneStatus) {
                // Get the "In Progress" status for this project
                $inProgressStatus = DB::table('custom_statuses')
                    ->where('project_id', $project->id)
                    ->where('name', 'In Progress')
                    ->first();

                if ($inProgressStatus) {
                    // Update order of statuses that come after "In Progress"
                    DB::table('custom_statuses')
                        ->where('project_id', $project->id)
                        ->where('order', '>', $inProgressStatus->order)
                        ->increment('order');

                    // Insert "Done" status right after "In Progress"
                    DB::table('custom_statuses')->insert([
                        'project_id' => $project->id,
                        'name' => 'Done',
                        'color' => '#10b981',
                        'type' => 'done',
                        'order' => $inProgressStatus->order + 1,
                        'is_default' => false,
                        'progress_contribution' => 100,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove "Done" status from all projects
        DB::table('custom_statuses')
            ->where('name', 'Done')
            ->where('type', 'done')
            ->delete();
    }
};
