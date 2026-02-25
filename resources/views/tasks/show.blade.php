@extends('layouts.app')

@section('title', $task->title)

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-500 text-white px-6 py-3 text-sm font-medium flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-500 text-white px-6 py-3 text-sm font-medium flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Top Breadcrumb Bar --}}
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-6 lg:px-10 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3 text-sm">
                <a href="{{ route('projects.show', $task->project) }}" class="text-gray-500 hover:text-indigo-600 transition font-medium flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    {{ $task->project->name }}
                </a>
                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-900 font-semibold">{{ Str::limit($task->title, 50) }}</span>
                @if($task->type === 'bug')
                    <span class="px-2 py-0.5 rounded bg-red-100 text-red-700 text-xs font-bold">BUG</span>
                @endif
                @if($task->is_main_task === 'yes')
                    <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-700 text-xs font-bold">MAIN</span>
                @endif
            </div>
            <div class="flex items-center space-x-2">
                @if(Auth::id() === $task->creator_id)
                    <a href="{{ route('tasks.edit', $task) }}" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">Edit</a>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Delete this task?');" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 text-sm font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition">Delete</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="flex">
        {{-- ==================== MAIN CONTENT ==================== --}}
        <div class="flex-1 min-w-0 px-6 lg:px-10 py-8 space-y-8">

            {{-- Task Title & Status Row --}}
            <div>
                <div class="flex items-start justify-between mb-3">
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $task->title }}</h1>
                    <div class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold ml-4 shrink-0" style="background-color: {{ $task->status->color }}15; color: {{ $task->status->color }}; border: 1px solid {{ $task->status->color }}30;">
                        <span class="w-2 h-2 rounded-full mr-2" style="background-color: {{ $task->status->color }}"></span>
                        {{ $task->status->name }}
                    </div>
                </div>
                {{-- Quick meta row --}}
                <div class="flex items-center flex-wrap gap-x-5 gap-y-2 text-sm text-gray-500">
                    @if($task->creator)
                        <span class="flex items-center">
                            <div class="w-5 h-5 rounded-full bg-indigo-500 text-white flex items-center justify-center text-[10px] font-bold mr-1.5">{{ strtoupper(substr($task->creator->name, 0, 1)) }}</div>
                            {{ $task->creator->name }}
                        </span>
                    @endif
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $task->created_at->format('M d, Y') }}
                    </span>
                    @if($task->estimated_time)
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $task->estimated_time }}h estimated
                        </span>
                    @endif
                    @if($task->due_date)
                        <span class="flex items-center {{ $task->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                            <svg class="w-4 h-4 mr-1 {{ $task->isOverdue() ? 'text-red-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Due {{ $task->due_date->format('M d, Y') }}
                            @if($task->isOverdue()) <span class="ml-1 px-1.5 py-0.5 rounded bg-red-100 text-red-700 text-xs">OVERDUE</span> @endif
                        </span>
                    @endif
                </div>
            </div>

            {{-- ===== DESCRIPTION ===== --}}
            <section>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Description</h3>
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="prose prose-sm max-w-none prose-indigo prose-p:text-gray-700 prose-headings:text-gray-900">
                        {!! $task->description ?: '<p class="text-gray-400 italic">No description provided.</p>' !!}
                    </div>
                </div>
            </section>

            {{-- ===== ATTACHMENTS ===== --}}
            @if($task->attachments && $task->attachments->count() > 0)
            <section>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">
                    Attachments <span class="text-gray-300 ml-1">({{ $task->attachments->count() }})</span>
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($task->attachments as $attachment)
                        @php
                            $extension = pathinfo($attachment->file_name, PATHINFO_EXTENSION);
                            $isImage = in_array(strtolower($extension), ['jpg','jpeg','png','gif','svg','webp']);
                        @endphp
                        <a href="{{ Storage::url($attachment->file_path) }}" download="{{ $attachment->file_name }}"
                           class="flex items-center p-3 bg-white border border-gray-200 rounded-xl hover:border-indigo-300 hover:shadow-sm transition group">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 {{ $isImage ? 'bg-indigo-50 text-indigo-500' : 'bg-gray-100 text-gray-400' }}">
                                @if($isImage)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                @endif
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate group-hover:text-indigo-600">{{ $attachment->file_name }}</p>
                                <p class="text-xs text-gray-400">{{ number_format($attachment->file_size / 1024, 1) }} KB</p>
                            </div>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-400 ml-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- ===== SUB-TASKS ===== --}}
            @if($task->subtasks && $task->subtasks->count() > 0)
            <section>
                @php
                    $completedSubtasks = $task->subtasks->filter(fn($s) => optional($s->status)->type === 'done')->count();
                    $totalSubtasks = $task->subtasks->count();
                    $subtaskProgress = $totalSubtasks > 0 ? round(($completedSubtasks / $totalSubtasks) * 100) : 0;
                @endphp
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">
                        Sub-tasks <span class="text-gray-300 ml-1">({{ $completedSubtasks }}/{{ $totalSubtasks }})</span>
                    </h3>
                    <span class="text-xs font-bold {{ $subtaskProgress === 100 ? 'text-green-600' : 'text-indigo-600' }}">{{ $subtaskProgress }}%</span>
                </div>
                {{-- Progress --}}
                <div class="w-full bg-gray-200 rounded-full h-1.5 mb-4">
                    <div class="h-1.5 rounded-full transition-all duration-500 {{ $subtaskProgress === 100 ? 'bg-green-500' : 'bg-indigo-500' }}" style="width: {{ $subtaskProgress }}%"></div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100" x-data="{ openSubtaskComment: null }">
                    @foreach($task->subtasks as $idx => $subtask)
                        @php $isDone = optional($subtask->status)->type === 'done'; @endphp
                        <div>
                            <div class="flex items-center px-5 py-3.5 {{ $isDone ? 'bg-green-50/40' : '' }}">
                                {{-- Checkbox --}}
                                <div class="w-5 h-5 rounded {{ $isDone ? 'bg-green-500' : 'border-2 border-gray-300' }} flex items-center justify-center mr-4 shrink-0">
                                    @if($isDone)
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </div>
                                {{-- Title & Assignees --}}
                                <div class="flex-1 min-w-0 mr-3">
                                    <p class="text-sm font-medium {{ $isDone ? 'text-gray-400 line-through' : 'text-gray-800' }}">{{ $subtask->title }}</p>
                                    @if($subtask->assignees && $subtask->assignees->count() > 0)
                                        <div class="flex items-center mt-1 -space-x-1">
                                            @foreach($subtask->assignees->take(3) as $assignee)
                                                <div class="w-5 h-5 rounded-full bg-indigo-400 text-white text-[9px] font-bold flex items-center justify-center ring-2 ring-white" title="{{ $assignee->name }}">{{ strtoupper(substr($assignee->name, 0, 1)) }}</div>
                                            @endforeach
                                            @if($subtask->assignees->count() > 3)
                                                <span class="text-xs text-gray-400 ml-2">+{{ $subtask->assignees->count() - 3 }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                {{-- Right side info --}}
                                <div class="flex items-center gap-2 shrink-0">
                                    @if($subtask->estimated_time)
                                        <span class="text-xs text-gray-400 font-mono">{{ $subtask->estimated_time }}h</span>
                                    @endif
                                    @if($subtask->status)
                                        <span class="px-2 py-0.5 rounded text-xs font-medium" style="background-color: {{ $subtask->status->color }}15; color: {{ $subtask->status->color }}">{{ $subtask->status->name }}</span>
                                    @endif
                                        <button @click="openSubtaskComment = openSubtaskComment === {{ $subtask->id }} ? null : {{ $subtask->id }}"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium transition"
                                            :class="openSubtaskComment === {{ $subtask->id }} ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        {{ $subtask->comments ? $subtask->comments->count() : 0 }}
                                    </button>
                                </div>
                            </div>
                            {{-- Subtask comments panel --}}
                            <div x-show="openSubtaskComment === {{ $subtask->id }}" x-transition x-cloak class="border-t border-gray-200">
                                @if($subtask->comments && $subtask->comments->count() > 0)
                                    <div class="px-6 pt-4 pb-2 space-y-3 max-h-60 overflow-y-auto">
                                        @foreach($subtask->comments as $comment)
                                            <div class="flex gap-2.5">
                                                <div class="w-6 h-6 rounded-full bg-indigo-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}</div>
                                                <div class="flex-1 bg-white rounded-xl p-3 border border-gray-100">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-sm font-semibold text-gray-800">{{ $comment->user->name ?? 'Unknown' }}</span>
                                                        <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">{{ $comment->content }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="px-6 pt-4 pb-2 text-center">
                                        <p class="text-sm text-gray-400">No comments yet</p>
                                    </div>
                                @endif
                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                                    <form action="{{ route('tasks.comments.store', $subtask) }}" method="POST">
                                        @csrf
                                        <div class="flex gap-2.5">
                                            <div class="w-6 h-6 rounded-full bg-indigo-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                                            <div class="flex-1">
                                                <textarea name="content" rows="2" required
                                                          class="w-full text-sm rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                                                          placeholder="Comment on this subtask..."></textarea>
                                                <div class="flex justify-end mt-2">
                                                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 shadow-sm transition">Post</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- ===== BUGS SECTION ===== --}}
            <section x-data="{ showBugForm: false }">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        Bugs
                        @if($task->bugs && $task->bugs->count() > 0)
                            <span class="ml-2 px-1.5 py-0.5 rounded-full bg-red-100 text-red-700 text-[10px] font-bold">{{ $task->bugs->count() }}</span>
                        @endif
                    </h3>
                    @if($canReportBugs && $task->is_main_task === 'yes')
                        <button @click="showBugForm = !showBugForm"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-lg transition"
                                :class="showBugForm ? 'bg-gray-200 text-gray-700' : 'bg-red-600 text-white hover:bg-red-700 shadow-sm'">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span x-text="showBugForm ? 'Cancel' : 'Report Bug'"></span>
                        </button>
                    @endif
                </div>

                {{-- Bug time budget --}}
                @if($task->is_main_task === 'yes' && $task->estimated_time)
                    @php
                        $percentage = $task->project->bug_time_allocation_percentage ?? 20;
                        $totalBugBudget = ($task->estimated_time * $percentage) / 100;
                        $bugsCount = $task->bugs ? $task->bugs->count() : 0;
                        $perBugTime = $bugsCount > 0 ? round($totalBugBudget / $bugsCount, 2) : 0;
                    @endphp
                    <div class="flex items-center gap-4 mb-4 p-3 bg-red-50 border border-red-100 rounded-xl text-sm">
                        <div class="flex items-center">
                            <span class="text-red-400 font-medium mr-1.5">Budget:</span>
                            <span class="font-bold text-red-700">{{ $totalBugBudget }}h</span>
                            <span class="text-red-400 ml-1">({{ $percentage }}% of {{ $task->estimated_time }}h)</span>
                        </div>
                        @if($bugsCount > 0)
                            <span class="text-red-300">|</span>
                            <span class="text-red-600">{{ $bugsCount }} bug{{ $bugsCount > 1 ? 's' : '' }}</span>
                            <span class="text-red-300">|</span>
                            <span class="text-red-600 font-semibold">{{ $perBugTime }}h each</span>
                        @endif
                    </div>
                @endif

                {{-- Bug Report Form --}}
                @if($canReportBugs && $task->is_main_task === 'yes')
                <div x-show="showBugForm" x-transition x-cloak class="mb-6">
                    <div class="bg-white rounded-xl border-2 border-red-200 overflow-hidden shadow-sm">
                        <div class="bg-red-50 px-6 py-3 border-b border-red-100">
                            <h4 class="text-sm font-bold text-red-800">Report a New Bug</h4>
                        </div>
                        <form action="{{ route('tasks.bugs.store', $task) }}" method="POST" class="p-6 space-y-5">
                            @csrf
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Bug Title <span class="text-red-500">*</span></label>
                                <input type="text" name="title" required
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-red-500 focus:border-red-500 shadow-sm"
                                       placeholder="e.g., Login button not working on mobile">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Description</label>
                                <textarea name="description" id="bug_description_editor" class="tinymce-bug-editor"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Priority</label>
                                <div class="grid grid-cols-4 gap-2" x-data="{ priority: 'normal' }">
                                    @foreach(['low' => ['Low', 'gray'], 'normal' => ['Normal', 'blue'], 'high' => ['High', 'orange'], 'urgent' => ['Urgent', 'red']] as $val => [$lbl, $clr])
                                    <label @click="priority = '{{ $val }}'"
                                           class="flex items-center justify-center p-2.5 border-2 rounded-lg cursor-pointer transition text-xs font-semibold"
                                           :class="priority === '{{ $val }}' ? 'border-{{ $clr }}-400 bg-{{ $clr }}-50 text-{{ $clr }}-700' : 'border-gray-200 text-gray-500 hover:border-gray-300'">
                                        <input type="radio" name="priority" value="{{ $val }}" class="sr-only" {{ $val === 'normal' ? 'checked' : '' }}>
                                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $clr }}-400 mr-1.5"></span>
                                        {{ $lbl }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                                <button type="button" @click="showBugForm = false" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition">Cancel</button>
                                <button type="submit" class="px-5 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 shadow-sm transition">Submit Bug</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Bug List --}}
                @if($task->bugs && $task->bugs->count() > 0)
                    <div class="space-y-4">
                        @foreach($task->bugs as $bug)
                            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{ showComments: false }">
                                {{-- Bug header --}}
                                <div class="px-6 py-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex items-start flex-1 min-w-0">
                                            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center shrink-0 mt-0.5 mr-3">
                                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap mb-1">
                                                    <a href="{{ route('tasks.show', $bug) }}" class="text-base font-semibold text-gray-900 hover:text-red-600 transition">{{ $bug->title }}</a>
                                                    @php $pColors = ['urgent'=>'red','high'=>'orange','normal'=>'blue','low'=>'gray']; $pc = $pColors[$bug->priority] ?? 'gray'; @endphp
                                                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-{{ $pc }}-100 text-{{ $pc }}-700">{{ ucfirst($bug->priority) }}</span>
                                                </div>
                                                @if($bug->description)
                                                    <div class="text-sm text-gray-600 leading-relaxed line-clamp-3">{!! Str::limit(strip_tags($bug->description), 200) !!}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 ml-4 shrink-0">
                                            @if($bug->estimated_time)
                                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 font-mono">{{ $bug->estimated_time }}h</span>
                                            @endif
                                            @if($bug->status)
                                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold" style="background-color: {{ $bug->status->color }}15; color: {{ $bug->status->color }}; border: 1px solid {{ $bug->status->color }}30;">{{ $bug->status->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    {{-- Bug meta + comment toggle --}}
                                    <div class="flex items-center justify-between mt-3 ml-11">
                                        <div class="flex items-center gap-3 text-xs text-gray-400">
                                            @if($bug->creator)
                                                <span class="flex items-center gap-1">
                                                    <div class="w-4 h-4 rounded-full bg-red-400 text-white text-[9px] font-bold flex items-center justify-center">{{ strtoupper(substr($bug->creator->name, 0, 1)) }}</div>
                                                    {{ $bug->creator->name }}
                                                </span>
                                            @endif
                                            <span>{{ $bug->created_at->diffForHumans() }}</span>
                                            @if($bug->assignees && $bug->assignees->count() > 0)
                                                <span>Assigned: {{ $bug->assignees->pluck('name')->join(', ') }}</span>
                                            @endif
                                        </div>
                                        <button @click="showComments = !showComments"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                                                :class="showComments ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            <span x-text="showComments ? 'Hide Comments' : '{{ ($bug->comments ? $bug->comments->count() : 0) }} Comment{{ ($bug->comments ? $bug->comments->count() : 0) !== 1 ? "s" : "" }}'"></span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Bug comments section --}}
                                <div x-show="showComments" x-transition class="border-t border-gray-200">
                                    {{-- Existing comments --}}
                                    @if($bug->comments && $bug->comments->count() > 0)
                                        <div class="px-6 pt-4 pb-2 space-y-3 max-h-72 overflow-y-auto">
                                            @foreach($bug->comments as $comment)
                                                <div class="flex gap-3">
                                                    <div class="w-7 h-7 rounded-full bg-red-400 text-white text-[10px] font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}</div>
                                                    <div class="flex-1 bg-gray-50 rounded-xl p-3 border border-gray-100">
                                                        <div class="flex items-center justify-between mb-1">
                                                            <span class="text-sm font-semibold text-gray-800">{{ $comment->user->name ?? 'Unknown' }}</span>
                                                            <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                                        </div>
                                                        <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">{{ $comment->content }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="px-6 pt-4 pb-2 text-center">
                                            <p class="text-sm text-gray-400">No comments on this bug yet</p>
                                        </div>
                                    @endif

                                    {{-- Comment form - always visible when panel is open --}}
                                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                                        <form action="{{ route('tasks.comments.store', $bug) }}" method="POST">
                                            @csrf
                                            <div class="flex gap-3">
                                                <div class="w-7 h-7 rounded-full bg-indigo-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                                                <div class="flex-1">
                                                    <textarea name="content" rows="3" required
                                                              class="w-full text-sm rounded-xl border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500 resize-none"
                                                              placeholder="Write a comment about this bug..."></textarea>
                                                    <div class="flex justify-end mt-2">
                                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 shadow-sm transition">
                                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                                            Post Comment
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-xl border border-gray-200 py-10 text-center">
                        <svg class="w-8 h-8 mx-auto text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm text-gray-400">No bugs reported yet</p>
                    </div>
                @endif
            </section>

            {{-- ===== COMMENTS / ACTIVITY ===== --}}
            <section>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">
                    Activity
                    @if($task->comments && $task->comments->count() > 0)
                        <span class="text-gray-300 ml-1">({{ $task->comments->count() }})</span>
                    @endif
                </h3>

                {{-- New comment form --}}
                <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
                    <form action="{{ route('tasks.comments.store', $task) }}" method="POST">
                        @csrf
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-500 text-white text-xs font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                            <div class="flex-1">
                                <textarea name="content" rows="3" required
                                          class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 resize-none shadow-sm"
                                          placeholder="Leave a comment..."></textarea>
                                @error('content')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <div class="flex justify-end mt-2">
                                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 shadow-sm transition">Post Comment</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Comments list --}}
                @if($task->comments && $task->comments->count() > 0)
                    <div class="space-y-3">
                        @foreach($task->comments as $comment)
                            <div class="bg-white rounded-xl border border-gray-200 p-4 flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-500 text-white text-xs font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline justify-between mb-1">
                                        <span class="text-sm font-semibold text-gray-900">{{ $comment->user->name ?? 'Unknown' }}</span>
                                        <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $comment->content }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-sm text-gray-400 py-6">No comments yet</p>
                @endif
            </section>

        </div>

        {{-- ==================== SIDEBAR ==================== --}}
        <div class="hidden lg:block w-72 xl:w-80 shrink-0 border-l border-gray-200 bg-white">
            <div class="sticky top-[53px] p-6 space-y-6 max-h-[calc(100vh-53px)] overflow-y-auto">

                {{-- Assignees --}}
                @if($task->assignees && $task->assignees->count() > 0)
                <div>
                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Assignees</h4>
                    <div class="space-y-2">
                        @foreach($task->assignees as $assignee)
                            @php
                                $track = $assignee->getTrackInWorkspace(session('current_workspace_id'));
                                $role = $assignee->getRoleInWorkspace(session('current_workspace_id'));
                            @endphp
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-indigo-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr($assignee->name, 0, 1)) }}</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $assignee->name }}</p>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        @if($track)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-600">{{ $track->name }}</span>
                                        @endif
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-medium {{ $role === 'admin' ? 'bg-purple-50 text-purple-600' : ($role === 'member' ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-500') }}">{{ ucfirst($role) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <hr class="border-gray-100">

                {{-- Details --}}
                <div class="space-y-4">
                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Details</h4>

                    {{-- Priority --}}
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Priority</span>
                        @php $pMap = ['urgent'=>['Urgent','red'],'high'=>['High','orange'],'normal'=>['Normal','blue'],'low'=>['Low','gray']]; [$pLabel,$pColor] = $pMap[$task->priority] ?? ['None','gray']; @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-{{ $pColor }}-100 text-{{ $pColor }}-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $pColor }}-400 mr-1"></span>
                            {{ $pLabel }}
                        </span>
                    </div>

                    {{-- Due Date --}}
                    @if($task->due_date)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Due Date</span>
                        <span class="text-xs font-medium {{ $task->isOverdue() ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $task->due_date->format('M d, Y') }}
                            @if($task->isOverdue()) <span class="text-red-500 font-bold">!</span> @endif
                        </span>
                    </div>
                    @endif

                    {{-- Created --}}
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Created</span>
                        <span class="text-xs text-gray-700">{{ $task->created_at->format('M d, Y') }}</span>
                    </div>

                    {{-- Type --}}
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Type</span>
                        <span class="text-xs font-medium text-gray-700">{{ $task->type === 'bug' ? 'Bug' : 'Task' }}</span>
                    </div>
                </div>

                <hr class="border-gray-100">

                {{-- Time Tracking --}}
                <div>
                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Time Tracking</h4>
                    <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between items-baseline">
                            <span class="text-xs text-gray-500">Estimated</span>
                            <span class="text-sm font-bold text-gray-900">{{ $task->estimated_time ? $task->estimated_time . 'h' : '—' }}</span>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <span class="text-xs text-gray-500">Logged</span>
                            <span class="text-sm font-bold text-gray-900">{{ $task->getFormattedTimeLogged() }}</span>
                        </div>
                        @if($task->estimated_time && $task->creator)
                            <p class="text-[10px] text-gray-400 pt-1 border-t border-gray-200">Set by {{ $task->creator->name }} &bull; {{ $task->created_at->format('M d') }}</p>
                        @endif
                        @if($task->estimation_edited_by && $task->estimation_completed_at)
                            <p class="text-[10px] text-gray-400">Edited by {{ optional(\App\Models\User::find($task->estimation_edited_by))->name ?? 'Unknown' }} &bull; {{ \Carbon\Carbon::parse($task->estimation_completed_at)->format('M d') }}</p>
                        @endif
                    </div>
                </div>

                {{-- Tags --}}
                @if($task->tags && $task->tags->count() > 0)
                <hr class="border-gray-100">
                <div>
                    <h4 class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Tags</h4>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($task->tags as $tag)
                            <span class="px-2 py-0.5 rounded-lg text-xs font-medium" style="background-color: {{ $tag->color }}15; color: {{ $tag->color }}">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- TinyMCE for bug description --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function initBugTinyMCE() {
        const el = document.getElementById('bug_description_editor');
        if (!el) return;
        if (tinymce.get('bug_description_editor')) {
            tinymce.get('bug_description_editor').remove();
        }
        tinymce.init({
            selector: '#bug_description_editor',
            menubar: false,
            height: 200,
            plugins: 'lists link code image',
            toolbar: 'bold italic underline | bullist numlist | link image | blockquote code | removeformat',
            branding: false,
            statusbar: false,
            placeholder: 'Steps to reproduce, expected vs actual behavior...',
            content_style: 'body{font-family:Inter,-apple-system,sans-serif;font-size:14px;line-height:1.6;color:#374151;padding:12px}p{margin:0 0 8px}',
            setup: function(editor) {
                editor.on('change keyup', function() { editor.save(); });
            }
        });
    }
    initBugTinyMCE();
    document.addEventListener('click', function(e) {
        if (e.target.closest('[\\@click*="showBugForm"]')) {
            setTimeout(initBugTinyMCE, 200);
        }
    });
});
</script>
@endpush
@endsection
