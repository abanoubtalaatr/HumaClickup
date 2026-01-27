@extends('layouts.app')

@section('title', 'Workspace Members')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8" x-data="membersManager()">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Team Members</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if($isAdmin)
                            Manage all members of {{ $workspace->name }}
                        @else
                            Manage guests you've created in {{ $workspace->name }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    @if($isAdmin)
                        <a href="{{ route('workspaces.tracks.index', $workspace) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Manage Tracks
                        </a>
                    @endif
                    <a href="{{ route('workspaces.show', $workspace) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Back to Workspace
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/50 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="ml-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="mb-6 flex flex-wrap gap-3">
            <button @click="showCreateModal = true" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Create New {{ $isAdmin ? 'Member' : 'Guest' }}
            </button>
            @if($canInviteExisting)
                <button @click="showInviteModal = true" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Invite Existing User
                </button>
            @endif
            @if($isAdmin)
                <button @click="showAssignModal = true; selectedMemberId = ''; selectedGuestIds = []; assignGuestSearch = '';" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Assign Guests to Member
                </button>
            @endif
        </div>

        @if($isAdmin)
        <!-- Filter Toggle -->
        <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">View:</label>
                <div class="flex space-x-2">
                    <a href="{{ route('workspaces.members', array_merge([$workspace], request()->except('filter'), ['filter' => 'all'])) }}" 
                       class="px-4 py-2 rounded-md text-sm font-medium {{ ($filter ?? 'all') === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        All
                    </a>
                    <a href="{{ route('workspaces.members', array_merge([$workspace], request()->except('filter'), ['filter' => 'guests'])) }}" 
                       class="px-4 py-2 rounded-md text-sm font-medium {{ ($filter ?? 'all') === 'guests' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        All Guests ({{ $allGuests->count() ?? 0 }})
                    </a>
                    <a href="{{ route('workspaces.members', array_merge([$workspace], request()->except('filter'), ['filter' => 'members'])) }}" 
                       class="px-4 py-2 rounded-md text-sm font-medium {{ ($filter ?? 'all') === 'members' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        All Members ({{ $allMembers->count() ?? 0 }})
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Role Permissions Info -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-2 flex items-center">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Role Permissions
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800 dark:text-blue-200">
                <div>
                    <p><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 mr-2">Owner</span>Full access, delete workspace, manage billing</p>
                    <p class="mt-2"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 mr-2">Admin</span>Manage all members, projects, and tasks</p>
                </div>
                <div>
                    <p><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 mr-2">Member</span>Create tasks/projects, manage own guests only</p>
                    <p class="mt-2"><span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 mr-2">Guest</span>View & work on assigned tasks only</p>
                </div>
            </div>
        </div>

        @if($isAdmin)
            <!-- Admin: Members Statistics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                @php
                    $allMembersForStats = $workspace->users;
                    $memberCounts = $allMembersForStats->groupBy('pivot.role')->map->count();
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $memberCounts->get('owner', 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Owners</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $memberCounts->get('admin', 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Admins</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $memberCounts->get('member', 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Members</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $memberCounts->get('guest', 0) }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Guests</div>
                </div>
            </div>

            @if(($filter ?? 'all') === 'guests')
                <!-- Show All Guests -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">All Guests ({{ ($allGuestsForView ?? $allGuests)->count() }})</h3>
                        </div>
                        <!-- Search Input -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" 
                                   x-model="guestSearch" 
                                   placeholder="Search guests by name or email..." 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    @if(($allGuestsForView ?? $allGuests)->count() > 0)
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach(($allGuestsForView ?? $allGuests) as $guest)
                                <li class="px-4 py-3 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                    data-guest-name="{{ strtolower($guest->name) }}"
                                    data-guest-email="{{ strtolower($guest->email) }}"
                                    x-show="!guestSearch || $el.dataset.guestName.includes(guestSearch.toLowerCase()) || $el.dataset.guestEmail.includes(guestSearch.toLowerCase())">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center min-w-0 flex-1">
                                            <div class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center text-white text-sm font-medium">
                                                {{ strtoupper(substr($guest->name, 0, 1)) }}
                                            </div>
                                            <div class="ml-3 min-w-0 flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $guest->name }}</p>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                                        Guest
                                                    </span>
                                                    @php
                                                        $guestTrack = $guest->pivot->track_id ? $tracks->firstWhere('id', $guest->pivot->track_id) : null;
                                                    @endphp
                                                    @if($guestTrack)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                                                              style="background-color: {{ $guestTrack->color }}20; color: {{ $guestTrack->color }}">
                                                            {{ $guestTrack->name }}
                                                        </span>
                                                    @endif
                                                    @if($guest->pivot && $guest->pivot->created_by_user_id)
                                                        @php
                                                            $creator = $workspace->users->find($guest->pivot->created_by_user_id);
                                                        @endphp
                                                        @if($creator)
                                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                                Created by: {{ $creator->name }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="text-xs text-gray-400 dark:text-gray-500 italic">Unassigned</span>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $guest->email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            No guests found
                        </div>
                    @endif
                    <!-- No results message when search filters everything out -->
                    <div x-show="guestSearch && Array.from(document.querySelectorAll('li[data-guest-name]')).every(el => el.offsetParent === null)" 
                         x-cloak
                         class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                        No guests match your search
                    </div>
                </div>
            @elseif(($filter ?? 'all') === 'members')
                <!-- Show All Members -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">All Members ({{ $allMembers->count() }})</h3>
                    </div>
                    @if($allMembers->count() > 0)
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($allMembers as $member)
                                <li class="px-4 py-3 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center min-w-0 flex-1">
                                            <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-semibold
                                                {{ $member->pivot->role === 'owner' ? 'bg-purple-500' : '' }}
                                                {{ $member->pivot->role === 'admin' ? 'bg-indigo-500' : '' }}
                                                {{ $member->pivot->role === 'member' ? 'bg-blue-500' : '' }}">
                                                {{ strtoupper(substr($member->name, 0, 1)) }}
                                            </div>
                                            <div class="ml-3 min-w-0 flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $member->name }}</p>
                                                    @if($member->id === auth()->id())
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">(You)</span>
                                                    @endif
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                        {{ $member->pivot->role === 'owner' ? 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' : '' }}
                                                        {{ $member->pivot->role === 'admin' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200' : '' }}
                                                        {{ $member->pivot->role === 'member' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' : '' }}">
                                                        {{ ucfirst($member->pivot->role) }}
                                                    </span>
                                                    @php
                                                        $memberTrack = $member->pivot->track_id ? $tracks->firstWhere('id', $member->pivot->track_id) : null;
                                                    @endphp
                                                    @if($memberTrack)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                                                              style="background-color: {{ $memberTrack->color }}20; color: {{ $memberTrack->color }}">
                                                            {{ $memberTrack->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member->email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            No members found
                        </div>
                    @endif
                </div>
            @else
                <!-- Admin: Members with their Guests (Default View) -->
                @php
                    $membersList = $workspace->users->whereIn('pivot.role', ['owner', 'admin', 'member']);
                    $guestsList = $workspace->users->where('pivot.role', 'guest');
                @endphp
                
                @foreach($membersList as $member)
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-semibold
                                    {{ $member->pivot->role === 'owner' ? 'bg-purple-500' : '' }}
                                    {{ $member->pivot->role === 'admin' ? 'bg-indigo-500' : '' }}
                                    {{ $member->pivot->role === 'member' ? 'bg-blue-500' : '' }}">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <div class="flex items-center space-x-2">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $member->name }}</p>
                                        @if($member->id === auth()->id())
                                            <span class="text-xs text-gray-500 dark:text-gray-400">(You)</span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            {{ $member->pivot->role === 'owner' ? 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' : '' }}
                                            {{ $member->pivot->role === 'admin' ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200' : '' }}
                                            {{ $member->pivot->role === 'member' ? 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200' : '' }}">
                                            {{ ucfirst($member->pivot->role) }}
                                        </span>
                                        @php
                                            $memberTrack = $member->pivot->track_id ? $tracks->firstWhere('id', $member->pivot->track_id) : null;
                                        @endphp
                                        @if($memberTrack)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                                                  style="background-color: {{ $memberTrack->color }}20; color: {{ $memberTrack->color }}">
                                                {{ $memberTrack->name }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $member->tasks_count == 0 ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' }}">
                                            <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            {{ $member->tasks_count }} {{ $member->tasks_count === 1 ? 'task' : 'tasks' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $member->email }}</p>
                                </div>
                            </div>
                            @if($member->id !== $workspace->owner_id)
                                <div class="flex items-center space-x-2">
                                    <button @click="openEditModal({{ json_encode([
                                        'id' => $member->id,
                                        'name' => $member->name,
                                        'email' => $member->email,
                                        'whatsapp_number' => $member->whatsapp_number,
                                        'slack_channel_link' => $member->slack_channel_link,
                                        'role' => $member->pivot->role,
                                        'track_id' => $member->pivot->track_id,
                                        'attendance_days' => is_string($member->pivot->attendance_days ?? '') ? json_decode($member->pivot->attendance_days, true) : ($member->pivot->attendance_days ?? [])
                                    ]) }})" 
                                            class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button @click="openRemoveModal({{ json_encode([
                                        'id' => $member->id,
                                        'name' => $member->name,
                                        'role' => $member->pivot->role
                                    ]) }})" 
                                            class="text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Guests created by this member -->
                    @php
                        $memberGuests = $guestsList->where('pivot.created_by_user_id', $member->id);
                    @endphp
                    @if($memberGuests->count() > 0)
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($memberGuests as $guest)
                                <li class="px-4 py-3 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 pl-12 {{ $guest->tasks_count == 0 ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center min-w-0 flex-1">
                                            <div class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center text-white text-sm font-medium">
                                                {{ strtoupper(substr($guest->name, 0, 1)) }}
                                            </div>
                                            <div class="ml-3 min-w-0 flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $guest->name }}</p>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                                        Guest
                                                    </span>
                                                    @php
                                                        $guestTrack = $guest->pivot->track_id ? $tracks->firstWhere('id', $guest->pivot->track_id) : null;
                                                    @endphp
                                                    @if($guestTrack)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                                                              style="background-color: {{ $guestTrack->color }}20; color: {{ $guestTrack->color }}">
                                                            {{ $guestTrack->name }}
                                                        </span>
                                                    @endif
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $guest->tasks_count == 0 ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' }}">
                                                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                        </svg>
                                                        {{ $guest->tasks_count }} {{ $guest->tasks_count === 1 ? 'task' : 'tasks' }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $guest->email }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2 ml-4">
                                            <button @click="openEditModal({{ json_encode([
                                                'id' => $guest->id,
                                                'name' => $guest->name,
                                                'email' => $guest->email,
                                                'whatsapp_number' => $guest->whatsapp_number,
                                                'slack_channel_link' => $guest->slack_channel_link,
                                                'role' => $guest->pivot->role,
                                                'track_id' => $guest->pivot->track_id,
                                                'attendance_days' => is_string($guest->pivot->attendance_days) ? json_decode($guest->pivot->attendance_days, true) : ($guest->pivot->attendance_days ?? [])
                                            ]) }})" 
                                                    class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button @click="openRemoveModal({{ json_encode([
                                                'id' => $guest->id,
                                                'name' => $guest->name,
                                                'role' => $guest->pivot->role
                                            ]) }})" 
                                                    class="text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="px-4 py-3 sm:px-6 pl-12 text-sm text-gray-500 dark:text-gray-400 italic">
                            No guests created by this member
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- Unassigned Guests (created before the system tracked creators) -->
            @php
                $unassignedGuests = $guestsList->whereNull('pivot.created_by_user_id');
            @endphp
            @if($unassignedGuests->count() > 0)
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-6">
                    <div class="px-4 py-4 sm:px-6 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Unassigned Guests</h3>
                    </div>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($unassignedGuests as $guest)
                            <li class="px-4 py-3 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $guest->tasks_count == 0 ? 'bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500' : '' }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center min-w-0 flex-1">
                                        <div class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center text-white text-sm font-medium">
                                            {{ strtoupper(substr($guest->name, 0, 1)) }}
                                        </div>
                                        <div class="ml-3 min-w-0 flex-1">
                                            <div class="flex items-center space-x-2">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $guest->name }}</p>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                                    Guest
                                                </span>
                                                @php
                                                    $guestTrack = $guest->pivot->track_id ? $tracks->firstWhere('id', $guest->pivot->track_id) : null;
                                                @endphp
                                                @if($guestTrack)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                                                          style="background-color: {{ $guestTrack->color }}20; color: {{ $guestTrack->color }}">
                                                        {{ $guestTrack->name }}
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $guest->tasks_count == 0 ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' }}">
                                                    <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                    </svg>
                                                    {{ $guest->tasks_count }} {{ $guest->tasks_count === 1 ? 'task' : 'tasks' }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $guest->email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 ml-4">
                                        <button @click="openEditModal({{ json_encode([
                                            'id' => $guest->id,
                                            'name' => $guest->name,
                                            'email' => $guest->email,
                                            'whatsapp_number' => $guest->whatsapp_number,
                                            'slack_channel_link' => $guest->slack_channel_link,
                                            'role' => $guest->pivot->role,
                                            'track_id' => $guest->pivot->track_id,
                                            'attendance_days' => is_string($guest->pivot->attendance_days) ? json_decode($guest->pivot->attendance_days, true) : ($guest->pivot->attendance_days ?? [])
                                        ]) }})" 
                                                class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button @click="openRemoveModal({{ json_encode([
                                            'id' => $guest->id,
                                            'name' => $guest->name,
                                            'role' => $guest->pivot->role
                                        ]) }})" 
                                                class="text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @endif

        @else
            <!-- Member View: Only show guests they created -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        My Guests ({{ $members->count() }})
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Guests you've created and can manage
                    </p>
                </div>
                @if($members->count() > 0)
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($members as $guest)
                            <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $guest->tasks_count == 0 ? 'border-l-4 border-red-500 bg-red-50 dark:bg-red-900/20' : '' }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center min-w-0 flex-1">
                                        <div class="h-10 w-10 rounded-full bg-gray-400 flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($guest->name, 0, 1)) }}
                                        </div>
                                        <div class="ml-4 min-w-0 flex-1">
                                            <div class="flex items-center space-x-2">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $guest->name }}</p>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                                    Guest
                                                </span>
                                                @php
                                                    $guestTrack = $guest->pivot->track_id ? $tracks->firstWhere('id', $guest->pivot->track_id) : null;
                                                @endphp
                                                @if($guestTrack)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" 
                                                          style="background-color: {{ $guestTrack->color }}20; color: {{ $guestTrack->color }}">
                                                        {{ $guestTrack->name }}
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $guest->tasks_count == 0 ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' : 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' }}">
                                                    <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                    </svg>
                                                    {{ $guest->tasks_count }} {{ $guest->tasks_count === 1 ? 'task' : 'tasks' }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $guest->email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3 ml-4">
                                        <button @click="openEditModal({{ json_encode([
                                            'id' => $guest->id,
                                            'name' => $guest->name,
                                            'email' => $guest->email,
                                            'whatsapp_number' => $guest->whatsapp_number,
                                            'slack_channel_link' => $guest->slack_channel_link,
                                            'role' => $guest->pivot->role,
                                            'track_id' => $guest->pivot->track_id,
                                            'attendance_days' => is_string($guest->pivot->attendance_days) ? json_decode($guest->pivot->attendance_days, true) : ($guest->pivot->attendance_days ?? [])
                                        ]) }})" 
                                                class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button @click="openRemoveModal({{ json_encode([
                                            'id' => $guest->id,
                                            'name' => $guest->name,
                                            'role' => $guest->pivot->role
                                        ]) }})" 
                                                class="text-gray-400 hover:text-red-600 dark:hover:text-red-400">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="px-4 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No guests yet</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Create your first guest to assign tasks to them.</p>
                        <button @click="showCreateModal = true"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Create Guest
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Create New Member Modal -->
    <div x-show="showCreateModal" x-cloak class="relative z-50" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div @click.stop class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="absolute right-0 top-0 pr-4 pt-4">
                        <button @click="showCreateModal = false" class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">Create New {{ $isAdmin ? 'Member' : 'Guest' }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create a new user account with default password "password"</p>
                        </div>
                    </div>
                    <form action="{{ route('workspaces.members.create', $workspace) }}" method="POST" class="mt-5">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="create_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                                <input type="text" name="name" id="create_name" required
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="John Doe">
                            </div>
                            <div>
                                <label for="create_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                                <input type="email" name="email" id="create_email" required
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="john@example.com">
                            </div>
                            <div>
                                <label for="create_whatsapp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp Number</label>
                                <input type="text" name="whatsapp_number" id="create_whatsapp"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="+1234567890">
                            </div>
                            <div>
                                <label for="create_slack" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slack Channel Link</label>
                                <input type="url" name="slack_channel_link" id="create_slack"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="https://workspace.slack.com/archives/C01234567">
                            </div>
                            <div>
                                <label for="create_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                <select name="role" id="create_role" x-model="newMemberRole"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="newMemberRole === 'guest' || newMemberRole === 'member'">
                                <label for="create_track_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Specialization/Track</label>
                                <select name="track_id" id="create_track_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select Track</option>
                                    @foreach($tracks as $track)
                                        <option value="{{ $track->id }}">{{ $track->name }}</option>
                                    @endforeach
                                </select>
                                @if(!$isAdmin)
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty to use your track</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">
                                Create {{ $isAdmin ? 'Member' : 'Guest' }}
                            </button>
                            <button type="button" @click="showCreateModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($canInviteExisting)
    <!-- Invite Existing User Modal (Admin only) -->
    <div x-show="showInviteModal" x-cloak class="relative z-50" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div @click.stop class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="absolute right-0 top-0 pr-4 pt-4">
                        <button @click="showInviteModal = false" class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">Invite Existing User</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add an existing user to this workspace</p>
                        </div>
                    </div>
                    <form action="{{ route('workspaces.members.invite', $workspace) }}" method="POST" class="mt-5">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="invite_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                                <input type="email" name="email" id="invite_email" required
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="existing@example.com">
                            </div>
                            <div>
                                <label for="invite_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                <select name="role" id="invite_role" x-model="inviteRole"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="inviteRole === 'guest' || inviteRole === 'member'">
                                <label for="invite_track_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Specialization/Track</label>
                                <select name="track_id" id="invite_track_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select Track</option>
                                    @foreach($tracks as $track)
                                        <option value="{{ $track->id }}">{{ $track->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">
                                Send Invite
                            </button>
                            <button type="button" @click="showInviteModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isAdmin)
    <!-- Assign Guests to Member Modal -->
    <div x-show="showAssignModal" x-cloak class="relative z-50" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div @click.stop class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">
                    <div class="absolute right-0 top-0 pr-4 pt-4">
                        <button @click="showAssignModal = false" class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">Assign Guests to Member</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Select a member and choose guests to assign to them</p>
                        </div>
                    </div>
                    <form action="{{ route('workspaces.members.assign-guests', $workspace) }}" method="POST" class="mt-5">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="assign_member_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Member</label>
                                <select name="member_id" id="assign_member_id" required x-model="selectedMemberId"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Choose a member...</option>
                                    @foreach($allMembers ?? [] as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }} ({{ ucfirst($member->pivot->role) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Select Guests (<span x-text="selectedGuestIds.length"></span> selected)
                                </label>
                                <!-- Search Input for Guests -->
                                <div class="relative mb-3">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <input type="text" 
                                           x-model="assignGuestSearch" 
                                           placeholder="Search guests by name or email..." 
                                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div class="max-h-64 overflow-y-auto border border-gray-300 dark:border-gray-600 rounded-md p-3 bg-gray-50 dark:bg-gray-700/50">
                                    @if(($allGuests ?? collect())->count() > 0)
                                        <div class="space-y-2">
                                            @foreach($allGuests ?? [] as $guest)
                                                <label class="flex items-center space-x-2 p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded cursor-pointer"
                                                       data-guest-name="{{ strtolower($guest->name) }}"
                                                       data-guest-email="{{ strtolower($guest->email) }}"
                                                       x-show="!assignGuestSearch || $el.dataset.guestName.includes(assignGuestSearch.toLowerCase()) || $el.dataset.guestEmail.includes(assignGuestSearch.toLowerCase())">
                                                    <input type="checkbox" name="guest_ids[]" value="{{ $guest->id }}" 
                                                           @change="toggleGuest({{ $guest->id }}, $event.target.checked)"
                                                           :checked="selectedGuestIds.includes({{ $guest->id }})"
                                                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                    <div class="flex-1">
                                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $guest->name }}</span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">{{ $guest->email }}</span>
                                                        @if($guest->pivot && $guest->pivot->created_by_user_id)
                                                            @php
                                                                $creator = $workspace->users->find($guest->pivot->created_by_user_id);
                                                            @endphp
                                                            @if($creator)
                                                                <span class="text-xs text-gray-400 dark:text-gray-500 ml-2">(Currently: {{ $creator->name }})</span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">No guests available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="!selectedMemberId || selectedGuestIds.length === 0"
                                    :class="!selectedMemberId || selectedGuestIds.length === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                                    class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 sm:ml-3 sm:w-auto">
                                Assign Guests
                            </button>
                            <button type="button" @click="showAssignModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Edit Member Modal -->
    <div x-show="showEditModal" x-cloak class="relative z-50" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div @click.stop class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="absolute right-0 top-0 pr-4 pt-4">
                        <button @click="showEditModal = false" class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">Edit Member</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Update <span x-text="editingMember.name"></span>'s information</p>
                        </div>
                    </div>
                    <form :action="`/workspaces/{{ $workspace->id }}/members/${editingMember.id}`" method="POST" class="mt-5">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label for="edit_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                                <input type="email" name="email" id="edit_email" x-model="editingMember.email" required
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="edit_whatsapp" class="block text-sm font-medium text-gray-700 dark:text-gray-300">WhatsApp Number</label>
                                <input type="text" name="whatsapp_number" id="edit_whatsapp" x-model="editingMember.whatsapp_number"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="+1234567890">
                                @error('whatsapp_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="edit_slack" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slack Channel Link</label>
                                <input type="url" name="slack_channel_link" id="edit_slack" x-model="editingMember.slack_channel_link"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                       placeholder="https://workspace.slack.com/archives/C01234567">
                                @error('slack_channel_link')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="edit_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                <select name="role" id="edit_role" x-model="editingMember.role"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div x-show="editingMember.role === 'guest' || editingMember.role === 'member'">
                                <label for="edit_track_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Specialization/Track</label>
                                <select name="track_id" id="edit_track_id" x-model="editingMember.track_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Select Track</option>
                                    @foreach($tracks as $track)
                                        <option value="{{ $track->id }}">{{ $track->name }}</option>
                                    @endforeach
                                </select>
                                @error('track_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Attendance Days (Guests Only) -->
                            <div x-show="editingMember.role === 'guest'">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attendance Days</label>
                                <div class="space-y-2">
                                    @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                                        <label class="inline-flex items-center mr-4">
                                            <input type="checkbox" name="attendance_days[]" value="{{ $day }}" 
                                                   x-model="editingMember.attendance_days"
                                                   class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($day) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('attendance_days')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="inline-flex w-full justify-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 sm:ml-3 sm:w-auto">
                                Update Member
                            </button>
                            <button type="button" @click="showEditModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Remove Member Modal -->
    <div x-show="showRemoveModal" x-cloak class="relative z-50" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div @click.stop class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <div class="absolute right-0 top-0 pr-4 pt-4">
                        <button @click="showRemoveModal = false" class="rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">Remove Member</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Are you sure you want to remove <span class="font-medium" x-text="removingMember.name"></span>?
                            </p>
                        </div>
                    </div>

                    <!-- Loading state -->
                    <div x-show="loadingTasks" class="mt-4 flex justify-center">
                        <svg class="animate-spin h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <!-- Tasks to reassign -->
                    <div x-show="!loadingTasks && memberTasks.length > 0" class="mt-4">
                        <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 mb-4">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                <strong x-text="removingMember.name"></strong> has <strong x-text="memberTasks.length"></strong> assigned task(s). 
                                Choose what to do with them:
                            </p>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reassign tasks to:</label>
                                <select x-model="reassignTo" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Leave unassigned</option>
                                    <template x-for="m in otherMembers" :key="m.id">
                                        <option :value="m.id" x-text="m.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="max-h-40 overflow-y-auto bg-gray-50 dark:bg-gray-700/50 rounded-lg p-2">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Tasks to be reassigned:</p>
                                <ul class="space-y-1">
                                    <template x-for="task in memberTasks" :key="task.id">
                                        <li class="text-sm text-gray-700 dark:text-gray-300 flex items-center">
                                            <span class="h-2 w-2 rounded-full bg-indigo-400 mr-2"></span>
                                            <span x-text="task.title"></span>
                                            <span class="text-gray-400 dark:text-gray-500 text-xs ml-2" x-text="task.project?.name ? `(${task.project.name})` : ''"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <form :action="`/workspaces/{{ $workspace->id }}/members/${removingMember.id}`" method="POST" class="mt-5">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="reassign_to" :value="reassignTo">
                        <div class="sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                Remove Member
                            </button>
                            <button type="button" @click="showRemoveModal = false"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function membersManager() {
    return {
        showCreateModal: false,
        showInviteModal: false,
        showAssignModal: false,
        showEditModal: false,
        showRemoveModal: false,
        guestSearch: '',
        assignGuestSearch: '',
        newMemberRole: '{{ in_array("guest", $roles) ? "guest" : (in_array("member", $roles) ? "member" : "admin") }}',
        inviteRole: 'member',
        selectedMemberId: '',
        selectedGuestIds: [],
        editingMember: { id: null, name: '', email: '', whatsapp_number: '', slack_channel_link: '', role: '', track_id: null, attendance_days: [] },
        removingMember: { id: null, name: '', role: '' },
        memberTasks: [],
        otherMembers: [],
        reassignTo: '',
        loadingTasks: false,

        openEditModal(member) {
            this.editingMember = { ...member };
            this.showEditModal = true;
        },

        async openRemoveModal(member) {
            this.removingMember = { ...member };
            this.memberTasks = [];
            this.otherMembers = [];
            this.reassignTo = '';
            this.showRemoveModal = true;
            this.loadingTasks = true;

            try {
                const response = await fetch(`{{ url('workspaces') }}/{{ $workspace->id }}/members/${member.id}/tasks`);
                const data = await response.json();
                this.memberTasks = data.tasks || [];
                this.otherMembers = data.members || [];
            } catch (error) {
                console.error('Failed to load member tasks:', error);
            } finally {
                this.loadingTasks = false;
            }
        },

        toggleGuest(guestId, isChecked) {
            if (isChecked) {
                if (!this.selectedGuestIds.includes(guestId)) {
                    this.selectedGuestIds.push(guestId);
                }
            } else {
                this.selectedGuestIds = this.selectedGuestIds.filter(id => id !== guestId);
            }
        }
    }
}
</script>
@endpush
@endsection
