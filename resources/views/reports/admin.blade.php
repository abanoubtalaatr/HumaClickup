@extends('layouts.app')

@section('title', 'All Reports - Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">All Guest Reports</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitor all member reports and track completion</p>
        </div>

        <!-- Warnings -->
        @if(count($warnings) > 0)
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">⚠️ Missing Reports This Week</h3>
                    <div class="mt-2 space-y-2">
                        @foreach($warnings as $warning)
                        <div class="text-sm text-red-700 dark:text-red-300 bg-white dark:bg-red-900/30 p-3 rounded">
                            <p class="font-semibold">{{ $warning['member']->name }}</p>
                            <p class="text-xs mt-1">Missing reports for {{ $warning['missing_guests']->count() }} out of {{ $warning['total_guests'] }} guests:</p>
                            <ul class="text-xs mt-1 list-disc list-inside">
                                @foreach($warning['missing_guests'] as $guest)
                                <li>{{ $guest->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="ml-3 text-sm text-green-700 dark:text-green-300">✓ All members have submitted reports for this week!</p>
            </div>
        </div>
        @endif

        <!-- Reports List -->
        @if($reports->count() > 0)
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($reports as $report)
                <li class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr($report->guest->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $report->guest->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        By: {{ $report->member->name }} • {{ $report->weekRange }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                                {{ Str::limit($report->feedback, 150) }}
                            </div>
                        </div>
                        <div class="ml-4">
                            <a href="{{ route('reports.show', $report) }}" 
                               class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                View Details
                            </a>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
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
