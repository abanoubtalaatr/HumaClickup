@extends('layouts.app')

@section('title', $sprint->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center space-x-3">
                <a href="{{ route('sprints.index', ['workspace' => session('current_workspace_id')]) }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $sprint->name }}</h1>
                
                <!-- Status Badge -->
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($sprint->status === 'active') bg-green-100 text-green-800
                    @elseif($sprint->status === 'completed') bg-gray-100 text-gray-800
                    @elseif($sprint->status === 'planning') bg-blue-100 text-blue-800
                    @else bg-red-100 text-red-800
                    @endif">
                    {{ ucfirst($sprint->status) }}
                </span>
            </div>

            <div class="flex items-center space-x-2">
                @if($sprint->status === 'planning')
                    <form action="{{ route('sprints.start', ['workspace' => session('current_workspace_id'), 'sprint' => $sprint]) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Start Sprint
                        </button>
                    </form>
                @endif
                
                @if($sprint->status === 'active')
                    <form action="{{ route('sprints.complete', ['workspace' => session('current_workspace_id'), 'sprint' => $sprint]) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                            Complete Sprint
                        </button>
                    </form>
                @endif

                <a href="{{ route('sprints.edit', ['workspace' => session('current_workspace_id'), 'sprint' => $sprint]) }}" 
                   class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                    Edit Sprint
                </a>
            </div>
        </div>

        @if($sprint->project)
            <p class="text-sm text-gray-500 dark:text-gray-400">Project: {{ $sprint->project->name }}</p>
        @endif

        @if($sprint->goal)
            <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $sprint->goal }}</p>
        @endif
    </div>

    <!-- Sprint Metrics -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Duration -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Duration</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $metrics['duration'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">days</p>
                </div>
                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                {{ $sprint->start_date->format('M d') }} - {{ $sprint->end_date->format('M d, Y') }}
            </p>
        </div>

        <!-- Days Remaining -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Days Remaining</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $metrics['days_remaining'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">days left</p>
                </div>
                <svg class="h-8 w-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="mt-2">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-orange-500 h-1.5 rounded-full" style="width: {{ $metrics['time_progress'] }}%"></div>
                </div>
            </div>
        </div>

        <!-- Total Tasks -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Tasks</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $metrics['total_tasks'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">tasks</p>
                </div>
                <svg class="h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
        </div>

        <!-- Completion -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Completion</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($metrics['completion_percentage'], 0) }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">completed</p>
                </div>
                <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="mt-2">
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $metrics['completion_percentage'] }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Breakdown -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Task Breakdown</h2>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center">
                <p class="text-3xl font-bold text-gray-500 dark:text-gray-400">{{ $metrics['todo_tasks'] }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">To Do</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-blue-600">{{ $metrics['in_progress_tasks'] }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">In Progress</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-green-600">{{ $metrics['completed_tasks'] }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Done</p>
            </div>
        </div>
    </div>

    <!-- Tasks by Status -->
    <div class="space-y-6">
        <!-- To Do Tasks -->
        @if($todoTasks->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">To Do ({{ $todoTasks->count() }})</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($todoTasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $task->title }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $task->project->name }}</p>
                                </div>
                                @if($task->assignees->count() > 0)
                                    <div class="flex -space-x-2">
                                        @foreach($task->assignees->take(3) as $assignee)
                                            <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-medium border-2 border-white dark:border-gray-800">
                                                {{ strtoupper(substr($assignee->name, 0, 1)) }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- In Progress Tasks -->
        @if($inProgressTasks->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">In Progress ({{ $inProgressTasks->count() }})</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($inProgressTasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $task->title }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $task->project->name }}</p>
                                </div>
                                @if($task->assignees->count() > 0)
                                    <div class="flex -space-x-2">
                                        @foreach($task->assignees->take(3) as $assignee)
                                            <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium border-2 border-white dark:border-gray-800">
                                                {{ strtoupper(substr($assignee->name, 0, 1)) }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Done Tasks -->
        @if($doneTasks->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Done ({{ $doneTasks->count() }})</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($doneTasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white line-through opacity-60">{{ $task->title }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $task->project->name }}</p>
                                </div>
                                @if($task->assignees->count() > 0)
                                    <div class="flex -space-x-2">
                                        @foreach($task->assignees->take(3) as $assignee)
                                            <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center text-white text-xs font-medium border-2 border-white dark:border-gray-800">
                                                {{ strtoupper(substr($assignee->name, 0, 1)) }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- No Tasks -->
        @if($sprint->tasks->count() === 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No tasks in this sprint</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by adding tasks to this sprint.</p>
            </div>
        @endif
    </div>
</div>
@endsection
