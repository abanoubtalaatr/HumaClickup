<!-- Project Name -->
<div class="mb-6">
    <label class="block text-sm font-semibold text-gray-700 mb-2">
        📌 Project Name <span class="text-red-500">*</span>
    </label>
    <input type="text" 
           x-model="projectName"
           placeholder="e.g., E-commerce Website"
           required
           class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-base"
           :class="{ 'border-red-300 bg-red-50': !projectName }">
</div>

<!-- Description -->
<div class="mb-6">
    <label class="block text-sm font-semibold text-gray-700 mb-2">📄 Description</label>
    <textarea x-model="description" 
              rows="3"
              placeholder="Describe what this project is about..."
              class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-base"></textarea>
</div>

<!-- Dates -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
            📅 Start Date <span class="text-red-500">*</span>
        </label>
        <input type="date" 
               x-model="startDate"
               required
               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
    </div>
    
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
            🕐 Total Days <span class="text-red-500">*</span>
        </label>
        <input type="number" 
               x-model.number="totalDays"
               min="1"
               max="365"
               required
               class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-base font-semibold">
        <p class="mt-2 text-xs text-gray-500">💡 Typical: 20 days = 4 weeks</p>
    </div>
</div>

<!-- Exclude Weekends -->
<div class="mb-6">
    <label class="flex items-center p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-200 cursor-pointer hover:border-indigo-300 transition-all">
        <input type="checkbox" 
               x-model="excludeWeekends"
               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-5 w-5">
        <span class="ml-3 text-sm font-medium text-gray-700">
            🏖️ Exclude weekends (Friday & Saturday) from working days
        </span>
    </label>
</div>

<!-- Guest/Group Selection Tabs -->
<div class="mb-6">
    <div class="bg-gray-100 rounded-xl p-1">
        <nav class="flex space-x-2">
            <button type="button" 
                    @click="selectionMode = 'guests'"
                    :class="selectionMode === 'guests' ? 'bg-white text-indigo-600 shadow-md' : 'text-gray-600 hover:text-gray-900'"
                    class="flex-1 py-3 px-4 rounded-lg font-medium text-sm transition-all duration-200">
                👥 Select Individual Guests
            </button>
            @if($groups->isNotEmpty())
            <button type="button" 
                    @click="selectionMode = 'group'"
                    :class="selectionMode === 'group' ? 'bg-white text-indigo-600 shadow-md' : 'text-gray-600 hover:text-gray-900'"
                    class="flex-1 py-3 px-4 rounded-lg font-medium text-sm transition-all duration-200">
                👨‍👩‍👧‍👦 Select Group
            </button>
            @endif
        </nav>
    </div>
</div>

<!-- Individual Guests (with pre-selected existing guests) -->
<div x-show="selectionMode === 'guests'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     class="mb-4">
    <label class="block text-sm font-semibold text-gray-700 mb-3">
        Select Guests <span class="text-red-500">*</span>
    </label>
    
    <!-- Search Box -->
    <div class="relative mb-3">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <input type="text" 
               x-model="guestSearch"
               placeholder="Search guests by name..."
               class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
    </div>
    
    <!-- Guests List -->
    <div class="max-h-80 overflow-y-auto border-2 border-gray-200 rounded-xl p-3 space-y-2 bg-gray-50">
        @foreach($guests as $guest)
        <label class="flex items-center p-3 bg-white hover:bg-indigo-50 rounded-lg cursor-pointer transition-all duration-200 border border-gray-200 hover:border-indigo-300 hover:shadow-md"
               x-show="!guestSearch || '{{ strtolower($guest->name) }}'.includes(guestSearch.toLowerCase())">
            <input type="checkbox"
                   :checked="selectedMembers.some(m => m.user_id === {{ $guest->id }})"
                   @change="if($event.target.checked) {
                       if (!selectedMembers.some(m => m.user_id === {{ $guest->id }})) {
                           selectedMembers.push({user_id: {{ $guest->id }}, name: '{{ addslashes($guest->name) }}', track_id: {{ $guest->pivot->track_id ?? 'null' }}})
                       }
                   } else {
                       selectedMembers = selectedMembers.filter(m => m.user_id !== {{ $guest->id }})
                   }"
                   class="rounded border-gray-300 text-indigo-600 h-5 w-5 focus:ring-2 focus:ring-indigo-500">
            <div class="ml-3 flex items-center space-x-2">
                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm">
                    {{ strtoupper(substr($guest->name, 0, 1)) }}
                </div>
                <span class="text-sm font-medium text-gray-900">{{ $guest->name }}</span>
                @if(in_array($guest->id, $projectGuests->pluck('user_id')->toArray()))
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                        Current Member
                    </span>
                @endif
            </div>
        </label>
        @endforeach
    </div>
    
    <!-- Selection Counter -->
    <div class="mt-3 flex items-center justify-between p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
        <span class="text-sm font-medium text-green-900">Selected:</span>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-600 text-white shadow-md">
            <span x-text="selectedMembers.length"></span> guest(s)
        </span>
    </div>
</div>

<!-- Group Selection -->
<div x-show="selectionMode === 'group'" 
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     class="mb-4">
    @if($groups->isNotEmpty())
    <label class="block text-sm font-semibold text-gray-700 mb-3">Select a Group</label>
    
    <!-- Search Box -->
    <div class="relative mb-3">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <input type="text" 
               x-model="groupSearch"
               placeholder="Search groups by name..."
               class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
    </div>
    
    <!-- Groups List -->
    <div class="space-y-3">
        @foreach($groups as $group)
        <div x-show="!groupSearch || '{{ strtolower($group->name) }}'.includes(groupSearch.toLowerCase())"
             @click="selectGroup({{ $group->id }}, {{ json_encode($group->guests->map(fn($g) => ['user_id' => $g->id, 'name' => $g->name, 'track_id' => $g->pivot->track_id ?? null])) }})"
             class="p-5 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 hover:border-indigo-300 transition-all duration-200 hover:shadow-lg group">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="h-12 w-12 rounded-full bg-gradient-to-r from-purple-500 to-indigo-500 flex items-center justify-center text-white font-bold text-lg shadow-md">
                        {{ strtoupper(substr($group->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ $group->name }}</p>
                        <p class="text-sm text-gray-600 flex items-center mt-1">
                            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                            </svg>
                            {{ $group->guests->count() }} members
                        </p>
                    </div>
                </div>
                <svg class="h-6 w-6 text-gray-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-8 text-gray-500">
        <svg class="h-12 w-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <p class="text-sm">No groups available</p>
    </div>
    @endif
</div>

<!-- Warning for removed members -->
<div x-show="originalMembers.some(om => !selectedMembers.some(sm => sm.user_id === om.user_id))"
     class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
    <div class="flex items-start">
        <svg class="h-5 w-5 text-red-500 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div>
            <h4 class="text-sm font-semibold text-red-800">Warning: Members Removed</h4>
            <p class="text-xs text-red-700 mt-1">Removing members will delete their assigned tasks from this project. This action cannot be undone.</p>
            <div class="mt-2 space-y-1">
                <template x-for="removed in originalMembers.filter(om => !selectedMembers.some(sm => sm.user_id === om.user_id))" :key="removed.user_id">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-1">
                        <span x-text="removed.name"></span>
                    </span>
                </template>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Preview -->
<div class="mt-6 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl shadow-md">
    <div class="flex items-center mb-3">
        <svg class="h-6 w-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <h3 class="text-base font-bold text-blue-900">📊 Planning Summary</h3>
    </div>
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-lg p-3 shadow-sm">
            <p class="text-xs text-gray-600 mb-1">Team Size</p>
            <p class="text-2xl font-bold text-indigo-600" x-text="selectedMembers.length"></p>
            <p class="text-xs text-gray-500">guests</p>
        </div>
        <div class="bg-white rounded-lg p-3 shadow-sm">
            <p class="text-xs text-gray-600 mb-1">Working Days</p>
            <p class="text-2xl font-bold text-green-600" x-text="workingDays"></p>
            <p class="text-xs text-gray-500">days</p>
        </div>
        <div class="bg-white rounded-lg p-3 shadow-sm">
            <p class="text-xs text-gray-600 mb-1">Total Tasks</p>
            <p class="text-2xl font-bold text-purple-600" x-text="requiredTasks"></p>
            <p class="text-xs text-gray-500">tasks</p>
        </div>
    </div>
    <p class="mt-3 text-xs text-blue-700 flex items-center">
        <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        Each task must be ≥ 6 hours
    </p>
</div>
