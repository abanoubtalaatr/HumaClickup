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
        <h4 class="text-sm font-medium text-green-900 mb-2">✅ Ready to Create</h4>
        <p class="text-xs text-green-700">
            All requirements met. Click "Create Project" to finalize.
        </p>
    </div>
</div>
