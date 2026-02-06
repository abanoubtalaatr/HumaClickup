@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create New Training Project</h1>
            <p class="mt-1 text-sm text-gray-500">Set up a new project with guests, working days, and requirements</p>
        </div>

        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('projects.store') }}" method="POST" class="p-6 space-y-6" x-data="projectForm()">
                @csrf

                <!-- Basic Information -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h2>

                    <!-- Project Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Project Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required
                               placeholder="e.g., E-commerce Website"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="3"
                                  placeholder="Describe what this project is about..."
                                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Color & Icon -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="color" class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                            <input type="color" 
                                   id="color" 
                                   name="color" 
                                   value="{{ old('color', '#6366f1') }}"
                                   class="h-10 w-full border border-gray-300 rounded cursor-pointer">
                        </div>
                        <div>
                            <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">Icon (Emoji)</label>
                            <input type="text" 
                                   id="icon" 
                                   name="icon" 
                                   value="{{ old('icon', '📁') }}"
                                   placeholder="📁"
                                   maxlength="2"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-2xl text-center">
                        </div>
                    </div>
                </div>

                <!-- Planning & Schedule -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Planning & Schedule</h2>

                    <!-- Start Date & Total Days -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Start Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   id="start_date" 
                                   name="start_date" 
                                   x-model="start_date"
                                   value="{{ old('start_date', now()->format('Y-m-d')) }}"
                                   required
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="total_days" class="block text-sm font-medium text-gray-700 mb-2">
                                Total Days <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   id="total_days" 
                                   name="total_days" 
                                   x-model.number="total_days"
                                   value="{{ old('total_days', 20) }}"
                                   min="1"
                                   max="365"
                                   required
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="mt-1 text-xs text-gray-500">Typical: 20 days = 4 weeks</p>
                            @error('total_days')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Exclude Weekends -->
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="exclude_weekends" 
                                   value="1"
                                   checked
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">
                                Exclude weekends (Friday & Saturday) from working days
                            </span>
                        </label>
                        <p class="mt-1 ml-6 text-xs text-gray-500">Recommended: Check this box</p>
                    </div>

                    <!-- Advanced Settings (Collapsible) -->
                    <div x-data="{open: false}" class="border border-gray-200 rounded-lg p-4">
                        <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                            <span class="text-sm font-medium text-gray-700">Advanced Settings</span>
                            <svg class="h-5 w-5 text-gray-400" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" x-collapse class="mt-4 space-y-4">
                            <div>
                                <label for="min_task_hours" class="block text-sm font-medium text-gray-700 mb-2">
                                    Minimum Hours Per Main Task
                                </label>
                                <input type="number" 
                                       id="min_task_hours" 
                                       name="min_task_hours" 
                                       value="{{ old('min_task_hours', 6) }}"
                                       min="1"
                                       max="24"
                                       step="0.5"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="mt-1 text-xs text-gray-500">Default: 6 hours per day</p>
                            </div>

                            <div>
                                <label for="weekly_hours_target" class="block text-sm font-medium text-gray-700 mb-2">
                                    Weekly Hours Target Per Guest
                                </label>
                                <input type="number" 
                                       id="weekly_hours_target" 
                                       name="weekly_hours_target" 
                                       value="{{ old('weekly_hours_target', 30) }}"
                                       min="1"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="mt-1 text-xs text-gray-500">Default: 30 hours (5 days × 6 hours)</p>
                            </div>

                            <div>
                                <label for="bug_time_allocation_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                    Bug Time Allocation (%)
                                </label>
                                <input type="number" 
                                       id="bug_time_allocation_percentage" 
                                       name="bug_time_allocation_percentage" 
                                       value="{{ old('bug_time_allocation_percentage', 20) }}"
                                       min="0"
                                       max="50"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="mt-1 text-xs text-gray-500">Default: 20% of main task time</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guest/Group Selection -->
                <div class="border-b border-gray-200 pb-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">
                        Team Members <span class="text-red-500">*</span>
                    </h2>
                    
                    <!-- Selection Mode Tabs -->
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

                    @error('guest_members')
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        </div>
                    @enderror

                    <!-- Group Selection Mode -->
                    <div x-show="selectionMode === 'group'" class="mb-4">
                        @if($groups->isNotEmpty())
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-gray-700">Select a Group</label>
                            <input type="text" 
                                   x-model="groupSearch"
                                   placeholder="Search groups..."
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mb-2">
                            
                            <div class="max-h-60 overflow-y-auto space-y-2">
                                @foreach($groups as $group)
                                <div x-show="!groupSearch || '{{ strtolower($group->name) }}'.includes(groupSearch.toLowerCase())"
                                     class="p-3 border rounded-lg cursor-pointer hover:bg-indigo-50 hover:border-indigo-300"
                                     :class="selectedGroupId === {{ $group->id }} ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'"
                                     @click="selectGroup({{ $group->id }}, {{ json_encode($group->guests->map(fn($g) => ['user_id' => $g->id, 'track_id' => $g->pivot->track_id ?? null])) }})">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $group->name }}</p>
                                            <p class="text-sm text-gray-600">{{ $group->guests->count() }} members
                                                @if($group->track)
                                                    • {{ $group->track->name }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <span x-show="selectedGroupId === {{ $group->id }}" class="text-indigo-600 font-medium">Selected ✓</span>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="p-4 bg-gray-50 rounded-lg text-center">
                            <p class="text-sm text-gray-600">No groups available. Create a group first or select individual guests.</p>
                        </div>
                        @endif
                    </div>

                    <!-- Individual Guest Selection Mode -->
                    <div x-show="selectionMode === 'guests'">
                        <p class="text-sm text-gray-600 mb-4">
                            Select 3-5 students from different tracks to work on this project
                        </p>

                        <div class="mb-3">
                            <input type="text" 
                                   x-model="guestSearch"
                                   placeholder="Search guests..."
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div id="guest-members-container" class="space-y-3">
                            <template x-for="(member, index) in members" :key="index">
                                <div class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg">
                                    <!-- Guest Selection -->
                                    <div class="flex-1">
                                        <select :name="'guest_members[' + index + '][user_id]'" 
                                                x-model="member.user_id"
                                                required
                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Select Guest...</option>
                                            @foreach($guests as $guest)
                                                <option value="{{ $guest->id }}" 
                                                        x-show="!guestSearch || '{{ strtolower($guest->name) }}'.includes(guestSearch.toLowerCase())">
                                                    {{ $guest->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Track Selection -->
                                    <div class="flex-1">
                                        <select :name="'guest_members[' + index + '][track_id]'" 
                                                x-model="member.track_id"
                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Select Track...</option>
                                            @foreach($tracks as $track)
                                                <option value="{{ $track->id }}">{{ $track->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Remove Button -->
                                    <button type="button" 
                                            @click="removeMember(index)"
                                            x-show="members.length > 1"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Add Guest Button -->
                        <button type="button" 
                                @click="addMember()"
                                x-show="members.length < 5"
                                class="mt-3 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Guest Member
                        </button>
                    </div>

                    <!-- Calculation Preview -->
                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h3 class="text-sm font-medium text-blue-900 mb-2">Planning Preview</h3>
                        <div class="text-sm text-blue-700 space-y-1">
                            <p><strong>Guests:</strong> <span x-text="members.length"></span></p>
                            <p><strong>Total Days:</strong> <span x-text="total_days"></span></p>
                            <p><strong>Working Days (excl. weekends):</strong> ~<span x-text="estimatedWorkingDays"></span></p>
                            <p><strong>Required Main Tasks:</strong> <span x-text="requiredTasks"></span> (guests × working days)</p>
                            <p class="text-xs text-blue-600 mt-2">Each guest must complete 1 main task per day (≥6 hours)</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('projects.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Create Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function projectForm() {
    return {
        selectionMode: 'guests', // 'guests' or 'group'
        selectedGroupId: null,
        guestSearch: '',
        groupSearch: '',
        members: [
            { user_id: '', track_id: '' }
        ],
        start_date: '{{ old('start_date', now()->format('Y-m-d')) }}',
        total_days: {{ old('total_days', 20) }},

        get estimatedWorkingDays() {
            // Rough estimate: 5/7 of total days
            return Math.floor(this.total_days * (5/7));
        },

        get requiredTasks() {
            return this.members.length * this.estimatedWorkingDays;
        },

        selectGroup(groupId, groupMembers) {
            this.selectedGroupId = groupId;
            this.members = groupMembers.length > 0 ? groupMembers : [{ user_id: '', track_id: '' }];
        },

        addMember() {
            if (this.members.length < 5) {
                this.members.push({ user_id: '', track_id: '' });
            }
        },

        removeMember(index) {
            if (this.members.length > 1) {
                this.members.splice(index, 1);
            }
        }
    }
}
</script>
@endsection
