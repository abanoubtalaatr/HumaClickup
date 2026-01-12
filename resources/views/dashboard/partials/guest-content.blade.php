{{-- Guest Dashboard Content Partial --}}

<!-- Estimation Polling Section -->
@if(isset($estimationPollingTasks) && $estimationPollingTasks->count() > 0)
<div class="mb-8 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-amber-100 rounded-lg">
                <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-amber-900">Task Estimation Polling</h2>
                <p class="text-sm text-amber-700">Tasks pending time estimates</p>
            </div>
        </div>
        <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-1 rounded-full">
            {{ $estimationPollingTasks->where('has_estimated', false)->count() }} pending
        </span>
    </div>

    <div class="space-y-3">
        @foreach($estimationPollingTasks as $item)
            <div class="bg-white rounded-lg border border-amber-100 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <h3 class="font-medium text-gray-900">{{ $item['task']->title }}</h3>
                            @if($item['has_estimated'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Submitted
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                    Awaiting estimate
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 mt-1">{{ $item['task']->project?->name }}</p>
                        
                        <!-- Progress indicator -->
                        <div class="mt-2 flex items-center space-x-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-xs">
                                <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $item['progress']['percentage'] }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500">{{ $item['progress']['submitted'] }}/{{ $item['progress']['total'] }} submitted</span>
                        </div>
                    </div>

                    <div class="ml-4">
                        @if($item['has_estimated'])
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Their estimate</p>
                                <p class="text-lg font-semibold text-green-600">{{ $item['my_estimation']->getFormattedEstimation() }}</p>
                            </div>
                        @else
                            <span class="text-sm text-amber-600">Not submitted yet</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- Time Tracking Summary Cards -->
<div class="mb-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Time Summary</h2>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-5 text-white">
            <div class="text-sm opacity-80">Today</div>
            <div class="text-2xl font-bold mt-1">{{ $timeSummaries['today']['total_formatted'] }}</div>
        </div>
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg p-5 text-white">
            <div class="text-sm opacity-80">This Week</div>
            <div class="text-2xl font-bold mt-1">{{ $timeSummaries['this_week']['total_formatted'] }}</div>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-5 text-white">
            <div class="text-sm opacity-80">Last 2 Weeks</div>
            <div class="text-2xl font-bold mt-1">{{ $timeSummaries['two_weeks']['total_formatted'] }}</div>
        </div>
        <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl shadow-lg p-5 text-white">
            <div class="text-sm opacity-80">Last 3 Weeks</div>
            <div class="text-2xl font-bold mt-1">{{ $timeSummaries['three_weeks']['total_formatted'] }}</div>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-5 text-white">
            <div class="text-sm opacity-80">Last Month</div>
            <div class="text-2xl font-bold mt-1">{{ $timeSummaries['four_weeks']['total_formatted'] }}</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Pending Tasks -->
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Pending Tasks</h2>
            <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                {{ $pendingTasks->count() }} tasks
            </span>
        </div>
        <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
            @forelse($pendingTasks as $task)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="flex-shrink-0 w-3 h-3 rounded-full" style="background-color: {{ $task->status?->color ?? '#94a3b8' }}"></span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                <p class="text-xs text-gray-500">{{ $task->project?->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($task->due_date)
                                <span class="text-xs {{ $task->due_date->isPast() ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                    {{ $task->due_date->format('M d') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500 text-sm">
                    No pending tasks.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Assigned Projects -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Assigned Projects</h2>
        </div>
        <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
            @forelse($assignedProjects as $project)
                <div class="px-6 py-4">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center" 
                             style="background-color: {{ $project->color ?? '#6366f1' }}20">
                            <span class="text-lg" style="color: {{ $project->color ?? '#6366f1' }}">
                                {{ $project->icon ?? '📁' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $project->name }}</p>
                            <p class="text-xs text-gray-500">{{ $project->tasks_count }} assigned tasks</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500 text-sm">
                    No projects assigned.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Recent Time Entries -->
<div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Recent Time Entries</h2>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse($recentTimeEntries as $entry)
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $entry->task?->title ?? 'No task' }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $entry->task?->project?->name }} • {{ $entry->description ?? 'No description' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">{{ $entry->getFormattedDuration() }}</p>
                        <p class="text-xs text-gray-500">{{ $entry->start_time->format('M d, H:i') }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-12 text-center text-gray-500 text-sm">
                No time entries.
            </div>
        @endforelse
    </div>
</div>

