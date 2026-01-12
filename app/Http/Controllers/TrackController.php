<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrackController extends Controller
{
    /**
     * Display a listing of tracks for the workspace.
     */
    public function index(Workspace $workspace)
    {
        // Only admins can manage tracks
        if (!auth()->user()->canManageTracksInWorkspace($workspace->id)) {
            abort(403, 'You do not have permission to manage tracks.');
        }

        $tracks = $workspace->tracks()->ordered()->get();

        return view('tracks.index', compact('workspace', 'tracks'));
    }

    /**
     * Show the form for creating a new track.
     */
    public function create(Workspace $workspace)
    {
        if (!auth()->user()->canManageTracksInWorkspace($workspace->id)) {
            abort(403, 'You do not have permission to create tracks.');
        }

        return view('tracks.create', compact('workspace'));
    }

    /**
     * Store a newly created track.
     */
    public function store(Request $request, Workspace $workspace)
    {
        if (!auth()->user()->canManageTracksInWorkspace($workspace->id)) {
            abort(403, 'You do not have permission to create tracks.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string|max:500',
        ]);

        $slug = Str::slug($validated['name']);
        
        // Ensure unique slug within workspace
        $count = 1;
        $originalSlug = $slug;
        while ($workspace->tracks()->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $maxOrder = $workspace->tracks()->max('order') ?? 0;

        $track = $workspace->tracks()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'color' => $validated['color'],
            'description' => $validated['description'],
            'order' => $maxOrder + 1,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'track' => $track]);
        }

        return redirect()->route('workspaces.tracks.index', $workspace)
            ->with('success', 'Track created successfully.');
    }

    /**
     * Show the form for editing the specified track.
     */
    public function edit(Workspace $workspace, Track $track)
    {
        if (!auth()->user()->canManageTracksInWorkspace($workspace->id)) {
            abort(403, 'You do not have permission to edit tracks.');
        }

        // Ensure track belongs to workspace
        if ($track->workspace_id !== $workspace->id) {
            abort(404);
        }

        return view('tracks.edit', compact('workspace', 'track'));
    }

    /**
     * Update the specified track.
     */
    public function update(Request $request, Workspace $workspace, Track $track)
    {
        if (!auth()->user()->canManageTracksInWorkspace($workspace->id)) {
            abort(403, 'You do not have permission to update tracks.');
        }

        if ($track->workspace_id !== $workspace->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        // Update slug if name changed
        if ($validated['name'] !== $track->name) {
            $slug = Str::slug($validated['name']);
            $count = 1;
            $originalSlug = $slug;
            while ($workspace->tracks()->where('slug', $slug)->where('id', '!=', $track->id)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $validated['slug'] = $slug;
        }

        $track->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'track' => $track]);
        }

        return redirect()->route('workspaces.tracks.index', $workspace)
            ->with('success', 'Track updated successfully.');
    }

    /**
     * Remove the specified track.
     */
    public function destroy(Workspace $workspace, Track $track)
    {
        if (!auth()->user()->canManageTracksInWorkspace($workspace->id)) {
            abort(403, 'You do not have permission to delete tracks.');
        }

        if ($track->workspace_id !== $workspace->id) {
            abort(404);
        }

        // Check if there are users using this track
        $usersWithTrack = $workspace->users()->wherePivot('track_id', $track->id)->count();
        
        if ($usersWithTrack > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => "Cannot delete track. {$usersWithTrack} user(s) are assigned to this track."
                ], 422);
            }
            return back()->with('error', "Cannot delete track. {$usersWithTrack} user(s) are assigned to this track.");
        }

        $track->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('workspaces.tracks.index', $workspace)
            ->with('success', 'Track deleted successfully.');
    }

    /**
     * Get all tracks for a workspace (JSON API)
     */
    public function list(Workspace $workspace)
    {
        $tracks = $workspace->tracks()->active()->ordered()->get(['id', 'name', 'color', 'slug']);
        return response()->json($tracks);
    }
}

