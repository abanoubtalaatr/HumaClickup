<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Group;
use Carbon\Carbon;

class ProjectPlanningService
{
    /**
     * Calculate working days excluding weekends (Friday & Saturday).
     */
    public function calculateWorkingDays(Carbon $startDate, Carbon $endDate, bool $excludeWeekends = true): int
    {
        return Project::calculateWorkingDays($startDate, $endDate, $excludeWeekends);
    }

    /**
     * Calculate required main tasks for a project.
     */
    public function calculateRequiredMainTasks(Project $project): int
    {
        if (!$project->group) {
            throw new \Exception('Project must have an assigned group.');
        }

        $groupMembersCount = $project->group->guests()->count();
        
        if ($groupMembersCount === 0) {
            throw new \Exception('Group must have at least one member.');
        }

        return $groupMembersCount * $project->working_days;
    }

    /**
     * Initialize project planning fields.
     */
    public function initializeProjectPlanning(Project $project, array $data): void
    {
        // Calculate dates
        $startDate = Carbon::parse($data['start_date']);
        $totalDays = $data['total_days'];
        
        // Calculate end date
        $endDate = $this->calculateEndDate($startDate, $totalDays, $data['exclude_weekends'] ?? true);
        
        // Calculate working days
        $workingDays = $this->calculateWorkingDays($startDate, $endDate, $data['exclude_weekends'] ?? true);
        
        // Calculate required main tasks
        $project->group_id = $data['group_id'];
        $project->load('group');
        $requiredMainTasks = $this->calculateRequiredMainTasks($project);
        
        // Update project
        $project->update([
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'exclude_weekends' => $data['exclude_weekends'] ?? true,
            'required_main_tasks_count' => $requiredMainTasks,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'min_task_hours' => $data['min_task_hours'] ?? 6,
            'bug_time_allocation_percentage' => $data['bug_time_allocation_percentage'] ?? 20,
            'weekly_hours_target' => $data['weekly_hours_target'] ?? 30,
        ]);
    }

    /**
     * Calculate end date based on total days and weekend exclusion.
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
     * Validate if project can be activated (all requirements met).
     */
    public function canActivateProject(Project $project): array
    {
        $errors = [];

        // Check group assignment
        if (!$project->group_id) {
            $errors[] = 'Project must have an assigned group.';
        }

        // Check group has members
        if ($project->group && $project->group->guests()->count() === 0) {
            $errors[] = 'Group must have at least one member.';
        }

        // Check required main tasks are created
        if ($project->current_main_tasks_count < $project->required_main_tasks_count) {
            $errors[] = "Project requires {$project->required_main_tasks_count} main tasks, but only {$project->current_main_tasks_count} created.";
        }

        // Check all main tasks meet minimum hours
        $invalidTasks = $project->tasks()
            ->where('is_main_task', 'yes')
            ->where('estimated_time', '<', $project->min_task_hours)
            ->count();

        if ($invalidTasks > 0) {
            $errors[] = "All main tasks must have at least {$project->min_task_hours} hours estimated time.";
        }

        return [
            'can_activate' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get project planning summary.
     */
    public function getProjectPlanningSummary(Project $project): array
    {
        return [
            'group' => $project->group?->name,
            'group_members_count' => $project->group?->guests()->count() ?? 0,
            'total_days' => $project->total_days,
            'working_days' => $project->working_days,
            'start_date' => $project->start_date?->format('Y-m-d'),
            'end_date' => $project->end_date?->format('Y-m-d'),
            'required_main_tasks' => $project->required_main_tasks_count,
            'current_main_tasks' => $project->current_main_tasks_count,
            'tasks_requirement_met' => $project->tasks_requirement_met,
            'min_task_hours' => $project->min_task_hours,
            'weekly_hours_target' => $project->weekly_hours_target,
        ];
    }

    /**
     * Validate group eligibility for project assignment.
     */
    public function validateGroupForProject(Group $group): array
    {
        $errors = [];

        if (!$group->is_active) {
            $errors[] = 'Group is not active.';
        }

        if (!$group->meetsMinimum()) {
            $errors[] = "Group must have at least {$group->min_members} members.";
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
