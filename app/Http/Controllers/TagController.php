<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Store a newly created tag. Members and admins can create tags in the current workspace.
     */
    public function store(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        if (!$workspaceId) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'No workspace selected.'], 403);
            }
            return back()->with('error', 'No workspace selected.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $validated['workspace_id'] = $workspaceId;
        $validated['color'] = $validated['color'] ?? '#6366f1';

        // Ensure unique name per workspace
        $exists = Tag::where('workspace_id', $workspaceId)
            ->where('name', $validated['name'])
            ->exists();
        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A tag with this name already exists in the workspace.',
                ], 422);
            }
            return back()->with('error', 'A tag with this name already exists.');
        }

        $tag = Tag::create($validated);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'tag' => $tag,
            ]);
        }

        return back()->with('success', 'Tag created.');
    }
}
