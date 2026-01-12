<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Workspace;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display groups list based on user role.
     * - Members: See their created groups
     * - Admin: See all members and their groups
     * - Guests: See groups they are assigned to
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Check user role
        if ($user->isAdminInWorkspace($workspaceId) || $user->isOwnerInWorkspace($workspaceId)) {
            return $this->adminView($workspaceId);
        } elseif ($user->isGuestInWorkspace($workspaceId)) {
            return $this->guestView($workspaceId, $user);
        } else {
            return $this->memberView($workspaceId, $user);
        }
    }

    /**
     * Member view: Show groups created by this member.
     */
    private function memberView(int $workspaceId, $user)
    {
        $groups = Group::where('workspace_id', $workspaceId)
            ->where('created_by_user_id', $user->id)
            ->with(['guests'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('groups.member', compact('groups'));
    }

    /**
     * Admin view: Show all members and their groups.
     */
    private function adminView(int $workspaceId)
    {
        $workspace = Workspace::find($workspaceId);
        
        // Get all members (not guests, not admins/owners)
        $members = $workspace->users()
            ->wherePivot('role', 'member')
            ->with(['createdGroups' => function ($query) use ($workspaceId) {
                $query->where('workspace_id', $workspaceId)->with('guests');
            }])
            ->get();

        return view('groups.admin', compact('members'));
    }

    /**
     * Guest view: Show groups guest is assigned to.
     */
    private function guestView(int $workspaceId, $user)
    {
        $groups = $user->groups()
            ->where('workspace_id', $workspaceId)
            ->with(['creator', 'guests'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('groups.guest', compact('groups'));
    }

    /**
     * Show the form for creating a new group (Members only).
     */
    public function create()
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Only members can create groups
        if (!$user->isMemberOnlyInWorkspace($workspaceId)) {
            abort(403, 'Only members can create groups.');
        }

        // Get guests created by this member
        $availableGuests = $user->getCreatedGuestsInWorkspace($workspaceId);

        return view('groups.create', compact('availableGuests'));
    }

    /**
     * Store a newly created group.
     */
    public function store(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Only members can create groups
        if (!$user->isMemberOnlyInWorkspace($workspaceId)) {
            return redirect()->route('groups.index')
                ->with('error', 'Only members can create groups.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'whatsapp_link' => 'nullable|url|max:255',
            'slack_link' => 'nullable|url|max:255',
            'repo_link' => 'nullable|url|max:255',
            'service_link' => 'nullable|url|max:255',
            'guest_ids' => 'nullable|array',
            'guest_ids.*' => 'exists:users,id',
        ]);

        $validated['workspace_id'] = $workspaceId;
        $validated['created_by_user_id'] = $user->id;
        $validated['color'] = $validated['color'] ?? '#3b82f6';

        $group = Group::create($validated);

        // Attach guests to the group
        if (!empty($validated['guest_ids'])) {
            $group->guests()->attach($validated['guest_ids'], [
                'assigned_at' => now()
            ]);
        }

        return redirect()->route('groups.index')
            ->with('success', 'Group created successfully.');
    }

    /**
     * Show the form for editing the specified group.
     */
    public function edit(Group $group)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Verify group belongs to current workspace and user
        if ($group->workspace_id !== $workspaceId || $group->created_by_user_id !== $user->id) {
            abort(403, 'You can only edit your own groups.');
        }

        // Get guests created by this member
        $availableGuests = $user->getCreatedGuestsInWorkspace($workspaceId);
        $assignedGuestIds = $group->guests()->pluck('users.id')->toArray();

        return view('groups.edit', compact('group', 'availableGuests', 'assignedGuestIds'));
    }

    /**
     * Update the specified group.
     */
    public function update(Request $request, Group $group)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Verify group belongs to current workspace and user
        if ($group->workspace_id !== $workspaceId || $group->created_by_user_id !== $user->id) {
            return redirect()->route('groups.index')
                ->with('error', 'You can only edit your own groups.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
            'whatsapp_link' => 'nullable|url|max:255',
            'slack_link' => 'nullable|url|max:255',
            'repo_link' => 'nullable|url|max:255',
            'service_link' => 'nullable|url|max:255',
            'guest_ids' => 'nullable|array',
            'guest_ids.*' => 'exists:users,id',
        ]);

        $group->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color' => $validated['color'] ?? $group->color,
            'whatsapp_link' => $validated['whatsapp_link'] ?? null,
            'slack_link' => $validated['slack_link'] ?? null,
            'repo_link' => $validated['repo_link'] ?? null,
            'service_link' => $validated['service_link'] ?? null,
        ]);

        // Sync guests
        $guestIds = $validated['guest_ids'] ?? [];
        $syncData = [];
        foreach ($guestIds as $guestId) {
            $syncData[$guestId] = ['assigned_at' => now()];
        }
        $group->guests()->sync($syncData);

        return redirect()->route('groups.index')
            ->with('success', 'Group updated successfully.');
    }

    /**
     * Remove the specified group.
     */
    public function destroy(Group $group)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Verify group belongs to current workspace and user
        if ($group->workspace_id !== $workspaceId || $group->created_by_user_id !== $user->id) {
            return redirect()->route('groups.index')
                ->with('error', 'You can only delete your own groups.');
        }

        $group->delete();

        return redirect()->route('groups.index')
            ->with('success', 'Group deleted successfully.');
    }
}
