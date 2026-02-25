<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Track;
use App\Models\Workspace;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\WorkspaceUser;
use Illuminate\Support\Facades\Log;

class WorkspaceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $currentWorkspaceId = session('current_workspace_id');
        
        // Guests cannot access the workspaces list page
        if ($currentWorkspaceId && $user->isGuestInWorkspace($currentWorkspaceId)) {
            return redirect()->route('dashboard')
                ->with('error', 'Guests cannot access workspace management.');
        }
        
        $workspaces = $user->workspaces()->get();

        return view('workspaces.index', compact('workspaces'));
    }

    public function create()
    {
        // Only allow workspace creation for users who are already workspace owners/admins
        // or the very first user (admin)
        if (!$this->canCreateWorkspace()) {
            abort(403, 'You do not have permission to create workspaces.');
        }
        
        return view('workspaces.create');
    }

    public function store(Request $request)
    {
        // Only allow workspace creation for users who are already workspace owners/admins
        if (!$this->canCreateWorkspace()) {
            abort(403, 'You do not have permission to create workspaces.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $workspace = Workspace::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
            'owner_id' => auth()->id(),
            'description' => $validated['description'] ?? null,
            'billing_status' => 'active',
            'storage_limit' => 10737418240, // 10GB
            'storage_used' => 0,
        ]);

        // Add creator as owner
        $workspace->addMember(auth()->user(), 'owner');

        // Set as current workspace
        session(['current_workspace_id' => $workspace->id]);

        return redirect()->route('dashboard')
            ->with('success', 'Workspace created successfully.');
    }

    /**
     * Check if the current user can create workspaces
     */
    private function canCreateWorkspace(): bool
    {
        $user = auth()->user();
        
        // First user in the system can create workspaces (initial admin)
        if (User::count() === 1 && $user->id === User::first()->id) {
            return true;
        }
        
        // User is an owner of any workspace
        if ($user->workspaces()->wherePivot('role', 'owner')->exists()) {
            return true;
        }
        
        // User is an admin of any workspace
        if ($user->workspaces()->wherePivot('role', 'admin')->exists()) {
            return true;
        }
        
        return false;
    }

    public function show(Workspace $workspace)
    {
        $this->authorize('view', $workspace);
        
        // Guests cannot access workspace details page
        if (auth()->user()->isGuestInWorkspace($workspace->id)) {
            return redirect()->route('dashboard')
                ->with('error', 'Guests cannot access workspace details.');
        }

        $workspace->load(['projects', 'users', 'tasks']);

        return view('workspaces.show', compact('workspace'));
    }

    public function edit(Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        return view('workspaces.edit', compact('workspace'));
    }

    public function update(Request $request, Workspace $workspace)
    {
        $this->authorize('update', $workspace);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $workspace->update($validated);

        return redirect()->route('workspaces.show', $workspace)
            ->with('success', 'Workspace updated successfully.');
    }

    public function destroy(Workspace $workspace)
    {
        
        $this->authorize('delete', $workspace);

        $workspace->delete();

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace deleted successfully.');
    }

    public function switch(Workspace $workspace)
    {
        $this->authorize('view', $workspace);
        
        $user = auth()->user();
        $currentWorkspaceId = session('current_workspace_id');
        
        // Guests cannot switch workspaces
        if ($currentWorkspaceId && $user->isGuestInWorkspace($currentWorkspaceId)) {
            return redirect()->route('dashboard')
                ->with('error', 'Guests cannot switch workspaces.');
        }
        
        // Prevent switching TO a workspace where user is a guest
        if ($user->isGuestInWorkspace($workspace->id)) {
            return redirect()->route('dashboard')
                ->with('error', 'You cannot switch to a workspace where you are a guest.');
        }

        session(['current_workspace_id' => $workspace->id]);

        return redirect()->route('dashboard');
    }

    /**
     * Show workspace members management page
     */
    public function members(Request $request, Workspace $workspace)
    {
        $user = auth()->user();
        $currentUserRole = $user->getRoleInWorkspace($workspace->id);
        
        // Guests cannot access this page
        // if ($currentUserRole === 'guest') {
        //     abort(403, 'You do not have permission to manage workspace members.');
        // }

        // Load workspace with users
        $workspace->load(['users', 'owner', 'tracks']);
        
        // Get filter from request (default: 'all')
        $filter = $request->get('filter', 'all');
        
        // For members, only show guests they created
        if ($user->isMemberOnlyInWorkspace($workspace->id)) {
            $members = $workspace->users()
                ->wherePivot('role', 'guest')
                ->wherePivot('created_by_user_id', $user->id)
                ->withPivot(['role', 'track_id', 'created_by_user_id', 'attendance_days', 'absence_count', 'is_suspended'])
                ->withCount('tasks')
                ->get();
            $canInviteExisting = false;
            $roles = ['guest'];
            $allGuests = collect();
            $allMembers = collect();
        } else {
            // Admin/Owner can see all members
            $members = $workspace->users()
                ->withPivot(['role', 'track_id', 'created_by_user_id', 'attendance_days', 'absence_count', 'is_suspended'])
                ->withCount('tasks')
                ->get();
            $canInviteExisting = true;
            $roles = ['admin', 'member', 'guest'];
            
            // Separate all guests and all members for filtering
            // Get all users directly from User model without any conditions, but exclude users who have any workspace
            $allGuests = User::doesntHave('workspaces')
                ->get()
                ->map(function ($user) {
                    // Create a default pivot object to prevent null errors
                    $user->pivot = (object) [
                        'role' => null,
                        'track_id' => null,
                        'created_by_user_id' => null,
                        'attendance_days' => null,
                        'absence_count' => null,
                        'is_suspended' => null,
                    ];
                    return $user;
                });
            
            $allMembers = $workspace->users()
                ->wherePivotIn('role', ['owner', 'admin', 'member'])
                ->withPivot(['role', 'track_id', 'created_by_user_id', 'attendance_days', 'absence_count', 'is_suspended'])
                ->withCount('tasks')
                ->get();
        }
        
        // Get tracks for the workspace
        $tracks = $workspace->tracks()->active()->ordered()->get();
        
        // Check if admin
        $isAdmin = $user->isAdminInWorkspace($workspace->id);

        return view('workspaces.members', compact('workspace', 'members', 'roles', 'tracks', 'canInviteExisting', 'isAdmin', 'allGuests', 'allMembers', 'filter'));
    }

    /**
     * Create a new member and add to workspace (creates new user account)
     */
    public function createMember(Request $request, Workspace $workspace)
    {
        $user = auth()->user();
        $currentUserRole = $user->getRoleInWorkspace($workspace->id);
        
        // Guests cannot create members
        if ($currentUserRole === 'guest') {
            abort(403, 'You do not have permission to add members.');
        }
        
        // Members can only create guests
        $allowedRoles = $user->isMemberOnlyInWorkspace($workspace->id) ? ['guest'] : ['admin', 'member', 'guest'];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:' . implode(',', $allowedRoles),
            'track_id' => 'nullable|exists:tracks,id',
            'whatsapp_number' => 'nullable|string|max:20',
            'slack_channel_link' => 'nullable|url|max:255',
        ]);

        // Check member capacity
        if ($workspace->member_capacity && $workspace->users()->count() >= $workspace->member_capacity) {
            return back()->with('error', 'Workspace member capacity reached. Please upgrade your plan.');
        }

        // Create new user with default password
        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt('password'), // Default password
            'email_verified_at' => now(),
            'status' => 'active',
            'timezone' => 'UTC',
            'locale' => 'en',
            'whatsapp_number' => $validated['whatsapp_number'] ?? null,
            'slack_channel_link' => $validated['slack_channel_link'] ?? null,
        ]);

        // Determine track_id
        $trackId = $validated['track_id'] ?? null;
        
        // If member creates guest, default to member's track
        if ($user->isMemberOnlyInWorkspace($workspace->id) && $validated['role'] === 'guest' && !$trackId) {
            $trackId = $user->getTrackIdInWorkspace($workspace->id);
        }

        // Add member to workspace with track_id and created_by_user_id
        $workspace->addMember($newUser, $validated['role'], $trackId, $user->id);

        return back()->with('success', "Successfully created {$newUser->name} with default password 'password'. They should change it from their profile.");
    }

    /**
     * Invite an existing user to the workspace (admin only)
     */
    public function inviteMember(Request $request, Workspace $workspace)
    {
        $user = auth()->user();
        
        // Only admins can invite existing users
        if (!$user->isAdminInWorkspace($workspace->id)) {
            abort(403, 'Only admins can invite existing users.');
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:admin,member,guest',
            'track_id' => 'nullable|exists:tracks,id',
            'attendance_days' => 'nullable|array',
            'attendance_days.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ]);

        // Check if user exists
        $existingUser = User::where('email', $validated['email'])->first();

        if (!$existingUser) {
            return back()->with('error', 'No user found with that email. Use "Create New Member" to create a new account.');
        }

        // Check if already a member
        if ($workspace->users()->where('user_id', $existingUser->id)->exists()) {
            return back()->with('error', 'This user is already a member of this workspace.');
        }

        // Check member capacity
        if ($workspace->member_capacity && $workspace->users()->count() >= $workspace->member_capacity) {
            return back()->with('error', 'Workspace member capacity reached. Please upgrade your plan.');
        }

        // Add member with track_id, created_by_user_id, and attendance_days
        $workspace->addMember(
            $existingUser, 
            $validated['role'], 
            $validated['track_id'] ?? null, 
            $user->id,
            $validated['role'] === 'guest' ? ($validated['attendance_days'] ?? []) : null
        );

        return back()->with('success', "Successfully added {$existingUser->name} to the workspace.");
    }

    /**
     * Update a member's role and/or track
     */
    public function updateMemberRole(Request $request, Workspace $workspace, User $user)
    {
        
        $currentUser = auth()->user();
        $targetUser = $user; // The user being edited
        
        // Check if user can manage this member
        // if (!$currentUser->canManageMember($targetUser, $workspace->id)) {
        //     abort(403, 'You do not have permission to update this member.');
        // }

        // Cannot change owner's role
        if ($workspace->owner_id === $targetUser->id) {
            return back()->with('error', 'Cannot change the role of the workspace owner.');
        }
        
        // Members can only change guests to guest role
        $allowedRoles = $currentUser->isMemberOnlyInWorkspace($workspace->id) ? ['guest'] : ['admin', 'member', 'guest'];

        // Validate email separately with proper unique rule
        $request->validate([
            'email' => 'required|email|max:255|unique:users,email,' . $targetUser->id . ',id',
        ]);

        
        $validated = $request->validate([
            'role' => 'required|in:' . implode(',', $allowedRoles),
            'track_id' => 'nullable|exists:tracks,id',
            'whatsapp_number' => 'nullable|string|max:20',
            'slack_channel_link' => 'nullable|sometimes|url|max:255',
            'attendance_days' => 'nullable|array',
            'attendance_days.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ]);

        // Update workspace role and track
        $workspace->updateMemberRole($targetUser, $validated['role'], $validated['track_id'] ?? null);

        // Update attendance days for guests (always update if role is guest)
        if ($validated['role'] === 'guest' || $targetUser->isGuestInWorkspace($workspace->id)) {
            $attendanceDays = $request->input('attendance_days', []);
            Log::info('Attendance Days Update:', [
                'guest_id' => $targetUser->id,
                'attendance_days_raw' => $attendanceDays,
                'attendance_days_json' => json_encode($attendanceDays),
            ]);
            
            $workspace->users()->updateExistingPivot($targetUser->id, [
                'attendance_days' => json_encode($attendanceDays),
            ]);
        }

        // Update user information
        $targetUser->update([
            'email' => $request->email,
            'whatsapp_number' => !empty($validated['whatsapp_number']) ? $validated['whatsapp_number'] : null,
            'slack_channel_link' => !empty($validated['slack_channel_link']) ? $validated['slack_channel_link'] : null,
        ]);

        return back()->with('success', "Updated {$targetUser->name}'s information successfully.");
    }

    /**
     * Remove a member from the workspace with task reassignment
     */
    public function removeMember(Request $request, Workspace $workspace, User $targetUser)
    {
        $user = auth()->user();
        
        // Cannot remove owner
        if ($workspace->owner_id === $targetUser->id) {
            return back()->with('error', 'Cannot remove the workspace owner.');
        }

        // Cannot remove yourself if you're not the owner
        if ($targetUser->id === $user->id && $workspace->owner_id !== $user->id) {
            return back()->with('error', 'You cannot remove yourself from the workspace.');
        }
        
        // Check if user can manage this member
        // if (!$user->canManageMember($targetUser, $workspace->id)) {
        //     return back()->with('error', 'You do not have permission to remove this member.');
        // }

        // Get tasks assigned to this user
        // $assignedTasks = Task::where('workspace_id', $workspace->id)
        //     ->whereHas('assignees', function ($q) use ($targetUser) {
        //         $q->where('user_id', $targetUser->id);
        //     })->get();

        // Handle task reassignment if specified
        // $reassignTo = $request->input('reassign_to');
        // if ($reassignTo && $assignedTasks->count() > 0) {
        //     $newAssignee = User::find($reassignTo);
        //     if ($newAssignee && $workspace->users()->where('user_id', $newAssignee->id)->exists()) {
        //         foreach ($assignedTasks as $task) {
        //             // Remove old assignee and add new one
        //             $task->assignees()->detach($targetUser->id);
        //             if (!$task->assignees()->where('user_id', $newAssignee->id)->exists()) {
        //                 $task->assignees()->attach($newAssignee->id);
        //             }
        //         }
        //     }
        // } else if ($assignedTasks->count() > 0) {
        //     // Just remove the user from assigned tasks
        //     foreach ($assignedTasks as $task) {
        //         $task->assignees()->detach($targetUser->id);
        //     }
        // }

        WorkspaceUser::where('workspace_id', $workspace->id)->where('user_id', $targetUser->id)->delete();

        return back()->with('success', "Successfully removed {$targetUser->name} from the workspace.");
    }

    /**
     * Get tasks assigned to a user (for reassignment modal)
     */
    public function getMemberTasks(Workspace $workspace, User $targetUser)
    {
        $user = auth()->user();
        
        if (!$user->canManageMember($targetUser, $workspace->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $tasks = Task::where('workspace_id', $workspace->id)
            ->whereHas('assignees', function ($q) use ($targetUser) {
                $q->where('user_id', $targetUser->id);
            })
            ->with(['project', 'status'])
            ->get();

        // Get other members that tasks can be reassigned to
        $otherMembers = $workspace->users()
            ->where('user_id', '!=', $targetUser->id)
            ->get(['users.id', 'users.name']);

        return response()->json([
            'tasks' => $tasks,
            'members' => $otherMembers,
        ]);
    }

    /**
     * Assign guests to a member (update created_by_user_id)
     */
    public function assignGuestsToMember(Request $request, Workspace $workspace)
    {
        $user = auth()->user();
        
        // Only admins can assign guests
        if (!$user->isAdminInWorkspace($workspace->id)) {
            abort(403, 'You do not have permission to assign guests.');
        }

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'guest_ids' => 'required|array',
            'guest_ids.*' => 'exists:users,id',
        ]);

        $memberId = $validated['member_id'];
        $guestIds = $validated['guest_ids'];

        // Verify the member is in this workspace and is a member/admin/owner
        $member = $workspace->users()
            ->wherePivotIn('role', ['owner', 'admin', 'member'])
            ->find($memberId);
        
        if (!$member) {
            return back()->with('error', 'Selected member is not valid.');
        }

        // Get member's track_id from their pivot
        $memberTrackId = $member->pivot->track_id;

        // Verify all guest IDs exist as users
        $guests = User::whereIn('id', $guestIds)->get();

        if ($guests->count() !== count($guestIds)) {
            return back()->with('error', 'Some selected guests are not valid.');
        }

        // Create workspace_user entries for each guest
        $createdCount = 0;
        foreach ($guests as $guest) {
            // Check if guest already exists in workspace
            $existingPivot = $workspace->users()->where('users.id', $guest->id)->first();
            
            if ($existingPivot) {
                // Update existing entry
                $workspace->users()->updateExistingPivot($guest->id, [
                    'created_by_user_id' => $memberId,
                    'track_id' => $memberTrackId,
                ]);
            } else {
                // Create new entry in workspace_user table
                $workspace->addMember($guest, 'guest', $memberTrackId, $memberId);
            }
            $createdCount++;
        }

        return back()->with('success', "Successfully assigned {$createdCount} guest(s) to {$member->name}.");
    }
}
