<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\CustomStatus;
use Illuminate\Console\Command;

class AddMissingStatusesToProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:add-missing-statuses {--project_id= : Specific project ID to update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add missing statuses (In Review, In Testing, Retest, Blocked, Done from Test, Closed) to existing projects';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projectId = $this->option('project_id');
        
        if ($projectId) {
            $projects = Project::where('id', $projectId)->get();
            if ($projects->isEmpty()) {
                $this->error("Project with ID {$projectId} not found.");
                return 1;
            }
        } else {
            $projects = Project::all();
        }

        $this->info("Processing {$projects->count()} project(s)...");

        foreach ($projects as $project) {
            $this->info("Processing project: {$project->name} (ID: {$project->id})");
            
            // Get existing statuses for this project
            $existingStatusNames = $project->customStatuses()->pluck('name')->toArray();
            $existingStatusNames = array_map('strtolower', $existingStatusNames);
            
            // Get the highest order number
            $maxOrder = $project->customStatuses()->max('order') ?? 2;
            
            // Define all desired statuses
            $desiredStatuses = [
                ['name' => 'To Do', 'color' => '#94a3b8', 'type' => 'todo', 'is_default' => true, 'progress_contribution' => 0],
                ['name' => 'In Progress', 'color' => '#3b82f6', 'type' => 'in_progress', 'progress_contribution' => 25],
                ['name' => 'In Review', 'color' => '#f59e0b', 'type' => 'in_progress', 'progress_contribution' => 50],
                ['name' => 'Retest', 'color' => '#ec4899', 'type' => 'in_progress', 'progress_contribution' => 70],
                ['name' => 'Blocked', 'color' => '#ef4444', 'type' => 'in_progress', 'progress_contribution' => 0],
                ['name' => 'Closed', 'color' => '#10b981', 'type' => 'done', 'progress_contribution' => 100],
            ];
            
            $addedCount = 0;
            $order = $maxOrder;
            
            foreach ($desiredStatuses as $statusData) {
                $statusNameLower = strtolower($statusData['name']);
                
                // Check if status already exists (case-insensitive)
                if (!in_array($statusNameLower, $existingStatusNames)) {
                    $order++;
                    $statusData['order'] = $order;
                    
                    // Don't set is_default for new statuses
                    if ($statusData['name'] !== 'To Do') {
                        unset($statusData['is_default']);
                    }
                    
                    $project->customStatuses()->create($statusData);
                    $this->line("  ✓ Added status: {$statusData['name']}");
                    $addedCount++;
                } else {
                    $this->line("  - Status already exists: {$statusData['name']}");
                }
            }
            
            if ($addedCount > 0) {
                $this->info("  Added {$addedCount} new status(es) to project: {$project->name}");
            } else {
                $this->comment("  No new statuses needed for project: {$project->name}");
            }
            
            $this->newLine();
        }

        $this->info("✓ Completed! All projects have been updated with the necessary statuses.");
        return 0;
    }
}
