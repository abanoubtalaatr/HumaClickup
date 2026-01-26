@extends('layouts.app')

@section('title', $task->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $task->title }}</h1>
                <p class="mt-1 text-sm text-gray-500">
                    <a href="{{ route('projects.show', $task->project) }}" class="text-indigo-600 hover:text-indigo-700">
                        {{ $task->project->name }}
                    </a>
                </p>
            </div>
            <div class="flex items-center space-x-3">
                
                @if(Auth::id() === $task->user_id)
                <a href="{{ route('tasks.edit', $task) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Edit
                </a>
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?');" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-gray-50">
                        Delete
                    </button>
                </form>
                @endif
            </div>
            
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Description</h2>
                    <div class="prose max-w-none prose-sm">
                        {!! $task->description ?: '<p class="text-gray-500 italic">No description provided.</p>' !!}
                    </div>
                </div>

                <!-- Sub-tasks -->
                @if($task->subtasks && $task->subtasks->count() > 0)
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Sub-tasks</h2>
                    <div class="space-y-2">
                        @foreach($task->subtasks as $subtask)
                            <div class="flex items-center">
                                <input type="checkbox" 
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                       {{ $subtask->status->type === 'done' ? 'checked' : '' }}
                                       disabled>
                                <label class="ml-2 text-sm text-gray-700">{{ $subtask->title }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Comments -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Comments</h2>
                    <div class="space-y-4">
                        @forelse($task->comments as $comment)
                            <div class="flex space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                                            <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No comments yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Creator -->
                @if($task->creator)
                <div class="bg-white shadow rounded-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Created By</label>
                    <div class="flex items-center p-2 bg-gray-50 rounded-lg">
                        <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm mr-3 flex-shrink-0">
                            {{ strtoupper(substr($task->creator->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $task->creator->name }}</p>
                            <p class="text-xs text-gray-500">{{ $task->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Status -->
                <div class="bg-white shadow rounded-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" 
                         style="background-color: {{ $task->status->color }}20; color: {{ $task->status->color }}">
                        {{ $task->status->name }}
                    </div>
                </div>

                <!-- Assignees -->
                @if($task->assignees && $task->assignees->count() > 0)
                <div class="bg-white shadow rounded-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Assignees</label>
                    <div class="space-y-3">
                        @foreach($task->assignees as $assignee)
                            @php
                                $track = $assignee->getTrackInWorkspace(session('current_workspace_id'));
                                $role = $assignee->getRoleInWorkspace(session('current_workspace_id'));
                            @endphp
                            <div class="flex items-center p-2 bg-gray-50 rounded-lg">
                                <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm mr-3 flex-shrink-0">
                                    {{ strtoupper(substr($assignee->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $assignee->name }}</p>
                                    <div class="flex items-center space-x-2">
                                        @if($track)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                                {{ $track->name }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                            {{ $role === 'admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $role === 'member' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $role === 'guest' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ ucfirst($role) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Priority -->
                <div class="bg-white shadow rounded-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                    @if($task->priority === 'urgent')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Urgent</span>
                    @elseif($task->priority === 'high')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">High</span>
                    @elseif($task->priority === 'normal')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Normal</span>
                    @elseif($task->priority === 'low')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Low</span>
                    @else
                        <span class="text-sm text-gray-500">None</span>
                    @endif
                </div>

                <!-- Due Date -->
                @if($task->due_date)
                <div class="bg-white shadow rounded-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                    <p class="text-sm text-gray-900">{{ $task->due_date->format('M d, Y g:i A') }}</p>
                    @if($task->isOverdue())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-2">Overdue</span>
                    @endif
                </div>
                @endif

                <!-- Time Tracking -->
                <div class="bg-white shadow rounded-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Time Tracking</label>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Estimated</span>
                            <span class="text-sm font-medium text-gray-900">
                                {{ $task->estimated_time ? round($task->estimated_time / 60, 1) . 'h' : 'Not set' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Logged</span>
                            <span class="text-sm font-medium text-gray-900">{{ $task->getFormattedTimeLogged() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tags -->
                @if($task->tags && $task->tags->count() > 0)
                <div class="bg-white shadow rounded-lg p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($task->tags as $tag)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                  style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

