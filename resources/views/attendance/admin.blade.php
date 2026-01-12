@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Attendance Management</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitor all guests' attendance</p>
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

        <!-- Warnings -->
        @if(count($suspendedGuests) > 0)
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">⚠️ Suspended Guests (3+ Absences)</h3>
                    <div class="mt-2 space-y-2">
                        @foreach($suspendedGuests as $data)
                        <div class="flex items-center justify-between text-sm bg-white dark:bg-red-900/30 p-3 rounded">
                            <div>
                                <p class="font-semibold text-red-900 dark:text-red-100">{{ $data['guest']->name }}</p>
                                <p class="text-xs text-red-700 dark:text-red-300">Absences: {{ $data['absence_count'] }}</p>
                            </div>
                            <form action="{{ route('attendance.unsuspend', $data['guest']) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 text-xs bg-green-600 text-white rounded-md hover:bg-green-700">
                                    Unsuspend
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(count($absentToday) > 0)
        <div class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Absent/Not Marked Today</h3>
                    <p class="mt-1 text-xs text-yellow-700 dark:text-yellow-300">{{ count($absentToday) }} guest(s) have not checked in today</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Attendance List -->
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Required</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Check In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Check Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hours</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Quality</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Absences</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($attendanceData as $data)
                            <tr class="{{ $data['is_suspended'] ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-semibold">
                                            {{ strtoupper(substr($data['guest']->name, 0, 1)) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $data['guest']->name }}</p>
                                            @if($data['is_suspended'])
                                                <span class="text-xs text-red-600 dark:text-red-400 font-semibold">SUSPENDED</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($data['should_attend'])
                                        <span class="text-green-600 dark:text-green-400">✓ Yes</span>
                                    @else
                                        <span class="text-gray-400">✗ No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $data['attendance'] && $data['attendance']->checked_in_at ? \Carbon\Carbon::parse($data['attendance']->checked_in_at)->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $data['attendance'] && $data['attendance']->checked_out_at ? \Carbon\Carbon::parse($data['attendance']->checked_out_at)->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $data['attendance'] && $data['attendance']->total_hours ? number_format($data['attendance']->total_hours, 1) . 'h' : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($data['should_attend'])
                                        @if($data['attendance'])
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                                {{ $data['attendance']->status === 'present' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                {{ ucfirst($data['attendance']->status) }}
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                Not Marked
                                            </span>
                                        @endif
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            Not Required
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($data['should_attend'] && $data['attendance'] && $data['attendance']->status === 'present')
                                        <div class="flex flex-col space-y-1">
                                            @if($data['attendance']->attended_early)
                                                <span class="inline-flex items-center text-xs text-blue-600 dark:text-blue-400">
                                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Early
                                                </span>
                                            @endif
                                            @if($data['attendance']->attended_full_time)
                                                <span class="inline-flex items-center text-xs text-green-600 dark:text-green-400">
                                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    6+ hrs
                                                </span>
                                            @elseif($data['attendance']->hasCheckedOut() && $data['attendance']->total_hours < 6)
                                                <span class="inline-flex items-center text-xs text-orange-600 dark:text-orange-400">
                                                    <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    &lt;6h
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="font-medium {{ $data['absence_count'] >= 3 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                        {{ $data['absence_count'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($data['should_attend'])
                                        <div class="flex items-center space-x-2">
                                            <form action="{{ route('attendance.toggle', $data['guest']) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ $selectedDate }}">
                                                <input type="hidden" name="status" value="present">
                                                <button type="submit" class="px-2 py-1 text-xs font-semibold rounded
                                                    {{ $data['attendance'] && $data['attendance']->status === 'present' ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-green-100' }}">
                                                    ✓
                                                </button>
                                            </form>
                                            <form action="{{ route('attendance.toggle', $data['guest']) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ $selectedDate }}">
                                                <input type="hidden" name="status" value="absent">
                                                <button type="submit" class="px-2 py-1 text-xs font-semibold rounded
                                                    {{ $data['attendance'] && $data['attendance']->status === 'absent' ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-red-100' }}">
                                                    ✗
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                    @if($data['is_suspended'])
                                        <form action="{{ route('attendance.unsuspend', $data['guest']) }}" method="POST" class="inline mt-1">
                                            @csrf
                                            <button type="submit" class="text-xs text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 underline">
                                                Unsuspend
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No guests found</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
