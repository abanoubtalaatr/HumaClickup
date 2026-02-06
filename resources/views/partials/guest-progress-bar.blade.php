@php
    // Calculate 20-day program progress (4 weeks × 5 days)
    $user = auth()->user();
    $workspaceId = session('current_workspace_id');
    
    // Get all projects this guest is assigned to
    $guestProjects = \App\Models\Project::where('workspace_id', $workspaceId)
        ->whereHas('projectMembers', function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->where('role', 'guest');
        })
        ->get();
    
    // Calculate from first project start date (or today if no projects)
    $programStartDate = $guestProjects->min('start_date') 
        ? \Carbon\Carbon::parse($guestProjects->min('start_date'))
        : now()->subDays(20);
    
    $programEndDate = $programStartDate->copy()->addWeeks(4); // 4 weeks
    
    // Get all daily progress in this range
    $allProgress = \App\Models\DailyProgress::where('user_id', $user->id)
        ->whereBetween('date', [$programStartDate, $programEndDate])
        ->get();
    
    $totalCompletedHours = $allProgress->sum('completed_hours');
    $targetHours = 20 * 6; // 20 working days × 6 hours = 120 hours
    $progressPercentage = min(($totalCompletedHours / $targetHours) * 100, 100);
    
    // Count working days completed
    $daysCompleted = $allProgress->where('progress_percentage', '>=', 100)->count();
    $totalWorkingDays = 20; // 4 weeks × 5 days
@endphp

<div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-3">
            <div class="flex items-center justify-between">
                <!-- Left: Program Progress -->
                <div class="flex items-center space-x-6">
                    <div>
                        <p class="text-xs text-indigo-100 mb-0.5">Program Progress (20 Days)</p>
                        <div class="flex items-center space-x-3">
                            <div class="w-48 bg-indigo-800 bg-opacity-40 rounded-full h-2.5">
                                <div class="bg-white h-2.5 rounded-full transition-all duration-500 shadow-sm" 
                                     style="width: {{ $progressPercentage }}%"></div>
                            </div>
                            <span class="text-sm font-bold">{{ number_format($progressPercentage, 0) }}%</span>
                        </div>
                    </div>
                    
                    <!-- Hours Stat -->
                    <div class="border-l border-indigo-400 pl-6">
                        <p class="text-xs text-indigo-100">Total Hours</p>
                        <p class="text-lg font-bold">{{ number_format($totalCompletedHours, 1) }}<span class="text-sm font-normal text-indigo-200">/{{ $targetHours }}h</span></p>
                    </div>
                    
                    <!-- Days Stat -->
                    <div class="border-l border-indigo-400 pl-6">
                        <p class="text-xs text-indigo-100">Days Completed</p>
                        <p class="text-lg font-bold">{{ $daysCompleted }}<span class="text-sm font-normal text-indigo-200">/{{ $totalWorkingDays }}</span></p>
                    </div>
                </div>
                
                <!-- Right: Quick Link -->
                <a href="{{ route('guests.progress') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg text-sm font-medium transition-all">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    View Details
                </a>
            </div>
        </div>
    </div>
</div>
