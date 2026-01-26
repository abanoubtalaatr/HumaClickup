@extends('layouts.app')

@section('title', 'Create Topic')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Create New Topic</h1>
            <p class="mt-1 text-sm text-gray-500">Add a new presentation topic</p>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('topics.store') }}" method="POST" class="p-6">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Topic Name *
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}"
                           required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
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
                              rows="4"
                              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Presentation Link -->
                <div class="mb-4">
                    <label for="presentation_link" class="block text-sm font-medium text-gray-700 mb-2">
                        Presentation Link
                    </label>
                    <input type="url" 
                           id="presentation_link" 
                           name="presentation_link" 
                           value="{{ old('presentation_link') }}"
                           placeholder="https://..."
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('presentation_link')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div class="mb-4">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date *
                    </label>
                    <input type="date" 
                           id="date" 
                           name="date" 
                           value="{{ old('date', date('Y-m-d')) }}"
                           required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Track -->
                <div class="mb-4">
                    <label for="track_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Track (Optional)
                    </label>
                    <select id="track_id" 
                            name="track_id" 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">No Track</option>
                        @foreach($tracks as $track)
                            <option value="{{ $track->id }}" {{ old('track_id', $userTrack?->id) == $track->id ? 'selected' : '' }}>
                                {{ $track->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('track_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Complete -->
                <div class="mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="is_complete" 
                               name="is_complete" 
                               value="1"
                               {{ old('is_complete') ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="is_complete" class="ml-2 block text-sm text-gray-900">
                            Mark as complete
                        </label>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('topics.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Create Topic
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
