@extends('layouts.app')

@section('title', 'My Progress')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">My Progress</h1>
        <p class="mt-1 text-sm text-gray-500">Track your daily and weekly performance</p>
    </div>

    <!-- Weekly Summary Card -->
    <div class="mb-6 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <h2 class="text-xl font-bold mb-4">This Week's Progress</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Hours -->
            <div>
                <p class="text-indigo-100 text-sm mb-1">Total Hours</p>
                <p class="text-4xl font-bold">{{ $weeklySummary['total_hours'] }}</p>
                <p class="text-sm text-indigo-200 mt-1">Target: {{ $weeklySummary['target_hours'] }}h</p>
            </div>

            <!-- Average Progress -->
            <div>
                <p class="text-indigo-100 text-sm mb-1">Average Progress</p>
                <p class="text-4xl font-bold">{{ number_format($weeklySummary['average_progress'], 0) }}%</p>
            </div>

            <!-- Status -->
            <div>
                <p class="text-indigo-100 text-sm mb-1">Weekly Target</p>
                @if($weeklySummary['meets_target'])
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-green-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-2xl font-bold text-green-300">Achieved!</span>
                    </div>
                @else
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-yellow-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-2xl font-bold text-yellow-300">{{ $weeklySummary['target_hours'] - $weeklySummary['total_hours'] }}h short</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Weekly Progress Bar -->
        <div class="mt-6">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-indigo-100">Weekly Progress</span>
                <span class="font-medium">{{ number_format(($weeklySummary['total_hours'] / $weeklySummary['target_hours']) * 100, 0) }}%</span>
            </div>
            <div class="w-full bg-indigo-800 bg-opacity-30 rounded-full h-3">
                <div class="bg-white h-3 rounded-full transition-all duration-500" 
                     style="width: {{ min(($weeklySummary['total_hours'] / $weeklySummary['target_hours']) * 100, 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Today's Progress by Project -->
    <div class="space-y-6">
        @forelse($projectProgress as $item)
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Project Header -->
            <div class="px-6 py-4 border-b border-gray-200" style="background-color: {{ $item['project']->color ?? '#6366f1' }}20">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">{{ $item['project']->icon ?? '📁' }}</span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $item['project']->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $date->format('l, F d, Y') }}</p>
                        </div>
                    </div>
                    
                    <!-- Attendance Badge -->
                    @if($item['attendance'])
                        <span class="px-3 py-1 rounded-full text-sm font-semibold 
                            {{ $item['attendance']->isPresent() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $item['attendance']->isPresent() ? '✓ Present' : '✗ Absent' }}
                            @if($item['attendance']->isApproved())
                                <span class="ml-1 text-xs">(Approved)</span>
                            @endif
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-600">
                            Not Marked
                        </span>
                    @endif
                </div>
            </div>

            <!-- Progress Content -->
            <div class="p-6">
                @if($item['progress'])
                    <!-- Daily Progress Bar -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Today's Progress</span>
                            <span class="text-sm font-semibold {{ $item['progress']->meetsTarget() ? 'text-green-600' : 'text-gray-600' }}">
                                {{ number_format($item['progress']->progress_percentage, 0) }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div class="h-4 rounded-full transition-all duration-500 {{ $item['progress']->meetsTarget() ? 'bg-green-600' : 'bg-yellow-500' }}" 
                                 style="width: {{ $item['progress']->progress_percentage }}%"></div>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs text-gray-600">
                            <span>{{ number_format($item['progress']->completed_hours, 1) }}h completed</span>
                            <span>{{ number_format($item['progress']->required_hours, 1) }}h required</span>
                        </div>
                    </div>

                    <!-- Main Task Info -->
                    @if($item['progress']->task)
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <p class="text-xs text-gray-500 mb-1">Today's Main Task</p>
                            <p class="font-medium text-gray-900">{{ $item['progress']->task->title }}</p>
                            <div class="mt-2 flex items-center space-x-4 text-xs text-gray-600">
                                <span class="flex items-center">
                                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ number_format($item['progress']->task->estimated_time, 1) }} hours
                                </span>
                                <span class="px-2 py-1 rounded-full {{ $item['progress']->task->status->type === 'done' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $item['progress']->task->status->name }}
                                </span>
                            </div>
                            <a href="{{ route('tasks.show', $item['progress']->task) }}" 
                               class="mt-2 inline-block text-sm text-indigo-600 hover:text-indigo-800">
                                View Task →
                            </a>
                        </div>
                    @else
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-red-800 font-medium">⚠️ No main task assigned for today</p>
                            <p class="text-xs text-red-600 mt-1">Contact your mentor to assign a daily task</p>
                        </div>
                    @endif

                    <!-- Approval Status -->
                    @if($item['progress']->isApproved())
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <p class="text-sm text-green-800">
                                ✓ Approved by {{ $item['progress']->approvedBy->name ?? 'Mentor' }} 
                                on {{ $item['progress']->approved_at->format('M d, h:i A') }}
                            </p>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <p class="text-sm text-yellow-800">
                                ⏳ Pending mentor approval
                            </p>
                        </div>
                    @endif

                @else
                    <!-- No Progress Record -->
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No progress recorded for today</p>
                    </div>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white shadow rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No Projects Assigned</h3>
            <p class="mt-1 text-sm text-gray-500">You are not assigned to any projects yet.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
