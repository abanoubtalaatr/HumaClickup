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
                
                @if(Auth::id() === $task->creator_id)
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

                <!-- Attachments -->
                @if($task->attachments && $task->attachments->count() > 0)
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Attachments</h2>
                    <div class="space-y-3">
                        @foreach($task->attachments as $attachment)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <div class="flex items-center space-x-3 flex-1 min-w-0">
                                    <div class="flex-shrink-0">
                                        @php
                                            $extension = pathinfo($attachment->file_name, PATHINFO_EXTENSION);
                                            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp']);
                                        @endphp
                                        @if($isImage)
                                            <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        @else
                                            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $attachment->file_name }}</p>
                                        <div class="flex items-center space-x-2 text-xs text-gray-500">
                                            <span>{{ number_format($attachment->file_size / 1024, 2) }} KB</span>
                                            <span>•</span>
                                            <span>{{ $attachment->created_at->diffForHumans() }}</span>
                                            @if($attachment->user)
                                                <span>•</span>
                                                <span>{{ $attachment->user->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ml-3">
                                    <a href="{{ Storage::url($attachment->file_path) }}" 
                                       download="{{ $attachment->file_name }}"
                                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download
                                    </a>
                                </div>
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
                                        {{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-900">{{ $comment->user->name ?? 'Unknown' }}</span>
                                            <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No comments yet.</p>
                        @endforelse
                    </div>
                    <form action="{{ route('tasks.comments.store', $task) }}" method="POST" class="mt-4">
                        @csrf
                        <label for="comment_content" class="block text-sm font-medium text-gray-700 mb-2">Add a comment</label>
                        <textarea name="content" id="comment_content" rows="3" required
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                  placeholder="Write your comment..."></textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="mt-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Post comment
                        </button>
                    </form>
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
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm text-gray-600">Estimated Time</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $task->estimated_time ? round($task->estimated_time / 60, 1) . 'h' : 'Not set' }}
                                </span>
                            </div>
                            @if($task->estimated_time && $task->creator)
                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Set by {{ $task->creator->name }}</span>
                                @if($task->created_at)
                                    <span class="ml-1">• {{ $task->created_at->format('M d, Y') }}</span>
                                @endif
                            </div>
                            @endif
                            @if($task->estimation_edited_by && $task->estimation_completed_at)
                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>Edited by {{ optional(\App\Models\User::find($task->estimation_edited_by))->name ?? 'Unknown' }}</span>
                                <span class="ml-1">• {{ \Carbon\Carbon::parse($task->estimation_completed_at)->format('M d, Y') }}</span>
                            </div>
                            @endif
                        </div>
                        <div class="border-t pt-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Time Logged</span>
                                <span class="text-sm font-medium text-gray-900">{{ $task->getFormattedTimeLogged() }}</span>
                            </div>
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

