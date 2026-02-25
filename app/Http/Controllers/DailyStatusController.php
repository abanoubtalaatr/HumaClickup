<?php

namespace App\Http\Controllers;

use App\Models\DailyStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        $member = $user->isMemberOnlyInWorkspace($workspaceId);
        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }

        $isGuest = $user->isGuestInWorkspace($workspaceId);
        $isOwner = $user->isOwnerInWorkspace($workspaceId);

        // Owner sees all statuses in the workspace; others see only their own
        $query = DailyStatus::where('workspace_id', $workspaceId);
        
        if($isGuest){
            $query->where('user_id', $user->id);
        }
        if($member){

            $query->whereIn('user_id', $user->getCreatedGuestsInWorkspace($workspaceId)->pluck('id')->toArray());
        }
        // Filter by date if provided
        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        // Filter by date range if provided
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $statuses = $query->with('user')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get today's status if exists (current user's)
        $todayStatus = DailyStatus::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->where('date', today())
            ->first();

        // Get tomorrow's date
        $tomorrow = today()->addDay();

        return view('daily-statuses.index', compact('statuses', 'todayStatus', 'tomorrow', 'isGuest', 'isOwner'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }

        // Get the date from request or default to today
        $date = $request->get('date', today()->format('Y-m-d'));

        // Check if status already exists for this date
        $existingStatus = DailyStatus::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->where('date', $date)
            ->first();

        if ($existingStatus) {
            return redirect()->route('daily-statuses.edit', $existingStatus)
                ->with('info', 'You already have a status for this date. You can edit it below.');
        }

        return view('daily-statuses.create', compact('date'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        $validated = $request->validate([
            'date' => 'required|date',
            'status' => 'required|string|min:10',
        ], [
            'status.required' => 'Please enter what you did today.',
            'status.min' => 'Status must be at least 10 characters long (excluding HTML tags).',
        ]);
        
        // Strip HTML tags for length validation, but keep the HTML for storage
        $textContent = strip_tags($validated['status']);
        if (strlen(trim($textContent)) < 10) {
            return back()->withErrors([
                'status' => 'Status must contain at least 10 characters of actual text.'
            ])->withInput();
        }

        // Check if status already exists for this date
        $existingStatus = DailyStatus::where('workspace_id', $workspaceId)
            ->where('user_id', $user->id)
            ->where('date', $validated['date'])
            ->first();

        if ($existingStatus) {
            return redirect()->route('daily-statuses.edit', $existingStatus)
                ->with('error', 'You already have a status for this date. Please edit it instead.');
        }

        try {
            $dailyStatus = DailyStatus::create([
                'workspace_id' => $workspaceId,
                'user_id' => $user->id,
                'date' => $validated['date'],
                'status' => $validated['status'],
            ]);

            return redirect()->route('daily-statuses.index')
                ->with('success', 'Daily status created successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint violation
            if ($e->getCode() == 23000) {
                $existingStatus = DailyStatus::where('workspace_id', $workspaceId)
                    ->where('user_id', $user->id)
                    ->where('date', $validated['date'])
                    ->first();
                
                if ($existingStatus) {
                    return redirect()->route('daily-statuses.edit', $existingStatus)
                        ->with('error', 'You already have a status for this date. Please edit it instead.');
                }
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyStatus $dailyStatus)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        if ($dailyStatus->workspace_id != $workspaceId) {
            abort(404);
        }
        // Owner can view any; others only their own
        if (!$user->isOwnerInWorkspace($workspaceId) && $dailyStatus->user_id != $user->id) {
            abort(403, 'You can only view your own daily statuses.');
        }

        $dailyStatus->load('user');

        return view('daily-statuses.show', compact('dailyStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyStatus $dailyStatus)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Check access - users can only edit their own statuses
        if ($dailyStatus->workspace_id != $workspaceId || $dailyStatus->user_id != $user->id) {
            abort(403, 'You can only edit your own daily statuses.');
        }

        return view('daily-statuses.edit', compact('dailyStatus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyStatus $dailyStatus)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Check access - users can only update their own statuses
        if ($dailyStatus->workspace_id != $workspaceId || $dailyStatus->user_id != $user->id) {
            abort(403, 'You can only update your own daily statuses.');
        }

        $validated = $request->validate([
            'status' => 'required|string|min:10',
        ], [
            'status.required' => 'Please enter what you did.',
            'status.min' => 'Status must be at least 10 characters long (excluding HTML tags).',
        ]);
        
        // Strip HTML tags for length validation, but keep the HTML for storage
        $textContent = strip_tags($validated['status']);
        if (strlen(trim($textContent)) < 10) {
            return back()->withErrors([
                'status' => 'Status must contain at least 10 characters of actual text.'
            ])->withInput();
        }

        $dailyStatus->update($validated);

        return redirect()->route('daily-statuses.index')
            ->with('success', 'Daily status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyStatus $dailyStatus)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Check access - users can only delete their own statuses
        if ($dailyStatus->workspace_id != $workspaceId || $dailyStatus->user_id != $user->id) {
            abort(403, 'You can only delete your own daily statuses.');
        }

        $dailyStatus->delete();

        return redirect()->route('daily-statuses.index')
            ->with('success', 'Daily status deleted successfully.');
    }
}
