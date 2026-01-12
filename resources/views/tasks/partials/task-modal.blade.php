<!-- Task Modal -->
<div x-show="showTaskModal" 
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50"
     @click.self="closeTaskModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Task Details</h3>
                <button @click="closeTaskModal()" 
                        class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content (Left) -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <input type="text" 
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-lg font-medium"
                                   value="{{ $task->title ?? '' }}"
                                   placeholder="Task title...">
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea rows="6" 
                                      class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                      placeholder="Add a description...">{{ $task->description ?? '' }}</textarea>
                        </div>

                        <!-- Sub-tasks -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sub-tasks</label>
                            <div class="space-y-2">
                                @if(isset($task) && $task->subtasks)
                                    @foreach($task->subtasks as $subtask)
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                   {{ $subtask->status->type === 'done' ? 'checked' : '' }}>
                                            <label class="ml-2 text-sm text-gray-700">{{ $subtask->title }}</label>
                                        </div>
                                    @endforeach
                                @endif
                                <button class="text-sm text-indigo-600 hover:text-indigo-700">+ Add sub-task</button>
                            </div>
                        </div>

                        <!-- Comments -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Comments</label>
                            <div class="space-y-4">
                                @if(isset($task) && $task->comments)
                                    @foreach($task->comments as $comment)
                                        <div class="flex space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm">
                                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <div class="bg-gray-50 rounded-lg p-3">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                                        <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <p class="text-sm text-gray-700">{{ $comment->content }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                <div>
                                    <textarea rows="3" 
                                              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                              placeholder="Add a comment..."></textarea>
                                    <div class="mt-2 flex justify-end">
                                        <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                            Comment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar (Right) -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($statuses ?? [] as $status)
                                    <option value="{{ $status->id }}" 
                                            {{ (isset($task) && $task->status_id === $status->id) ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Assignees -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Assignees</label>
                            <select multiple 
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($users ?? [] as $user)
                                    <option value="{{ $user->id }}"
                                            {{ (isset($task) && $task->assignees->contains($user->id)) ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                            <select class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="none" {{ (isset($task) && $task->priority === 'none') ? 'selected' : '' }}>None</option>
                                <option value="low" {{ (isset($task) && $task->priority === 'low') ? 'selected' : '' }}>Low</option>
                                <option value="normal" {{ (isset($task) && $task->priority === 'normal') ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ (isset($task) && $task->priority === 'high') ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ (isset($task) && $task->priority === 'urgent') ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>

                        <!-- Due Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                            <input type="datetime-local" 
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ isset($task) && $task->due_date ? $task->due_date->format('Y-m-d\TH:i') : '' }}">
                        </div>

                        <!-- Time Tracking -->
                        <div class="border-t border-gray-200 pt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Time Tracking</label>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Estimated</span>
                                    <input type="number" 
                                           class="w-24 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                           placeholder="Hours"
                                           value="{{ isset($task) && $task->estimated_time ? round($task->estimated_time / 60, 1) : '' }}">
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Logged</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ isset($task) ? $task->getFormattedTimeLogged() : '0h' }}
                                    </span>
                                </div>
                                <button class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Start Timer
                                </button>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                            <div class="flex flex-wrap gap-2">
                                @if(isset($task) && $task->tags)
                                    @foreach($task->tags as $tag)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                              style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                @endif
                                <button class="text-sm text-indigo-600 hover:text-indigo-700">+ Add tag</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200">
                <button @click="closeTaskModal()" 
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </button>
                <button class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

