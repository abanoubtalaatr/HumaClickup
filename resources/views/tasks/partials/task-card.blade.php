<a href="{{ route('tasks.show', $task) }}" 
   class="block bg-white rounded-lg shadow p-4 cursor-pointer hover:shadow-md transition-shadow" 
   data-task-id="{{ $task->id }}">
    <!-- Task Title -->
    <h4 class="text-sm font-medium text-gray-900 mb-2">{{ $task->title }}</h4>
    
    <!-- Task Meta -->
    <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
        <div class="flex items-center space-x-2">
            <!-- Priority Indicator -->
            @if($task->priority === 'urgent')
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                    Urgent
                </span>
            @elseif($task->priority === 'high')
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                    High
                </span>
            @endif
            
            <!-- Due Date -->
            @if($task->due_date)
                <span class="{{ $task->isOverdue() ? 'text-red-600 font-medium' : '' }}">
                    {{ $task->due_date->format('M d') }}
                </span>
            @endif
        </div>
    </div>
    
    <!-- Task Footer -->
    <div class="flex items-center justify-between">
        <!-- Assignees with name and track -->
        <div class="flex flex-wrap gap-1">
            @foreach($task->assignees->take(2) as $assignee)
                @php
                    $track = $assignee->getTrackInWorkspace(session('current_workspace_id'));
                @endphp
                <div class="flex items-center bg-gray-100 rounded-full px-2 py-0.5" title="{{ $assignee->name }}{{ $track ? ' - ' . $track->name : '' }}">
                    <div class="h-5 w-5 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs mr-1">
                        {{ strtoupper(substr($assignee->name, 0, 1)) }}
                    </div>
                    <span class="text-xs text-gray-700 truncate max-w-[60px]">{{ explode(' ', $assignee->name)[0] }}</span>
                    @if($track)
                        <span class="text-xs text-indigo-600 ml-1 truncate max-w-[40px]">({{ Str::limit($track->name, 8, '') }})</span>
                    @endif
                </div>
            @endforeach
            @if($task->assignees->count() > 2)
                <div class="flex items-center bg-gray-200 rounded-full px-2 py-0.5">
                    <span class="text-xs text-gray-600">+{{ $task->assignees->count() - 2 }}</span>
                </div>
            @endif
        </div>
        
        <!-- Task Stats -->
        <div class="flex items-center space-x-2 text-xs text-gray-500">
            @if(isset($task->comments_count) && $task->comments_count > 0)
                <span class="flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    {{ $task->comments_count }}
                </span>
            @endif
            @if(isset($task->attachments_count) && $task->attachments_count > 0)
                <span class="flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    {{ $task->attachments_count }}
                </span>
            @endif
            @if(isset($task->subtasks_count) && $task->subtasks_count > 0)
                <span class="flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    {{ $task->subtasks_count }}
                </span>
            @endif
        </div>
    </div>
</a>
