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
    
    $totalCompletedHours = (float) $allProgress->sum('completed_hours');
    $targetHours = 120; // 20 working days × 6 hours = 120 hours
    $progressPercentage = $targetHours > 0 ? min(($totalCompletedHours / $targetHours) * 100, 100) : 0;
    
    // Status text based on progress
    $statusText = $progressPercentage >= 100 ? 'Completed!' : 
                  ($progressPercentage >= 75 ? 'Almost There' : 
                  ($progressPercentage >= 50 ? 'Great Progress' : 
                  ($progressPercentage >= 25 ? 'Keep Going' : 'Getting Started')));
    
    $statusColor = $progressPercentage >= 100 ? 'text-emerald-300' : 
                   ($progressPercentage >= 75 ? 'text-teal-200' : 
                   ($progressPercentage >= 50 ? 'text-cyan-200' : 'text-green-200'));
@endphp

<div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 text-white shadow-lg border-b-4 border-emerald-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-2.5">
            <div class="flex items-center justify-between">
                <!-- Left: Compact Progress Display -->
                <div class="flex items-center space-x-5">
                    <!-- Circular Progress -->
                    <div class="relative w-14 h-14">
                        <svg class="transform -rotate-90 w-14 h-14">
                            <circle cx="28" cy="28" r="24" stroke="rgba(255,255,255,0.2)" stroke-width="4" fill="none" />
                            <circle cx="28" cy="28" r="24" 
                                    stroke="white" 
                                    stroke-width="4" 
                                    fill="none"
                                    stroke-dasharray="{{ 2 * 3.14159 * 24 }}"
                                    stroke-dashoffset="{{ 2 * 3.14159 * 24 * (1 - $progressPercentage / 100) }}"
                                    stroke-linecap="round"
                                    class="transition-all duration-500" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-bold">{{ number_format($progressPercentage, 0) }}%</span>
                        </div>
                    </div>
                    
                    <!-- Progress Info -->
                    <div class="flex-1">
                        <div class="flex items-baseline space-x-2 mb-1">
                            <h3 class="text-base font-bold">Program Progress</h3>
                            <span class="text-xs {{ $statusColor }} font-semibold">{{ $statusText }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <!-- Progress Bar -->
                            <div class="w-64 bg-white bg-opacity-25 rounded-full h-2.5 shadow-inner">
                                <div class="bg-gradient-to-r from-yellow-300 via-lime-400 to-green-400 h-2.5 rounded-full transition-all duration-700 ease-out shadow-md" 
                                     style="width: {{ $progressPercentage > 0 ? $progressPercentage : 1 }}%"></div>
                            </div>
                            <!-- Hours Badge -->
                            <div class="inline-flex items-center px-3 py-1.5 bg-white bg-opacity-25 rounded-full shadow-sm">
                                <svg class="h-4 w-4 mr-1.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-bold text-white">{{ number_format($totalCompletedHours, 1) }}h</span>
                                <span class="text-sm font-medium text-white opacity-90 mx-1">/</span>
                                <span class="text-sm font-bold text-white">{{ $targetHours }}h</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right: Action Button -->
                <a href="{{ route('guests.progress') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white text-emerald-600 rounded-lg text-sm font-semibold hover:bg-opacity-90 hover:shadow-lg transition-all transform hover:scale-105">
                    <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Details
                </a>
            </div>
        </div>
    </div>
</div>
