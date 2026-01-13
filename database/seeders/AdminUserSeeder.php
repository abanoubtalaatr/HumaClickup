<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use App\Models\Space;
use App\Models\Project;
use App\Models\CustomStatus;
use App\Models\Task;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@humaclickup.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('Master@#12HumaClickup'),
                'email_verified_at' => now(),
                'status' => 'active',
                'timezone' => 'UTC',
                'locale' => 'en',
            ]
        );

        $this->command->info("✅ Admin user created: admin@humaclickup.com / Master@#12HumaClickup");

        // Create Workspace
        $workspace = Workspace::firstOrCreate(
            ['slug' => 'main-workspace'],
            [
                'name' => 'Main Workspace',
                'owner_id' => $admin->id,
                'description' => 'Main workspace for testing HumaClickup',
                'billing_status' => 'active',
                'storage_limit' => 10737418240, // 10GB
                'storage_used' => 0,
            ]
        );

        // Add admin to workspace as owner
        if (!$workspace->users()->where('user_id', $admin->id)->exists()) {
            $workspace->addMember($admin, 'owner');
        }

        $this->command->info("✅ Workspace created: Demo Workspace");

        // Create Space
        $space = Space::firstOrCreate(
            [
                'workspace_id' => $workspace->id,
                'name' => 'Development',
            ],
            [
                'color' => '#3b82f6',
                'icon' => '💻',
                'description' => 'Development projects',
                'is_archived' => false,
                'order' => 0,
            ]
        );

        $this->command->info("✅ Space created: Development");

        // // Create Project
        // $project = Project::firstOrCreate(
        //     [
        //         'workspace_id' => $workspace->id,
        //         'name' => 'Website Redesign',
        //     ],
        //     [
        //         'space_id' => $space->id,
        //         'description' => 'Complete redesign of the company website with modern UI/UX',
        //         'color' => '#6366f1',
        //         'icon' => '🎨',
        //         'progress' => 0,
        //         'is_archived' => false,
        //         'order' => 0,
        //     ]
        // );

        // $this->command->info("✅ Project created: Website Redesign");

        // // Create Custom Statuses
        // $statuses = [
        //     ['name' => 'To Do', 'color' => '#94a3b8', 'type' => 'todo', 'order' => 0, 'progress_contribution' => 0, 'is_default' => true],
        //     ['name' => 'In Progress', 'color' => '#3b82f6', 'type' => 'in_progress', 'order' => 1, 'progress_contribution' => 25],
        //     ['name' => 'In Review', 'color' => '#f59e0b', 'type' => 'in_progress', 'order' => 2, 'progress_contribution' => 50],
        //     ['name' => 'Testing', 'color' => '#8b5cf6', 'type' => 'in_progress', 'order' => 3, 'progress_contribution' => 75],
        //     ['name' => 'Blocked', 'color' => '#ef4444', 'type' => 'blocked', 'order' => 4, 'progress_contribution' => 0],
        //     ['name' => 'Done', 'color' => '#10b981', 'type' => 'done', 'order' => 5, 'progress_contribution' => 100],
        // ];

        // $createdStatuses = [];
        // foreach ($statuses as $statusData) {
        //     $status = CustomStatus::firstOrCreate(
        //         [
        //             'project_id' => $project->id,
        //             'name' => $statusData['name'],
        //         ],
        //         array_merge($statusData, ['project_id' => $project->id])
        //     );
        //     $createdStatuses[$statusData['name']] = $status;
        // }

        // $this->command->info("✅ Statuses created: " . implode(', ', array_keys($createdStatuses)));

        // // Create Tags
        // $tags = [
        //     ['name' => 'Frontend', 'color' => '#3b82f6'],
        //     ['name' => 'Backend', 'color' => '#8b5cf6'],
        //     ['name' => 'Design', 'color' => '#ec4899'],
        //     ['name' => 'Bug', 'color' => '#ef4444'],
        //     ['name' => 'Feature', 'color' => '#10b981'],
        // ];

        // $createdTags = [];
        // foreach ($tags as $tagData) {
        //     $tag = Tag::firstOrCreate(
        //         [
        //             'workspace_id' => $workspace->id,
        //             'name' => $tagData['name'],
        //         ],
        //         array_merge($tagData, ['workspace_id' => $workspace->id])
        //     );
        //     $createdTags[$tagData['name']] = $tag;
        // }

        // $this->command->info("✅ Tags created: " . implode(', ', array_keys($createdTags)));

        // // Create Sample Tasks
        // $tasks = [
        //     [
        //         'title' => 'Design new homepage layout',
        //         'description' => 'Create wireframes and mockups for the new homepage design. Focus on modern, clean UI.',
        //         'status' => 'In Progress',
        //         'priority' => 'high',
        //         'due_date' => now()->addDays(5),
        //         'estimated_time' => 480, // 8 hours
        //         'tags' => ['Design', 'Frontend'],
        //     ],
        //     [
        //         'title' => 'Implement responsive navigation',
        //         'description' => 'Build responsive navigation menu that works on all device sizes.',
        //         'status' => 'To Do',
        //         'priority' => 'normal',
        //         'due_date' => now()->addDays(7),
        //         'estimated_time' => 240, // 4 hours
        //         'tags' => ['Frontend'],
        //     ],
        //     [
        //         'title' => 'Setup API endpoints',
        //         'description' => 'Create REST API endpoints for user authentication and data management.',
        //         'status' => 'To Do',
        //         'priority' => 'high',
        //         'due_date' => now()->addDays(10),
        //         'estimated_time' => 600, // 10 hours
        //         'tags' => ['Backend'],
        //     ],
        //     [
        //         'title' => 'Fix mobile menu bug',
        //         'description' => 'Menu doesn\'t close properly on mobile devices. Need to fix the click handler.',
        //         'status' => 'In Review',
        //         'priority' => 'urgent',
        //         'due_date' => now()->addDays(1),
        //         'estimated_time' => 60, // 1 hour
        //         'tags' => ['Bug', 'Frontend'],
        //     ],
        //     [
        //         'title' => 'Add dark mode toggle',
        //         'description' => 'Implement dark mode feature with user preference storage.',
        //         'status' => 'Done',
        //         'priority' => 'normal',
        //         'due_date' => now()->subDays(2),
        //         'estimated_time' => 180, // 3 hours
        //         'tags' => ['Feature', 'Frontend'],
        //     ],
        // ];

        // foreach ($tasks as $index => $taskData) {
        //     $status = $createdStatuses[$taskData['status']];
            
        //     $task = Task::firstOrCreate(
        //         [
        //             'workspace_id' => $workspace->id,
        //             'project_id' => $project->id,
        //             'title' => $taskData['title'],
        //         ],
        //         [
        //             'status_id' => $status->id,
        //             'creator_id' => $admin->id,
        //             'description' => $taskData['description'],
        //             'priority' => $taskData['priority'],
        //             'due_date' => $taskData['due_date'],
        //             'estimated_time' => $taskData['estimated_time'],
        //             'position' => ($index + 1) * 100,
        //             'is_archived' => false,
        //         ]
        //     );

        //     // Attach tags
        //     foreach ($taskData['tags'] as $tagName) {
        //         if (isset($createdTags[$tagName])) {
        //             $task->tags()->syncWithoutDetaching([$createdTags[$tagName]->id]);
        //         }
        //     }

        //     // Assign admin to some tasks
        //     if ($index % 2 === 0) {
        //         $task->assignees()->syncWithoutDetaching([$admin->id]);
        //     }
        // }

        // $this->command->info("✅ Sample tasks created: " . count($tasks) . " tasks");

        // // Update project progress
        // $project->calculateProgress();

        // $this->command->info("\n🎉 Setup complete!");
        // $this->command->info("\n📧 Login Credentials:");
        // $this->command->info("   Email: admin@humaclickup.com");
        // $this->command->info("   Password: password");
        // $this->command->info("\n🚀 Next steps:");
        // $this->command->info("   1. Run: php artisan serve");
        // $this->command->info("   2. Visit: http://localhost:8000/login");
        // $this->command->info("   3. Login with the credentials above");
    }
}
