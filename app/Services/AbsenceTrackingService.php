<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Absence Tracking Service
 *
 * Calculates absence days for guests based on overdue tasks (task not completed by due_date).
 * Deterministic and reproducible; supports future extensions (excused absences, grace periods, holidays).
 *
 * Rules (based on task due_date):
 * - If task completed on or before due_date → no absence.
 * - If task completed after due_date → absence from due_date through completion_date.
 * - If task not completed by end of due_date → absence from due_date through current (or as_of) date.
 * - The due_date day itself counts as absence when the task is not done by end of that day.
 * - No duplicate absence days per guest (one per day across all tasks).
 * - No absence counted for future dates.
 */
class AbsenceTrackingService
{
    /**
     * Get total number of unique absence days for a guest in the workspace.
     *
     * @param int $workspaceId
     * @param int $guestId
     * @param Carbon|null $asOfDate Consider only absences up to this date (default: today)
     * @return int
     */
    public function getTotalAbsenceDaysForGuest(int $workspaceId, int $guestId, ?Carbon $asOfDate = null): int
    {
        $dates = $this->getAbsenceDatesForGuest($workspaceId, $guestId, $asOfDate);
        return $dates->count();
    }

    /**
     * Get unique list of dates the guest was absent (task-based).
     *
     * @param int $workspaceId
     * @param int $guestId
     * @param Carbon|null $asOfDate
     * @return Collection<int, Carbon> Unique dates (sorted)
     */
    public function getAbsenceDatesForGuest(int $workspaceId, int $guestId, ?Carbon $asOfDate = null): Collection
    {
        $asOfDate = $asOfDate ?? Carbon::today();
        $asOfDate = Carbon::parse($asOfDate->format('Y-m-d'))->startOfDay();

        $tasks = $this->getTasksAssignedToGuestWithDueDate($workspaceId, $guestId);
        
        $allDates = collect();

        foreach ($tasks as $task) {
            $task->load('status');
            $dates = $this->getAbsenceDatesForTask($task, $asOfDate);
            $allDates = $allDates->merge($dates);
        }

        return $allDates->unique(fn (Carbon $d) => $d->format('Y-m-d'))->sort()->values();
    }

    /**
     * Get absence breakdown per task for a guest (for auditing/reporting).
     *
     * @return array{total_days: int, by_task: array<int, array{task: Task, absence_days: int, dates: array}>}
     */
    public function getAbsencePerTaskForGuest(int $workspaceId, int $guestId, ?Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? Carbon::today();
        $asOfDate = Carbon::parse($asOfDate->format('Y-m-d'))->startOfDay();

        $tasks = $this->getTasksAssignedToGuestWithDueDate($workspaceId, $guestId);
        
        $byTask = [];
        $allDates = collect();

        foreach ($tasks as $task) {
            
            
            $task->load(['status', 'project']);
            $dates = $this->getAbsenceDatesForTask($task, $asOfDate);
            
            $dateStrings = $dates->map(fn (Carbon $d) => $d->format('Y-m-d'))->values()->all();
            
            $byTask[$task->id] = [
                'task' => $task,
                'absence_days' => count($dateStrings),
                'dates' => $dateStrings,
            ];
            $allDates = $allDates->merge($dates);
        }

        $uniqueDates = $allDates->unique(fn (Carbon $d) => $d->format('Y-m-d'));
        return [
            'total_days' => $uniqueDates->count(),
            'by_task' => $byTask,
        ];
    }

    /**
     * Get absence summary for guests in the workspace.
     * Shows all relevant guests (member's guests or all workspace guests); absence is computed per guest.
     * Guests with no tasks or all tasks done on time show 0 absence.
     *
     * @param array<int>|null $guestIds If provided, only these guest user IDs are included (e.g. for members: their created guests).
     * @return Collection<int, array{user: User, total_absence_days: int, by_task: array}>
     */
    public function getAbsenceSummaryForWorkspace(int $workspaceId, ?Carbon $asOfDate = null, ?array $guestIds = null): Collection
    {
        $asOfDate = $asOfDate ?? today();
        
        // Build guest list: either the given guest IDs (member's guests) or all workspace guests
        if ($guestIds !== null) {
            $guests = User::whereIn('id', $guestIds)
                ->whereHas('workspaces', fn ($q) => $q->where('workspace_id', $workspaceId)->where('role', 'guest'))
                ->get();
        } else {
            $guests = User::whereHas('workspaces', fn ($q) => $q->where('workspace_id', $workspaceId)->where('role', 'guest'))
                ->get();
        }


        $result = collect();
        
        foreach ($guests as $guest) {
            $perTask = $this->getAbsencePerTaskForGuest($workspaceId, $guest->id, $asOfDate);
            
            $result->push([
                'user' => $guest,
                'total_absence_days' => $perTask['total_days'],
                'by_task' => $perTask['by_task'],
            ]);
        }

        return $result->sortByDesc('total_absence_days')->values();
    }

    /**
     * Get tasks assigned to this guest that have a due_date (used for absence calculation).
     */
    private function getTasksAssignedToGuestWithDueDate(int $workspaceId, int $guestId): Collection
    {
        $projectIds = Project::where('workspace_id', $workspaceId)->pluck('id');
        
        return Task::withoutGlobalScopes()
            ->where('workspace_id', $workspaceId)
            
            ->whereNotNull('start_date')
            ->whereHas('assignees', fn ($q) => $q->where('user_id', $guestId))
            ->with('status')
            ->get();
    }

    /**
     * Compute absence dates for a single task for a given guest (based on task due_date).
     * The due_date day counts as absence when the task is not done by end of that day.
     * - Completed on or before due_date → [].
     * - Completed after due_date → due_date through completion_date (no future).
     * - Not completed → due_date through asOfDate (no future).
     *
     * @return Collection<int, Carbon>
     */
    private function getAbsenceDatesForTask(Task $task, Carbon $asOfDate): Collection
    {
        $start =  $task->start_date;
        if (!$start) {
            return collect();
        }
        $start = Carbon::parse($start instanceof \DateTimeInterface ? $start->format('Y-m-d') : $start)->startOfDay();
        $endOfRange = $asOfDate->copy();

        // No absence for future due_date
        if ($start->isFuture()) {
            return collect();
        }

        // First absence day is due_date itself (task not done by end of that day)
        $firstAbsenceDate = $start->copy();
        if ($firstAbsenceDate->gt($endOfRange)) {
            return collect();
        }

        $isCompleted = $task->status && strtolower((string) $task->status->type) === 'done' ;
        $completionDate = $task->completion_date
            ? Carbon::parse($task->completion_date instanceof \DateTimeInterface ? $task->completion_date->format('Y-m-d') : $task->completion_date)->startOfDay()
            : null;

        if ($isCompleted && $completionDate !== null) {
            if ($completionDate->lte($start)) {
                return collect();
            }
            $end = $completionDate->copy()->min($endOfRange);
        } else {
            $end = $endOfRange->copy();
        }

        $dates = collect();
        $current = $firstAbsenceDate->copy();
        while ($current->lte($end)) {
            $dates->push($current->copy());
            $current->addDay();
        }
        return $dates;
    }
}
