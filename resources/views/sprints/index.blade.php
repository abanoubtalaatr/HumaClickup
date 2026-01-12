@extends('layouts.app')

@section('title', 'Sprints')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Sprints</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage your agile sprints and iterations
            </p>
        </div>
        <a href="{{ route('sprints.create', ['workspace' => session('current_workspace_id'), 'project_id' => $project?->id]) }}" 
           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Sprint
        </a>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <!-- Project Filter -->
        <select onchange="window.location.href='{{ route('sprints.index', ['workspace' => session('current_workspace_id')]) }}?project_id=' + this.value + '&status={{ $status }}'" 
                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Projects</option>
            @foreach($projects as $proj)
                <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                    {{ $proj->name }}
                </option>
            @endforeach
        </select>

        <!-- Status Filter -->
        <select onchange="window.location.href='{{ route('sprints.index', ['workspace' => session('current_workspace_id')]) }}?project_id={{ request('project_id') }}&status=' + this.value" 
                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
            <option value="planning" {{ $status === 'planning' ? 'selected' : '' }}>Planning</option>
            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </div>

    <!-- Sprints List -->
    @if($sprints->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No sprints</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new sprint.</p>
            <div class="mt-6">
                <a href="{{ route('sprints.create', ['workspace' => session('current_workspace_id')]) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Sprint
                </a>
            </div>
        </div>
    @else
        <div class="space-y-4">
            @foreach($sprints as $sprint)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <a href="{{ route('sprints.show', ['workspace' => $sprint->workspace_id, 'sprint' => $sprint->id]) }}" class="text-lg font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ $sprint->name }}
                                    </a>
                                    
                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($sprint->status === 'active') bg-green-100 text-green-800
                                        @elseif($sprint->status === 'completed') bg-gray-100 text-gray-800
                                        @elseif($sprint->status === 'planning') bg-blue-100 text-blue-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($sprint->status) }}
                                    </span>

                                    @if($sprint->isOverdue())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Overdue
                                        </span>
                                    @endif
                                </div>

                                @if($sprint->project)
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                        Project: {{ $sprint->project->name }}
                                    </p>
                                @endif

                                @if($sprint->goal)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        {{ Str::limit($sprint->goal, 150) }}
                                    </p>
                                @endif

                                <!-- Sprint Dates and Progress -->
                                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $sprint->start_date->format('M d') }} - {{ $sprint->end_date->format('M d, Y') }}
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        {{ $sprint->tasks_count }} tasks
                                    </div>
                                    @if($sprint->status === 'active')
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $sprint->days_remaining }} days remaining
                                        </div>
                                    @endif
                                </div>

                                <!-- Progress Bar -->
                                @if($sprint->tasks_count > 0)
                                    <div class="mt-4">
                                        <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                                            <span>Progress</span>
                                            <span>{{ number_format($sprint->completion_progress, 0) }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-indigo-600 h-2 rounded-full transition-all" 
                                                 style="width: {{ $sprint->completion_progress }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="ml-4 flex items-center space-x-2">
                                @if($sprint->status === 'planning')
                                    <form action="{{ route('sprints.start', ['workspace' => $sprint->workspace_id, 'sprint' => $sprint->id]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                                            Start Sprint
                                        </button>
                                    </form>
                                @endif
                                
                                @if($sprint->status === 'active')
                                    <form action="{{ route('sprints.complete', ['workspace' => $sprint->workspace_id, 'sprint' => $sprint->id]) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 text-sm bg-gray-600 text-white rounded hover:bg-gray-700">
                                            Complete
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('sprints.edit', ['workspace' => $sprint->workspace_id, 'sprint' => $sprint->id]) }}" 
                                   class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
