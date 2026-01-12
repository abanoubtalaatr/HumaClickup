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
                    <a href="{{ $project ? route('projects.tasks.index', $project) : route('tasks.list') }}" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-r-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        List
                    </a>
                </div>
                
                <button @click="showFilters = !showFilters" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filters
                </button>
                @if(!($isGuest ?? false))
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

        <!-- Filters Panel -->
        <div x-show="showFilters" 
             x-transition
             class="mb-6 bg-white p-4 rounded-lg shadow">
            <form method="GET" action="{{ $project ? route('projects.tasks.kanban', $project) : route('tasks.kanban') }}">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
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
                            <h3 class="text-lg font-semibold text-gray-900">Create New Task</h3>
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
                            <!-- Task Title -->
                            <div>
                                <label for="task_title" class="block text-sm font-medium text-gray-700 mb-1">Task Title *</label>
                                <input type="text" 
                                       id="task_title" 
                                       x-model="newTask.title"
                                       placeholder="Enter task title..."
                                       required
                                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="task_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea id="task_description" 
                                          x-model="newTask.description"
                                          rows="3"
                                          placeholder="Add a description..."
                                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
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
                                <select id="task_assignee" 
                                        x-model="newTask.assignee_id"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Unassigned</option>
                                    @foreach($assignees ?? [] as $assignee)
                                        <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
                                    @endforeach
                                </select>
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
                                <span x-show="!isLoading">Create Task</span>
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
function kanbanBoard() {
    return {
        showFilters: false,
        showCreateModal: false,
        isLoading: false,
        newTask: {
            title: '',
            description: '',
            project_id: '{{ $project->id ?? '' }}',
            status_id: '{{ $statuses->where('is_default', true)->first()?->id ?? $statuses->first()?->id ?? '' }}',
            priority: 'normal',
            due_date: '',
            sprint_id: '',
            assignee_id: '',
            estimated_time: ''
        },
        
        openCreateModal(statusId = null) {
            // Reset form
            this.newTask = {
                title: '',
                description: '',
                project_id: '{{ $project->id ?? '' }}',
                status_id: statusId || '{{ $statuses->where('is_default', true)->first()?->id ?? $statuses->first()?->id ?? '' }}',
                priority: 'normal',
                due_date: '',
                assignee_id: '',
                estimated_time: ''
            };
            this.showCreateModal = true;
        },
        
        async createTask() {
            if (!this.newTask.title) return;
            
            this.isLoading = true;
            
            try {
                const formData = new FormData();
                formData.append('title', this.newTask.title);
                formData.append('description', this.newTask.description || '');
                formData.append('status_id', this.newTask.status_id);
                formData.append('priority', this.newTask.priority);
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
                                
                                // Update task status via AJAX
                                fetch('/tasks/' + taskId + '/status', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        status_id: newStatusId,
                                        position: evt.newIndex
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (!data.success) {
                                        // Revert on error
                                        evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
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
