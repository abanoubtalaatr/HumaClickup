@extends('layouts.app')

@section('title', 'Workspaces')

@section('content')
@php
    $canCreateWorkspace = !session('current_workspace_id') || auth()->user()->isAdminInWorkspace(session('current_workspace_id'));
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Workspaces</h1>
                <p class="mt-1 text-sm text-gray-500">Manage your workspaces</p>
            </div>
            @if($canCreateWorkspace)
            <a href="{{ route('workspaces.create') }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Workspace
            </a>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($workspaces as $workspace)
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $workspace->name }}</h3>
                            @if($workspace->description)
                                <p class="mt-1 text-sm text-gray-500">{{ $workspace->description }}</p>
                            @endif
                        </div>
                        @if($workspace->id == session('current_workspace_id'))
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                Active
                            </span>
                        @endif
                    </div>
                    
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                        <span>{{ $workspace->users->count() }} members</span>
                        <span>{{ $workspace->projects->count() }} projects</span>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        @php
                            $isGuestInThisWorkspace = auth()->user()->isGuestInWorkspace($workspace->id);
                        @endphp
                        @if($workspace->id != session('current_workspace_id') && !$isGuestInThisWorkspace)
                            <form method="POST" action="{{ route('workspaces.switch', $workspace) }}" class="flex-1">
                                @csrf
                                <button type="submit" 
                                   class="w-full text-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Switch
                                </button>
                            </form>
                        @endif
                        @if(!$isGuestInThisWorkspace)
                        <a href="{{ route('workspaces.show', $workspace) }}" 
                           class="flex-1 text-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            View
                        </a>
                        @else
                        <span class="flex-1 text-center px-4 py-2 text-sm font-medium text-gray-500">
                            Guest Access
                        </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No workspaces</h3>
                    @if($canCreateWorkspace)
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new workspace.</p>
                        <div class="mt-6">
                            <a href="{{ route('workspaces.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                New Workspace
                            </a>
                        </div>
                    @else
                        <p class="mt-1 text-sm text-gray-500">You don't have access to any workspaces yet. Please contact an administrator.</p>
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

