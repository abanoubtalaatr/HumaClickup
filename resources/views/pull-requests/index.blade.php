@extends('layouts.app')

@section('title', 'Pull Requests')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Pull Requests</h1>
                <p class="mt-1 text-sm text-gray-500">Track daily pull request submissions by track and project.</p>
            </div>
            @if($isGuest)
                <a href="{{ route('pull-requests.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Submit Pull Request
                </a>
            @endif
        </div>

        <!-- Daily requirement message -->
        @if(!$prRequiredToday)
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-blue-800">Pull requests are <strong>not required</strong> today (Friday and Saturday are off). You can still submit if you have work to share.</p>
                </div>
            </div>
        @elseif($isGuest && !$todaySubmitted)
            <div class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <p class="text-sm text-amber-800">You haven't submitted a pull request for today yet.</p>
                    <a href="{{ route('pull-requests.create') }}" class="inline-flex items-center px-3 py-2 border border-amber-300 rounded-md text-sm font-medium text-amber-800 bg-white hover:bg-amber-50">
                        Submit now
                    </a>
                </div>
            </div>
        @elseif($isGuest && $todaySubmitted)
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-sm text-green-800">✅ You have submitted at least one pull request for today.</p>
            </div>
        @endif

        <!-- Filters (for member/admin/owner) -->
        @if($canManageAll)
            <div class="mb-6 bg-white shadow rounded-lg p-4">
                <form method="GET" action="{{ route('pull-requests.index') }}" class="flex flex-wrap gap-4 items-end">
                    <div class="min-w-[140px]">
                        <label for="filter_date_from" class="block text-sm font-medium text-gray-700 mb-1">From date</label>
                        <input id="filter_date_from" type="date" name="date_from" value="{{ request('date_from') }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div class="min-w-[140px]">
                        <label for="filter_date_to" class="block text-sm font-medium text-gray-700 mb-1">To date</label>
                        <input id="filter_date_to" type="date" name="date_to" value="{{ request('date_to') }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    @if($isAdminOrOwner)
                    <div class="min-w-[160px]">
                        <label for="filter_track_id" class="block text-sm font-medium text-gray-700 mb-1">Track</label>
                        <select id="filter_track_id" name="track_id" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">All tracks</option>
                            @foreach($tracks as $t)
                                <option value="{{ $t->id }}" {{ request('track_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="min-w-[180px]">
                        <label for="filter_project_id" class="block text-sm font-medium text-gray-700 mb-1">Project</label>
                        <select id="filter_project_id" name="project_id" class="block w-full border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">All projects</option>
                            @foreach($projectsFilter as $p)
                                <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('pull-requests.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Clear</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Filter</button>
                    </div>
                </form>
            </div>

            @if($guestsWithoutPrForDate->isNotEmpty())
                <div class="mb-6 bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-red-50 border-b border-red-100">
                        <h2 class="text-sm font-semibold text-red-800">Guests without a pull request for the selected day</h2>
                        <p class="text-xs text-red-600 mt-0.5">These guests have not submitted a PR for a required day (excluding Friday/Saturday).</p>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        @foreach($guestsWithoutPrForDate as $g)
                        
                            <li class="px-4 py-2 flex items-center justify-between gap-4">
                                <span class="text-sm font-medium text-gray-900">{{ $g->name }}</span>
                                <span class="text-xs text-gray-500">{{ $g->email }} — {{ $g->getTrackNameInWorkspace() ?? '—' }}</span>
                                <span class="text-xs font-medium text-red-600 whitespace-nowrap">{{ $guestMissingCounts[$g->id] ?? 0 }} missing</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endif

        <!-- Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                <h2 class="text-sm font-medium text-gray-700">Submissions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            @if($canManageAll)
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                            @endif
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Track</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pull request link</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            @if($canManageAll)
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Compliance</th>
                            @endif
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($pullRequests as $pr)
                            <tr class="hover:bg-gray-50 {{ $pr->date->format('Y-m-d') === $today ? 'bg-indigo-50/50' : '' }}">
                                @if($canManageAll)
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $pr->user->name ?? '—' }}</td>
                                @endif
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $pr->track->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $pr->project->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ $pr->link }}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:text-indigo-800 truncate max-w-xs inline-block" title="{{ $pr->link }}">
                                        {{ Str::limit($pr->link, 50) }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">{{ $pr->date->format('M d, Y') }}</td>
                                @if($canManageAll)
                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                        @if(\App\Models\PullRequest::isRequiredDay($pr->date))
                                            <span class="text-green-600" title="Submitted for a required day">✅ Submitted</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm">
                                    @if(($isGuest && $pr->user_id === auth()->id()) || $canManageAll)
                                        <a href="{{ route('pull-requests.edit', $pr) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form action="{{ route('pull-requests.destroy', $pr) }}" method="POST" class="inline" onsubmit="return confirm('Delete this pull request?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canManageAll ? 7 : 5 }}" class="px-4 py-12 text-center text-sm text-gray-500">
                                    No pull requests found.
                                    @if($isGuest)
                                        <a href="{{ route('pull-requests.create') }}" class="ml-1 text-indigo-600 hover:text-indigo-800">Submit one</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
