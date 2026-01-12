{{-- Admin Dashboard Content Partial --}}

<!-- Stats Overview -->
<div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="text-2xl font-bold text-indigo-600">{{ $stats['total_projects'] }}</div>
        <div class="text-sm text-gray-500">Total Projects</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="text-2xl font-bold text-green-600">{{ $stats['active_projects'] }}</div>
        <div class="text-sm text-gray-500">Active Projects</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="text-2xl font-bold text-blue-600">{{ $stats['total_tasks'] }}</div>
        <div class="text-sm text-gray-500">Total Tasks</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="text-2xl font-bold text-emerald-600">{{ $stats['completed_tasks'] }}</div>
        <div class="text-sm text-gray-500">Completed</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="text-2xl font-bold text-purple-600">{{ $stats['total_members'] }}</div>
        <div class="text-sm text-gray-500">Team Members</div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="text-2xl font-bold text-orange-600">{{ $stats['total_time_week'] }}</div>
        <div class="text-sm text-gray-500">Time This Week</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- All Projects -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Projects</h2>
        </div>
        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
            @forelse($allProjects as $project)
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
                                <p class="text-xs text-gray-500">
                                    {{ $project->completed_tasks_count ?? 0 }}/{{ $project->tasks_count }} tasks completed
                                </p>
                            </div>
                        </div>
                        @if($project->tasks_count > 0)
                            <div class="w-24">
                                <div class="flex items-center">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full" 
                                             style="width: {{ ($project->completed_tasks_count / $project->tasks_count) * 100 }}%"></div>
                                    </div>
                                    <span class="ml-2 text-xs text-gray-500">
                                        {{ round(($project->completed_tasks_count / $project->tasks_count) * 100) }}%
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-gray-500 text-sm">
                    No projects.
                </div>
            @endforelse
        </div>
    </div>

    <!-- All Members -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All Members</h2>
        </div>
        <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
            @foreach($allMembers as $member)
                <div class="px-6 py-3 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="h-8 w-8 rounded-full flex items-center justify-center text-white text-sm font-medium
                            {{ $member->pivot->role === 'owner' ? 'bg-purple-500' : '' }}
                            {{ $member->pivot->role === 'admin' ? 'bg-indigo-500' : '' }}
                            {{ $member->pivot->role === 'member' ? 'bg-blue-500' : '' }}
                            {{ $member->pivot->role === 'guest' ? 'bg-gray-500' : '' }}">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                            <p class="text-xs text-gray-500">{{ $member->email }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                            {{ $member->pivot->role === 'owner' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $member->pivot->role === 'admin' ? 'bg-indigo-100 text-indigo-800' : '' }}
                            {{ $member->pivot->role === 'member' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $member->pivot->role === 'guest' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucfirst($member->pivot->role) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

