{{-- Member Dashboard Content Partial --}}

<!-- Estimation Polling Overview -->
@if(isset($estimationPollingTasks) && $estimationPollingTasks->count() > 0)
<div class="mb-6 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-amber-100 rounded-lg">
                <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-amber-900">Estimation Polling</h2>
                <p class="text-sm text-amber-700">Team task time estimation overview</p>
            </div>
        </div>
    </div>

    <div class="space-y-3">
        @foreach($estimationPollingTasks->take(3) as $item)
            <div class="bg-white rounded-lg border border-amber-100 p-4 shadow-sm">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <span class="font-medium text-gray-900">{{ $item['task']->title }}</span>
                            @if($item['task']->estimation_status === 'completed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Completed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                    Polling
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 mt-1">{{ $item['task']->project?->name }}</p>
                        
                        <!-- Progress -->
                        <div class="mt-2 flex items-center space-x-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-xs">
                                <div class="h-2 rounded-full {{ $item['progress']['is_complete'] ? 'bg-green-500' : 'bg-amber-500' }}" 
                                     style="width: {{ $item['progress']['percentage'] }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500">{{ $item['progress']['submitted'] }}/{{ $item['progress']['total'] }} submitted</span>
                        </div>

                        <!-- Estimations list -->
                        @if($item['estimations']->count() > 0)
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach($item['estimations'] as $est)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700">
                                        {{ $est['user_name'] }}: <span class="font-medium ml-1">{{ $est['formatted'] }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="ml-4 text-right">
                        @if($item['task']->estimation_status === 'completed')
                            <p class="text-sm text-gray-500">Final</p>
                            <p class="text-lg font-bold text-green-600">{{ $item['task']->getFormattedEstimation() }}</p>
                        @else
                            <p class="text-sm text-gray-400">Awaiting</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

<!-- My Time This Week -->
<div class="mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-indigo-100">Time This Week</p>
            <p class="text-3xl font-bold mt-1">{{ $myTimeSummary['total_formatted'] }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Team Time Overview -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Team Time This Week</h2>
        </div>
        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
            @foreach($teamTimeTracking as $member)
                <div class="px-6 py-3 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="h-8 w-8 rounded-full flex items-center justify-center text-white text-sm font-medium
                            {{ $member['role'] === 'owner' ? 'bg-purple-500' : '' }}
                            {{ $member['role'] === 'admin' ? 'bg-indigo-500' : '' }}
                            {{ $member['role'] === 'member' ? 'bg-blue-500' : '' }}
                            {{ $member['role'] === 'guest' ? 'bg-gray-500' : '' }}">
                            {{ strtoupper(substr($member['user']->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $member['user']->name }}</p>
                            @if($member['track'])
                                <span class="text-xs text-gray-500">{{ ucfirst(str_replace('_', '/', $member['track'])) }}</span>
                            @endif
                        </div>
                    </div>
                    <span class="text-sm font-medium text-gray-700">{{ $member['weekly_formatted'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- My Tasks -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Tasks</h2>
            <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                {{ $myTasks->count() }}
            </span>
        </div>
        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
            @forelse($myTasks as $task)
                <div class="px-6 py-3">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $task->title }}</p>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-500">{{ $task->project?->name }}</span>
                        @if($task->due_date)
                            <span class="text-xs {{ $task->due_date->isPast() ? 'text-red-600' : 'text-gray-400' }}">
                                {{ $task->due_date->format('M d') }}
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500 text-sm">
                    No pending tasks.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Guests -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Team Guests</h2>
        </div>
        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
            @forelse($guests as $guest)
                <div class="px-6 py-3 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="h-8 w-8 rounded-full bg-gray-500 flex items-center justify-center text-white text-sm font-medium">
                            {{ strtoupper(substr($guest->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $guest->name }}</p>
                            @if($guest->pivot->track)
                                <span class="text-xs text-green-600">{{ ucfirst(str_replace('_', '/', $guest->pivot->track)) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500 text-sm">
                    No guests.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Projects -->
<div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Projects</h2>
    </div>
    <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
        @forelse($myProjects as $project)
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center" 
                             style="background-color: {{ $project->color ?? '#6366f1' }}20">
                            <span class="text-lg" style="color: {{ $project->color ?? '#6366f1' }}">
                                {{ $project->icon ?? '📁' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $project->name }}</p>
                            <p class="text-xs text-gray-500">{{ $project->tasks_count }} tasks</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-8 text-center text-gray-500 text-sm">
                No projects.
            </div>
        @endforelse
    </div>
</div>

