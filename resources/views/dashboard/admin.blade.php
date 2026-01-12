@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                <p class="mt-1 text-gray-500">Complete workspace overview and analytics</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('workspaces.members', session('current_workspace_id')) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Manage Team
                </a>
                <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Project
                </a>
            </div>
        </div>

        <!-- Alerts Section -->
        @if($overdueProjects->count() > 0 || $overdueTasks->count() > 0 || $inactiveUsers->count() > 0 || $unassignedTasks->count() > 0)
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-3">
                @if($overdueProjects->count() > 0)
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-red-700">
                                <strong>{{ $overdueProjects->count() }} overdue project(s)</strong>
                            </p>
                        </div>
                    </div>
                @endif

                @if($projectsDueSoon->count() > 0)
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-yellow-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-yellow-700">
                                <strong>{{ $projectsDueSoon->count() }} project(s) due this week</strong>
                            </p>
                        </div>
                    </div>
                @endif

                @if($inactiveUsers->count() > 0)
                    <div class="bg-orange-50 border-l-4 border-orange-400 p-4 rounded-r-lg">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-orange-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-orange-700">
                                <strong>{{ $inactiveUsers->count() }} inactive user(s)</strong> (no time tracked in 7 days)
                            </p>
                        </div>
                    </div>
                @endif

                @if($unassignedTasks->count() > 0)
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-blue-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-blue-700">
                                <strong>{{ $unassignedTasks->count() }} unassigned task(s)</strong>
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Project Due Dates Details -->
        @if($overdueProjects->count() > 0 || $projectsDueSoon->count() > 0)
            <div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ showDetails: false }">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between cursor-pointer" @click="showDetails = !showDetails">
                    <div class="flex items-center space-x-3">
                        <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <h2 class="text-lg font-semibold text-gray-900">Project Due Dates</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $overdueProjects->count() + $projectsDueSoon->count() }} need attention
                        </span>
                    </div>
                    <svg class="h-5 w-5 text-gray-400 transition-transform" :class="{ 'rotate-180': showDetails }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                <div x-show="showDetails" x-cloak class="px-6 py-4">
                    @if($overdueProjects->count() > 0)
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold text-red-700 mb-3 flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Overdue Projects
                            </h3>
                            <div class="space-y-2">
                                @foreach($overdueProjects as $project)
                                    <a href="{{ route('projects.show', $project) }}" class="block p-3 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background-color: {{ $project->color ?? '#6366f1' }}20">
                                                    <span style="color: {{ $project->color ?? '#6366f1' }}">{{ $project->icon ?? '📁' }}</span>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $project->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $project->createdBy?->name ?? 'Unknown' }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-red-600">{{ $project->due_date->format('M d, Y') }}</p>
                                                <p class="text-xs text-red-500">{{ $project->due_date->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($projectsDueSoon->count() > 0)
                        <div>
                            <h3 class="text-sm font-semibold text-yellow-700 mb-3 flex items-center">
                                <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Due This Week
                            </h3>
                            <div class="space-y-2">
                                @foreach($projectsDueSoon as $project)
                                    <a href="{{ route('projects.show', $project) }}" class="block p-3 bg-yellow-50 border border-yellow-200 rounded-lg hover:bg-yellow-100 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3">
                                                <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="background-color: {{ $project->color ?? '#6366f1' }}20">
                                                    <span style="color: {{ $project->color ?? '#6366f1' }}">{{ $project->icon ?? '📁' }}</span>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $project->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $project->createdBy?->name ?? 'Unknown' }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-medium text-yellow-600">{{ $project->due_date->format('M d, Y') }}</p>
                                                <p class="text-xs text-yellow-600">{{ $project->due_date->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Estimation Polling Summary -->
        @if(($estimationSummary['polling'] ?? 0) > 0 || ($estimationSummary['completed'] ?? 0) > 0)
        <div class="mb-6 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6" x-data="{ expanded: false, editingTask: null, editHours: 0, editMinutes: 0 }">
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
                <div class="flex items-center space-x-3">
                    <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-1 rounded-full">
                        {{ $estimationSummary['polling'] }} in progress
                    </span>
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full">
                        {{ $estimationSummary['completed'] }} completed
                    </span>
                    @if(($estimationSummary['pending'] ?? 0) > 0)
                    <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-1 rounded-full">
                        {{ $estimationSummary['pending'] }} pending
                    </span>
                    @endif
                </div>
            </div>

            @if($estimationPollingTasks->count() > 0)
            <div class="space-y-3">
                @foreach($estimationPollingTasks->take(5) as $item)
                    <div class="bg-white rounded-lg border border-amber-100 p-4 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('tasks.show', $item['task']) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $item['task']->title }}
                                    </a>
                                    @if($item['task']->estimation_status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
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

                                <!-- Estimations + Who hasn't submitted -->
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($item['estimations'] as $est)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-50 text-green-700 border border-green-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $est['user_name'] }}: <span class="font-medium ml-1">{{ $est['formatted'] }}</span>
                                        </span>
                                    @endforeach
                                    @foreach($item['guest_assignees']->where('has_estimated', false) as $guest)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-700 border border-amber-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $guest['name'] }}: <span class="font-medium ml-1">Pending</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="ml-4 text-right">
                                @if($item['task']->estimation_status === 'completed')
                                    <div>
                                        <p class="text-sm text-gray-500">Final Estimate</p>
                                        <p class="text-xl font-bold text-green-600">{{ $item['task']->getFormattedEstimation() }}</p>
                                        @if($item['task']->estimation_edited_by)
                                            <p class="text-xs text-gray-400">Edited by {{ $item['task']->estimationEditedBy?->name }}</p>
                                        @endif
                                        <button @click="editingTask = {{ $item['task']->id }}; editHours = {{ floor($item['task']->estimated_minutes / 60) }}; editMinutes = {{ $item['task']->estimated_minutes % 60 }}"
                                                class="mt-1 text-xs text-indigo-600 hover:text-indigo-800">
                                            Edit Estimate
                                        </button>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-400">Awaiting</p>
                                    <p class="text-sm text-gray-500">{{ $item['progress']['remaining'] }} more</p>
                                @endif
                            </div>
                        </div>

                        <!-- Edit Estimation Inline Form -->
                        <div x-show="editingTask === {{ $item['task']->id }}" x-cloak class="mt-4 pt-4 border-t border-gray-200">
                            <form @submit.prevent="
                                fetch('/estimations/tasks/{{ $item['task']->id }}/final', {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ estimated_hours: editHours, estimated_minutes: editMinutes })
                                }).then(r => r.json()).then(data => {
                                    if (data.success) window.location.reload();
                                    else alert(data.message);
                                })
                            " class="flex items-end space-x-3">
                                <div>
                                    <label class="block text-xs text-gray-500">Hours</label>
                                    <input type="number" x-model="editHours" min="0" class="w-20 rounded border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500">Minutes</label>
                                    <input type="number" x-model="editMinutes" min="0" max="59" class="w-20 rounded border-gray-300 text-sm">
                                </div>
                                <button type="submit" class="px-3 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                    Save
                                </button>
                                <button type="button" @click="editingTask = null" class="px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
        @endif

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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Top Time Trackers Leaderboard -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-amber-50 to-yellow-50">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="h-5 w-5 text-amber-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Top Time Trackers
                    </h2>
                </div>
                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                    @forelse($topTimeTrackers as $index => $tracker)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                    {{ $index === 0 ? 'bg-gradient-to-br from-amber-400 to-yellow-500 text-white' : '' }}
                                    {{ $index === 1 ? 'bg-gradient-to-br from-gray-300 to-gray-400 text-white' : '' }}
                                    {{ $index === 2 ? 'bg-gradient-to-br from-orange-400 to-amber-500 text-white' : '' }}
                                    {{ $index > 2 ? 'bg-gray-100 text-gray-600' : '' }}">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $tracker['user']->name }}</p>
                                    <div class="flex items-center space-x-1">
                                        <span class="text-xs px-1.5 py-0.5 rounded 
                                            {{ $tracker['role'] === 'owner' ? 'bg-purple-100 text-purple-700' : '' }}
                                            {{ $tracker['role'] === 'admin' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                            {{ $tracker['role'] === 'member' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $tracker['role'] === 'guest' ? 'bg-gray-100 text-gray-700' : '' }}">
                                            {{ ucfirst($tracker['role']) }}
                                        </span>
                                        @if($tracker['track'])
                                            <span class="text-xs text-green-600">{{ ucfirst(str_replace('_', '/', $tracker['track'])) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-gray-900">{{ $tracker['total_formatted'] }}</span>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500 text-sm">
                            No time tracked this week yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Team Time Overview -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Team Time This Week</h2>
                    <a href="{{ route('time-tracking.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        Details →
                    </a>
                </div>
                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
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

            <!-- All Members - View Dashboard -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <h2 class="text-lg font-semibold text-gray-900">Team Members</h2>
                        </div>
                        <a href="{{ route('workspaces.members', session('current_workspace_id')) }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            Manage Team →
                        </a>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Click "View Dashboard" to see a member's dashboard as they see it</p>
                </div>
                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
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
                            <div class="flex items-center space-x-2">
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $member->pivot->role === 'owner' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $member->pivot->role === 'admin' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                        {{ $member->pivot->role === 'member' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $member->pivot->role === 'guest' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst($member->pivot->role) }}
                                    </span>
                                    @if($member->pivot->track)
                                        <p class="text-xs text-green-600 mt-1">{{ ucfirst(str_replace('_', '/', $member->pivot->track)) }}</p>
                                    @endif
                                </div>
                                @if($member->id !== auth()->id())
                                    <a href="{{ route('dashboard.as-user', $member) }}" 
                                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors"
                                       title="View {{ $member->name }}'s dashboard">
                                        <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Dashboard
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Projects & Tasks Section -->
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- All Projects -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">All Projects</h2>
                    <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        View all →
                    </a>
                </div>
                <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                    @forelse($allProjects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="block px-6 py-4 hover:bg-gray-50 transition-colors">
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
                                            {{ $project->completed_tasks_count }}/{{ $project->tasks_count }} tasks completed
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
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500 text-sm">
                            No projects yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Overdue & Unassigned Tasks -->
            <div class="space-y-6">
                <!-- Overdue Tasks -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                        <h2 class="text-lg font-semibold text-red-900 flex items-center">
                            <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            Overdue Tasks ({{ $overdueTasks->count() }})
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-40 overflow-y-auto">
                        @forelse($overdueTasks->take(5) as $task)
                            <a href="{{ route('tasks.show', $task) }}" class="block px-6 py-3 hover:bg-red-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $task->title }}</p>
                                        <p class="text-xs text-gray-500">{{ $task->project?->name }}</p>
                                    </div>
                                    <span class="text-xs text-red-600 font-medium">{{ $task->due_date->diffForHumans() }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="px-6 py-4 text-center text-green-600 text-sm">
                                No overdue tasks!
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Unassigned Tasks -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                        <h2 class="text-lg font-semibold text-blue-900 flex items-center">
                            <svg class="h-5 w-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Unassigned Tasks ({{ $unassignedTasks->count() }})
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-40 overflow-y-auto">
                        @forelse($unassignedTasks->take(5) as $task)
                            <a href="{{ route('tasks.show', $task) }}" class="block px-6 py-3 hover:bg-blue-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $task->title }}</p>
                                        <p class="text-xs text-gray-500">{{ $task->project?->name }}</p>
                                    </div>
                                    <span class="text-xs text-blue-600">Needs assignment</span>
                                </div>
                            </a>
                        @empty
                            <div class="px-6 py-4 text-center text-green-600 text-sm">
                                All tasks are assigned!
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Inactive Users & Recent Activity -->
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Inactive Users -->
            @if($inactiveUsers->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-orange-50">
                        <h2 class="text-lg font-semibold text-orange-900 flex items-center">
                            <svg class="h-5 w-5 text-orange-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            Inactive Users (7+ days)
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto">
                        @foreach($inactiveUsers as $user)
                            <div class="px-6 py-3 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-sm font-medium">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                        <span class="text-xs text-gray-500">{{ ucfirst($user->pivot->role) }}</span>
                                    </div>
                                </div>
                                <span class="text-xs text-orange-600">No recent activity</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden {{ $inactiveUsers->count() === 0 ? 'lg:col-span-2' : '' }}">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
                </div>
                <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto">
                    @forelse($recentActivity as $activity)
                        <div class="px-6 py-3 flex items-center space-x-3">
                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-sm font-medium">
                                {{ strtoupper(substr($activity->user?->name ?? 'S', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">
                                    <span class="font-medium">{{ $activity->user?->name ?? 'System' }}</span>
                                    {{ $activity->description }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500 text-sm">
                            No recent activity.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

