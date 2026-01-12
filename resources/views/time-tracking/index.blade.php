@extends('layouts.app')

@section('title', 'Time Tracking')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ 
    showStartTimerModal: false, 
    showManualEntryModal: false,
    selectedTaskId: '',
    timerDescription: '',
    isLoading: false,
    manualEntry: {
        task_id: '',
        start_time: '',
        end_time: '',
        description: ''
    },
    async startTimer() {
        if (!this.selectedTaskId) return;
        this.isLoading = true;
        try {
            const response = await fetch('{{ route('time-tracking.start') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({
                    task_id: this.selectedTaskId,
                    description: this.timerDescription
                })
            });
            const data = await response.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to start timer');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to start timer');
        } finally {
            this.isLoading = false;
        }
    },
    async createManualEntry() {
        if (!this.manualEntry.task_id || !this.manualEntry.start_time || !this.manualEntry.end_time) return;
        this.isLoading = true;
        try {
            const response = await fetch('{{ route('time-tracking.manual') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify(this.manualEntry)
            });
            const data = await response.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to create time entry');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to create time entry');
        } finally {
            this.isLoading = false;
        }
    }
}">
    <div class="py-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Time Tracking</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Track and manage your time</p>
            </div>
            <div class="flex items-center space-x-3">
                @php
                    $workspaceId = session('current_workspace_id');
                    $isMember = auth()->user()->isMemberOnlyInWorkspace($workspaceId);
                @endphp
                @if($isMember)
                <a href="{{ route('time-tracking.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    My Team Tracking
                </a>
                @endif
                <button @click="showStartTimerModal = true"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Start Timer
                </button>
            </div>
        </div>

        <!-- Active Timer -->
        @if($activeTimer)
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-red-900">Timer Running</h3>
                    <p class="text-sm text-red-700 mt-1">
                        Task: {{ $activeTimer->task->title ?? 'Unknown Task' }}
                    </p>
                    @if($activeTimer->task->project)
                        <p class="text-xs text-red-600 mt-0.5">
                            Project: {{ $activeTimer->task->project->name }}
                        </p>
                    @endif
                    <p class="text-sm text-red-600 mt-1" x-data="{ elapsed: 0 }" x-init="setInterval(() => elapsed = Math.floor((new Date() - new Date('{{ $activeTimer->start_time->toIso8601String() }}')) / 1000), 1000)">
                        Elapsed: <span class="font-mono font-bold" x-text="Math.floor(elapsed / 3600).toString().padStart(2, '0') + ':' + Math.floor((elapsed % 3600) / 60).toString().padStart(2, '0') + ':' + (elapsed % 60).toString().padStart(2, '0')"></span>
                    </p>
                </div>
                <form action="{{ route('time-tracking.stop') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        <svg class="h-4 w-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                        </svg>
                        Stop Timer
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Summary -->
        @if(isset($summary))
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">This Week</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $summary['total_formatted'] ?? '0h' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Entries</dt>
                                <dd class="text-2xl font-bold text-gray-900">{{ $summary['entries_count'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Time Entries -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Recent Time Entries</h3>
                    <button @click="showManualEntryModal = true"
                            class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                        + Add Manual Entry
                    </button>
                </div>
                <div class="space-y-4">
                    @forelse($timeEntries ?? [] as $entry)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $entry->task->title ?? 'Unknown Task' }}</p>
                                <p class="text-sm text-gray-500">{{ $entry->task->project->name ?? 'No Project' }}</p>
                                @if($entry->description)
                                    <p class="text-xs text-gray-400 mt-1">{{ $entry->description }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900">{{ $entry->getFormattedDuration() }}</p>
                                <p class="text-xs text-gray-500">{{ $entry->start_time->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No time entries yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Start a timer or add a manual entry to begin tracking time.</p>
                            <div class="mt-6">
                                <button @click="showStartTimerModal = true"
                                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    </svg>
                                    Start Timer
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Start Timer Modal -->
    <div x-show="showStartTimerModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             x-show="showStartTimerModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showStartTimerModal = false"></div>

        <!-- Modal panel -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="showStartTimerModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @click.stop
                     class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100">
                            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900" id="modal-title">Start Timer</h3>
                            <div class="mt-4 text-left">
                                <div class="mb-4">
                                    <label for="task_id" class="block text-sm font-medium text-gray-700 mb-2">Select Task</label>
                                    <select id="task_id" 
                                            x-model="selectedTaskId"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Choose a task...</option>
                                        @foreach($tasks ?? [] as $task)
                                            <option value="{{ $task->id }}">
                                                {{ $task->title }} ({{ $task->project->name ?? 'No Project' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description (optional)</label>
                                    <input type="text" 
                                           id="description" 
                                           x-model="timerDescription"
                                           placeholder="What are you working on?"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                        <button type="button"
                                @click="startTimer"
                                :disabled="!selectedTaskId || isLoading"
                                class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isLoading">Start Timer</span>
                            <span x-show="isLoading">Starting...</span>
                        </button>
                        <button type="button"
                                @click="showStartTimerModal = false"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Entry Modal -->
    <div x-show="showManualEntryModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             x-show="showManualEntryModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showManualEntryModal = false"></div>

        <!-- Modal panel -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="showManualEntryModal"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     @click.stop
                     class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div>
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900">Add Manual Time Entry</h3>
                            <div class="mt-4 text-left">
                                <div class="mb-4">
                                    <label for="manual_task_id" class="block text-sm font-medium text-gray-700 mb-2">Select Task</label>
                                    <select id="manual_task_id" 
                                            x-model="manualEntry.task_id"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Choose a task...</option>
                                        @foreach($tasks ?? [] as $task)
                                            <option value="{{ $task->id }}">
                                                {{ $task->title }} ({{ $task->project->name ?? 'No Project' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                                        <input type="datetime-local" 
                                               id="start_time" 
                                               x-model="manualEntry.start_time"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                                        <input type="datetime-local" 
                                               id="end_time" 
                                               x-model="manualEntry.end_time"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="manual_description" class="block text-sm font-medium text-gray-700 mb-2">Description (optional)</label>
                                    <input type="text" 
                                           id="manual_description" 
                                           x-model="manualEntry.description"
                                           placeholder="What did you work on?"
                                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               x-model="manualEntry.is_billable"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Billable</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                        <button type="button"
                                @click="createManualEntry"
                                :disabled="!manualEntry.task_id || !manualEntry.start_time || !manualEntry.end_time || isLoading"
                                class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 sm:col-start-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isLoading">Add Entry</span>
                            <span x-show="isLoading">Adding...</span>
                        </button>
                        <button type="button"
                                @click="showManualEntryModal = false"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
