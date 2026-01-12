<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckWorkspaceAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Get workspace from route parameter or request
        $workspaceId = $request->route('workspace')?->id 
            ?? $request->route('workspace')
            ?? $request->input('workspace_id')
            ?? session('current_workspace_id');

        // If no workspace specified, try to get user's first workspace
        if (!$workspaceId) {
            $workspace = $user->workspaces()->first();
            if ($workspace) {
                $workspaceId = $workspace->id;
            } else {
                // User has no workspaces, redirect to waiting page
                return redirect()->route('no-workspace');
            }
        }

        // Verify user belongs to workspace
        if (!$user->belongsToWorkspace($workspaceId)) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Set current workspace in session
        session(['current_workspace_id' => $workspaceId]);

        // Make workspace available to all views
        $workspace = \App\Models\Workspace::find($workspaceId);
        view()->share('currentWorkspace', $workspace);

        return $next($request);
    }
}
