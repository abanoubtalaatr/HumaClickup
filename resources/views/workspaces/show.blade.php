@extends('layouts.app')

@section('title', $workspace->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $workspace->name }}</h1>
                @if($workspace->description)
                    <p class="mt-1 text-sm text-gray-500">{{ $workspace->description }}</p>
                @endif
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('workspaces.members', $workspace) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Manage Members
                </a>
                <a href="{{ route('workspaces.edit', $workspace) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Edit
                </a>
                @if($workspace->id != session('current_workspace_id'))
                    <form method="POST" action="{{ route('workspaces.switch', $workspace) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            Switch to this Workspace
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Projects</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $workspace->projects->count() }}</dd>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Members</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $workspace->users->count() }}</dd>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Tasks</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $workspace->tasks->count() }}</dd>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Storage</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ number_format($workspace->getStorageUsagePercentage(), 1) }}%</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Projects</h2>
                <a href="{{ route('projects.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    New Project
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($workspace->projects as $project)
                    <a href="{{ route('projects.show', $project) }}" 
                       class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-4">
                        <div class="flex items-center mb-2">
                            <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center mr-3" 
                                 style="background-color: {{ $project->color ?? '#6366f1' }}20">
                                <span class="text-xl">{{ $project->icon ?? '📁' }}</span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $project->name }}</h3>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" 
                                 style="width: {{ $project->progress ?? 0 }}%"></div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">{{ $project->tasks->count() }} tasks</p>
                    </a>
                @empty
                    <div class="col-span-full text-center py-8 text-gray-500">
                        No projects yet. <a href="{{ route('projects.create') }}" class="text-indigo-600 hover:text-indigo-700">Create your first project</a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Members -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Members ({{ $workspace->users->count() }})</h2>
                <a href="{{ route('workspaces.members', $workspace) }}" 
                   class="text-sm text-indigo-600 hover:text-indigo-700">
                    Manage all members →
                </a>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <ul class="divide-y divide-gray-200">
                    @foreach($workspace->users->take(5) as $member)
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-medium">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $member->email }}</p>
                                    </div>
                                </div>
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $member->pivot->role === 'owner' ? 'bg-indigo-100 text-indigo-800' : ($member->pivot->role === 'admin' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst($member->pivot->role) }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if($workspace->users->count() > 5)
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                        <a href="{{ route('workspaces.members', $workspace) }}" 
                           class="text-sm text-indigo-600 hover:text-indigo-700">
                            View all {{ $workspace->users->count() }} members
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

