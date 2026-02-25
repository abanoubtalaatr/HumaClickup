@extends('layouts.app')

@section('title', 'Attendance (Absence Tracking)')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Attendance</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Absence days from overdue tasks (incomplete past due date)</p>
            </div>
            <form method="GET" action="{{ route('attendance.index') }}" class="flex items-center gap-2">
                <label for="as_of" class="text-sm font-medium text-gray-700 dark:text-gray-300">As of date</label>
                <input type="date" id="as_of" name="as_of" value="{{ $asOfDate->format('Y-m-d') }}"
                       max="{{ today()->format('Y-m-d') }}"
                       class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                    Update
                </button>
            </form>
        </div>

        <!-- Info -->
        <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <p class="text-sm text-blue-800 dark:text-blue-200">
                <strong>How absence is calculated:</strong> Each task has a start date. If the task is not completed by the start date, every day from the day after start date until completion (or today) counts as one absence day. Completed on or before start date = no absence. Each guest is counted absent at most once per day across all tasks.
            </p>
            <p class="text-xs text-blue-600 dark:text-blue-300 mt-1">No future dates are counted. Data is as of <strong>{{ $asOfDate->format('F j, Y') }}</strong>.</p>
        </div>

        <!-- Summary table -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Guests – total absence days</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Guest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total absence days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Per task (audit)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($summary as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-9 w-9 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center text-indigo-700 dark:text-indigo-300 text-sm font-semibold">
                                            {{ strtoupper(substr($row['user']->name, 0, 1)) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $row['user']->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $row['user']->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                                        {{ $row['total_absence_days'] > 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200' : 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200' }}">
                                        {{ $row['total_absence_days'] }} day{{ $row['total_absence_days'] !== 1 ? 's' : '' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if(count($row['by_task']) > 0)
                                        <div class="space-y-2" x-data="{ open: false }">
                                            <button type="button" @click="open = !open"
                                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                                <span x-text="open ? 'Hide' : 'Show'">Show</span> {{ count($row['by_task']) }} task(s)
                                            </button>
                                            <div x-show="open" class="text-xs">
                                                <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-400">
                                                    @foreach($row['by_task'] as $taskId => $data)
                                                        <li>
                                                            <span class="font-medium">{{ $data['task']->title }}</span>
                                                            @if($data['task']->project)
                                                                <span class="text-gray-400">({{ $data['task']->project->name }})</span>
                                                            @endif
                                                            — {{ $data['absence_days'] }} day(s)
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center">
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">No guests to show</p>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                                        There are no guests in this workspace yet. Add guests to projects and they will appear here with their absence summary. Absence is calculated from tasks that have a <strong>due date</strong> (set when creating/editing the task or project).
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
