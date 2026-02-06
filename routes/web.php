<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GuestReportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\TimeTrackingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\TaskEstimationController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\DailyStatusController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MentorDashboardController;
use App\Http\Controllers\GuestProgressController;
use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\TesterAssignmentController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes (Laravel Breeze/Fortify will add these, but we'll include basic ones)
Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (\Illuminate\Support\Facades\Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
})->middleware('guest');

// Registration routes
Route::get('/register', function () {
    return view('auth.register');
})->name('register')->middleware('guest');

Route::post('/register', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = \App\Models\User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => bcrypt($validated['password']),
        'email_verified_at' => now(),
        'timezone' => 'UTC',
        'locale' => 'en',
    ]);

    \Illuminate\Support\Facades\Auth::login($user);

    // Check if user has any workspace invitations
    if ($user->workspaces()->count() > 0) {
        $workspace = $user->workspaces()->first();
        session(['current_workspace_id' => $workspace->id]);
        return redirect()->route('dashboard')
            ->with('success', 'Welcome! You have been added to a workspace.');
    }

    // No workspaces - show waiting page
    return redirect()->route('no-workspace')
        ->with('success', 'Account created! Please wait to be invited to a workspace.');
})->middleware('guest');

// No workspace page for users without any workspace access
Route::get('/no-workspace', function () {
    if (auth()->user()->workspaces()->count() > 0) {
        $workspace = auth()->user()->workspaces()->first();
        session(['current_workspace_id' => $workspace->id]);
        return redirect()->route('dashboard');
    }
    return view('auth.no-workspace');
})->name('no-workspace')->middleware('auth');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Workspace routes
    Route::prefix('workspaces')->name('workspaces.')->group(function () {
        Route::get('/', [WorkspaceController::class, 'index'])->name('index');
        Route::get('/create', [WorkspaceController::class, 'create'])->name('create');
        Route::post('/', [WorkspaceController::class, 'store'])->name('store');
        Route::get('/{workspace}', [WorkspaceController::class, 'show'])->name('show');
        Route::get('/{workspace}/edit', [WorkspaceController::class, 'edit'])->name('edit');
        Route::put('/{workspace}', [WorkspaceController::class, 'update'])->name('update');
        Route::delete('/{workspace}', [WorkspaceController::class, 'destroy'])->name('destroy');
        Route::post('/{workspace}/switch', [WorkspaceController::class, 'switch'])->name('switch');
        
        // Member management routes
        Route::get('/{workspace}/members', [WorkspaceController::class, 'members'])->name('members');
        Route::post('/{workspace}/members/create', [WorkspaceController::class, 'createMember'])->name('members.create');
        Route::post('/{workspace}/members/invite', [WorkspaceController::class, 'inviteMember'])->name('members.invite');
        Route::post('/{workspace}/members/assign-guests', [WorkspaceController::class, 'assignGuestsToMember'])->name('members.assign-guests');
        Route::put('/{workspace}/members/{user}', [WorkspaceController::class, 'updateMemberRole'])->name('members.update');
        Route::delete('/{workspace}/members/{user}', [WorkspaceController::class, 'removeMember'])->name('members.remove');
        Route::get('/{workspace}/members/{user}/tasks', [WorkspaceController::class, 'getMemberTasks'])->name('members.tasks');
        
        // Track management routes (admin only)
        Route::get('/{workspace}/tracks', [TrackController::class, 'index'])->name('tracks.index');
        Route::get('/{workspace}/tracks/create', [TrackController::class, 'create'])->name('tracks.create');
        Route::post('/{workspace}/tracks', [TrackController::class, 'store'])->name('tracks.store');
        Route::get('/{workspace}/tracks/{track}/edit', [TrackController::class, 'edit'])->name('tracks.edit');
        Route::put('/{workspace}/tracks/{track}', [TrackController::class, 'update'])->name('tracks.update');
        Route::delete('/{workspace}/tracks/{track}', [TrackController::class, 'destroy'])->name('tracks.destroy');
        Route::get('/{workspace}/tracks/list', [TrackController::class, 'list'])->name('tracks.list');
    });
    
    // Project routes (within workspace context)
    Route::middleware(['workspace.access'])->prefix('workspaces/{workspace}')->group(function () {
        // Bind project to workspace scope for workspace routes
        Route::bind('project', function ($value, $route) {
            $workspaceId = $route->parameter('workspace')->id ?? $route->parameter('workspace');
            return \App\Models\Project::where('id', $value)
                ->where('workspace_id', $workspaceId)
                ->firstOrFail();
        });
        
        // Bind sprint to workspace scope
        Route::bind('sprint', function ($value, $route) {
            $workspaceId = $route->parameter('workspace')->id ?? $route->parameter('workspace');
            return \App\Models\Sprint::where('id', $value)
                ->where('workspace_id', $workspaceId)
                ->firstOrFail();
        });
        
        Route::resource('projects', ProjectController::class)->names([
            'index' => 'workspace.projects.index',
            'create' => 'workspace.projects.create',
            'store' => 'workspace.projects.store',
            'show' => 'workspace.projects.show',
            'edit' => 'workspace.projects.edit',
            'update' => 'workspace.projects.update',
            'destroy' => 'workspace.projects.destroy',
        ]);

        // Sprint routes
        Route::resource('sprints', SprintController::class);
        Route::post('sprints/{sprint}/start', [SprintController::class, 'start'])->name('sprints.start');
        Route::post('sprints/{sprint}/complete', [SprintController::class, 'complete'])->name('sprints.complete');
       
        
        // Task routes (within project context)
        Route::prefix('projects/{project}')->group(function () {
            // Bind task to workspace scope
            Route::bind('task', function ($value, $route) {
                $workspaceId = $route->parameter('workspace')->id ?? $route->parameter('workspace');
                return \App\Models\Task::where('id', $value)
                    ->where('workspace_id', $workspaceId)
                    ->firstOrFail();
            });
         
            Route::get('/tasks/kanban', [TaskController::class, 'kanban'])->name('project.tasks.kanban');
            Route::get('/tasks/list', [TaskController::class, 'list'])->name('project.tasks.list');
            Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('workspace.project.tasks.updateStatus');
            Route::post('/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
            Route::resource('tasks', TaskController::class)->names([
                'index' => 'project.tasks.index',
                'create' => 'project.tasks.create',
                'store' => 'project.tasks.store',
                'show' => 'project.tasks.show',
                'edit' => 'project.tasks.edit',
                'update' => 'project.tasks.update',
                'destroy' => 'project.tasks.destroy',
            ]);
        });
     
        
        // Time tracking routes (workspace-scoped)
        Route::prefix('time-tracking')->name('workspace.time-tracking.')->group(function () {
            Route::get('/', [TimeTrackingController::class, 'index'])->name('index');
            Route::post('/start', [TimeTrackingController::class, 'start'])->name('start');
            Route::post('/stop', [TimeTrackingController::class, 'stop'])->name('stop');
            Route::post('/manual', [TimeTrackingController::class, 'createManual'])->name('manual');
        });
    });
    
    // Profile routes
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    
    // Standalone routes (for when workspace is in session)
    Route::middleware(['workspace.access'])->group(function () {
        // Bind project to workspace scope
        Route::bind('project', function ($value) {
            $workspaceId = session('current_workspace_id');
            return \App\Models\Project::where('id', $value)
                ->where('workspace_id', $workspaceId)
                ->firstOrFail();
        });
        
        // Bind task to workspace scope (include tasks with null workspace_id so they can be moved on kanban)
        Route::bind('task', function ($value) {
            $workspaceId = session('current_workspace_id');
            return \App\Models\Task::withoutGlobalScopes()
                ->where('id', $value)
                ->where(function ($q) use ($workspaceId) {
                    $q->where('workspace_id', $workspaceId)->orWhereNull('workspace_id');
                })
                ->firstOrFail();
        });
        
        // Bind topic to workspace scope
        Route::bind('topic', function ($value) {
            $workspaceId = session('current_workspace_id');
            return \App\Models\Topic::where('id', $value)
                ->where('workspace_id', $workspaceId)
                ->firstOrFail();
        });
        
        // Bind daily_status to workspace scope
        Route::bind('daily_status', function ($value) {
            $workspaceId = session('current_workspace_id');
            return \App\Models\DailyStatus::where('id', $value)
                ->where('workspace_id', $workspaceId)
                ->firstOrFail();
        });
        
        // Bind time_entry to workspace scope
        Route::bind('time_entry', function ($value) {
            $workspaceId = session('current_workspace_id');
            return \App\Models\TimeEntry::where('id', $value)
                ->where('workspace_id', $workspaceId)
                ->firstOrFail();
        });
        
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::post('/projects/with-tasks', [ProjectController::class, 'storeWithTasks'])->name('projects.store-with-tasks');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::get('/projects/{project}/assign-testers', [TesterAssignmentController::class, 'create'])->name('projects.assign-testers');
        Route::post('/projects/{project}/assign-testers', [TesterAssignmentController::class, 'store'])->name('projects.store-testers');
        
        Route::get('/tasks', [TaskController::class, 'kanban'])->name('tasks.index');
        Route::get('/tasks/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');
        Route::get('/tasks/list', [TaskController::class, 'list'])->name('tasks.list');
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
        Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('tasks.comments.store');
        
        // Bugs routes
        Route::get('/bugs', [TaskController::class, 'bugs'])->name('bugs.index');
        
        // Topics routes
        Route::resource('topics', TopicController::class);
        Route::post('/topics/{topic}/toggle-complete', [TopicController::class, 'toggleComplete'])->name('topics.toggle-complete');
        
        // Daily Statuses routes
        Route::resource('daily-statuses', DailyStatusController::class);
        
        // API endpoint for getting assignable users (used in task creation)
        Route::get('/api/assignable-users', [TaskController::class, 'getAssignableUsers'])->name('api.assignable-users');

        // Tags (members and admins can create tags)
        Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
        
        // Standalone project-scoped task routes (uses session workspace)
        Route::prefix('projects/{project}')->group(function () {
            Route::get('/tasks/kanban', [TaskController::class, 'kanban'])->name('projects.tasks.kanban');
            Route::get('/tasks/list', [TaskController::class, 'index'])->name('projects.tasks.index');
            Route::get('/tasks/create', [TaskController::class, 'create'])->name('projects.tasks.create');
            Route::post('/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
            // Use explicit task resolution to avoid binding conflicts
            Route::get('/tasks/{taskId}', function ($project, $taskId) {
                $workspaceId = session('current_workspace_id');
                $task = \App\Models\Task::where('id', $taskId)
                    ->where('project_id', $project->id)
                    ->where('workspace_id', $workspaceId)
                    ->firstOrFail();
                return app(TaskController::class)->show(request(), $task, $project);
            })->name('projects.tasks.show');
            Route::get('/tasks/{taskId}/edit', function ($project, $taskId) {
                $workspaceId = session('current_workspace_id');
                $task = \App\Models\Task::where('id', $taskId)
                    ->where('project_id', $project->id)
                    ->where('workspace_id', $workspaceId)
                    ->firstOrFail();
                return app(TaskController::class)->edit(request(), $task, $project);
            })->name('projects.tasks.edit');
            Route::put('/tasks/{taskId}', function ($project, $taskId) {
                $workspaceId = session('current_workspace_id');
                $task = \App\Models\Task::where('id', $taskId)
                    ->where('project_id', $project->id)
                    ->where('workspace_id', $workspaceId)
                    ->firstOrFail();
                return app(TaskController::class)->update(request(), $task, $project);
            })->name('projects.tasks.update');
            Route::delete('/tasks/{taskId}', function ($project, $taskId) {
                $workspaceId = session('current_workspace_id');
                $task = \App\Models\Task::where('id', $taskId)
                    ->where('project_id', $project->id)
                    ->where('workspace_id', $workspaceId)
                    ->firstOrFail();
                return app(TaskController::class)->destroy($task, $project);
            })->name('projects.tasks.destroy');
            Route::post('/tasks/{taskId}/status', function ($project, $taskId) {
                $workspaceId = session('current_workspace_id');
                $task = \App\Models\Task::where('id', $taskId)
                    ->where('project_id', $project->id)
                    ->where('workspace_id', $workspaceId)
                    ->firstOrFail();
                return app(TaskController::class)->updateStatus(request(), $task, $project);
            })->name('projects.tasks.updateStatus');
        });
        
        // Time tracking routes (standalone - uses workspace from session)
        Route::prefix('time-tracking')->name('time-tracking.')->group(function () {
            Route::get('/', [TimeTrackingController::class, 'index'])->name('index');
            Route::post('/start', [TimeTrackingController::class, 'start'])->name('start');
            Route::post('/stop', [TimeTrackingController::class, 'stop'])->name('stop');
            Route::post('/manual', [TimeTrackingController::class, 'createManual'])->name('manual');
            Route::get('/tasks', [TimeTrackingController::class, 'getTasksForTracking'])->name('tasks');
            Route::get('/entries/{time_entry}/edit', [TimeTrackingController::class, 'edit'])->name('entries.edit');
            Route::put('/entries/{time_entry}', [TimeTrackingController::class, 'update'])->name('entries.update');
        });
        
        // Estimation Polling routes
        Route::prefix('estimations')->name('estimations.')->group(function () {
            // Guest routes
            Route::get('/pending', [TaskEstimationController::class, 'getPendingTasks'])->name('pending');
            Route::post('/tasks/{task}/submit', [TaskEstimationController::class, 'submit'])->name('submit');
            
            // Member/Admin routes
            Route::get('/overview', [TaskEstimationController::class, 'getPollingOverview'])->name('overview');
            Route::get('/tasks/{task}/status', [TaskEstimationController::class, 'getPollingStatus'])->name('status');
            Route::put('/tasks/{task}/final', [TaskEstimationController::class, 'updateFinalEstimation'])->name('update-final');
        });
        
        // Group management routes
        Route::resource('groups', GroupController::class);
        
        // Guest Report routes
        Route::get('/reports', [GuestReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/create', [GuestReportController::class, 'create'])->name('reports.create');
        Route::post('/reports', [GuestReportController::class, 'store'])->name('reports.store');
        Route::get('/reports/{report}', [GuestReportController::class, 'show'])->name('reports.show');
        
        // Debug route - remove after checking
        Route::get('/debug-attendance', function() {
            $workspaceId = session('current_workspace_id');
            $workspace = \App\Models\Workspace::find($workspaceId);
            $guests = $workspace->users()->wherePivot('role', 'guest')->withPivot('attendance_days')->get();
            $today = strtolower(now()->format('l'));
            
            $debug = [
                'today' => $today,
                'guests' => []
            ];
            
            foreach ($guests as $guest) {
                $days = $guest->pivot->attendance_days;
                $decoded = is_string($days) ? json_decode($days, true) : $days;
                
                $debug['guests'][] = [
                    'name' => $guest->name,
                    'attendance_days_raw' => $days,
                    'attendance_days_decoded' => $decoded,
                    'should_attend_today' => in_array($today, $decoded ?? []),
                ];
            }
            
            return response()->json($debug);
        });
        
        // Attendance routes
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/checkin', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
        Route::post('/attendance/checkout', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');
        Route::post('/attendance/toggle/{guest}', [AttendanceController::class, 'toggleAttendance'])->name('attendance.toggle');
        Route::post('/attendance/mark-absent/{guest}', [AttendanceController::class, 'markAbsent'])->name('attendance.mark-absent');
        Route::post('/attendance/unsuspend/{guest}', [AttendanceController::class, 'unsuspend'])->name('attendance.unsuspend');
        
        // Dashboard as user route (Admin feature to view member dashboards)
        Route::get('/dashboard/as/{targetUser}', [DashboardController::class, 'viewAsUser'])->name('dashboard.as-user');
        Route::post('/dashboard/stop-impersonating', [DashboardController::class, 'stopImpersonating'])->name('dashboard.stop-impersonating');
        
        // ============================================
        // STUDENT TRAINING SYSTEM ROUTES
        // ============================================
        
        // Mentor Dashboard Routes
        Route::prefix('mentor')->name('mentor.')->group(function () {
            Route::get('/dashboard', [MentorDashboardController::class, 'index'])->name('dashboard');
            Route::post('/approve-progress/{progress}', [MentorDashboardController::class, 'approveProgress'])->name('approve-progress');
            Route::post('/approve-attendance/{attendance}', [MentorDashboardController::class, 'approveAttendance'])->name('approve-attendance');
            Route::post('/bulk-approve-progress', [MentorDashboardController::class, 'bulkApproveProgress'])->name('bulk-approve-progress');
            Route::post('/bulk-approve-attendance', [MentorDashboardController::class, 'bulkApproveAttendance'])->name('bulk-approve-attendance');
            Route::get('/projects/{project}/guests/{userId}/progress', [MentorDashboardController::class, 'showGuestProgress'])->name('guest-progress');
        });
        
        // Guest Progress Routes
        Route::prefix('guests')->name('guests.')->group(function () {
            Route::get('/progress', [GuestProgressController::class, 'index'])->name('progress');
            Route::get('/projects/{project}/progress', [GuestProgressController::class, 'show'])->name('project-progress');
            Route::get('/projects/{project}/calendar', [GuestProgressController::class, 'calendar'])->name('calendar');
        });
        
        // Owner Dashboard Routes
        Route::prefix('owner')->name('owner.')->group(function () {
            Route::get('/overview', [OwnerDashboardController::class, 'index'])->name('overview');
            Route::get('/projects/{project}/details', [OwnerDashboardController::class, 'showProject'])->name('project-details');
            Route::get('/guests-without-tasks', [OwnerDashboardController::class, 'guestsWithoutTasks'])->name('guests-without-tasks');
        });
        
        // Notification Routes
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.api');
        Route::get('/notifications/all', [NotificationController::class, 'all'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    });
});
