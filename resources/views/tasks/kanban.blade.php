@extends('layouts.app')

@section('title', 'Kanban Board')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8" 
     x-data="kanbanBoard()">
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
                       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-l-lg border bg-indigo-600 text-white border-indigo-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        Kanban
                    </a>
                    
                </div>
                
                <button @click="showFilters = !showFilters" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filters
                </button>
                @if(!($isGuest ?? false) || $tester)
                <button @click="openCreateModal()" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Task
                </button>
                @endif
            </div>
        </div>

        <!-- Project Selector (when no specific project in URL) -->
        @if(!$project && $projects->count() > 1)
        <div class="mb-6 bg-white p-4 rounded-lg shadow">
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Project:</label>
                <select onchange="window.location.href='{{ route('tasks.kanban') }}?project_id=' + this.value" 
                        class="block w-64 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Projects</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ (request('project_id') == $proj->id ) ? 'selected' : '' }}>
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

        <!-- Filters Panel -->
        <div x-show="showFilters" 
             x-transition
             class="mb-6 bg-white p-4 rounded-lg shadow">
            <form method="GET" action="{{ $project ? route('projects.tasks.kanban', $project) : route('tasks.kanban') }}">
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
                    <a href="{{ $project ? route('projects.tasks.kanban', $project) : route('tasks.kanban') }}" 
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

        <!-- Kanban Board -->
        <div class="flex space-x-4 overflow-x-auto pb-4" style="min-height: 600px;">
            @forelse($statuses ?? [] as $status)
                <div class="flex-shrink-0 w-80 bg-gray-100 rounded-lg p-4" 
                     data-status-id="{{ $status->id }}">
                    <!-- Status Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="h-3 w-3 rounded-full mr-2" style="background-color: {{ $status->color }}"></div>
                            <h3 class="text-sm font-semibold text-gray-900">{{ $status->name }}</h3>
                            <span class="ml-2 text-xs text-gray-500">({{ $status->tasks->count() ?? 0 }})</span>
                        </div>
                        @if(!($isGuest ?? false))
                        <button @click="openCreateModal({{ $status->id }})" 
                                class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>
                        @endif
                    </div>

                    <!-- Tasks Container -->
                    <div class="space-y-3 kanban-column min-h-[200px]" data-status-id="{{ $status->id }}">
                        @foreach($status->tasks ?? [] as $task)
                            @include('tasks.partials.task-card', ['task' => $task])
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="flex-1 flex items-center justify-center bg-gray-50 rounded-lg p-8">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">
                            @if($projects->isEmpty())
                                No projects available
                            @else
                                No statuses configured
                            @endif
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if($projects->isEmpty())
                                Create a project first to start adding tasks.
                            @else
                                This project doesn't have any statuses yet. Statuses like "To Do", "In Progress", "In Review", "Retest", "Blocked", "Closed" should be configured in the project settings.
                            @endif
                        </p>
                        @if($projects->isEmpty() && !($isGuest ?? false))
                            <a href="{{ route('projects.create') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                Create Project
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse
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
                                    @if(isset($tasks) && $tasks->count() > 0)
                                        @foreach($tasks as $task)
                                            <option value="{{ $task->id }}">{{ $task->title }}</option>
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
                                        @if(isset($statuses) && $statuses->count() > 0)
                                            @foreach($statuses as $status)
                                                <option value="{{ $status->id }}" {{ $status->is_default ? 'selected' : '' }}>{{ $status->name }}</option>
                                            @endforeach
                                        @else
                                            <option value="">No statuses available</option>
                                        @endif
                                    </select>
                                    @if(!isset($statuses) || $statuses->count() == 0)
                                        <p class="mt-1 text-xs text-red-600">Please select a project first to see available statuses</p>
                                    @endif
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

                            <!-- Estimated Time -->
                            <div>
                                <label for="task_estimated" class="block text-sm font-medium text-gray-700 mb-1">Estimated Time (minutes)</label>
                                <input type="number" 
                                       id="task_estimated" 
                                       x-model="newTask.estimated_time"
                                       min="0"
                                       placeholder="e.g., 60"
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
@php
    $routeName = request()->route()->getName();
    if ($routeName === 'project.tasks.kanban' && request()->route()->parameter('workspace') && request()->route()->parameter('project')) {
        $workspace = request()->route()->parameter('workspace');
        $projectParam = request()->route()->parameter('project');
        $workspaceId = is_object($workspace) ? $workspace->id : $workspace;
        $projectId = is_object($projectParam) ? $projectParam->id : $projectParam;
        $updateStatusUrlTemplate = route('workspace.project.tasks.updateStatus', [
            'workspace' => $workspaceId,
            'project' => $projectId,
            'task' => 0
        ]);
    } elseif ($routeName === 'projects.tasks.kanban' && request()->route()->parameter('project')) {
        $projectParam = request()->route()->parameter('project');
        $projectId = is_object($projectParam) ? $projectParam->id : $projectParam;
        $updateStatusUrlTemplate = route('projects.tasks.updateStatus', ['project' => $projectId, 'taskId' => 0]);
    } else {
        $updateStatusUrlTemplate = route('tasks.updateStatus', ['task' => 0]);
    }
@endphp
<script>
function kanbanBoard() {
    return {
        showFilters: false,
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
            estimated_time: '',
            related_task_id: ''
        },
        
        openCreateModal(statusId = null) {
            // Reset form
            this.newTask = {
                type: 'task',
                title: '',
                description: '',
                project_id: '{{ $project->id ?? '' }}',
                status_id: statusId || '{{ $statuses->where('is_default', true)->first()?->id ?? $statuses->first()?->id ?? '' }}',
                priority: 'normal',
                due_date: '',
                assignee_id: '',
                estimated_time: '',
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
                if (this.newTask.related_task_id) {
                    formData.append('related_task_id', this.newTask.related_task_id);
                }
                if (this.newTask.due_date) formData.append('due_date', this.newTask.due_date);
                if (this.newTask.sprint_id) formData.append('sprint_id', this.newTask.sprint_id);
                if (this.newTask.assignee_id) formData.append('assignee_ids[]', this.newTask.assignee_id);
                if (this.newTask.estimated_time) formData.append('estimated_time', this.newTask.estimated_time);
                
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
                    // Reload page to show new task
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
        },
        
        init() {
            // Initialize Sortable for each kanban column
            this.$nextTick(() => {
                document.querySelectorAll('.kanban-column').forEach((column) => {
                    if (window.Sortable) {
                        new Sortable(column, {
                            group: 'kanban',
                            animation: 150,
                            ghostClass: 'bg-blue-100',
                            onEnd: (evt) => {
                                const taskId = evt.item.dataset.taskId;
                                const newStatusId = evt.to.dataset.statusId;
                                const updateStatusUrl = "{{ $updateStatusUrlTemplate }}".replace(/\/0\/status/, `/${taskId}/status`);
                                fetch(updateStatusUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        status_id: newStatusId,
                                        position: evt.newIndex
                                    })
                                })
                                .then(response => {
                                    return response.json().then(data => ({ ok: response.ok, data }));
                                })
                                .then(({ ok, data }) => {
                                    if (!ok || !data.success) {
                                        evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                                        if (data.message) {
                                            alert(data.message);
                                        }
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                                });
                            }
                        });
                    }
                });
            });
        }
    };
}
</script>
@endpush
@endsection
