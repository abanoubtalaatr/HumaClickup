@extends('layouts.app')

@section('title', 'Guest Attendance')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Guest Attendance</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitor your guests' attendance</p>
            </div>
            
            <!-- Date Filter -->
            <form method="GET" class="flex items-center space-x-2">
                <input type="date" name="date" value="{{ $selectedDate }}" 
                       class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Filter
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $date->format('l, F d, Y') }}</h2>
            </div>
            
            @if(count($attendanceData) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Guest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Check In/Out</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Attended Early</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Full Time</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($attendanceData as $data)
                            <tr class="{{ (!$data['attendance'] || $data['attendance']->status === 'absent') ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($data['guest']->name, 0, 1)) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $data['guest']->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $data['guest']->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($data['attendance'] && $data['attendance']->checked_in_at)
                                        <div class="text-sm">
                                            <div class="text-gray-500 dark:text-gray-400">
                                                <span class="font-medium text-gray-900 dark:text-white">In:</span> {{ \Carbon\Carbon::parse($data['attendance']->checked_in_at)->format('h:i A') }}
                                            </div>
                                            <div class="text-gray-500 dark:text-gray-400">
                                                <span class="font-medium text-gray-900 dark:text-white">Out:</span> {{ $data['attendance']->checked_out_at ? \Carbon\Carbon::parse($data['attendance']->checked_out_at)->format('h:i A') : '-' }}
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($data['attendance'] && $data['attendance']->checked_in_at)
                                        @if($data['attendance']->attended_early)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                ✓ Early
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($data['attendance'] && $data['attendance']->hasCheckedOut())
                                        @if($data['attendance']->attended_full_time)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                ✓ 6+ hours
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                {{ number_format($data['attendance']->total_hours, 1) }}h
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <form action="{{ route('attendance.toggle', $data['guest']) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="date" value="{{ $selectedDate }}">
                                            <input type="hidden" name="status" value="present">
                                            <button type="submit" class="px-3 py-1 text-xs font-semibold rounded-md
                                                {{ $data['attendance'] && $data['attendance']->status === 'present' ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-green-100 dark:hover:bg-green-900' }}">
                                                ✓ Present
                                            </button>
                                        </form>
                                        <form action="{{ route('attendance.toggle', $data['guest']) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="date" value="{{ $selectedDate }}">
                                            <input type="hidden" name="status" value="absent">
                                            <button type="submit" class="px-3 py-1 text-xs font-semibold rounded-md
                                                {{ $data['attendance'] && $data['attendance']->status === 'absent' ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-red-100 dark:hover:bg-red-900' }}">
                                                ✗ Absent
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No guests should attend today</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $date->format('l, F d, Y') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
