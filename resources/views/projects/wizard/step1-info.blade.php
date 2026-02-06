<!-- Project Name -->
<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Project Name <span class="text-red-500">*</span>
    </label>
    <input type="text" 
           x-model="projectName"
           placeholder="e.g., E-commerce Website"
           required
           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
</div>

<!-- Description -->
<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
    <textarea x-model="description" 
              rows="3"
              placeholder="Describe what this project is about..."
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
</div>

<!-- Dates -->
<div class="grid grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Start Date <span class="text-red-500">*</span>
        </label>
        <input type="date" 
               x-model="startDate"
               required
               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    </div>
    
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Total Days <span class="text-red-500">*</span>
        </label>
        <input type="number" 
               x-model.number="totalDays"
               min="1"
               max="365"
               required
               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        <p class="mt-1 text-xs text-gray-500">Typical: 20 days = 4 weeks</p>
    </div>
</div>

<!-- Exclude Weekends -->
<div class="mb-6">
    <label class="flex items-center">
        <input type="checkbox" 
               x-model="excludeWeekends"
               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <span class="ml-2 text-sm text-gray-700">
            Exclude weekends (Friday & Saturday) from working days
        </span>
    </label>
</div>

<!-- Guest/Group Selection Tabs -->
<div class="mb-4">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button type="button" 
                    @click="selectionMode = 'guests'"
                    :class="selectionMode === 'guests' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Select Individual Guests
            </button>
            @if($groups->isNotEmpty())
            <button type="button" 
                    @click="selectionMode = 'group'"
                    :class="selectionMode === 'group' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Select Group
            </button>
            @endif
        </nav>
    </div>
</div>

<!-- Individual Guests -->
<div x-show="selectionMode === 'guests'" class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Select Guests <span class="text-red-500">*</span>
    </label>
    <input type="text" 
           x-model="guestSearch"
           placeholder="Search guests..."
           class="block w-full mb-3 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    
    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-md p-2 space-y-1">
        @foreach($guests as $guest)
        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer"
               x-show="!guestSearch || '{{ strtolower($guest->name) }}'.includes(guestSearch.toLowerCase())">
            <input type="checkbox"
                   @change="if($event.target.checked) {
                       selectedMembers.push({user_id: {{ $guest->id }}, name: '{{ $guest->name }}', track_id: {{ $guest->pivot->track_id ?? 'null' }}})
                   } else {
                       selectedMembers = selectedMembers.filter(m => m.user_id !== {{ $guest->id }})
                   }"
                   class="rounded border-gray-300 text-indigo-600">
            <span class="ml-2 text-sm">{{ $guest->name }}</span>
        </label>
        @endforeach
    </div>
    
    <div class="mt-2 text-sm text-gray-600">
        Selected: <strong x-text="selectedMembers.length"></strong> guest(s)
    </div>
</div>

<!-- Group Selection -->
<div x-show="selectionMode === 'group'" class="mb-4">
    @if($groups->isNotEmpty())
    <label class="block text-sm font-medium text-gray-700 mb-2">Select a Group</label>
    <input type="text" 
           x-model="groupSearch"
           placeholder="Search groups..."
           class="block w-full mb-3 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    
    <div class="space-y-2">
        @foreach($groups as $group)
        <div x-show="!groupSearch || '{{ strtolower($group->name) }}'.includes(groupSearch.toLowerCase())"
             @click="selectGroup({{ $group->id }}, {{ json_encode($group->guests->map(fn($g) => ['user_id' => $g->id, 'name' => $g->name, 'track_id' => $g->pivot->track_id ?? null])) }})"
             class="p-4 border rounded-lg cursor-pointer hover:bg-indigo-50 hover:border-indigo-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-900">{{ $group->name }}</p>
                    <p class="text-sm text-gray-600">{{ $group->guests->count() }} members</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<!-- Preview -->
<div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
    <h3 class="text-sm font-medium text-blue-900 mb-2">Planning Summary</h3>
    <div class="text-sm text-blue-700 space-y-1">
        <p><strong>Guests:</strong> <span x-text="selectedMembers.length"></span></p>
        <p><strong>Working Days:</strong> ~<span x-text="workingDays"></span></p>
        <p><strong>Required Main Tasks:</strong> <span x-text="requiredTasks"></span></p>
        <p class="text-xs mt-2">Each task must be ≥ 6 hours</p>
    </div>
</div>
