<?php

namespace Database\Seeders;

use App\Models\Track;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class TrackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all workspaces and seed default tracks for each
        $workspaces = Workspace::all();
        
        $defaultTracks = [
            [
                'name' => 'Frontend Developer',
                'slug' => 'frontend',
                'color' => '#3b82f6', // blue
                'description' => 'Responsible for building user interfaces and client-side functionality',
                'order' => 1,
            ],
            [
                'name' => 'Backend Developer',
                'slug' => 'backend',
                'color' => '#10b981', // green
                'description' => 'Handles server-side logic, databases, and APIs',
                'order' => 2,
            ],
            [
                'name' => 'Full Stack Developer',
                'slug' => 'fullstack',
                'color' => '#8b5cf6', // purple
                'description' => 'Works across the entire application stack',
                'order' => 3,
            ],
            [
                'name' => 'UI/UX Designer',
                'slug' => 'ui-ux',
                'color' => '#ec4899', // pink
                'description' => 'Designs user interfaces and user experiences',
                'order' => 4,
            ],
            [
                'name' => 'DevOps Engineer',
                'slug' => 'devops',
                'color' => '#f59e0b', // amber
                'description' => 'Manages infrastructure, CI/CD, and deployments',
                'order' => 5,
            ],
            [
                'name' => 'QA Engineer',
                'slug' => 'qa',
                'color' => '#ef4444', // red
                'description' => 'Tests and ensures quality of the software',
                'order' => 6,
            ],
            [
                'name' => 'Mobile Developer',
                'slug' => 'mobile',
                'color' => '#6366f1', // indigo
                'description' => 'Builds mobile applications for iOS and Android',
                'order' => 7,
            ],
            [
                'name' => 'Product Manager',
                'slug' => 'product',
                'color' => '#14b8a6', // teal
                'description' => 'Manages product strategy and roadmap',
                'order' => 8,
            ],
        ];

        foreach ($workspaces as $workspace) {
            foreach ($defaultTracks as $track) {
                // Only create if it doesn't exist
                Track::firstOrCreate(
                    [
                        'workspace_id' => $workspace->id,
                        'slug' => $track['slug'],
                    ],
                    array_merge($track, ['workspace_id' => $workspace->id])
                );
            }
        }
    }
}

