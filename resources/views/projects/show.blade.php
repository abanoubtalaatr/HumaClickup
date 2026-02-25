@extends('layouts.app')

@section('title', $project->name)

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-12 w-12 rounded-lg flex items-center justify-center mr-4"
                            style="background-color: {{ $project->color ?? '#6366f1' }}20">
                            <span class="text-2xl">{{ $project->icon ?? '📁' }}</span>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">{{ $project->name }}</h1>
                            @if ($project->description)
                                <p class="mt-1 text-sm text-gray-500">{{ $project->description }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('projects.tasks.kanban', $project) }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Kanban
                        </a>
                        <a href="{{ route('projects.edit', $project) }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Edit
                        </a>
                        <a href="{{ route('projects.tasks.create', $project) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            New Task
                        </a>

                        @if(auth()->user()->hasTestingTrackInWorkspace($project->workspace_id) && auth()->user()->isMemberOnlyInWorkspace($project->workspace_id) || auth()->user()->isAdminInWorkspace($project->workspace_id) || auth()->user()->isOwnerInWorkspace($project->workspace_id))
                        <a href="{{ route('projects.assign-testers', $project) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Assign Testers
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-6">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Tasks</dt>

                                    <dd class="text-lg font-medium text-gray-900">{{ $project->tasks_count ?? 0 }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $project->completed_tasks_count ?? 0 }}
                                    </dd>
                                </dl>
                            </div>


                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Time Logged</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $project->time_logged_formatted ?? '0h' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Progress</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ number_format($project->progress ?? 0, 0) }}%</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Project Details Card -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Project Details</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5">
                    {{-- Creator --}}
                    @if($project->createdBy)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Created By</dt>
                        
                        <dd class="flex items-center gap-2 mt-2">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:{{ $project->color ?? '#6366f1' }}">
                                {{ strtoupper(substr($project->createdBy->name, 0, 2)) }}
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $project->createdBy->name }}</span>
                        </dd>
                        
                    </div>
                    @endif
            
                    {{-- Date Information Container --}}
                    <div class="lg:col-span-3 col-span-2">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Timeline</dt>
                        <hr class="mt-2">
                        <dd class="grid grid-cols-3 gap-4 mt-2">
                            {{-- Start Date --}}
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Start Date</div>
                                <div class="text-sm font-medium text-gray-900">
                                    @if($project->start_date)
                                        {{ $project->start_date->format('M j, Y') }}
                                    @else
                                        <span class="text-gray-400">Not set</span>
                                    @endif
                                </div>
                            </div>
            
                            {{-- End Date --}}
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">End Date</div>
                                <div>
                                    @if($project->end_date)
                                        @if($project->end_date->isPast())
                                            <span class="inline-flex items-center gap-1 text-sm font-semibold text-red-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ $project->end_date->format('M j, Y') }}
                                            </span>
                                            <span class="block text-xs font-semibold text-red-500 mt-0.5">ENDED</span>
                                        @else
                                            <span class="text-sm font-medium text-gray-900">{{ $project->end_date->format('M j, Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-sm text-gray-400">Not set</span>
                                    @endif
                                </div>
                            </div>
            
                            {{-- Total Days --}}
                            <div>
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total Days</div>
                                <div class="text-sm font-medium text-gray-900">
                                    @if($project->total_days)
                                        {{ $project->total_days }} days
                                        @if($project->working_days)
                                            <span class="text-xs text-gray-500 block">({{ $project->working_days }} working)</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">Not set</span>
                                    @endif
                                </div>
                            </div>
                        </dd>
                    </div>
            
                    {{-- Due Date --}}
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Due Date</dt>
                        <dd>
                            @if($project->due_date)
                                @if($project->isOverdue())
                                    <span class="inline-flex items-center gap-1 text-sm font-semibold text-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $project->due_date->format('M j, Y') }}
                                    </span>
                                    <span class="block text-xs font-semibold text-red-500 mt-0.5">OVERDUE</span>
                                @else
                                    <span class="text-sm font-medium text-gray-900">{{ $project->due_date->format('M j, Y') }}</span>
                                @endif
                            @else
                                <span class="text-sm text-gray-400">Not set</span>
                            @endif
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('projects.tasks.kanban', $project) }}"
                    class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Kanban Board</h3>
                            <p class="text-sm text-gray-500">View and manage tasks in Kanban view</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('projects.tasks.list', $project) }}"
                    class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Task List</h3>
                            <p class="text-sm text-gray-500">View all tasks in a list format</p>
                        </div>
                    </div>
                </a>
            </div>
            <div class="mb-6 bg-white shadow rounded-lg p-6 my-5 mb-4" style="margin-top: 1.5rem !important;">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-medium text-gray-900">Project Progress</h3>
                    <span
                        class="text-sm font-medium text-gray-600">{{ number_format($project->progress ?? 0, 1) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-indigo-600 h-3 rounded-full transition-all duration-300"
                        style="width: {{ $project->progress ?? 0 }}%"></div>
                </div>
            </div>

            {{-- members and testers --}}
{{-- members and testers side by side --}}
<div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="p-5">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Guest Members Section (from project_members table) --}}
            @php
                $guestMembers = $project->projectMembers->where('role', 'guest');
            @endphp
            <div>
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Guests</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $guestMembers->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>

                @if ($guestMembers->count())
                    <div class="space-y-3 max-h-80 overflow-y-auto pr-2">
                        @foreach ($guestMembers as $member)
                            <div class="p-3 border rounded bg-gray-50 hover:bg-gray-100 transition-colors duration-150">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 mr-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 font-semibold">
                                                {{ strtoupper(substr($member->user->name ?? 'U', 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-800 truncate">{{ $member->user->name ?? '-' }}
                                        </div>
                                        <div class="text-sm text-gray-600 truncate">
                                            {{ $member->user->email ?? '-' }}</div>
                                            
                                        @if ($member->track)
                                            <div class="text-xs text-gray-500 mt-1">
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ $member->track->name ?? '-' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 border border-dashed border-gray-300 rounded-lg">
                        <div class="text-gray-400 mb-2">
                            <svg class="h-12 w-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <div class="text-sm text-gray-500">No guests assigned</div>
                    </div>
                @endif
            </div>

            {{-- Testers Section (from project_testers table) --}}
            <div>
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Testers</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $project->testers->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>

                @if ($project->testers && $project->testers->count())
                    <div class="space-y-3 max-h-80 overflow-y-auto pr-2">
                        @foreach ($project->testers as $projectTester)
                            <div class="p-3 border rounded bg-gray-50 hover:bg-gray-100 transition-colors duration-150">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 mr-3">
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <span class="text-green-600 font-semibold">
                                                {{ strtoupper(substr($projectTester->tester->name ?? 'U', 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-800 truncate">{{ $projectTester->tester->name ?? '-' }}
                                        </div>
                                        <div class="text-sm text-gray-600 truncate">
                                            {{ $projectTester->tester->email ?? '-' }}</div>
                                        @if ($projectTester->status)
                                            <div class="text-xs text-gray-500 mt-1">
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded">{{ ucfirst($projectTester->status) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 border border-dashed border-gray-300 rounded-lg">
                        <div class="text-gray-400 mb-2">
                            <svg class="h-12 w-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <div class="text-sm text-gray-500">No testers assigned</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

        </div>
    </div>
@endsection
