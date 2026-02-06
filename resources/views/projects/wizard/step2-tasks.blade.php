<div class="space-y-4">
    <!-- Task Grid -->
    <div class="max-h-96 overflow-y-auto">
        <template x-for="(task, index) in mainTasks" :key="task.id">
            <div class="border border-gray-200 rounded-lg p-4 mb-3">
                <!-- Task Header -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded">
                            Day <span x-text="task.day_number"></span>
                        </span>
                        <span class="text-sm font-medium text-gray-700" x-text="task.guest_name"></span>
                    </div>
                    <span class="text-xs text-gray-500" x-text="`Task ${index + 1} of ${mainTasks.length}`"></span>
                </div>

                <!-- Task Title -->
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Task Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           x-model="task.title"
                           placeholder="e.g., Build user authentication"
                           required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>

                <!-- Estimated Hours -->
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Estimated Hours <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500">(minimum 6 hours)</span>
                    </label>
                    <input type="number" 
                           x-model.number="task.estimated_hours"
                           min="6"
                           step="0.5"
                           required
                           class="block w-32 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <p x-show="task.estimated_hours < 6" class="mt-1 text-xs text-red-600">
                        Must be at least 6 hours
                    </p>
                </div>

                <!-- Subtasks Section -->
                <div class="border-t border-gray-200 pt-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Subtasks</span>
                        <button type="button"
                                @click="addSubtask(task.id)"
                                class="text-xs text-indigo-600 hover:text-indigo-800">
                            + Add Subtask
                        </button>
                    </div>

                    <div x-show="task.subtasks.length > 0" class="space-y-2">
                        <template x-for="subtask in task.subtasks" :key="subtask.id">
                            <div class="flex items-center space-x-2 bg-gray-50 p-2 rounded">
                                <input type="text" 
                                       x-model="subtask.title"
                                       placeholder="Subtask title"
                                       class="flex-1 border-gray-300 rounded text-sm">
                                <input type="number" 
                                       x-model.number="subtask.estimated_hours"
                                       placeholder="Hours"
                                       min="0.5"
                                       step="0.5"
                                       class="w-20 border-gray-300 rounded text-sm">
                                <button type="button"
                                        @click="removeSubtask(task.id, subtask.id)"
                                        class="text-red-600 hover:text-red-800">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <!-- Subtask Total -->
                        <div class="text-xs text-gray-600 bg-gray-50 p-2 rounded">
                            Subtask total: <strong x-text="calculateSubtaskTotal(task).toFixed(1)"></strong>h
                            <span x-show="task.subtasks.length > 0 && Math.abs(calculateSubtaskTotal(task) - task.estimated_hours) > 0.1"
                                  class="text-yellow-600 ml-2">
                                (Should equal main task: <span x-text="task.estimated_hours"></span>h)
                            </span>
                        </div>
                    </div>

                    <p x-show="task.subtasks.length === 0" class="text-xs text-gray-500 italic">
                        No subtasks yet. Click "+ Add Subtask" to break down this task.
                    </p>
                </div>
            </div>
        </template>
    </div>

    <!-- Validation Summary -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-yellow-900 mb-2">⚠️ Before Proceeding</h4>
        <ul class="text-xs text-yellow-700 space-y-1">
            <li>✓ All tasks must have a title</li>
            <li>✓ All tasks must have at least 6 hours</li>
            <li>✓ Subtask hours should match main task hours (recommended)</li>
        </ul>
    </div>
</div>
