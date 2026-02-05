@extends('layouts.app')

@section('title', 'Create Task')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8" x-data="taskCreateForm()" data-tags-store-url="{{ route('tags.store') }}">
    <div class="py-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900" x-text="isBug ? 'Create New Bug' : 'Create New Task'"></h1>
            <p class="mt-1 text-sm text-gray-500" x-text="isBug ? '{{ $project ? "Report a bug in {$project->name}" : "Report a bug" }}' : '{{ $project ? "Add a task to {$project->name}" : "Create a new task" }}'"></p>
        </div>

        <div class="bg-white shadow rounded-lg">
            <form action="{{ $project ? route('projects.tasks.store', $project) : route('tasks.store') }}" method="POST" class="p-6" enctype="multipart/form-data">
                @csrf

                <!-- Project Selection (if not already selected) -->
                @if(!$project && $projects && $projects->count() > 0)
                <div class="mb-4">
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Project <span class="text-red-500">*</span>
                    </label>
                    <select id="project_id" 
                            name="project_id" 
                            required
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select a project</option>
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}" {{ old('project_id') == $proj->id ? 'selected' : '' }}>
                                {{ $proj->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @elseif($project)
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                @endif

                <!-- Task Type - Required Field -->
                <div class="mb-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Type <span class="text-red-500">*</span>
                        </span>
                    </label>
                    <select id="type" 
                            name="type" 
                            required
                            x-on:change="updateFormType()"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        @php
                            $defaultType = old('type', request('type', 'task'));
                        @endphp
                        <option value="task" {{ $defaultType == 'task' ? 'selected' : '' }}>Task</option>
                        <option value="bug" {{ $defaultType == 'bug' ? 'selected' : '' }}>Bug</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Choose whether this is a regular task or a bug report</p>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Related Task (only for bugs) -->
                <div class="mb-4" x-show="isBug" x-cloak>
                    <label for="related_task_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Related Task (Optional)
                    </label>
                    <select id="related_task_id" 
                            name="related_task_id" 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">No related task</option>
                        @if(isset($tasks) && $tasks->count() > 0)
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}" {{ old('related_task_id') == $task->id ? 'selected' : '' }}>
                                    {{ $task->title }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Link this bug to a specific task</p>
                    @error('related_task_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Task Title -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        <span x-text="isBug ? 'Bug Title' : 'Task Title'"></span> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="{{ old('title') }}" 
                           required
                           x-bind:placeholder="isBug ? 'Describe the bug...' : 'What needs to be done?'"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <!-- Hidden textarea for form submission -->
                    <textarea name="description" 
                              style="display: none;">{{ old('description') }}</textarea>
                    <!-- Quill editor container -->
                    <div id="description" 
                         class="bg-white border border-gray-300 rounded-md shadow-sm focus-within:ring-indigo-500 focus-within:border-indigo-500"
                         style="min-height: 200px;">
                        {!! old('description') !!}
                    </div>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Status -->
                    @if($statuses && $statuses->count() > 0)
                    <div>
                        <label for="status_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select id="status_id" 
                                name="status_id" 
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" 
                                        {{ ($status->is_default || old('status_id') == $status->id) ? 'selected' : '' }}
                                        style="color: {{ $status->color }}">
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('status_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endif

                    <!-- Priority -->
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Priority
                        </label>
                        <select id="priority" 
                                name="priority" 
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="none" {{ old('priority') == 'none' ? 'selected' : '' }}>None</option>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                        @error('priority')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Sprint -->
                @if($sprints && $sprints->count() > 0)
                <div class="mb-4">
                    <label for="sprint_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Sprint
                    </label>
                    <select id="sprint_id" 
                            name="sprint_id" 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">No Sprint</option>
                        @foreach($sprints as $sprint)
                            <option value="{{ $sprint->id }}" {{ old('sprint_id') == $sprint->id ? 'selected' : '' }}>
                                {{ $sprint->name }} 
                                @if($sprint->project)
                                    ({{ $sprint->project->name }})
                                @endif
                                - {{ $sprint->status }}
                                ({{ $sprint->start_date->format('M d') }} - {{ $sprint->end_date->format('M d') }})
                            </option>
                        @endforeach
                    </select>
                    @error('sprint_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Due Date
                        </label>
                        <input type="datetime-local" 
                               id="due_date" 
                               name="due_date" 
                               value="{{ old('due_date') }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('due_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Start Date
                        </label>
                        <input type="datetime-local" 
                               id="start_date" 
                               name="start_date" 
                               value="{{ old('start_date') }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Estimated Time -->
                <div class="mb-4">
                    <label for="estimated_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Estimated Time (hours)
                    </label>
                    <input type="number" 
                           id="estimated_time" 
                           name="estimated_time" 
                           value="{{ old('estimated_time') }}"
                           step="0.5"
                           min="0"
                           placeholder="e.g., 2.5"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">Enter estimated time in hours (e.g., 2.5 for 2 hours 30 minutes)</p>
                    @error('estimated_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Assignees -->
                @if($users && $users->count() > 0)
                <div class="mb-4" x-data="{ assigneesOpen: false, assigneeSearch: '' }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Assignees
                    </label>
                    <div class="relative">
                        <button type="button" @click="assigneesOpen = !assigneesOpen" 
                                class="relative w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <span class="block truncate" x-show="selectedAssignees.length === 0">Select assignees...</span>
                            <span class="block truncate" x-show="selectedAssignees.length > 0" x-text="selectedAssignees.length + ' selected'"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </button>
                        <div x-show="assigneesOpen" @click.away="assigneesOpen = false" x-cloak
                             class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                            <div class="sticky top-0 px-2 py-1 bg-white dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                <input type="text" 
                                       x-model="assigneeSearch" 
                                       placeholder="Search by name or email..." 
                                       class="block w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-600 dark:text-white py-1.5 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       @click.stop>
                            </div>
                            <div class="max-h-48 overflow-y-auto py-1">
                            @foreach($users as $user)
                            <label class="flex items-center px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer"
                                   data-name="{{ strtolower($user->name) }}"
                                   data-email="{{ strtolower($user->email ?? '') }}"
                                   x-show="!assigneeSearch || $el.dataset.name.includes(assigneeSearch.toLowerCase()) || $el.dataset.email.includes(assigneeSearch.toLowerCase())">
                                <input type="checkbox" 
                                       name="assignee_ids[]" 
                                       value="{{ $user->id }}"
                                       {{ in_array($user->id, old('assignee_ids', [])) ? 'checked' : '' }}
                                       @change="updateSelectedAssignees()"
                                       class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                <span class="ml-3 text-sm text-gray-900 dark:text-gray-200">{{ $user->name }}</span>
                                @if($user->email)
                                    <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</span>
                                @endif
                            </label>
                            @endforeach
                            </div>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Click to select multiple assignees</p>
                    @error('assignee_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Tags (optional - list always shown; members/admins can create new tags) -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tags <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <div class="relative">
                        <button type="button" @click="tagsOpen = !tagsOpen" 
                                class="relative w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <span class="block truncate" x-show="selectedTags.length === 0">Select tags...</span>
                            <span class="block truncate" x-show="selectedTags.length > 0" x-text="selectedTags.length + ' selected'"></span>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </span>
                        </button>
                        <div x-show="tagsOpen" @click.away="tagsOpen = false" x-cloak
                             class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                            <div id="tag-list-container">
                                @forelse($tags ?? [] as $tag)
                                <label class="flex items-center px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer">
                                    <input type="checkbox" 
                                           name="tag_ids[]" 
                                           value="{{ $tag->id }}"
                                           {{ in_array($tag->id, old('tag_ids', [])) ? 'checked' : '' }}
                                           @change="updateSelectedTags()"
                                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <span class="ml-3 text-sm text-gray-900 dark:text-gray-200">{{ $tag->name }}</span>
                                </label>
                                @empty
                                <p class="px-3 py-2 text-sm text-gray-500">No tags yet. Create one below.</p>
                                @endforelse
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-600 px-3 py-2 mt-1">
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Create new tag</p>
                                <div class="flex gap-2 flex-wrap items-center">
                                    <input type="text" 
                                           x-model="newTagName" 
                                           placeholder="Tag name"
                                           class="flex-1 min-w-0 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-600 dark:text-white py-1.5 text-sm">
                                    <input type="color" 
                                           x-model="newTagColor" 
                                           class="h-8 w-12 rounded border border-gray-300 cursor-pointer">
                                    <button type="button" 
                                            @click="createTag()"
                                            :disabled="!newTagName.trim() || creatingTag"
                                            class="px-3 py-1.5 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Add tag
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Select existing tags or create new ones. Tags are optional.</p>
                    @error('tag_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Attachments -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Attach files <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="file" 
                           name="attachments[]" 
                           multiple
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.png,.jpg,.jpeg,.gif,.zip"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max 10MB per file. You can select multiple files.</p>
                    @error('attachments.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ $project ? route('projects.show', $project) : route('tasks.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                            x-text="isBug ? 'Create Bug' : 'Create Task'">
                        Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function taskCreateForm() {
    return {
        selectedAssignees: [],
        selectedTags: [],
        isBug: false,
        tagsOpen: false,
        newTagName: '',
        newTagColor: '#6366f1',
        creatingTag: false,

        init() {
            // Initialize Quill editor for description
            if (typeof Quill !== 'undefined') {
                const quill = new Quill('#description', {
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
                    placeholder: 'Add details about this task...',
                });
                
                // Update hidden input when content changes
                quill.on('text-change', () => {
                    const descriptionInput = document.querySelector('textarea[name="description"]');
                    if (descriptionInput) {
                        descriptionInput.value = quill.root.innerHTML;
                    }
                });
                
                // Set initial content if exists
                const descriptionInput = document.querySelector('textarea[name="description"]');
                if (descriptionInput && descriptionInput.value) {
                    quill.root.innerHTML = descriptionInput.value;
                }
            }
            
            // Initialize selected assignees from old input
            document.querySelectorAll('input[name="assignee_ids[]"]:checked').forEach(checkbox => {
                this.selectedAssignees.push(checkbox.value);
            });
            
            // Initialize selected tags from old input
            document.querySelectorAll('input[name="tag_ids[]"]:checked').forEach(checkbox => {
                this.selectedTags.push(checkbox.value);
            });
            
            // Initialize type - check if there's an old value or default to task
            const typeSelect = document.getElementById('type');
            if (typeSelect) {
                // Check URL parameter for type
                const urlParams = new URLSearchParams(window.location.search);
                const typeParam = urlParams.get('type');
                if (typeParam && (typeParam === 'task' || typeParam === 'bug')) {
                    typeSelect.value = typeParam;
                }
                // Set isBug based on current selection
                this.isBug = typeSelect.value === 'bug';
            }
        },
        
        updateFormType() {
            const typeSelect = document.getElementById('type');
            if (typeSelect) {
                this.isBug = typeSelect.value === 'bug';
            }
        },
        
        updateSelectedAssignees() {
            this.selectedAssignees = Array.from(
                document.querySelectorAll('input[name="assignee_ids[]"]:checked')
            ).map(checkbox => checkbox.value);
        },
        
        updateSelectedTags() {
            this.selectedTags = Array.from(
                document.querySelectorAll('input[name="tag_ids[]"]:checked')
            ).map(checkbox => checkbox.value);
        },

        async createTag() {
            const name = (this.newTagName && this.newTagName.trim()) || '';
            if (!name) return;
            const wrap = document.getElementById('tag-list-container');
            this.creatingTag = true;
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('name', name);
                formData.append('color', this.newTagColor || '#6366f1');
                const url = document.querySelector('[data-tags-store-url]').dataset.tagsStoreUrl;
                const res = await fetch(url, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.success && data.tag) {
                    const noTagsMsg = wrap.querySelector('p.text-gray-500');
                    if (noTagsMsg) noTagsMsg.remove();
                    const label = document.createElement('label');
                    label.className = 'flex items-center px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer';
                    const tagName = (data.tag.name || name).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    label.innerHTML = '<input type="checkbox" name="tag_ids[]" value="' + data.tag.id + '" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"> <span class="ml-3 text-sm text-gray-900 dark:text-gray-200">' + tagName + '</span>';
                    wrap.appendChild(label);
                    label.querySelector('input').checked = true;
                    label.querySelector('input').addEventListener('change', () => this.updateSelectedTags());
                    this.newTagName = '';
                    this.newTagColor = '#6366f1';
                    this.updateSelectedTags();
                } else {
                    alert(data.message || 'Could not create tag.');
                }
            } catch (e) {
                console.error(e);
                alert('Could not create tag. Please try again.');
            }
            this.creatingTag = false;
        }
    }
}

</script>
@endpush
@endsection

