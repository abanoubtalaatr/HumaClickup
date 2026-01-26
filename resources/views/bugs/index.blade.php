@extends('layouts.app')

@section('title', 'Bugs')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="bugList()">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Bugs</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $project->name ?? 'All Bugs' }}</p>
            </div>
            <div class="flex items-center space-x-3">
                @if(!($isGuest ?? false))
                <a href="{{ route('tasks.create', $project ? ['project' => $project] : []) }}?type=bug" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Report Bug
                </a>
                @endif
            </div>
        </div>

        <!-- Project Selector (when no specific project in URL) -->
        @if(!$project && $projects->count() > 1)
        <div class="mb-6 bg-white p-4 rounded-lg shadow">
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Project:</label>
                <select onchange="window.location.href='{{ route('bugs.index') }}?project_id=' + this.value" 
                        class="block w-64 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ (request('project_id') == $proj->id || (!request('project_id') && $loop->first)) ? 'selected' : '' }}>
                            {{ $proj->name }}
                        </option>
                    @endforeach
                </select>
                <span class="text-sm text-gray-500">or</span>
                <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                    View all projects
                </a>
            </div>
        </div>
        @endif

        <!-- Filters -->
        <div class="mb-6 bg-white p-4 rounded-lg shadow">
            <form method="GET" action="{{ route('bugs.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Project</label>
                        <select name="project_id" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Projects</option>
                            @foreach($projects ?? [] as $proj)
                                <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                                    {{ $proj->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status_id" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Statuses</option>
                            @foreach($statuses ?? [] as $status)
                                <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                    {{ $status->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assignee</label>
                        <select name="assignee_id" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Assignees</option>
                            @foreach($assignees ?? [] as $assignee)
                                <option value="{{ $assignee->id }}" {{ request('assignee_id') == $assignee->id ? 'selected' : '' }}>
                                    {{ $assignee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select name="priority" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Priorities</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Creator</label>
                        <select name="creator_id" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Creators</option>
                            @foreach($assignees ?? [] as $assignee)
                                <option value="{{ $assignee->id }}" {{ request('creator_id') == $assignee->id ? 'selected' : '' }}>
                                    {{ $assignee->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('bugs.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Clear Filters
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Bugs Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @forelse($bugs ?? [] as $bug)
                    <li class="hover:bg-gray-50">
                        <a href="{{ route('tasks.show', $bug) }}" class="block">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center flex-1">
                                        <div class="flex-shrink-0 mr-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Bug
                                            </span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $bug->title }}
                                                </p>
                                                @if($bug->relatedTask)
                                                    <span class="ml-2 text-xs text-gray-500">
                                                        (Related to: {{ $bug->relatedTask->title }})
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mt-2 flex items-center text-sm text-gray-500">
                                                <span class="mr-4">Project: {{ $bug->project->name }}</span>
                                                @if($bug->status)
                                                    <span class="mr-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $bug->status->color }}20; color: {{ $bug->status->color }}">
                                                        {{ $bug->status->name }}
                                                    </span>
                                                @endif
                                                @if($bug->priority && $bug->priority !== 'none')
                                                    <span class="mr-4 capitalize">{{ $bug->priority }} priority</span>
                                                @endif
                                                <span class="mr-4">Created by: {{ $bug->creator->name }}</span>
                                                <span>{{ $bug->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if($bug->assignees->count() > 0)
                                            <div class="flex -space-x-2">
                                                @foreach($bug->assignees->take(3) as $assignee)
                                                    <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-medium border-2 border-white" title="{{ $assignee->name }}">
                                                        {{ strtoupper(substr($assignee->name, 0, 1)) }}
                                                    </div>
                                                @endforeach
                                                @if($bug->assignees->count() > 3)
                                                    <div class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center text-white text-xs font-medium border-2 border-white">
                                                        +{{ $bug->assignees->count() - 3 }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </div>
                                @if($bug->description)
                                    <div class="mt-2 text-sm text-gray-600 line-clamp-2">
                                        {!! Str::limit(strip_tags($bug->description), 150) !!}
                                    </div>
                                @endif
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No bugs found</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by reporting a new bug.</p>
                        @if(!($isGuest ?? false))
                        <div class="mt-6">
                            <a href="{{ route('tasks.create', $project ? ['project' => $project] : []) }}?type=bug" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Report Bug
                            </a>
                        </div>
                        @endif
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
function bugList() {
    return {
        // Add any bug-specific functionality here
    };
}
</script>
@endpush
@endsection
