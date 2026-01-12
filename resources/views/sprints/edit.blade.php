@extends('layouts.app')

@section('title', 'Edit Sprint')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Sprint</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update sprint details</p>
    </div>

    <form action="{{ route('sprints.update', ['workspace' => session('current_workspace_id'), 'sprint' => $sprint]) }}" method="POST" class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
        @csrf
        @method('PUT')

        <!-- Sprint Name -->
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Sprint Name *
            </label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   value="{{ old('name', $sprint->name) }}"
                   required
                   class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Project (Optional) -->
        <div class="mb-4">
            <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Project (Optional)
            </label>
            <select id="project_id" 
                    name="project_id" 
                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">No specific project</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id', $sprint->project_id) == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
            @error('project_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Sprint Goal -->
        <div class="mb-4">
            <label for="goal" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Sprint Goal
            </label>
            <textarea id="goal" 
                      name="goal" 
                      rows="3"
                      class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('goal', $sprint->goal) }}</textarea>
            @error('goal')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Dates -->
        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Start Date *
                </label>
                <input type="date" 
                       id="start_date" 
                       name="start_date" 
                       value="{{ old('start_date', $sprint->start_date->format('Y-m-d')) }}"
                       required
                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('start_date')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    End Date *
                </label>
                <input type="date" 
                       id="end_date" 
                       name="end_date" 
                       value="{{ old('end_date', $sprint->end_date->format('Y-m-d')) }}"
                       required
                       class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('end_date')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Status -->
        <div class="mb-6">
            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Status *
            </label>
            <select id="status" 
                    name="status" 
                    required
                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="planning" {{ old('status', $sprint->status) === 'planning' ? 'selected' : '' }}>Planning</option>
                <option value="active" {{ old('status', $sprint->status) === 'active' ? 'selected' : '' }}>Active</option>
                <option value="completed" {{ old('status', $sprint->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ old('status', $sprint->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-between">
            <form action="{{ route('sprints.destroy', ['workspace' => session('current_workspace_id'), 'sprint' => $sprint]) }}" method="POST" 
                  onsubmit="return confirm('Are you sure you want to delete this sprint? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                    Delete Sprint
                </button>
            </form>

            <div class="flex items-center space-x-3">
                <a href="{{ route('sprints.show', ['workspace' => session('current_workspace_id'), 'sprint' => $sprint]) }}" 
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-50 dark:hover:bg-gray-700">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                    Update Sprint
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
