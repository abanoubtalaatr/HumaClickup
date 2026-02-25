<div class="space-y-6">
    <!-- Project Summary -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="text-lg font-medium text-gray-900 mb-3">Project Summary</h3>
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Project Name</dt>
                <dd class="font-medium text-gray-900" x-text="projectName"></dd>
            </div>
            <div>
                <dt class="text-gray-500">Start Date</dt>
                <dd class="font-medium text-gray-900" x-text="startDate"></dd>
            </div>
            <div>
                <dt class="text-gray-500">Duration</dt>
                <dd class="font-medium text-gray-900">
                    <span x-text="totalDays"></span> days 
                    (<span x-text="workingDays"></span> working days)
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Team Size</dt>
                <dd class="font-medium text-gray-900">
                    <span x-text="selectedMembers.length"></span> guests
                </dd>
            </div>
        </dl>
    </div>

    <!-- Changes Summary -->
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
        <h3 class="text-lg font-medium text-amber-900 mb-3 flex items-center">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Changes Summary
        </h3>
        
        <!-- New Members -->
        <div x-show="selectedMembers.some(sm => !originalMembers.some(om => om.user_id === sm.user_id))" class="mb-3">
            <p class="text-sm font-medium text-green-800 mb-1">New Members Added:</p>
            <div class="flex flex-wrap gap-1">
                <template x-for="added in selectedMembers.filter(sm => !originalMembers.some(om => om.user_id === sm.user_id))" :key="added.user_id">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="added.name"></span>
                    </span>
                </template>
            </div>
        </div>
        
        <!-- Removed Members -->
        <div x-show="originalMembers.some(om => !selectedMembers.some(sm => sm.user_id === om.user_id))" class="mb-3">
            <p class="text-sm font-medium text-red-800 mb-1">Members Removed:</p>
            <div class="flex flex-wrap gap-1">
                <template x-for="removed in originalMembers.filter(om => !selectedMembers.some(sm => sm.user_id === om.user_id))" :key="removed.user_id">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span x-text="removed.name"></span>
                    </span>
                </template>
            </div>
        </div>
        
        <!-- No Changes -->
        <div x-show="!selectedMembers.some(sm => !originalMembers.some(om => om.user_id === sm.user_id)) && !originalMembers.some(om => !selectedMembers.some(sm => sm.user_id === om.user_id))">
            <p class="text-sm text-amber-700">No team member changes.</p>
        </div>

        <!-- Task Changes -->
        <div class="mt-3 pt-3 border-t border-amber-200">
            <p class="text-sm text-amber-700">
                <span class="font-medium" x-text="mainTasks.filter(t => t.db_id).length"></span> existing tasks will be updated, 
                <span class="font-medium" x-text="mainTasks.filter(t => !t.db_id).length"></span> new tasks will be created.
            </p>
        </div>
    </div>

    <!-- Team Members -->
    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-3">Team Members</h3>
        <div class="grid grid-cols-2 gap-2">
            <template x-for="member in selectedMembers" :key="member.user_id">
                <div class="flex items-center space-x-2 p-2 bg-gray-50 rounded">
                    <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-semibold">
                        <span x-text="member.name.substring(0,1).toUpperCase()"></span>
                    </div>
                    <span class="text-sm" x-text="member.name"></span>
                    <span x-show="originalMembers.some(om => om.user_id === member.user_id)" 
                          class="text-xs text-gray-400">existing</span>
                    <span x-show="!originalMembers.some(om => om.user_id === member.user_id)" 
                          class="text-xs text-green-600 font-medium">new</span>
                </div>
            </template>
        </div>
    </div>

    <!-- Tasks Summary -->
    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-3">Tasks Summary</h3>
        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="bg-indigo-50 rounded-lg p-3">
                <p class="text-2xl font-bold text-indigo-600" x-text="mainTasks.length"></p>
                <p class="text-xs text-indigo-700">Main Tasks</p>
            </div>
            <div class="bg-green-50 rounded-lg p-3">
                <p class="text-2xl font-bold text-green-600" 
                   x-text="mainTasks.reduce((sum, t) => sum + t.subtasks.length, 0)"></p>
                <p class="text-xs text-green-700">Subtasks</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-3">
                <p class="text-2xl font-bold text-purple-600" 
                   x-text="mainTasks.reduce((sum, t) => sum + parseFloat(t.estimated_hours), 0).toFixed(1) + 'h'"></p>
                <p class="text-xs text-purple-700">Total Hours</p>
            </div>
        </div>

        <!-- Tasks by Guest -->
        <div class="space-y-3">
            <template x-for="member in selectedMembers" :key="member.user_id">
                <div class="border border-gray-200 rounded-lg p-3">
                    <p class="font-medium text-gray-900 mb-2" x-text="member.name"></p>
                    <p class="text-xs text-gray-600">
                        <span x-text="mainTasks.filter(t => t.guest_user_id === member.user_id).length"></span> tasks, 
                        <span x-text="mainTasks.filter(t => t.guest_user_id === member.user_id).reduce((sum, t) => sum + parseFloat(t.estimated_hours), 0).toFixed(1)"></span> hours
                    </p>
                </div>
            </template>
        </div>
    </div>

    <!-- Final Check -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h4 class="text-sm font-medium text-green-900 mb-2">✅ Ready to Update</h4>
        <p class="text-xs text-green-700">
            Review your changes above. Click "Update Project" to save all changes.
        </p>
    </div>
</div>
