@extends('layouts.app')

@section('title', 'Mentor Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Mentor Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Approve student progress and attendance</p>
        </div>

        <!-- Date Filter -->
        <form method="GET" class="flex items-center space-x-2">
            <input type="date" 
                   name="date" 
                   value="{{ $date->format('Y-m-d') }}" 
                   class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Filter
            </button>
        </form>
    </div>

    <!-- Alert Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Guests Without Tasks -->
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-6 w-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-red-900">No Task Today</p>
                    <p class="text-2xl font-bold text-red-600">{{ $guestsWithoutTasks->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Incomplete Progress -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-6 w-6 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-yellow-900">Incomplete Progress</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $guestsWithIncomplete->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="h-6 w-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-900">Pending Approvals</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $pendingProgress->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Progress Approvals -->
    @if($pendingProgress->count() > 0)
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Pending Progress Approvals</h2>
            
            <!-- Bulk Approve Form -->
            <form action="{{ route('mentor.bulk-approve-progress') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                @foreach($pendingProgress as $progress)
                    <input type="hidden" name="progress_ids[]" value="{{ $progress->id }}">
                @endforeach
                <button type="submit" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700">
                    Approve All ({{ $pendingProgress->count() }})
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Main Task</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pendingProgress as $progress)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-semibold">
                                    {{ strtoupper(substr($progress->user->name, 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $progress->user->name }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">{{ $progress->project->name }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($progress->task)
                                <p class="text-sm text-gray-900">{{ $progress->task->title }}</p>
                                <p class="text-xs text-gray-500">Status: {{ $progress->task->status->name ?? 'N/A' }}</p>
                            @else
                                <p class="text-sm text-gray-500">No task assigned</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="text-sm text-gray-900">{{ number_format($progress->completed_hours, 1) }}h / {{ number_format($progress->required_hours, 1) }}h</p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <!-- Progress Bar -->
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                    <div class="h-2 rounded-full transition-all {{ $progress->meetsTarget() ? 'bg-green-600' : 'bg-yellow-500' }}" 
                                         style="width: {{ $progress->progress_percentage }}%"></div>
                                </div>
                                <span class="text-sm font-medium {{ $progress->meetsTarget() ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ number_format($progress->progress_percentage, 0) }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <form action="{{ route('mentor.approve-progress', $progress) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="px-3 py-1.5 text-xs font-semibold rounded {{ $progress->meetsTarget() ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 hover:bg-gray-500' }} text-white">
                                    Approve
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Guests Without Tasks Alert -->
    @if($guestsWithoutTasks->count() > 0)
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 text-red-600">⚠️ Guests Without Main Tasks Today</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($guestsWithoutTasks as $item)
                    <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                        <p class="font-medium text-gray-900">{{ $item['guest']->name }}</p>
                        <p class="text-sm text-gray-600">{{ $item['project']->name }}</p>
                        <a href="{{ route('projects.tasks.create', $item['project']) }}" 
                           class="mt-2 inline-block text-xs text-indigo-600 hover:text-indigo-800">
                            Create Task →
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Guests With Incomplete Progress -->
    @if($guestsWithIncomplete->count() > 0)
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Guests With Incomplete Progress</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($guestsWithIncomplete as $item)
                    <div class="border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                        <p class="font-medium text-gray-900">{{ $item['guest']->name }}</p>
                        <p class="text-sm text-gray-600">{{ $item['project']->name }}</p>
                        <p class="text-xs text-yellow-700 mt-1">Progress below 100%</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
