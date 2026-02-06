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
    $statusText = $progressPercentage >= 100 ? '🎉 Completed!' : 
                  ($progressPercentage >= 75 ? '🚀 Almost There' : 
                  ($progressPercentage >= 50 ? '💪 Great Progress' : 
                  ($progressPercentage >= 25 ? '⚡ Keep Going' : '🌱 Getting Started')));
@endphp

<div style="background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 50%, #2563eb 100%);" class="shadow-2xl border-b-4 border-purple-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-4">
            <div class="flex items-center justify-between">
                <!-- Left: Progress Display -->
                <div class="flex items-center space-x-6">
                    <!-- Circular Progress Ring -->
                    <div class="relative w-20 h-20 flex-shrink-0">
                        <!-- Background circle -->
                        <svg class="w-20 h-20 transform -rotate-90">
                            <circle 
                                cx="40" 
                                cy="40" 
                                r="34" 
                                stroke="rgba(255,255,255,0.15)" 
                                stroke-width="6" 
                                fill="none" 
                            />
                            <!-- Progress circle with glow -->
                            <circle 
                                cx="40" 
                                cy="40" 
                                r="34" 
                                stroke="url(#progressGradient)" 
                                stroke-width="6" 
                                fill="none"
                                stroke-dasharray="{{ 2 * 3.14159 * 34 }}"
                                stroke-dashoffset="{{ 2 * 3.14159 * 34 * (1 - $progressPercentage / 100) }}"
                                stroke-linecap="round"
                                class="transition-all duration-1000 ease-out drop-shadow-lg"
                                style="filter: drop-shadow(0 0 8px rgba(74, 222, 128, 0.6));"
                            />
                            <defs>
                                <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#22c55e;stop-opacity:1" />
                                    <stop offset="50%" style="stop-color:#4ade80;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#86efac;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <!-- Percentage text -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xl font-black text-white drop-shadow-lg">{{ number_format($progressPercentage, 0) }}%</span>
                        </div>
                    </div>
                    
                    <!-- Progress Details -->
                    <div class="flex-1 min-w-0">
                        <!-- Title and Status -->
                        <div class="flex items-center space-x-3 mb-2">
                            <h3 class="text-xl font-black text-white drop-shadow-md">Program Progress</h3>
                            <span class="text-sm font-bold text-yellow-300 bg-yellow-400 bg-opacity-20 px-3 py-1 rounded-full backdrop-blur-sm border border-yellow-300 border-opacity-30">
                                {{ $statusText }}
                            </span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="flex items-center space-x-3">
                            <div class="flex-1 bg-white bg-opacity-20 backdrop-blur-sm rounded-full h-4 shadow-inner border-2 border-white border-opacity-30 overflow-hidden">
                                <div class="relative h-full">
                                    <!-- Animated gradient progress -->
                                    <div class="absolute inset-0 rounded-full transition-all duration-1000 ease-out"
                                         style="width: {{ $progressPercentage > 0 ? $progressPercentage : 2 }}%; 
                                                background: linear-gradient(90deg, #4ade80 0%, #22c55e 50%, #16a34a 100%);
                                                box-shadow: 0 0 20px rgba(74, 222, 128, 0.7), inset 0 2px 4px rgba(255,255,255,0.3);">
                                        <!-- Shine effect -->
                                        <div class="absolute inset-0 opacity-30 animate-shimmer" style="background: linear-gradient(90deg, transparent 0%, white 50%, transparent 100%);"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hours Badge -->
                            <div class="flex items-center space-x-2 bg-white bg-opacity-20 backdrop-blur-md px-4 py-2 rounded-xl border-2 border-white border-opacity-30 shadow-lg">
                                <svg class="h-5 w-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                {{-- <div class="flex items-baseline space-x-1"> --}}
                                    {{-- <span class="text-lg font-black text-white">{{ number_format($totalCompletedHours, 1) }}</span> --}}
                                    {{-- <span class="text-sm font-bold text-white opacity-80">/</span> --}}
                                    {{-- <span class="text-lg font-black text-white">{{ $targetHours }}</span> --}}
                                    {{-- <span class="text-sm font-bold text-yellow-300">hours</span> --}}
                                {{-- </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right: Action Button -->
                <a href="{{ route('guests.progress') }}" 
                   style="background: linear-gradient(90deg, #facc15 0%, #f59e0b 100%);"
                   class="flex-shrink-0 inline-flex items-center px-6 py-3 text-purple-900 rounded-xl text-base font-black shadow-xl hover:shadow-2xl transition-all transform hover:scale-105 hover:-translate-y-0.5 border-2 border-yellow-300">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    View Details
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.animate-shimmer {
    animation: shimmer 2s infinite;
}
</style>
