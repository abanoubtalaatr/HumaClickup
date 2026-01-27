@extends('layouts.app')

@section('title', 'Team Time Tracking')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ expandedGuest: null }">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Team Time Tracking</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        View time tracking for guests you manage
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('time-tracking.index', ['view' => 'personal']) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        My Personal Time
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="space-y-4">
                <!-- Period Filter -->
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Time Period</h3>
                    <div class="flex space-x-2">
                        <a href="{{ route('time-tracking.index', array_merge(request()->except('period'), ['period' => 'day'])) }}" 
                           class="px-3 py-1.5 rounded text-sm font-medium {{ $period === 'day' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            Today
                        </a>
                        <a href="{{ route('time-tracking.index', array_merge(request()->except('period'), ['period' => 'week'])) }}" 
                           class="px-3 py-1.5 rounded text-sm font-medium {{ $period === 'week' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            Week
                        </a>
                        <a href="{{ route('time-tracking.index', array_merge(request()->except('period'), ['period' => '2weeks'])) }}" 
                           class="px-3 py-1.5 rounded text-sm font-medium {{ $period === '2weeks' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            2 Weeks
                        </a>
                        <a href="{{ route('time-tracking.index', array_merge(request()->except('period'), ['period' => '3weeks'])) }}" 
                           class="px-3 py-1.5 rounded text-sm font-medium {{ $period === '3weeks' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            3 Weeks
                        </a>
                        <a href="{{ route('time-tracking.index', array_merge(request()->except('period'), ['period' => 'month'])) }}" 
                           class="px-3 py-1.5 rounded text-sm font-medium {{ $period === 'month' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            Month
                        </a>
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Showing data from {{ $startDate->format('M d, Y') }} to {{ $endDate->format('M d, Y') }}
                </p>
                
                <!-- Sort Filter -->
                <div class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</h3>
                    <div class="flex space-x-2">
                        <a href="{{ route('time-tracking.index', array_merge(request()->except('sort'), ['sort' => 'all'])) }}" 
                           class="px-3 py-1.5 rounded text-sm font-medium {{ ($sortFilter ?? 'all') === 'all' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            All
                        </a>
                        <a href="{{ route('time-tracking.index', array_merge(request()->except('sort'), ['sort' => 'most'])) }}" 
                           class="px-3 py-1.5 rounded text-sm font-medium {{ ($sortFilter ?? 'all') === 'most' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            Most Tracking
                        </a>
                        <a href="{{ route('time-tracking.index', array_merge(request()->except('sort'), ['sort' => 'lower'])) }}" 
                           class="px-3 py-1.5 rounded text-sm font-medium {{ ($sortFilter ?? 'all') === 'lower' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            Lower Tracking
                        </a>
                        <a href="{{ route('time-tracking.index', array_merge(request()->except('sort'), ['sort' => 'never'])) }}" 
                           class="px-3 py-1.5 rounded text-sm font-medium {{ ($sortFilter ?? 'all') === 'never' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            Never Tracking
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 mb-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Time Tracked</dt>
                                <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalFormatted }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Entries</dt>
                                <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $totalEntries }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guests Time Tracking Table -->
        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    My Guests Time Tracking
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Time tracking overview for all guests you manage
                </p>
            </div>

            @if($guestTimeData->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Guest
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Track
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Total Time
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Entries
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Details
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($guestTimeData as $data)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-gray-400 flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr($data['user']->name, 0, 1)) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $data['user']->name }}</span>
                                            @if($data['user']->whatsapp_number)
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $data['user']->whatsapp_number) }}"
                                                   target="_blank"
                                                   class="inline-flex items-center px-2 py-0.5 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded hover:bg-green-200 dark:hover:bg-green-800 transition-colors">
                                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                                    </svg>
                                                    <span class="text-xs font-medium">{{ $data['user']->whatsapp_number }}</span>
                                                </a>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $data['user']->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($data['track'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                          style="background-color: {{ $data['track']->color }}20; color: {{ $data['track']->color }}">
                                        {{ $data['track']->name }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $data['total_formatted'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">({{ $data['total_minutes'] }} minutes)</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $data['entries_count'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($data['entries_count'] > 0)
                                    <button @click="expandedGuest = expandedGuest === {{ $data['user']->id }} ? null : {{ $data['user']->id }}"
                                            class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                        <span x-show="expandedGuest !== {{ $data['user']->id }}">Show</span>
                                        <span x-show="expandedGuest === {{ $data['user']->id }}" x-cloak>Hide</span>
                                    </button>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @if($data['entries_count'] > 0)
                        <tr x-show="expandedGuest === {{ $data['user']->id }}" x-cloak>
                            <td colspan="5" class="px-6 py-4 bg-gray-50 dark:bg-gray-900">
                                <div class="space-y-2">
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Time Entries</h4>
                                    @foreach($data['entries'] as $entry)
                                    <div class="flex items-center justify-between bg-white dark:bg-gray-800 p-3 rounded-lg">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $entry->task->title }}
                                                </span>
                                                @if($entry->task->project)
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        in {{ $entry->task->project->name }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ $entry->start_time->format('M d, Y g:i A') }}
                                                @if($entry->end_time)
                                                    - {{ $entry->end_time->format('g:i A') }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ floor($entry->duration / 3600) }}h {{ floor(($entry->duration % 3600) / 60) }}m
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No guests found</h3>
                <p class="mt-2 text-gray-500 dark:text-gray-400">You don't have any guests assigned to you yet.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
