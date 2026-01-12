<?php

namespace App\Http\Controllers;

use App\Models\GuestReport;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GuestReportController extends Controller
{
    /**
     * Display a listing of reports (Admin view - all reports)
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }

        // Admin can see all reports
        if ($user->isAdminInWorkspace($workspaceId) || $user->isOwnerInWorkspace($workspaceId)) {
            return $this->adminView($workspaceId, $request);
        }
        
        // Members see their reports
        if ($user->isMemberOnlyInWorkspace($workspaceId)) {
            return $this->memberView($workspaceId, $user);
        }
        
        // Guests see their own reports
        return $this->guestView($workspaceId, $user);
    }

    /**
     * Admin view - see all reports and warnings
     */
    private function adminView(int $workspaceId, Request $request)
    {
        $workspace = Workspace::find($workspaceId);
        
        // Get all reports
        $reports = GuestReport::where('workspace_id', $workspaceId)
            ->with(['guest', 'member'])
            ->orderBy('week_start_date', 'desc')
            ->paginate(20);

        // Check for members who haven't submitted reports this week
        $currentWeekStart = now()->startOfWeek();
        $currentWeekEnd = now()->endOfWeek();
        
        $members = $workspace->users()
            ->wherePivot('role', 'member')
            ->get();
        
        $warnings = [];
        foreach ($members as $member) {
            // Get all guests for this member
            $memberGuests = $workspace->users()
                ->wherePivot('role', 'guest')
                ->wherePivot('created_by_user_id', $member->id)
                ->get();
            
            if ($memberGuests->count() > 0) {
                // Check if member has submitted reports for all guests this week
                $reportsThisWeek = GuestReport::where('workspace_id', $workspaceId)
                    ->where('member_id', $member->id)
                    ->where('week_start_date', $currentWeekStart)
                    ->pluck('guest_id')
                    ->toArray();
                
                $missingGuests = $memberGuests->whereNotIn('id', $reportsThisWeek);
                
                if ($missingGuests->count() > 0) {
                    $warnings[] = [
                        'member' => $member,
                        'missing_guests' => $missingGuests,
                        'total_guests' => $memberGuests->count(),
                    ];
                }
            }
        }

        return view('reports.admin', compact('reports', 'warnings', 'workspace'));
    }

    /**
     * Member view - see their reports
     */
    private function memberView(int $workspaceId, User $member)
    {
        $workspace = Workspace::find($workspaceId);
        
        // Get member's guests
        $guests = $workspace->users()
            ->wherePivot('role', 'guest')
            ->wherePivot('created_by_user_id', $member->id)
            ->get();

        // Get member's reports
        $reports = GuestReport::where('workspace_id', $workspaceId)
            ->where('member_id', $member->id)
            ->with('guest')
            ->orderBy('week_start_date', 'desc')
            ->paginate(20);

        return view('reports.member', compact('reports', 'guests', 'workspace'));
    }

    /**
     * Guest view - see their own reports
     */
    private function guestView(int $workspaceId, User $guest)
    {
        $reports = GuestReport::where('workspace_id', $workspaceId)
            ->where('guest_id', $guest->id)
            ->with('member')
            ->orderBy('week_start_date', 'desc')
            ->paginate(20);

        return view('reports.guest', compact('reports'));
    }

    /**
     * Show the form for creating a new report
     */
    public function create(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        if (!$user->isMemberOnlyInWorkspace($workspaceId) && !$user->isAdminInWorkspace($workspaceId)) {
            abort(403, 'Only members can create reports');
        }

        $workspace = Workspace::find($workspaceId);
        
        // Get member's guests
        $guests = $workspace->users()
            ->wherePivot('role', 'guest')
            ->wherePivot('created_by_user_id', $user->id)
            ->get();

        // Pre-select guest if provided
        $selectedGuestId = $request->get('guest_id');

        return view('reports.create', compact('guests', 'workspace', 'selectedGuestId'));
    }

    /**
     * Store a newly created report
     */
    public function store(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        if (!$user->isMemberOnlyInWorkspace($workspaceId) && !$user->isAdminInWorkspace($workspaceId)) {
            abort(403, 'Only members can create reports');
        }

        $validated = $request->validate([
            'guest_id' => 'required|exists:users,id',
            'week_start_date' => 'required|date',
            'week_end_date' => 'required|date|after_or_equal:week_start_date',
            'weaknesses' => 'nullable|string|max:5000',
            'strong_points' => 'nullable|string|max:5000',
            'feedback' => 'required|string|max:5000',
        ]);

        GuestReport::create([
            'workspace_id' => $workspaceId,
            'guest_id' => $validated['guest_id'],
            'member_id' => $user->id,
            'week_start_date' => $validated['week_start_date'],
            'week_end_date' => $validated['week_end_date'],
            'weaknesses' => $validated['weaknesses'],
            'strong_points' => $validated['strong_points'],
            'feedback' => $validated['feedback'],
        ]);

        return redirect()->route('reports.index')
            ->with('success', 'Report submitted successfully!');
    }

    /**
     * Display the specified report
     */
    public function show(GuestReport $report)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        // Check if user has access to this report
        if ($report->workspace_id !== $workspaceId) {
            abort(403);
        }

        if (!$user->isAdminInWorkspace($workspaceId) && 
            !$user->isOwnerInWorkspace($workspaceId) &&
            $report->member_id !== $user->id &&
            $report->guest_id !== $user->id) {
            abort(403);
        }

        $report->load(['guest', 'member']);

        return view('reports.show', compact('report'));
    }
}
