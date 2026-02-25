<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Models\Track;
use Carbon\Carbon;

/**
 * ProjectPlanningService
 * 
 * Service for enforcing project planning rules in student training system.
 * 
 * Business Rules:
 * - Project must have multiple guests from different tracks
 * - Working days exclude Friday & Saturday
 * - Required main tasks = guests_count × working_days
 * - Each guest must have 1 main task per day
 * - Each main task must be >= 6 hours
 * - Weekly target = 30 hours per guest (5 days × 6 hours)
 */
class ProjectPlanningService
{
    protected TesterAssignmentService $testerService;

    public function __construct(TesterAssignmentService $testerService)
    {
        $this->testerService = $testerService;
    }

    /**
     * Calculate working days excluding weekends (Friday & Saturday).
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param bool $excludeWeekends
     * @return int
     */
    public function calculateWorkingDays(Carbon $startDate, Carbon $endDate, bool $excludeWeekends = true): int
    {
        return Project::calculateWorkingDays($startDate, $endDate, $excludeWeekends);
    }

    /**
     * Calculate required main tasks for a project.
     * 
     * Rule: required_tasks = guests_count × working_days
     * 
     * @param Project $project
     * @return int
     */
    public function calculateRequiredMainTasks(Project $project): int
    {
        $guestsCount = $project->getGuestsCount();

        if ($guestsCount === 0) {
            throw new \Exception('Project must have at least one guest member.');
        }

        if (!$project->working_days) {
            throw new \Exception('Project must have working_days calculated.');
        }

        return $guestsCount * $project->working_days;
    }

    /**
     * Initialize project planning with guests and dates.
     * 
     * @param Project $project
     * @param array $guestsData Array of ['user_id' => int, 'track_id' => int|null]
     * @param array $planningData ['start_date', 'total_days', 'exclude_weekends', etc.]
     * @return array
     */
    public function initializeProject(Project $project, array $guestsData, array $planningData): array
    {
        // Validate guests
        if (empty($guestsData)) {
            throw new \Exception('Project must have at least one guest.');
        }

        // Add guests to project
        foreach ($guestsData as $guestData) {
            $user = User::findOrFail($guestData['user_id']);
            $track = isset($guestData['track_id']) ? Track::find($guestData['track_id']) : null;

            $project->addGuestMember($user, $track);
        }

        // Calculate dates
        $startDate = Carbon::parse($planningData['start_date']);
        $totalDays = $planningData['total_days'];
        $excludeWeekends = $planningData['exclude_weekends'] ?? true;

        // Calculate end date
        $endDate = $this->calculateEndDate($startDate, $totalDays, $excludeWeekends);

        // Calculate working days
        $workingDays = $this->calculateWorkingDays($startDate, $endDate, $excludeWeekends);

        // Calculate required main tasks
        $requiredMainTasks = count($guestsData) * $workingDays;

        // Update project
        $project->update([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'exclude_weekends' => $excludeWeekends,
            'required_main_tasks_count' => $requiredMainTasks,
            'current_main_tasks_count' => 0,
            'min_task_hours' => $planningData['min_task_hours'] ?? 6,
            'bug_time_allocation_percentage' => $planningData['bug_time_allocation_percentage'] ?? 20,
            'weekly_hours_target' => $planningData['weekly_hours_target'] ?? 30,
            'tasks_requirement_met' => false,
        ]);

        // Request tester assignment (2 testers per project)
        $this->testerService->requestTesterAssignment($project, 2);

        return [
            'success' => true,
            'project' => $project,
            'guests_count' => count($guestsData),
            'working_days' => $workingDays,
            'required_main_tasks' => $requiredMainTasks,
        ];
    }

    /**
     * Calculate end date based on total days and weekend exclusion.
     * 
     * @param Carbon $startDate
     * @param int $totalDays
     * @param bool $excludeWeekends
     * @return Carbon
     */
    public function calculateEndDate(Carbon $startDate, int $totalDays, bool $excludeWeekends = true): Carbon
    {
        if (!$excludeWeekends) {
            return $startDate->copy()->addDays($totalDays - 1);
        }

        $currentDate = $startDate->copy();
        $daysAdded = 0;

        while ($daysAdded < $totalDays) {
            // Skip Friday (5) and Saturday (6)
            if (!in_array($currentDate->dayOfWeek, [5, 6])) {
                $daysAdded++;
            }

            if ($daysAdded < $totalDays) {
                $currentDate->addDay();
            }
        }

        return $currentDate;
    }

    /**
     * Validate if project can start (all requirements met).
     * 
     * @param Project $project
     * @return array
     */
    public function canStartProject(Project $project): array
    {
        $errors = [];

        // Check guests exist
        $guestsCount = $project->getGuestsCount();
        if ($guestsCount === 0) {
            $errors[] = 'Project must have at least one guest.';
        }

        // Check planning fields are set
        if (!$project->start_date) {
            $errors[] = 'Project must have a start date.';
        }

        if (!$project->working_days) {
            $errors[] = 'Project must have working days calculated.';
        }

        // Check required main tasks are created
        $currentMainTasks = $project->tasks()->where('is_main_task', 'yes')->count();

        if ($currentMainTasks < $project->required_main_tasks_count) {
            $errors[] = "Project requires {$project->required_main_tasks_count} main tasks, but only {$currentMainTasks} created.";
        }

        // Check all main tasks meet minimum hours
        $invalidTasks = $project->tasks()
            ->where('is_main_task', 'yes')
            ->where('estimated_time', '<', $project->min_task_hours)
            ->count();

        if ($invalidTasks > 0) {
            $errors[] = "All main tasks must have at least {$project->min_task_hours} hours. Found {$invalidTasks} task(s) below minimum.";
        }

        // Check all main tasks are assigned
        $unassignedTasks = $project->tasks()
            ->where('is_main_task', 'yes')
            ->doesntHave('assignees')
            ->count();

        if ($unassignedTasks > 0) {
            $errors[] = "All main tasks must be assigned. Found {$unassignedTasks} unassigned task(s).";
        }

        // Check testers assigned
        $testersCount = $project->projectTesters()->count();
        if ($testersCount < 2) {
            $errors[] = "Project must have at least 2 testers assigned. Currently: {$testersCount}.";
        }

        return [
            'can_start' => empty($errors),
            'errors' => $errors,
            'validation' => [
                'guests_count' => $guestsCount,
                'required_main_tasks' => $project->required_main_tasks_count,
                'current_main_tasks' => $currentMainTasks,
                'testers_count' => $testersCount,
            ],
        ];
    }

    /**
     * Get project planning summary.
     * 
     * @param Project $project
     * @return array
     */
    public function getProjectPlanningSummary(Project $project): array
    {
        $guests = $project->getGuestMembers();

        // Group guests by track
        $guestsByTrack = $guests->groupBy(function ($guest) use ($project) {
            $projectMember = $project->projectMembers()
                ->where('user_id', $guest->id)
                ->first();
            return $projectMember?->track?->name ?? 'No Track';
        });

        return [
            'project_name' => $project->name,
            'start_date' => $project->start_date?->format('Y-m-d'),
            'end_date' => $project->end_date?->format('Y-m-d'),
            'total_days' => $project->total_days,
            'working_days' => $project->working_days,
            'guests_count' => $guests->count(),
            'guests_by_track' => $guestsByTrack->map->count(),
            'required_main_tasks' => $project->required_main_tasks_count,
            'current_main_tasks' => $project->current_main_tasks_count,
            'tasks_requirement_met' => $project->tasks_requirement_met,
            'min_task_hours' => $project->min_task_hours,
            'weekly_hours_target' => $project->weekly_hours_target,
            'testers_count' => $project->projectTesters()->count(),
        ];
    }

    /**
     * Update main tasks count and check if requirement is met.
     * 
     * @param Project $project
     * @return void
     */
    public function updateMainTasksStatus(Project $project): void
    {
        $currentCount = $project->tasks()->where('is_main_task', 'yes')->count();

        $project->update([
            'current_main_tasks_count' => $currentCount,
            'tasks_requirement_met' => $currentCount >= $project->required_main_tasks_count,
        ]);
    }
}
