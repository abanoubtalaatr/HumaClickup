@extends('layouts.app')

@section('title', 'Viewing Dashboard: ' . $viewingAsUser->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Admin View Banner -->
        <div class="mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="h-14 w-14 rounded-full bg-white/20 flex items-center justify-center text-white text-xl font-bold">
                        {{ strtoupper(substr($viewingAsUser->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-indigo-100 text-sm">Viewing Dashboard As</p>
                        <p class="text-2xl font-bold">{{ $viewingAsUser->name }}</p>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-white/20">
                                {{ ucfirst($targetRole) }}
                            </span>
                            <span class="text-indigo-200 text-sm">{{ $viewingAsUser->email }}</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to My Dashboard
                </a>
            </div>
        </div>

        @if($targetRole === 'guest')
            {{-- Guest Dashboard Content --}}
            @include('dashboard.partials.guest-content', [
                'timeSummaries' => $timeSummaries,
                'myTasks' => $myTasks,
                'pendingTasks' => $pendingTasks,
                'completedTasks' => $completedTasks,
                'recentTimeEntries' => $recentTimeEntries,
                'assignedProjects' => $assignedProjects,
                'estimationPollingTasks' => $estimationPollingTasks,
                'viewingAsUser' => $viewingAsUser
            ])
        @elseif($targetRole === 'member')
            {{-- Member Dashboard Content --}}
            @include('dashboard.partials.member-content', [
                'myTimeSummary' => $myTimeSummary,
                'teamTimeTracking' => $teamTimeTracking,
                'myProjects' => $myProjects,
                'myTasks' => $myTasks,
                'guests' => $guests,
                'estimationPollingTasks' => $estimationPollingTasks,
                'viewingAsUser' => $viewingAsUser
            ])
        @else
            {{-- Admin Dashboard Content --}}
            @include('dashboard.partials.admin-content', [
                'stats' => $stats,
                'allProjects' => $allProjects,
                'allMembers' => $allMembers,
                'viewingAsUser' => $viewingAsUser
            ])
        @endif
    </div>
</div>
@endsection

