@extends('layouts.app')

@section('title', 'Edit Time Entry')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Time Entry</h1>
            <p class="mt-1 text-sm text-gray-500">Update your time tracking entry</p>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('time-tracking.entries.update', $timeEntry) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <!-- Task -->
                <div class="mb-4">
                    <label for="task_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Task *
                    </label>
                    <select id="task_id" 
                            name="task_id" 
                            required
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Choose a task...</option>
                        @foreach($tasks ?? [] as $task)
                            <option value="{{ $task->id }}" {{ old('task_id', $timeEntry->task_id) == $task->id ? 'selected' : '' }}>
                                {{ $task->title }} ({{ $task->project->name ?? 'No Project' }})
                            </option>
                        @endforeach
                    </select>
                    @error('task_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Time -->
                <div class="mb-4">
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                        Start Time *
                    </label>
                    <input type="datetime-local" 
                           id="start_time" 
                           name="start_time" 
                           value="{{ old('start_time', $timeEntry->start_time->format('Y-m-d\TH:i')) }}"
                           required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('start_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- End Time -->
                <div class="mb-4">
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                        End Time *
                    </label>
                    <input type="datetime-local" 
                           id="end_time" 
                           name="end_time" 
                           value="{{ old('end_time', $timeEntry->end_time ? $timeEntry->end_time->format('Y-m-d\TH:i') : '') }}"
                           required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('end_time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              placeholder="What did you work on?"
                              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $timeEntry->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Billable -->
                <div class="mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_billable" 
                               name="is_billable" 
                               value="1"
                               {{ old('is_billable', $timeEntry->is_billable) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_billable" class="ml-2 block text-sm text-gray-900">
                            Billable
                        </label>
                    </div>
                </div>

                <!-- Current Duration Display -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">Current Duration:</span> 
                        <span class="text-lg font-bold text-gray-900">{{ $timeEntry->getFormattedDuration() }}</span>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Duration will be recalculated automatically when you update the times.</p>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('time-tracking.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Update Time Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
