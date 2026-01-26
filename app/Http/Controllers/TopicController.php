<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Track;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        
        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }

        $isAdmin = $user->isAdminInWorkspace($workspaceId) || $user->isOwnerInWorkspace($workspaceId);
        $isGuest = $user->isGuestInWorkspace($workspaceId);

        // Get all tracks for filtering (admin only)
        $tracks = collect();
        if ($isAdmin) {
            $tracks = Track::where('workspace_id', $workspaceId)
                ->where('is_active', true)
                ->orderBy('order')
                ->orderBy('name')
                ->get();
        }

        // Build query
        $query = Topic::where('workspace_id', $workspaceId);

        // Guests can only see their own topics
        if ($isGuest) {
            $query->where('user_id', $user->id);
        }

        // Admin can filter by track
        if ($isAdmin && $request->filled('track_id')) {
            $query->where('track_id', $request->track_id);
        }

        // Filter by completion status
        if ($request->filled('is_complete')) {
            $query->where('is_complete', $request->is_complete === '1');
        }

        $topics = $query->with(['user', 'track'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // For admin: Get topic counts by track and user
        $trackStats = collect();
        $userStats = collect();
        
        if ($isAdmin) {
            // Count topics by track
            $trackStats = Topic::where('workspace_id', $workspaceId)
                ->selectRaw('track_id, COUNT(*) as count')
                ->groupBy('track_id')
                ->get()
                ->keyBy('track_id');

            // Count topics by user (grouped by track)
            $allTopicsForStats = Topic::where('workspace_id', $workspaceId)
                ->with(['user', 'track'])
                ->get();
                
            $userStats = $allTopicsForStats->groupBy(function ($topic) {
                    return $topic->track_id ?? 'no_track';
                })
                ->map(function ($topics) {
                    return $topics->groupBy('user_id')
                        ->map(function ($userTopics) {
                            return [
                                'user' => $userTopics->first()->user,
                                'count' => $userTopics->count(),
                            ];
                        })
                        ->values();
                });
        }

        return view('topics.index', compact('topics', 'tracks', 'isAdmin', 'isGuest', 'trackStats', 'userStats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $workspaceId = session('current_workspace_id');
        
        if (!$workspaceId) {
            return redirect()->route('workspaces.index');
        }

        // Get user's track for pre-selection
        $user = auth()->user();
        $userTrack = $user->getTrackInWorkspace($workspaceId);
        
        // Get all tracks for selection
        $tracks = Track::where('workspace_id', $workspaceId)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('topics.create', compact('tracks', 'userTrack'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'presentation_link' => 'nullable|url|max:500',
            'date' => 'required|date',
            'track_id' => 'nullable|exists:tracks,id',
            'is_complete' => 'boolean',
        ]);

        $topic = Topic::create([
            'workspace_id' => $workspaceId,
            'user_id' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'presentation_link' => $validated['presentation_link'] ?? null,
            'date' => $validated['date'],
            'track_id' => $validated['track_id'] ?? null,
            'is_complete' => $validated['is_complete'] ?? false,
        ]);

        return redirect()->route('topics.index')
            ->with('success', 'Topic created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Topic $topic)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Check access
        if ($topic->workspace_id != $workspaceId) {
            abort(404);
        }

        $isGuest = $user->isGuestInWorkspace($workspaceId);
        
        // Guests can only see their own topics
        if ($isGuest && $topic->user_id != $user->id) {
            abort(403, 'You can only view your own topics.');
        }

        $topic->load(['user', 'track']);

        return view('topics.show', compact('topic'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Topic $topic)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Check access
        if ($topic->workspace_id != $workspaceId) {
            abort(404);
        }

        $isGuest = $user->isGuestInWorkspace($workspaceId);
        
        // Guests can only edit their own topics
        if ($isGuest && $topic->user_id != $user->id) {
            abort(403, 'You can only edit your own topics.');
        }

        // Get all tracks for selection
        $tracks = Track::where('workspace_id', $workspaceId)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('topics.edit', compact('topic', 'tracks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Topic $topic)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Check access
        if ($topic->workspace_id != $workspaceId) {
            abort(404);
        }

        $isGuest = $user->isGuestInWorkspace($workspaceId);
        
        // Guests can only update their own topics
        if ($isGuest && $topic->user_id != $user->id) {
            abort(403, 'You can only update your own topics.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'presentation_link' => 'nullable|url|max:500',
            'date' => 'required|date',
            'track_id' => 'nullable|exists:tracks,id',
            'is_complete' => 'boolean',
        ]);

        $topic->update($validated);

        return redirect()->route('topics.index')
            ->with('success', 'Topic updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Topic $topic)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Check access
        if ($topic->workspace_id != $workspaceId) {
            abort(404);
        }

        $isGuest = $user->isGuestInWorkspace($workspaceId);
        
        // Guests can only delete their own topics
        if ($isGuest && $topic->user_id != $user->id) {
            abort(403, 'You can only delete your own topics.');
        }

        $topic->delete();

        return redirect()->route('topics.index')
            ->with('success', 'Topic deleted successfully.');
    }

    /**
     * Toggle completion status
     */
    public function toggleComplete(Topic $topic)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();

        // Check access
        if ($topic->workspace_id != $workspaceId) {
            abort(404);
        }

        $isGuest = $user->isGuestInWorkspace($workspaceId);
        
        // Guests can only toggle their own topics
        if ($isGuest && $topic->user_id != $user->id) {
            abort(403, 'You can only update your own topics.');
        }

        $topic->update(['is_complete' => !$topic->is_complete]);

        return redirect()->back()
            ->with('success', 'Topic status updated.');
    }
}
