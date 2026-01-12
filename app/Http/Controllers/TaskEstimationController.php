<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskEstimation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskEstimationController extends Controller
{
    /**
     * Submit or update an estimation for a task (Guest only)
     */
    public function submit(Request $request, Task $task): JsonResponse
    {
        $user = auth()->user();
        $workspaceId = session('current_workspace_id');

        // Verify user is a guest in this workspace
        if (!$user->isGuestInWorkspace($workspaceId)) {
            return response()->json([
                'success' => false,
                'message' => 'Only guests can submit estimations.',
            ], 403);
        }

        // Verify user is assigned to this task
        if (!$task->assignees->contains($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not assigned to this task.',
            ], 403);
        }

        // Verify estimation is not already completed
        if ($task->estimation_status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Estimation polling has already been completed for this task.',
            ], 400);
        }

        $validated = $request->validate([
            'estimated_hours' => 'required|numeric|min:0|max:999',
            'estimated_minutes' => 'required|numeric|min:0|max:59',
            'notes' => 'nullable|string|max:500',
        ]);

        // Calculate total minutes
        $totalMinutes = (int)($validated['estimated_hours'] * 60) + (int)$validated['estimated_minutes'];

        if ($totalMinutes <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Estimation must be greater than 0.',
            ], 400);
        }

        // Create or update the estimation
        $estimation = TaskEstimation::updateOrCreate(
            [
                'task_id' => $task->id,
                'user_id' => $user->id,
            ],
            [
                'estimated_minutes' => $totalMinutes,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // Start polling if not already started
        $task->startEstimationPolling();

        // Check if all guests have submitted
        if ($task->allGuestsHaveEstimated()) {
            $task->completeEstimationPolling();
        }

        $progress = $task->fresh()->getEstimationProgress();

        return response()->json([
            'success' => true,
            'message' => 'Estimation submitted successfully.',
            'estimation' => $estimation,
            'progress' => $progress,
            'is_complete' => $progress['is_complete'],
            'average_minutes' => $progress['is_complete'] ? $task->fresh()->estimated_minutes : null,
        ]);
    }

    /**
     * Get tasks that need estimation for the current guest
     */
    public function getPendingTasks(): JsonResponse
    {
        $user = auth()->user();
        $workspaceId = session('current_workspace_id');

        if (!$user->isGuestInWorkspace($workspaceId)) {
            return response()->json([
                'success' => false,
                'message' => 'Only guests can view estimation polling.',
            ], 403);
        }

        // Get tasks assigned to this guest that need estimation
        $tasks = Task::where('workspace_id', $workspaceId)
            ->whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->where('estimation_status', '!=', 'completed')
            ->with(['project', 'status', 'estimations' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->get()
            ->filter(function ($task) {
                // Only include tasks that have guest assignees (need estimation)
                return $task->getGuestAssignees()->isNotEmpty();
            })
            ->map(function ($task) use ($user) {
                $myEstimation = $task->getEstimationByUser($user->id);
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'project' => $task->project?->name,
                    'status' => $task->status?->name,
                    'has_estimated' => $myEstimation !== null,
                    'my_estimation_minutes' => $myEstimation?->estimated_minutes,
                    'my_estimation_formatted' => $myEstimation?->getFormattedEstimation(),
                    'progress' => $task->getEstimationProgress(),
                ];
            });

        return response()->json([
            'success' => true,
            'tasks' => $tasks->values(),
        ]);
    }

    /**
     * Get estimation polling status for a task (for members/admins)
     */
    public function getPollingStatus(Task $task): JsonResponse
    {
        $user = auth()->user();
        $workspaceId = session('current_workspace_id');

        // Only members and admins can see full polling status
        if ($user->isGuestInWorkspace($workspaceId)) {
            // Guests can only see their own estimation and if polling is complete
            $myEstimation = $task->getEstimationByUser($user->id);
            $isComplete = $task->estimation_status === 'completed';

            return response()->json([
                'success' => true,
                'my_estimation' => $myEstimation,
                'is_complete' => $isComplete,
                'final_estimation' => $isComplete ? $task->estimated_minutes : null,
                'final_estimation_formatted' => $isComplete ? $task->getFormattedEstimation() : null,
            ]);
        }

        // For members/admins: show all estimations
        $estimations = $task->estimations()->with('user:id,name')->get();
        $progress = $task->getEstimationProgress();

        return response()->json([
            'success' => true,
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'estimation_status' => $task->estimation_status,
                'estimated_minutes' => $task->estimated_minutes,
                'estimated_formatted' => $task->getFormattedEstimation(),
                'estimation_completed_at' => $task->estimation_completed_at,
                'edited_by' => $task->estimationEditedBy?->name,
            ],
            'estimations' => $estimations->map(fn($e) => [
                'user_id' => $e->user_id,
                'user_name' => $e->user->name,
                'estimated_minutes' => $e->estimated_minutes,
                'formatted' => $e->getFormattedEstimation(),
                'notes' => $e->notes,
                'submitted_at' => $e->created_at,
            ]),
            'progress' => $progress,
        ]);
    }

    /**
     * Update the final estimation (Member/Admin only)
     */
    public function updateFinalEstimation(Request $request, Task $task): JsonResponse
    {
        $user = auth()->user();
        $workspaceId = session('current_workspace_id');

        // Only members and admins can edit final estimation
        if ($user->isGuestInWorkspace($workspaceId)) {
            return response()->json([
                'success' => false,
                'message' => 'Only members can edit the final estimation.',
            ], 403);
        }

        $validated = $request->validate([
            'estimated_hours' => 'required|numeric|min:0|max:999',
            'estimated_minutes' => 'required|numeric|min:0|max:59',
        ]);

        $totalMinutes = (int)($validated['estimated_hours'] * 60) + (int)$validated['estimated_minutes'];

        if ($totalMinutes <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Estimation must be greater than 0.',
            ], 400);
        }

        $task->update([
            'estimated_minutes' => $totalMinutes,
            'estimation_edited_by' => $user->id,
            'estimation_status' => 'completed',
            'estimation_completed_at' => $task->estimation_completed_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estimation updated successfully.',
            'estimated_minutes' => $totalMinutes,
            'formatted' => $task->fresh()->getFormattedEstimation(),
        ]);
    }

    /**
     * Get all tasks with their estimation polling status (Member/Admin view)
     */
    public function getPollingOverview(): JsonResponse
    {
        $user = auth()->user();
        $workspaceId = session('current_workspace_id');

        if ($user->isGuestInWorkspace($workspaceId)) {
            return response()->json([
                'success' => false,
                'message' => 'Only members can view the polling overview.',
            ], 403);
        }

        // Get all tasks that have estimation polling
        $tasks = Task::where('workspace_id', $workspaceId)
            ->where(function ($q) {
                $q->where('estimation_status', '!=', 'pending')
                    ->orWhereHas('estimations');
            })
            ->with(['project', 'status', 'assignees', 'estimations.user'])
            ->orderByRaw("CASE 
                WHEN estimation_status = 'polling' THEN 1 
                WHEN estimation_status = 'completed' THEN 2 
                ELSE 3 
            END")
            ->orderBy('updated_at', 'desc')
            ->get();

        $tasksData = $tasks->map(function ($task) {
            $progress = $task->getEstimationProgress();
            return [
                'id' => $task->id,
                'title' => $task->title,
                'project' => $task->project?->name,
                'status' => $task->status?->name,
                'estimation_status' => $task->estimation_status,
                'estimated_minutes' => $task->estimated_minutes,
                'estimated_formatted' => $task->getFormattedEstimation(),
                'progress' => $progress,
                'estimations' => $task->estimations->map(fn($e) => [
                    'user_name' => $e->user->name,
                    'minutes' => $e->estimated_minutes,
                    'formatted' => $e->getFormattedEstimation(),
                ]),
                'guest_assignees' => $task->getGuestAssignees()->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'has_estimated' => $task->hasEstimationFromUser($u->id),
                ]),
            ];
        });

        return response()->json([
            'success' => true,
            'tasks' => $tasksData,
            'summary' => [
                'total' => $tasks->count(),
                'polling' => $tasks->where('estimation_status', 'polling')->count(),
                'completed' => $tasks->where('estimation_status', 'completed')->count(),
            ],
        ]);
    }
}

