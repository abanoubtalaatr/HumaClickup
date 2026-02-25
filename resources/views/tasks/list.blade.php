@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="taskList()">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Tasks</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $project->name ?? 'All Tasks' }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <!-- View Toggle -->
                <div class="inline-flex rounded-md shadow-sm" role="group">
                    <a href="{{ $project ? route('projects.tasks.kanban', $project) : route('tasks.kanban') }}" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-l-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        Kanban
                    </a>
                    <a href="{{ $project ? route('projects.tasks.list', $project) : route('tasks.list') }}" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-r-lg border bg-indigo-600 text-white border-indigo-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        List
                    </a>
                </div>
                
                <button @click="openCreateModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Task
                </button>
            </div>
        </div>

        <!-- Project Selector (when no specific project in URL) -->
        @if(!$project && $projects->count() > 1)
        <div class="mb-6 bg-white p-4 rounded-lg shadow">
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Project:</label>
                <select onchange="window.location.href='{{ route('tasks.list') }}?project_id=' + this.value" 
                        class="block w-64 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ (request('project_id') == $proj->id || (!request('project_id') && $loop->first)) ? 'selected' : '' }}>
                            {{ $proj->name }}
                        </option>
                    @endforeach
                </select>
                <span class="text-sm text-gray-500">or</span>
                <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                    View all projects
                </a>
            </div>
        </div>
        @endif

        <!-- Filters -->
        <div class="mb-6 bg-white p-4 rounded-lg shadow">
            <form method="GET" action="{{ $project ? route('projects.tasks.list', $project) : route('tasks.list') }}">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                        <select name="project_id" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Projects</option>
                            @foreach($projects ?? [] as $proj)
                                <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                    {{ $proj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sprint</label>
                        <select name="sprint_id" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Sprints</option>
                            @foreach($sprints ?? [] as $sprint)
                                <option value="{{ $sprint->id }}" {{ request('sprint_id') == $sprint->id ? 'selected' : '' }}>
                                    {{ $sprint->name }} ({{ $sprint->status }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status_id" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Statuses</option>
                            @foreach($statuses ?? [] as $status)
                                <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assignee</label>
                        <select name="assignee_id" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Assignees</option>
                            @foreach($assignees ?? [] as $assignee)
                                <option value="{{ $assignee->id }}" {{ request('assignee_id') == $assignee->id ? 'selected' : '' }}>
                                    {{ $assignee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select name="priority" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Priorities</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="type" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Types</option>
                            <option value="task" {{ request('type') == 'task' ? 'selected' : '' }}>Tasks</option>
                            <option value="bug" {{ request('type') == 'bug' ? 'selected' : '' }}>Bugs</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ $project ? route('projects.tasks.list', $project) : route('tasks.list') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Clear Filters
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Tasks Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @forelse($tasks ?? [] as $task)
                    <li class="hover:bg-gray-50">
                        <a href="{{ route('tasks.show', $task) }}" class="block">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mr-4"
                                               onclick="event.preventDefault();">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                            <div class="mt-1 flex items-center space-x-2">
                                                <p class="text-sm text-gray-500">{{ $task->project->name ?? 'No Project' }}</p>
                                                @if($task->creator)
                                                    <span class="text-xs text-gray-400">•</span>
                                                    <p class="text-xs text-gray-500">Created by <span class="font-medium">{{ $task->creator->name }}</span></p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <!-- Bugs & Comments counts -->
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                            @if(isset($task->bugs_count) && $task->bugs_count > 0)
                                                <span class="flex items-center text-red-600 font-medium" title="Bugs">
                                                    <svg class="h-4 w-4 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                                    </svg>
                                                    {{ $task->bugs_count }}
                                                </span>
                                            @endif
                                            <span class="flex items-center" title="Comments">
                                                <svg class="h-4 w-4 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                </svg>
                                                {{ $task->comments_count ?? 0 }}
                                            </span>
                                            @if(isset($task->subtasks_count) && $task->subtasks_count > 0)
                                                <span class="flex items-center" title="Subtasks">
                                                    <svg class="h-4 w-4 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                    </svg>
                                                    {{ $task->subtasks_count }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Status -->
                                        @if($task->status)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                              style="background-color: {{ $task->status->color }}20; color: {{ $task->status->color }}">
                                            {{ $task->status->name }}
                                        </span>
                                        @endif
                                        
                                        <!-- Priority -->
                                        @if($task->priority === 'urgent')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Urgent
                                            </span>
                                        @elseif($task->priority === 'high')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                High
                                            </span>
                                        @endif
                                        
                                        <!-- Due Date -->
                                        @if($task->due_date)
                                            <span class="text-sm text-gray-500 {{ $task->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                                                {{ $task->due_date->format('M d, Y') }}
                                            </span>
                                        @endif
                                        
                                        <!-- Assignees with name and track -->
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($task->assignees->take(2) as $assignee)
                                                @php
                                                    $track = $assignee->getTrackInWorkspace(session('current_workspace_id'));
                                                @endphp
                                                <div class="flex items-center bg-gray-100 rounded-full px-2 py-1" 
                                                     title="{{ $assignee->name }}{{ $track ? ' - ' . $track->name : '' }}">
                                                    <div class="h-6 w-6 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs mr-1.5">
                                                        {{ strtoupper(substr($assignee->name, 0, 1)) }}
                                                    </div>
                                                    <span class="text-xs text-gray-700 font-medium">{{ explode(' ', $assignee->name)[0] }}</span>
                                                    @if($track)
                                                        <span class="text-xs text-indigo-600 ml-1">({{ Str::limit($track->name, 10, '') }})</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if($task->assignees->count() > 2)
                                                <div class="flex items-center bg-gray-200 rounded-full px-2 py-1">
                                                    <span class="text-xs text-gray-600 font-medium">+{{ $task->assignees->count() - 2 }} more</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="mt-2">No tasks found.</p>
                        <button @click="openCreateModal()" class="mt-2 text-indigo-600 hover:text-indigo-700 font-medium">Create your first task</button>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Create Task Modal -->
    <div x-show="showCreateModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             x-show="showCreateModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showCreateModal = false"></div>

        <!-- Modal panel -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="showCreateModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @click.stop
                     class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                    
                    <!-- Modal Header -->
                    <div class="bg-white px-4 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900" x-text="newTask.type === 'bug' ? 'Create New Bug' : 'Create New Task'"></h3>
                            <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <form @submit.prevent="createTask" class="bg-white px-6 py-4">
                        <div class="space-y-4">
                            <!-- Task Type - Prominent Field -->
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <label for="task_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        Type <span class="text-red-500">*</span>
                                    </span>
                                </label>
                                <select id="task_type" 
                                        name="type"
                                        x-model="newTask.type"
                                        x-on:change="updateTaskType()"
                                        required
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white sm:text-sm">
                                    <option value="task">Task</option>
                                    <option value="bug">Bug</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Choose whether this is a regular task or a bug report</p>
                            </div>

                            <!-- Related Task (only for bugs) -->
                            <div x-show="newTask.type === 'bug'" x-cloak>
                                <label for="task_related_task" class="block text-sm font-medium text-gray-700 mb-1">Related Task (Optional)</label>
                                <select id="task_related_task" 
                                        x-model="newTask.related_task_id"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">No related task</option>
                                    @if(isset($relatedTasks) && $relatedTasks->count() > 0)
                                        @foreach($relatedTasks as $relatedTask)
                                            <option value="{{ $relatedTask->id }}">{{ $relatedTask->title }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Link this bug to a specific task</p>
                            </div>

                            <!-- Task Title -->
                            <div>
                                <label for="task_title" class="block text-sm font-medium text-gray-700 mb-1">
                                    <span x-text="newTask.type === 'bug' ? 'Bug Title' : 'Task Title'"></span> *
                                </label>
                                <input type="text" 
                                       id="task_title" 
                                       x-model="newTask.title"
                                       x-bind:placeholder="newTask.type === 'bug' ? 'Describe the bug...' : 'Enter task title...'"
                                       required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="task_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <div id="task_description" 
                                     class="bg-white border border-gray-300 rounded-md shadow-sm focus-within:ring-indigo-500 focus-within:border-indigo-500"
                                     style="min-height: 150px;">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Project -->
                                @if(!isset($project))
                                <div>
                                    <label for="task_project" class="block text-sm font-medium text-gray-700 mb-1">Project *</label>
                                    <select id="task_project" 
                                            x-model="newTask.project_id"
                                            required
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Select project...</option>
                                        @foreach($projects ?? [] as $proj)
                                            <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <!-- Status -->
                                <div>
                                    <label for="task_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select id="task_status" 
                                            x-model="newTask.status_id"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        @foreach($statuses ?? [] as $status)
                                            <option value="{{ $status->id }}" {{ $status->is_default ? 'selected' : '' }}>{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Priority -->
                                <div>
                                    <label for="task_priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                    <select id="task_priority" 
                                            x-model="newTask.priority"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="low">Low</option>
                                        <option value="normal" selected>Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>

                                <!-- Due Date -->
                                <div>
                                    <label for="task_due_date" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                    <input type="date" 
                                           id="task_due_date" 
                                           x-model="newTask.due_date"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>

                            <!-- Sprint -->
                            @if(isset($sprints) && $sprints->count() > 0)
                            <div>
                                <label for="task_sprint" class="block text-sm font-medium text-gray-700 mb-1">Sprint</label>
                                <select id="task_sprint" 
                                        x-model="newTask.sprint_id"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">No Sprint</option>
                                    @foreach($sprints as $sprint)
                                        <option value="{{ $sprint->id }}">
                                            {{ $sprint->name }}
                                            @if($sprint->project) ({{ $sprint->project->name }})@endif
                                            - {{ ucfirst($sprint->status) }}
                                            ({{ $sprint->start_date->format('M d') }} - {{ $sprint->end_date->format('M d') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- Assignee -->
                            <div>
                                <label for="task_assignee" class="block text-sm font-medium text-gray-700 mb-1">Assignee</label>
                                <div class="relative">
                                    <button type="button" @click="assigneeDropdownOpen = !assigneeDropdownOpen; assigneeSearch = ''" 
                                            class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <span class="block truncate" x-text="newTask.assignee_id ? (assignees.find(a => String(a.id) === String(newTask.assignee_id))?.name || 'Select...') : 'Unassigned'"></span>
                                        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </span>
                                    </button>
                                    <input type="hidden" name="assignee_id" :value="newTask.assignee_id">
                                    <div x-show="assigneeDropdownOpen" @click.away="assigneeDropdownOpen = false" x-cloak
                                         class="absolute z-20 mt-1 w-full bg-white shadow-lg max-h-56 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden sm:text-sm">
                                        <div class="sticky top-0 px-2 py-1 bg-white border-b border-gray-200">
                                            <input type="text" x-model="assigneeSearch" placeholder="Search assignee..." 
                                                   class="block w-full rounded border-gray-300 py-1.5 text-sm focus:ring-indigo-500 focus:border-indigo-500" @click.stop>
                                        </div>
                                        <div class="max-h-40 overflow-y-auto py-1">
                                            <button type="button" @click="newTask.assignee_id = ''; assigneeDropdownOpen = false"
                                                    class="w-full text-left px-3 py-2 hover:bg-gray-100 text-sm"
                                                    :class="!newTask.assignee_id ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700'">
                                                Unassigned
                                            </button>
                                            @foreach($assignees ?? [] as $assignee)
                                            <button type="button" 
                                                    @click="newTask.assignee_id = '{{ $assignee->id }}'; assigneeDropdownOpen = false"
                                                    class="w-full text-left px-3 py-2 hover:bg-gray-100 text-sm flex items-center"
                                                    :class="newTask.assignee_id == '{{ $assignee->id }}' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700'"
                                                    data-name="{{ strtolower($assignee->name) }}"
                                                    data-email="{{ strtolower($assignee->email ?? '') }}"
                                                    x-show="!assigneeSearch || $el.dataset.name.includes(assigneeSearch.toLowerCase()) || $el.dataset.email.includes(assigneeSearch.toLowerCase())">
                                                {{ $assignee->name }}
                                                @if(!empty($assignee->email))
                                                    <span class="ml-2 text-xs text-gray-500">{{ $assignee->email }}</span>
                                                @endif
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="mt-6 flex items-center justify-end space-x-3">
                            <button type="button"
                                    @click="showCreateModal = false"
                                    class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit"
                                    :disabled="isLoading || !newTask.title"
                                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!isLoading" x-text="newTask.type === 'bug' ? 'Create Bug' : 'Create Task'"></span>
                                <span x-show="isLoading" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Creating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function taskList() {
    return {
        showCreateModal: false,
        isLoading: false,
        assignees: @json($assignees ?? []),
        assigneeDropdownOpen: false,
        assigneeSearch: '',
        newTask: {
            type: 'task',
            title: '',
            description: '',
            project_id: '{{ $project->id ?? '' }}',
            status_id: '{{ $statuses->where('is_default', true)->first()?->id ?? $statuses->first()?->id ?? '' }}',
            priority: 'normal',
            due_date: '',
            sprint_id: '',
            assignee_id: '',
            related_task_id: ''
        },
        
        openCreateModal() {
            // Reset form
            this.newTask = {
                type: 'task',
                title: '',
                description: '',
                project_id: '{{ $project->id ?? '' }}',
                status_id: '{{ $statuses->where('is_default', true)->first()?->id ?? $statuses->first()?->id ?? '' }}',
                priority: 'normal',
                due_date: '',
                sprint_id: '',
                assignee_id: '',
                related_task_id: ''
            };
            this.assigneeDropdownOpen = false;
            this.assigneeSearch = '';
            this.showCreateModal = true;
            
            // Initialize Quill editor when modal opens
            this.$nextTick(() => {
                if (typeof Quill !== 'undefined') {
                    // Remove existing instance if any
                    const existingEditor = document.querySelector('#task_description .ql-container');
                    if (existingEditor) {
                        const editorContainer = document.getElementById('task_description');
                        editorContainer.innerHTML = '';
                    }
                    
                    // Initialize Quill
                    const quill = new Quill('#task_description', {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                [{ 'color': [] }, { 'background': [] }],
                                [{ 'align': [] }],
                                ['link', 'image'],
                                ['clean']
                            ]
                        },
                        placeholder: 'Add a description...',
                    });
                    
                    // Update newTask.description when content changes
                    quill.on('text-change', () => {
                        this.newTask.description = quill.root.innerHTML;
                    });
                }
            });
        },
        
        updateTaskType() {
            // This function can be used to update UI when type changes
            // Currently handled by x-show directives
        },
        
        async createTask() {
            if (!this.newTask.title) return;
            
            this.isLoading = true;
            
            try {
                // Get content from Quill editor if it exists
                let description = '';
                const quillEditor = document.querySelector('#task_description .ql-editor');
                if (quillEditor) {
                    description = quillEditor.innerHTML;
                } else {
                    description = this.newTask.description || '';
                }
                
                const formData = new FormData();
                formData.append('type', this.newTask.type || 'task');
                formData.append('title', this.newTask.title);
                formData.append('description', description);
                formData.append('status_id', this.newTask.status_id);
                formData.append('priority', this.newTask.priority);
                if (this.newTask.due_date) formData.append('due_date', this.newTask.due_date);
                if (this.newTask.sprint_id) formData.append('sprint_id', this.newTask.sprint_id);
                if (this.newTask.assignee_id) formData.append('assignee_ids[]', this.newTask.assignee_id);
                if (this.newTask.related_task_id) formData.append('related_task_id', this.newTask.related_task_id);
                
                const projectId = this.newTask.project_id || '{{ $project->id ?? '' }}';
                const url = projectId 
                    ? `/projects/${projectId}/tasks`
                    : '/tasks';
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Clear Quill editor before closing
                    const quillEditor = document.querySelector('#task_description .ql-editor');
                    if (quillEditor) {
                        quillEditor.innerHTML = '';
                    }
                    this.showCreateModal = false;
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to create task');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to create task. Please try again.');
            } finally {
                this.isLoading = false;
            }
        }
    };
}
</script>
@endpush
@endsection
