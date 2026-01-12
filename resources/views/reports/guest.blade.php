@extends('layouts.app')

@section('title', 'My Feedback')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Feedback Reports</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Weekly feedback from your mentor</p>
        </div>

        <!-- Reports List -->
        @if($reports->count() > 0)
        <div class="space-y-4">
            @foreach($reports as $report)
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Week: {{ $report->weekRange }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">From: {{ $report->member->name }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $report->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                
                <div class="px-6 py-4">
                    <!-- Strong Points -->
                    @if($report->strong_points)
                    <div class="mb-4">
                        <h4 class="text-sm font-semibold text-green-700 dark:text-green-400 mb-2 flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            💪 Strong Points
                        </h4>
                        <div class="text-sm text-gray-700 dark:text-gray-300 bg-green-50 dark:bg-green-900/20 p-3 rounded-lg whitespace-pre-wrap">{{ $report->strong_points }}</div>
                    </div>
                    @endif

                    <!-- Weaknesses -->
                    @if($report->weaknesses)
                    <div class="mb-4">
                        <h4 class="text-sm font-semibold text-orange-700 dark:text-orange-400 mb-2 flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            📋 Areas for Improvement
                        </h4>
                        <div class="text-sm text-gray-700 dark:text-gray-300 bg-orange-50 dark:bg-orange-900/20 p-3 rounded-lg whitespace-pre-wrap">{{ $report->weaknesses }}</div>
                    </div>
                    @endif

                    <!-- Feedback -->
                    <div>
                        <h4 class="text-sm font-semibold text-indigo-700 dark:text-indigo-400 mb-2 flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                            </svg>
                            💬 Overall Feedback
                        </h4>
                        <div class="text-sm text-gray-700 dark:text-gray-300 bg-indigo-50 dark:bg-indigo-900/20 p-4 rounded-lg whitespace-pre-wrap">{{ $report->feedback }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $reports->links() }}
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No reports yet</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">No reports have been submitted yet.</p>
        </div>
        @endif
    </div>
</div>
@endsection
