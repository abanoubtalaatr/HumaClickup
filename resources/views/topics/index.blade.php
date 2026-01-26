@extends('layouts.app')

@section('title', 'Topics')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Topics</h1>
                <p class="mt-1 text-sm text-gray-500">Manage your presentation topics</p>
            </div>
            <a href="{{ route('topics.create') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Topic
            </a>
        </div>

        <!-- Admin Stats Section -->
        @if($isAdmin)
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Track Statistics -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Topics by Track</h2>
                <div class="space-y-3">
                    @forelse($tracks as $track)
                        @php
                            $count = $trackStats->get($track->id)?->count ?? 0;
                        @endphp
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="h-3 w-3 rounded-full mr-2" style="background-color: {{ $track->color }}"></div>
                                <span class="text-sm text-gray-700">{{ $track->name }}</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $count }} topics</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No tracks found</p>
                    @endforelse
                </div>
            </div>

            <!-- User Statistics by Track -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Topics by Person (by Track)</h2>
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @forelse($tracks as $track)
                        @php
                            $usersInTrack = $userStats->get($track->id) ?? collect();
                        @endphp
                        @if($usersInTrack->count() > 0)
                            <div class="mb-4 pb-3 border-b border-gray-200 last:border-0">
                                <div class="flex items-center mb-2">
                                    <div class="h-3 w-3 rounded-full mr-2" style="background-color: {{ $track->color }}"></div>
                                    <span class="text-sm font-medium text-gray-900">{{ $track->name }}</span>
                                </div>
                                <div class="ml-5 space-y-2">
                                    @foreach($usersInTrack as $stat)
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-700">{{ $stat['user']->name }}</span>
                                            <span class="text-sm font-medium text-gray-900">{{ $stat['count'] }} topics</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-sm text-gray-500">No tracks found</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endif

        <!-- Filters (Admin Only) -->
        @if($isAdmin)
        <div class="mb-6 bg-white shadow rounded-lg p-4">
            <form method="GET" action="{{ route('topics.index') }}" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Track</label>
                    <select name="track_id" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Tracks</option>
                        @foreach($tracks as $track)
                            <option value="{{ $track->id }}" {{ request('track_id') == $track->id ? 'selected' : '' }}>
                                {{ $track->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="is_complete" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All</option>
                        <option value="0" {{ request('is_complete') === '0' ? 'selected' : '' }}>Incomplete</option>
                        <option value="1" {{ request('is_complete') === '1' ? 'selected' : '' }}>Complete</option>
                    </select>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('topics.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Clear
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Topics List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @forelse($topics as $topic)
                    <li class="hover:bg-gray-50 transition-colors">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $topic->name }}</h3>
                                        @if($topic->is_complete)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Complete
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Incomplete
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($topic->description)
                                        <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $topic->description }}</p>
                                    @endif
                                    
                                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>{{ $topic->date->format('M d, Y') }}</span>
                                        </div>
                                        
                                        @if($topic->track)
                                            <div class="flex items-center">
                                                <div class="h-3 w-3 rounded-full mr-1" style="background-color: {{ $topic->track->color }}"></div>
                                                <span>{{ $topic->track->name }}</span>
                                            </div>
                                        @endif
                                        
                                        @if($isAdmin)
                                            <div class="flex items-center">
                                                <span>Created by: <span class="font-medium">{{ $topic->user->name }}</span></span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-3 ml-4">
                                    @if($topic->presentation_link)
                                        <a href="{{ $topic->presentation_link }}" 
                                           target="_blank"
                                           class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                            View Link
                                        </a>
                                    @endif
                                    
                                    <form action="{{ route('topics.toggle-complete', $topic) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium {{ $topic->is_complete ? 'text-gray-700 bg-white hover:bg-gray-50' : 'text-green-700 bg-green-50 hover:bg-green-100' }}">
                                            {{ $topic->is_complete ? 'Mark Incomplete' : 'Mark Complete' }}
                                        </button>
                                    </form>
                                    
                                    <a href="{{ route('topics.edit', $topic) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                        Edit
                                    </a>
                                    
                                    <form action="{{ route('topics.destroy', $topic) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this topic?');">
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
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No topics</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new topic.</p>
                        <div class="mt-6">
                            <a href="{{ route('topics.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                New Topic
                            </a>
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
