@extends('layouts.app')

@section('title', 'Assign Testers - ' . $project->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Assign Testers</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Project: <strong>{{ $project->name }}</strong>
                </p>
            </div>
            <a href="{{ route('projects.show', $project) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Project
            </a>
        </div>
    </div>

    <!-- Already Assigned Testers -->
    @if($assignedTesters->isNotEmpty())
    <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-green-900 mb-3">✅ Assigned Testers</h3>
        <div class="space-y-2">
            @foreach($assignedTesters as $projectTester)
            <div class="flex items-center justify-between bg-white rounded-lg p-3 shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr($projectTester->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $projectTester->user->name }}</p>
                        <p class="text-xs text-gray-600">Assigned {{ $projectTester->assigned_at->diffForHumans() }}</p>
                    </div>
                </div>
                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                    Active
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Recommended Testers -->
    @if($recommendedTesters->isNotEmpty())
    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-blue-900 mb-2">💡 Recommended Testers</h3>
        <p class="text-xs text-blue-700 mb-3">Based on current workload (least busy testers)</p>
        <div class="space-y-2">
            @foreach($recommendedTesters as $tester)
            <div class="flex items-center justify-between bg-white rounded-lg p-3 shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr($tester->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $tester->name }}</p>
                        <p class="text-xs text-gray-600">
                            Current workload: {{ app(App\Services\TesterAssignmentService::class)->getTesterWorkload($tester) }} project(s)
                        </p>
                    </div>
                </div>
                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                    Recommended
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Assignment Form -->
    <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Select Testers to Assign</h2>

        <form method="POST" action="{{ route('projects.store-testers', $project) }}">
            @csrf

            <!-- Testers List -->
            <div class="space-y-2 mb-6 max-h-80 overflow-y-auto">
                @forelse($availableTesters as $tester)
                <label class="flex items-center p-4 bg-gray-50 hover:bg-indigo-50 rounded-lg cursor-pointer border-2 border-gray-200 hover:border-indigo-300 transition-all">
                    <input type="checkbox" 
                           name="tester_ids[]" 
                           value="{{ $tester->id }}"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-5 w-5">
                    <div class="ml-4 flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr($tester->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $tester->name }}</p>
                            <p class="text-xs text-gray-600">
                                {{ app(App\Services\TesterAssignmentService::class)->getTesterWorkload($tester) }} active project(s)
                            </p>
                        </div>
                    </div>
                </label>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <svg class="h-12 w-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-sm">No testers available in this workspace</p>
                    <p class="text-xs text-gray-400 mt-1">Create guests with Testing track first</p>
                </div>
                @endforelse
            </div>

            @if($availableTesters->isNotEmpty())
            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('projects.show', $project) }}" 
                   class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-lg text-sm font-medium text-white shadow-md hover:shadow-lg transition-all">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Assign Selected Testers
                </button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection
