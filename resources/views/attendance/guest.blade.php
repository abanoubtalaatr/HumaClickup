@extends('layouts.app')

@section('title', 'My Attendance')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">My Attendance</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Track your daily attendance</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        <!-- Today's Attendance Card -->
        <div class="mb-6 bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Today - {{ now()->format('l, F d, Y') }}</h2>
            
            @if($shouldAttendToday)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Check In Status -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Check In</p>
                                @if($todayAttendance && $todayAttendance->hasCheckedIn())
                                    <p class="mt-1 text-2xl font-bold text-blue-900 dark:text-blue-100">
                                        {{ \Carbon\Carbon::parse($todayAttendance->checked_in_at)->format('h:i A') }}
                                    </p>
                                @else
                                    <p class="mt-1 text-sm text-blue-600 dark:text-blue-400">Not checked in</p>
                                @endif
                            </div>
                            @if(!$todayAttendance || !$todayAttendance->hasCheckedIn())
                                <form action="{{ route('attendance.checkin') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Check In
                                    </button>
                                </form>
                            @else
                                <svg class="h-8 w-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </div>
                    </div>

                    <!-- Check Out Status -->
                    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-700 dark:text-purple-300">Check Out</p>
                                @if($todayAttendance && $todayAttendance->hasCheckedOut())
                                    <p class="mt-1 text-2xl font-bold text-purple-900 dark:text-purple-100">
                                        {{ \Carbon\Carbon::parse($todayAttendance->checked_out_at)->format('h:i A') }}
                                    </p>
                                @else
                                    <p class="mt-1 text-sm text-purple-600 dark:text-purple-400">Not checked out</p>
                                @endif
                            </div>
                            @if($todayAttendance && $todayAttendance->hasCheckedIn() && !$todayAttendance->hasCheckedOut())
                                <form action="{{ route('attendance.checkout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                                        Check Out
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Total Hours -->
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <p class="text-sm font-medium text-green-700 dark:text-green-300">Total Hours Today</p>
                        <p class="mt-1 text-2xl font-bold text-green-900 dark:text-green-100">
                            @if($todayAttendance && $todayAttendance->total_hours)
                                {{ number_format($todayAttendance->total_hours, 1) }}h
                            @else
                                0h
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Attendance Quality Indicators -->
                @if($todayAttendance && $todayAttendance->hasCheckedIn())
                <div class="mt-4 flex items-center space-x-3">
                    @if($todayAttendance->attended_early)
                        <div class="flex items-center px-3 py-1 bg-blue-100 dark:bg-blue-900/50 border border-blue-300 dark:border-blue-700 rounded-full">
                            <svg class="h-4 w-4 text-blue-600 dark:text-blue-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-300">Attended Early ✓</span>
                        </div>
                    @endif
                    
                    @if($todayAttendance->attended_full_time)
                        <div class="flex items-center px-3 py-1 bg-green-100 dark:bg-green-900/50 border border-green-300 dark:border-green-700 rounded-full">
                            <svg class="h-4 w-4 text-green-600 dark:text-green-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-semibold text-green-700 dark:text-green-300">Full Time (6+ hours) ✓</span>
                        </div>
                    @elseif($todayAttendance->hasCheckedOut() && $todayAttendance->total_hours < 6)
                        <div class="flex items-center px-3 py-1 bg-red-100 dark:bg-red-900/50 border border-red-300 dark:border-red-700 rounded-full">
                            <svg class="h-4 w-4 text-red-600 dark:text-red-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-semibold text-red-700 dark:text-red-300">Less than 6 hours</span>
                        </div>
                    @endif
                </div>
                @endif
            @else
                <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">You are not required to attend today</p>
                </div>
            @endif
        </div>

        <!-- Attendance Days Info -->
        <div class="mb-6 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-indigo-900 dark:text-indigo-100 mb-2">Your Attendance Schedule</h3>
            <div class="flex flex-wrap gap-2">
                @php
                    $allDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                @endphp
                @foreach($allDays as $day)
                    <span class="px-3 py-1 rounded-full text-xs font-medium 
                        {{ in_array($day, $attendanceDays ?? []) 
                            ? 'bg-indigo-600 text-white' 
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                        {{ ucfirst($day) }}
                    </span>
                @endforeach
            </div>
        </div>

        <!-- Attendance History -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Attendance History (This Month)</h2>
            </div>
            
            @if($attendances->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Hours</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quality</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($attendances as $attendance)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $attendance->date->format('D, M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $attendance->checked_in_at ? \Carbon\Carbon::parse($attendance->checked_in_at)->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $attendance->checked_out_at ? \Carbon\Carbon::parse($attendance->checked_out_at)->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $attendance->total_hours ? number_format($attendance->total_hours, 1) . 'h' : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($attendance->status === 'present')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            Present
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                            Absent
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($attendance->status === 'present')
                                        <div class="flex flex-col space-y-1">
                                            @if($attendance->attended_early)
                                                <span class="inline-flex items-center text-xs text-blue-600 dark:text-blue-400">
                                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Early
                                                </span>
                                            @endif
                                            @if($attendance->attended_full_time)
                                                <span class="inline-flex items-center text-xs text-green-600 dark:text-green-400">
                                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Full Time
                                                </span>
                                            @elseif($attendance->hasCheckedOut() && $attendance->total_hours < 6)
                                                <span class="inline-flex items-center text-xs text-orange-600 dark:text-orange-400">
                                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Incomplete
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No attendance records yet this month</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
