<?php

namespace App\Http\Controllers;

use App\Models\PullRequest;
use App\Models\Project;
use App\Models\Track;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PullRequestController extends Controller
{
    private const PR_TRACKS_ONLY_MESSAGE = 'Pull Requests are only available for Backend, Frontend, and Mobile tracks.';

    /**
     * Display pull requests dashboard. Guests see own; member/admin/owner see all with filters.
     * Only guests in backend, frontend, or mobile track can access; members/admins/owners can always access.
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }

        if ($user->isGuestInWorkspace($workspaceId) && !$user->hasPullRequestTrackInWorkspace($workspaceId)) {
            abort(403, self::PR_TRACKS_ONLY_MESSAGE);
        }

        $isGuest = $user->isGuestInWorkspace($workspaceId);
        $canManageAll = $user->isAdminInWorkspace($workspaceId) || $user->isOwnerInWorkspace($workspaceId) || $user->isMemberInWorkspace($workspaceId);
        $isMemberOnly = $user->isMemberOnlyInWorkspace($workspaceId);
        $isAdminOrOwner = $user->isAdminInWorkspace($workspaceId) || $user->isOwnerInWorkspace($workspaceId);
        $query = PullRequest::where('workspace_id', $workspaceId)
            ->with(['user', 'project', 'track']);

        if ($isGuest) {
            $query->where('user_id', $user->id);
        }

        // Members (non-admin) see only PRs for projects they created
        if ($isMemberOnly) {
            $query->whereHas('project', fn ($q) => $q->where('created_by_user_id', $user->id));
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        if ($request->filled('track_id')) {
            
            $query->where('track_id', $request->track_id);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $pullRequests = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $today = today()->format('Y-m-d');
        $prRequiredToday = PullRequest::isRequiredDay($today);
        $todaySubmitted = $isGuest
            ? PullRequest::where('workspace_id', $workspaceId)->where('user_id', $user->id)->where('date', $today)->exists()
            : null;

        // For admin/member/owner: list guests who did not submit for a specific day (when filtering by single date or showing compliance)
        $guestsWithoutPrForDate = collect();
        $workspaceGuests = collect();
        $tracks = collect();
        $projectsFilter = collect();
        $guestMissingCounts = [];

        if ($canManageAll) {
            $workspace = Workspace::find($workspaceId);
            // Members see only guests they created; admin/owner see all workspace guests (exclude workspace owner; only PR tracks)
            $workspaceGuests = $isMemberOnly
                ? $user->getCreatedGuestsInWorkspace($workspaceId)
                : ($workspace ? $workspace->guests()->get() : collect());
            $prTrackIds = config('pull_requests.track_ids', []);
            $tracks = collect();
            if (!empty($prTrackIds)) {
                $tracks = Track::where('workspace_id', $workspaceId)
                    ->whereIn('id', $prTrackIds)
                    ->active()
                    ->ordered()
                    ->get();
            }
            if ($tracks->isEmpty()) {
                $tracks = Track::where('workspace_id', $workspaceId)->active()->ordered()->get()
                    ->filter(fn ($t) => in_array(strtolower(trim($t->name ?? '')), ['backend', 'frontend', 'mobile'], true))
                    ->values();
            }
            $prTrackIdsForWorkspace = $tracks->pluck('id')->toArray();

            if ($workspace && $workspaceGuests->isNotEmpty()) {
                $workspaceGuests = $workspaceGuests
                    ->filter(fn ($u) => (int) $u->id !== (int) $workspace->owner_id)
                    ->filter(function ($u) use ($workspaceId, $prTrackIdsForWorkspace) {
                        $trackId = (int) ($u->getTrackIdInWorkspace($workspaceId) ?? 0);
                        return $trackId && in_array($trackId, $prTrackIdsForWorkspace, true);
                    })
                    ->values();
            }
                
            // Members see only projects they created; admin/owner see all
            $projectsFilter = $isMemberOnly
                ? Project::where('workspace_id', $workspaceId)->where('created_by_user_id', $user->id)->orderBy('name')->get()
                : Project::where('workspace_id', $workspaceId)->orderBy('name')->get();

            $checkDate = $request->filled('date_from') && $request->filled('date_to') && $request->date_from === $request->date_to
                ? $request->date_from
                : $today;
            if (PullRequest::isRequiredDay($checkDate)) {
                $submittedUserIds = PullRequest::where('workspace_id', $workspaceId)
                    ->where('date', $checkDate)
                    ->pluck('user_id')
                    ->unique();
                $guestsWithoutPrForDate = $workspaceGuests->whereNotIn('id', $submittedUserIds);
            }

            // Count how many required PR days each guest missed in the filter period (for display beside name)
            $guestMissingCounts = [];
            $periodStart = $request->filled('date_from') ? $request->date_from : Carbon::parse($checkDate)->startOfMonth()->format('Y-m-d');
            $periodEnd = $request->filled('date_to') ? $request->date_to : $checkDate;
            $requiredDaysInPeriod = collect();
            for ($d = Carbon::parse($periodStart); $d->lte(Carbon::parse($periodEnd)); $d->addDay()) {
                if (PullRequest::isRequiredDay($d->format('Y-m-d'))) {
                    $requiredDaysInPeriod->push($d->format('Y-m-d'));
                }
            }
            
            $requiredDaysInPeriod = $requiredDaysInPeriod->unique()->values();
            if ($requiredDaysInPeriod->isNotEmpty() && $workspaceGuests->isNotEmpty()) {
                $guestIds = $workspaceGuests->pluck('id')->toArray();
                $submittedByGuestAndDate = PullRequest::where('workspace_id', $workspaceId)
                    ->whereIn('user_id', $guestIds)
                    ->whereDate('date', '>=', $periodStart)
                    ->whereDate('date', '<=', $periodEnd)
                    ->get()
                    ->groupBy('user_id')
                    ->map(fn ($prs) => $prs->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->unique()->flip()->toArray());
                foreach ($workspaceGuests as $g) {
                    $submittedDates = $submittedByGuestAndDate->get($g->id, []);
                    $missing = $requiredDaysInPeriod->filter(fn ($day) => !isset($submittedDates[$day]))->count();
                    $guestMissingCounts[$g->id] = $missing;
                }
            }
        }

        return view('pull-requests.index', compact(
            'pullRequests',
            'isGuest',
            'canManageAll',
            'prRequiredToday',
            'todaySubmitted',
            'today',
            'guestsWithoutPrForDate',
            'workspaceGuests',
            'tracks',
            'projectsFilter',
            'guestMissingCounts',
            'isAdminOrOwner'
        ));
    }

    /**
     * Show the form for creating a new pull request (guests only; must be in backend/frontend/mobile track).
     */
    public function create(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }

        if (!$user->isGuestInWorkspace($workspaceId)) {
            abort(403, 'Only guests can submit pull requests.');
        }
        if (!$user->hasPullRequestTrackInWorkspace($workspaceId)) {
            abort(403, self::PR_TRACKS_ONLY_MESSAGE);
        }

        $assignedProjects = $this->getAssignedProjectsForGuest($workspaceId, $user->id);
        if ($assignedProjects->isEmpty()) {
            return redirect()->route('pull-requests.index')
                ->with('error', 'You are not assigned to any project. Get assigned first to submit pull requests.');
        }

        $date = $request->get('date', today()->format('Y-m-d'));

        return view('pull-requests.create', compact('assignedProjects', 'date'));
    }

    /**
     * Store a newly created pull request.
     */
    public function store(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        if (!$workspaceId || !$user->isGuestInWorkspace($workspaceId)) {
            abort(403, 'Only guests can submit pull requests.');
        }
        if (!$user->hasPullRequestTrackInWorkspace($workspaceId)) {
            abort(403, self::PR_TRACKS_ONLY_MESSAGE);
        }

        $assignedProjects = $this->getAssignedProjectsForGuest($workspaceId, $user->id);
        $validProjectIds = $assignedProjects->pluck('id')->toArray();

        $validated = $request->validate([
            'project_id' => 'required|in:' . implode(',', $validProjectIds),
            'link' => 'required|url|max:2048',
            'date' => 'required|date',
        ], [
            'link.required' => 'Please enter the pull request URL.',
            'link.url' => 'The pull request link must be a valid URL.',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $projectMember = $project->projectMembers()->where('user_id', $user->id)->where('role', 'guest')->first();
        $trackId = $projectMember?->track_id;

        PullRequest::create([
            'workspace_id' => $workspaceId,
            'user_id' => $user->id,
            'project_id' => $validated['project_id'],
            'track_id' => $trackId,
            'link' => $validated['link'],
            'date' => $validated['date'],
        ]);

        return redirect()->route('pull-requests.index')
            ->with('success', 'Pull request submitted successfully.');
    }

    /**
     * Display the specified pull request (redirect to index; single-PR view not required).
     */
    public function show(PullRequest $pullRequest)
    {
        return redirect()->route('pull-requests.index');
    }

    /**
     * Show the form for editing the specified pull request.
     */
    public function edit(PullRequest $pullRequest)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        if ($pullRequest->workspace_id != $workspaceId) {
            abort(404);
        }

        $canManageAll = $user->isAdminInWorkspace($workspaceId) || $user->isOwnerInWorkspace($workspaceId);
        
        if (!$canManageAll && $pullRequest->user_id != $user->id) {
            abort(403, 'You can only edit your own pull requests.');
        }
        if ($user->isGuestInWorkspace($workspaceId) && !$user->hasPullRequestTrackInWorkspace($workspaceId)) {
            abort(403, self::PR_TRACKS_ONLY_MESSAGE);
        }

        $assignedProjects = $user->isGuestInWorkspace($workspaceId)
            ? $this->getAssignedProjectsForGuest($workspaceId, $user->id)
            : Project::where('workspace_id', $workspaceId)->orderBy('name')->get();

        return view('pull-requests.edit', compact('pullRequest', 'assignedProjects'));
    }

    /**
     * Update the specified pull request.
     */
    public function update(Request $request, PullRequest $pullRequest)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        if ($pullRequest->workspace_id != $workspaceId) {
            abort(404);
        }

        if ($user->isGuestInWorkspace($workspaceId) && !$user->hasPullRequestTrackInWorkspace($workspaceId)) {
            abort(403, self::PR_TRACKS_ONLY_MESSAGE);
        }

        $canManageAll = $user->isAdminInWorkspace($workspaceId) || $user->isOwnerInWorkspace($workspaceId);
        if (!$canManageAll && $pullRequest->user_id != $user->id) {
            abort(403, 'You can only update your own pull requests.');
        }

        $validProjectIds = $canManageAll
            ? Project::where('workspace_id', $workspaceId)->pluck('id')->toArray()
            : $this->getAssignedProjectsForGuest($workspaceId, $user->id)->pluck('id')->toArray();

        $validated = $request->validate([
            'project_id' => 'required|in:' . implode(',', $validProjectIds),
            'link' => 'required|url|max:2048',
            'date' => 'required|date',
        ], [
            'link.required' => 'Please enter the pull request URL.',
            'link.url' => 'The pull request link must be a valid URL.',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $projectMember = $project->projectMembers()->where('user_id', $pullRequest->user_id)->where('role', 'guest')->first();
        $trackId = $projectMember?->track_id;

        $pullRequest->update([
            'project_id' => $validated['project_id'],
            'track_id' => $trackId,
            'link' => $validated['link'],
            'date' => $validated['date'],
        ]);

        return redirect()->route('pull-requests.index')
            ->with('success', 'Pull request updated successfully.');
    }

    /**
     * Remove the specified pull request.
     */
    public function destroy(PullRequest $pullRequest)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        $isMemberInWorkspace = $user->isMemberInWorkspace($workspaceId);
        if ($pullRequest->workspace_id != $workspaceId) {
            abort(404);
        }

        if ($user->isGuestInWorkspace($workspaceId) && !$user->hasPullRequestTrackInWorkspace($workspaceId)) {
            abort(403, self::PR_TRACKS_ONLY_MESSAGE);
        }

        $canManageAll = $user->isAdminInWorkspace($workspaceId) || $user->isOwnerInWorkspace($workspaceId);
        // if ((!$canManageAll && $pullRequest->user_id != $user->id )|| $isMemberInWorkspace ) {
        //     abort(403, 'You can only delete your own pull requests.');
        // }

        $pullRequest->delete();

        return redirect()->route('pull-requests.index')
            ->with('success', 'Pull request deleted successfully.');
    }

    /**
     * Get projects the guest is assigned to (as guest in project_members).
     */
    private function getAssignedProjectsForGuest(int $workspaceId, int $userId)
    {
        return Project::where('workspace_id', $workspaceId)
            ->whereHas('projectMembers', function ($q) use ($userId) {
                $q->where('user_id', $userId)->where('role', 'guest');
            })
            ->orderBy('name')
            ->get();
    }
}
