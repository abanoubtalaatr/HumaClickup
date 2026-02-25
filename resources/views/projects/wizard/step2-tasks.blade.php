<!-- Sticky Summary Bar -->
<div class="sticky top-0 z-10 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl p-4 mb-6 shadow-lg">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center space-x-6">
            <div>
                <p class="text-xs opacity-90">Total Tasks</p>
                <p class="text-2xl font-bold" x-text="mainTasks.length"></p>
            </div>
            <div>
                <p class="text-xs opacity-90">Completed</p>
                <p class="text-2xl font-bold" x-text="mainTasks.filter(t => t.title && t.estimated_hours >= 6).length"></p>
            </div>
            <div>
                <p class="text-xs opacity-90">Total Hours</p>
                <p class="text-2xl font-bold" x-text="mainTasks.reduce((sum, t) => sum + (t.estimated_hours || 0), 0).toFixed(1) + 'h'"></p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <div class="flex items-center space-x-2 bg-white bg-opacity-20 rounded-lg px-3 py-2">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-medium" x-text="`${Math.round((mainTasks.filter(t => t.title && t.estimated_hours >= 6).length / mainTasks.length) * 100)}% Ready`"></span>
            </div>
        </div>
    </div>
</div>

<!-- Tasks Grouped by Guest -->
<div class="space-y-6">
    <template x-for="(member, memberIndex) in selectedMembers" :key="member.user_id">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 hover:shadow-xl transition-shadow duration-200">
            <!-- Guest Header (Collapsible) -->
            <div @click="toggleGuestSection(member.user_id)" 
                 class="cursor-pointer bg-gradient-to-r from-gray-50 to-white border-b border-gray-200 p-5 hover:bg-gray-50 transition-colors"
                 :class="{
                     'border-l-4 border-l-blue-500': memberIndex === 0,
                     'border-l-4 border-l-green-500': memberIndex === 1,
                     'border-l-4 border-l-purple-500': memberIndex === 2,
                     'border-l-4 border-l-orange-500': memberIndex === 3,
                     'border-l-4 border-l-pink-500': memberIndex === 4
                 }">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Avatar -->
                        <div class="h-12 w-12 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md"
                             :class="{
                                 'bg-blue-500': memberIndex === 0,
                                 'bg-green-500': memberIndex === 1,
                                 'bg-purple-500': memberIndex === 2,
                                 'bg-orange-500': memberIndex === 3,
                                 'bg-pink-500': memberIndex === 4
                             }">
                            <span x-text="member.name.substring(0, 2).toUpperCase()"></span>
                        </div>
                        
                        <!-- Guest Info -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900" x-text="member.name"></h3>
                            <p class="text-sm text-gray-600">
                                <span x-text="mainTasks.filter(t => t.guest_user_id === member.user_id).length"></span> tasks • 
                                <span x-text="mainTasks.filter(t => t.guest_user_id === member.user_id).reduce((sum, t) => sum + (t.estimated_hours || 0), 0).toFixed(1)"></span>h total
                            </p>
                        </div>
                    </div>
                    
                    <!-- Progress & Toggle -->
                    <div class="flex items-center space-x-4">
                        <!-- Progress Ring -->
                        <div class="text-center">
                            <div class="relative inline-flex items-center justify-center">
                                <svg class="h-16 w-16 transform -rotate-90">
                                    <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none" class="text-gray-200"/>
                                    <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none" 
                                            :stroke-dasharray="`${(mainTasks.filter(t => t.guest_user_id === member.user_id && t.title && t.estimated_hours >= 6).length / mainTasks.filter(t => t.guest_user_id === member.user_id).length) * 175.93}, 175.93`"
                                            :class="{
                                                'text-blue-500': memberIndex === 0,
                                                'text-green-500': memberIndex === 1,
                                                'text-purple-500': memberIndex === 2,
                                                'text-orange-500': memberIndex === 3,
                                                'text-pink-500': memberIndex === 4
                                            }"/>
                                </svg>
                                <span class="absolute text-sm font-bold text-gray-700" 
                                      x-text="`${Math.round((mainTasks.filter(t => t.guest_user_id === member.user_id && t.title && t.estimated_hours >= 6).length / mainTasks.filter(t => t.guest_user_id === member.user_id).length) * 100)}%`"></span>
                            </div>
                        </div>
                        
                        <!-- Chevron -->
                        <svg class="h-6 w-6 text-gray-400 transition-transform duration-200" 
                             :class="{ 'transform rotate-180': !expandedGuests[member.user_id] }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
            
            <!-- Guest Tasks (Collapsible Content) -->
            <div x-show="!expandedGuests[member.user_id]" 
                 x-collapse
                 class="p-5 space-y-4 bg-gray-50">
                <template x-for="(task, taskIndex) in mainTasks.filter(t => t.guest_user_id === member.user_id)" :key="task.id">
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-200">
                        <!-- Task Card Header -->
                        <div class="bg-gradient-to-r from-gray-50 to-white px-4 py-3 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="flex items-center justify-center h-8 w-8 rounded-full text-white font-bold text-xs shadow"
                                          :class="{
                                              'bg-blue-500': memberIndex === 0,
                                              'bg-green-500': memberIndex === 1,
                                              'bg-purple-500': memberIndex === 2,
                                              'bg-orange-500': memberIndex === 3,
                                              'bg-pink-500': memberIndex === 4
                                          }">
                                        <span x-text="task.day_number"></span>
                                    </span>
                                    <span class="text-sm font-semibold text-gray-700">Day <span x-text="task.day_number"></span></span>
                                </div>
                                
                                <!-- Validation Status -->
                                <div>
                                    <span x-show="task.title && task.estimated_hours >= 6" 
                                          class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Valid
                                    </span>
                                    <span x-show="!task.title || task.estimated_hours < 6" 
                                          class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        Incomplete
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Task Card Body -->
                        <div class="p-4">
                            <!-- Task Title -->
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    📝 Task Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="task.title"
                                       placeholder="e.g., Build user authentication system"
                                       required
                                       class="block w-full px-5 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-base font-medium transition-all"
                                       :class="{ 'border-red-300 bg-red-50': !task.title }">
                            </div>

                            <!-- Task Description -->
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    📄 Description
                                </label>
                                <textarea 
                                       x-model="task.description"
                                       :id="'task-desc-' + task.id"
                                       rows="4"
                                       placeholder="Describe the task in detail..."
                                       class="tinymce-editor block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-base leading-relaxed transition-all"></textarea>
                            </div>

                            <!-- Estimated Hours -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    ⏱️ Estimated Hours <span class="text-red-500">*</span>
                                    <span class="text-xs text-gray-500 font-normal">(minimum 6 hours)</span>
                                </label>
                                <div class="flex items-center space-x-3">
                                    <input type="number" 
                                           x-model.number="task.estimated_hours"
                                           min="6"
                                           step="0.5"
                                           required
                                           class="block w-32 px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-semibold transition-all"
                                           :class="{ 'border-red-300 bg-red-50': task.estimated_hours < 6 }">
                                    <span class="text-sm text-gray-600">hours</span>
                                </div>
                                <p x-show="task.estimated_hours < 6" class="mt-2 text-xs text-red-600 flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Must be at least 6 hours
                                </p>
                            </div>

                            <!-- Subtasks Section (Collapsible) -->
                            <div class="border-t border-gray-200 pt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <button type="button"
                                            @click="toggleSubtasks(task.id)"
                                            class="flex items-center space-x-2 text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">
                                        <svg class="h-5 w-5 transition-transform duration-200" 
                                             :class="{ 'transform rotate-180': !expandedSubtasks[task.id] }"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                        <span>Subtasks (<span x-text="task.subtasks.length"></span>)</span>
                                    </button>
                                    <button type="button"
                                            @click="addSubtask(task.id); expandedSubtasks[task.id] = false;"
                                            class="inline-flex items-center px-3 py-1.5 border border-indigo-300 rounded-lg text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Add Subtask
                                    </button>
                                </div>

                                <!-- Subtasks List (Collapsible) -->
                                <div x-show="!expandedSubtasks[task.id]" 
                                     x-collapse
                                     class="space-y-2 mt-3">
                                    <template x-for="(subtask, subIndex) in task.subtasks" :key="subtask.id">
                                        <div class="bg-gradient-to-r from-gray-50 to-white p-4 rounded-lg border border-gray-200 hover:border-indigo-300 transition-colors">
                                            <div class="flex items-center space-x-2 mb-3">
                                                <span class="flex items-center justify-center h-7 w-7 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold" x-text="subIndex + 1"></span>
                                                <input type="text" 
                                                       x-model="subtask.title"
                                                       placeholder="Subtask title..."
                                                       class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-base font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                <input type="number" 
                                                       x-model.number="subtask.estimated_hours"
                                                       placeholder="Hours"
                                                       min="0.5"
                                                       step="0.5"
                                                       class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-base font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                <button type="button"
                                                        @click="removeSubtask(task.id, subtask.id)"
                                                        class="flex items-center justify-center h-9 w-9 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="ml-9">
                                                <textarea 
                                                       x-model="subtask.description"
                                                       :id="'subtask-desc-' + task.id + '-' + subtask.id"
                                                       rows="2"
                                                       placeholder="Subtask description (optional)..."
                                                       class="tinymce-editor block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm leading-relaxed focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Subtask Total -->
                                    <div x-show="task.subtasks.length > 0" 
                                         class="mt-3 p-3 rounded-lg"
                                         :class="{
                                             'bg-green-50 border border-green-200': Math.abs(calculateSubtaskTotal(task) - task.estimated_hours) <= 0.1,
                                             'bg-yellow-50 border border-yellow-200': task.subtasks.length > 0 && Math.abs(calculateSubtaskTotal(task) - task.estimated_hours) > 0.1
                                         }">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="font-medium text-gray-700">Subtask Total:</span>
                                            <span class="font-bold" x-text="calculateSubtaskTotal(task).toFixed(1) + 'h'"></span>
                                        </div>
                                        <div x-show="Math.abs(calculateSubtaskTotal(task) - task.estimated_hours) <= 0.1" 
                                             class="mt-1 text-xs text-green-700 flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            ✓ Matches main task estimation
                                        </div>
                                        <div x-show="task.subtasks.length > 0 && Math.abs(calculateSubtaskTotal(task) - task.estimated_hours) > 0.1" 
                                             class="mt-1 text-xs text-yellow-700 flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            Should equal <span x-text="task.estimated_hours" class="font-semibold mx-1"></span>h (main task)
                                        </div>
                                    </div>
                                    
                                    <p x-show="task.subtasks.length === 0" class="text-xs text-gray-500 italic text-center py-3">
                                        No subtasks yet. Click "Add Subtask" to break down this task.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>

<!-- Enhanced Validation Summary -->
<div class="mt-6 bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-400 rounded-lg p-5 shadow-md">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <h4 class="text-sm font-semibold text-yellow-900 mb-2">⚠️ Validation Requirements</h4>
            <ul class="text-xs text-yellow-800 space-y-1.5">
                <li class="flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    All tasks must have a title
                </li>
                <li class="flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    All tasks must have at least 6 hours estimation
                </li>
                <li class="flex items-center">
                    <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Subtask hours should match main task hours (recommended, not required)
                </li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:initialized', () => {
    // Initialize TinyMCE for description fields when Step 2 is shown
    const initTinyMCE = () => {
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: 'textarea.tinymce-editor',
                menubar: false,
                height: 200,
                plugins: 'lists link code',
                toolbar: 'undo redo | bold italic | bullist numlist | link | removeformat',
                branding: false,
                statusbar: false,
                content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; line-height: 1.6; }',
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save(); // Sync content back to textarea (Alpine x-model)
                    });
                }
            });
        }
    };

    // Initialize when moving to Step 2
    window.addEventListener('step-changed', (e) => {
        if (e.detail === 2) {
            setTimeout(initTinyMCE, 300); // Delay to ensure DOM is ready
        }
    });
});
</script>
@endpush
