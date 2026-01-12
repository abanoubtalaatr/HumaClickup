@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="mt-1 text-gray-500 dark:text-gray-400">Here's your productivity overview</p>
        </div>

        <!-- Work Instructions in Arabic -->
        <div class="mb-8 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-2 border-blue-200 dark:border-blue-800 rounded-xl p-6 shadow-lg">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1" dir="rtl">
                    <h2 class="text-xl font-bold text-blue-900 dark:text-blue-100 mb-4">تعليمات العمل اليومية</h2>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-blue-900 dark:text-blue-100 leading-relaxed">
                                يجب قضاء <span class="font-bold text-blue-700 dark:text-blue-300">6 ساعات على الأقل يومياً</span> في العمل على المهام التطويرية
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-blue-900 dark:text-blue-100 leading-relaxed">
                                الالتزام بالعمل <span class="font-bold text-blue-700 dark:text-blue-300">5 أيام في الأسبوع</span> على مدار الشهر
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-red-900 dark:text-red-100 leading-relaxed">
                                ما تعديش العدد المسموح بيه في الغياب خلال الشهر <span class="font-bold text-red-700 dark:text-red-300">3 مرات فقط</span>
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-blue-900 dark:text-blue-100 leading-relaxed">
                                <span class="font-bold text-blue-700 dark:text-blue-300">التزامك بالتاسكات</span> والتسليم في المواعيد المحددة
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-blue-900 dark:text-blue-100 leading-relaxed">
                                <span class="font-bold text-blue-700 dark:text-blue-300">التواصل مع باقي أعضاء الفريق بدون مشاكل</span> والتعاون الجماعي
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-700">
                        <div class="flex items-start space-x-3 space-x-reverse bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                            <div class="flex-shrink-0 mt-0.5">
                                <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-red-900 dark:text-red-100 leading-relaxed font-semibold">
                                ⚠️ لو ما التزمتش بالتعليمات دي <span class="text-red-700 dark:text-red-300">مش هتقدر تاخد الشهادة</span>، وأي شركة هتسأل عنك أكيد هنبلغها بمواصفاتك
                            </p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-xs text-blue-700 dark:text-blue-300 font-medium">
                            💡 الرجاء الالتزام بهذه التعليمات لضمان سير العمل بشكل سليم
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estimation Polling Section -->
        @if($estimationPollingTasks->count() > 0)
        <div class="mb-8 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6" x-data="estimationPolling()">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-amber-900">Task Estimation Polling</h2>
                        <p class="text-sm text-amber-700">Submit your time estimates for the following tasks</p>
                    </div>
                </div>
                <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-1 rounded-full">
                    {{ $estimationPollingTasks->where('has_estimated', false)->count() }} pending
                </span>
            </div>

            <div class="space-y-3">
                @foreach($estimationPollingTasks as $item)
                    <div class="bg-white rounded-lg border border-amber-100 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <h3 class="font-medium text-gray-900">{{ $item['task']->title }}</h3>
                                    @if($item['has_estimated'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Submitted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                            Awaiting your estimate
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 mt-1">{{ $item['task']->project?->name }}</p>
                                
                                <!-- Progress indicator -->
                                <div class="mt-2 flex items-center space-x-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-xs">
                                        <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $item['progress']['percentage'] }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $item['progress']['submitted'] }}/{{ $item['progress']['total'] }} submitted</span>
                                </div>
                            </div>

                            <div class="ml-4">
                                @if($item['has_estimated'])
                                    <div class="text-right">
                                        <p class="text-sm text-gray-500">Your estimate</p>
                                        <p class="text-lg font-semibold text-green-600">{{ $item['my_estimation']->getFormattedEstimation() }}</p>
                                        <button @click="openEditModal({{ $item['task']->id }}, {{ $item['my_estimation']->estimated_minutes }})" 
                                                class="text-xs text-indigo-600 hover:text-indigo-800">Edit</button>
                                    </div>
                                @else
                                    <button @click="openSubmitModal({{ $item['task']->id }}, '{{ addslashes($item['task']->title) }}')" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Submit Estimate
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Estimation Submit Modal -->
            <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>
                    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" @click.stop>
                        <div class="absolute right-4 top-4">
                            <button @click="closeModal()" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-amber-100 rounded-lg">
                                    <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Submit Time Estimate</h3>
                                    <p class="text-sm text-gray-500" x-text="taskTitle"></p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submitEstimation()">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Hours</label>
                                        <input type="number" x-model="estimatedHours" min="0" max="999" 
                                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                               placeholder="0">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Minutes</label>
                                        <input type="number" x-model="estimatedMinutes" min="0" max="59"
                                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                               placeholder="0">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                                    <textarea x-model="notes" rows="2"
                                              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                                              placeholder="Any assumptions or notes about this estimate..."></textarea>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <button type="button" @click="closeModal()" 
                                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="submitting"
                                        class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 disabled:opacity-50">
                                    <span x-show="!submitting">Submit Estimate</span>
                                    <span x-show="submitting">Submitting...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Time Tracking Summary Cards -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">My Time Summary</h2>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-5 text-white">
                    <div class="text-sm opacity-80">Today</div>
                    <div class="text-2xl font-bold mt-1">{{ $timeSummaries['today']['total_formatted'] }}</div>
                    <div class="text-xs mt-2 opacity-60">{{ $timeSummaries['today']['hours'] }}h {{ $timeSummaries['today']['minutes'] % 60 }}m</div>
                </div>
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-lg p-5 text-white">
                    <div class="text-sm opacity-80">This Week</div>
                    <div class="text-2xl font-bold mt-1">{{ $timeSummaries['this_week']['total_formatted'] }}</div>
                    <div class="text-xs mt-2 opacity-60">{{ $timeSummaries['this_week']['hours'] }}h total</div>
                </div>
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-5 text-white">
                    <div class="text-sm opacity-80">Last 2 Weeks</div>
                    <div class="text-2xl font-bold mt-1">{{ $timeSummaries['two_weeks']['total_formatted'] }}</div>
                    <div class="text-xs mt-2 opacity-60">{{ $timeSummaries['two_weeks']['hours'] }}h total</div>
                </div>
                <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl shadow-lg p-5 text-white">
                    <div class="text-sm opacity-80">Last 3 Weeks</div>
                    <div class="text-2xl font-bold mt-1">{{ $timeSummaries['three_weeks']['total_formatted'] }}</div>
                    <div class="text-xs mt-2 opacity-60">{{ $timeSummaries['three_weeks']['hours'] }}h total</div>
                </div>
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-5 text-white">
                    <div class="text-sm opacity-80">Last Month</div>
                    <div class="text-2xl font-bold mt-1">{{ $timeSummaries['four_weeks']['total_formatted'] }}</div>
                    <div class="text-xs mt-2 opacity-60">{{ $timeSummaries['four_weeks']['hours'] }}h total</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- My Pending Tasks -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">My Pending Tasks</h2>
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $pendingTasks->count() }} tasks
                    </span>
                </div>
                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                    @forelse($pendingTasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="flex-shrink-0 w-3 h-3 rounded-full" style="background-color: {{ $task->status?->color ?? '#94a3b8' }}"></span>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                        <p class="text-xs text-gray-500">{{ $task->project?->name }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($task->due_date)
                                        <span class="text-xs {{ $task->due_date->isPast() ? 'text-red-600 font-medium' : 'text-gray-500' }}">
                                            {{ $task->due_date->format('M d') }}
                                        </span>
                                    @endif
                                    @if($task->priority && $task->priority !== 'none')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $task->priority === 'high' ? 'bg-orange-100 text-orange-800' : '' }}
                                            {{ $task->priority === 'normal' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $task->priority === 'low' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ ucfirst($task->priority) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">All caught up! No pending tasks.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- My Projects -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">My Projects</h2>
                </div>
                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                    @forelse($assignedProjects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="block px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center" 
                                     style="background-color: {{ $project->color ?? '#6366f1' }}20">
                                    <span class="text-lg" style="color: {{ $project->color ?? '#6366f1' }}">
                                        {{ $project->icon ?? '📁' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $project->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $project->tasks_count }} assigned tasks</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <p class="text-sm text-gray-500">No projects assigned yet.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Time Entries -->
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Recent Time Entries</h2>
                <a href="{{ route('time-tracking.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                    View all →
                </a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentTimeEntries as $entry)
                    <div class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $entry->task?->title ?? 'No task' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $entry->task?->project?->name }} • {{ $entry->description ?? 'No description' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">{{ $entry->getFormattedDuration() }}</p>
                                <p class="text-xs text-gray-500">{{ $entry->start_time->format('M d, H:i') }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm text-gray-500">No time entries yet. Start tracking your work!</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Completed Tasks -->
        @if($completedTasks->count() > 0)
            <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Recently Completed</h2>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($completedTasks as $task)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm text-gray-600 line-through">{{ $task->title }}</span>
                            </div>
                            <span class="text-xs text-gray-400">{{ $task->updated_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function estimationPolling() {
    return {
        showModal: false,
        taskId: null,
        taskTitle: '',
        estimatedHours: 0,
        estimatedMinutes: 0,
        notes: '',
        submitting: false,
        isEdit: false,

        openSubmitModal(taskId, taskTitle) {
            this.taskId = taskId;
            this.taskTitle = taskTitle;
            this.estimatedHours = 0;
            this.estimatedMinutes = 0;
            this.notes = '';
            this.isEdit = false;
            this.showModal = true;
        },

        openEditModal(taskId, totalMinutes) {
            this.taskId = taskId;
            this.taskTitle = 'Edit Estimation';
            this.estimatedHours = Math.floor(totalMinutes / 60);
            this.estimatedMinutes = totalMinutes % 60;
            this.notes = '';
            this.isEdit = true;
            this.showModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.taskId = null;
            this.taskTitle = '';
        },

        async submitEstimation() {
            if (this.submitting) return;
            
            const totalMinutes = (parseInt(this.estimatedHours) || 0) * 60 + (parseInt(this.estimatedMinutes) || 0);
            
            if (totalMinutes <= 0) {
                alert('Please enter a valid estimation time.');
                return;
            }

            this.submitting = true;

            try {
                const response = await fetch(`/estimations/tasks/${this.taskId}/submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        estimated_hours: this.estimatedHours,
                        estimated_minutes: this.estimatedMinutes,
                        notes: this.notes
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message and reload
                    if (data.is_complete) {
                        alert(`All team members have submitted! Average estimation: ${Math.floor(data.average_minutes / 60)}h ${data.average_minutes % 60}m`);
                    }
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to submit estimation.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
@endsection
