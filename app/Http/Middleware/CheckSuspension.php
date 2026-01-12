<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuspension
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $workspaceId = session('current_workspace_id');
        
        // Only check if user is logged in and has a workspace selected
        if ($user && $workspaceId) {
            // Check if user is a guest in this workspace
            if ($user->isGuestInWorkspace($workspaceId)) {
                // Get the pivot data for this workspace
                $workspaceUser = \DB::table('workspace_user')
                    ->where('workspace_id', $workspaceId)
                    ->where('user_id', $user->id)
                    ->first();
                
                // If suspended, log them out and redirect with error
                if ($workspaceUser && $workspaceUser->is_suspended) {
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    
                    return redirect()->route('login')
                        ->with('error', 'Your account has been suspended due to excessive absences (3 or more). Please contact your administrator.');
                }
            }
        }
        
        return $next($request);
    }
}
