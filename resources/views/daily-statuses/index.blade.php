@extends('layouts.app')

@section('title', 'Daily Status')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Daily Status</h1>
                <p class="mt-1 text-sm text-gray-500">Track what you did each day</p>
            </div>
            <div class="flex items-center space-x-3">
                @if(!$todayStatus)
                    <a href="{{ route('daily-statuses.create', ['date' => today()->format('Y-m-d')]) }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Today's Status
                    </a>
                @else
                    <a href="{{ route('daily-statuses.edit', $todayStatus) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Edit Today's Status
                    </a>
                @endif
                <a href="{{ route('daily-statuses.create', ['date' => $tomorrow->format('Y-m-d')]) }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Tomorrow's Status
                </a>
            </div>
        </div>

        <!-- Today's Status Card (if exists) -->
        @if($todayStatus)
        <div class="mb-6 bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Today's Status</h2>
                    <p class="text-sm text-gray-600">{{ $todayStatus->date->format('l, F d, Y') }}</p>
                </div>
                <a href="{{ route('daily-statuses.edit', $todayStatus) }}" 
                   class="inline-flex items-center px-3 py-2 border border-indigo-300 rounded-md shadow-sm text-sm font-medium text-indigo-700 bg-white hover:bg-indigo-50">
                    Edit
                </a>
            </div>
                                    <div class="bg-white rounded-lg p-4 border border-indigo-100">
                                        <div class="prose max-w-none prose-sm">
                                            {!! $todayStatus->status !!}
                                        </div>
                                    </div>
        </div>
        @else
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-yellow-800">No status for today</h3>
                    <p class="mt-1 text-sm text-yellow-700">You haven't added your status for today yet.</p>
                </div>
                <a href="{{ route('daily-statuses.create', ['date' => today()->format('Y-m-d')]) }}" 
                   class="ml-4 inline-flex items-center px-3 py-2 border border-yellow-300 rounded-md shadow-sm text-sm font-medium text-yellow-700 bg-white hover:bg-yellow-50">
                    Add Now
                </a>
            </div>
        </div>
        @endif

        <!-- Date Filter -->
        <div class="mb-6 bg-white shadow rounded-lg p-4">
            <form method="GET" action="{{ route('daily-statuses.index') }}" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ request('date_to') }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('daily-statuses.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Clear
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Statuses List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @forelse($statuses as $status)
                    <li class="hover:bg-gray-50 transition-colors {{ $status->date->isToday() ? 'bg-indigo-50' : '' }}">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            {{ $status->date->format('l, F d, Y') }}
                                        </h3>
                                        @if($status->date->isToday())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                Today
                                            </span>
                                        @elseif($status->date->isYesterday())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Yesterday
                                            </span>
                                        @elseif($status->date->isFuture())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Future
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                        <div class="prose max-w-none prose-sm">
                                            {!! $status->status !!}
                                        </div>
                                    </div>
                                    
                                    <p class="mt-2 text-xs text-gray-500">
                                        Created {{ $status->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                
                                <div class="flex items-center space-x-3 ml-4">
                                    <a href="{{ route('daily-statuses.edit', $status) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                        Edit
                                    </a>
                                    
                                    <form action="{{ route('daily-statuses.destroy', $status) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this status?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No statuses found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding your first daily status.</p>
                        <div class="mt-6">
                            <a href="{{ route('daily-statuses.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add Status
                            </a>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
